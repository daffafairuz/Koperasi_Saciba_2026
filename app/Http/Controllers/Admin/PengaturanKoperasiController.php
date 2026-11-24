<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanKoperasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PengaturanKoperasiController extends Controller
{
    /**
     * Menampilkan konfigurasi tarif aktif koperasi beserta info audit pembaruan terakhir.
     */
    public function index(): View
    {
        $pengaturan = PengaturanKoperasi::query()
            ->with('diperbaruiOleh:id,nama_lengkap,nomor_anggota')
            ->latest('diperbarui_pada')
            ->latest('created_at')
            ->first();

        return view('admin.pengaturan.index', [
            'pengaturan' => $pengaturan,
        ]);
    }

    /**
     * Memperbarui tarif dinamis koperasi (berlaku untuk transaksi/tagihan baru ke depannya).
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nominal_simpanan_pokok' => ['required', 'numeric', 'min:0'],
            'nominal_simpanan_wajib' => ['required', 'numeric', 'min:0'],
            'persen_margin_barang' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'nominal_simpanan_pokok.required' => 'Nominal Simpanan Pokok wajib diisi.',
            'nominal_simpanan_pokok.numeric' => 'Nominal Simpanan Pokok harus berupa angka.',
            'nominal_simpanan_pokok.min' => 'Nominal Simpanan Pokok tidak boleh negatif.',
            'nominal_simpanan_wajib.required' => 'Nominal Simpanan Wajib wajib diisi.',
            'nominal_simpanan_wajib.numeric' => 'Nominal Simpanan Wajib harus berupa angka.',
            'nominal_simpanan_wajib.min' => 'Nominal Simpanan Wajib tidak boleh negatif.',
            'persen_margin_barang.required' => 'Persentase margin pinjaman barang wajib diisi.',
            'persen_margin_barang.numeric' => 'Persentase margin barang harus berupa angka.',
            'persen_margin_barang.min' => 'Persentase margin barang tidak boleh negatif.',
            'persen_margin_barang.max' => 'Persentase margin barang maksimal 100%.',
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $payload = [
                'nominal_simpanan_pokok' => number_format((float) $validated['nominal_simpanan_pokok'], 2, '.', ''),
                'nominal_simpanan_wajib' => number_format((float) $validated['nominal_simpanan_wajib'], 2, '.', ''),
                'persen_margin_barang' => number_format((float) $validated['persen_margin_barang'], 2, '.', ''),
                'diperbarui_oleh' => $request->user()?->id,
                'diperbarui_pada' => now(),
            ];

            $pengaturan = PengaturanKoperasi::query()
                ->latest('diperbarui_pada')
                ->latest('created_at')
                ->first();

            if ($pengaturan !== null) {
                $pengaturan->update($payload);
            } else {
                PengaturanKoperasi::query()->create($payload);
            }
        });

        return redirect()
            ->route('admin.pengaturan.index')
            ->with('success', 'Pengaturan tarif koperasi berhasil diperbarui dan langsung berlaku untuk transaksi baru.');
    }
}
