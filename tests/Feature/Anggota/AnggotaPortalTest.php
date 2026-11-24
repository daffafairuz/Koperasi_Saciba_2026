<?php

namespace Tests\Feature\Anggota;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnggotaPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_beranda_anggota_menampilkan_rekap_pribadi_dengan_format_helper_dan_anti_idor(): void
    {
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $response = $this->actingAs($siti)->get(route('anggota.dashboard'));

        $response->assertOk();
        $response->assertSee('Siti Aminah');
        $response->assertSee('Rp 7.900.000'); // 7.500.000 awal + 300.000 SW + 100.000 SP
        $response->assertSee('Juni 2026');
        $response->assertSee('Rp 250.000'); // 5 bulan tunggakan (Jul-Nov 2026)
        $response->assertSee('BYR-2026-001');

        // Anti-IDOR: struk milik anggota lain tidak boleh muncul
        $response->assertDontSee('BYR-2026-003');
        $response->assertDontSee('BYR-2026-004');
        $response->assertDontSee('Kembali ke Panel Admin');
    }

    public function test_halaman_simpanan_menampilkan_detail_dompet_dan_tagihan_terurut_terbaru(): void
    {
        $ahmad = User::query()->where('nomor_anggota', 'K.10.102')->firstOrFail();

        $response = $this->actingAs($ahmad)->get(route('anggota.simpanan'));

        $response->assertOk();
        $response->assertSee('Rp 7.600.000'); // 6.850.000 awal + 650.000 SW + 100.000 SP
        $response->assertSee('Januari 2027'); // Advance payment muncul teratas
        $response->assertSee('LUNAS');
        $response->assertSeeInOrder(['Januari 2027', 'Desember 2026', 'November 2026']);
    }

    public function test_halaman_pinjaman_menampilkan_pinjaman_uang_dan_lebih_dari_dua_pinjaman_barang_aktif(): void
    {
        $dewi = User::query()->where('nomor_anggota', 'K.10.103')->firstOrFail();

        $response = $this->actingAs($dewi)->get(route('anggota.pinjaman'));

        $response->assertOk();
        $response->assertSee('PJM-2026-001');
        $response->assertSee('PJM-2026-002');
        $response->assertSee('Laptop Lenovo ThinkPad');
        $response->assertSee('PJM-2026-003');
        $response->assertSee('Mesin Cuci Samsung 8Kg');
        $response->assertSee('PJM-2026-004');
        $response->assertSee('Smartphone Redmi Note 14');
        $response->assertSee('x-data="{ open: false }"', false);

        // Anti-IDOR: pinjaman milik Hendra tidak boleh tampil
        $response->assertDontSee('PJM-MIG-2026-001');
        $response->assertDontSee('Kulkas 2 Pintu Sharp');
    }

    public function test_halaman_riwayat_menampilkan_struk_dan_rincian_alokasi_detail_pembayaran(): void
    {
        $hendra = User::query()->where('nomor_anggota', 'K.10.104')->firstOrFail();

        $response = $this->actingAs($hendra)->get(route('anggota.riwayat'));

        $response->assertOk();
        $response->assertSee('BYR-2026-003');
        $response->assertSee('Rp 4.225.000');
        $response->assertSee('Simpanan Wajib');
        $response->assertSee('Mei 2026');
        $response->assertSee('Cicilan Pinjaman');
        $response->assertSee('Kulkas 2 Pintu Sharp');
        $response->assertSee('PJM-MIG-2026-001');

        // Anti-IDOR: transaksi anggota lain tidak boleh tampil
        $response->assertDontSee('BYR-2026-001');
        $response->assertDontSee('BYR-2026-004');
    }

    public function test_admin_memiliki_sakelar_bolak_balik_antara_panel_admin_dan_mode_anggota(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Lihat Mode Anggota Saya');

        $this->actingAs($admin)
            ->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSee('Kembali ke Panel Admin')
            ->assertSee('Rp 9.275.000');
    }
}
