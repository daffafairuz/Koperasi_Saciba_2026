@extends('layouts.admin')

@section('title', 'Pengaturan Koperasi')
@section('header', 'Pengaturan Tarif Dinamis Koperasi')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Kartu Ringkasan Tarif Aktif --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Simpanan Pokok Aktif</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">
                    {{ format_rupiah($pengaturan?->nominal_simpanan_pokok ?? 100000) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">Dibayar 1 kali saat menjadi anggota.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Simpanan Wajib Aktif</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">
                    {{ format_rupiah($pengaturan?->nominal_simpanan_wajib ?? 50000) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">Tarif tagihan bulanan anggota aktif.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Margin Pinjaman Barang</p>
                <p class="mt-2 text-2xl font-bold text-teal-700">
                    {{ number_format((float) ($pengaturan?->persen_margin_barang ?? 15), 2, ',', '.') }}%
                </p>
                <p class="mt-1.5 text-xs text-slate-500">Margin flat (pinjaman uang tetap 0% syariah).</p>
            </div>
        </section>

        {{-- Informasi Audit Terakhir Diperbarui --}}
        <section class="rounded-2xl bg-white border border-slate-200 px-5 py-4 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-slate-600">
            <div>
                <span class="font-semibold text-slate-800">Terakhir diperbarui oleh:</span>
                @if ($pengaturan?->diperbaruiOleh)
                    {{ $pengaturan->diperbaruiOleh->nama_lengkap }} ({{ $pengaturan->diperbaruiOleh->nomor_anggota }})
                @else
                    Sistem / Default Awal
                @endif
            </div>
            <div>
                <span class="font-semibold text-slate-800">Waktu pembaruan:</span>
                {{ $pengaturan?->diperbarui_pada ? $pengaturan->diperbarui_pada->translatedFormat('d M Y, H:i') : '-' }}
            </div>
        </section>

        {{-- Info Alert Non-Retroaktif --}}
        <section class="rounded-2xl bg-sky-50 border border-sky-200 p-4 text-sm text-sky-900" role="note">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-sky-600 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
                </svg>
                <div class="space-y-1">
                    <p class="font-semibold">Perubahan Tarif Bersifat Snapshot (Tidak Retroaktif)</p>
                    <p class="text-xs sm:text-sm text-sky-800">
                        Perubahan tarif di bawah ini <strong>hanya berlaku untuk pendaftaran anggota baru, penerbitan tagihan Simpanan Wajib periode berikutnya, dan pencairan pinjaman barang baru</strong>. Tagihan Simpanan Wajib dan jadwal cicilan pinjaman lama yang sudah terbit tidak akan berubah.
                    </p>
                </div>
            </div>
        </section>

        {{-- Form Pembaruan Tarif --}}
        <section class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
            <div class="mb-5">
                <h2 class="text-base font-bold text-slate-900">Perbarui Tarif Koperasi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Masukkan nominal dalam Rupiah penuh dan persentase margin barang (0–100%).</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nominal_simpanan_pokok" class="block text-sm font-medium text-slate-700">
                            Nominal Simpanan Pokok (Rp)
                        </label>
                        <input
                            id="nominal_simpanan_pokok"
                            name="nominal_simpanan_pokok"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            value="{{ old('nominal_simpanan_pokok', (float) ($pengaturan?->nominal_simpanan_pokok ?? 100000)) }}"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label for="nominal_simpanan_wajib" class="block text-sm font-medium text-slate-700">
                            Nominal Simpanan Wajib / Bulan (Rp)
                        </label>
                        <input
                            id="nominal_simpanan_wajib"
                            name="nominal_simpanan_wajib"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            value="{{ old('nominal_simpanan_wajib', (float) ($pengaturan?->nominal_simpanan_wajib ?? 50000)) }}"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>
                </div>

                <div class="max-w-sm">
                    <label for="persen_margin_barang" class="block text-sm font-medium text-slate-700">
                        Persentase Margin Pinjaman Barang (%)
                    </label>
                    <input
                        id="persen_margin_barang"
                        name="persen_margin_barang"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        required
                        value="{{ old('persen_margin_barang', (float) ($pengaturan?->persen_margin_barang ?? 15)) }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                    <p class="mt-1 text-xs text-slate-500">Contoh: 15 untuk margin flat 15%.</p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition cursor-pointer"
                    >
                        Simpan Pengaturan Tarif
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
