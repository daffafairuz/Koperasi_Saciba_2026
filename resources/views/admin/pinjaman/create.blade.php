@extends('layouts.admin')

@section('title', 'Cairkan Pinjaman Baru')
@section('header', 'Pencairan Pinjaman Baru')

@section('content')
    <div
        x-data="{
            jenisPinjaman: @js(old('jenis_pinjaman', \App\Models\Pinjaman::JENIS_UANG)),
            namaBarang: @js(old('nama_barang', '')),
            pokokPinjaman: Number(@js(old('pokok_pinjaman', 1000000))) || 0,
            tenorBulan: Number(@js(old('tenor_bulan', 10))) || 1,
            marginBarangPersen: {{ number_format($persenMarginBarang, 2, '.', '') }},
            maxKas: {{ number_format($totalKasKoperasiAngka, 2, '.', '') }},
            confirmModalOpen: false,
            isSubmitting: false,
            selectedAnggotaNama: '',

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
            },
            openConfirmModal() {
                if (this.melebihiKas || this.isSubmitting) {
                    return;
                }
                if (!this.$refs.pinjamanForm.reportValidity()) {
                    return;
                }
                const selectEl = this.$refs.anggotaSelect;
                this.selectedAnggotaNama = selectEl && selectEl.selectedIndex > 0
                    ? selectEl.options[selectEl.selectedIndex].text.trim()
                    : '-';
                this.confirmModalOpen = true;
            },
            confirmSubmit() {
                if (this.isSubmitting) {
                    return;
                }
                this.isSubmitting = true;
                this.$refs.pinjamanForm.submit();
            }
        }"
        @keydown.escape.window="if (!isSubmitting) { confirmModalOpen = false; }"
        class="max-w-5xl mx-auto space-y-6"
    >
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Form Pencairan Pinjaman</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Jadwal angsuran bulanan dibuat otomatis saat pencairan disimpan (penyesuaian pembulatan pada cicilan terakhir).
                </p>
            </div>
            <a
                href="{{ route('admin.pinjaman.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition shrink-0"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Form Input (Crisp White Card) --}}
            <div class="lg:col-span-7 rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs">
                @if ($errors->any())
                    <div class="mb-5 rounded-xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form x-ref="pinjamanForm" method="POST" action="{{ route('admin.pinjaman.store') }}" @submit.prevent="openConfirmModal()" class="space-y-5">
                    @csrf

                    <div>
                        <label for="anggota_id" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Anggota Peminjam (Aktif)</label>
                        <select
                            id="anggota_id"
                            x-ref="anggotaSelect"
                            name="anggota_id"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
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
                            <label for="jenis_pinjaman" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Jenis Pinjaman</label>
                            <select
                                id="jenis_pinjaman"
                                name="jenis_pinjaman"
                                x-model="jenisPinjaman"
                                required
                                class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            >
                                <option value="{{ \App\Models\Pinjaman::JENIS_UANG }}">Pinjaman Uang (Margin 0% Syariah)</option>
                                <option value="{{ \App\Models\Pinjaman::JENIS_BARANG }}">Pinjaman Barang (Margin {{ number_format($persenMarginBarang, 2, ',', '.') }}%)</option>
                            </select>
                        </div>

                        <div>
                            <label for="tanggal_cair" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Tanggal Cair</label>
                            <input
                                id="tanggal_cair"
                                name="tanggal_cair"
                                type="date"
                                required
                                value="{{ old('tanggal_cair', now()->toDateString()) }}"
                                class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            >
                        </div>
                    </div>

                    <div x-show="jenisPinjaman === 'BARANG'" x-transition x-cloak>
                        <label for="nama_barang" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nama / Spesifikasi Barang</label>
                        <input
                            id="nama_barang"
                            name="nama_barang"
                            type="text"
                            x-model="namaBarang"
                            :required="jenisPinjaman === 'BARANG'"
                            placeholder="Contoh: Kulkas 2 Pintu Sharp / Laptop Lenovo"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="pokok_pinjaman" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Pokok Pinjaman (Rp)</label>
                            <input
                                id="pokok_pinjaman"
                                name="pokok_pinjaman"
                                type="number"
                                step="1000"
                                min="50000"
                                required
                                x-model.number="pokokPinjaman"
                                class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            >
                            <p class="mt-1 text-xs text-zinc-500 font-mono tabular-nums">Min Rp 50.000 &middot; Maks {{ format_rupiah($totalKasKoperasi) }}</p>
                        </div>

                        <div>
                            <label for="tenor_bulan" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Tenor (Bulan)</label>
                            <input
                                id="tenor_bulan"
                                name="tenor_bulan"
                                type="number"
                                min="1"
                                max="60"
                                required
                                x-model.number="tenorBulan"
                                class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                            >
                            <p class="mt-1 text-xs text-zinc-500">Rentang 1 s.d. 60 bulan.</p>
                        </div>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Keterangan Tambahan (Opsional)</label>
                        <textarea
                            id="keterangan"
                            name="keterangan"
                            rows="2"
                            maxlength="500"
                            placeholder="Catatan opsional terkait pencairan..."
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >{{ old('keterangan') }}</textarea>
                    </div>

                    <div class="pt-4 border-t border-zinc-100 flex items-center justify-end gap-3">
                        <a
                            href="{{ route('admin.pinjaman.index') }}"
                            class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition"
                        >
                            Batal
                        </a>
                        <button
                            type="button"
                            @click="openConfirmModal()"
                            :disabled="melebihiKas || isSubmitting"
                            :class="melebihiKas || isSubmitting ? 'bg-zinc-300 text-zinc-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer shadow-xs'"
                            class="rounded-xl px-5 py-2.5 text-sm font-semibold transition"
                        >
                            Cairkan Pinjaman &amp; Buat Jadwal
                        </button>
                    </div>
                </form>
            </div>

            {{-- Panel Kalkulator Simulasi Real-Time Obsidian (Alpine.js) --}}
            <aside class="lg:col-span-5 rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-6 shadow-md space-y-5 sticky top-20">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/25 px-2.5 py-0.5 font-mono text-[10px] font-semibold uppercase tracking-widest text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Simulasi Real-Time
                        </span>
                        <h3 class="mt-2 text-lg font-bold tracking-tight text-white">Kalkulator Cicilan</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">Otomatis mengikuti jenis, pokok, dan tenor.</p>
                    </div>
                    <div class="h-9 w-9 rounded-xl bg-white/5 border border-white/10 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="20" x="4" y="2" rx="2" />
                            <line x1="8" x2="16" y1="6" y2="6" />
                            <line x1="16" x2="16" y1="14" y2="18" />
                            <path d="M16 10h.01" />
                            <path d="M12 10h.01" />
                            <path d="M8 10h.01" />
                            <path d="M12 14h.01" />
                            <path d="M8 14h.01" />
                            <path d="M12 18h.01" />
                            <path d="M8 18h.01" />
                        </svg>
                    </div>
                </div>

                <div class="rounded-xl bg-white/[0.04] border border-white/10 px-4 py-3 text-xs flex items-center justify-between gap-2">
                    <span class="text-zinc-400">Kas Tersedia Saat Ini</span>
                    <span class="font-mono tabular-nums font-bold text-emerald-400">{{ format_rupiah($totalKasKoperasi) }}</span>
                </div>

                <div x-show="melebihiKas" x-cloak class="rounded-xl bg-rose-500/15 border border-rose-500/30 p-3 text-xs text-rose-200">
                    Pokok pinjaman melebihi saldo kas koperasi yang tersedia saat ini.
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-zinc-400">Pokok Pinjaman</dt>
                        <dd class="font-mono tabular-nums font-semibold text-white" x-text="formatRp(pokokCents)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-zinc-400">
                            Margin (<span class="font-mono tabular-nums" x-text="persenMargin"></span>%)
                        </dt>
                        <dd class="font-mono tabular-nums font-semibold text-amber-300" x-text="formatRp(marginCents)"></dd>
                    </div>
                    <div class="pt-3 border-t border-white/10 flex items-center justify-between gap-2">
                        <dt class="font-semibold text-zinc-200">Total Tagihan</dt>
                        <dd class="font-mono tabular-nums text-lg font-bold text-emerald-400" x-text="formatRp(totalTagihanCents)"></dd>
                    </div>
                </dl>

                <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/25 p-4 space-y-1.5">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-200">
                        Estimasi Angsuran / Bulan (<span class="font-mono tabular-nums" x-text="tenorValid"></span>x)
                    </p>
                    <p class="font-mono tabular-nums text-3xl font-bold text-white" x-text="formatRp(cicilanDasarCents)"></p>
                    <p
                        x-show="cicilanTerakhirCents !== cicilanDasarCents"
                        x-cloak
                        class="text-[11px] text-emerald-200/90"
                    >
                        Cicilan terakhir (ke-<span class="font-mono" x-text="tenorValid"></span>):
                        <strong class="font-mono tabular-nums text-white" x-text="formatRp(cicilanTerakhirCents)"></strong> (penyesuaian pembulatan).
                    </p>
                </div>
            </aside>
        </div>

        {{-- Modal Konfirmasi Pencairan Pinjaman (Confirmation Guard) --}}
        <div
            x-show="confirmModalOpen"
            x-cloak
            x-transition.opacity
            @click.self="if (!isSubmitting) { confirmModalOpen = false; }"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-konfirmasi-pinjaman-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white border border-zinc-200 p-6 shadow-2xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="modal-konfirmasi-pinjaman-title" class="text-base font-bold tracking-tight text-zinc-950">
                                Konfirmasi Pencairan Pinjaman
                            </h3>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                Pastikan seluruh rincian kontrak sudah benar sebelum kas dicairkan.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="if (!isSubmitting) { confirmModalOpen = false; }"
                        :disabled="isSubmitting"
                        aria-label="Tutup modal konfirmasi"
                        class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 cursor-pointer disabled:opacity-40"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                <dl class="rounded-xl bg-zinc-50 border border-zinc-200/80 p-4 space-y-2.5 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-xs text-zinc-500 shrink-0">Anggota Penerima</dt>
                        <dd class="text-right font-semibold text-zinc-950" x-text="selectedAnggotaNama"></dd>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <dt class="text-xs text-zinc-500 shrink-0">Jenis Pinjaman</dt>
                        <dd class="text-right font-semibold text-zinc-900">
                            <span x-text="jenisPinjaman === 'BARANG' ? 'BARANG' : 'UANG (0% Syariah)'"></span>
                            <span x-show="jenisPinjaman === 'BARANG' && namaBarang" class="block text-xs font-normal text-zinc-500" x-text="namaBarang"></span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-zinc-500">Pokok Pinjaman</dt>
                        <dd class="font-mono tabular-nums font-semibold text-zinc-950" x-text="formatRp(pokokCents)"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-zinc-500">Tenor &amp; Cicilan/Bln</dt>
                        <dd class="font-mono tabular-nums font-semibold text-zinc-900">
                            <span x-text="tenorValid"></span> bln &middot; <span x-text="formatRp(cicilanDasarCents)"></span>
                        </dd>
                    </div>
                    <div class="pt-2.5 border-t border-zinc-200 flex items-center justify-between gap-3">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-600">Total Tagihan Akhir</dt>
                        <dd class="font-mono tabular-nums text-lg font-bold text-emerald-600" x-text="formatRp(totalTagihanCents)"></dd>
                    </div>
                </dl>

                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <button
                        type="button"
                        @click="confirmModalOpen = false"
                        :disabled="isSubmitting"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Periksa Kembali
                    </button>
                    <button
                        type="button"
                        @click="confirmSubmit()"
                        :disabled="isSubmitting"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 px-4 py-2.5 text-xs font-semibold text-white shadow-xs transition cursor-pointer disabled:bg-emerald-400 disabled:cursor-not-allowed"
                    >
                        <svg x-show="isSubmitting" x-cloak class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v3a5 5 0 0 0-5 5H4Z"></path>
                        </svg>
                        <span x-text="isSubmitting ? 'Memproses Pencairan...' : 'Ya, Cairkan Pinjaman'">Ya, Cairkan Pinjaman</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
