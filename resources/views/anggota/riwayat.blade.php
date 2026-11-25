@extends('layouts.anggota')

@section('title', 'Riwayat Pembayaran')

@section('content')
    <div class="space-y-6">
        <div>
            <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-emerald-600">Arsip Resi Transaksi</p>
            <h1 class="mt-1 text-xl sm:text-2xl font-bold tracking-tight text-zinc-950">Riwayat Pembayaran</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Struk setoran Anda beserta rincian alokasi ke Simpanan Pokok, Simpanan Wajib, dan Cicilan Pinjaman.</p>
        </div>

        @if ($daftarPembayaran->isEmpty())
            <div class="rounded-2xl bg-white border border-zinc-200/80 p-10 text-center shadow-xs">
                <div class="mx-auto h-11 w-11 rounded-2xl bg-zinc-100 text-zinc-400 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                        <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                        <path d="M12 17.5v-11" />
                    </svg>
                </div>
                <p class="mt-3 text-base font-semibold text-zinc-900">Belum Ada Riwayat Pembayaran</p>
                <p class="mt-1 text-sm text-zinc-500">Setiap pembayaran yang dicatat pengurus koperasi akan tampil otomatis di sini.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($daftarPembayaran as $pembayaran)
                    <article class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
                        {{-- Mobile Banking Style Receipt Header --}}
                        <div class="px-5 py-4 bg-zinc-50/80 border-b border-dashed border-zinc-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="font-mono tabular-nums text-sm font-bold text-zinc-950">{{ $pembayaran->nomor_transaksi }}</h2>
                                        <span class="inline-flex items-center rounded-md bg-white border border-zinc-200/80 px-2 py-0.5 text-[10px] font-semibold text-zinc-700">
                                            {{ $pembayaran->metode_bayar }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-zinc-500 mt-1">
                                        <span class="tabular-nums">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}</span>
                                        @if ($pembayaran->diinputOleh)
                                            &middot; Dicatat oleh {{ $pembayaran->diinputOleh->nama_lengkap }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="sm:text-right pl-13 sm:pl-0">
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Total Dibayar</p>
                                <p class="font-mono tabular-nums text-xl font-bold text-emerald-600">{{ format_rupiah($pembayaran->total_bayar) }}</p>
                            </div>
                        </div>

                        {{-- Rincian Item Detail Pembayaran --}}
                        <div class="divide-y divide-zinc-100">
                            @foreach ($pembayaran->detailPembayaran as $detail)
                                @php
                                    if ($detail->jenis_pembayaran === \App\Models\DetailPembayaran::JENIS_SIMPANAN_POKOK) {
                                        $jenisLabel = 'Simpanan Pokok';
                                        $jenisClass = 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15';
                                        $deskripsi = 'Pelunasan Simpanan Pokok Keanggotaan';
                                    } elseif ($detail->jenis_pembayaran === \App\Models\DetailPembayaran::JENIS_SIMPANAN_WAJIB) {
                                        $jenisLabel = 'Simpanan Wajib';
                                        $jenisClass = 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20';
                                        $deskripsi = 'Periode '.format_periode($detail->tagihanSimpananWajib?->periode);
                                    } else {
                                        $jenisLabel = 'Cicilan Pinjaman';
                                        $jenisClass = 'bg-amber-500/10 text-amber-700 border-amber-500/20';
                                        $cicilan = $detail->cicilanPinjaman;
                                        $pinjaman = $cicilan?->pinjaman;
                                        $namaPinjaman = $pinjaman?->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG
                                            ? ($pinjaman->nama_barang ?: 'Pinjaman Barang')
                                            : 'Pinjaman Uang';
                                        $deskripsi = 'Cicilan ke-'.($cicilan?->cicilan_ke ?? '-').' — '.$namaPinjaman.($pinjaman ? ' ('.$pinjaman->nomor_pinjaman.')' : '');
                                    }
                                @endphp
                                <div class="px-5 py-3 flex items-center justify-between gap-4 text-sm hover:bg-zinc-50/70 transition">
                                    <div class="min-w-0">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold {{ $jenisClass }}">
                                            {{ $jenisLabel }}
                                        </span>
                                        <p class="mt-1 text-xs sm:text-sm text-zinc-700">{{ $deskripsi }}</p>
                                    </div>
                                    <p class="font-mono tabular-nums font-semibold text-zinc-950 whitespace-nowrap shrink-0">
                                        {{ format_rupiah($detail->nominal_bayar) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        @if ($pembayaran->catatan)
                            <div class="px-5 py-3 bg-zinc-50/60 border-t border-zinc-100 text-xs text-zinc-600">
                                <span class="font-semibold text-zinc-800">Catatan:</span> {{ $pembayaran->catatan }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            @if ($daftarPembayaran->hasPages())
                <div class="pt-2">
                    {{ $daftarPembayaran->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
