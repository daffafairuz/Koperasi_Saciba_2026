@extends('layouts.admin')

@section('title', 'Struk Pembayaran — '.$pembayaran->nomor_transaksi)
@section('header', 'Struk Pembayaran Anggota')

@section('content')
    <div class="max-w-3xl mx-auto space-y-5">
        {{-- Action Bar (disembunyikan saat cetak) --}}
        <div class="print:hidden flex flex-wrap items-center justify-between gap-3">
            <a
                href="{{ route('admin.pembayaran.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                <span>Riwayat Pembayaran</span>
            </a>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('admin.pembayaran.create') }}"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3.5 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-500/15 transition"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    <span>Input Pembayaran Baru</span>
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-xs font-semibold text-white shadow-xs transition cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect width="12" height="8" x="6" y="14" />
                    </svg>
                    <span>Cetak Struk</span>
                </button>
            </div>
        </div>

        {{-- Kartu Struk Resmi Print-Friendly --}}
        <article class="rounded-2xl bg-white border border-zinc-200/80 p-6 sm:p-8 shadow-xs print:shadow-none print:border-zinc-300 space-y-6">
            {{-- Kop Koperasi --}}
            <header class="border-b border-dashed border-zinc-300 pb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-zinc-950 text-emerald-400 flex items-center justify-center shrink-0 print:border print:border-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18" />
                            <path d="M3 10h18" />
                            <path d="M5 6l7-3 7 3" />
                            <path d="M4 10v11" />
                            <path d="M20 10v11" />
                            <path d="M8 14v3" />
                            <path d="M12 14v3" />
                            <path d="M16 14v3" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold tracking-tight text-zinc-950">Koperasi Jasa Berkah Saciba</h2>
                        <p class="font-mono text-[11px] uppercase tracking-wider text-zinc-500 mt-0.5">Bukti Resmi Pembayaran Anggota</p>
                    </div>
                </div>
                <div class="sm:text-right">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Nomor Struk</p>
                    <p class="font-mono tabular-nums text-base font-bold text-zinc-950">{{ $pembayaran->nomor_transaksi }}</p>
                    <p class="text-xs text-zinc-500 tabular-nums mt-0.5">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}</p>
                </div>
            </header>

            {{-- Metadata Transaksi --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Diterima Dari</p>
                    <p class="font-bold text-zinc-950">{{ $pembayaran->anggota->nama_lengkap }}</p>
                    <p class="text-xs text-zinc-600">
                        No. Anggota: <span class="font-mono tabular-nums text-zinc-800">{{ $pembayaran->anggota->nomor_anggota }}</span>
                        &middot; HP: <span class="font-mono tabular-nums text-zinc-800">{{ $pembayaran->anggota->no_hp }}</span>
                    </p>
                </div>

                <div class="sm:text-right space-y-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-400">Metode &amp; Petugas</p>
                    <p class="font-semibold text-zinc-950">
                        Metode:
                        <span class="inline-flex items-center rounded-md bg-zinc-100 border border-zinc-200/80 px-2 py-0.5 text-xs font-semibold text-zinc-800">
                            {{ $pembayaran->metode_bayar }}
                        </span>
                    </p>
                    <p class="text-xs text-zinc-600">
                        Dicatat oleh: {{ $pembayaran->diinputOleh?->nama_lengkap ?? '-' }}
                    </p>
                </div>
            </section>

            {{-- Tabel Rincian Alokasi --}}
            <section class="overflow-x-auto rounded-xl border border-zinc-200/80 print:border-zinc-300">
                <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                    <thead class="bg-zinc-50/80 text-[11px] font-semibold uppercase tracking-wider text-zinc-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Jenis Pos</th>
                            <th class="px-4 py-3 text-left">Rincian Alokasi</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white">
                        @foreach ($pembayaran->detailPembayaran as $detail)
                            @php
                                if ($detail->jenis_pembayaran === \App\Models\DetailPembayaran::JENIS_SIMPANAN_POKOK) {
                                    $jenisLabel = 'Simpanan Pokok';
                                    $deskripsi = 'Pelunasan Simpanan Pokok Keanggotaan';
                                } elseif ($detail->jenis_pembayaran === \App\Models\DetailPembayaran::JENIS_SIMPANAN_WAJIB) {
                                    $jenisLabel = 'Simpanan Wajib';
                                    $deskripsi = 'Periode '.format_periode($detail->tagihanSimpananWajib?->periode);
                                } else {
                                    $jenisLabel = 'Cicilan Pinjaman';
                                    $cicilan = $detail->cicilanPinjaman;
                                    $pinjaman = $cicilan?->pinjaman;
                                    $namaPinjaman = $pinjaman?->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG
                                        ? ($pinjaman->nama_barang ?: 'Pinjaman Barang')
                                        : 'Pinjaman Uang';
                                    $deskripsi = 'Cicilan ke-'.($cicilan?->cicilan_ke ?? '-').' — '.$namaPinjaman.($pinjaman ? ' ('.$pinjaman->nomor_pinjaman.')' : '');
                                }
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-semibold text-zinc-900 whitespace-nowrap">{{ $jenisLabel }}</td>
                                <td class="px-4 py-3 text-zinc-600">{{ $deskripsi }}</td>
                                <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-zinc-950 whitespace-nowrap">
                                    {{ format_rupiah($detail->nominal_bayar) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-zinc-200 bg-zinc-50/90">
                        <tr>
                            <td colspan="2" class="px-4 py-3.5 text-xs sm:text-sm font-bold uppercase tracking-wider text-zinc-900">
                                Total Pembayaran Diterima
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono tabular-nums text-lg font-bold text-emerald-600 whitespace-nowrap">
                                {{ format_rupiah($pembayaran->total_bayar) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            @if ($pembayaran->catatan)
                <div class="rounded-xl bg-zinc-50 border border-zinc-200/80 p-3.5 text-xs text-zinc-600">
                    <span class="font-semibold text-zinc-800">Catatan:</span> {{ $pembayaran->catatan }}
                </div>
            @endif

            <footer class="pt-4 border-t border-dashed border-zinc-300 flex items-center justify-between text-xs text-zinc-500">
                <span>Simpan struk ini sebagai bukti pembayaran yang sah.</span>
                <span class="font-mono text-zinc-600">Koperasi Jasa Berkah Saciba</span>
            </footer>
        </article>
    </div>
@endsection
