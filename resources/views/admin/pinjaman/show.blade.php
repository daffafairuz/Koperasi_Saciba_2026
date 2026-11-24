@extends('layouts.admin')

@section('title', 'Detail Pinjaman — '.$pinjaman->nomor_pinjaman)
@section('header', 'Detail Kontrak & Jadwal Cicilan')

@section('content')
    @php
        $isBarang = $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG;
        $isBerjalan = $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN;
        $jumlahCicilanLunas = $pinjaman->cicilan->where('status', \App\Models\CicilanPinjaman::STATUS_LUNAS)->count();
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header Kontrak --}}
        <section class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.pinjaman.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700">
                            &larr; Daftar Pinjaman
                        </a>
                        <span class="text-slate-300">/</span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBarang ? 'bg-purple-100 text-purple-800 border-purple-200' : 'bg-blue-100 text-blue-800 border-blue-200' }}">
                            PINJAMAN {{ $pinjaman->jenis_pinjaman }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBerjalan ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200' }}">
                            {{ $pinjaman->status }}
                        </span>
                        @if ($pinjaman->is_migrasi)
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                Pinjaman Migrasi
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-2 text-2xl font-bold text-slate-900">
                        {{ $pinjaman->nomor_pinjaman }}
                        @if ($isBarang && $pinjaman->nama_barang)
                            <span class="font-medium text-slate-600">— {{ $pinjaman->nama_barang }}</span>
                        @endif
                    </h2>

                    <p class="mt-1 text-sm text-slate-600">
                        Peminjam:
                        <a href="{{ route('admin.anggota.show', $pinjaman->anggota) }}" class="font-semibold text-emerald-700 hover:underline">
                            {{ $pinjaman->anggota->nama_lengkap }} ({{ $pinjaman->anggota->nomor_anggota }})
                        </a>
                        &middot; Tanggal Cair: <span class="font-medium">{{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }}</span>
                        @if ($pinjaman->dicairkanOleh)
                            &middot; Dicairkan oleh: {{ $pinjaman->dicairkanOleh->nama_lengkap }}
                        @endif
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-right shrink-0">
                    <p class="text-xs text-slate-500">Progres Tenor</p>
                    <p class="text-lg font-bold text-slate-900">{{ $jumlahCicilanLunas }} / {{ $pinjaman->tenor_bulan }} Bulan</p>
                </div>
            </div>
        </section>

        {{-- 4 Kartu Keuangan Kontrak --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pokok Pinjaman</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ format_rupiah($pinjaman->pokok_pinjaman) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $pinjaman->is_migrasi ? 'Tidak memotong kas berjalan.' : 'Memotong kas koperasi.' }}</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Margin ({{ number_format((float) $pinjaman->persen_margin, 2, ',', '.') }}%)</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ format_rupiah($nominalMargin) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $isBarang ? 'Margin flat pinjaman barang.' : 'Pinjaman uang 0% syariah.' }}</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Tagihan</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ format_rupiah($pinjaman->total_tagihan) }}</p>
                <p class="mt-1 text-xs text-slate-500">Angsuran dasar {{ format_rupiah($pinjaman->cicilan_per_bulan) }}/bln.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sisa Tagihan</p>
                <p class="mt-2 text-2xl font-bold {{ $isBerjalan ? 'text-rose-600' : 'text-emerald-700' }}">{{ format_rupiah($pinjaman->sisa_tagihan) }}</p>
                <p class="mt-1 text-xs text-slate-500">Status kontrak: {{ $pinjaman->status }}.</p>
            </div>
        </section>

        {{-- Tabel Jadwal Cicilan Lengkap --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Jadwal Angsuran ({{ $pinjaman->cicilan->count() }} Cicilan)</h3>
                <p class="text-xs text-slate-500">Total seluruh nominal tagihan cicilan selalu sama persis dengan total tagihan kontrak.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Cicilan Ke</th>
                            <th class="px-5 py-3 text-left">Jatuh Tempo</th>
                            <th class="px-5 py-3 text-right">Nominal Tagihan</th>
                            <th class="px-5 py-3 text-right">Nominal Terbayar</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-left">Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($pinjaman->cicilan as $cicilan)
                            @php
                                $badgeClass = match ($cicilan->status) {
                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'bg-amber-100 text-amber-800 border-amber-200',
                                    default => 'bg-rose-100 text-rose-800 border-rose-200',
                                };
                                $badgeLabel = match ($cicilan->status) {
                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'LUNAS',
                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'SEBAGIAN',
                                    default => 'BELUM BAYAR',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                    Cicilan ke-{{ $cicilan->cicilan_ke }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                    {{ $cicilan->jatuh_tempo->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-medium text-slate-900 whitespace-nowrap">
                                    {{ format_rupiah($cicilan->nominal_tagihan) }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-medium text-emerald-700 whitespace-nowrap">
                                    {{ format_rupiah($cicilan->nominal_terbayar) }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">
                                    {{ $cicilan->tanggal_bayar ? $cicilan->tanggal_bayar->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-semibold text-slate-900">
                        <tr>
                            <td colspan="2" class="px-5 py-3.5 text-left">Total Kontrak</td>
                            <td class="px-5 py-3.5 text-right">{{ format_rupiah($pinjaman->total_tagihan) }}</td>
                            <td class="px-5 py-3.5 text-right text-emerald-700">
                                {{ format_rupiah((float) $pinjaman->total_tagihan - (float) $pinjaman->sisa_tagihan) }}
                            </td>
                            <td colspan="2" class="px-5 py-3.5 text-right text-xs text-slate-500">
                                Sisa: {{ format_rupiah($pinjaman->sisa_tagihan) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
@endsection
