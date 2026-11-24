@extends('layouts.admin')

@section('title', 'Struk Pembayaran — '.$pembayaran->nomor_transaksi)
@section('header', 'Struk Pembayaran Anggota')

@section('content')
    <div class="max-w-3xl mx-auto space-y-5">
        {{-- Action Bar (disembunyikan saat cetak) --}}
        <div class="print:hidden flex flex-wrap items-center justify-between gap-3">
            <a
                href="{{ route('admin.pembayaran.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
            >
                &larr; Riwayat Pembayaran
            </a>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('admin.pembayaran.create') }}"
                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 transition"
                >
                    + Input Pembayaran Baru
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition cursor-pointer"
                >
                    Cetak Struk
                </button>
            </div>
        </div>

        {{-- Kartu Struk Print-Friendly --}}
        <article class="rounded-2xl bg-white border border-slate-200 p-6 sm:p-8 shadow-sm print:shadow-none print:border-slate-300 space-y-6">
            {{-- Kop Koperasi --}}
            <header class="border-b border-dashed border-slate-300 pb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Koperasi Jasa Berkah Saciba</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Bukti Resmi Pembayaran Anggota</p>
                </div>
                <div class="sm:text-right">
                    <p class="text-xs uppercase tracking-wider text-slate-500">Nomor Struk</p>
                    <p class="text-base font-bold text-slate-900">{{ $pembayaran->nomor_transaksi }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y, H:i') }}</p>
                </div>
            </header>

            {{-- Metadata Transaksi --}}
            <section class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="space-y-1">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Diterima Dari</p>
                    <p class="font-bold text-slate-900">{{ $pembayaran->anggota->nama_lengkap }}</p>
                    <p class="text-xs text-slate-600">
                        No. Anggota: {{ $pembayaran->anggota->nomor_anggota }} &middot; HP: {{ $pembayaran->anggota->no_hp }}
                    </p>
                </div>

                <div class="sm:text-right space-y-1">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Metode &amp; Petugas</p>
                    <p class="font-semibold text-slate-900">Metode: {{ $pembayaran->metode_bayar }}</p>
                    <p class="text-xs text-slate-600">
                        Dicatat oleh: {{ $pembayaran->diinputOleh?->nama_lengkap ?? '-' }}
                    </p>
                </div>
            </section>

            {{-- Tabel Rincian Alokasi --}}
            <section class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-2.5 text-left">Jenis Pos</th>
                            <th class="px-4 py-2.5 text-left">Rincian Alokasi</th>
                            <th class="px-4 py-2.5 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
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
                                <td class="px-4 py-3 font-semibold text-slate-800 whitespace-nowrap">{{ $jenisLabel }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $deskripsi }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 whitespace-nowrap">
                                    {{ format_rupiah($detail->nominal_bayar) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200 bg-slate-50/80">
                        <tr>
                            <td colspan="2" class="px-4 py-3.5 text-sm font-bold text-slate-900">TOTAL PEMBAYARAN DITERIMA</td>
                            <td class="px-4 py-3.5 text-right text-lg font-bold text-emerald-700 whitespace-nowrap">
                                {{ format_rupiah($pembayaran->total_bayar) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </section>

            @if ($pembayaran->catatan)
                <div class="rounded-xl bg-slate-50 border border-slate-200/80 p-3.5 text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Catatan:</span> {{ $pembayaran->catatan }}
                </div>
            @endif

            <footer class="pt-4 border-t border-dashed border-slate-300 flex items-center justify-between text-xs text-slate-500">
                <span>Simpan struk ini sebagai bukti pembayaran yang sah.</span>
                <span>Koperasi Jasa Berkah Saciba</span>
            </footer>
        </article>
    </div>
@endsection
