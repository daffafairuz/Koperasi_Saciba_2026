<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PembayaranAnggota;
use App\Models\Pinjaman;
use App\Models\RiwayatStatusAnggota;
use App\Models\SimpananAnggota;
use App\Models\TransaksiKasUsaha;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama Admin beserta ringkasan metrik keuangan real-time.
     */
    public function index(): View
    {
        // 1. Total Kas Koperasi (100% portabel SQLite & PostgreSQL, presisi integer-cent)
        $totalPembayaranAnggotaCents = $this->toCents(PembayaranAnggota::query()->sum('total_bayar'));
        $totalKasMasukCents = $this->toCents(
            TransaksiKasUsaha::query()
                ->where('jenis_arus', TransaksiKasUsaha::ARUS_MASUK)
                ->sum('nominal'),
        );
        $totalKasKeluarCents = $this->toCents(
            TransaksiKasUsaha::query()
                ->where('jenis_arus', TransaksiKasUsaha::ARUS_KELUAR)
                ->sum('nominal'),
        );
        $totalPencairanPinjamanBaruCents = $this->toCents(
            Pinjaman::query()
                ->where('is_migrasi', false)
                ->sum('pokok_pinjaman'),
        );

        $totalKasKoperasiCents = $totalPembayaranAnggotaCents
            + $totalKasMasukCents
            - $totalKasKeluarCents
            - $totalPencairanPinjamanBaruCents;

        // 2. Total Dana Simpanan Anggota (SP LUNAS + total SW seluruh anggota)
        $totalSimpananPokokLunasCents = $this->toCents(
            SimpananAnggota::query()
                ->where('status_simpanan_pokok', SimpananAnggota::STATUS_POKOK_LUNAS)
                ->sum('nominal_simpanan_pokok'),
        );
        $totalSimpananWajibCents = $this->toCents(
            SimpananAnggota::query()->sum('total_simpanan_wajib'),
        );
        $totalDanaSimpananCents = $totalSimpananPokokLunasCents + $totalSimpananWajibCents;

        // 3. Total Piutang Pinjaman Berjalan
        $totalPiutangPinjamanCents = $this->toCents(
            Pinjaman::query()->berjalan()->sum('sisa_tagihan'),
        );
        $jumlahPinjamanBerjalan = Pinjaman::query()->berjalan()->count();

        // 4. Statistik Keanggotaan
        $jumlahAnggotaAktif = User::query()->where('status', User::STATUS_AKTIF)->count();
        $jumlahAnggotaKeluar = User::query()->where('status', User::STATUS_KELUAR)->count();

        // 5. Data pendukung dashboard (menggunakan eager loading untuk mencegah N+1 query)
        $topPinjamanAktif = Pinjaman::query()
            ->with('anggota:id,nomor_anggota,nama_lengkap')
            ->berjalan()
            ->orderByDesc('sisa_tagihan')
            ->orderByDesc('tanggal_cair')
            ->limit(5)
            ->get();

        $pembayaranTerbaru = PembayaranAnggota::query()
            ->with([
                'anggota:id,nomor_anggota,nama_lengkap',
                'diinputOleh:id,nama_lengkap',
                'detailPembayaran',
            ])
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $anggotaKeluarTerbaru = RiwayatStatusAnggota::query()
            ->with([
                'anggota:id,nomor_anggota,nama_lengkap,tanggal_keluar',
                'disetujuiOleh:id,nama_lengkap',
            ])
            ->where('status_baru', RiwayatStatusAnggota::STATUS_KELUAR)
            ->orderByDesc('tanggal_disetujui')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'totalKasKoperasi' => $this->formatCents($totalKasKoperasiCents),
            'totalKasMasuk' => $this->formatCents($totalPembayaranAnggotaCents + $totalKasMasukCents),
            'totalKasKeluar' => $this->formatCents($totalKasKeluarCents + $totalPencairanPinjamanBaruCents),
            'totalDanaSimpanan' => $this->formatCents($totalDanaSimpananCents),
            'totalSimpananPokokLunas' => $this->formatCents($totalSimpananPokokLunasCents),
            'totalSimpananWajib' => $this->formatCents($totalSimpananWajibCents),
            'totalPiutangPinjaman' => $this->formatCents($totalPiutangPinjamanCents),
            'jumlahPinjamanBerjalan' => $jumlahPinjamanBerjalan,
            'jumlahAnggotaAktif' => $jumlahAnggotaAktif,
            'jumlahAnggotaKeluar' => $jumlahAnggotaKeluar,
            'totalAnggota' => $jumlahAnggotaAktif + $jumlahAnggotaKeluar,
            'topPinjamanAktif' => $topPinjamanAktif,
            'pembayaranTerbaru' => $pembayaranTerbaru,
            'anggotaKeluarTerbaru' => $anggotaKeluarTerbaru,
        ]);
    }

    private function toCents(int|float|string|null $amount): int
    {
        return (int) round(((float) ($amount ?? 0)) * 100);
    }

    private function formatCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
