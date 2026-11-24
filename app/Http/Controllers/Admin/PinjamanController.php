<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CicilanPinjaman;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\User;
use App\Services\KasKoperasiService;
use App\Services\PinjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PinjamanController extends Controller
{
    public function __construct(
        private readonly PinjamanService $pinjamanService,
        private readonly KasKoperasiService $kasKoperasiService,
    ) {}

    /**
     * Menampilkan daftar seluruh pinjaman dengan pencarian, filter jenis/status, dan pagination.
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));
        $jenisFilter = strtoupper((string) $request->query('jenis_pinjaman', 'SEMUA'));
        $statusFilter = strtoupper((string) $request->query('status', 'SEMUA'));

        if (! in_array($jenisFilter, ['SEMUA', Pinjaman::JENIS_UANG, Pinjaman::JENIS_BARANG], true)) {
            $jenisFilter = 'SEMUA';
        }

        if (! in_array($statusFilter, ['SEMUA', Pinjaman::STATUS_BERJALAN, Pinjaman::STATUS_LUNAS], true)) {
            $statusFilter = 'SEMUA';
        }

        $query = Pinjaman::query()
            ->with([
                'anggota:id,nomor_anggota,nama_lengkap',
                'dicairkanOleh:id,nama_lengkap',
            ])
            ->withCount([
                'cicilan',
                'cicilan as cicilan_lunas_count' => fn ($q) => $q->where('status', CicilanPinjaman::STATUS_LUNAS),
            ]);

        if ($keyword !== '') {
            $likeKeyword = '%'.mb_strtolower($keyword).'%';
            $query->where(function ($subQuery) use ($likeKeyword): void {
                $subQuery
                    ->whereRaw('LOWER(nomor_pinjaman) LIKE ?', [$likeKeyword])
                    ->orWhereRaw('LOWER(COALESCE(nama_barang, \'\')) LIKE ?', [$likeKeyword])
                    ->orWhereHas('anggota', function ($anggotaQuery) use ($likeKeyword): void {
                        $anggotaQuery
                            ->whereRaw('LOWER(nama_lengkap) LIKE ?', [$likeKeyword])
                            ->orWhereRaw('LOWER(nomor_anggota) LIKE ?', [$likeKeyword]);
                    });
            });
        }

        if ($jenisFilter !== 'SEMUA') {
            $query->where('jenis_pinjaman', $jenisFilter);
        }

        if ($statusFilter !== 'SEMUA') {
            $query->where('status', $statusFilter);
        }

        $daftarPinjaman = $query
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Pinjaman::STATUS_BERJALAN])
            ->orderByDesc('tanggal_cair')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $ringkasanKas = $this->kasKoperasiService->ringkasanKas();
        $totalPiutangBerjalan = $this->kasKoperasiService->formatCents(
            $this->kasKoperasiService->toCents(Pinjaman::query()->berjalan()->sum('sisa_tagihan')),
        );
        $jumlahBerjalan = Pinjaman::query()->berjalan()->count();
        $jumlahLunas = Pinjaman::query()->where('status', Pinjaman::STATUS_LUNAS)->count();

        return view('admin.pinjaman.index', [
            'daftarPinjaman' => $daftarPinjaman,
            'keyword' => $keyword,
            'jenisFilter' => $jenisFilter,
            'statusFilter' => $statusFilter,
            'totalKasKoperasi' => $ringkasanKas['total_kas_koperasi'],
            'totalPiutangBerjalan' => $totalPiutangBerjalan,
            'jumlahBerjalan' => $jumlahBerjalan,
            'jumlahLunas' => $jumlahLunas,
        ]);
    }

    /**
     * Menampilkan form pencairan pinjaman baru lengkap dengan simulasi cicilan.
     */
    public function create(): View
    {
        $daftarAnggotaAktif = User::query()
            ->aktif()
            ->orderBy('nomor_anggota')
            ->get(['id', 'nomor_anggota', 'nama_lengkap']);

        $pengaturan = PengaturanKoperasi::query()
            ->latest('diperbarui_pada')
            ->latest('created_at')
            ->first();

        $ringkasanKas = $this->kasKoperasiService->ringkasanKas();

        return view('admin.pinjaman.create', [
            'daftarAnggotaAktif' => $daftarAnggotaAktif,
            'persenMarginBarang' => (float) ($pengaturan?->persen_margin_barang ?? 15.00),
            'totalKasKoperasi' => $ringkasanKas['total_kas_koperasi'],
            'totalKasKoperasiAngka' => $ringkasanKas['total_kas_koperasi_cents'] / 100,
        ]);
    }

    /**
     * Memvalidasi dan mencairkan pinjaman baru melalui PinjamanService secara atomik.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'anggota_id' => [
                'required',
                Rule::exists('anggota', 'id')->where('status', User::STATUS_AKTIF),
            ],
            'jenis_pinjaman' => ['required', 'string', Rule::in([Pinjaman::JENIS_UANG, Pinjaman::JENIS_BARANG])],
            'nama_barang' => ['required_if:jenis_pinjaman,'.Pinjaman::JENIS_BARANG, 'nullable', 'string', 'max:255'],
            'pokok_pinjaman' => ['required', 'numeric', 'min:50000'],
            'tenor_bulan' => ['required', 'integer', 'min:1', 'max:60'],
            'tanggal_cair' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ], [
            'anggota_id.required' => 'Anggota peminjam wajib dipilih.',
            'anggota_id.exists' => 'Anggota peminjam tidak ditemukan atau berstatus tidak aktif (KELUAR).',
            'jenis_pinjaman.required' => 'Jenis pinjaman wajib dipilih.',
            'jenis_pinjaman.in' => 'Jenis pinjaman harus UANG atau BARANG.',
            'nama_barang.required_if' => 'Nama barang wajib diisi untuk Pinjaman Barang.',
            'pokok_pinjaman.required' => 'Pokok pinjaman wajib diisi.',
            'pokok_pinjaman.numeric' => 'Pokok pinjaman harus berupa angka.',
            'pokok_pinjaman.min' => 'Pokok pinjaman minimal Rp 50.000.',
            'tenor_bulan.required' => 'Tenor bulan wajib diisi.',
            'tenor_bulan.integer' => 'Tenor bulan harus berupa bilangan bulat.',
            'tenor_bulan.min' => 'Tenor minimal 1 bulan.',
            'tenor_bulan.max' => 'Tenor maksimal 60 bulan.',
            'tanggal_cair.required' => 'Tanggal pencairan wajib diisi.',
            'tanggal_cair.date' => 'Format tanggal pencairan tidak valid.',
        ]);

        /** @var User $admin */
        $admin = $request->user();

        $pinjaman = $this->pinjamanService->cairkanPinjaman($validated, $admin);

        return redirect()
            ->route('admin.pinjaman.show', $pinjaman)
            ->with('success', "Pinjaman {$pinjaman->nomor_pinjaman} sebesar ".format_rupiah($pinjaman->pokok_pinjaman)." berhasil dicairkan beserta {$pinjaman->tenor_bulan} jadwal cicilan.");
    }

    /**
     * Menampilkan detail kontrak pinjaman beserta seluruh jadwal cicilannya.
     */
    public function show(Pinjaman $pinjaman): View
    {
        $pinjaman->load([
            'anggota:id,nomor_anggota,nama_lengkap,no_hp,status',
            'dicairkanOleh:id,nama_lengkap,nomor_anggota',
            'cicilan' => fn ($query) => $query->orderBy('cicilan_ke'),
        ]);

        $nominalMarginCents = $this->kasKoperasiService->toCents((string) $pinjaman->total_tagihan)
            - $this->kasKoperasiService->toCents((string) $pinjaman->pokok_pinjaman);

        return view('admin.pinjaman.show', [
            'pinjaman' => $pinjaman,
            'nominalMargin' => $this->kasKoperasiService->formatCents(max(0, $nominalMarginCents)),
        ]);
    }
}
