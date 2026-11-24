<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\Pinjaman;
use App\Models\TagihanSimpananWajib;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnggotaPortalController extends Controller
{
    /**
     * Menampilkan ringkasan beranda portal anggota (100% read-only & scoped ke Auth::id()).
     */
    public function dashboard(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $simpanan = $user->simpanan()->first();

        $tagihanBelumLunas = $user->tagihanSimpananWajib()
            ->belumLunas()
            ->orderBy('periode')
            ->get();

        $jumlahBulanTunggakan = $tagihanBelumLunas->count();
        $totalTunggakanSw = $this->formatCents(
            $tagihanBelumLunas->sum(fn (TagihanSimpananWajib $tagihan): int => $this->toCents((string) $tagihan->sisa_tagihan)),
        );

        $pinjamanBerjalan = $user->pinjaman()
            ->berjalan()
            ->get();

        $jumlahPinjamanAktif = $pinjamanBerjalan->count();
        $totalSisaPinjaman = $this->formatCents(
            $pinjamanBerjalan->sum(fn (Pinjaman $pinjaman): int => $this->toCents((string) $pinjaman->sisa_tagihan)),
        );

        $pembayaranTerakhir = $user->pembayaran()
            ->with(['detailPembayaran', 'diinputOleh:id,nama_lengkap'])
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        return view('anggota.dashboard', [
            'anggota' => $user,
            'simpanan' => $simpanan,
            'jumlahBulanTunggakan' => $jumlahBulanTunggakan,
            'totalTunggakanSw' => $totalTunggakanSw,
            'jumlahPinjamanAktif' => $jumlahPinjamanAktif,
            'totalSisaPinjaman' => $totalSisaPinjaman,
            'pembayaranTerakhir' => $pembayaranTerakhir,
        ]);
    }

    /**
     * Menampilkan detail Simpanan Pokok, Simpanan Wajib, dan daftar tagihan bulanan milik anggota.
     */
    public function simpanan(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $simpanan = $user->simpanan()->first();

        $daftarTagihanSw = $user->tagihanSimpananWajib()
            ->orderByDesc('periode')
            ->get();

        $jumlahLunas = $daftarTagihanSw
            ->where('status', TagihanSimpananWajib::STATUS_LUNAS)
            ->count();

        $jumlahBelumLunas = $daftarTagihanSw
            ->whereIn('status', [TagihanSimpananWajib::STATUS_BELUM_BAYAR, TagihanSimpananWajib::STATUS_SEBAGIAN])
            ->count();

        $totalTunggakanSw = $this->formatCents(
            $daftarTagihanSw
                ->whereIn('status', [TagihanSimpananWajib::STATUS_BELUM_BAYAR, TagihanSimpananWajib::STATUS_SEBAGIAN])
                ->sum(fn (TagihanSimpananWajib $tagihan): int => $this->toCents((string) $tagihan->sisa_tagihan)),
        );

        return view('anggota.simpanan', [
            'anggota' => $user,
            'simpanan' => $simpanan,
            'daftarTagihanSw' => $daftarTagihanSw,
            'jumlahLunas' => $jumlahLunas,
            'jumlahBelumLunas' => $jumlahBelumLunas,
            'totalTunggakanSw' => $totalTunggakanSw,
        ]);
    }

    /**
     * Menampilkan daftar pinjaman (uang & barang) beserta jadwal cicilan milik anggota.
     */
    public function pinjaman(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $daftarPinjaman = $user->pinjaman()
            ->with(['cicilan' => fn ($query) => $query->orderBy('cicilan_ke')])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Pinjaman::STATUS_BERJALAN])
            ->orderByDesc('tanggal_cair')
            ->orderByDesc('created_at')
            ->get();

        $pinjamanBerjalan = $daftarPinjaman->where('status', Pinjaman::STATUS_BERJALAN);

        $totalSisaPinjaman = $this->formatCents(
            $pinjamanBerjalan->sum(fn (Pinjaman $pinjaman): int => $this->toCents((string) $pinjaman->sisa_tagihan)),
        );

        return view('anggota.pinjaman', [
            'anggota' => $user,
            'daftarPinjaman' => $daftarPinjaman,
            'jumlahPinjamanAktif' => $pinjamanBerjalan->count(),
            'totalSisaPinjaman' => $totalSisaPinjaman,
        ]);
    }

    /**
     * Menampilkan riwayat struk pembayaran (bulk payment) beserta rincian alokasi itemnya.
     */
    public function riwayat(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $daftarPembayaran = $user->pembayaran()
            ->with([
                'diinputOleh:id,nama_lengkap',
                'detailPembayaran.simpananAnggota',
                'detailPembayaran.tagihanSimpananWajib',
                'detailPembayaran.cicilanPinjaman.pinjaman',
            ])
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('anggota.riwayat', [
            'anggota' => $user,
            'daftarPembayaran' => $daftarPembayaran,
        ]);
    }

    private function toCents(string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
