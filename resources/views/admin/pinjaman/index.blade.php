@extends('layouts.admin')

@section('title', 'Daftar Pinjaman')
@section('header', 'Manajemen Pinjaman & Pencairan')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Daftar Pinjaman Anggota</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Kelola pinjaman uang (syariah 0%) dan pembiayaan barang (unlimited per anggota) beserta jadwal angsurannya.
                </p>
            </div>
            <a
                href="{{ route('admin.pinjaman.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition self-start sm:self-auto"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                <span>Cairkan Pinjaman Baru</span>
            </a>
        </div>

        {{-- 3 Kartu Ringkasan (Bento Grid) --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Kas Tersedia untuk Pencairan</p>
                <p class="mt-2.5 font-mono tabular-nums text-2xl font-bold text-emerald-400">{{ format_rupiah($totalKasKoperasi) }}</p>
                <p class="mt-1.5 text-xs text-zinc-400">Batas maksimal pokok pinjaman baru yang dapat dicairkan.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total Piutang Berjalan</p>
                <p class="mt-2.5 font-mono tabular-nums text-2xl font-bold text-amber-700">{{ format_rupiah($totalPiutangBerjalan) }}</p>
                <p class="mt-1.5 text-xs text-zinc-500">Dari <span class="font-mono tabular-nums font-semibold text-zinc-700">{{ $jumlahBerjalan }}</span> kontrak pinjaman berstatus BERJALAN.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Ringkasan Kontrak</p>
                <div class="mt-2.5 flex items-baseline gap-4">
                    <div>
                        <span class="font-mono tabular-nums text-2xl font-bold text-amber-700">{{ $jumlahBerjalan }}</span>
                        <span class="ml-1 text-xs font-medium text-zinc-500">Berjalan</span>
                    </div>
                    <div class="h-5 w-px bg-zinc-200"></div>
                    <div>
                        <span class="font-mono tabular-nums text-2xl font-bold text-emerald-600">{{ $jumlahLunas }}</span>
                        <span class="ml-1 text-xs font-medium text-zinc-500">Lunas</span>
                    </div>
                </div>
                <p class="mt-1.5 text-xs text-zinc-500">Anggota aktif dapat memiliki lebih dari 2 pinjaman barang.</p>
            </div>
        </section>

        {{-- Filter Bar --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.pinjaman.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
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
                        placeholder="Cari nama/nomor anggota, nomor pinjaman, atau nama barang..."
                        class="w-full rounded-xl border border-zinc-200 pl-10 pr-4 py-2 text-sm text-zinc-950 placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <select
                        name="jenis_pinjaman"
                        class="rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm text-zinc-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                        <option value="SEMUA" @selected($jenisFilter === 'SEMUA')>Semua Jenis</option>
                        <option value="{{ \App\Models\Pinjaman::JENIS_UANG }}" @selected($jenisFilter === \App\Models\Pinjaman::JENIS_UANG)>Pinjaman Uang (0%)</option>
                        <option value="{{ \App\Models\Pinjaman::JENIS_BARANG }}" @selected($jenisFilter === \App\Models\Pinjaman::JENIS_BARANG)>Pinjaman Barang</option>
                    </select>

                    <select
                        name="status"
                        class="rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm text-zinc-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                        <option value="SEMUA" @selected($statusFilter === 'SEMUA')>Semua Status</option>
                        <option value="{{ \App\Models\Pinjaman::STATUS_BERJALAN }}" @selected($statusFilter === \App\Models\Pinjaman::STATUS_BERJALAN)>BERJALAN</option>
                        <option value="{{ \App\Models\Pinjaman::STATUS_LUNAS }}" @selected($statusFilter === \App\Models\Pinjaman::STATUS_LUNAS)>LUNAS</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-sm font-semibold text-white transition cursor-pointer"
                    >
                        Filter
                    </button>

                    @if ($keyword !== '' || $jenisFilter !== 'SEMUA' || $statusFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.pinjaman.index') }}"
                            class="rounded-xl border border-zinc-200 px-3.5 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Daftar Pinjaman --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            @if ($daftarPinjaman->isEmpty())
                <div class="p-10 text-center text-sm text-zinc-500">
                    Tidak ada data pinjaman yang sesuai dengan filter pencarian.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Kontrak &amp; Peminjam</th>
                                <th class="px-5 py-3 text-center">Jenis</th>
                                <th class="px-5 py-3 text-right">Total Tagihan</th>
                                <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                                <th class="px-5 py-3 text-left">Progres Pelunasan</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($daftarPinjaman as $pinjaman)
                                @php
                                    $totalVal = (float) $pinjaman->total_tagihan;
                                    $sisaVal = (float) $pinjaman->sisa_tagihan;
                                    $persenLunas = $totalVal > 0 ? min(100, (int) round((max(0, $totalVal - $sisaVal) / $totalVal) * 100)) : 0;
                                    $isBarang = $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG;
                                    $isBerjalan = $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN;
                                @endphp
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <p class="font-mono tabular-nums font-semibold text-zinc-950">{{ $pinjaman->nomor_pinjaman }}</p>
                                            @if ($pinjaman->is_migrasi)
                                                <span class="rounded-md bg-zinc-100 border border-zinc-200/80 px-1.5 py-0.5 text-[10px] font-medium text-zinc-600">Migrasi</span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-medium text-zinc-800 mt-0.5">
                                            {{ $pinjaman->anggota->nama_lengkap }}
                                            (<span class="font-mono tabular-nums text-zinc-500">{{ $pinjaman->anggota->nomor_anggota }}</span>)
                                        </p>
                                        <p class="text-xs text-zinc-500">
                                            {{ $isBarang && $pinjaman->nama_barang ? $pinjaman->nama_barang.' · ' : '' }}Cair <span class="tabular-nums">{{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }}</span>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBarang ? 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                                            {{ $pinjaman->jenis_pinjaman }}
                                        </span>
                                        <p class="font-mono tabular-nums text-[11px] text-zinc-500 mt-0.5">Margin {{ number_format((float) $pinjaman->persen_margin, 0) }}%</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <p class="font-mono tabular-nums font-semibold text-zinc-950">{{ format_rupiah($pinjaman->total_tagihan) }}</p>
                                        <p class="font-mono tabular-nums text-xs text-zinc-500">Pokok {{ format_rupiah($pinjaman->pokok_pinjaman) }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold whitespace-nowrap {{ $isBerjalan ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 min-w-40">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-mono tabular-nums text-zinc-600">{{ $pinjaman->cicilan_lunas_count }}/{{ $pinjaman->tenor_bulan }} bln</span>
                                            <span class="font-mono tabular-nums font-semibold text-zinc-900">{{ $persenLunas }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-zinc-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $persenLunas }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBerjalan ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                                            {{ $pinjaman->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.pinjaman.show', $pinjaman) }}"
                                            class="inline-flex items-center rounded-lg border border-zinc-200/90 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition"
                                        >
                                            Detail Cicilan
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($daftarPinjaman->hasPages())
                    <div class="px-5 py-4 border-t border-zinc-100">
                        {{ $daftarPinjaman->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
