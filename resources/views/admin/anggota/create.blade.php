@extends('layouts.admin')

@section('title', 'Tambah Anggota Baru')
@section('header', 'Pendaftaran Anggota Baru')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Form Anggota Baru</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Dompet simpanan anggota dibuat otomatis dengan tarif Simpanan Pokok
                    <span class="font-mono tabular-nums font-semibold text-zinc-800">{{ format_rupiah($nominalSimpananPokokDefault) }}</span> (status BELUM BAYAR).
                </p>
            </div>
            <a
                href="{{ route('admin.anggota.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-3.5 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition shrink-0"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <div class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs">
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700">
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
                        <label for="nomor_anggota" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nomor Anggota</label>
                        <input
                            id="nomor_anggota"
                            name="nomor_anggota"
                            type="text"
                            required
                            value="{{ old('nomor_anggota') }}"
                            placeholder="Contoh: K.10.201"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="tanggal_gabung" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Tanggal Gabung</label>
                        <input
                            id="tanggal_gabung"
                            name="tanggal_gabung"
                            type="date"
                            required
                            value="{{ old('tanggal_gabung', now()->toDateString()) }}"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>
                </div>

                <div>
                    <label for="nama_lengkap" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nama Lengkap</label>
                    <input
                        id="nama_lengkap"
                        name="nama_lengkap"
                        type="text"
                        required
                        value="{{ old('nama_lengkap') }}"
                        placeholder="Masukkan nama lengkap sesuai KTP"
                        class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nomor HP (Login)</label>
                        <input
                            id="no_hp"
                            name="no_hp"
                            type="tel"
                            required
                            value="{{ old('no_hp') }}"
                            placeholder="081234567890"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                        <p class="mt-1 text-xs text-zinc-500">Format 628... atau 8... otomatis dinormalisasi ke 08...</p>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Kata Sandi Awal</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                    <div>
                        <label for="peran" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Peran Akun</label>
                        <select
                            id="peran"
                            name="peran"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
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
                                class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"
                            >
                            <span class="text-sm font-medium text-zinc-700">Wajib Simpanan Bulanan (SW)</span>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.anggota.index') }}"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-600 hover:bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition cursor-pointer"
                    >
                        Simpan Anggota Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
