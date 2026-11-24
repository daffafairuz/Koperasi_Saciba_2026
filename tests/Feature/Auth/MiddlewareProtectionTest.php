<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
    }

    public function test_anggota_diblokir_403_saat_mengakses_rute_admin(): void
    {
        $anggota = User::query()->where('no_hp', '081200000101')->firstOrFail();

        $response = $this->actingAs($anggota)->get(route('admin.dashboard'));

        $response->assertForbidden();
        $response->assertSee('Anda tidak memiliki hak akses ke halaman ini.');
    }

    public function test_admin_dapat_mengakses_dashboard_admin_maupun_portal_anggota_pribadi(): void
    {
        $admin = User::query()->where('no_hp', '081200000001')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin');

        $this->actingAs($admin)
            ->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSee('Portal Anggota')
            ->assertSee('Kembali ke Panel Admin');
    }

    public function test_anggota_aktif_dapat_mengakses_dashboard_anggota(): void
    {
        $anggota = User::query()->where('no_hp', '081200000101')->firstOrFail();

        $this->actingAs($anggota)
            ->get(route('anggota.dashboard'))
            ->assertOk()
            ->assertSee('Portal Anggota')
            ->assertDontSee('Kembali ke Panel Admin');
    }

    public function test_sesi_otomatis_diputus_ketika_status_anggota_berubah_menjadi_keluar(): void
    {
        $anggota = User::query()->where('no_hp', '081200000101')->firstOrFail();

        // Login saat status masih AKTIF
        $this->post(route('login.process'), [
            'no_hp' => $anggota->no_hp,
            'password' => 'password',
        ])->assertRedirect(route('anggota.dashboard'));

        $this->assertAuthenticated();

        // Admin mengubah status anggota menjadi KELUAR saat sesi sedang aktif
        $anggota->update([
            'status' => User::STATUS_KELUAR,
            'tanggal_keluar' => now()->toDateString(),
        ]);

        // Simulasikan HTTP request baru (agar SessionGuard memuat ulang user dari database, bukan cache in-memory PHPUnit)
        auth()->forgetUser();

        // Request berikutnya harus langsung memutus sesi dan redirect ke login
        $response = $this->get(route('anggota.dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'no_hp' => 'Sesi Anda berakhir karena status keanggotaan sudah tidak aktif.',
        ]);
        $this->assertGuest();
    }

    public function test_guest_diarahkan_ke_login_saat_mengakses_rute_terproteksi(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('anggota.dashboard'))->assertRedirect(route('login'));
    }
}
