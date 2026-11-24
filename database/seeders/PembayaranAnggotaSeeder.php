<?php

namespace Database\Seeders;

use App\Models\CicilanPinjaman;
use App\Models\DetailPembayaran;
use App\Models\PembayaranAnggota;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\SimpananAnggota;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembayaranAnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::query()
                ->where('peran', User::PERAN_ADMIN)
                ->firstOrFail();

            $pengaturan = PengaturanKoperasi::query()->firstOrFail();

            $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();
            $ahmad = User::query()->where('nomor_anggota', 'K.10.102')->firstOrFail();
            $dewi = User::query()->where('nomor_anggota', 'K.10.103')->firstOrFail();
            $hendra = User::query()->where('nomor_anggota', 'K.10.104')->firstOrFail();
            $rina = User::query()->where('nomor_anggota', 'K.10.105')->firstOrFail();

            // 1. Siti Aminah: Pelunasan lumpsum 6 bulan SW (202601 - 202606)
            $this->prosesBulkPayment(
                nomorTransaksi: 'BYR-2026-001',
                anggota: $siti,
                admin: $admin,
                tanggalBayar: Carbon::create(2026, 6, 10, 10, 15, 0),
                metodeBayar: PembayaranAnggota::METODE_TRANSFER,
                catatan: 'Pelunasan lumpsum Simpanan Wajib semester I 2026 (Januari - Juni 2026).',
                periodeSw: ['202601', '202602', '202603', '202604', '202605', '202606'],
                alokasiCicilan: [],
                nominalSwDefault: (string) $pengaturan->nominal_simpanan_wajib,
            );

            // 2. Ahmad Fauzi: Pelunasan SW 2026 + Advance Payment (202612 & 202701)
            $this->prosesBulkPayment(
                nomorTransaksi: 'BYR-2026-002',
                anggota: $ahmad,
                admin: $admin,
                tanggalBayar: Carbon::create(2026, 11, 5, 11, 30, 0),
                metodeBayar: PembayaranAnggota::METODE_TRANSFER,
                catatan: 'Pelunasan Simpanan Wajib 2026 dan pembayaran di muka (advance payment) s.d. Januari 2027.',
                periodeSw: [
                    '202601', '202602', '202603', '202604', '202605', '202606',
                    '202607', '202608', '202609', '202610', '202611', '202612', '202701',
                ],
                alokasiCicilan: [],
                nominalSwDefault: (string) $pengaturan->nominal_simpanan_wajib,
            );

            // 3. Hendra Wijaya: Bulk payment gabungan SW + cicilan pinjaman migrasi (termasuk 1 cicilan SEBAGIAN)
            $this->prosesBulkPayment(
                nomorTransaksi: 'BYR-2026-003',
                anggota: $hendra,
                admin: $admin,
                tanggalBayar: Carbon::create(2026, 5, 12, 9, 45, 0),
                metodeBayar: PembayaranAnggota::METODE_TUNAI,
                catatan: 'Pembayaran gabungan SW Jan-Mei 2026, cicilan pinjaman uang 1-4 lunas & ke-5 sebagian, serta cicilan kulkas 1-3 lunas.',
                periodeSw: ['202601', '202602', '202603', '202604', '202605'],
                alokasiCicilan: [
                    ['nomor_pinjaman' => 'PJM-MIG-2026-001', 'cicilan_ke' => 1, 'nominal' => '500000.00'],
                    ['nomor_pinjaman' => 'PJM-MIG-2026-001', 'cicilan_ke' => 2, 'nominal' => '500000.00'],
                    ['nomor_pinjaman' => 'PJM-MIG-2026-001', 'cicilan_ke' => 3, 'nominal' => '500000.00'],
                    ['nomor_pinjaman' => 'PJM-MIG-2026-001', 'cicilan_ke' => 4, 'nominal' => '500000.00'],
                    ['nomor_pinjaman' => 'PJM-MIG-2026-001', 'cicilan_ke' => 5, 'nominal' => '250000.00'], // SEBAGIAN
                    ['nomor_pinjaman' => 'PJM-MIG-2026-002', 'cicilan_ke' => 1, 'nominal' => '575000.00'],
                    ['nomor_pinjaman' => 'PJM-MIG-2026-002', 'cicilan_ke' => 2, 'nominal' => '575000.00'],
                    ['nomor_pinjaman' => 'PJM-MIG-2026-002', 'cicilan_ke' => 3, 'nominal' => '575000.00'],
                ],
                nominalSwDefault: (string) $pengaturan->nominal_simpanan_wajib,
            );

            // 4. Dewi Lestari: Bulk payment gabungan SW + 4 pinjaman aktif sekaligus (1 uang + 3 barang)
            $this->prosesBulkPayment(
                nomorTransaksi: 'BYR-2026-004',
                anggota: $dewi,
                admin: $admin,
                tanggalBayar: Carbon::create(2026, 9, 10, 14, 20, 0),
                metodeBayar: PembayaranAnggota::METODE_TRANSFER,
                catatan: 'Pembayaran gabungan SW Jan-Sep 2026 dan angsuran pinjaman uang + 3 pinjaman barang aktif.',
                periodeSw: [
                    '202601', '202602', '202603', '202604', '202605',
                    '202606', '202607', '202608', '202609',
                ],
                alokasiCicilan: [
                    ['nomor_pinjaman' => 'PJM-2026-001', 'cicilan_ke' => 1, 'nominal' => '500000.00'],
                    ['nomor_pinjaman' => 'PJM-2026-001', 'cicilan_ke' => 2, 'nominal' => '500000.00'],
                    ['nomor_pinjaman' => 'PJM-2026-002', 'cicilan_ke' => 1, 'nominal' => '575000.00'],
                    ['nomor_pinjaman' => 'PJM-2026-002', 'cicilan_ke' => 2, 'nominal' => '575000.00'],
                    ['nomor_pinjaman' => 'PJM-2026-003', 'cicilan_ke' => 1, 'nominal' => '410714.28'],
                    ['nomor_pinjaman' => 'PJM-2026-004', 'cicilan_ke' => 1, 'nominal' => '460000.00'],
                ],
                nominalSwDefault: (string) $pengaturan->nominal_simpanan_wajib,
            );

            // 5. Rina Kusuma (KELUAR): Pelunasan SW Jan-Jun 2026 sebelum keluar per 30 Juni 2026
            $this->prosesBulkPayment(
                nomorTransaksi: 'BYR-2026-005',
                anggota: $rina,
                admin: $admin,
                tanggalBayar: Carbon::create(2026, 6, 25, 13, 0, 0),
                metodeBayar: PembayaranAnggota::METODE_TUNAI,
                catatan: 'Pelunasan Simpanan Wajib hingga bulan pengunduran diri (Juni 2026).',
                periodeSw: ['202601', '202602', '202603', '202604', '202605', '202606'],
                alokasiCicilan: [],
                nominalSwDefault: (string) $pengaturan->nominal_simpanan_wajib,
            );
        });
    }

    /**
     * Eksekusi 1 transaksi bulk payment sekaligus menyinkronkan seluruh tabel induk secara atomik.
     *
     * @param  list<string>  $periodeSw
     * @param  list<array{nomor_pinjaman: string, cicilan_ke: int, nominal: string}>  $alokasiCicilan
     */
    private function prosesBulkPayment(
        string $nomorTransaksi,
        User $anggota,
        User $admin,
        Carbon $tanggalBayar,
        string $metodeBayar,
        string $catatan,
        array $periodeSw,
        array $alokasiCicilan,
        string $nominalSwDefault,
    ): void {
        if (PembayaranAnggota::query()->where('nomor_transaksi', $nomorTransaksi)->exists()) {
            return;
        }

        $pembayaran = PembayaranAnggota::query()->create([
            'nomor_transaksi' => $nomorTransaksi,
            'anggota_id' => $anggota->id,
            'tanggal_bayar' => $tanggalBayar,
            'total_bayar' => '0.00',
            'metode_bayar' => $metodeBayar,
            'catatan' => $catatan,
            'diinput_oleh' => $admin->id,
        ]);

        $totalBayarCents = 0;
        $tambahanSwCents = 0;
        $periodeSwTerakhir = null;

        // 1. Proses alokasi Simpanan Wajib (termasuk advance payment jika periode belum ada)
        foreach ($periodeSw as $periode) {
            $tagihan = TagihanSimpananWajib::query()->firstOrCreate(
                [
                    'anggota_id' => $anggota->id,
                    'periode' => $periode,
                ],
                [
                    'nominal_tagihan' => $nominalSwDefault,
                    'nominal_terbayar' => '0.00',
                    'status' => TagihanSimpananWajib::STATUS_BELUM_BAYAR,
                    'tanggal_lunas' => null,
                ],
            );

            $tagihanCents = $this->toCents((string) $tagihan->nominal_tagihan);
            $sudahBayarCents = $this->toCents((string) $tagihan->nominal_terbayar);
            $sisaCents = max(0, $tagihanCents - $sudahBayarCents);

            if ($sisaCents === 0) {
                continue;
            }

            DetailPembayaran::query()->create([
                'pembayaran_id' => $pembayaran->id,
                'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_WAJIB,
                'simpanan_anggota_id' => null,
                'tagihan_sw_id' => $tagihan->id,
                'cicilan_pinjaman_id' => null,
                'nominal_bayar' => $this->formatCents($sisaCents),
            ]);

            $terbayarBaruCents = $sudahBayarCents + $sisaCents;
            $tagihan->update([
                'nominal_terbayar' => $this->formatCents($terbayarBaruCents),
                'status' => $terbayarBaruCents >= $tagihanCents
                    ? TagihanSimpananWajib::STATUS_LUNAS
                    : TagihanSimpananWajib::STATUS_SEBAGIAN,
                'tanggal_lunas' => $terbayarBaruCents >= $tagihanCents ? $tanggalBayar : null,
            ]);

            $totalBayarCents += $sisaCents;
            $tambahanSwCents += $sisaCents;

            if ($periodeSwTerakhir === null || $periode > $periodeSwTerakhir) {
                $periodeSwTerakhir = $periode;
            }
        }

        // Sinkronisasi simpanan_anggota
        if ($tambahanSwCents > 0) {
            $simpanan = SimpananAnggota::query()
                ->where('anggota_id', $anggota->id)
                ->firstOrFail();

            $totalSwBaruCents = $this->toCents((string) $simpanan->total_simpanan_wajib) + $tambahanSwCents;
            $periodeTerbaru = $simpanan->periode_sw_terakhir;

            if ($periodeSwTerakhir !== null && ($periodeTerbaru === null || $periodeSwTerakhir > $periodeTerbaru)) {
                $periodeTerbaru = $periodeSwTerakhir;
            }

            $simpanan->update([
                'total_simpanan_wajib' => $this->formatCents($totalSwBaruCents),
                'periode_sw_terakhir' => $periodeTerbaru,
            ]);
        }

        // 2. Proses alokasi Cicilan Pinjaman & sinkronisasi pinjaman.sisa_tagihan
        $pinjamanTerdampakIds = [];

        foreach ($alokasiCicilan as $item) {
            $pinjaman = Pinjaman::query()
                ->where('nomor_pinjaman', $item['nomor_pinjaman'])
                ->firstOrFail();

            $cicilan = CicilanPinjaman::query()
                ->where('pinjaman_id', $pinjaman->id)
                ->where('cicilan_ke', $item['cicilan_ke'])
                ->firstOrFail();

            $nominalBayarCents = $this->toCents($item['nominal']);
            $tagihanCents = $this->toCents((string) $cicilan->nominal_tagihan);
            $terbayarLamaCents = $this->toCents((string) $cicilan->nominal_terbayar);
            $terbayarBaruCents = min($tagihanCents, $terbayarLamaCents + $nominalBayarCents);
            $aktualBayarCents = $terbayarBaruCents - $terbayarLamaCents;

            if ($aktualBayarCents <= 0) {
                continue;
            }

            DetailPembayaran::query()->create([
                'pembayaran_id' => $pembayaran->id,
                'jenis_pembayaran' => DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                'simpanan_anggota_id' => null,
                'tagihan_sw_id' => null,
                'cicilan_pinjaman_id' => $cicilan->id,
                'nominal_bayar' => $this->formatCents($aktualBayarCents),
            ]);

            $statusCicilan = $terbayarBaruCents >= $tagihanCents
                ? CicilanPinjaman::STATUS_LUNAS
                : CicilanPinjaman::STATUS_SEBAGIAN;

            $cicilan->update([
                'nominal_terbayar' => $this->formatCents($terbayarBaruCents),
                'status' => $statusCicilan,
                'tanggal_bayar' => $tanggalBayar,
            ]);

            $totalBayarCents += $aktualBayarCents;
            $pinjamanTerdampakIds[$pinjaman->id] = $pinjaman;
        }

        // Sinkronisasi sisa_tagihan & status pada setiap pinjaman terdampak
        foreach ($pinjamanTerdampakIds as $pinjaman) {
            $totalTerbayarCents = CicilanPinjaman::query()
                ->where('pinjaman_id', $pinjaman->id)
                ->get()
                ->sum(fn (CicilanPinjaman $cicilan): int => $this->toCents((string) $cicilan->nominal_terbayar));

            $totalTagihanCents = $this->toCents((string) $pinjaman->total_tagihan);
            $sisaTagihanCents = max(0, $totalTagihanCents - $totalTerbayarCents);

            $pinjaman->update([
                'sisa_tagihan' => $this->formatCents($sisaTagihanCents),
                'status' => $sisaTagihanCents === 0 ? Pinjaman::STATUS_LUNAS : Pinjaman::STATUS_BERJALAN,
            ]);
        }

        // 3. Perbarui total_bayar pada header struk pembayaran_anggota
        $pembayaran->update([
            'total_bayar' => $this->formatCents($totalBayarCents),
        ]);
    }

    private function toCents(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
