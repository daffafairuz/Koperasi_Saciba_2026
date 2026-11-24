<?php

namespace Tests\Feature\Admin;

use App\Models\PengaturanKoperasi;
use App\Models\SimpananAnggota;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanKoperasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dapat_melihat_halaman_pengaturan_tarif_aktif_dan_info_audit(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.pengaturan.index'));

        $response->assertOk();
        $response->assertSee('Rp 100.000');
        $response->assertSee('Rp 50.000');
        $response->assertSee('15,00%');
        $response->assertSee('Budi Santoso');
    }

    public function test_admin_dapat_memperbarui_tarif_dan_hanya_berlaku_untuk_transaksi_baru(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $tagihanLama = TagihanSimpananWajib::query()->firstOrFail();
        $nominalTagihanLamaSebelum = (string) $tagihanLama->nominal_tagihan;

        $response = $this->actingAs($admin)->put(route('admin.pengaturan.update'), [
            'nominal_simpanan_pokok' => 150000,
            'nominal_simpanan_wajib' => 75000,
            'persen_margin_barang' => 12.5,
        ]);

        $response->assertRedirect(route('admin.pengaturan.index'));
        $response->assertSessionHas('success');

        $pengaturan = PengaturanKoperasi::query()->firstOrFail();
        $this->assertSame('150000.00', (string) $pengaturan->nominal_simpanan_pokok);
        $this->assertSame('75000.00', (string) $pengaturan->nominal_simpanan_wajib);
        $this->assertSame('12.50', (string) $pengaturan->persen_margin_barang);
        $this->assertSame($admin->id, $pengaturan->diperbarui_oleh);
        $this->assertNotNull($pengaturan->diperbarui_pada);

        // Tagihan lama yang sudah terbit tidak boleh berubah (snapshot integrity)
        $tagihanLama->refresh();
        $this->assertSame($nominalTagihanLamaSebelum, (string) $tagihanLama->nominal_tagihan);

        // Anggota baru langsung memakai tarif Simpanan Pokok terbaru (Rp 150.000)
        $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nomor_anggota' => 'K.10.301',
            'nama_lengkap' => 'Anggota Tarif Baru',
            'no_hp' => '081300000301',
            'password' => 'rahasia123',
            'peran' => User::PERAN_ANGGOTA,
            'wajib_simpanan' => '1',
            'tanggal_gabung' => '2026-11-24',
        ]);

        $anggotaBaru = User::query()->where('nomor_anggota', 'K.10.301')->firstOrFail();
        $simpananBaru = SimpananAnggota::query()->where('anggota_id', $anggotaBaru->id)->firstOrFail();
        $this->assertSame('150000.00', (string) $simpananBaru->nominal_simpanan_pokok);
    }

    public function test_validasi_menolak_tarif_negatif_atau_margin_melebihi_100_persen(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.pengaturan.index'))
            ->put(route('admin.pengaturan.update'), [
                'nominal_simpanan_pokok' => -1000,
                'nominal_simpanan_wajib' => -500,
                'persen_margin_barang' => 150,
            ])
            ->assertRedirect(route('admin.pengaturan.index'))
            ->assertSessionHasErrors([
                'nominal_simpanan_pokok',
                'nominal_simpanan_wajib',
                'persen_margin_barang',
            ]);
    }

    public function test_anggota_biasa_ditolak_403_saat_mengakses_pengaturan_koperasi(): void
    {
        $anggota = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($anggota)->get(route('admin.pengaturan.index'))->assertForbidden();
        $this->actingAs($anggota)->put(route('admin.pengaturan.update'), [
            'nominal_simpanan_pokok' => 200000,
            'nominal_simpanan_wajib' => 100000,
            'persen_margin_barang' => 10,
        ])->assertForbidden();
    }
}
