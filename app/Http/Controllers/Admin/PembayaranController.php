<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetailPembayaran;
use App\Models\PembayaranAnggota;
use App\Models\User;
use App\Services\PembayaranService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function __construct(
        private readonly PembayaranService $pembayaranService,
    ) {}

    /**
     * Menampilkan riwayat seluruh struk pembayaran anggota.
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));
        $metodeFilter = strtoupper((string) $request->query('metode_bayar', 'SEMUA'));

        if (! in_array($metodeFilter, ['SEMUA', PembayaranAnggota::METODE_TUNAI, PembayaranAnggota::METODE_TRANSFER], true)) {
            $metodeFilter = 'SEMUA';
        }

        $query = PembayaranAnggota::query()
            ->with([
                'anggota:id,nomor_anggota,nama_lengkap',
                'diinputOleh:id,nama_lengkap',
            ])
            ->withCount('detailPembayaran');

        if ($keyword !== '') {
            $likeKeyword = '%'.mb_strtolower($keyword).'%';
            $query->where(function ($subQuery) use ($likeKeyword): void {
                $subQuery
                    ->whereRaw('LOWER(nomor_transaksi) LIKE ?', [$likeKeyword])
                    ->orWhereHas('anggota', function ($anggotaQuery) use ($likeKeyword): void {
                        $anggotaQuery
                            ->whereRaw('LOWER(nama_lengkap) LIKE ?', [$likeKeyword])
                            ->orWhereRaw('LOWER(nomor_anggota) LIKE ?', [$likeKeyword]);
                    });
            });
        }

        if ($metodeFilter !== 'SEMUA') {
            $query->where('metode_bayar', $metodeFilter);
        }

        $daftarPembayaran = $query
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pembayaran.index', [
            'daftarPembayaran' => $daftarPembayaran,
            'keyword' => $keyword,
            'metodeFilter' => $metodeFilter,
        ]);
    }

    /**
     * Menampilkan form interaktif Input Pembayaran Gabungan (Bulk Payment).
     */
    public function create(Request $request): View
    {
        $daftarAnggota = User::query()
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [User::STATUS_AKTIF])
            ->orderBy('nomor_anggota')
            ->get(['id', 'nomor_anggota', 'nama_lengkap', 'status']);

        return view('admin.pembayaran.create', [
            'daftarAnggota' => $daftarAnggota,
            'selectedAnggotaId' => (string) $request->query('anggota_id', old('anggota_id', '')),
        ]);
    }

    /**
     * Endpoint JSON kewajiban anggota untuk Alpine.js di halaman Input Pembayaran.
     */
    public function getKewajibanAnggota(User $anggota): JsonResponse
    {
        return response()->json(
            $this->pembayaranService->getKewajibanAnggota($anggota),
        );
    }

    /**
     * Memvalidasi dan memproses transaksi Bulk Payment melalui PembayaranService secara atomik.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'anggota_id' => ['required', 'exists:anggota,id'],
            'tanggal_bayar' => ['required', 'date'],
            'metode_bayar' => ['required', 'string', Rule::in([PembayaranAnggota::METODE_TUNAI, PembayaranAnggota::METODE_TRANSFER])],
            'catatan' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.jenis_pembayaran' => [
                'required',
                'string',
                Rule::in([
                    DetailPembayaran::JENIS_SIMPANAN_POKOK,
                    DetailPembayaran::JENIS_SIMPANAN_WAJIB,
                    DetailPembayaran::JENIS_CICILAN_PINJAMAN,
                ]),
            ],
            'items.*.simpanan_anggota_id' => ['nullable', 'uuid'],
            'items.*.tagihan_sw_id' => ['nullable', 'uuid'],
            'items.*.periode_advance' => ['nullable', 'string', 'size:6'],
            'items.*.cicilan_pinjaman_id' => ['nullable', 'uuid'],
            'items.*.nominal_bayar' => ['required', 'numeric', 'gt:0'],
        ], [
            'anggota_id.required' => 'Anggota wajib dipilih.',
            'anggota_id.exists' => 'Data anggota tidak ditemukan.',
            'tanggal_bayar.required' => 'Tanggal pembayaran wajib diisi.',
            'metode_bayar.in' => 'Metode bayar harus TUNAI atau TRANSFER.',
            'items.required' => 'Pilih minimal satu item kewajiban yang akan dibayar.',
            'items.min' => 'Pilih minimal satu item kewajiban yang akan dibayar.',
            'items.*.nominal_bayar.gt' => 'Nominal bayar setiap item harus lebih dari Rp 0.',
        ]);

        /** @var User $admin */
        $admin = $request->user();

        $pembayaran = $this->pembayaranService->prosesPembayaran($validated, $admin);

        return redirect()
            ->route('admin.pembayaran.show', $pembayaran)
            ->with('success', "Struk pembayaran {$pembayaran->nomor_transaksi} sebesar ".format_rupiah($pembayaran->total_bayar).' berhasil dicatat.');
    }

    /**
     * Menampilkan halaman detail Struk Pembayaran (print-friendly).
     */
    public function show(PembayaranAnggota $pembayaran): View
    {
        $pembayaran->load([
            'anggota:id,nomor_anggota,nama_lengkap,no_hp',
            'diinputOleh:id,nama_lengkap,nomor_anggota',
            'detailPembayaran.simpananAnggota',
            'detailPembayaran.tagihanSimpananWajib',
            'detailPembayaran.cicilanPinjaman.pinjaman',
        ]);

        return view('admin.pembayaran.show', [
            'pembayaran' => $pembayaran,
        ]);
    }
}
