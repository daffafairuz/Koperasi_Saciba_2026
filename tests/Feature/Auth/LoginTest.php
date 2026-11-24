<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
    }

    public function test_halaman_login_dapat_diakses_dan_tidak_menampilkan_daftar_atau_lupa_password(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Koperasi Jasa Berkah Saciba');
        $response->assertDontSee('Daftar Akun');
        $response->assertDontSee('Lupa Password');
    }

    public function test_admin_aktif_berhasil_login_dan_diarahkan_ke_dashboard_admin(): void
    {
        $response = $this->post(route('login.process'), [
            'no_hp' => '081200000001',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertSame(User::PERAN_ADMIN, auth()->user()->peran);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin');
    }

    public function test_normalisasi_nomor_hp_mendukung_format_62_plus62_dan_awalan_8(): void
    {
        $variasiNomorHp = [
            '+62 812-0000-0101',
            '6281200000101',
            '81200000101',
            '0812 0000 0101',
        ];

        foreach ($variasiNomorHp as $inputHp) {
            $response = $this->post(route('login.process'), [
                'no_hp' => $inputHp,
                'password' => 'password',
            ]);

            $response->assertRedirect(route('anggota.dashboard'));
            $this->assertAuthenticated();
            $this->assertSame('081200000101', auth()->user()->no_hp);

            $this->post(route('logout'))->assertRedirect(route('login'));
            $this->assertGuest();
        }
    }

    public function test_anggota_berstatus_keluar_ditolak_login_dengan_pesan_khusus(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'no_hp' => '6281200000105',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'no_hp' => 'Akun keanggotaan Anda sudah tidak aktif (KELUAR). Hubungi pengurus koperasi untuk informasi lebih lanjut.',
        ]);
        $this->assertSame('6281200000105', session()->getOldInput('no_hp'));
        $this->assertGuest();
    }

    public function test_login_gagal_saat_kata_sandi_salah_dan_mempertahankan_input_no_hp(): void
    {
        $response = $this->from(route('login'))->post(route('login.process'), [
            'no_hp' => '081200000101',
            'password' => 'salah-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'no_hp' => 'Nomor HP atau kata sandi salah.',
        ]);
        $this->assertSame('081200000101', session()->getOldInput('no_hp'));
        $this->assertGuest();
    }

    public function test_user_yang_sudah_login_diarahkan_langsung_ke_dashboard_sesuai_peran(): void
    {
        $admin = User::query()->where('no_hp', '081200000001')->firstOrFail();
        $anggota = User::query()->where('no_hp', '081200000101')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('login'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($anggota)
            ->get(route('login'))
            ->assertRedirect(route('anggota.dashboard'));
    }

    public function test_logout_mengakhiri_sesi_dan_mengarahkan_ke_login(): void
    {
        $anggota = User::query()->where('no_hp', '081200000101')->firstOrFail();

        $response = $this->actingAs($anggota)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
