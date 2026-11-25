@extends('layouts.anggota')

@section('title', 'Beranda Anggota')

@section('content')
    <div class="space-y-6">
        {{-- Bento Grid Top: Hero Card (Total Simpanan Akhir) + 2 Metric Cards --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            {{-- Obsidian-Emerald Hero Card: Total Simpanan Akhir --}}
            <div class="lg:col-span-7 relative overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-6 shadow-sm flex flex-col justify-between gap-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Portal Anggota &middot; {{ $anggota->status }}
                        </div>
                        <h1 class="mt-2.5 text-xl sm:text-2xl font-bold tracking-tight text-white">{{ $anggota->nama_lengkap }}</h1>
                        <p class="mt-0.5 text-xs text-zinc-400">
                            No. Anggota <span class="font-mono tabular-nums text-zinc-200">{{ $anggota->nomor_anggota }}</span>
                            &middot; Gabung <span class="tabular-nums">{{ $anggota->tanggal_gabung->translatedFormat('d M Y') }}</span>
                        </p>
                    </div>
                    <a
                        href="{{ route('anggota.simpanan') }}"
                        class="inline-flex items-center gap-1 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 px-3 py-1.5 text-xs font-semibold text-white transition shrink-0"
                    >
                        <span>Detail</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="m12 5 7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Total Simpanan Akhir</p>
                    <p class="mt-1.5 text-3xl sm:text-4xl font-bold tracking-tight font-mono tabular-nums text-emerald-400">
                        {{ format_rupiah($simpanan?->total_simpanan_akhir ?? 0) }}
                    </p>
                    <p class="mt-1.5 text-xs text-zinc-400">
                        SW terakhir lunas: <span class="font-medium text-zinc-200">{{ format_periode($simpanan?->periode_sw_terakhir) }}</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-white/10">
                    <div class="rounded-xl bg-white/[0.04] border border-white/10 px-3.5 py-2.5">
                        <p class="text-[11px] text-zinc-400">Simpanan Pokok</p>
                        <p class="mt-0.5 font-mono tabular-nums text-sm font-semibold text-white">
                            {{ format_rupiah($simpanan?->nominal_simpanan_pokok ?? 0) }}
                        </p>
                        <p class="mt-0.5 font-mono text-[10px] {{ $simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS ? 'LUNAS' : 'BELUM BAYAR' }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-white/[0.04] border border-white/10 px-3.5 py-2.5">
                        <p class="text-[11px] text-zinc-400">Total Simpanan Wajib</p>
                        <p class="mt-0.5 font-mono tabular-nums text-sm font-semibold text-white">
                            {{ format_rupiah($simpanan?->total_simpanan_wajib ?? 0) }}
                        </p>
                        <p class="mt-0.5 font-mono tabular-nums text-[10px] text-zinc-400">
                            Awal {{ format_rupiah($simpanan?->saldo_sw_awal ?? 0) }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Right Bento Stack: Tunggakan SW & Sisa Pinjaman --}}
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                <a href="{{ route('anggota.simpanan') }}" class="group rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs hover:border-zinc-300 transition flex flex-col justify-between gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="h-9 w-9 rounded-xl {{ $jumlahBulanTunggakan > 0 ? 'bg-rose-500/10 text-rose-600 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' }} border flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" />
                                    <line x1="16" x2="16" y1="2" y2="6" />
                                    <line x1="8" x2="8" y1="2" y2="6" />
                                    <line x1="3" x2="21" y1="10" y2="10" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Tunggakan SW</p>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $jumlahBulanTunggakan > 0 ? 'bg-rose-500/10 text-rose-700 border-rose-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                            {{ $jumlahBulanTunggakan > 0 ? $jumlahBulanTunggakan.' Bulan' : 'Lunas' }}
                        </span>
                    </div>

                    <div>
                        <p class="font-mono tabular-nums text-2xl font-bold tracking-tight {{ $jumlahBulanTunggakan > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            {{ format_rupiah($totalTunggakanSw) }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ $jumlahBulanTunggakan > 0 ? 'Selesaikan melalui pengurus koperasi.' : 'Tidak ada tunggakan Simpanan Wajib.' }}
                        </p>
                    </div>
                </a>

                <a href="{{ route('anggota.pinjaman') }}" class="group rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs hover:border-zinc-300 transition flex flex-col justify-between gap-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="h-9 w-9 rounded-xl bg-amber-500/10 text-amber-700 border border-amber-500/20 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="5" rx="2" />
                                    <line x1="2" x2="22" y1="10" y2="10" />
                                </svg>
                            </div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Sisa Pinjaman</p>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $jumlahPinjamanAktif > 0 ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                            {{ $jumlahPinjamanAktif }} Aktif
                        </span>
                    </div>

                    <div>
                        <p class="font-mono tabular-nums text-2xl font-bold tracking-tight text-zinc-950">
                            {{ format_rupiah($totalSisaPinjaman) }}
                        </p>
                        <p class="mt-1 text-xs text-zinc-500">
                            {{ $jumlahPinjamanAktif > 0 ? 'Lihat progres & jadwal angsuran Anda.' : 'Tidak ada pinjaman berjalan saat ini.' }}
                        </p>
                    </div>
                </a>
            </div>
        </section>

        {{-- 3 Transaksi Terakhir (Modern Banking Feed) --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-lg bg-zinc-100 text-zinc-700 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                            <path d="M12 17.5v-11" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold tracking-tight text-zinc-950">3 Transaksi Terakhir</h2>
                        <p class="text-xs text-zinc-500">Setoran terbaru yang tercatat di buku kas koperasi.</p>
                    </div>
                </div>
                <a href="{{ route('anggota.riwayat') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition">
                    <span>Semua Riwayat</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </a>
            </div>

            @if ($pembayaranTerakhir->isEmpty())
                <div class="p-10 text-center text-sm text-zinc-500">
                    Belum ada riwayat pembayaran yang tercatat.
                </div>
            @else
                <div class="divide-y divide-zinc-100">
                    @foreach ($pembayaranTerakhir as $pembayaran)
                        <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-zinc-50/70 transition">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 19V5" />
                                        <path d="m5 12 7-7 7 7" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono tabular-nums text-sm font-semibold text-zinc-950">{{ $pembayaran->nomor_transaksi }}</span>
                                        <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 text-[10px] font-semibold text-zinc-700">
                                            {{ $pembayaran->metode_bayar }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                            {{ $pembayaran->detailPembayaran->count() }} pos
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-zinc-500 truncate">
                                        <span class="tabular-nums">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}</span>
                                        @if ($pembayaran->diinputOleh)
                                            &middot; Petugas: {{ $pembayaran->diinputOleh->nama_lengkap }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <p class="font-mono tabular-nums text-sm sm:text-base font-bold text-emerald-600 shrink-0">
                                {{ format_rupiah($pembayaran->total_bayar) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
