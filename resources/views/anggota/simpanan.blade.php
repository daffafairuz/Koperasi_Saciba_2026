@extends('layouts.anggota')

@section('title', 'Simpanan Saya')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Simpanan Saya</h1>
            <p class="text-sm text-slate-500 mt-0.5">Rekap Simpanan Pokok, Simpanan Wajib, dan status tagihan bulanan Anda.</p>
        </div>

        {{-- Ringkasan Dompet Simpanan --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-emerald-600 text-white p-5 shadow-sm">
                <p class="text-xs font-medium text-emerald-100">Total Simpanan Akhir</p>
                <p class="mt-2 text-2xl font-bold">{{ format_rupiah($simpanan?->total_simpanan_akhir ?? 0) }}</p>
                <p class="mt-2 text-xs text-emerald-100">
                    Periode SW terakhir: <span class="font-semibold">{{ format_periode($simpanan?->periode_sw_terakhir) }}</span>
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-500">Simpanan Pokok</p>
                    @if ($simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">
                            LUNAS
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800">
                            BELUM BAYAR
                        </span>
                    @endif
                </div>
                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ format_rupiah($simpanan?->nominal_simpanan_pokok ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">
                    @if ($simpanan?->tanggal_lunas_pokok)
                        Lunas {{ $simpanan->tanggal_lunas_pokok->translatedFormat('d M Y') }}
                    @else
                        Belum lunas
                    @endif
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold text-slate-500">Saldo Awal SW (Migrasi)</p>
                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ format_rupiah($simpanan?->saldo_sw_awal ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">Saldo tutup buku sebelum sistem web.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold text-slate-500">Total Simpanan Wajib</p>
                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ format_rupiah($simpanan?->total_simpanan_wajib ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">
                    {{ $jumlahLunas }} bulan lunas &middot; {{ $jumlahBelumLunas }} belum lunas
                </p>
            </div>
        </section>

        {{-- Daftar Tagihan Simpanan Wajib --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Tagihan Simpanan Wajib</h2>
                    <p class="text-xs text-slate-500">Diurutkan dari periode terbaru ke terlama.</p>
                </div>
                @if ($jumlahBelumLunas > 0)
                    <span class="inline-flex items-center rounded-xl bg-rose-50 border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-700 self-start sm:self-auto">
                        Total Tunggakan: {{ format_rupiah($totalTunggakanSw) }}
                    </span>
                @endif
            </div>

            @if ($daftarTagihanSw->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada tagihan Simpanan Wajib yang tercatat.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Periode</th>
                                <th class="px-5 py-3 text-right">Tagihan</th>
                                <th class="px-5 py-3 text-right">Terbayar</th>
                                <th class="px-5 py-3 text-right">Sisa</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-left">Tanggal Lunas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($daftarTagihanSw as $tagihan)
                                @php
                                    $badgeClass = match ($tagihan->status) {
                                        \App\Models\TagihanSimpananWajib::STATUS_LUNAS => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        \App\Models\TagihanSimpananWajib::STATUS_SEBAGIAN => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-rose-100 text-rose-800 border-rose-200',
                                    };
                                    $badgeLabel = match ($tagihan->status) {
                                        \App\Models\TagihanSimpananWajib::STATUS_LUNAS => 'LUNAS',
                                        \App\Models\TagihanSimpananWajib::STATUS_SEBAGIAN => 'SEBAGIAN',
                                        default => 'BELUM BAYAR',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ format_periode($tagihan->periode) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-slate-700 whitespace-nowrap">
                                        {{ format_rupiah($tagihan->nominal_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-medium text-emerald-700 whitespace-nowrap">
                                        {{ format_rupiah($tagihan->nominal_terbayar) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-medium {{ (float) $tagihan->sisa_tagihan > 0 ? 'text-rose-600' : 'text-slate-400' }} whitespace-nowrap">
                                        {{ format_rupiah($tagihan->sisa_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">
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
