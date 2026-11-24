@extends('layouts.admin')

@section('title', 'Buku Kas Usaha')
@section('header', 'Buku Kas Usaha Non-Anggota')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Arus Kas Usaha &amp; Operasional</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Pencatatan pemasukan unit usaha (warung, sewa kantin) dan pengeluaran operasional koperasi secara append-only.
                </p>
            </div>
            <a
                href="{{ route('admin.kas-usaha.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition self-start sm:self-auto"
            >
                <span>+ Catat Transaksi Kas</span>
            </a>
        </div>

        {{-- 3 Kartu Ringkasan Saldo --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Total Kas Koperasi Saat Ini</p>
                <p class="mt-2.5 text-2xl font-bold">{{ format_rupiah($totalKasKoperasi) }}</p>
                <p class="mt-1.5 text-xs text-emerald-100">Saldo kas likuid aktual (termasuk setoran anggota &amp; pencairan pinjaman baru).</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Pemasukan Usaha</p>
                <p class="mt-2.5 text-2xl font-bold text-emerald-700">{{ format_rupiah($totalPemasukanUsaha) }}</p>
                <p class="mt-1.5 text-xs text-slate-500">Akumulasi seluruh arus MASUK di buku kas usaha.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Pengeluaran Usaha</p>
                <p class="mt-2.5 text-2xl font-bold text-rose-600">{{ format_rupiah($totalPengeluaranUsaha) }}</p>
                <p class="mt-1.5 text-xs text-slate-500">Akumulasi seluruh arus KELUAR (operasional, THR, dll.).</p>
            </div>
        </section>

        {{-- Filter Transaksi --}}
        <section class="rounded-2xl bg-white border border-slate-200 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.kas-usaha.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <select
                    name="jenis_arus"
                    class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                >
                    <option value="SEMUA" @selected($jenisArusFilter === 'SEMUA')>Semua Jenis Arus</option>
                    <option value="{{ \App\Models\TransaksiKasUsaha::ARUS_MASUK }}" @selected($jenisArusFilter === \App\Models\TransaksiKasUsaha::ARUS_MASUK)>Arus MASUK</option>
                    <option value="{{ \App\Models\TransaksiKasUsaha::ARUS_KELUAR }}" @selected($jenisArusFilter === \App\Models\TransaksiKasUsaha::ARUS_KELUAR)>Arus KELUAR</option>
                </select>

                <select
                    name="kategori"
                    class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                >
                    <option value="SEMUA" @selected($kategoriFilter === 'SEMUA')>Semua Kategori</option>
                    @foreach ($kategoriTersedia as $kodeKategori => $labelKategori)
                        <option value="{{ $kodeKategori }}" @selected($kategoriFilter === $kodeKategori)>
                            {{ $labelKategori }} ({{ $kodeKategori }})
                        </option>
                    @endforeach
                </select>

                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition cursor-pointer"
                    >
                        Terapkan Filter
                    </button>

                    @if ($jenisArusFilter !== 'SEMUA' || $kategoriFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.kas-usaha.index') }}"
                            class="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Riwayat Kas Usaha --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 text-xs text-slate-500">
                Demi integritas audit pembukuan, transaksi kas yang telah dicatat bersifat permanen (tidak dapat diedit/dihapus). Jika terjadi kekeliruan, buat transaksi koreksi balik.
            </div>

            @if ($daftarTransaksi->isEmpty())
                <div class="p-10 text-center text-sm text-slate-500">
                    Tidak ada transaksi kas usaha yang sesuai dengan filter.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Nomor &amp; Tanggal</th>
                                <th class="px-5 py-3 text-center">Arus</th>
                                <th class="px-5 py-3 text-left">Kategori &amp; Keterangan</th>
                                <th class="px-5 py-3 text-left">Diinput Oleh</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($daftarTransaksi as $transaksi)
                                @php
                                    $isMasuk = $transaksi->jenis_arus === \App\Models\TransaksiKasUsaha::ARUS_MASUK;
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-slate-900">{{ $transaksi->nomor_transaksi }}</p>
                                        <p class="text-xs text-slate-500">{{ $transaksi->tanggal_transaksi->translatedFormat('d M Y') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isMasuk ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                                            {{ $transaksi->jenis_arus }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                            {{ $transaksi->kategori }}
                                        </span>
                                        <p class="mt-1 text-xs sm:text-sm text-slate-700">{{ $transaksi->keterangan }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        {{ $transaksi->diinputOleh?->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold whitespace-nowrap {{ $isMasuk ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $isMasuk ? '+' : '-' }}{{ format_rupiah($transaksi->nominal) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($daftarTransaksi->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $daftarTransaksi->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
