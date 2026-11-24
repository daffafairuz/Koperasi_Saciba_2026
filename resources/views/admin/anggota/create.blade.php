@extends('layouts.admin')

@section('title', 'Tambah Anggota Baru')
@section('header', 'Pendaftaran Anggota Baru')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Form Anggota Baru</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Dompet simpanan anggota akan dibuat otomatis dengan tarif Simpanan Pokok {{ format_rupiah($nominalSimpananPokokDefault) }} (status BELUM BAYAR).
                </p>
            </div>
            <a
                href="{{ route('admin.anggota.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition shrink-0"
            >
                &larr; Kembali
            </a>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.anggota.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nomor_anggota" class="block text-sm font-medium text-slate-700">Nomor Anggota</label>
                        <input
                            id="nomor_anggota"
                            name="nomor_anggota"
                            type="text"
                            required
                            value="{{ old('nomor_anggota') }}"
                            placeholder="Contoh: K.10.201"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label for="tanggal_gabung" class="block text-sm font-medium text-slate-700">Tanggal Gabung</label>
                        <input
                            id="tanggal_gabung"
                            name="tanggal_gabung"
                            type="date"
                            required
                            value="{{ old('tanggal_gabung', now()->toDateString()) }}"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-slate-700">Nama Lengkap</label>
                    <input
                        id="nama_lengkap"
                        name="nama_lengkap"
                        type="text"
                        required
                        value="{{ old('nama_lengkap') }}"
                        placeholder="Masukkan nama lengkap sesuai KTP"
                        class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-slate-700">Nomor HP (Kredensial Login)</label>
                        <input
                            id="no_hp"
                            name="no_hp"
                            type="tel"
                            required
                            value="{{ old('no_hp') }}"
                            placeholder="Contoh: 081234567890"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                        <p class="mt-1 text-xs text-slate-500">Format 628... atau 8... otomatis dinormalisasi ke 08...</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi Awal</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                    <div>
                        <label for="peran" class="block text-sm font-medium text-slate-700">Peran Akun</label>
                        <select
                            id="peran"
                            name="peran"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                        >
                            <option value="{{ \App\Models\User::PERAN_ANGGOTA }}" @selected(old('peran', \App\Models\User::PERAN_ANGGOTA) === \App\Models\User::PERAN_ANGGOTA)>ANGGOTA</option>
                            <option value="{{ \App\Models\User::PERAN_ADMIN }}" @selected(old('peran') === \App\Models\User::PERAN_ADMIN)>ADMIN (Pengurus)</option>
                        </select>
                    </div>

                    <div class="pt-2 sm:pt-6">
                        <input type="hidden" name="wajib_simpanan" value="0">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input
                                type="checkbox"
                                name="wajib_simpanan"
                                value="1"
                                @checked(old('wajib_simpanan', '1'))
                                class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >
                            <span class="text-sm font-medium text-slate-700">Wajib Simpanan Bulanan (SW)</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.anggota.index') }}"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition cursor-pointer"
                    >
                        Simpan Anggota Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
