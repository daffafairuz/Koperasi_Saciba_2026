<?php

namespace Tests\Feature\Console;

use App\Models\CicilanPinjaman;
use App\Models\DetailPembayaran;
use App\Models\PembayaranAnggota;
use App\Models\Pinjaman;
use App\Models\SimpananAnggota;
use App\Models\TransaksiKasUsaha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportDataExcelKoperasiCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $tempExcelPath = '';

    protected function tearDown(): void
    {
        if ($this->tempExcelPath !== '' && is_file($this->tempExcelPath)) {
            @unlink($this->tempExcelPath);
        }

        parent::tearDown();
    }

    public function test_command_mengimpor_multi_sheet_excel_secara_atomik_idempoten_dan_sinkron_dengan_bulk_payment(): void
    {
        $this->tempExcelPath = $this->buatFileExcelSimulasi();

        // 1. Jalankan impor pertama
        $this->artisan('koperasi:import-excel', ['file' => $this->tempExcelPath])
            ->expectsOutputToContain('Anggota diproses         : 3 orang')
            ->expectsOutputToContain('Total Saldo SW Awal      : Rp 17.500.000')
            ->expectsOutputToContain('Pinjaman Migrasi Diproses: 2 kontrak (10 jadwal cicilan baru)')
            ->expectsOutputToContain('Saldo Kas Awal Tercatat  : Rp 180.000.000')
            ->expectsOutputToContain('K.20.002') // Anggota tanpa HP muncul di daftar placeholder
            ->assertSuccessful();

        // Verifikasi 3 anggota impor + 1 admin bootstrap = 4 anggota
        $this->assertSame(4, User::query()->count());

        $anggota1 = User::query()->where('nomor_anggota', 'K.20.001')->firstOrFail();
        $anggota2 = User::query()->where('nomor_anggota', 'K.20.002')->firstOrFail();
        $anggota3 = User::query()->where('nomor_anggota', 'K.20.003')->firstOrFail();

        $this->assertSame('081234560001', $anggota1->no_hp);
        $this->assertTrue(str_starts_with($anggota2->no_hp, '08000'));
        $this->assertSame(User::STATUS_KELUAR, $anggota3->status);

        // Verifikasi SimpananAnggota
        $simpanan1 = SimpananAnggota::query()->where('anggota_id', $anggota1->id)->firstOrFail();
        $this->assertSame('7500000.00', (string) $simpanan1->saldo_sw_awal);
        $this->assertSame('7500000.00', (string) $simpanan1->total_simpanan_wajib);
        $this->assertSame('7600000.00', (string) $simpanan1->total_simpanan_akhir);

        // Verifikasi Pinjaman Migrasi (is_migrasi = true) & jadwal cicilan tenor ganjil 7 bulan
        $pinjamanBarang = Pinjaman::query()->where('nomor_pinjaman', 'PJM-EXL-002')->firstOrFail();
        $this->assertTrue($pinjamanBarang->is_migrasi);
        $this->assertSame('3450000.00', (string) $pinjamanBarang->sisa_tagihan);
        $this->assertSame(7, $pinjamanBarang->cicilan()->count());

        $sumCicilanCents = $pinjamanBarang->cicilan()
            ->get()
            ->sum(fn (CicilanPinjaman $c): int => (int) round(((float) $c->nominal_tagihan) * 100));
        $this->assertSame(345000000, $sumCicilanCents);

        // Verifikasi Saldo Kas Awal (is_migrasi = true tidak memotong kas Rp 180.000.000)
        $this->assertDatabaseHas('transaksi_kas_usaha', [
            'nomor_transaksi' => 'KAS-MIG-SALDO-AWAL',
            'kategori' => 'SALDO_AWAL',
            'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
            'nominal' => '180000000.00',
        ]);

        $admin = User::query()->where('peran', User::PERAN_ADMIN)->firstOrFail();
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '180000000.00');

        // 2. Uji idempotensi: jalankan impor ulang -> tidak ada duplikasi pinjaman/cicilan/kas
        $this->artisan('koperasi:import-excel', ['file' => $this->tempExcelPath])
            ->expectsOutputToContain('Pinjaman Migrasi Diproses: 2 kontrak (0 jadwal cicilan baru)')
            ->assertSuccessful();

        $this->assertSame(4, User::query()->count());
        $this->assertSame(2, Pinjaman::query()->count());
        $this->assertSame(10, CicilanPinjaman::query()->count());
        $this->assertSame(1, TransaksiKasUsaha::query()->count());

        // 3. Uji pembayaran 1 cicilan hasil migrasi lewat Bulk Payment -> sisa_tagihan turun akurat & kas bertambah!
        $pinjamanUang = Pinjaman::query()->where('nomor_pinjaman', 'PJM-EXL-001')->firstOrFail();
        $cicilan1 = $pinjamanUang->cicilan()->orderBy('cicilan_ke')->firstOrFail(); // 500.000

        $this->actingAs($admin)->post(route('admin.pembayaran.store'), [
            'anggota_id' => $anggota1->id,
            'tanggal_bayar' => '2026-01-15',
            'metode_bayar' => PembayaranAnggota::METODE_TUNAI,
            'items' => [
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                    'cicilan_pinjaman_id' => $cicilan1->id,
                    'nominal_bayar' => 500000,
                ],
            ],
        ])->assertRedirect();

        $pinjamanUang->refresh();
        $this->assertSame('1000000.00', (string) $pinjamanUang->sisa_tagihan); // 1.500.000 - 500.000
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '180500000.00');
    }

    public function test_command_mengembalikan_failure_jika_file_excel_tidak_ditemukan(): void
    {
        $this->artisan('koperasi:import-excel', ['file' => 'storage/app/tidak-ada.xlsx'])
            ->expectsOutputToContain('File Excel tidak ditemukan')
            ->assertFailed();
    }

    private function buatFileExcelSimulasi(): string
    {
        $spreadsheet = new Spreadsheet;

        // Sheet 1: Anggota_sw
        $sheetAnggota = $spreadsheet->getActiveSheet();
        $sheetAnggota->setTitle('Anggota_sw');
        $sheetAnggota->fromArray([
            ['Nomor Anggota', 'Nama Lengkap', 'No HP', 'Status', 'Tanggal Gabung', 'Tanggal Keluar', 'SP', 'Status SP', 'Saldo SW Awal'],
            ['K.20.001', 'Andi Saputra', '+62 812-3456-0001', 'AKTIF', '2021-02-01', '', 100000, 'LUNAS', 7500000],
            ['K.20.002', 'Sari Melati', '', 'AKTIF', '2022-05-10', '', 100000, 'LUNAS', 6000000],
            ['K.20.003', 'Tono Mulyono', '081234560003', 'KELUAR', '2020-01-15', '2025-11-30', 100000, 'LUNAS', 4000000],
        ]);

        // Sheet 2: Piutang
        $sheetPiutang = $spreadsheet->createSheet();
        $sheetPiutang->setTitle('Piutang');
        $sheetPiutang->fromArray([
            ['Nomor Pinjaman', 'Nomor Anggota', 'Jenis', 'Nama Barang', 'Pokok', 'Margin %', 'Sisa Tagihan', 'Sisa Tenor', 'Tanggal Cair'],
            ['PJM-EXL-001', 'K.20.001', 'UANG', '', 3000000, 0, 1500000, 3, '2025-09-01'],
            ['PJM-EXL-002', 'K.20.002', 'BARANG', 'Kulkas Polytron', 3000000, 15, 3450000, 7, '2025-12-01'],
        ]);

        // Sheet 3: Neraca 2025
        $sheetNeraca = $spreadsheet->createSheet();
        $sheetNeraca->setTitle('Neraca 2025');
        $sheetNeraca->fromArray([
            ['Pos Neraca', 'Nominal'],
            ['Saldo Kas Koperasi', 180000000],
        ]);

        $path = storage_path('app/test_import_koperasi_'.uniqid().'.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        return $path;
    }
}
