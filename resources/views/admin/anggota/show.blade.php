@extends('layouts.admin')

@section('title', 'Detail Anggota — '.$anggota->nama_lengkap)
@section('header', 'Detail Profil Anggota')

@section('content')
    <div
        x-data="{
            modalStatusOpen: {{ $errors->ubahStatus->any() ? 'true' : 'false' }},
            modalPasswordOpen: {{ $errors->resetPassword->any() ? 'true' : 'false' }}
        }"
        @keydown.escape.window="modalStatusOpen = false; modalPasswordOpen = false"
        class="max-w-7xl mx-auto space-y-6"
    >
        {{-- Top Profile Header & Actions --}}
        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.anggota.index') }}" class="text-xs font-semibold text-slate-500 hover:text-emerald-700">
                            &larr; Daftar Anggota
                        </a>
                        <span class="text-slate-300">/</span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $anggota->isAktif() ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                            {{ $anggota->status }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $anggota->isAdmin() ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ $anggota->peran }}
                        </span>
                    </div>

                    <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $anggota->nama_lengkap }}</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ $anggota->nomor_anggota }} &middot; HP: <span class="font-medium">{{ $anggota->no_hp }}</span>
                        &middot; Gabung {{ $anggota->tanggal_gabung->translatedFormat('d M Y') }}
                        @if ($anggota->tanggal_keluar)
                            &middot; Keluar {{ $anggota->tanggal_keluar->translatedFormat('d M Y') }}
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <a
                        href="{{ route('admin.anggota.edit', $anggota) }}"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition"
                    >
                        Edit Profil
                    </a>

                    <button
                        type="button"
                        @click="modalPasswordOpen = true"
                        class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition cursor-pointer"
                    >
                        Reset Password
                    </button>

                    <button
                        type="button"
                        @click="modalStatusOpen = true"
                        class="rounded-xl px-4 py-2 text-xs font-semibold text-white transition cursor-pointer {{ $anggota->isAktif() ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                    >
                        {{ $anggota->isAktif() ? 'Ubah Status (Soft-Exit)' : 'Re-aktivasi Anggota' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Ringkasan Simpanan 1-on-1 --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-emerald-600 text-white p-5 shadow-xs">
                <p class="text-xs font-medium text-emerald-100">Total Simpanan Akhir</p>
                <p class="mt-2 text-2xl font-bold">{{ format_rupiah($anggota->simpanan?->total_simpanan_akhir ?? 0) }}</p>
                <p class="mt-1.5 text-xs text-emerald-100">
                    SW terakhir: {{ format_periode($anggota->simpanan?->periode_sw_terakhir) }}
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-500">Simpanan Pokok</p>
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $anggota->simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-rose-100 text-rose-800 border-rose-200' }}">
                        {{ $anggota->simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS ? 'LUNAS' : 'BELUM BAYAR' }}
                    </span>
                </div>
                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ format_rupiah($anggota->simpanan?->nominal_simpanan_pokok ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">
                    Wajib SW: {{ $anggota->wajib_simpanan ? 'Ya' : 'Tidak' }}
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold text-slate-500">Saldo Awal SW (Excel)</p>
                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ format_rupiah($anggota->simpanan?->saldo_sw_awal ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">Saldo pindahan tutup buku.</p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-xs">
                <p class="text-xs font-semibold text-slate-500">Total Simpanan Wajib</p>
                <p class="mt-2 text-xl font-bold text-slate-900">
                    {{ format_rupiah($anggota->simpanan?->total_simpanan_wajib ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-slate-500">Akumulasi saldo awal + setoran SW.</p>
            </div>
        </section>

        {{-- Daftar Pinjaman Anggota --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Daftar Pinjaman ({{ $anggota->pinjaman->count() }})</h3>
                <p class="text-xs text-slate-500">Pinjaman uang syariah (0%) dan pinjaman barang milik anggota.</p>
            </div>

            @if ($anggota->pinjaman->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">
                    Anggota ini belum memiliki catatan pinjaman.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Nomor &amp; Jenis</th>
                                <th class="px-5 py-3 text-left">Tanggal Cair</th>
                                <th class="px-5 py-3 text-right">Total Tagihan</th>
                                <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                                <th class="px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($anggota->pinjaman as $pinjaman)
                                <tr>
                                    <td class="px-5 py-3.5">
                                        <p class="font-semibold text-slate-900">
                                            {{ $pinjaman->nomor_pinjaman }}
                                            <span class="ml-1 inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? 'bg-teal-100 text-teal-800' : 'bg-indigo-100 text-indigo-800' }}">
                                                {{ $pinjaman->jenis_pinjaman }}
                                            </span>
                                        </p>
                                        @if ($pinjaman->nama_barang)
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $pinjaman->nama_barang }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        {{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }} ({{ $pinjaman->tenor_bulan }} bln)
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-slate-700 whitespace-nowrap">
                                        {{ format_rupiah($pinjaman->total_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold {{ $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN ? 'text-rose-600' : 'text-emerald-700' }} whitespace-nowrap">
                                        {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN ? 'bg-sky-100 text-sky-800' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $pinjaman->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Tabel Riwayat Perubahan Status (Audit Soft-Exit) --}}
        <section class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Riwayat Perubahan Status Keanggotaan</h3>
                <p class="text-xs text-slate-500">Rekam jejak audit proses Soft-Exit dan Re-aktivasi anggota.</p>
            </div>

            @if ($anggota->riwayatStatus->isEmpty())
                <div class="p-8 text-center text-sm text-slate-500">
                    Belum ada riwayat perubahan status untuk anggota ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Waktu Perubahan</th>
                                <th class="px-5 py-3 text-left">Perubahan Status</th>
                                <th class="px-5 py-3 text-left">Alasan</th>
                                <th class="px-5 py-3 text-left">Diubah / Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($anggota->riwayatStatus as $riwayat)
                                <tr>
                                    <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                        {{ ($riwayat->tanggal_disetujui ?? $riwayat->created_at)->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="font-semibold text-slate-700">{{ $riwayat->status_sebelumnya }}</span>
                                        <span class="mx-1 text-slate-400">&rarr;</span>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $riwayat->status_baru === \App\Models\RiwayatStatusAnggota::STATUS_AKTIF ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $riwayat->status_baru }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700">
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

        {{-- Modal 1: Ubah Status Keanggotaan (Soft-Exit / Re-aktivasi) --}}
        <div
            x-show="modalStatusOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60"
            @click.self="modalStatusOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-status-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="modal-status-title" class="text-lg font-bold text-slate-900">
                            Ubah Status Keanggotaan (Soft-Exit)
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Data anggota berstatus KELUAR tidak dihapus, tetapi akses login otomatis diblokir.
                        </p>
                    </div>
                    <button type="button" @click="modalStatusOpen = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none cursor-pointer">
                        &times;
                    </button>
                </div>

                @if ($errors->ubahStatus->any())
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->ubahStatus->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.anggota.status', $anggota) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status_baru" class="block text-sm font-medium text-slate-700">Status Baru</label>
                        @php
                            $defaultStatusBaru = $anggota->isAktif() ? \App\Models\User::STATUS_KELUAR : \App\Models\User::STATUS_AKTIF;
                        @endphp
                        <select
                            id="status_baru"
                            name="status_baru"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            <option value="{{ \App\Models\User::STATUS_KELUAR }}" @selected(old('status_baru', $defaultStatusBaru) === \App\Models\User::STATUS_KELUAR)>
                                KELUAR (Nonaktifkan / Soft-Exit)
                            </option>
                            <option value="{{ \App\Models\User::STATUS_AKTIF }}" @selected(old('status_baru', $defaultStatusBaru) === \App\Models\User::STATUS_AKTIF)>
                                AKTIF (Aktifkan Kembali)
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="alasan" class="block text-sm font-medium text-slate-700">Alasan Perubahan Status</label>
                        <textarea
                            id="alasan"
                            name="alasan"
                            rows="3"
                            required
                            placeholder="Tuliskan alasan pengunduran diri atau re-aktivasi untuk catatan audit..."
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >{{ old('alasan') }}</textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="modalStatusOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 cursor-pointer"
                        >
                            Simpan Perubahan Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal 2: Reset Password Anggota --}}
        <div
            x-show="modalPasswordOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60"
            @click.self="modalPasswordOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-password-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="modal-password-title" class="text-lg font-bold text-slate-900">
                            Reset Password Anggota
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Atur kata sandi baru untuk {{ $anggota->nama_lengkap }} ({{ $anggota->no_hp }}).
                        </p>
                    </div>
                    <button type="button" @click="modalPasswordOpen = false" class="text-slate-400 hover:text-slate-600 text-xl leading-none cursor-pointer">
                        &times;
                    </button>
                </div>

                @if ($errors->resetPassword->any())
                    <div class="rounded-xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700">
                        <ul class="list-disc pl-4 space-y-1">
                            @foreach ($errors->resetPassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.anggota.reset-password', $anggota) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="password_baru" class="block text-sm font-medium text-slate-700">Kata Sandi Baru</label>
                        <input
                            id="password_baru"
                            name="password_baru"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label for="password_baru_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi Baru</label>
                        <input
                            id="password_baru_confirmation"
                            name="password_baru_confirmation"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Ulangi kata sandi baru"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="modalPasswordOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-700 cursor-pointer"
                        >
                            Simpan Kata Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
