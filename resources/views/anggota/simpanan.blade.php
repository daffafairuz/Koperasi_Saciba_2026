@extends('layouts.anggota')

@section('title', 'Simpanan Saya')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-emerald-600">Dompet Keanggotaan</p>
            <h1 class="mt-1 text-xl sm:text-2xl font-bold tracking-tight text-zinc-950">Simpanan Saya</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Rekap Simpanan Pokok, Simpanan Wajib, dan status tagihan bulanan Anda.</p>
        </div>

        {{-- Bento Grid Ringkasan Simpanan --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Total Simpanan Akhir</p>
                <p class="mt-2.5 font-mono tabular-nums text-2xl font-bold text-emerald-400">
                    {{ format_rupiah($simpanan?->total_simpanan_akhir ?? 0) }}
                </p>
                <p class="mt-2 text-xs text-zinc-400">
                    SW terakhir: <span class="font-medium text-zinc-200">{{ format_periode($simpanan?->periode_sw_terakhir) }}</span>
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Simpanan Pokok</p>
                    @if ($simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS)
                        <span class="inline-flex items-center rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                            LUNAS
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                            BELUM BAYAR
                        </span>
                    @endif
                </div>
                <p class="mt-2.5 font-mono tabular-nums text-xl font-bold text-zinc-950">
                    {{ format_rupiah($simpanan?->nominal_simpanan_pokok ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500 tabular-nums">
                    @if ($simpanan?->tanggal_lunas_pokok)
                        Lunas {{ $simpanan->tanggal_lunas_pokok->translatedFormat('d M Y') }}
                    @else
                        Menunggu pelunasan
                    @endif
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Saldo SW Awal</p>
                <p class="mt-2.5 font-mono tabular-nums text-xl font-bold text-zinc-950">
                    {{ format_rupiah($simpanan?->saldo_sw_awal ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500">Saldo tutup buku migrasi Excel.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total SW Berjalan</p>
                <p class="mt-2.5 font-mono tabular-nums text-xl font-bold text-zinc-950">
                    {{ format_rupiah($simpanan?->total_simpanan_wajib ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500 tabular-nums">
                    <span class="text-emerald-700 font-semibold">{{ $jumlahLunas }}</span> lunas &middot;
                    <span class="{{ $jumlahBelumLunas > 0 ? 'text-rose-600 font-semibold' : 'text-zinc-500' }}">{{ $jumlahBelumLunas }}</span> belum lunas
                </p>
            </div>
        </section>

        {{-- Daftar Tagihan Simpanan Wajib --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                <div>
                    <h2 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Daftar Tagihan Simpanan Wajib</h2>
                    <p class="text-xs text-zinc-500">Diurutkan dari periode terbaru ke terlama.</p>
                </div>
                @if ($jumlahBelumLunas > 0)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 border border-rose-500/20 px-3 py-1 text-xs font-semibold text-rose-700 self-start sm:self-auto">
                        <span>Tunggakan:</span>
                        <span class="font-mono tabular-nums">{{ format_rupiah($totalTunggakanSw) }}</span>
                    </span>
                @endif
            </div>

            @if ($daftarTagihanSw->isEmpty())
                <div class="p-10 text-center text-sm text-zinc-500">
                    Belum ada tagihan Simpanan Wajib yang tercatat.
                </div>
            @else
                {{-- Mobile Card List --}}
                <div class="sm:hidden divide-y divide-zinc-100">
                    @foreach ($daftarTagihanSw as $tagihan)
                        @php
                            $badgeClass = match ($tagihan->status) {
                                \App\Models\TagihanSimpananWajib::STATUS_LUNAS => 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20',
                                \App\Models\TagihanSimpananWajib::STATUS_SEBAGIAN => 'bg-amber-500/10 text-amber-700 border-amber-500/20',
                                default => 'bg-rose-500/10 text-rose-700 border-rose-500/20',
                            };
                            $badgeLabel = match ($tagihan->status) {
                                \App\Models\TagihanSimpananWajib::STATUS_LUNAS => 'LUNAS',
                                \App\Models\TagihanSimpananWajib::STATUS_SEBAGIAN => 'SEBAGIAN',
                                default => 'BELUM BAYAR',
                            };
                        @endphp
                        <div class="p-4 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-950">{{ format_periode($tagihan->periode) }}</p>
                                    <p class="font-mono tabular-nums text-[11px] text-zinc-400">{{ $tagihan->periode }}</p>
                                </div>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1">
                                <span class="text-zinc-500">Tagihan <span class="font-mono tabular-nums font-medium text-zinc-800">{{ format_rupiah($tagihan->nominal_tagihan) }}</span></span>
                                <span class="text-zinc-500">Terbayar <span class="font-mono tabular-nums font-semibold text-emerald-700">{{ format_rupiah($tagihan->nominal_terbayar) }}</span></span>
                            </div>
                            @if ($tagihan->tanggal_lunas)
                                <p class="text-[11px] text-zinc-400 tabular-nums">Lunas {{ $tagihan->tanggal_lunas->translatedFormat('d M Y') }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Desktop Table --}}
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Periode</th>
                                <th class="px-5 py-3 text-right">Tagihan</th>
                                <th class="px-5 py-3 text-right">Terbayar</th>
                                <th class="px-5 py-3 text-right">Sisa</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-left">Tanggal Lunas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($daftarTagihanSw as $tagihan)
                                @php
                                    $badgeClass = match ($tagihan->status) {
                                        \App\Models\TagihanSimpananWajib::STATUS_LUNAS => 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20',
                                        \App\Models\TagihanSimpananWajib::STATUS_SEBAGIAN => 'bg-amber-500/10 text-amber-700 border-amber-500/20',
                                        default => 'bg-rose-500/10 text-rose-700 border-rose-500/20',
                                    };
                                    $badgeLabel = match ($tagihan->status) {
                                        \App\Models\TagihanSimpananWajib::STATUS_LUNAS => 'LUNAS',
                                        \App\Models\TagihanSimpananWajib::STATUS_SEBAGIAN => 'SEBAGIAN',
                                        default => 'BELUM BAYAR',
                                    };
                                @endphp
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-zinc-950">{{ format_periode($tagihan->periode) }}</p>
                                        <p class="font-mono tabular-nums text-[11px] text-zinc-400">{{ $tagihan->periode }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums text-zinc-700 whitespace-nowrap">
                                        {{ format_rupiah($tagihan->nominal_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-semibold text-emerald-700 whitespace-nowrap">
                                        {{ format_rupiah($tagihan->nominal_terbayar) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-medium {{ (float) $tagihan->sisa_tagihan > 0 ? 'text-rose-600' : 'text-zinc-400' }} whitespace-nowrap">
                                        {{ format_rupiah($tagihan->sisa_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-zinc-500 tabular-nums whitespace-nowrap">
                                        {{ $tagihan->tanggal_lunas ? $tagihan->tanggal_lunas->translatedFormat('d M Y') : '-' }}
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
