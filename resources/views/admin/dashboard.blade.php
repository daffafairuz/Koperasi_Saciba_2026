@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header', 'Dashboard Utama Koperasi')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Bento Grid 4 Kartu Metrik Utama Real-Time --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- 1. Hero Card Obsidian-Emerald: Total Kas Koperasi --}}
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-emerald-500/15 border border-emerald-500/25 text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                                <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                            </svg>
                        </div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-300">Total Kas Koperasi</p>
                    </div>
                    <span class="rounded-full bg-emerald-500/15 border border-emerald-500/25 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-300">Likuid</span>
                </div>
                <div>
                    <p class="font-mono tabular-nums text-2xl font-bold tracking-tight text-emerald-400">{{ format_rupiah($totalKasKoperasi) }}</p>
                    <p class="mt-2 text-[11px] text-zinc-400 font-mono tabular-nums">
                        In {{ format_rupiah($totalKasMasuk) }} &middot; Out {{ format_rupiah($totalKasKeluar) }}
                    </p>
                </div>
            </div>

            {{-- 2. Total Dana Simpanan Anggota --}}
            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20" />
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                            </svg>
                        </div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total Dana Simpanan</p>
                    </div>
                    <span class="rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 font-mono text-[10px] font-semibold text-emerald-700">SP+SW</span>
                </div>
                <div>
                    <p class="font-mono tabular-nums text-2xl font-bold tracking-tight text-zinc-950">{{ format_rupiah($totalDanaSimpanan) }}</p>
                    <p class="mt-2 text-[11px] text-zinc-500 font-mono tabular-nums">
                        SP {{ format_rupiah($totalSimpananPokokLunas) }} &middot; SW {{ format_rupiah($totalSimpananWajib) }}
                    </p>
                </div>
            </div>

            {{-- 3. Total Piutang Pinjaman Berjalan --}}
            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2" />
                                <line x1="2" x2="22" y1="10" y2="10" />
                            </svg>
                        </div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Piutang Berjalan</p>
                    </div>
                    <span class="rounded-full bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 font-mono text-[10px] font-semibold text-amber-700">
                        {{ $jumlahPinjamanBerjalan }} Aktif
                    </span>
                </div>
                <div>
                    <p class="font-mono tabular-nums text-2xl font-bold tracking-tight text-amber-700">{{ format_rupiah($totalPiutangPinjaman) }}</p>
                    <p class="mt-2 text-xs text-zinc-500">Akumulasi sisa tagihan seluruh kontrak berjalan.</p>
                </div>
            </div>

            {{-- 4. Statistik Keanggotaan --}}
            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs flex flex-col justify-between gap-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-8 rounded-xl bg-zinc-100 border border-zinc-200/80 text-zinc-700 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            </svg>
                        </div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Statistik Anggota</p>
                    </div>
                    <span class="rounded-full bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 font-mono text-[10px] font-semibold text-zinc-700">
                        Total {{ $totalAnggota }}
                    </span>
                </div>
                <div>
                    <div class="flex items-baseline gap-4">
                        <div>
                            <span class="font-mono tabular-nums text-2xl font-bold text-emerald-600">{{ $jumlahAnggotaAktif }}</span>
                            <span class="ml-1 text-xs font-medium text-zinc-500">Aktif</span>
                        </div>
                        <div class="h-5 w-px bg-zinc-200"></div>
                        <div>
                            <span class="font-mono tabular-nums text-2xl font-bold text-zinc-500">{{ $jumlahAnggotaKeluar }}</span>
                            <span class="ml-1 text-xs font-medium text-zinc-500">Keluar</span>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-zinc-500">Data anggota KELUAR tetap tersimpan untuk audit.</p>
                </div>
            </div>
        </section>

        {{-- Top 5 Piutang Terbesar & 5 Transaksi Setoran Terbaru --}}
        <section class="grid grid-cols-1 2xl:grid-cols-2 gap-6">
            {{-- Top 5 Pinjaman Aktif Terbesar --}}
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Top 5 Piutang Terbesar</h2>
                        <p class="text-xs text-zinc-500">Pinjaman berstatus BERJALAN dengan sisa tagihan tertinggi.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-amber-500/10 border border-amber-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-amber-700">
                        Berjalan
                    </span>
                </div>

                @if ($topPinjamanAktif->isEmpty())
                    <div class="p-8 text-center text-sm text-zinc-500">
                        Tidak ada pinjaman yang sedang berjalan.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                            <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">Anggota</th>
                                    <th class="px-5 py-3 text-left">Pinjaman</th>
                                    <th class="px-5 py-3 text-right">Total</th>
                                    <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 bg-white">
                                @foreach ($topPinjamanAktif as $pinjaman)
                                    <tr class="hover:bg-zinc-50/70 transition">
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <p class="font-semibold text-zinc-950">{{ $pinjaman->anggota->nama_lengkap }}</p>
                                            <p class="font-mono tabular-nums text-xs text-zinc-500">{{ $pinjaman->anggota->nomor_anggota }}</p>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                                                    {{ $pinjaman->jenis_pinjaman }}
                                                </span>
                                                <span class="font-mono tabular-nums text-xs font-medium text-zinc-700">{{ $pinjaman->nomor_pinjaman }}</span>
                                            </div>
                                            @if ($pinjaman->nama_barang)
                                                <p class="text-xs text-zinc-500 mt-0.5">{{ $pinjaman->nama_barang }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono tabular-nums text-zinc-600 whitespace-nowrap">
                                            {{ format_rupiah($pinjaman->total_tagihan) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold text-rose-600 whitespace-nowrap">
                                            {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- 5 Transaksi Pembayaran Anggota Terbaru --}}
            <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Transaksi Setoran Terbaru</h2>
                        <p class="text-xs text-zinc-500">5 struk pembayaran anggota terakhir yang masuk ke koperasi.</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                        Terbaru
                    </span>
                </div>

                @if ($pembayaranTerbaru->isEmpty())
                    <div class="p-8 text-center text-sm text-zinc-500">
                        Belum ada transaksi pembayaran anggota.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                            <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">Transaksi</th>
                                    <th class="px-5 py-3 text-left">Anggota</th>
                                    <th class="px-5 py-3 text-center">Metode</th>
                                    <th class="px-5 py-3 text-right">Total Bayar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 bg-white">
                                @foreach ($pembayaranTerbaru as $pembayaran)
                                    <tr class="hover:bg-zinc-50/70 transition">
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <p class="font-mono tabular-nums font-semibold text-zinc-950">{{ $pembayaran->nomor_transaksi }}</p>
                                            <p class="text-xs text-zinc-500 tabular-nums">
                                                {{ $pembayaran->tanggal_bayar->translatedFormat('d M Y') }} &middot; {{ $pembayaran->detailPembayaran->count() }} item
                                            </p>
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <p class="font-medium text-zinc-900">{{ $pembayaran->anggota->nama_lengkap }}</p>
                                            <p class="font-mono tabular-nums text-xs text-zinc-500">{{ $pembayaran->anggota->nomor_anggota }}</p>
                                        </td>
                                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 text-[11px] font-semibold text-zinc-700">
                                                {{ $pembayaran->metode_bayar }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold text-emerald-600 whitespace-nowrap">
                                            {{ format_rupiah($pembayaran->total_bayar) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        {{-- Daftar Anggota Keluar Terbaru --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Daftar Anggota Keluar Terbaru</h2>
                    <p class="text-xs text-zinc-500">Log audit perubahan status keanggotaan menjadi KELUAR (soft-exit).</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-rose-500/10 border border-rose-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700">
                    Audit Status
                </span>
            </div>

            @if ($anggotaKeluarTerbaru->isEmpty())
                <div class="p-8 text-center text-sm text-zinc-500">
                    Belum ada riwayat anggota keluar.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Anggota</th>
                                <th class="px-5 py-3 text-left">Tanggal Keluar</th>
                                <th class="px-5 py-3 text-left">Alasan</th>
                                <th class="px-5 py-3 text-left">Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($anggotaKeluarTerbaru as $riwayat)
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-zinc-950">{{ $riwayat->anggota->nama_lengkap }}</p>
                                        <p class="font-mono tabular-nums text-xs text-zinc-500">{{ $riwayat->anggota->nomor_anggota }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-700 tabular-nums whitespace-nowrap">
                                        {{ $riwayat->anggota->tanggal_keluar ? $riwayat->anggota->tanggal_keluar->translatedFormat('d M Y') : ($riwayat->tanggal_disetujui ? $riwayat->tanggal_disetujui->translatedFormat('d M Y') : '-') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs sm:text-sm text-zinc-600">
                                        {{ $riwayat->alasan ?: '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-700 whitespace-nowrap">
                                        {{ $riwayat->disetujuiOleh?->nama_lengkap ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
