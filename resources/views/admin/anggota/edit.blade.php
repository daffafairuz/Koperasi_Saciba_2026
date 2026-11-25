@extends('layouts.admin')

@section('title', 'Edit Profil Anggota')
@section('header', 'Edit Profil Anggota')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Edit Profil — {{ $anggota->nama_lengkap }}</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Perubahan status keanggotaan (Soft-Exit / Re-aktivasi) dan reset kata sandi dilakukan melalui halaman Detail Anggota.
                </p>
            </div>
            <a
                href="{{ route('admin.anggota.show', $anggota) }}"
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

            <form method="POST" action="{{ route('admin.anggota.update', $anggota) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nomor_anggota" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nomor Anggota</label>
                        <input
                            id="nomor_anggota"
                            name="nomor_anggota"
                            type="text"
                            required
                            value="{{ old('nomor_anggota', $anggota->nomor_anggota) }}"
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
                            value="{{ old('tanggal_gabung', $anggota->tanggal_gabung->toDateString()) }}"
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
                        value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}"
                        class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Nomor HP</label>
                        <input
                            id="no_hp"
                            name="no_hp"
                            type="tel"
                            required
                            value="{{ old('no_hp', $anggota->no_hp) }}"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 font-mono tabular-nums text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="peran" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Peran Akun</label>
                        <select
                            id="peran"
                            name="peran"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                            <option
                                value="{{ \App\Models\User::PERAN_ANGGOTA }}"
                                @selected(old('peran', $anggota->peran) === \App\Models\User::PERAN_ANGGOTA)
                                @disabled($isOnlyActiveAdmin)
                            >
                                ANGGOTA{{ $isOnlyActiveAdmin ? ' (Terkunci — Admin Aktif Terakhir)' : '' }}
                            </option>
                            <option value="{{ \App\Models\User::PERAN_ADMIN }}" @selected(old('peran', $anggota->peran) === \App\Models\User::PERAN_ADMIN)>ADMIN (Pengurus)</option>
                        </select>
                        @if ($isOnlyActiveAdmin)
                            <div class="mt-2 flex items-start gap-2 rounded-xl bg-amber-500/10 border border-amber-500/20 px-3 py-2 text-xs text-amber-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                    <path d="M12 8v4" />
                                    <path d="M12 16h.01" />
                                </svg>
                                <span>Peran tidak dapat diubah menjadi Anggota karena ini adalah satu-satunya akun Admin yang aktif. Tambahkan Admin aktif lain terlebih dahulu sebelum menurunkan peran akun ini.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <input type="hidden" name="wajib_simpanan" value="0">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input
                            type="checkbox"
                            name="wajib_simpanan"
                            value="1"
                            @checked(old('wajib_simpanan', $anggota->wajib_simpanan ? '1' : '0'))
                            class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"
                        >
                        <span class="text-sm font-medium text-zinc-700">Wajib Simpanan Bulanan (SW)</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-zinc-100 flex items-center justify-end gap-3">
                    <a
                        href="{{ route('admin.anggota.show', $anggota) }}"
                        class="rounded-xl border border-zinc-200 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-900 hover:bg-zinc-800 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
