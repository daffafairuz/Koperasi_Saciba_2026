@extends('layouts.admin')

@section('title', 'Catat Transaksi Kas Usaha')
@section('header', 'Catat Transaksi Kas Usaha')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Input Transaksi Kas Baru</h2>
                <p class="text-sm text-slate-500 mt-0.5">Nomor transaksi akan dibuat otomatis dengan format KAS-YYYYMMDD-XXXX.</p>
            </div>
            <a
                href="{{ route('admin.kas-usaha.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0"
            >
                &larr; Kembali
            </a>
        </div>

        {{-- Info Saldo Kas Tersedia --}}
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Saldo Kas Koperasi Tersedia Saat Ini</p>
                <p class="text-xs text-emerald-700 mt-0.5">Pengeluaran kas (KELUAR) tidak dapat melebihi saldo ini.</p>
            </div>
            <p class="text-xl font-bold text-emerald-800 shrink-0">{{ format_rupiah($totalKasKoperasi) }}</p>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
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
                        <label for="tanggal_transaksi" class="block text-sm font-medium text-slate-700">Tanggal Transaksi</label>
                        <input
                            id="tanggal_transaksi"
                            name="tanggal_transaksi"
                            type="date"
                            required
                            value="{{ old('tanggal_transaksi', now()->toDateString()) }}"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label for="jenis_arus" class="block text-sm font-medium text-slate-700">Jenis Arus Kas</label>
                        <select
                            id="jenis_arus"
                            name="jenis_arus"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
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
                        <label for="kategori" class="block text-sm font-medium text-slate-700">Kategori</label>
                        <select
                            id="kategori"
                            name="kategori"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            @foreach ($daftarKategori as $kode => $label)
                                <option value="{{ $kode }}" @selected(old('kategori', 'USAHA_WARUNG') === $kode)>
                                    {{ $label }} ({{ $kode }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="nominal" class="block text-sm font-medium text-slate-700">Nominal (Rp)</label>
                        <input
                            id="nominal"
                            name="nominal"
                            type="number"
                            step="0.01"
                            min="1000"
                            required
                            value="{{ old('nominal') }}"
                            placeholder="Minimal 1000"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-slate-700">Keterangan Transaksi</label>
                    <textarea
                        id="keterangan"
                        name="keterangan"
                        rows="3"
                        required
                        maxlength="500"
                        placeholder="Tuliskan rincian sumber pemasukan atau tujuan pengeluaran kas..."
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >{{ old('keterangan') }}</textarea>
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-200 p-3.5 text-xs text-amber-800">
                    Pastikan nominal dan jenis arus kas sudah benar sebelum menyimpan. Transaksi yang sudah tercatat tidak dapat diubah atau dihapus demi keamanan audit.
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.kas-usaha.index') }}"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition cursor-pointer"
                    >
                        Simpan Transaksi Kas
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
