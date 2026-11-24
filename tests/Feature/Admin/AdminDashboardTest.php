<?php

namespace Tests\Feature\Admin;

use App\Models\Pinjaman;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_admin_menghitung_seluruh_metrik_keuangan_dan_statistik_secara_akurat(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();

        // Verifikasi nilai mentah di view data
        $response->assertViewHas('totalKasKoperasi', '253445714.28');
        $response->assertViewHas('totalDanaSimpanan', '50925000.00');
        $response->assertViewHas('totalPiutangPinjaman', '17529285.72');
        $response->assertViewHas('jumlahPinjamanBerjalan', 6);
        $response->assertViewHas('jumlahAnggotaAktif', 5);
        $response->assertViewHas('jumlahAnggotaKeluar', 2);

        // Verifikasi tampilan format_rupiah() pada kartu metrik
        $response->assertSee('Rp 253.445.714');
        $response->assertSee('Rp 50.925.000');
        $response->assertSee('Rp 17.529.286');

        // Verifikasi menu sidebar & sakelar mode anggota
        $response->assertSee('Data Anggota');
        $response->assertSee('Pengaturan Koperasi');
        $response->assertSee('Pinjaman');
        $response->assertSee('Input Pembayaran');
        $response->assertSee('Kas Usaha');
        $response->assertSee('Lihat Mode Anggota Saya');

        // Verifikasi Top 5 Piutang Terbesar (Laptop Rp 5.750.000 di posisi teratas)
        $response->assertSeeInOrder(['PJM-2026-002', 'PJM-2026-001', 'PJM-MIG-2026-001']);

        // Verifikasi 5 Transaksi Setoran Terbaru & Daftar Anggota Keluar Terbaru
        $response->assertSee('BYR-2026-002');
        $response->assertSee('BYR-2026-004');
        $response->assertSee('Rina Kusuma');
        $response->assertSee('Dedi Pratama');
    }

    public function test_pinjaman_migrasi_tidak_memotong_total_kas_koperasi_sedangkan_pinjaman_baru_memotong(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        // Tambah pinjaman migrasi Rp 1.000.000 -> kas tidak berkurang
        Pinjaman::query()->create([
            'nomor_pinjaman' => 'PJM-MIG-TEST-001',
            'anggota_id' => $siti->id,
            'jenis_pinjaman' => Pinjaman::JENIS_UANG,
            'pokok_pinjaman' => '1000000.00',
            'persen_margin' => '0.00',
            'total_tagihan' => '1000000.00',
            'sisa_tagihan' => '1000000.00',
            'tenor_bulan' => 5,
            'cicilan_per_bulan' => '200000.00',
            'status' => Pinjaman::STATUS_BERJALAN,
            'tanggal_cair' => '2025-11-01',
            'is_migrasi' => true,
            'dicairkan_oleh' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '253445714.28')
            ->assertViewHas('totalPiutangPinjaman', '18529285.72');

        // Tambah pinjaman baru (is_migrasi = false) Rp 2.000.000 -> kas berkurang Rp 2.000.000
        Pinjaman::query()->create([
            'nomor_pinjaman' => 'PJM-BARU-TEST-002',
            'anggota_id' => $siti->id,
            'jenis_pinjaman' => Pinjaman::JENIS_UANG,
            'pokok_pinjaman' => '2000000.00',
            'persen_margin' => '0.00',
            'total_tagihan' => '2000000.00',
            'sisa_tagihan' => '2000000.00',
            'tenor_bulan' => 4,
            'cicilan_per_bulan' => '500000.00',
            'status' => Pinjaman::STATUS_BERJALAN,
            'tanggal_cair' => '2026-11-20',
            'is_migrasi' => false,
            'dicairkan_oleh' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '251445714.28')
            ->assertViewHas('totalPiutangPinjaman', '20529285.72');
    }

    public function test_anggota_biasa_tetap_ditolak_403_pada_dashboard_admin(): void
    {
        $anggota = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($anggota)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
