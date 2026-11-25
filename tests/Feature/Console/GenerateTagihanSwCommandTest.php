<?php

namespace Tests\Feature\Console;

use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GenerateTagihanSwCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_command_menerbitkan_tagihan_hanya_untuk_anggota_aktif_wajib_sw_dan_melewati_advance_payment(): void
    {
        // Tambah 1 anggota baru yang baru bergabung di masa depan (Januari 2027) -> tidak boleh dapat tagihan 202612
        $anggotaMasaDepan = User::query()->create([
            'nomor_anggota' => 'K.10.901',
            'nama_lengkap' => 'Anggota Masa Depan',
            'no_hp' => '081900000901',
            'password' => Hash::make('password'),
            'peran' => User::PERAN_ANGGOTA,
            'status' => User::STATUS_AKTIF,
            'wajib_simpanan' => true,
            'tanggal_gabung' => '2027-01-10',
        ]);

        // Tambah 1 anggota aktif dengan wajib_simpanan = false -> tidak boleh dapat tagihan
        $anggotaNonWajib = User::query()->create([
            'nomor_anggota' => 'K.10.902',
            'nama_lengkap' => 'Anggota Non Wajib SW',
            'no_hp' => '081900000902',
            'password' => Hash::make('password'),
            'peran' => User::PERAN_ANGGOTA,
            'status' => User::STATUS_AKTIF,
            'wajib_simpanan' => false,
            'tanggal_gabung' => '2025-01-10',
        ]);

        // Periode 202612: 5 anggota aktif dari seeder layak, tetapi K.10.102 (Ahmad) sudah bayar di muka (LUNAS)
        // Jadi harus 4 baru diterbitkan dan 1 dilewati!
        $this->artisan('koperasi:generate-tagihan-sw', ['--periode' => '202612'])
            ->expectsOutputToContain('Tagihan baru diterbitkan  : 4 tagihan')
            ->expectsOutputToContain('Dilewati (sudah ada/lunas): 1 anggota')
            ->assertSuccessful();

        $this->assertSame(5, TagihanSimpananWajib::query()->where('periode', '202612')->count());

        // Ahmad Fauzi (advance payment) tetap LUNAS
        $ahmad = User::query()->where('nomor_anggota', 'K.10.102')->firstOrFail();
        $tagihanAhmad = TagihanSimpananWajib::query()
            ->where('anggota_id', $ahmad->id)
            ->where('periode', '202612')
            ->firstOrFail();
        $this->assertSame(TagihanSimpananWajib::STATUS_LUNAS, $tagihanAhmad->status);

        // Anggota KELUAR, anggota gabung 2027, dan wajib_simpanan=false tidak mendapat tagihan 202612
        $rinaKeluar = User::query()->where('nomor_anggota', 'K.10.105')->firstOrFail();
        $this->assertDatabaseMissing('tagihan_simpanan_wajib', ['anggota_id' => $rinaKeluar->id, 'periode' => '202612']);
        $this->assertDatabaseMissing('tagihan_simpanan_wajib', ['anggota_id' => $anggotaMasaDepan->id, 'periode' => '202612']);
        $this->assertDatabaseMissing('tagihan_simpanan_wajib', ['anggota_id' => $anggotaNonWajib->id, 'periode' => '202612']);

        // Jalankan kedua kali -> 100% idempoten (0 baru, 5 dilewati)
        $this->artisan('koperasi:generate-tagihan-sw', ['--periode' => '202612'])
            ->expectsOutputToContain('Tagihan baru diterbitkan  : 0 tagihan')
            ->expectsOutputToContain('Dilewati (sudah ada/lunas): 5 anggota')
            ->assertSuccessful();
    }

    public function test_command_menolak_format_periode_tidak_valid(): void
    {
        $this->artisan('koperasi:generate-tagihan-sw', ['--periode' => '202613'])
            ->expectsOutputToContain('Format periode tidak valid')
            ->assertExitCode(2);
    }

    public function test_admin_dapat_memicu_penerbitan_tagihan_sw_secara_manual_dari_halaman_pengaturan(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $anggota = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.pengaturan.generate-sw'))
            ->assertRedirect(route('admin.pengaturan.index'))
            ->assertSessionHas('success');

        $this->actingAs($anggota)
            ->post(route('admin.pengaturan.generate-sw'))
            ->assertForbidden();
    }
}
