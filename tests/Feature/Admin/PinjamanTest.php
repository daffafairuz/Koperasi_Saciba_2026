<?php

namespace Tests\Feature\Admin;

use App\Models\CicilanPinjaman;
use App\Models\Pinjaman;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinjamanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_dapat_melihat_dan_memfilter_daftar_pinjaman(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.pinjaman.index'))
            ->assertOk()
            ->assertSee('PJM-2026-002')
            ->assertSee('Laptop Lenovo ThinkPad');

        $this->actingAs($admin)
            ->get(route('admin.pinjaman.index', ['jenis_pinjaman' => Pinjaman::JENIS_UANG, 'q' => 'hendra']))
            ->assertOk()
            ->assertSee('PJM-MIG-2026-001')
            ->assertDontSee('PJM-2026-002');
    }

    public function test_pencairan_pinjaman_barang_tenor_ganjil_membuat_jadwal_cicilan_presisi_dan_memotong_kas(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $dewi = User::query()->where('nomor_anggota', 'K.10.103')->firstOrFail();

        // Dewi sudah punya 3 pinjaman barang aktif; tambahkan ke-4 (unlimited pinjaman barang) dengan tenor ganjil 7 bulan
        $response = $this->actingAs($admin)->post(route('admin.pinjaman.store'), [
            'anggota_id' => $dewi->id,
            'jenis_pinjaman' => Pinjaman::JENIS_BARANG,
            'nama_barang' => 'TV LED LG 43 Inch',
            'pokok_pinjaman' => 3000000,
            'tenor_bulan' => 7,
            'tanggal_cair' => '2026-11-24',
        ]);

        $pinjaman = Pinjaman::query()->where('nomor_pinjaman', 'PJM-20261124-0001')->first();
        $this->assertNotNull($pinjaman);
        $this->assertFalse($pinjaman->is_migrasi);
        $this->assertSame($admin->id, $pinjaman->dicairkan_oleh);
        $this->assertSame('15.00', (string) $pinjaman->persen_margin);
        $this->assertSame('3450000.00', (string) $pinjaman->total_tagihan);
        $this->assertSame('3450000.00', (string) $pinjaman->sisa_tagihan);

        $response->assertRedirect(route('admin.pinjaman.show', $pinjaman));

        // Pastikan 7 cicilan terbuat & jumlah total cicilan persis sama dengan 3.450.000,00 (492.857,14 * 6 + 492.857,16)
        $cicilanList = CicilanPinjaman::query()
            ->where('pinjaman_id', $pinjaman->id)
            ->orderBy('cicilan_ke')
            ->get();

        $this->assertCount(7, $cicilanList);
        $this->assertSame('2026-12-24', $cicilanList->first()->jatuh_tempo->toDateString());
        $this->assertSame('2027-06-24', $cicilanList->last()->jatuh_tempo->toDateString());

        $sumCicilanCents = $cicilanList->sum(fn (CicilanPinjaman $c): int => (int) round(((float) $c->nominal_tagihan) * 100));
        $this->assertSame(345000000, $sumCicilanCents);

        // Saldo kas utama berkurang sebesar pokok_pinjaman Rp 3.000.000 (bukan total_tagihan): 253.445.714,28 -> 250.445.714,28
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '250445714.28');
    }

    public function test_pencairan_pinjaman_uang_memakai_margin_0_persen_syariah(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.pinjaman.store'), [
            'anggota_id' => $siti->id,
            'jenis_pinjaman' => Pinjaman::JENIS_UANG,
            'pokok_pinjaman' => 1200000,
            'tenor_bulan' => 6,
            'tanggal_cair' => '2026-11-24',
        ]);

        $pinjaman = Pinjaman::query()->where('anggota_id', $siti->id)->firstOrFail();
        $this->assertSame('0.00', (string) $pinjaman->persen_margin);
        $this->assertSame('1200000.00', (string) $pinjaman->total_tagihan);
        $this->assertSame('200000.00', (string) $pinjaman->cicilan_per_bulan);
        $this->assertNull($pinjaman->nama_barang);
    }

    public function test_pencairan_ditolak_jika_anggota_keluar_atau_melebihi_kas_koperasi(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $rinaKeluar = User::query()->where('nomor_anggota', 'K.10.105')->firstOrFail();
        $sitiAktif = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        // 1. Anggota KELUAR ditolak
        $this->actingAs($admin)
            ->post(route('admin.pinjaman.store'), [
                'anggota_id' => $rinaKeluar->id,
                'jenis_pinjaman' => Pinjaman::JENIS_UANG,
                'pokok_pinjaman' => 500000,
                'tenor_bulan' => 5,
                'tanggal_cair' => '2026-11-24',
            ])
            ->assertSessionHasErrors('anggota_id');

        // 2. Pokok melebihi saldo kas Rp 253.445.714,28 ditolak
        $this->actingAs($admin)
            ->post(route('admin.pinjaman.store'), [
                'anggota_id' => $sitiAktif->id,
                'jenis_pinjaman' => Pinjaman::JENIS_UANG,
                'pokok_pinjaman' => 300000000,
                'tenor_bulan' => 12,
                'tanggal_cair' => '2026-11-24',
            ])
            ->assertSessionHasErrors('pokok_pinjaman');

        // 3. Pinjaman barang tanpa nama_barang ditolak
        $this->actingAs($admin)
            ->post(route('admin.pinjaman.store'), [
                'anggota_id' => $sitiAktif->id,
                'jenis_pinjaman' => Pinjaman::JENIS_BARANG,
                'pokok_pinjaman' => 500000,
                'tenor_bulan' => 5,
                'tanggal_cair' => '2026-11-24',
            ])
            ->assertSessionHasErrors('nama_barang');
    }

    public function test_halaman_create_memuat_kalkulator_alpine_dan_anggota_biasa_ditolak_403(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $anggota = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.pinjaman.create'))
            ->assertOk()
            ->assertSee('Kalkulator Cicilan')
            ->assertSee('Rp 253.445.714');

        $this->actingAs($anggota)->get(route('admin.pinjaman.index'))->assertForbidden();
        $this->actingAs($anggota)->get(route('admin.pinjaman.create'))->assertForbidden();
    }
}
