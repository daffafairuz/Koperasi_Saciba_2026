<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PembayaranAnggota;
use App\Models\Pinjaman;
use App\Models\RiwayatStatusAnggota;
use App\Models\SimpananAnggota;
use App\Models\User;
use App\Services\KasKoperasiService;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly KasKoperasiService $kasKoperasiService,
    ) {}

    /**
     * Menampilkan dashboard utama Admin beserta ringkasan metrik keuangan real-time.
     */
    public function index(): View
    {
        // 1. Total Kas Koperasi (sumber perhitungan tunggal via KasKoperasiService)
        $ringkasanKas = $this->kasKoperasiService->ringkasanKas();

        // 2. Total Dana Simpanan Anggota (SP LUNAS + total SW seluruh anggota)
        $totalSimpananPokokLunasCents = $this->kasKoperasiService->toCents(
            SimpananAnggota::query()
                ->where('status_simpanan_pokok', SimpananAnggota::STATUS_POKOK_LUNAS)
                ->sum('nominal_simpanan_pokok'),
        );
        $totalSimpananWajibCents = $this->kasKoperasiService->toCents(
            SimpananAnggota::query()->sum('total_simpanan_wajib'),
        );
        $totalDanaSimpananCents = $totalSimpananPokokLunasCents + $totalSimpananWajibCents;

        // 3. Total Piutang Pinjaman Berjalan
        $totalPiutangPinjamanCents = $this->kasKoperasiService->toCents(
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
            'totalKasKoperasi' => $ringkasanKas['total_kas_koperasi'],
            'totalKasMasuk' => $ringkasanKas['total_kas_masuk'],
            'totalKasKeluar' => $ringkasanKas['total_kas_keluar'],
            'totalDanaSimpanan' => $this->kasKoperasiService->formatCents($totalDanaSimpananCents),
            'totalSimpananPokokLunas' => $this->kasKoperasiService->formatCents($totalSimpananPokokLunasCents),
            'totalSimpananWajib' => $this->kasKoperasiService->formatCents($totalSimpananWajibCents),
            'totalPiutangPinjaman' => $this->kasKoperasiService->formatCents($totalPiutangPinjamanCents),
            'jumlahPinjamanBerjalan' => $jumlahPinjamanBerjalan,
            'jumlahAnggotaAktif' => $jumlahAnggotaAktif,
            'jumlahAnggotaKeluar' => $jumlahAnggotaKeluar,
            'totalAnggota' => $jumlahAnggotaAktif + $jumlahAnggotaKeluar,
            'topPinjamanAktif' => $topPinjamanAktif,
            'pembayaranTerbaru' => $pembayaranTerbaru,
            'anggotaKeluarTerbaru' => $anggotaKeluarTerbaru,
        ]);
    }
}
