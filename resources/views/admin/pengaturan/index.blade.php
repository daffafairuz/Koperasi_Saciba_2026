@extends('layouts.admin')

@section('title', 'Pengaturan Koperasi')
@section('header', 'Pengaturan Tarif Dinamis Koperasi')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Bento Grid 3 Kartu Tarif Aktif --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Simpanan Pokok Aktif</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-emerald-400">
                    {{ format_rupiah($pengaturan?->nominal_simpanan_pokok ?? 100000) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-400">Dibayar 1 kali saat pendaftaran anggota.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Simpanan Wajib Aktif</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-zinc-950">
                    {{ format_rupiah($pengaturan?->nominal_simpanan_wajib ?? 50000) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500">Tarif tagihan bulanan anggota aktif.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Margin Pinjaman Barang</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-emerald-600">
                    {{ number_format((float) ($pengaturan?->persen_margin_barang ?? 15), 2, ',', '.') }}%
                </p>
                <p class="mt-1.5 text-xs text-zinc-500">Margin flat (pinjaman uang tetap 0% syariah).</p>
            </div>
        </section>

        {{-- Informasi Audit Terakhir Diperbarui --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 px-5 py-4 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-zinc-600">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <span>
                    <span class="font-semibold text-zinc-900">Terakhir diperbarui oleh:</span>
                    @if ($pengaturan?->diperbaruiOleh)
                        {{ $pengaturan->diperbaruiOleh->nama_lengkap }}
                        (<span class="font-mono tabular-nums">{{ $pengaturan->diperbaruiOleh->nomor_anggota }}</span>)
                    @else
                        Sistem / Default Awal
                    @endif
                </span>
            </div>
            <div class="tabular-nums">
                <span class="font-semibold text-zinc-900">Waktu:</span>
                {{ $pengaturan?->diperbarui_pada ? $pengaturan->diperbarui_pada->translatedFormat('d M Y, H:i') : '-' }}
            </div>
        </section>

        {{-- Info Alert Non-Retroaktif --}}
        <section class="rounded-2xl bg-zinc-900 text-zinc-200 border border-zinc-800 p-4 text-sm" role="note">
            <div class="flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
                <div class="space-y-1">
                    <p class="font-semibold text-white text-xs uppercase tracking-wider">Perubahan Tarif Bersifat Snapshot (Tidak Retroaktif)</p>
                    <p class="text-xs text-zinc-300 leading-relaxed">
                        Perubahan tarif hanya berlaku untuk <strong class="text-white">pendaftaran anggota baru, penerbitan tagihan Simpanan Wajib periode berikutnya, dan pencairan pinjaman barang baru</strong>. Tagihan dan jadwal cicilan lama tidak akan berubah.
                    </p>
                </div>
            </div>
        </section>

        {{-- Form Pembaruan Tarif --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs">
            <div class="mb-5">
                <h2 class="text-base font-bold tracking-tight text-zinc-950">Perbarui Tarif Koperasi</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Masukkan nominal dalam Rupiah penuh dan persentase margin barang (0–100%).</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700">
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
                        <label for="nominal_simpanan_pokok" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">
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
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="nominal_simpanan_wajib" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">
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
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>
                </div>

                <div class="max-w-sm">
                    <label for="persen_margin_barang" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">
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
                        class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                    <p class="mt-1 text-xs text-zinc-500">Contoh: 15 untuk margin flat 15%.</p>
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition cursor-pointer"
                    >
                        Simpan Pengaturan Tarif
                    </button>
                </div>
            </form>
        </section>

        {{-- Pemicu Manual Tagihan Simpanan Wajib Bulanan --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 border border-zinc-200/80 px-2.5 py-0.5 font-mono text-[10px] font-semibold text-zinc-600">
                    SCHEDULER // TANGGAL 1 PUKUL 00:05 WIB
                </div>
                <h2 class="text-base font-bold tracking-tight text-zinc-950">Penerbitan Tagihan Simpanan Wajib Bulanan</h2>
                <p class="text-xs sm:text-sm text-zinc-500">
                    Cadangan manual untuk menerbitkan tagihan periode bulan berjalan (<strong class="text-zinc-900">{{ format_periode(now('Asia/Jakarta')->format('Ym')) }}</strong>); anggota yang sudah memiliki tagihan atau bayar di muka otomatis dilewati.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.pengaturan.generate-sw') }}" class="shrink-0">
                @csrf
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-xs transition cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8" />
                        <path d="M21 3v5h-5" />
                    </svg>
                    <span>Terbitkan Tagihan SW Bulan Ini Secara Manual</span>
                </button>
            </form>
        </section>
    </div>
@endsection
