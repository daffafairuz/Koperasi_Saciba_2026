<?php

namespace Tests\Feature\Admin;

use App\Models\CicilanPinjaman;
use App\Models\DetailPembayaran;
use App\Models\PembayaranAnggota;
use App\Models\Pinjaman;
use App\Models\SimpananAnggota;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_endpoint_json_kewajiban_anggota_mengembalikan_sp_tunggakan_advance_dan_cicilan(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $hendra = User::query()->where('nomor_anggota', 'K.10.104')->firstOrFail();

        $response = $this->actingAs($admin)->getJson(route('admin.pembayaran.kewajiban', $hendra));

        $response->assertOk();
        $response->assertJsonPath('anggota.nomor_anggota', 'K.10.104');
        $response->assertJsonPath('simpanan_pokok.bisa_dibayar', false);
        $response->assertJsonPath('advance_sw.periode_berikutnya', '202612');
        $response->assertJsonPath('advance_sw.nominal_sw_aktif', 50000);
        $response->assertJsonCount(6, 'tagihan_sw'); // Jun-Nov 2026 belum bayar
        $response->assertJsonCount(9, 'cicilan_pinjaman'); // 6 cicilan uang (termasuk ke-5 sebagian) + 3 cicilan kulkas
    }

    public function test_bulk_payment_gabungan_sw_advance_dan_cicilan_sebagian_menyinkronkan_seluruh_tabel_dan_portal_anggota(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $hendra = User::query()->where('nomor_anggota', 'K.10.104')->firstOrFail();

        $tagihanJun = TagihanSimpananWajib::query()
            ->where('anggota_id', $hendra->id)
            ->where('periode', '202606')
            ->firstOrFail();

        $pinjamanUang = Pinjaman::query()->where('nomor_pinjaman', 'PJM-MIG-2026-001')->firstOrFail();
        $cicilanKe5 = CicilanPinjaman::query()
            ->where('pinjaman_id', $pinjamanUang->id)
            ->where('cicilan_ke', 5)
            ->firstOrFail(); // Sisa 250.000 (SEBAGIAN)
        $cicilanKe6 = CicilanPinjaman::query()
            ->where('pinjaman_id', $pinjamanUang->id)
            ->where('cicilan_ke', 6)
            ->firstOrFail(); // Tagihan 500.000 -> kita bayar sebagian Rp 100.000

        $response = $this->actingAs($admin)->post(route('admin.pembayaran.store'), [
            'anggota_id' => $hendra->id,
            'tanggal_bayar' => '2026-11-24',
            'metode_bayar' => PembayaranAnggota::METODE_TRANSFER,
            'catatan' => 'Pelunasan SW Juni + Advance Des + pelunasan sisa cicilan 5 & sebagian cicilan 6.',
            'items' => [
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_WAJIB,
                    'tagihan_sw_id' => $tagihanJun->id,
                    'nominal_bayar' => 50000,
                ],
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_WAJIB,
                    'periode_advance' => '202612',
                    'nominal_bayar' => 50000,
                ],
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                    'cicilan_pinjaman_id' => $cicilanKe5->id,
                    'nominal_bayar' => 250000,
                ],
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                    'cicilan_pinjaman_id' => $cicilanKe6->id,
                    'nominal_bayar' => 100000,
                ],
            ],
        ]);

        $pembayaran = PembayaranAnggota::query()->where('nomor_transaksi', 'TRX-20261124-0001')->first();
        $this->assertNotNull($pembayaran);
        $this->assertSame('450000.00', (string) $pembayaran->total_bayar);
        $this->assertSame(4, $pembayaran->detailPembayaran()->count());

        $response->assertRedirect(route('admin.pembayaran.show', $pembayaran));

        // 1. Cek Tagihan SW Juni & Advance Desember (otomatis dibuat & LUNAS)
        $tagihanJun->refresh();
        $this->assertSame(TagihanSimpananWajib::STATUS_LUNAS, $tagihanJun->status);

        $tagihanDes = TagihanSimpananWajib::query()
            ->where('anggota_id', $hendra->id)
            ->where('periode', '202612')
            ->first();
        $this->assertNotNull($tagihanDes);
        $this->assertSame(TagihanSimpananWajib::STATUS_LUNAS, $tagihanDes->status);

        // 2. Cek Sinkronisasi SimpananAnggota (sebelumnya 8.250.000 + 250.000 = 8.500.000; tambah 100.000 = 8.600.000)
        $simpananHendra = SimpananAnggota::query()->where('anggota_id', $hendra->id)->firstOrFail();
        $this->assertSame('8600000.00', (string) $simpananHendra->total_simpanan_wajib);
        $this->assertSame('202612', $simpananHendra->periode_sw_terakhir);

        // 3. Cek Cicilan ke-5 LUNAS, Cicilan ke-6 SEBAGIAN, dan sisa_tagihan Pinjaman berkurang Rp 350.000
        $cicilanKe5->refresh();
        $cicilanKe6->refresh();
        $pinjamanUang->refresh();

        $this->assertSame(CicilanPinjaman::STATUS_LUNAS, $cicilanKe5->status);
        $this->assertSame(CicilanPinjaman::STATUS_SEBAGIAN, $cicilanKe6->status);
        $this->assertSame('100000.00', (string) $cicilanKe6->nominal_terbayar);
        $this->assertSame('2400000.00', (string) $pinjamanUang->sisa_tagihan); // 2.750.000 - 350.000

        // 4. Cek Dashboard Admin kas bertambah Rp 450.000 & Portal Anggota Hendra langsung menampilkan struk baru
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertViewHas('totalKasKoperasi', '253895714.28');

        $this->actingAs($hendra)
            ->get(route('anggota.riwayat'))
            ->assertOk()
            ->assertSee('TRX-20261124-0001')
            ->assertSee('Desember 2026');
    }

    public function test_pelunasan_simpanan_pokok_anggota_baru_dan_pelunasan_penuh_pinjaman_mengubah_status_lunas(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();

        // Daftar anggota baru (Simpanan Pokok BELUM_BAYAR Rp 100.000)
        $this->actingAs($admin)->post(route('admin.anggota.store'), [
            'nomor_anggota' => 'K.10.401',
            'nama_lengkap' => 'Nadia Putri',
            'no_hp' => '081300000401',
            'password' => 'rahasia123',
            'peran' => User::PERAN_ANGGOTA,
            'wajib_simpanan' => '1',
            'tanggal_gabung' => '2026-11-24',
        ]);

        $nadia = User::query()->where('nomor_anggota', 'K.10.401')->firstOrFail();
        $simpananNadia = SimpananAnggota::query()->where('anggota_id', $nadia->id)->firstOrFail();

        // Cairkan pinjaman 1 bulan Rp 100.000 (0%)
        $this->actingAs($admin)->post(route('admin.pinjaman.store'), [
            'anggota_id' => $nadia->id,
            'jenis_pinjaman' => Pinjaman::JENIS_UANG,
            'pokok_pinjaman' => 100000,
            'tenor_bulan' => 1,
            'tanggal_cair' => '2026-11-24',
        ]);

        $pinjamanNadia = Pinjaman::query()->where('anggota_id', $nadia->id)->firstOrFail();
        $cicilanTunggal = $pinjamanNadia->cicilan()->firstOrFail();

        // Bayar Simpanan Pokok + Cicilan ke-1 sekaligus -> keduanya LUNAS!
        $this->actingAs($admin)->post(route('admin.pembayaran.store'), [
            'anggota_id' => $nadia->id,
            'tanggal_bayar' => '2026-11-24',
            'metode_bayar' => PembayaranAnggota::METODE_TUNAI,
            'items' => [
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_POKOK,
                    'simpanan_anggota_id' => $simpananNadia->id,
                    'nominal_bayar' => 100000,
                ],
                [
                    'jenis_pembayaran' => DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                    'cicilan_pinjaman_id' => $cicilanTunggal->id,
                    'nominal_bayar' => 100000,
                ],
            ],
        ])->assertRedirect();

        $simpananNadia->refresh();
        $pinjamanNadia->refresh();

        $this->assertSame(SimpananAnggota::STATUS_POKOK_LUNAS, $simpananNadia->status_simpanan_pokok);
        $this->assertSame('100000.00', (string) $simpananNadia->total_simpanan_akhir);
        $this->assertSame('0.00', (string) $pinjamanNadia->sisa_tagihan);
        $this->assertSame(Pinjaman::STATUS_LUNAS, $pinjamanNadia->status);
    }

    public function test_pembayaran_ditolak_jika_nominal_melebihi_sisa_tagihan_dan_anggota_biasa_ditolak_403(): void
    {
        $admin = User::query()->where('nomor_anggota', 'K.10.001')->firstOrFail();
        $siti = User::query()->where('nomor_anggota', 'K.10.101')->firstOrFail();
        $tagihanJul = TagihanSimpananWajib::query()
            ->where('anggota_id', $siti->id)
            ->where('periode', '202607')
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.pembayaran.store'), [
                'anggota_id' => $siti->id,
                'tanggal_bayar' => '2026-11-24',
                'metode_bayar' => PembayaranAnggota::METODE_TUNAI,
                'items' => [
                    [
                        'jenis_pembayaran' => DetailPembayaran::JENIS_SIMPANAN_WAJIB,
                        'tagihan_sw_id' => $tagihanJul->id,
                        'nominal_bayar' => 75000, // Melebihi sisa Rp 50.000
                    ],
                ],
            ])
            ->assertSessionHasErrors('items.0.nominal_bayar');

        $this->actingAs($siti)->get(route('admin.pembayaran.index'))->assertForbidden();
        $this->actingAs($siti)->get(route('admin.pembayaran.kewajiban', $siti))->assertForbidden();
    }
}
