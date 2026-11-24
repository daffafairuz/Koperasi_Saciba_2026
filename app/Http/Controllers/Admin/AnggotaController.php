<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnggotaRequest;
use App\Http\Requests\Admin\UpdateAnggotaRequest;
use App\Models\PengaturanKoperasi;
use App\Models\Pinjaman;
use App\Models\RiwayatStatusAnggota;
use App\Models\SimpananAnggota;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    /**
     * Menampilkan daftar anggota dengan pencarian, filter status, dan pagination.
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('q', ''));
        $statusFilter = strtoupper((string) $request->query('status', 'SEMUA'));

        if (! in_array($statusFilter, ['SEMUA', User::STATUS_AKTIF, User::STATUS_KELUAR], true)) {
            $statusFilter = 'SEMUA';
        }

        $query = User::query()->with('simpanan');

        if ($keyword !== '') {
            $likeKeyword = '%'.mb_strtolower($keyword).'%';
            $query->where(function ($subQuery) use ($likeKeyword): void {
                $subQuery
                    ->whereRaw('LOWER(nama_lengkap) LIKE ?', [$likeKeyword])
                    ->orWhereRaw('LOWER(nomor_anggota) LIKE ?', [$likeKeyword])
                    ->orWhereRaw('LOWER(no_hp) LIKE ?', [$likeKeyword]);
            });
        }

        if ($statusFilter !== 'SEMUA') {
            $query->where('status', $statusFilter);
        }

        $daftarAnggota = $query
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [User::STATUS_AKTIF])
            ->orderBy('nomor_anggota')
            ->paginate(10)
            ->withQueryString();

        $jumlahAktif = User::query()->where('status', User::STATUS_AKTIF)->count();
        $jumlahKeluar = User::query()->where('status', User::STATUS_KELUAR)->count();

        return view('admin.anggota.index', [
            'daftarAnggota' => $daftarAnggota,
            'keyword' => $keyword,
            'statusFilter' => $statusFilter,
            'totalAnggota' => $jumlahAktif + $jumlahKeluar,
            'jumlahAktif' => $jumlahAktif,
            'jumlahKeluar' => $jumlahKeluar,
        ]);
    }

    /**
     * Menampilkan form pendaftaran anggota baru.
     */
    public function create(): View
    {
        $pengaturan = PengaturanKoperasi::query()
            ->latest('diperbarui_pada')
            ->latest('created_at')
            ->first();

        return view('admin.anggota.create', [
            'nominalSimpananPokokDefault' => (string) ($pengaturan?->nominal_simpanan_pokok ?? '100000.00'),
        ]);
    }

    /**
     * Menyimpan anggota baru sekaligus membuat dompet simpanan_anggota (1-on-1) secara atomik.
     */
    public function store(StoreAnggotaRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $anggota = DB::transaction(function () use ($validated): User {
            $pengaturan = PengaturanKoperasi::query()
                ->latest('diperbarui_pada')
                ->latest('created_at')
                ->first();

            $anggotaBaru = User::query()->create([
                'nomor_anggota' => $validated['nomor_anggota'],
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_hp' => $validated['no_hp'],
                'password' => Hash::make($validated['password']),
                'peran' => $validated['peran'],
                'status' => User::STATUS_AKTIF,
                'wajib_simpanan' => $validated['wajib_simpanan'],
                'tanggal_gabung' => $validated['tanggal_gabung'],
                'tanggal_keluar' => null,
            ]);

            SimpananAnggota::query()->create([
                'anggota_id' => $anggotaBaru->id,
                'nominal_simpanan_pokok' => (string) ($pengaturan?->nominal_simpanan_pokok ?? '100000.00'),
                'status_simpanan_pokok' => SimpananAnggota::STATUS_POKOK_BELUM_BAYAR,
                'tanggal_lunas_pokok' => null,
                'saldo_sw_awal' => '0.00',
                'total_simpanan_wajib' => '0.00',
                'periode_sw_terakhir' => null,
            ]);

            return $anggotaBaru;
        });

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', "Anggota {$anggota->nama_lengkap} ({$anggota->nomor_anggota}) berhasil didaftarkan beserta dompet simpanannya.");
    }

    /**
     * Menampilkan profil lengkap anggota, ringkasan simpanan, pinjaman, dan riwayat status.
     */
    public function show(User $anggota): View
    {
        $anggota->load([
            'simpanan',
            'pinjaman' => fn ($query) => $query
                ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Pinjaman::STATUS_BERJALAN])
                ->orderByDesc('tanggal_cair')
                ->orderByDesc('created_at'),
            'riwayatStatus' => fn ($query) => $query
                ->with('disetujuiOleh:id,nama_lengkap')
                ->orderByDesc('tanggal_disetujui')
                ->orderByDesc('created_at'),
        ]);

        return view('admin.anggota.show', [
            'anggota' => $anggota,
        ]);
    }

    /**
     * Menampilkan form edit profil anggota.
     */
    public function edit(User $anggota): View
    {
        return view('admin.anggota.edit', [
            'anggota' => $anggota,
        ]);
    }

    /**
     * Memperbarui data profil anggota (status tidak diubah melalui form profil biasa).
     */
    public function update(UpdateAnggotaRequest $request, User $anggota): RedirectResponse
    {
        $validated = $request->validated();

        $anggota->update([
            'nomor_anggota' => $validated['nomor_anggota'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_hp' => $validated['no_hp'],
            'peran' => $validated['peran'],
            'wajib_simpanan' => $validated['wajib_simpanan'],
            'tanggal_gabung' => $validated['tanggal_gabung'],
        ]);

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', "Profil anggota {$anggota->nama_lengkap} berhasil diperbarui.");
    }

    /**
     * Proses Soft-Exit (KELUAR) atau Re-aktivasi (AKTIF) beserta pencatatan log audit status.
     */
    public function ubahStatus(Request $request, User $anggota): RedirectResponse
    {
        $validated = $request->validateWithBag('ubahStatus', [
            'status_baru' => ['required', 'string', Rule::in([User::STATUS_AKTIF, User::STATUS_KELUAR])],
            'alasan' => ['required', 'string', 'max:1000'],
        ], [
            'status_baru.required' => 'Status baru wajib dipilih.',
            'status_baru.in' => 'Status baru tidak valid.',
            'alasan.required' => 'Alasan perubahan status wajib diisi untuk catatan audit.',
        ]);

        if ($anggota->id === $request->user()?->id && $validated['status_baru'] === User::STATUS_KELUAR) {
            return back()
                ->withErrors(['status_baru' => 'Anda tidak dapat menonaktifkan akun Anda sendiri yang sedang digunakan.'], 'ubahStatus')
                ->withInput();
        }

        if ($anggota->status === $validated['status_baru']) {
            return back()
                ->withErrors(['status_baru' => "Anggota sudah berstatus {$anggota->status}."], 'ubahStatus')
                ->withInput();
        }

        DB::transaction(function () use ($request, $anggota, $validated): void {
            $statusSebelumnya = $anggota->status;
            $waktuPerubahan = now();

            $anggota->update([
                'status' => $validated['status_baru'],
                'tanggal_keluar' => $validated['status_baru'] === User::STATUS_KELUAR
                    ? $waktuPerubahan->toDateString()
                    : null,
            ]);

            RiwayatStatusAnggota::query()->create([
                'anggota_id' => $anggota->id,
                'status_sebelumnya' => $statusSebelumnya,
                'status_baru' => $validated['status_baru'],
                'alasan' => trim($validated['alasan']),
                'tanggal_pengajuan' => $waktuPerubahan,
                'disetujui_oleh' => $request->user()?->id,
                'tanggal_disetujui' => $waktuPerubahan,
            ]);
        });

        $labelStatus = $validated['status_baru'] === User::STATUS_KELUAR ? 'KELUAR (Soft-Exit)' : 'AKTIF (Re-aktivasi)';

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', "Status keanggotaan {$anggota->nama_lengkap} berhasil diubah menjadi {$labelStatus}.");
    }

    /**
     * Mereset kata sandi anggota secara aman oleh Admin.
     */
    public function resetPassword(Request $request, User $anggota): RedirectResponse
    {
        $validated = $request->validateWithBag('resetPassword', [
            'password_baru' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_baru.required' => 'Kata sandi baru wajib diisi.',
            'password_baru.min' => 'Kata sandi baru minimal 6 karakter.',
            'password_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $anggota->update([
            'password' => Hash::make($validated['password_baru']),
        ]);

        return redirect()
            ->route('admin.anggota.show', $anggota)
            ->with('success', "Kata sandi untuk {$anggota->nama_lengkap} berhasil direset.");
    }
}
