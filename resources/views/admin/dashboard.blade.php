@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header', 'Dashboard Utama Koperasi')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- 4 Kartu Metrik Utama Real-Time --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- 1. Total Kas Koperasi --}}
            <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white p-5 shadow-sm">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Total Kas Koperasi</p>
                    <span class="rounded-lg bg-white/15 px-2 py-0.5 text-[11px] font-semibold">Likuid</span>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight">{{ format_rupiah($totalKasKoperasi) }}</p>
                <p class="mt-2 text-xs text-emerald-100">
                    Masuk {{ format_rupiah($totalKasMasuk) }} &middot; Keluar {{ format_rupiah($totalKasKeluar) }}
                </p>
            </div>

            {{-- 2. Total Dana Simpanan Anggota --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Dana Simpanan</p>
                    <span class="rounded-lg bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700">SP + SW</span>
                </div>
                <p class="mt-3 text-2xl font-bold text-slate-900">{{ format_rupiah($totalDanaSimpanan) }}</p>
                <p class="mt-2 text-xs text-slate-500">
                    SP Lunas {{ format_rupiah($totalSimpananPokokLunas) }} &middot; SW {{ format_rupiah($totalSimpananWajib) }}
                </p>
            </div>

            {{-- 3. Total Piutang Pinjaman Berjalan --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Piutang Berjalan</p>
                    <span class="rounded-lg bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">
                        {{ $jumlahPinjamanBerjalan }} Pinjaman
                    </span>
                </div>
                <p class="mt-3 text-2xl font-bold text-amber-700">{{ format_rupiah($totalPiutangPinjaman) }}</p>
                <p class="mt-2 text-xs text-slate-500">
                    Akumulasi sisa tagihan seluruh pinjaman aktif.
                </p>
            </div>

            {{-- 4. Statistik Keanggotaan --}}
            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Statistik Anggota</p>
                    <span class="rounded-lg bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                        Total {{ $totalAnggota }}
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-4">
                    <div>
                        <span class="text-2xl font-bold text-emerald-700">{{ $jumlahAnggotaAktif }}</span>
                        <span class="ml-1 text-xs font-medium text-slate-500">Aktif</span>
                    </div>
                    <div class="h-5 w-px bg-slate-200"></div>
                    <div>
                        <span class="text-2xl font-bold text-slate-500">{{ $jumlahAnggotaKeluar }}</span>
                        <span class="ml-1 text-xs font-medium text-slate-500">Keluar</span>
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-500">
                    Anggota berstatus KELUAR tetap tersimpan untuk audit.
                </p>
            </div>
        </section>

        {{-- Top 5 Piutang Terbesar & 5 Transaksi Setoran Terbaru --}}
        <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            {{-- Top 5 Pinjaman Aktif Terbesar --}}
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Top 5 Piutang Terbesar</h2>
                        <p class="text-xs text-slate-500">Pinjaman berstatus BERJALAN dengan sisa tagihan tertinggi.</p>
                    </div>
                    <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Aktif</span>
                </div>

                @if ($topPinjamanAktif->isEmpty())
                    <div class="p-8 text-center text-sm text-slate-500">
                        Tidak ada pinjaman yang sedang berjalan.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">Anggota</th>
                                    <th class="px-5 py-3 text-left">Pinjaman</th>
                                    <th class="px-5 py-3 text-right">Total</th>
                                    <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($topPinjamanAktif as $pinjaman)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <p class="font-semibold text-slate-900">{{ $pinjaman->anggota->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500">{{ $pinjaman->anggota->nomor_anggota }}</p>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800' }}">
                                                    {{ $pinjaman->jenis_pinjaman }}
                                                </span>
                                                <span class="text-xs font-medium text-slate-700">{{ $pinjaman->nomor_pinjaman }}</span>
                                            </div>
                                            @if ($pinjaman->nama_barang)
                                                <p class="text-xs text-slate-500 mt-0.5">{{ $pinjaman->nama_barang }}</p>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3.5 text-right text-slate-600 whitespace-nowrap">
                                            {{ format_rupiah($pinjaman->total_tagihan) }}
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-bold text-rose-600 whitespace-nowrap">
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
            <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Transaksi Setoran Terbaru</h2>
                        <p class="text-xs text-slate-500">5 struk pembayaran anggota terakhir yang masuk ke koperasi.</p>
                    </div>
                    <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Terbaru</span>
                </div>

                @if ($pembayaranTerbaru->isEmpty())
                    <div class="p-8 text-center text-sm text-slate-500">
                        Belum ada transaksi pembayaran anggota.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">Transaksi</th>
                                    <th class="px-5 py-3 text-left">Anggota</th>
                                    <th class="px-5 py-3 text-center">Metode</th>
                                    <th class="px-5 py-3 text-right">Total Bayar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($pembayaranTerbaru as $pembayaran)
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <p class="font-semibold text-slate-900">{{ $pembayaran->nomor_transaksi }}</p>
                                            <p class="text-xs text-slate-500">
                                                {{ $pembayaran->tanggal_bayar->translatedFormat('d M Y') }} &middot; {{ $pembayaran->detailPembayaran->count() }} item
                                            </p>
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <p class="font-medium text-slate-800">{{ $pembayaran->anggota->nama_lengkap }}</p>
                                            <p class="text-xs text-slate-500">{{ $pembayaran->anggota->nomor_anggota }}</p>
                                        </td>
                                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                                {{ $pembayaran->metode_bayar }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-bold text-emerald-700 whitespace-nowrap">
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
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Anggota Keluar Terbaru</h2>
                    <p class="text-xs text-slate-500">Log audit perubahan status keanggotaan menjadi KELUAR (soft-exit).</p>
                </div>
                <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Audit Status</span>
            </div>

            @if ($anggotaKeluarTerbaru->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada riwayat anggota keluar.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Anggota</th>
                                <th class="px-5 py-3 text-left">Tanggal Keluar</th>
                                <th class="px-5 py-3 text-left">Alasan</th>
                                <th class="px-5 py-3 text-left">Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($anggotaKeluarTerbaru as $riwayat)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-slate-900">{{ $riwayat->anggota->nama_lengkap }}</p>
                                        <p class="text-xs text-slate-500">{{ $riwayat->anggota->nomor_anggota }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                        {{ $riwayat->anggota->tanggal_keluar ? $riwayat->anggota->tanggal_keluar->translatedFormat('d M Y') : ($riwayat->tanggal_disetujui ? $riwayat->tanggal_disetujui->translatedFormat('d M Y') : '-') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-xs sm:text-sm text-slate-600">
                                        {{ $riwayat->alasan ?: '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
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
