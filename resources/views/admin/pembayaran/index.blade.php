@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran Anggota')
@section('header', 'Pembayaran Gabungan (Bulk Payment)')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Riwayat Struk Pembayaran Anggota</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Satu struk dapat menggabungkan Simpanan Pokok, Simpanan Wajib (termasuk bayar di muka), dan Cicilan Pinjaman.
                </p>
            </div>
            <a
                href="{{ route('admin.pembayaran.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition self-start sm:self-auto"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                <span>Input Pembayaran Baru</span>
            </a>
        </div>

        {{-- Filter Bar --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.pembayaran.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                    </div>
                    <input
                        type="search"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Cari nomor transaksi, nama anggota, atau nomor anggota..."
                        class="w-full rounded-xl border border-zinc-200 pl-10 pr-4 py-2 text-sm text-zinc-950 placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <select
                        name="metode_bayar"
                        class="rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm text-zinc-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                        <option value="SEMUA" @selected($metodeFilter === 'SEMUA')>Semua Metode</option>
                        <option value="{{ \App\Models\PembayaranAnggota::METODE_TUNAI }}" @selected($metodeFilter === \App\Models\PembayaranAnggota::METODE_TUNAI)>TUNAI</option>
                        <option value="{{ \App\Models\PembayaranAnggota::METODE_TRANSFER }}" @selected($metodeFilter === \App\Models\PembayaranAnggota::METODE_TRANSFER)>TRANSFER</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-sm font-semibold text-white transition cursor-pointer"
                    >
                        Cari
                    </button>

                    @if ($keyword !== '' || $metodeFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.pembayaran.index') }}"
                            class="rounded-xl border border-zinc-200 px-3.5 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Riwayat Struk --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            @if ($daftarPembayaran->isEmpty())
                <div class="p-10 text-center text-sm text-zinc-500">
                    Tidak ada riwayat struk pembayaran yang sesuai dengan filter.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
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
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($daftarPembayaran as $pembayaran)
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-mono tabular-nums font-semibold text-zinc-950">{{ $pembayaran->nomor_transaksi }}</p>
                                        <p class="text-xs text-zinc-500 tabular-nums">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-zinc-900">{{ $pembayaran->anggota->nama_lengkap }}</p>
                                        <p class="font-mono tabular-nums text-xs text-zinc-500">{{ $pembayaran->anggota->nomor_anggota }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2.5 py-0.5 text-[11px] font-semibold text-zinc-700">
                                            {{ $pembayaran->metode_bayar }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                            {{ $pembayaran->detail_pembayaran_count }} alokasi
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-600 whitespace-nowrap">
                                        {{ $pembayaran->diinputOleh?->nama_lengkap ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold text-emerald-600 whitespace-nowrap">
                                        {{ format_rupiah($pembayaran->total_bayar) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.pembayaran.show', $pembayaran) }}"
                                            class="inline-flex items-center rounded-lg border border-zinc-200/90 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition"
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
                    <div class="px-5 py-4 border-t border-zinc-100">
                        {{ $daftarPembayaran->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
