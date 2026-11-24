<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransaksiKasUsaha;
use App\Services\KasKoperasiService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KasUsahaController extends Controller
{
    /**
     * Daftar kategori standar untuk input transaksi kas usaha baru.
     *
     * @var array<string, string>
     */
    public const array KATEGORI_STANDAR = [
        'USAHA_WARUNG' => 'Usaha Warung',
        'SEWA_KANTIN' => 'Sewa Kantin',
        'BIAYA_OPERASIONAL' => 'Biaya Operasional',
        'THR' => 'THR & Bingkisan',
        'LAINNYA' => 'Lainnya / Koreksi',
    ];

    public function __construct(
        private readonly KasKoperasiService $kasKoperasiService,
    ) {}

    /**
     * Menampilkan buku kas usaha non-anggota beserta ringkasan saldo real-time.
     */
    public function index(Request $request): View
    {
        $jenisArusFilter = strtoupper((string) $request->query('jenis_arus', 'SEMUA'));
        $kategoriFilter = strtoupper(trim((string) $request->query('kategori', 'SEMUA')));

        if (! in_array($jenisArusFilter, ['SEMUA', TransaksiKasUsaha::ARUS_MASUK, TransaksiKasUsaha::ARUS_KELUAR], true)) {
            $jenisArusFilter = 'SEMUA';
        }

        $query = TransaksiKasUsaha::query()
            ->with('diinputOleh:id,nama_lengkap,nomor_anggota');

        if ($jenisArusFilter !== 'SEMUA') {
            $query->where('jenis_arus', $jenisArusFilter);
        }

        if ($kategoriFilter !== '' && $kategoriFilter !== 'SEMUA') {
            $query->where('kategori', $kategoriFilter);
        }

        $daftarTransaksi = $query
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $ringkasanKas = $this->kasKoperasiService->ringkasanKas();

        $kategoriTersedia = array_merge(
            ['SALDO_AWAL' => 'Saldo Awal'],
            self::KATEGORI_STANDAR,
        );

        foreach (TransaksiKasUsaha::query()->distinct()->pluck('kategori') as $kategoriDb) {
            if (! isset($kategoriTersedia[$kategoriDb])) {
                $kategoriTersedia[$kategoriDb] = str_replace('_', ' ', ucwords(strtolower($kategoriDb), '_'));
            }
        }

        return view('admin.kas-usaha.index', [
            'daftarTransaksi' => $daftarTransaksi,
            'jenisArusFilter' => $jenisArusFilter,
            'kategoriFilter' => $kategoriFilter,
            'kategoriTersedia' => $kategoriTersedia,
            'totalKasKoperasi' => $ringkasanKas['total_kas_koperasi'],
            'totalPemasukanUsaha' => $ringkasanKas['total_pemasukan_usaha'],
            'totalPengeluaranUsaha' => $ringkasanKas['total_pengeluaran_usaha'],
        ]);
    }

    /**
     * Menampilkan form pencatatan transaksi kas usaha baru.
     */
    public function create(): View
    {
        $ringkasanKas = $this->kasKoperasiService->ringkasanKas();

        return view('admin.kas-usaha.create', [
            'daftarKategori' => self::KATEGORI_STANDAR,
            'totalKasKoperasi' => $ringkasanKas['total_kas_koperasi'],
        ]);
    }

    /**
     * Menyimpan transaksi kas usaha baru (append-only, validasi saldo kas untuk KELUAR).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_transaksi' => ['required', 'date'],
            'jenis_arus' => ['required', 'string', Rule::in([TransaksiKasUsaha::ARUS_MASUK, TransaksiKasUsaha::ARUS_KELUAR])],
            'kategori' => ['required', 'string', 'max:100'],
            'nominal' => ['required', 'numeric', 'min:1000'],
            'keterangan' => ['required', 'string', 'max:500'],
        ], [
            'tanggal_transaksi.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal_transaksi.date' => 'Format tanggal transaksi tidak valid.',
            'jenis_arus.required' => 'Jenis arus kas wajib dipilih.',
            'jenis_arus.in' => 'Jenis arus kas harus MASUK atau KELUAR.',
            'kategori.required' => 'Kategori transaksi wajib dipilih.',
            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.numeric' => 'Nominal transaksi harus berupa angka.',
            'nominal.min' => 'Nominal transaksi minimal Rp 1.000.',
            'keterangan.required' => 'Keterangan transaksi wajib diisi untuk keperluan audit.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
        ]);

        $transaksi = DB::transaction(function () use ($request, $validated): TransaksiKasUsaha {
            $nominalCents = $this->kasKoperasiService->toCents($validated['nominal']);

            if ($validated['jenis_arus'] === TransaksiKasUsaha::ARUS_KELUAR) {
                $ringkasanKas = $this->kasKoperasiService->ringkasanKas();

                if ($nominalCents > $ringkasanKas['total_kas_koperasi_cents']) {
                    throw ValidationException::withMessages([
                        'nominal' => 'Saldo kas koperasi tidak mencukupi untuk pengeluaran ini. Saldo tersedia saat ini: '.format_rupiah($ringkasanKas['total_kas_koperasi']).'.',
                    ]);
                }
            }

            $nomorTransaksi = $this->generateNomorTransaksi(
                Carbon::parse($validated['tanggal_transaksi']),
            );

            return TransaksiKasUsaha::query()->create([
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal_transaksi' => Carbon::parse($validated['tanggal_transaksi'])->toDateString(),
                'jenis_arus' => $validated['jenis_arus'],
                'kategori' => strtoupper(trim($validated['kategori'])),
                'keterangan' => trim($validated['keterangan']),
                'nominal' => $this->kasKoperasiService->formatCents($nominalCents),
                'diinput_oleh' => $request->user()?->id,
            ]);
        });

        return redirect()
            ->route('admin.kas-usaha.index')
            ->with('success', "Transaksi kas {$transaksi->nomor_transaksi} ({$transaksi->jenis_arus} ".format_rupiah($transaksi->nominal).') berhasil dicatat.');
    }

    /**
     * Menghasilkan nomor transaksi unik otomatis format KAS-YYYYMMDD-XXXX.
     */
    private function generateNomorTransaksi(Carbon $tanggal): string
    {
        $prefix = 'KAS-'.$tanggal->format('Ymd').'-';

        $nomorTerakhir = TransaksiKasUsaha::query()
            ->where('nomor_transaksi', 'like', $prefix.'%')
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $urutanBerikutnya = 1;

        if (is_string($nomorTerakhir)) {
            $suffix = (int) substr($nomorTerakhir, strlen($prefix));
            $urutanBerikutnya = $suffix + 1;
        }

        return $prefix.str_pad((string) $urutanBerikutnya, 4, '0', STR_PAD_LEFT);
    }
}
