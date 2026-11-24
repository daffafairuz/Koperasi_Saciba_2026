@extends('layouts.anggota')

@section('title', 'Pinjaman & Cicilan Saya')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Pinjaman &amp; Jadwal Cicilan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Daftar seluruh pinjaman uang (syariah 0%) dan pinjaman barang Anda.</p>
            </div>
            <div class="rounded-xl bg-white border border-slate-200 px-4 py-2.5 shadow-xs">
                <p class="text-[11px] font-medium text-slate-500">Total Sisa Tagihan ({{ $jumlahPinjamanAktif }} Aktif)</p>
                <p class="text-base font-bold text-slate-900">{{ format_rupiah($totalSisaPinjaman) }}</p>
            </div>
        </div>

        @if ($daftarPinjaman->isEmpty())
            <div class="rounded-2xl bg-white border border-slate-200 p-10 text-center shadow-xs">
                <p class="text-base font-semibold text-slate-800">Belum Ada Data Pinjaman</p>
                <p class="mt-1 text-sm text-slate-500">Anda saat ini tidak memiliki pinjaman berjalan maupun riwayat pinjaman.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($daftarPinjaman as $pinjaman)
                    @php
                        $totalTagihanVal = (float) $pinjaman->total_tagihan;
                        $sisaTagihanVal = (float) $pinjaman->sisa_tagihan;
                        $terbayarVal = max(0, $totalTagihanVal - $sisaTagihanVal);
                        $persenLunas = $totalTagihanVal > 0 ? min(100, (int) round(($terbayarVal / $totalTagihanVal) * 100)) : 0;
                        $jumlahCicilanLunas = $pinjaman->cicilan->where('status', \App\Models\CicilanPinjaman::STATUS_LUNAS)->count();
                    @endphp

                    <article
                        x-data="{ open: false }"
                        class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden"
                    >
                        <div class="p-5 space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[11px] font-semibold {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800' }}">
                                            {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? 'PINJAMAN BARANG' : 'PINJAMAN UANG' }}
                                        </span>
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-[11px] font-semibold {{ $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $pinjaman->status }}
                                        </span>
                                        @if ($pinjaman->is_migrasi)
                                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                                                Migrasi
                                            </span>
                                        @endif
                                    </div>

                                    <h2 class="mt-2 text-base font-bold text-slate-900">
                                        {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? ($pinjaman->nama_barang ?: 'Pinjaman Barang') : 'Pinjaman Uang Tunai (Margin 0%)' }}
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $pinjaman->nomor_pinjaman }} &middot; Cair {{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }}
                                        &middot; Margin {{ number_format((float) $pinjaman->persen_margin, 0) }}%
                                    </p>
                                </div>

                                <div class="sm:text-right">
                                    <p class="text-xs text-slate-500">Sisa Tagihan</p>
                                    <p class="text-xl font-bold {{ $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN ? 'text-rose-600' : 'text-emerald-700' }}">
                                        {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Ringkasan Angka Pinjaman --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 rounded-xl bg-slate-50 p-3.5 text-xs">
                                <div>
                                    <p class="text-slate-500">Pokok Pinjaman</p>
                                    <p class="mt-0.5 font-semibold text-slate-900">{{ format_rupiah($pinjaman->pokok_pinjaman) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Total Tagihan</p>
                                    <p class="mt-0.5 font-semibold text-slate-900">{{ format_rupiah($pinjaman->total_tagihan) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Cicilan / Bulan</p>
                                    <p class="mt-0.5 font-semibold text-slate-900">{{ format_rupiah($pinjaman->cicilan_per_bulan) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Progres Tenor</p>
                                    <p class="mt-0.5 font-semibold text-slate-900">{{ $jumlahCicilanLunas }} / {{ $pinjaman->tenor_bulan }} bulan</p>
                                </div>
                            </div>

                            {{-- Progress Bar + Toggle Alpine.js --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-slate-500">Pelunasan</span>
                                        <span class="font-semibold text-slate-700">{{ $persenLunas }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-emerald-500" style="width: {{ $persenLunas }}%"></div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-700 transition cursor-pointer shrink-0"
                                >
                                    <span x-text="open ? 'Tutup Jadwal Cicilan' : 'Lihat Jadwal Cicilan'">Lihat Jadwal Cicilan</span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 transition-transform duration-200"
                                        :class="open ? 'rotate-180' : ''"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Panel Collapsible Jadwal Cicilan --}}
                        <div x-show="open" x-transition x-cloak class="border-t border-slate-200 bg-slate-50/60">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-xs sm:text-sm">
                                    <thead class="bg-slate-100/80 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Ke-</th>
                                            <th class="px-4 py-2.5 text-left">Jatuh Tempo</th>
                                            <th class="px-4 py-2.5 text-right">Tagihan</th>
                                            <th class="px-4 py-2.5 text-right">Terbayar</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                            <th class="px-4 py-2.5 text-left">Tanggal Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200/70 bg-white">
                                        @foreach ($pinjaman->cicilan as $cicilan)
                                            @php
                                                $cicilanBadgeClass = match ($cicilan->status) {
                                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'bg-amber-100 text-amber-800 border-amber-200',
                                                    default => 'bg-rose-100 text-rose-800 border-rose-200',
                                                };
                                                $cicilanBadgeLabel = match ($cicilan->status) {
                                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'LUNAS',
                                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'SEBAGIAN',
                                                    default => 'BELUM BAYAR',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap">
                                                    Cicilan {{ $cicilan->cicilan_ke }}
                                                </td>
                                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                                    {{ $cicilan->jatuh_tempo->translatedFormat('d M Y') }}
                                                </td>
                                                <td class="px-4 py-3 text-right text-slate-800 whitespace-nowrap">
                                                    {{ format_rupiah($cicilan->nominal_tagihan) }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-medium text-emerald-700 whitespace-nowrap">
                                                    {{ format_rupiah($cicilan->nominal_terbayar) }}
                                                </td>
                                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $cicilanBadgeClass }}">
                                                        {{ $cicilanBadgeLabel }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">
                                                    {{ $cicilan->tanggal_bayar ? $cicilan->tanggal_bayar->translatedFormat('d M Y') : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
