@extends('layouts.admin')

@section('title', 'Catat Transaksi Kas Usaha')
@section('header', 'Catat Transaksi Kas Usaha')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Input Transaksi Kas Baru</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Nomor transaksi dibuat otomatis dengan format <span class="font-mono text-zinc-700">KAS-YYYYMMDD-XXXX</span>.</p>
            </div>
            <a
                href="{{ route('admin.kas-usaha.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition shrink-0"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        {{-- Hero Banner Saldo Kas Tersedia --}}
        <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 shadow-xs">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Saldo Kas Koperasi Tersedia</p>
                <p class="text-xs text-zinc-400 mt-0.5">Pengeluaran kas (KELUAR) tidak dapat melebihi saldo ini.</p>
            </div>
            <p class="font-mono tabular-nums text-2xl font-bold text-emerald-400 shrink-0">{{ format_rupiah($totalKasKoperasi) }}</p>
        </div>

        <div class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs">
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.kas-usaha.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_transaksi" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Tanggal Transaksi</label>
                        <input
                            id="tanggal_transaksi"
                            name="tanggal_transaksi"
                            type="date"
                            required
                            value="{{ old('tanggal_transaksi', now()->toDateString()) }}"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="jenis_arus" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Jenis Arus Kas</label>
                        <select
                            id="jenis_arus"
                            name="jenis_arus"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                            <option value="{{ \App\Models\TransaksiKasUsaha::ARUS_MASUK }}" @selected(old('jenis_arus', \App\Models\TransaksiKasUsaha::ARUS_MASUK) === \App\Models\TransaksiKasUsaha::ARUS_MASUK)>
                                MASUK (Pemasukan Kas)
                            </option>
                            <option value="{{ \App\Models\TransaksiKasUsaha::ARUS_KELUAR }}" @selected(old('jenis_arus') === \App\Models\TransaksiKasUsaha::ARUS_KELUAR)>
                                KELUAR (Pengeluaran Kas)
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kategori" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Kategori</label>
                        <select
                            id="kategori"
                            name="kategori"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                            @foreach ($daftarKategori as $kode => $label)
                                <option value="{{ $kode }}" @selected(old('kategori', 'USAHA_WARUNG') === $kode)>
                                    {{ $label }} ({{ $kode }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="nominal" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nominal (Rp)</label>
                        <input
                            id="nominal"
                            name="nominal"
                            type="number"
                            step="0.01"
                            min="1000"
                            required
                            value="{{ old('nominal') }}"
                            placeholder="Minimal 1000"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Keterangan Transaksi</label>
                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="3"
                        required
                        maxlength="500"
                        placeholder="Tuliskan rincian sumber pemasukan atau tujuan pengeluaran kas..."
                        class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >{{ old('keterangan') }}</textarea>
                </div>

                <div class="rounded-xl bg-amber-500/10 border border-amber-500/20 p-3.5 text-xs text-amber-800">
                    Pastikan nominal dan jenis arus kas sudah benar sebelum menyimpan. Transaksi yang sudah tercatat bersifat permanen demi keamanan audit.
                </div>

                <div class="pt-2 border-t border-zinc-100 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.kas-usaha.index') }}"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition cursor-pointer"
                    >
                        Simpan Transaksi Kas
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
