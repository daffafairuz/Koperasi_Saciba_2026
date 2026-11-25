@extends('layouts.anggota')

@section('title', 'Pinjaman & Cicilan Saya')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-emerald-600">Pembiayaan Aktif</p>
                <h1 class="mt-1 text-xl sm:text-2xl font-bold tracking-tight text-zinc-950">Pinjaman &amp; Jadwal Cicilan</h1>
                <p class="text-sm text-zinc-500 mt-0.5">Daftar pinjaman uang (syariah 0%) dan pembiayaan barang Anda.</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 px-4 py-3 shadow-xs shrink-0">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Total Sisa Tagihan ({{ $jumlahPinjamanAktif }} Aktif)</p>
                <p class="mt-0.5 font-mono tabular-nums text-lg font-bold text-emerald-400">{{ format_rupiah($totalSisaPinjaman) }}</p>
            </div>
        </div>

        @if ($daftarPinjaman->isEmpty())
            <div class="rounded-2xl bg-white border border-zinc-200/80 p-10 text-center shadow-xs">
                <div class="mx-auto h-11 w-11 rounded-2xl bg-zinc-100 text-zinc-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2" />
                        <line x1="2" x2="22" y1="10" y2="10" />
                    </svg>
                </div>
                <p class="mt-3 text-base font-semibold text-zinc-900">Belum Ada Data Pinjaman</p>
                <p class="mt-1 text-sm text-zinc-500">Anda saat ini tidak memiliki pinjaman berjalan maupun riwayat pinjaman.</p>
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
                        $isBarang = $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG;
                        $isBerjalan = $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN;
                    @endphp

                    <article
                        x-data="{ open: false }"
                        class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden"
                    >
                        <div class="p-5 sm:p-6 space-y-5">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold {{ $isBarang ? 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                                            {{ $isBarang ? 'PINJAMAN BARANG' : 'PINJAMAN UANG' }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold {{ $isBerjalan ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                                            {{ $pinjaman->status }}
                                        </span>
                                        @if ($pinjaman->is_migrasi)
                                            <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 text-[10px] font-medium text-zinc-600">
                                                Migrasi
                                            </span>
                                        @endif
                                    </div>

                                    <h2 class="mt-2 text-base sm:text-lg font-bold tracking-tight text-zinc-950">
                                        {{ $isBarang ? ($pinjaman->nama_barang ?: 'Pinjaman Barang') : 'Pinjaman Uang Tunai (Margin 0%)' }}
                                    </h2>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        <span class="font-mono tabular-nums text-zinc-700">{{ $pinjaman->nomor_pinjaman }}</span>
                                        &middot; Cair <span class="tabular-nums">{{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }}</span>
                                        &middot; Margin <span class="font-mono tabular-nums">{{ number_format((float) $pinjaman->persen_margin, 0) }}%</span>
                                    </p>
                                </div>

                                <div class="sm:text-right">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Sisa Tagihan</p>
                                    <p class="mt-0.5 font-mono tabular-nums text-xl font-bold {{ $isBerjalan ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Ringkasan Angka Pinjaman --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 rounded-xl bg-zinc-50/80 border border-zinc-200/60 p-3.5 text-xs">
                                <div>
                                    <p class="text-zinc-500">Pokok Pinjaman</p>
                                    <p class="mt-0.5 font-mono tabular-nums font-semibold text-zinc-950">{{ format_rupiah($pinjaman->pokok_pinjaman) }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500">Total Tagihan</p>
                                    <p class="mt-0.5 font-mono tabular-nums font-semibold text-zinc-950">{{ format_rupiah($pinjaman->total_tagihan) }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500">Cicilan / Bulan</p>
                                    <p class="mt-0.5 font-mono tabular-nums font-semibold text-zinc-950">{{ format_rupiah($pinjaman->cicilan_per_bulan) }}</p>
                                </div>
                                <div>
                                    <p class="text-zinc-500">Progres Tenor</p>
                                    <p class="mt-0.5 font-mono tabular-nums font-semibold text-zinc-950">{{ $jumlahCicilanLunas }}/{{ $pinjaman->tenor_bulan }} bulan</p>
                                </div>
                            </div>

                            {{-- Progress Bar + Toggle Alpine.js --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between text-xs mb-1.5">
                                        <span class="text-zinc-500">Progres Pelunasan</span>
                                        <span class="font-mono tabular-nums font-semibold text-zinc-900">{{ $persenLunas }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-zinc-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-300" style="width: {{ $persenLunas }}%"></div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="open = !open"
                                    :aria-expanded="open.toString()"
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white hover:bg-zinc-900 hover:text-white hover:border-zinc-900 px-4 py-2 text-xs font-semibold text-zinc-700 transition cursor-pointer shrink-0"
                                >
                                    <span x-text="open ? 'Tutup Jadwal Cicilan' : 'Lihat Jadwal Cicilan'">Lihat Jadwal Cicilan</span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5 transition-transform duration-200"
                                        :class="open ? 'rotate-180' : ''"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.75"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Panel Collapsible Jadwal Cicilan --}}
                        <div x-show="open" x-transition x-cloak class="border-t border-zinc-200/80 bg-zinc-50/50">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-zinc-200/80 text-xs sm:text-sm">
                                    <thead class="bg-zinc-50/80 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left">Ke-</th>
                                            <th class="px-4 py-2.5 text-left">Jatuh Tempo</th>
                                            <th class="px-4 py-2.5 text-right">Tagihan</th>
                                            <th class="px-4 py-2.5 text-right">Terbayar</th>
                                            <th class="px-4 py-2.5 text-center">Status</th>
                                            <th class="px-4 py-2.5 text-left">Tanggal Bayar</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 bg-white">
                                        @foreach ($pinjaman->cicilan as $cicilan)
                                            @php
                                                $cicilanBadgeClass = match ($cicilan->status) {
                                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20',
                                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'bg-amber-500/10 text-amber-700 border-amber-500/20',
                                                    default => 'bg-rose-500/10 text-rose-700 border-rose-500/20',
                                                };
                                                $cicilanBadgeLabel = match ($cicilan->status) {
                                                    \App\Models\CicilanPinjaman::STATUS_LUNAS => 'LUNAS',
                                                    \App\Models\CicilanPinjaman::STATUS_SEBAGIAN => 'SEBAGIAN',
                                                    default => 'BELUM BAYAR',
                                                };
                                            @endphp
                                            <tr class="hover:bg-zinc-50/70 transition">
                                                <td class="px-4 py-3 font-mono tabular-nums font-semibold text-zinc-900 whitespace-nowrap">
                                                    Cicilan {{ $cicilan->cicilan_ke }}
                                                </td>
                                                <td class="px-4 py-3 text-zinc-600 tabular-nums whitespace-nowrap">
                                                    {{ $cicilan->jatuh_tempo->translatedFormat('d M Y') }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-mono tabular-nums text-zinc-800 whitespace-nowrap">
                                                    {{ format_rupiah($cicilan->nominal_tagihan) }}
                                                </td>
                                                <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-emerald-700 whitespace-nowrap">
                                                    {{ format_rupiah($cicilan->nominal_terbayar) }}
                                                </td>
                                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $cicilanBadgeClass }}">
                                                        {{ $cicilanBadgeLabel }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-xs text-zinc-500 tabular-nums whitespace-nowrap">
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
