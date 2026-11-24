@extends('layouts.anggota')

@section('title', 'Riwayat Pembayaran')

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Riwayat Pembayaran</h1>
            <p class="text-sm text-slate-500 mt-0.5">Struk setoran Anda beserta rincian alokasi ke Simpanan Pokok, Simpanan Wajib, dan Cicilan Pinjaman.</p>
        </div>

        @if ($daftarPembayaran->isEmpty())
            <div class="rounded-2xl bg-white border border-slate-200 p-10 text-center shadow-xs">
                <p class="text-base font-semibold text-slate-800">Belum Ada Riwayat Pembayaran</p>
                <p class="mt-1 text-sm text-slate-500">Setiap pembayaran yang dicatat pengurus koperasi akan tampil otomatis di sini.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($daftarPembayaran as $pembayaran)
                    <article class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
                        {{-- Header Struk --}}
                        <div class="px-5 py-4 bg-slate-50/80 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-sm font-bold text-slate-900">{{ $pembayaran->nomor_transaksi }}</h2>
                                    <span class="inline-flex items-center rounded-md bg-white border border-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                        {{ $pembayaran->metode_bayar }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}
                                    @if ($pembayaran->diinputOleh)
                                        &middot; Dicatat oleh {{ $pembayaran->diinputOleh->nama_lengkap }}
                                    @endif
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <p class="text-[11px] text-slate-500">Total Dibayar</p>
                                <p class="text-lg font-bold text-emerald-700">{{ format_rupiah($pembayaran->total_bayar) }}</p>
                            </div>
                        </div>

                        {{-- Rincian Item Detail Pembayaran --}}
                        <div class="divide-y divide-slate-100">
                            @foreach ($pembayaran->detailPembayaran as $detail)
                                @php
                                    if ($detail->jenis_pembayaran === \App\Models\DetailPembayaran::JENIS_SIMPANAN_POKOK) {
                                        $jenisLabel = 'Simpanan Pokok';
                                        $jenisClass = 'bg-purple-100 text-purple-800';
                                        $deskripsi = 'Pelunasan Simpanan Pokok Keanggotaan';
                                    } elseif ($detail->jenis_pembayaran === \App\Models\DetailPembayaran::JENIS_SIMPANAN_WAJIB) {
                                        $jenisLabel = 'Simpanan Wajib';
                                        $jenisClass = 'bg-emerald-100 text-emerald-800';
                                        $deskripsi = 'Periode '.format_periode($detail->tagihanSimpananWajib?->periode);
                                    } else {
                                        $jenisLabel = 'Cicilan Pinjaman';
                                        $jenisClass = 'bg-sky-100 text-sky-800';
                                        $cicilan = $detail->cicilanPinjaman;
                                        $pinjaman = $cicilan?->pinjaman;
                                        $namaPinjaman = $pinjaman?->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG
                                            ? ($pinjaman->nama_barang ?: 'Pinjaman Barang')
                                            : 'Pinjaman Uang';
                                        $deskripsi = 'Cicilan ke-'.($cicilan?->cicilan_ke ?? '-').' — '.$namaPinjaman.($pinjaman ? ' ('.$pinjaman->nomor_pinjaman.')' : '');
                                    }
                                @endphp
                                <div class="px-5 py-3 flex items-center justify-between gap-4 text-sm">
                                    <div class="min-w-0">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $jenisClass }}">
                                            {{ $jenisLabel }}
                                        </span>
                                        <p class="mt-1 text-xs sm:text-sm text-slate-700">{{ $deskripsi }}</p>
                                    </div>
                                    <p class="font-semibold text-slate-900 whitespace-nowrap shrink-0">
                                        {{ format_rupiah($detail->nominal_bayar) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>

                        @if ($pembayaran->catatan)
                            <div class="px-5 py-3 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-600">
                                <span class="font-semibold text-slate-700">Catatan:</span> {{ $pembayaran->catatan }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if ($daftarPembayaran->hasPages())
                <div class="pt-2">
                    {{ $daftarPembayaran->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
