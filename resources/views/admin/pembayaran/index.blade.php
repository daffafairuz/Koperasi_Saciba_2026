@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran Anggota')
@section('header', 'Pembayaran Gabungan (Bulk Payment)')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Riwayat Struk Pembayaran Anggota</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Satu struk dapat menggabungkan pelunasan Simpanan Pokok, Simpanan Wajib (termasuk bayar di muka), dan Cicilan Pinjaman.
                </p>
            </div>
            <a
                href="{{ route('admin.pembayaran.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition self-start sm:self-auto"
            >
                <span>+ Input Pembayaran Baru</span>
            </a>
        </div>

        {{-- Filter Bar --}}
        <section class="rounded-2xl bg-white border border-slate-200 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.pembayaran.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="flex-1">
                    <input
                        type="search"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Cari nomor transaksi, nama anggota, atau nomor anggota..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <select
                        name="metode_bayar"
                        class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="SEMUA" @selected($metodeFilter === 'SEMUA')>Semua Metode</option>
                        <option value="{{ \App\Models\PembayaranAnggota::METODE_TUNAI }}" @selected($metodeFilter === \App\Models\PembayaranAnggota::METODE_TUNAI)>TUNAI</option>
                        <option value="{{ \App\Models\PembayaranAnggota::METODE_TRANSFER }}" @selected($metodeFilter === \App\Models\PembayaranAnggota::METODE_TRANSFER)>TRANSFER</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition cursor-pointer"
                    >
                        Cari
                    </button>

                    @if ($keyword !== '' || $metodeFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.pembayaran.index') }}"
                            class="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Riwayat Struk --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            @if ($daftarPembayaran->isEmpty())
                <div class="p-10 text-center text-sm text-slate-500">
                    Tidak ada riwayat struk pembayaran yang sesuai dengan filter.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Nomor &amp; Tanggal</th>
                                <th class="px-5 py-3 text-left">Anggota</th>
                                <th class="px-5 py-3 text-center">Metode</th>
                                <th class="px-5 py-3 text-center">Item</th>
                                <th class="px-5 py-3 text-left">Petugas</th>
                                <th class="px-5 py-3 text-right">Total Bayar</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($daftarPembayaran as $pembayaran)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-slate-900">{{ $pembayaran->nomor_transaksi }}</p>
                                        <p class="text-xs text-slate-500">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-slate-800">{{ $pembayaran->anggota->nama_lengkap }}</p>
                                        <p class="text-xs text-slate-500">{{ $pembayaran->anggota->nomor_anggota }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-700">
                                            {{ $pembayaran->metode_bayar }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                            {{ $pembayaran->detail_pembayaran_count }} alokasi
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        {{ $pembayaran->diinputOleh?->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-emerald-700 whitespace-nowrap">
                                        {{ format_rupiah($pembayaran->total_bayar) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.pembayaran.show', $pembayaran) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-700 transition"
                                        >
                                            Lihat Struk
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($daftarPembayaran->hasPages())
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $daftarPembayaran->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
