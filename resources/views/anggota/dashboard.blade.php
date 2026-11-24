@extends('layouts.anggota')

@section('title', 'Beranda Anggota')

@section('content')
    <div class="space-y-6">
        {{-- Hero Greeting --}}
        <section class="rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-6 shadow-md shadow-emerald-600/15">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wider text-emerald-100">
                        Portal Anggota &middot; Read-Only
                    </p>
                    <h1 class="mt-1 text-2xl font-bold">{{ $anggota->nama_lengkap }}</h1>
                    <p class="mt-1 text-sm text-emerald-50">
                        No. Anggota: <span class="font-semibold">{{ $anggota->nomor_anggota }}</span>
                        &middot; Bergabung sejak {{ $anggota->tanggal_gabung->translatedFormat('d M Y') }}
                    </p>
                </div>
                <div class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-3.5 py-2 text-xs font-medium backdrop-blur-xs self-start sm:self-auto">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    <span>Status: {{ $anggota->status }}</span>
                </div>
            </div>
        </section>

        {{-- 3 Summary Cards --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('anggota.simpanan') }}" class="group rounded-2xl bg-white border border-slate-200 p-5 shadow-xs hover:border-emerald-300 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Simpanan Akhir</p>
                    <span class="rounded-lg bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Simpanan</span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">
                    {{ format_rupiah($simpanan?->total_simpanan_akhir ?? 0) }}
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    SW terakhir lunas: <span class="font-medium text-slate-700">{{ format_periode($simpanan?->periode_sw_terakhir) }}</span>
                </p>
            </a>

            <a href="{{ route('anggota.simpanan') }}" class="group rounded-2xl bg-white border border-slate-200 p-5 shadow-xs hover:border-amber-300 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tunggakan Simpanan Wajib</p>
                    <span class="rounded-lg px-2 py-0.5 text-[11px] font-semibold {{ $jumlahBulanTunggakan > 0 ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700' }}">
                        {{ $jumlahBulanTunggakan > 0 ? $jumlahBulanTunggakan.' Bulan' : 'Lunas' }}
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold {{ $jumlahBulanTunggakan > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                    {{ format_rupiah($totalTunggakanSw) }}
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ $jumlahBulanTunggakan > 0 ? 'Segera selesaikan melalui pengurus koperasi.' : 'Tidak ada tunggakan Simpanan Wajib.' }}
                </p>
            </a>

            <a href="{{ route('anggota.pinjaman') }}" class="group rounded-2xl bg-white border border-slate-200 p-5 shadow-xs hover:border-sky-300 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sisa Pinjaman Berjalan</p>
                    <span class="rounded-lg bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700">
                        {{ $jumlahPinjamanAktif }} Aktif
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">
                    {{ format_rupiah($totalSisaPinjaman) }}
                </p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ $jumlahPinjamanAktif > 0 ? 'Klik untuk melihat rincian jadwal cicilan.' : 'Tidak ada pinjaman yang sedang berjalan.' }}
                </p>
            </a>
        </section>

        {{-- 3 Transaksi Pembayaran Terakhir --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">3 Pembayaran Terakhir</h2>
                    <p class="text-xs text-slate-500">Ringkasan setoran terbaru yang dicatat pengurus koperasi.</p>
                </div>
                <a href="{{ route('anggota.riwayat') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                    Lihat Semua &rarr;
                </a>
            </div>

            @if ($pembayaranTerakhir->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada riwayat pembayaran yang tercatat.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach ($pembayaranTerakhir as $pembayaran)
                        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-semibold text-slate-900">{{ $pembayaran->nomor_transaksi }}</span>
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                        {{ $pembayaran->metode_bayar }}
                                    </span>
                                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                        {{ $pembayaran->detailPembayaran->count() }} item
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}
                                    @if ($pembayaran->diinputOleh)
                                        &middot; Dicatat oleh {{ $pembayaran->diinputOleh->nama_lengkap }}
                                    @endif
                                </p>
                            </div>
                            <p class="text-base font-bold text-emerald-700 shrink-0">
                                {{ format_rupiah($pembayaran->total_bayar) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
