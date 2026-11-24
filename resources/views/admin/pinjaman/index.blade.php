@extends('layouts.admin')

@section('title', 'Daftar Pinjaman')
@section('header', 'Manajemen Pinjaman & Pencairan')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Daftar Pinjaman Anggota</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Kelola pinjaman uang (syariah 0%) dan pinjaman barang (unlimited per anggota) beserta jadwal angsurannya.
                </p>
            </div>
            <a
                href="{{ route('admin.pinjaman.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition self-start sm:self-auto"
            >
                <span>+ Cairkan Pinjaman Baru</span>
            </a>
        </div>

        {{-- 3 Kartu Ringkasan --}}
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Kas Tersedia untuk Pencairan</p>
                <p class="mt-2.5 text-2xl font-bold">{{ format_rupiah($totalKasKoperasi) }}</p>
                <p class="mt-1.5 text-xs text-emerald-100">Batas maksimal pokok pinjaman baru yang dapat dicairkan.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Piutang Berjalan</p>
                <p class="mt-2.5 text-2xl font-bold text-amber-700">{{ format_rupiah($totalPiutangBerjalan) }}</p>
                <p class="mt-1.5 text-xs text-slate-500">Dari {{ $jumlahBerjalan }} kontrak pinjaman berstatus BERJALAN.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ringkasan Kontrak</p>
                <div class="mt-2.5 flex items-baseline gap-4">
                    <div>
                        <span class="text-2xl font-bold text-amber-700">{{ $jumlahBerjalan }}</span>
                        <span class="ml-1 text-xs font-medium text-slate-500">Berjalan</span>
                    </div>
                    <div class="h-5 w-px bg-slate-200"></div>
                    <div>
                        <span class="text-2xl font-bold text-emerald-700">{{ $jumlahLunas }}</span>
                        <span class="ml-1 text-xs font-medium text-slate-500">Lunas</span>
                    </div>
                </div>
                <p class="mt-1.5 text-xs text-slate-500">Anggota aktif dapat memiliki lebih dari 2 pinjaman barang.</p>
            </div>
        </section>

        {{-- Filter Bar --}}
        <section class="rounded-2xl bg-white border border-slate-200 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.pinjaman.index') }}" class="flex flex-col lg:flex-row lg:items-center gap-3">
                <div class="flex-1">
                    <input
                        type="search"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Cari nama/nomor anggota, nomor pinjaman, atau nama barang..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <select
                        name="jenis_pinjaman"
                        class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="SEMUA" @selected($jenisFilter === 'SEMUA')>Semua Jenis</option>
                        <option value="{{ \App\Models\Pinjaman::JENIS_UANG }}" @selected($jenisFilter === \App\Models\Pinjaman::JENIS_UANG)>Pinjaman Uang (0%)</option>
                        <option value="{{ \App\Models\Pinjaman::JENIS_BARANG }}" @selected($jenisFilter === \App\Models\Pinjaman::JENIS_BARANG)>Pinjaman Barang</option>
                    </select>

                    <select
                        name="status"
                        class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="SEMUA" @selected($statusFilter === 'SEMUA')>Semua Status</option>
                        <option value="{{ \App\Models\Pinjaman::STATUS_BERJALAN }}" @selected($statusFilter === \App\Models\Pinjaman::STATUS_BERJALAN)>BERJALAN</option>
                        <option value="{{ \App\Models\Pinjaman::STATUS_LUNAS }}" @selected($statusFilter === \App\Models\Pinjaman::STATUS_LUNAS)>LUNAS</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition cursor-pointer"
                    >
                        Filter
                    </button>

                    @if ($keyword !== '' || $jenisFilter !== 'SEMUA' || $statusFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.pinjaman.index') }}"
                            class="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </section>

        {{-- Tabel Daftar Pinjaman --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            @if ($daftarPinjaman->isEmpty())
                <div class="p-10 text-center text-sm text-slate-500">
                    Tidak ada data pinjaman yang sesuai dengan filter pencarian.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
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
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($daftarPinjaman as $pinjaman)
                                @php
                                    $totalVal = (float) $pinjaman->total_tagihan;
                                    $sisaVal = (float) $pinjaman->sisa_tagihan;
                                    $persenLunas = $totalVal > 0 ? min(100, (int) round((max(0, $totalVal - $sisaVal) / $totalVal) * 100)) : 0;
                                    $isBarang = $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG;
                                    $isBerjalan = $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN;
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <p class="font-semibold text-slate-900">{{ $pinjaman->nomor_pinjaman }}</p>
                                            @if ($pinjaman->is_migrasi)
                                                <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">Migrasi</span>
                                            @endif
                                        </div>
                                        <p class="text-xs font-medium text-slate-700 mt-0.5">
                                            {{ $pinjaman->anggota->nama_lengkap }} ({{ $pinjaman->anggota->nomor_anggota }})
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            {{ $isBarang && $pinjaman->nama_barang ? $pinjaman->nama_barang.' · ' : '' }}Cair {{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBarang ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-blue-100 text-blue-800 border-blue-200' }}">
                                            {{ $pinjaman->jenis_pinjaman }}
                                        </span>
                                        <p class="text-[11px] text-slate-500 mt-0.5">Margin {{ number_format((float) $pinjaman->persen_margin, 0) }}%</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <p class="font-semibold text-slate-900">{{ format_rupiah($pinjaman->total_tagihan) }}</p>
                                        <p class="text-xs text-slate-500">Pokok {{ format_rupiah($pinjaman->pokok_pinjaman) }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold whitespace-nowrap {{ $isBerjalan ? 'text-rose-600' : 'text-emerald-700' }}">
                                        {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 min-w-40">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-slate-600">{{ $pinjaman->cicilan_lunas_count }}/{{ $pinjaman->tenor_bulan }} bln</span>
                                            <span class="font-semibold text-slate-800">{{ $persenLunas }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $persenLunas }}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBerjalan ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200' }}">
                                            {{ $pinjaman->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a
                                            href="{{ route('admin.pinjaman.show', $pinjaman) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-700 transition"
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
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $daftarPinjaman->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
@endsection
