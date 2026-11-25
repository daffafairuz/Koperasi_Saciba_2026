@extends('layouts.admin')

@section('title', 'Input Pembayaran Gabungan')
@section('header', 'Input Pembayaran Gabungan (Bulk Payment)')

@section('content')
    <div
        x-data="bulkPaymentForm({
            endpointBase: @js(url('/admin/pembayaran/kewajiban')),
            initialAnggotaId: @js($selectedAnggotaId),
            initialTanggalBayar: @js(old('tanggal_bayar', now()->toDateString())),
            initialMetodeBayar: @js(old('metode_bayar', \App\Models\PembayaranAnggota::METODE_TUNAI))
        })"
        x-init="if (anggotaId) { muatKewajiban(); }"
        @keydown.escape.window="if (!isSubmitting) { confirmModalOpen = false; }"
        class="max-w-5xl mx-auto space-y-6 pb-28"
    >
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Kasir Pembayaran Anggota</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Pilih anggota untuk memuat Simpanan Pokok, Tunggakan SW, SW di muka, dan Cicilan Pinjaman dalam satu struk.
                </p>
            </div>
            <a
                href="{{ route('admin.pembayaran.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition shrink-0"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form x-ref="pembayaranForm" method="POST" action="{{ route('admin.pembayaran.store') }}" @submit.prevent="openConfirmModal()" class="space-y-6">
            @csrf

            {{-- Header Pembayaran --}}
            <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs space-y-4">
                <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-zinc-400">01 // Informasi Struk Pembayaran</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="anggota_id" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Pilih Anggota</label>
                        <select
                            id="anggota_id"
                            x-ref="anggotaSelect"
                            name="anggota_id"
                            x-model="anggotaId"
                            @change="muatKewajiban()"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                            <option value="">-- Pilih Anggota --</option>
                            @foreach ($daftarAnggota as $anggota)
                                <option value="{{ $anggota->id }}">
                                    {{ $anggota->nomor_anggota }} — {{ $anggota->nama_lengkap }}{{ $anggota->status === \App\Models\User::STATUS_KELUAR ? ' [KELUAR]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tanggal_bayar" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Tanggal Pembayaran</label>
                        <input
                            id="tanggal_bayar"
                            name="tanggal_bayar"
                            type="date"
                            required
                            x-model="tanggalBayar"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="metode_bayar" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Metode Pembayaran</label>
                        <select
                            id="metode_bayar"
                            name="metode_bayar"
                            x-model="metodeBayar"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                            <option value="{{ \App\Models\PembayaranAnggota::METODE_TUNAI }}">TUNAI</option>
                            <option value="{{ \App\Models\PembayaranAnggota::METODE_TRANSFER }}">TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Catatan Struk (Opsional)</label>
                    <input
                        id="catatan"
                        name="catatan"
                        type="text"
                        maxlength="500"
                        value="{{ old('catatan') }}"
                        placeholder="Contoh: Setoran tunai di sekretariat / transfer rekening BRI"
                        class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>
            </section>

            {{-- Loading / Error Fetch State --}}
            <div x-show="loading" x-cloak class="rounded-2xl bg-white border border-zinc-200/80 p-8 text-center text-sm text-zinc-500">
                Memuat daftar kewajiban anggota...
            </div>

            <div x-show="fetchError" x-cloak class="rounded-2xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700" x-text="fetchError"></div>

            {{-- Daftar Item Kewajiban Anggota --}}
            <div x-show="!loading && loaded" x-cloak class="space-y-6">
                {{-- Bagian A: Simpanan Pokok --}}
                <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-zinc-400">02 // Simpanan Pokok</p>
                        <span
                            x-show="!simpananPokok.bisa_dibayar"
                            class="inline-flex items-center rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700"
                        >
                            Sudah Lunas
                        </span>
                    </div>

                    <template x-if="simpananPokok.bisa_dibayar">
                        <label
                            for="sp-check"
                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-4 cursor-pointer transition"
                            :class="simpananPokok.checked ? 'border-emerald-500/60 bg-emerald-500/[0.04] ring-1 ring-emerald-500/20' : 'border-zinc-200/80 hover:bg-zinc-50'"
                        >
                            <div class="flex items-center gap-3.5">
                                <input id="sp-check" type="checkbox" x-model="simpananPokok.checked" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="text-sm font-semibold text-zinc-950">Pelunasan Simpanan Pokok</p>
                                    <p class="text-xs text-zinc-500">Dibayar penuh satu kali sesuai tarif pendaftaran.</p>
                                </div>
                            </div>
                            <p class="font-mono tabular-nums text-base font-bold text-emerald-600" x-text="formatRp(simpananPokok.nominal_bayar)"></p>
                        </label>
                    </template>
                </section>

                {{-- Bagian B: Simpanan Wajib (Tunggakan + Advance Payment) --}}
                <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-zinc-400">03 // Simpanan Wajib &amp; Bayar di Muka</p>
                            <p class="text-xs text-zinc-500 mt-1">
                                Tarif SW aktif: <strong class="font-mono tabular-nums text-zinc-800" x-text="formatRp(advanceSw.nominal_sw_aktif)"></strong>/bulan.
                            </p>
                        </div>

                        <button
                            type="button"
                            x-show="advanceSw.bisa_advance"
                            @click="tambahBulanSwDiMuka()"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition cursor-pointer self-start sm:self-auto"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                            <span>Tambah Bulan SW di Muka</span>
                        </button>
                    </div>

                    <div x-show="tagihanSwList.length === 0 && advanceSwList.length === 0" class="rounded-xl bg-zinc-50 border border-zinc-200/60 p-4 text-xs text-zinc-500 text-center">
                        Tidak ada tunggakan Simpanan Wajib. Gunakan tombol "Tambah Bulan SW di Muka" untuk pembayaran di muka.
                    </div>

                    <div class="space-y-2.5">
                        {{-- Tunggakan SW yang sudah terbit --}}
                        <template x-for="sw in tagihanSwList" :key="sw.id">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-4 transition"
                                :class="sw.checked ? 'border-emerald-500/60 bg-emerald-500/[0.04] ring-1 ring-emerald-500/20' : 'border-zinc-200/80 hover:bg-zinc-50/60'"
                            >
                                <label class="flex items-center gap-3.5 cursor-pointer flex-1" :for="'sw-check-' + sw.id">
                                    <input :id="'sw-check-' + sw.id" type="checkbox" x-model="sw.checked" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-zinc-950" x-text="'SW ' + sw.label_periode"></span>
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-[10px] font-semibold"
                                                :class="sw.status === 'SEBAGIAN' ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-rose-500/10 text-rose-700 border-rose-500/20'"
                                                x-text="sw.status === 'SEBAGIAN' ? 'SEBAGIAN' : 'BELUM BAYAR'"
                                            ></span>
                                        </div>
                                        <p class="text-xs text-zinc-500 mt-0.5">
                                            Sisa tagihan: <span class="font-mono tabular-nums font-semibold text-zinc-700" x-text="formatRp(sw.sisa_tagihan)"></span>
                                        </p>
                                    </div>
                                </label>

                                <div class="flex items-center gap-2 sm:w-48">
                                    <span class="font-mono text-xs text-zinc-400">Rp</span>
                                    <input
                                        :id="'sw-nominal-' + sw.id"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :max="sw.sisa_tagihan"
                                        x-model.number="sw.nominal_bayar"
                                        :disabled="!sw.checked"
                                        class="w-full rounded-lg border border-zinc-200 px-3 py-1.5 font-mono tabular-nums text-sm text-right font-semibold text-zinc-950 disabled:bg-zinc-100 disabled:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    >
                                </div>
                            </div>
                        </template>

                        {{-- Item SW Bayar di Muka (Advance Payment) --}}
                        <template x-for="(adv, idx) in advanceSwList" :key="adv.periode">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-emerald-500/40 bg-emerald-500/[0.05] p-4">
                                <label class="flex items-center gap-3.5 cursor-pointer flex-1" :for="'adv-check-' + adv.periode">
                                    <input :id="'adv-check-' + adv.periode" type="checkbox" x-model="adv.checked" class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-zinc-950" x-text="'SW ' + adv.label_periode"></span>
                                            <span class="rounded-full bg-emerald-500/15 border border-emerald-500/25 px-2 py-0.5 text-[10px] font-semibold text-emerald-800">BAYAR DI MUKA</span>
                                        </div>
                                        <p class="text-xs text-zinc-500 mt-0.5">Tagihan periode ini akan diterbitkan &amp; dilunasi otomatis.</p>
                                    </div>
                                </label>

                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5 sm:w-40">
                                        <span class="font-mono text-xs text-zinc-400">Rp</span>
                                        <input
                                            :id="'adv-nominal-' + adv.periode"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            :max="adv.nominal_tagihan"
                                            x-model.number="adv.nominal_bayar"
                                            :disabled="!adv.checked"
                                            class="w-full rounded-lg border border-zinc-200 px-3 py-1.5 font-mono tabular-nums text-sm text-right font-semibold text-zinc-950 disabled:bg-zinc-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        @click="hapusBulanSwDiMuka(idx)"
                                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-rose-500/10 hover:text-rose-600 cursor-pointer transition"
                                        title="Hapus"
                                        aria-label="Hapus bulan bayar di muka"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 6 6 18" />
                                            <path d="m6 6 12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                {{-- Bagian C: Cicilan Pinjaman Berjalan --}}
                <section class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs space-y-4">
                    <div>
                        <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-zinc-400">04 // Cicilan Pinjaman Berjalan</p>
                        <p class="text-xs text-zinc-500 mt-1">Nominal dapat diubah lebih kecil dari sisa tagihan untuk pembayaran sebagian (SEBAGIAN).</p>
                    </div>

                    <div x-show="cicilanList.length === 0" class="rounded-xl bg-zinc-50 border border-zinc-200/60 p-4 text-xs text-zinc-500 text-center">
                        Anggota ini tidak memiliki tagihan cicilan pinjaman yang belum lunas.
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="cicilan in cicilanList" :key="cicilan.id">
                            <div
                                class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-4 transition"
                                :class="cicilan.checked ? 'border-emerald-500/60 bg-emerald-500/[0.04] ring-1 ring-emerald-500/20' : 'border-zinc-200/80 hover:bg-zinc-50/60'"
                            >
                                <label class="flex items-start gap-3.5 cursor-pointer flex-1" :for="'cicilan-check-' + cicilan.id">
                                    <input :id="'cicilan-check-' + cicilan.id" type="checkbox" x-model="cicilan.checked" class="mt-1 h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-[10px] font-semibold"
                                                :class="cicilan.jenis_pinjaman === 'BARANG' ? 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20'"
                                                x-text="cicilan.jenis_pinjaman"
                                            ></span>
                                            <span
                                                class="font-mono tabular-nums text-sm font-semibold text-zinc-950"
                                                x-text="cicilan.nomor_pinjaman + ' · Cicilan ke-' + cicilan.cicilan_ke + '/' + cicilan.tenor_bulan"
                                            ></span>
                                            <span
                                                class="rounded-full border px-2 py-0.5 text-[10px] font-semibold"
                                                :class="cicilan.status === 'SEBAGIAN' ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-rose-500/10 text-rose-700 border-rose-500/20'"
                                                x-text="cicilan.status === 'SEBAGIAN' ? 'SEBAGIAN' : 'BELUM BAYAR'"
                                            ></span>
                                        </div>
                                        <p class="text-xs text-zinc-600 mt-1">
                                            <span x-show="cicilan.nama_barang" x-text="cicilan.nama_barang + ' · '"></span>
                                            Jatuh tempo: <span class="tabular-nums" x-text="cicilan.jatuh_tempo"></span>
                                            &middot; Sisa: <strong class="font-mono tabular-nums text-zinc-900" x-text="formatRp(cicilan.sisa_tagihan)"></strong>
                                        </p>
                                    </div>
                                </label>

                                <div class="flex items-center gap-2 sm:w-48">
                                    <span class="font-mono text-xs text-zinc-400">Rp</span>
                                    <input
                                        :id="'cicilan-nominal-' + cicilan.id"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :max="cicilan.sisa_tagihan"
                                        x-model.number="cicilan.nominal_bayar"
                                        :disabled="!cicilan.checked"
                                        class="w-full rounded-lg border border-zinc-200 px-3 py-1.5 font-mono tabular-nums text-sm text-right font-semibold text-zinc-950 disabled:bg-zinc-100 disabled:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                    >
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            {{-- Hidden Payload Dinamis hanya untuk Item Tercentang --}}
            <template x-for="(item, idx) in itemTerpilih" :key="item.key">
                <div>
                    <input type="hidden" :name="'items[' + idx + '][jenis_pembayaran]'" :value="item.jenis_pembayaran">
                    <input type="hidden" :name="'items[' + idx + '][simpanan_anggota_id]'" :value="item.simpanan_anggota_id ?? ''">
                    <input type="hidden" :name="'items[' + idx + '][tagihan_sw_id]'" :value="item.tagihan_sw_id ?? ''">
                    <input type="hidden" :name="'items[' + idx + '][periode_advance]'" :value="item.periode_advance ?? ''">
                    <input type="hidden" :name="'items[' + idx + '][cicilan_pinjaman_id]'" :value="item.cicilan_pinjaman_id ?? ''">
                    <input type="hidden" :name="'items[' + idx + '][nominal_bayar]'" :value="item.nominal_bayar">
                </div>
            </template>

            {{-- Sticky Bottom Bar Obsidian: Total Uang Diterima --}}
            <div class="sticky bottom-4 z-20 rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/25 p-5 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-zinc-400">
                        Total Uang Diterima (<span class="font-mono tabular-nums font-semibold text-white" x-text="itemTerpilih.length"></span> item tercentang)
                    </p>
                    <p class="font-mono tabular-nums text-2xl sm:text-3xl font-bold text-emerald-400 mt-0.5" x-text="formatRp(totalDiterima)"></p>
                </div>

                <button
                    type="button"
                    @click="openConfirmModal()"
                    :disabled="!anggotaId || itemTerpilih.length === 0 || totalDiterima <= 0 || isSubmitting"
                    :class="!anggotaId || itemTerpilih.length === 0 || totalDiterima <= 0 || isSubmitting ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-500 text-white cursor-pointer shadow-sm'"
                    class="rounded-xl px-6 py-3 text-sm font-semibold transition shrink-0"
                >
                    Simpan Pembayaran &amp; Terbitkan Struk
                </button>
            </div>
        </form>

        {{-- Modal Konfirmasi Pembayaran Gabungan (Confirmation Guard) --}}
        <div
            x-show="confirmModalOpen"
            x-cloak
            x-transition.opacity
            @click.self="if (!isSubmitting) { confirmModalOpen = false; }"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-konfirmasi-pembayaran-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white border border-zinc-200 p-6 shadow-2xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                                <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                                <path d="M12 17.5v-11" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="modal-konfirmasi-pembayaran-title" class="text-base font-bold tracking-tight text-zinc-950">
                                Konfirmasi Simpan Pembayaran
                            </h3>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                Periksa ringkasan setoran berikut sebelum struk resmi diterbitkan.
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
                        <dt class="text-xs text-zinc-500 shrink-0">Anggota Pembayar</dt>
                        <dd class="text-right font-semibold text-zinc-950" x-text="selectedAnggotaNama"></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-zinc-500">Tanggal &amp; Metode</dt>
                        <dd class="text-right font-medium text-zinc-900">
                            <span class="tabular-nums" x-text="formatTanggalIndo(tanggalBayar)"></span>
                            <span class="ml-1.5 inline-flex items-center rounded-md bg-zinc-200/70 px-2 py-0.5 text-[10px] font-semibold text-zinc-800" x-text="metodeBayar"></span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-zinc-500">Item Dibayarkan</dt>
                        <dd class="font-semibold text-zinc-900 tabular-nums" x-text="itemTerpilih.length + ' item kewajiban terpilih'"></dd>
                    </div>
                    <div class="pt-3 border-t border-zinc-200 flex items-center justify-between gap-3">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-600">Total Uang Diterima</dt>
                        <dd class="font-mono text-2xl text-emerald-600 font-bold tabular-nums" x-text="formatRp(totalDiterima)"></dd>
                    </div>
                </dl>

                <div class="flex items-center justify-end gap-2.5 pt-1">
                    <button
                        type="button"
                        @click="confirmModalOpen = false"
                        :disabled="isSubmitting"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Batal / Cek Lagi
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
                        <span x-text="isSubmitting ? 'Menyimpan Struk...' : 'Konfirmasi & Simpan Struk'">Konfirmasi &amp; Simpan Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function bulkPaymentForm(config) {
            const namaBulan = [
                '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            return {
                anggotaId: config.initialAnggotaId || '',
                tanggalBayar: config.initialTanggalBayar || '',
                metodeBayar: config.initialMetodeBayar || 'TUNAI',
                selectedAnggotaNama: '',
                confirmModalOpen: false,
                isSubmitting: false,
                loading: false,
                loaded: false,
                fetchError: '',

                simpananPokok: {
                    simpanan_anggota_id: null,
                    nominal: 0,
                    bisa_dibayar: false,
                    checked: false,
                    nominal_bayar: 0,
                },
                tagihanSwList: [],
                advanceSw: {
                    bisa_advance: false,
                    periode_berikutnya: '',
                    nominal_sw_aktif: 50000,
                },
                advanceSwList: [],
                cicilanList: [],

                async muatKewajiban() {
                    this.fetchError = '';
                    this.advanceSwList = [];

                    if (!this.anggotaId) {
                        this.loaded = false;
                        return;
                    }

                    this.loading = true;
                    try {
                        const res = await fetch(`${config.endpointBase}/${this.anggotaId}`, {
                            headers: { 'Accept': 'application/json' },
                        });

                        if (!res.ok) {
                            throw new Error('Gagal memuat data kewajiban anggota.');
                        }

                        const data = await res.json();

                        this.simpananPokok = {
                            ...data.simpanan_pokok,
                            checked: false,
                            nominal_bayar: Number(data.simpanan_pokok.sisa) || 0,
                        };

                        this.tagihanSwList = (data.tagihan_sw || []).map((item) => ({
                            ...item,
                            checked: false,
                            nominal_bayar: Number(item.sisa_tagihan) || 0,
                        }));

                        this.advanceSw = data.advance_sw || {
                            bisa_advance: false,
                            periode_berikutnya: '',
                            nominal_sw_aktif: 50000,
                        };

                        this.cicilanList = (data.cicilan_pinjaman || []).map((item) => ({
                            ...item,
                            checked: false,
                            nominal_bayar: Number(item.sisa_tagihan) || 0,
                        }));

                        this.loaded = true;
                    } catch (e) {
                        this.fetchError = e.message || 'Terjadi kesalahan saat mengambil data.';
                        this.loaded = false;
                    } finally {
                        this.loading = false;
                    }
                },

                nextPeriode(yyyymm) {
                    let tahun = parseInt(yyyymm.slice(0, 4), 10);
                    let bulan = parseInt(yyyymm.slice(4, 6), 10) + 1;
                    if (bulan > 12) {
                        bulan = 1;
                        tahun += 1;
                    }
                    return String(tahun) + String(bulan).padStart(2, '0');
                },

                labelPeriode(yyyymm) {
                    const tahun = yyyymm.slice(0, 4);
                    const bulan = parseInt(yyyymm.slice(4, 6), 10);
                    return `${namaBulan[bulan] || ''} ${tahun}`;
                },

                tambahBulanSwDiMuka() {
                    if (!this.advanceSw.bisa_advance || !this.advanceSw.periode_berikutnya) {
                        return;
                    }

                    const periodeBaru = this.advanceSwList.length === 0
                        ? this.advanceSw.periode_berikutnya
                        : this.nextPeriode(this.advanceSwList[this.advanceSwList.length - 1].periode);

                    this.advanceSwList.push({
                        periode: periodeBaru,
                        label_periode: this.labelPeriode(periodeBaru),
                        nominal_tagihan: Number(this.advanceSw.nominal_sw_aktif) || 50000,
                        nominal_bayar: Number(this.advanceSw.nominal_sw_aktif) || 50000,
                        checked: true,
                    });
                },

                hapusBulanSwDiMuka(index) {
                    this.advanceSwList.splice(index, 1);
                },

                get itemTerpilih() {
                    const items = [];

                    if (this.simpananPokok.bisa_dibayar && this.simpananPokok.checked && Number(this.simpananPokok.nominal_bayar) > 0) {
                        items.push({
                            key: 'sp-' + this.simpananPokok.simpanan_anggota_id,
                            jenis_pembayaran: 'SIMPANAN_POKOK',
                            simpanan_anggota_id: this.simpananPokok.simpanan_anggota_id,
                            nominal_bayar: Number(this.simpananPokok.nominal_bayar),
                        });
                    }

                    for (const sw of this.tagihanSwList) {
                        if (sw.checked && Number(sw.nominal_bayar) > 0) {
                            items.push({
                                key: 'sw-' + sw.id,
                                jenis_pembayaran: 'SIMPANAN_WAJIB',
                                tagihan_sw_id: sw.id,
                                nominal_bayar: Number(sw.nominal_bayar),
                            });
                        }
                    }

                    for (const adv of this.advanceSwList) {
                        if (adv.checked && Number(adv.nominal_bayar) > 0) {
                            items.push({
                                key: 'adv-' + adv.periode,
                                jenis_pembayaran: 'SIMPANAN_WAJIB',
                                periode_advance: adv.periode,
                                nominal_bayar: Number(adv.nominal_bayar),
                            });
                        }
                    }

                    for (const c of this.cicilanList) {
                        if (c.checked && Number(c.nominal_bayar) > 0) {
                            items.push({
                                key: 'cicilan-' + c.id,
                                jenis_pembayaran: 'CICILAN_PINJAMAN',
                                cicilan_pinjaman_id: c.id,
                                nominal_bayar: Number(c.nominal_bayar),
                            });
                        }
                    }

                    return items;
                },

                get totalDiterima() {
                    return this.itemTerpilih.reduce((acc, item) => acc + (Number(item.nominal_bayar) || 0), 0);
                },

                formatRp(angka) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(Number(angka) || 0));
                },

                formatTanggalIndo(isoDate) {
                    if (!isoDate || typeof isoDate !== 'string') {
                        return '-';
                    }
                    const parts = isoDate.split('-');
                    if (parts.length !== 3) {
                        return isoDate;
                    }
                    const [tahun, bulan, hari] = parts;
                    return `${parseInt(hari, 10)} ${namaBulan[parseInt(bulan, 10)] || bulan} ${tahun}`;
                },

                openConfirmModal() {
                    if (!this.anggotaId || this.itemTerpilih.length === 0 || this.totalDiterima <= 0 || this.isSubmitting) {
                        return;
                    }
                    if (!this.$refs.pembayaranForm.reportValidity()) {
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
                    this.$refs.pembayaranForm.submit();
                },
            };
        }
    </script>
@endsection
