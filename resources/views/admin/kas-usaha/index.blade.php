@extends('layouts.admin')

@section('title', 'Buku Kas Usaha')
@section('header', 'Buku Kas Usaha Non-Anggota')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Arus Kas Usaha &amp; Operasional</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Pencatatan pemasukan unit usaha (warung, sewa kantin) dan pengeluaran operasional secara append-only.
                </p>
            </div>
            <a
                href="{{ route('admin.kas-usaha.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition self-start sm:self-auto"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                <span>Catat Transaksi Kas</span>
            </a>
        </div>

        {{-- 3 Kartu Ringkasan Saldo (Bento Grid) --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Total Kas Koperasi Saat Ini</p>
                <p class="mt-2.5 font-mono tabular-nums text-2xl font-bold text-emerald-400">{{ format_rupiah($totalKasKoperasi) }}</p>
                <p class="mt-1.5 text-xs text-zinc-400">Saldo kas likuid aktual (termasuk setoran anggota &amp; pencairan pinjaman baru).</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total Pemasukan Usaha</p>
                <p class="mt-2.5 font-mono tabular-nums text-2xl font-bold text-emerald-600">{{ format_rupiah($totalPemasukanUsaha) }}</p>
                <p class="mt-1.5 text-xs text-zinc-500">Akumulasi seluruh arus MASUK di buku kas usaha.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total Pengeluaran Usaha</p>
                <p class="mt-2.5 font-mono tabular-nums text-2xl font-bold text-rose-600">{{ format_rupiah($totalPengeluaranUsaha) }}</p>
                <p class="mt-1.5 text-xs text-zinc-500">Akumulasi seluruh arus KELUAR (operasional, THR, dll.).</p>
            </div>
        </section>

        {{-- Filter Transaksi --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.kas-usaha.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <select
                    name="jenis_arus"
                    class="rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm text-zinc-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                >
                    <option value="SEMUA" @selected($jenisArusFilter === 'SEMUA')>Semua Jenis Arus</option>
                    <option value="{{ \App\Models\TransaksiKasUsaha::ARUS_MASUK }}" @selected($jenisArusFilter === \App\Models\TransaksiKasUsaha::ARUS_MASUK)>Arus MASUK</option>
                    <option value="{{ \App\Models\TransaksiKasUsaha::ARUS_KELUAR }}" @selected($jenisArusFilter === \App\Models\TransaksiKasUsaha::ARUS_KELUAR)>Arus KELUAR</option>
                </select>

                <select
                    name="kategori"
                    class="rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm text-zinc-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
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
                        class="rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-sm font-semibold text-white transition cursor-pointer"
                    >
                        Terapkan Filter
                    </button>

                    @if ($jenisArusFilter !== 'SEMUA' || $kategoriFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.kas-usaha.index') }}"
                            class="rounded-xl border border-zinc-200 px-3.5 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Riwayat Kas Usaha --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 bg-zinc-50/80 border-b border-zinc-100 flex items-center gap-2 text-xs text-zinc-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                </svg>
                <span>Transaksi kas yang telah dicatat bersifat permanen (append-only). Jika terjadi kekeliruan, catat transaksi koreksi balik.</span>
            </div>

            @if ($daftarTransaksi->isEmpty())
                <div class="p-10 text-center text-sm text-zinc-500">
                    Tidak ada transaksi kas usaha yang sesuai dengan filter.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Nomor &amp; Tanggal</th>
                                <th class="px-5 py-3 text-center">Arus</th>
                                <th class="px-5 py-3 text-left">Kategori &amp; Keterangan</th>
                                <th class="px-5 py-3 text-left">Diinput Oleh</th>
                                <th class="px-5 py-3 text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($daftarTransaksi as $transaksi)
                                @php
                                    $isMasuk = $transaksi->jenis_arus === \App\Models\TransaksiKasUsaha::ARUS_MASUK;
                                @endphp
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-mono tabular-nums font-semibold text-zinc-950">{{ $transaksi->nomor_transaksi }}</p>
                                        <p class="text-xs text-zinc-500 tabular-nums">{{ $transaksi->tanggal_transaksi->translatedFormat('d M Y') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isMasuk ? 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' : 'bg-rose-500/10 text-rose-700 border-rose-500/20' }}">
                                            {{ $transaksi->jenis_arus }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 font-mono text-[10px] font-semibold text-zinc-700">
                                            {{ $transaksi->kategori }}
                                        </span>
                                        <p class="mt-1 text-xs sm:text-sm text-zinc-700">{{ $transaksi->keterangan }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-600 whitespace-nowrap">
                                        {{ $transaksi->diinputOleh?->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold whitespace-nowrap {{ $isMasuk ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $isMasuk ? '+' : '-' }}{{ format_rupiah($transaksi->nominal) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($daftarTransaksi->hasPages())
                    <div class="px-5 py-4 border-t border-zinc-100">
                        {{ $daftarTransaksi->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
