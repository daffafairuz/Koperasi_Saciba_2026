<?php

namespace Tests\Feature\Admin;

use App\Models\TransaksiKasUsaha;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KasUsahaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dapat_melihat_ringkasan_dan_memfilter_buku_kas_usaha(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.kas-usaha.index'));

        $response->assertOk();
        $response->assertViewHas('totalKasKoperasi', '253445714.28');
        $response->assertViewHas('totalPemasukanUsaha', '281200000.00');
        $response->assertViewHas('totalPengeluaranUsaha', '22200000.00');
        $response->assertSee('Rp 253.445.714');
        $response->assertSee('KAS-2026-001');

        $this->actingAs($admin)
            ->get(route('admin.kas-usaha.index', ['jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR, 'kategori' => 'THR']))
            ->assertOk()
            ->assertSee('KAS-2026-004')
            ->assertDontSee('KAS-2026-001');
    }

    public function test_pencatatan_kas_masuk_dan_keluar_membuat_nomor_otomatis_dan_sinkron_ke_dashboard(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        // 1. Catat MASUK Rp 5.000.000
        $this->actingAs($admin)
            ->post(route('admin.kas-usaha.store'), [
                'tanggal_transaksi' => '2026-11-24',
                'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                'kategori' => 'USAHA_WARUNG',
                'nominal' => 5000000,
                'keterangan' => 'Setoran penjualan warung bulan November 2026.',
            ])
            ->assertRedirect(route('admin.kas-usaha.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('transaksi_kas_usaha', [
            'nomor_transaksi' => 'KAS-20261124-0001',
            'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
            'kategori' => 'USAHA_WARUNG',
            'nominal' => '5000000.00',
            'diinput_oleh' => $admin->id,
        ]);

        // 2. Catat KELUAR Rp 1.500.000 di tanggal yang sama -> urutan 0002
        $this->actingAs($admin)
            ->post(route('admin.kas-usaha.store'), [
                'tanggal_transaksi' => '2026-11-24',
                'jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR,
                'kategori' => 'BIAYA_OPERASIONAL',
                'nominal' => 1500000,
                'keterangan' => 'Pembelian perlengkapan administrasi koperasi.',
            ])
            ->assertRedirect(route('admin.kas-usaha.index'));

        $this->assertDatabaseHas('transaksi_kas_usaha', [
            'nomor_transaksi' => 'KAS-20261124-0002',
            'jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR,
            'nominal' => '1500000.00',
        ]);

        // Saldo kas utama bertambah net Rp 3.500.000: 253.445.714,28 + 3.500.000 = 256.945.714,28
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '256945714.28');
    }

    public function test_pengeluaran_melebihi_total_kas_koperasi_ditolak_agar_kas_tidak_minus(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $jumlahSebelum = TransaksiKasUsaha::query()->count();

        $this->actingAs($admin)
            ->from(route('admin.kas-usaha.create'))
            ->post(route('admin.kas-usaha.store'), [
                'tanggal_transaksi' => '2026-11-24',
                'jenis_arus' => TransaksiKasUsaha::ARUS_KELUAR,
                'kategori' => 'BIAYA_OPERASIONAL',
                'nominal' => 300000000, // Melebihi kas Rp 253.445.714,28
                'keterangan' => 'Pengeluaran melebihi saldo kas.',
            ])
            ->assertRedirect(route('admin.kas-usaha.create'))
            ->assertSessionHasErrors('nominal');

        $this->assertSame($jumlahSebelum, TransaksiKasUsaha::query()->count());
    }

    public function test_validasi_menolak_nominal_kurang_dari_1000_dan_anggota_biasa_ditolak_403(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $anggota = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.kas-usaha.store'), [
                'tanggal_transaksi' => '2026-11-24',
                'jenis_arus' => TransaksiKasUsaha::ARUS_MASUK,
                'kategori' => 'LAINNYA',
                'nominal' => 500,
                'keterangan' => 'Nominal terlalu kecil.',
            ])
            ->assertSessionHasErrors('nominal');

        $this->actingAs($anggota)->get(route('admin.kas-usaha.index'))->assertForbidden();
        $this->actingAs($anggota)->get(route('admin.kas-usaha.create'))->assertForbidden();
    }
}
