<?php

namespace Tests\Feature;

use App\Models\CicilanPinjaman;
use App\Models\DetailPembayaran;
use App\Models\PembayaranAnggota;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\RiwayatStatusAnggota;
use App\Models\SimpananAnggota;
use App\Models\TagihanSimpananWajib;
use App\Models\TransaksiKasUsaha;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_mengisi_data_secara_konsisten_dan_idempoten(): void
    {
        $this->seed(DatabaseSeeder::class);

        $jumlahAwal = [
            'anggota' => User::query()->count(),
            'pengaturan_koperasi' => PengaturanKoperasi::query()->count(),
            'riwayat_status_anggota' => RiwayatStatusAnggota::query()->count(),
            'simpanan_anggota' => SimpananAnggota::query()->count(),
            'tagihan_simpanan_wajib' => TagihanSimpananWajib::query()->count(),
            'pinjaman' => Pinjaman::query()->count(),
            'cicilan_pinjaman' => CicilanPinjaman::query()->count(),
            'transaksi_kas_usaha' => TransaksiKasUsaha::query()->count(),
            'pembayaran_anggota' => PembayaranAnggota::query()->count(),
            'detail_pembayaran' => DetailPembayaran::query()->count(),
        ];

        // Uji idempotensi: jalankan ulang seeder, jumlah baris tidak boleh berubah
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($jumlahAwal['anggota'], User::query()->count());
        $this->assertSame($jumlahAwal['pengaturan_koperasi'], PengaturanKoperasi::query()->count());
        $this->assertSame($jumlahAwal['riwayat_status_anggota'], RiwayatStatusAnggota::query()->count());
        $this->assertSame($jumlahAwal['simpanan_anggota'], SimpananAnggota::query()->count());
        $this->assertSame($jumlahAwal['tagihan_simpanan_wajib'], TagihanSimpananWajib::query()->count());
        $this->assertSame($jumlahAwal['pinjaman'], Pinjaman::query()->count());
        $this->assertSame($jumlahAwal['cicilan_pinjaman'], CicilanPinjaman::query()->count());
        $this->assertSame($jumlahAwal['transaksi_kas_usaha'], TransaksiKasUsaha::query()->count());
        $this->assertSame($jumlahAwal['pembayaran_anggota'], PembayaranAnggota::query()->count());
        $this->assertSame($jumlahAwal['detail_pembayaran'], DetailPembayaran::query()->count());

        // 1. Relasi 1-on-1 anggota <-> simpanan_anggota
        $this->assertSame(User::query()->count(), SimpananAnggota::query()->count());

        // 2. Rekonsiliasi simpanan_anggota vs tagihan_simpanan_wajib
        foreach (SimpananAnggota::query()->get() as $simpanan) {
            $terbayarSwCents = TagihanSimpananWajib::query()
                ->where('anggota_id', $simpanan->anggota_id)
                ->get()
                ->sum(fn (TagihanSimpananWajib $tagihan): int => $this->toCents((string) $tagihan->nominal_terbayar));

            $expectedTotalSwCents = $this->toCents((string) $simpanan->saldo_sw_awal) + $terbayarSwCents;
            $this->assertSame($expectedTotalSwCents, $this->toCents((string) $simpanan->total_simpanan_wajib));

            $expectedAkhirCents = $expectedTotalSwCents + ($simpanan->status_simpanan_pokok === SimpananAnggota::STATUS_POKOK_LUNAS
                ? $this->toCents((string) $simpanan->nominal_simpanan_pokok)
                : 0);
            $this->assertSame($expectedAkhirCents, $this->toCents((string) $simpanan->total_simpanan_akhir));
        }

        // 3. Rekonsiliasi total SW terbayar vs detail_pembayaran (SIMPANAN_WAJIB)
        $totalTagihanSwTerbayarCents = TagihanSimpananWajib::query()
            ->get()
            ->sum(fn (TagihanSimpananWajib $t): int => $this->toCents((string) $t->nominal_terbayar));

        $totalDetailSwCents = DetailPembayaran::query()
            ->where('jenis_pembayaran', DetailPembayaran::JENIS_SIMPANAN_WAJIB)
            ->get()
            ->sum(fn (DetailPembayaran $d): int => $this->toCents((string) $d->nominal_bayar));

        $this->assertSame($totalTagihanSwTerbayarCents, $totalDetailSwCents);

        // 4. Rekonsiliasi pinjaman vs cicilan_pinjaman
        foreach (Pinjaman::query()->with('cicilan')->get() as $pinjaman) {
            $totalJadwalCents = $pinjaman->cicilan
                ->sum(fn (CicilanPinjaman $c): int => $this->toCents((string) $c->nominal_tagihan));

            $totalTerbayarCents = $pinjaman->cicilan
                ->sum(fn (CicilanPinjaman $c): int => $this->toCents((string) $c->nominal_terbayar));

            $this->assertSame($this->toCents((string) $pinjaman->total_tagihan), $totalJadwalCents);
            $this->assertSame(
                $this->toCents((string) $pinjaman->total_tagihan) - $totalTerbayarCents,
                $this->toCents((string) $pinjaman->sisa_tagihan),
            );
        }

        // 5. Rekonsiliasi total cicilan terbayar vs detail_pembayaran (CICILAN_PINJAMAN)
        $totalCicilanTerbayarCents = CicilanPinjaman::query()
            ->get()
            ->sum(fn (CicilanPinjaman $c): int => $this->toCents((string) $c->nominal_terbayar));

        $totalDetailCicilanCents = DetailPembayaran::query()
            ->where('jenis_pembayaran', DetailPembayaran::JENIS_CICILAN_PINJAMAN)
            ->get()
            ->sum(fn (DetailPembayaran $d): int => $this->toCents((string) $d->nominal_bayar));

        $this->assertSame($totalCicilanTerbayarCents, $totalDetailCicilanCents);

        // 6. Rekonsiliasi header pembayaran_anggota vs detail_pembayaran
        foreach (PembayaranAnggota::query()->with('detailPembayaran')->get() as $pembayaran) {
            $totalDetailCents = $pembayaran->detailPembayaran
                ->sum(fn (DetailPembayaran $d): int => $this->toCents((string) $d->nominal_bayar));

            $this->assertSame($this->toCents((string) $pembayaran->total_bayar), $totalDetailCents);
        }

        // 7. Skenario bisnis utama terpenuhi
        $dewi = User::query()->where('nomor_anggota', 'K.10.103')->firstOrFail();
        $this->assertSame(
            3,
            Pinjaman::query()
                ->where('anggota_id', $dewi->id)
                ->where('jenis_pinjaman', Pinjaman::JENIS_BARANG)
                ->where('status', Pinjaman::STATUS_BERJALAN)
                ->count(),
        );
        $this->assertSame(
            1,
            CicilanPinjaman::query()->where('status', CicilanPinjaman::STATUS_SEBAGIAN)->count(),
        );
    }

    private function toCents(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
