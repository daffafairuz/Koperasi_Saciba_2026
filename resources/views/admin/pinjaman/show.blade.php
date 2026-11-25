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
        <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.pinjaman.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 19-7-7 7-7" />
                                <path d="M19 12H5" />
                            </svg>
                            <span>Daftar Pinjaman</span>
                        </a>
                        <span class="text-zinc-300">/</span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBarang ? 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                            PINJAMAN {{ $pinjaman->jenis_pinjaman }}
                        </span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $isBerjalan ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                            {{ $pinjaman->status }}
                        </span>
                        @if ($pinjaman->is_migrasi)
                            <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 text-[11px] font-medium text-zinc-600">
                                Pinjaman Migrasi
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-2.5 text-2xl font-bold tracking-tight text-zinc-950">
                        <span class="font-mono tabular-nums">{{ $pinjaman->nomor_pinjaman }}</span>
                        @if ($isBarang && $pinjaman->nama_barang)
                            <span class="font-semibold text-zinc-600">— {{ $pinjaman->nama_barang }}</span>
                        @endif
                    </h2>

                    <p class="mt-1 text-sm text-zinc-600">
                        Peminjam:
                        <a href="{{ route('admin.anggota.show', $pinjaman->anggota) }}" class="font-semibold text-emerald-700 hover:underline">
                            {{ $pinjaman->anggota->nama_lengkap }}
                            (<span class="font-mono tabular-nums">{{ $pinjaman->anggota->nomor_anggota }}</span>)
                        </a>
                        &middot; Tanggal Cair: <span class="font-medium tabular-nums">{{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }}</span>
                        @if ($pinjaman->dicairkanOleh)
                            &middot; Dicairkan oleh: {{ $pinjaman->dicairkanOleh->nama_lengkap }}
                        @endif
                    </p>
                </div>

                <div class="rounded-xl bg-zinc-50 border border-zinc-200/80 px-4 py-3 text-right shrink-0">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Progres Tenor</p>
                    <p class="mt-0.5 font-mono tabular-nums text-lg font-bold text-zinc-950">{{ $jumlahCicilanLunas }} / {{ $pinjaman->tenor_bulan }} Bulan</p>
                </div>
            </div>
        </section>

        {{-- 4 Kartu Keuangan Kontrak --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Pokok Pinjaman</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-zinc-950">{{ format_rupiah($pinjaman->pokok_pinjaman) }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $pinjaman->is_migrasi ? 'Tidak memotong kas berjalan.' : 'Memotong kas koperasi.' }}</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Margin ({{ number_format((float) $pinjaman->persen_margin, 2, ',', '.') }}%)</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-zinc-950">{{ format_rupiah($nominalMargin) }}</p>
                <p class="mt-1 text-xs text-zinc-500">{{ $isBarang ? 'Margin flat pinjaman barang.' : 'Pinjaman uang 0% syariah.' }}</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total Tagihan</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-zinc-950">{{ format_rupiah($pinjaman->total_tagihan) }}</p>
                <p class="mt-1 text-xs text-zinc-500 font-mono tabular-nums">Dasar {{ format_rupiah($pinjaman->cicilan_per_bulan) }}/bln</p>
            </div>

            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Sisa Tagihan</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold {{ $isBerjalan ? 'text-amber-300' : 'text-emerald-400' }}">{{ format_rupiah($pinjaman->sisa_tagihan) }}</p>
                <p class="mt-1 text-xs text-zinc-400">Status kontrak: {{ $pinjaman->status }}.</p>
            </div>
        </section>

        {{-- Tabel Jadwal Cicilan Lengkap --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h3 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Jadwal Angsuran ({{ $pinjaman->cicilan->count() }} Cicilan)</h3>
                <p class="text-xs text-zinc-500">Total seluruh nominal tagihan cicilan selalu sama persis dengan total tagihan kontrak.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                    <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th class="px-5 py-3 text-left">Cicilan Ke</th>
                            <th class="px-5 py-3 text-left">Jatuh Tempo</th>
                            <th class="px-5 py-3 text-right">Nominal Tagihan</th>
                            <th class="px-5 py-3 text-right">Nominal Terbayar</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-left">Tanggal Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @foreach ($pinjaman->cicilan as $cicilan)
                            @php
                                $badgeClass = match ($cicilan->status) {
                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20',
                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'bg-amber-500/10 text-amber-700 border-amber-500/20',
                                    default => 'bg-rose-500/10 text-rose-700 border-rose-500/20',
                                };
                                $badgeLabel = match ($cicilan->status) {
                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'LUNAS',
                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'SEBAGIAN',
                                    default => 'BELUM BAYAR',
                                };
                            @endphp
                            <tr class="hover:bg-zinc-50/70 transition">
                                <td class="px-5 py-3.5 font-mono tabular-nums font-semibold text-zinc-950 whitespace-nowrap">
                                    Cicilan ke-{{ $cicilan->cicilan_ke }}
                                </td>
                                <td class="px-5 py-3.5 text-zinc-700 tabular-nums whitespace-nowrap">
                                    {{ $cicilan->jatuh_tempo->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums font-medium text-zinc-900 whitespace-nowrap">
                                    {{ format_rupiah($cicilan->nominal_tagihan) }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono tabular-nums font-semibold text-emerald-700 whitespace-nowrap">
                                    {{ format_rupiah($cicilan->nominal_terbayar) }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">
                                        {{ $badgeLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-zinc-500 tabular-nums whitespace-nowrap">
                                    {{ $cicilan->tanggal_bayar ? $cicilan->tanggal_bayar->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-zinc-50/90 font-semibold text-zinc-950 border-t border-zinc-200/80">
                        <tr>
                            <td colspan="2" class="px-5 py-3.5 text-left">Total Kontrak</td>
                            <td class="px-5 py-3.5 text-right font-mono tabular-nums">{{ format_rupiah($pinjaman->total_tagihan) }}</td>
                            <td class="px-5 py-3.5 text-right font-mono tabular-nums text-emerald-700">
                                {{ format_rupiah((float) $pinjaman->total_tagihan - (float) $pinjaman->sisa_tagihan) }}
                            </td>
                            <td colspan="2" class="px-5 py-3.5 text-right text-xs text-zinc-500 font-mono tabular-nums">
                                Sisa: {{ format_rupiah($pinjaman->sisa_tagihan) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
@endsection
