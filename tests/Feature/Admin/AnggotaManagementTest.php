<?php

namespace Tests\Feature\Admin;

use App\Models\RiwayatStatusAnggota;
use App\Models\SimpananAnggota;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnggotaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dapat_mencari_dan_memfilter_daftar_anggota(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['q' => 'dewi']))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertDontSee('Siti Aminah');

        $this->actingAs($admin)
            ->get(route('admin.anggota.index', ['status' => User::STATUS_KELUAR]))
            ->assertOk()
            ->assertSee('Rina Kusuma')
            ->assertSee('Dedi Pratama')
            ->assertDontSee('Siti Aminah');
    }

    public function test_pendaftaran_anggota_baru_menormalisasi_no_hp_dan_membuat_dompet_simpanan_otomatis(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nomor_anggota' => 'K.10.201',
            'nama_lengkap' => 'Fajar Nugroho',
            'no_hp' => '+62 813-9999-0001',
            'password' => 'rahasia123',
            'peran' => User::PERAN_ANGGOTA,
            'wajib_simpanan' => '1',
            'tanggal_gabung' => '2026-11-24',
        ]);

        $anggotaBaru = User::query()->where('nomor_anggota', 'K.10.201')->first();
        $this->assertNotNull($anggotaBaru);
        $this->assertSame('081399990001', $anggotaBaru->no_hp);
        $this->assertSame(User::STATUS_AKTIF, $anggotaBaru->status);
        $this->assertTrue(Hash::check('rahasia123', $anggotaBaru->password));

        $response->assertRedirect(route('admin.anggota.show', $anggotaBaru));

        // Pastikan dompet simpanan_anggota (1-on-1) terbuat otomatis sesuai PengaturanKoperasi
        $simpanan = SimpananAnggota::query()->where('anggota_id', $anggotaBaru->id)->first();
        $this->assertNotNull($simpanan);
        $this->assertSame('100000.00', (string) $simpanan->nominal_simpanan_pokok);
        $this->assertSame(SimpananAnggota::STATUS_POKOK_BELUM_BAYAR, $simpanan->status_simpanan_pokok);
        $this->assertSame('0.00', (string) $simpanan->saldo_sw_awal);
        $this->assertSame('0.00', (string) $simpanan->total_simpanan_wajib);
        $this->assertSame('0.00', (string) $simpanan->total_simpanan_akhir);
    }

    public function test_validasi_unique_no_hp_mendeteksi_duplikat_setelah_normalisasi(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        // 081200000101 sudah dimiliki Siti Aminah; kirim format 628...
        $response = $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nomor_anggota' => 'K.10.202',
            'nama_lengkap' => 'Duplikat Nomor',
            'no_hp' => '6281200000101',
            'password' => 'rahasia123',
            'peran' => User::PERAN_ANGGOTA,
            'wajib_simpanan' => '1',
            'tanggal_gabung' => '2026-11-24',
        ]);

        $response->assertSessionHasErrors('no_hp');
        $this->assertDatabaseMissing('anggota', ['nomor_anggota' => 'K.10.202']);
    }

    public function test_admin_dapat_mengedit_profil_tanpa_bentrok_unique_dan_tanpa_mengubah_status(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.anggota.update', $siti), [
            'nomor_anggota' => 'K.10.101',
            'nama_lengkap' => 'Siti Aminah, S.Pd.',
            'no_hp' => '6281200000101',
            'peran' => User::PERAN_ANGGOTA,
            'wajib_simpanan' => '1',
            'tanggal_gabung' => '2021-03-15',
            'status' => User::STATUS_KELUAR, // Harus diabaikan oleh update profil
        ]);

        $response->assertRedirect(route('admin.anggota.show', $siti));

        $siti->refresh();
        $this->assertSame('Siti Aminah, S.Pd.', $siti->nama_lengkap);
        $this->assertSame('081200000101', $siti->no_hp);
        $this->assertSame(User::STATUS_AKTIF, $siti->status);
    }

    public function test_proses_soft_exit_dan_reaktivasi_mencatat_riwayat_status_secara_atomik(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        // 1. Soft-Exit: AKTIF -> KELUAR
        $this->actingAs($admin)
            ->patch(route('admin.anggota.status', $siti), [
                'status_baru' => User::STATUS_KELUAR,
                'alasan' => 'Mutasi kerja ke luar provinsi.',
            ])
            ->assertRedirect(route('admin.anggota.show', $siti));

        $siti->refresh();
        $this->assertSame(User::STATUS_KELUAR, $siti->status);
        $this->assertNotNull($siti->tanggal_keluar);

        $this->assertDatabaseHas('riwayat_status_anggota', [
            'anggota_id' => $siti->id,
            'status_sebelumnya' => User::STATUS_AKTIF,
            'status_baru' => User::STATUS_KELUAR,
            'alasan' => 'Mutasi kerja ke luar provinsi.',
            'disetujui_oleh' => $admin->id,
        ]);

        // 2. Re-aktivasi: KELUAR -> AKTIF
        $this->actingAs($admin)
            ->patch(route('admin.anggota.status', $siti), [
                'status_baru' => User::STATUS_AKTIF,
                'alasan' => 'Kembali bertugas dan bergabung aktif kembali.',
            ])
            ->assertRedirect(route('admin.anggota.show', $siti));

        $siti->refresh();
        $this->assertSame(User::STATUS_AKTIF, $siti->status);
        $this->assertNull($siti->tanggal_keluar);
        $this->assertSame(2, RiwayatStatusAnggota::query()->where('anggota_id', $siti->id)->count());
    }

    public function test_admin_dapat_mereset_password_anggota_dan_rute_hapus_permanen_tidak_tersedia(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.anggota.reset-password', $siti), [
                'password_baru' => 'sandibaru2026',
                'password_baru_confirmation' => 'sandibaru2026',
            ])
            ->assertRedirect(route('admin.anggota.show', $siti));

        $siti->refresh();
        $this->assertTrue(Hash::check('sandibaru2026', $siti->password));

        // Pastikan tidak ada rute DELETE /admin/anggota/{anggota}
        $this->actingAs($admin)
            ->delete('/admin/anggota/'.$siti->id)
            ->assertStatus(405);
    }

    public function test_last_admin_guard_menolak_penurunan_peran_admin_aktif_terakhir(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        // Tambahkan 1 Admin berstatus KELUAR untuk memastikan hanya Admin AKTIF yang dihitung
        User::query()->create([
            'nomor_anggota' => 'K.10.099',
            'nama_lengkap' => 'Admin Nonaktif',
            'no_hp' => '081200000099',
            'password' => Hash::make('password'),
            'peran' => User::PERAN_ADMIN,
            'status' => User::STATUS_KELUAR,
            'wajib_simpanan' => false,
            'tanggal_gabung' => '2020-01-01',
            'tanggal_keluar' => '2025-01-01',
        ]);

        // Halaman edit menampilkan peringatan & mengunci opsi ANGGOTA
        $this->actingAs($admin)
            ->get(route('admin.anggota.edit', $admin))
            ->assertOk()
            ->assertSee('Peran tidak dapat diubah menjadi Anggota karena ini adalah satu-satunya akun Admin yang aktif.')
            ->assertSee('disabled', false);

        // Request update peran menjadi ANGGOTA harus ditolak
        $response = $this->actingAs($admin)->put(route('admin.anggota.update', $admin), [
            'nomor_anggota' => $admin->nomor_anggota,
            'nama_lengkap' => $admin->nama_lengkap,
            'no_hp' => $admin->no_hp,
            'peran' => User::PERAN_ANGGOTA,
            'wajib_simpanan' => '1',
            'tanggal_gabung' => $admin->tanggal_gabung->toDateString(),
        ]);

        $response->assertSessionHasErrors([
            'peran' => 'Peran tidak dapat diubah menjadi Anggota karena ini adalah satu-satunya akun Admin yang aktif.',
        ]);

        $admin->refresh();
        $this->assertSame(User::PERAN_ADMIN, $admin->peran);
    }

    public function test_last_admin_guard_menolak_soft_exit_admin_aktif_terakhir(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        // Halaman detail menampilkan peringatan Last-Admin Guard di modal Soft-Exit
        $this->actingAs($admin)
            ->get(route('admin.anggota.show', $admin))
            ->assertOk()
            ->assertSee('Status keanggotaan tidak dapat dinonaktifkan (KELUAR) karena akun ini adalah satu-satunya Admin yang aktif.');

        $response = $this->actingAs($admin)->patch(route('admin.anggota.status', $admin), [
            'status_baru' => User::STATUS_KELUAR,
            'alasan' => 'Mencoba keluar sebagai satu-satunya admin.',
        ]);

        $response->assertSessionHasErrors([
            'status_baru' => 'Status keanggotaan tidak dapat dinonaktifkan (KELUAR) karena akun ini adalah satu-satunya Admin yang aktif.',
        ], null, 'ubahStatus');

        $admin->refresh();
        $this->assertSame(User::STATUS_AKTIF, $admin->status);
        $this->assertNull($admin->tanggal_keluar);
    }

    public function test_perubahan_peran_dan_soft_exit_admin_diizinkan_jika_masih_ada_admin_aktif_lain(): void
    {
        $adminUtama = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $adminKedua = User::query()->create([
            'nomor_anggota' => 'K.10.002',
            'nama_lengkap' => 'Pengurus Kedua',
            'no_hp' => '081200000002',
            'password' => Hash::make('password'),
            'peran' => User::PERAN_ADMIN,
            'status' => User::STATUS_AKTIF,
            'wajib_simpanan' => true,
            'tanggal_gabung' => '2021-01-10',
            'tanggal_keluar' => null,
        ]);

        // 1. Menurunkan peran admin kedua ke ANGGOTA diizinkan karena adminUtama masih aktif
        $this->actingAs($adminUtama)
            ->put(route('admin.anggota.update', $adminKedua), [
                'nomor_anggota' => $adminKedua->nomor_anggota,
                'nama_lengkap' => $adminKedua->nama_lengkap,
                'no_hp' => $adminKedua->no_hp,
                'peran' => User::PERAN_ANGGOTA,
                'wajib_simpanan' => '1',
                'tanggal_gabung' => $adminKedua->tanggal_gabung->toDateString(),
            ])
            ->assertRedirect(route('admin.anggota.show', $adminKedua));

        $adminKedua->refresh();
        $this->assertSame(User::PERAN_ANGGOTA, $adminKedua->peran);

        // 2. Promosikan kembali ke ADMIN lalu Soft-Exit adminKedua -> diizinkan
        $adminKedua->update(['peran' => User::PERAN_ADMIN]);

        $this->actingAs($adminUtama)
            ->patch(route('admin.anggota.status', $adminKedua), [
                'status_baru' => User::STATUS_KELUAR,
                'alasan' => 'Masa kepengurusan berakhir.',
            ])
            ->assertRedirect(route('admin.anggota.show', $adminKedua));

        $adminKedua->refresh();
        $this->assertSame(User::STATUS_KELUAR, $adminKedua->status);
    }
}
