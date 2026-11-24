@extends('layouts.admin')

@section('title', 'Input Pembayaran Gabungan')
@section('header', 'Input Pembayaran Gabungan (Bulk Payment)')

@section('content')
    <div
        x-data="bulkPaymentForm({
            endpointBase: @js(url('/admin/pembayaran/kewajiban')),
            initialAnggotaId: @js($selectedAnggotaId)
        })"
        x-init="if (anggotaId) { muatKewajiban(); }"
        class="max-w-5xl mx-auto space-y-6 pb-24"
    >
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Kasir Pembayaran Anggota</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Pilih anggota untuk memuat Simpanan Pokok, Tunggakan SW, SW di muka, dan Cicilan Pinjaman dalam satu struk.
                </p>
            </div>
            <a
                href="{{ route('admin.pembayaran.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0"
            >
                &larr; Kembali
            </a>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pembayaran.store') }}" class="space-y-6">
            @csrf

            {{-- Header Pembayaran --}}
            <section class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">1. Informasi Struk Pembayaran</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="anggota_id" class="block text-sm font-medium text-slate-700">Pilih Anggota</label>
                        <select
                            id="anggota_id"
                            name="anggota_id"
                            x-model="anggotaId"
                            @change="muatKewajiban()"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
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
                        <label for="tanggal_bayar" class="block text-sm font-medium text-slate-700">Tanggal Pembayaran</label>
                        <input
                            id="tanggal_bayar"
                            name="tanggal_bayar"
                            type="date"
                            required
                            value="{{ old('tanggal_bayar', now()->toDateString()) }}"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label for="metode_bayar" class="block text-sm font-medium text-slate-700">Metode Pembayaran</label>
                        <select
                            id="metode_bayar"
                            name="metode_bayar"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            <option value="{{ \App\Models\PembayaranAnggota::METODE_TUNAI }}" @selected(old('metode_bayar', \App\Models\PembayaranAnggota::METODE_TUNAI) === \App\Models\PembayaranAnggota::METODE_TUNAI)>TUNAI</option>
                            <option value="{{ \App\Models\PembayaranAnggota::METODE_TRANSFER }}" @selected(old('metode_bayar') === \App\Models\PembayaranAnggota::METODE_TRANSFER)>TRANSFER</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="catatan" class="block text-sm font-medium text-slate-700">Catatan Struk (Opsional)</label>
                    <input
                        id="catatan"
                        name="catatan"
                        type="text"
                        maxlength="500"
                        value="{{ old('catatan') }}"
                        placeholder="Contoh: Setoran tunai di sekretariat / transfer rekening BRI"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                </div>
            </section>

            {{-- Loading / Error Fetch State --}}
            <div x-show="loading" x-cloak class="rounded-2xl bg-white border border-slate-200 p-8 text-center text-sm text-slate-500">
                Memuat daftar kewajiban anggota...
            </div>

            <div x-show="fetchError" x-cloak class="rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700" x-text="fetchError"></div>

            {{-- Daftar Item Kewajiban Anggota --}}
            <div x-show="!loading && loaded" x-cloak class="space-y-6">
                {{-- Bagian A: Simpanan Pokok --}}
                <section class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">2. Simpanan Pokok</h3>
                        <span
                            x-show="!simpananPokok.bisa_dibayar"
                            class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800"
                        >
                            Sudah Lunas
                        </span>
                    </div>

                    <template x-if="simpananPokok.bisa_dibayar">
                        <label class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-4 cursor-pointer transition"
                               :class="simpananPokok.checked ? 'border-emerald-400 bg-emerald-50/50' : 'border-slate-200 hover:bg-slate-50'">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" x-model="simpananPokok.checked" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">Pelunasan Simpanan Pokok</p>
                                    <p class="text-xs text-slate-500">Dibayar penuh satu kali sesuai tarif pendaftaran.</p>
                                </div>
                            </div>
                            <p class="text-base font-bold text-emerald-700" x-text="formatRp(simpananPokok.nominal_bayar)"></p>
                        </label>
                    </template>
                </section>

                {{-- Bagian B: Simpanan Wajib (Tunggakan + Advance Payment) --}}
                <section class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">3. Simpanan Wajib (Tunggakan &amp; Bayar di Muka)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Tarif SW aktif: <strong x-text="formatRp(advanceSw.nominal_sw_aktif)"></strong>/bulan.
                            </p>
                        </div>

                        <button
                            type="button"
                            x-show="advanceSw.bisa_advance"
                            @click="tambahBulanSwDiMuka()"
                            class="inline-flex items-center justify-center rounded-xl bg-teal-50 border border-teal-200 px-3.5 py-2 text-xs font-semibold text-teal-800 hover:bg-teal-100 transition cursor-pointer self-start sm:self-auto"
                        >
                            + Tambah Bulan SW di Muka
                        </button>
                    </div>

                    <div x-show="tagihanSwList.length === 0 && advanceSwList.length === 0" class="rounded-xl bg-slate-50 p-4 text-xs text-slate-500 text-center">
                        Tidak ada tunggakan Simpanan Wajib. Gunakan tombol "+ Tambah Bulan SW di Muka" untuk pembayaran di muka.
                    </div>

                    <div class="space-y-2.5">
                        {{-- Tunggakan SW yang sudah terbit --}}
                        <template x-for="sw in tagihanSwList" :key="sw.id">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-3.5 transition"
                                 :class="sw.checked ? 'border-emerald-400 bg-emerald-50/40' : 'border-slate-200'">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" x-model="sw.checked" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-900" x-text="'SW ' + sw.label_periode"></span>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                  :class="sw.status === 'SEBAGIAN' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'"
                                                  x-text="sw.status === 'SEBAGIAN' ? 'SEBAGIAN' : 'BELUM BAYAR'"></span>
                                        </div>
                                        <p class="text-xs text-slate-500">
                                            Sisa tagihan: <span class="font-medium text-slate-700" x-text="formatRp(sw.sisa_tagihan)"></span>
                                        </p>
                                    </div>
                                </label>

                                <div class="flex items-center gap-2 sm:w-48">
                                    <span class="text-xs text-slate-500">Rp</span>
                                    <input
                                        type="number"
                                        step="100"
                                        min="1"
                                        :max="sw.sisa_tagihan"
                                        x-model.number="sw.nominal_bayar"
                                        :disabled="!sw.checked"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-right font-semibold text-slate-900 disabled:bg-slate-100 disabled:text-slate-400 focus:border-emerald-500 focus:outline-none"
                                    >
                                </div>
                            </div>
                        </template>

                        {{-- Item SW Bayar di Muka (Advance Payment) --}}
                        <template x-for="(adv, idx) in advanceSwList" :key="adv.periode">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border border-teal-300 bg-teal-50/40 p-3.5">
                                <label class="flex items-center gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" x-model="adv.checked" class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-900" x-text="'SW ' + adv.label_periode"></span>
                                            <span class="rounded-full bg-teal-100 px-2 py-0.5 text-[10px] font-semibold text-teal-800">BAYAR DI MUKA</span>
                                        </div>
                                        <p class="text-xs text-slate-500">Tagihan periode ini akan diterbitkan &amp; dilunasi otomatis.</p>
                                    </div>
                                </label>

                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-1.5 sm:w-40">
                                        <span class="text-xs text-slate-500">Rp</span>
                                        <input
                                            type="number"
                                            step="100"
                                            min="1"
                                            :max="adv.nominal_tagihan"
                                            x-model.number="adv.nominal_bayar"
                                            :disabled="!adv.checked"
                                            class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-right font-semibold text-slate-900 disabled:bg-slate-100 focus:border-teal-500 focus:outline-none"
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        @click="hapusBulanSwDiMuka(idx)"
                                        class="rounded-lg px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 cursor-pointer"
                                        title="Hapus"
                                    >
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>

                {{-- Bagian C: Cicilan Pinjaman Berjalan --}}
                <section class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs space-y-4">
                    <div>
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">4. Cicilan Pinjaman Berjalan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Nominal bisa diubah lebih kecil dari sisa tagihan untuk pembayaran cicilan sebagian (SEBAGIAN).</p>
                    </div>

                    <div x-show="cicilanList.length === 0" class="rounded-xl bg-slate-50 p-4 text-xs text-slate-500 text-center">
                        Anggota ini tidak memiliki tagihan cicilan pinjaman yang belum lunas.
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="cicilan in cicilanList" :key="cicilan.id">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 rounded-xl border p-3.5 transition"
                                 :class="cicilan.checked ? 'border-emerald-400 bg-emerald-50/40' : 'border-slate-200'">
                                <label class="flex items-start gap-3 cursor-pointer flex-1">
                                    <input type="checkbox" x-model="cicilan.checked" class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="rounded-md px-2 py-0.5 text-[10px] font-semibold"
                                                  :class="cicilan.jenis_pinjaman === 'BARANG' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'"
                                                  x-text="cicilan.jenis_pinjaman"></span>
                                            <span class="text-sm font-semibold text-slate-900"
                                                  x-text="cicilan.nomor_pinjaman + ' · Cicilan ke-' + cicilan.cicilan_ke + '/' + cicilan.tenor_bulan"></span>
                                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                                  :class="cicilan.status === 'SEBAGIAN' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'"
                                                  x-text="cicilan.status === 'SEBAGIAN' ? 'SEBAGIAN' : 'BELUM BAYAR'"></span>
                                        </div>
                                        <p class="text-xs text-slate-600 mt-0.5">
                                            <span x-show="cicilan.nama_barang" x-text="cicilan.nama_barang + ' · '"></span>
                                            Jatuh tempo: <span x-text="cicilan.jatuh_tempo"></span>
                                            &middot; Sisa: <strong x-text="formatRp(cicilan.sisa_tagihan)"></strong>
                                        </p>
                                    </div>
                                </label>

                                <div class="flex items-center gap-2 sm:w-48">
                                    <span class="text-xs text-slate-500">Rp</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="1"
                                        :max="cicilan.sisa_tagihan"
                                        x-model.number="cicilan.nominal_bayar"
                                        :disabled="!cicilan.checked"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-right font-semibold text-slate-900 disabled:bg-slate-100 disabled:text-slate-400 focus:border-emerald-500 focus:outline-none"
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

            {{-- Sticky Bottom Bar: Total Uang Diterima --}}
            <div class="sticky bottom-4 z-20 rounded-2xl bg-slate-900 text-white p-5 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-400">
                        Total Uang Diterima (<span class="font-semibold text-white" x-text="itemTerpilih.length"></span> item tercentang)
                    </p>
                    <p class="text-2xl font-bold text-emerald-400 mt-0.5" x-text="formatRp(totalDiterima)"></p>
                </div>

                <button
                    type="submit"
                    :disabled="itemTerpilih.length === 0 || totalDiterima <= 0"
                    :class="itemTerpilih.length === 0 || totalDiterima <= 0 ? 'bg-slate-700 text-slate-400 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 cursor-pointer'"
                    class="rounded-xl px-6 py-3 text-sm font-bold transition shrink-0"
                >
                    Simpan Pembayaran &amp; Terbitkan Struk
                </button>
            </div>
        </form>
    </div>

    <script>
        function bulkPaymentForm(config) {
            const namaBulan = [
                '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            return {
                anggotaId: config.initialAnggotaId || '',
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
            };
        }
    </script>
@endsection
