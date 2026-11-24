@extends('layouts.admin')

@section('title', 'Cairkan Pinjaman Baru')
@section('header', 'Pencairan Pinjaman Baru')

@section('content')
    <div
        x-data="{
            jenisPinjaman: @js(old('jenis_pinjaman', \App\Models\Pinjaman::JENIS_UANG)),
            pokokPinjaman: Number(@js(old('pokok_pinjaman', 1000000))) || 0,
            tenorBulan: Number(@js(old('tenor_bulan', 10))) || 1,
            marginBarangPersen: {{ number_format($persenMarginBarang, 2, '.', '') }},
            maxKas: {{ number_format($totalKasKoperasiAngka, 2, '.', '') }},

            get persenMargin() {
                return this.jenisPinjaman === 'BARANG' ? this.marginBarangPersen : 0;
            },
            get pokokCents() {
                return Math.max(0, Math.round((Number(this.pokokPinjaman) || 0) * 100));
            },
            get marginCents() {
                return Math.round(this.pokokCents * (this.persenMargin / 100));
            },
            get totalTagihanCents() {
                return this.pokokCents + this.marginCents;
            },
            get tenorValid() {
                const t = parseInt(this.tenorBulan, 10);
                return Number.isFinite(t) && t >= 1 ? Math.min(60, t) : 1;
            },
            get cicilanDasarCents() {
                return Math.floor(this.totalTagihanCents / this.tenorValid);
            },
            get cicilanTerakhirCents() {
                return this.totalTagihanCents - (this.cicilanDasarCents * (this.tenorValid - 1));
            },
            get melebihiKas() {
                return (this.pokokCents / 100) > this.maxKas;
            },
            formatRp(cents) {
                const nilai = Math.round(cents / 100);
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(nilai);
            }
        }"
        class="max-w-5xl mx-auto space-y-6"
    >
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Form Pencairan Pinjaman</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Jadwal angsuran bulanan dibuat otomatis saat pencairan disimpan (pembulatan diselesaikan di cicilan terakhir).
                </p>
            </div>
            <a
                href="{{ route('admin.pinjaman.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0"
            >
                &larr; Kembali
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- Form Input --}}
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.pinjaman.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="anggota_id" class="block text-sm font-medium text-slate-700">Anggota Peminjam (Aktif)</label>
                        <select
                            id="anggota_id"
                            name="anggota_id"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            <option value="">-- Pilih Anggota Aktif --</option>
                            @foreach ($daftarAnggotaAktif as $anggota)
                                <option value="{{ $anggota->id }}" @selected(old('anggota_id') === $anggota->id)>
                                    {{ $anggota->nomor_anggota }} — {{ $anggota->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_pinjaman" class="block text-sm font-medium text-slate-700">Jenis Pinjaman</label>
                            <select
                                id="jenis_pinjaman"
                                name="jenis_pinjaman"
                                x-model="jenisPinjaman"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                            >
                                <option value="{{ \App\Models\Pinjaman::JENIS_UANG }}">Pinjaman Uang (Margin 0% Syariah)</option>
                                <option value="{{ \App\Models\Pinjaman::JENIS_BARANG }}">Pinjaman Barang (Margin {{ number_format($persenMarginBarang, 2, ',', '.') }}%)</option>
                            </select>
                        </div>

                        <div>
                            <label for="tanggal_cair" class="block text-sm font-medium text-slate-700">Tanggal Cair</label>
                            <input
                                id="tanggal_cair"
                                name="tanggal_cair"
                                type="date"
                                required
                                value="{{ old('tanggal_cair', now()->toDateString()) }}"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                            >
                        </div>
                    </div>

                    <div x-show="jenisPinjaman === 'BARANG'" x-transition x-cloak>
                        <label for="nama_barang" class="block text-sm font-medium text-slate-700">Nama / Spesifikasi Barang</label>
                        <input
                            id="nama_barang"
                            name="nama_barang"
                            type="text"
                            :required="jenisPinjaman === 'BARANG'"
                            value="{{ old('nama_barang') }}"
                            placeholder="Contoh: Kulkas 2 Pintu Sharp / Laptop Lenovo"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pokok_pinjaman" class="block text-sm font-medium text-slate-700">Pokok Pinjaman (Rp)</label>
                            <input
                                id="pokok_pinjaman"
                                name="pokok_pinjaman"
                                type="number"
                                step="1000"
                                min="50000"
                                required
                                x-model.number="pokokPinjaman"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                            >
                            <p class="mt-1 text-xs text-slate-500">Minimal Rp 50.000 &middot; Maks {{ format_rupiah($totalKasKoperasi) }}</p>
                        </div>

                        <div>
                            <label for="tenor_bulan" class="block text-sm font-medium text-slate-700">Tenor (Bulan)</label>
                            <input
                                id="tenor_bulan"
                                name="tenor_bulan"
                                type="number"
                                min="1"
                                max="60"
                                required
                                x-model.number="tenorBulan"
                                class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                            >
                            <p class="mt-1 text-xs text-slate-500">Rentang 1 s.d. 60 bulan.</p>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-slate-700">Keterangan Tambahan (Opsional)</label>
                        <textarea
                            id="keterangan"
                            name="keterangan"
                            rows="2"
                            maxlength="500"
                            placeholder="Catatan opsional terkait pencairan..."
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a
                            href="{{ route('admin.pinjaman.index') }}"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            :disabled="melebihiKas"
                            :class="melebihiKas ? 'bg-slate-300 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 cursor-pointer'"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition"
                        >
                            Cairkan Pinjaman &amp; Buat Jadwal
                        </button>
                    </div>
                </form>
            </div>

            {{-- Panel Kalkulator Simulasi Real-Time (Alpine.js) --}}
            <aside class="rounded-2xl bg-slate-900 text-slate-100 p-6 shadow-md space-y-5 sticky top-6">
                <div>
                    <span class="inline-flex items-center rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-300">
                        Simulasi Real-Time
                    </span>
                    <h3 class="mt-2 text-lg font-bold text-white">Kalkulator Cicilan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Dihitung otomatis mengikuti pilihan jenis, pokok, dan tenor.</p>
                </div>

                <div class="rounded-xl bg-slate-800/80 p-3.5 text-xs flex items-center justify-between gap-2">
                    <span class="text-slate-400">Kas Tersedia Saat Ini</span>
                    <span class="font-bold text-emerald-400">{{ format_rupiah($totalKasKoperasi) }}</span>
                </div>

                <div x-show="melebihiKas" x-cloak class="rounded-xl bg-rose-500/20 border border-rose-400/40 p-3 text-xs text-rose-200">
                    Pokok pinjaman melebihi saldo kas koperasi yang tersedia saat ini.
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-slate-400">Pokok Pinjaman</dt>
                        <dd class="font-semibold text-white" x-text="formatRp(pokokCents)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-slate-400">
                            Margin (<span x-text="persenMargin"></span>%)
                        </dt>
                        <dd class="font-semibold text-amber-300" x-text="formatRp(marginCents)"></dd>
                    </div>
                    <div class="pt-3 border-t border-slate-700 flex items-center justify-between gap-2">
                        <dt class="font-semibold text-slate-200">Total Tagihan</dt>
                        <dd class="text-lg font-bold text-emerald-400" x-text="formatRp(totalTagihanCents)"></dd>
                    </div>
                </dl>

                <div class="rounded-xl bg-emerald-600/20 border border-emerald-500/30 p-4 space-y-2">
                    <p class="text-xs text-emerald-200">Estimasi Angsuran / Bulan (<span x-text="tenorValid"></span>x)</p>
                    <p class="text-2xl font-bold text-white" x-text="formatRp(cicilanDasarCents)"></p>
                    <p
                        x-show="cicilanTerakhirCents !== cicilanDasarCents"
                        x-cloak
                        class="text-[11px] text-emerald-200"
                    >
                        Cicilan terakhir (ke-<span x-text="tenorValid"></span>): <strong x-text="formatRp(cicilanTerakhirCents)"></strong> (penyesuaian pembulatan).
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
