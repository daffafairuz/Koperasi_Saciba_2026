@extends('layouts.admin')

@section('title', 'Detail Anggota — '.$anggota->nama_lengkap)
@section('header', 'Detail Profil Anggota')

@section('content')
    @php
        $defaultStatusBaru = $anggota->isAktif() ? \App\Models\User::STATUS_KELUAR : \App\Models\User::STATUS_AKTIF;
    @endphp
    <div
        x-data="{
            modalStatusOpen: {{ $errors->ubahStatus->any() ? 'true' : 'false' }},
            modalPasswordOpen: {{ $errors->resetPassword->any() ? 'true' : 'false' }},
            statusBaru: @js(old('status_baru', $defaultStatusBaru)),
            isOnlyActiveAdmin: {{ $isOnlyActiveAdmin ? 'true' : 'false' }}
        }"
        @keydown.escape.window="modalStatusOpen = false; modalPasswordOpen = false"
        class="max-w-7xl mx-auto space-y-6"
    >
        {{-- Top Profile Header & Actions --}}
        <div class="rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.anggota.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-zinc-500 hover:text-zinc-900 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 19-7-7 7-7" />
                                <path d="M19 12H5" />
                            </svg>
                            <span>Daftar Anggota</span>
                        </a>
                        <span class="text-zinc-300">/</span>
                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $anggota->isAktif() ? 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' : 'bg-rose-500/10 text-rose-700 border-rose-500/20' }}">
                            {{ $anggota->status }}
                        </span>
                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $anggota->isAdmin() ? 'bg-zinc-900 text-emerald-400 border-zinc-800' : 'bg-zinc-100 text-zinc-700 border-zinc-200/80' }}">
                            {{ $anggota->peran }}
                        </span>
                    </div>

                    <h2 class="mt-2.5 text-2xl font-bold tracking-tight text-zinc-950">{{ $anggota->nama_lengkap }}</h2>
                    <p class="mt-1 text-sm text-zinc-600">
                        <span class="font-mono tabular-nums font-medium text-zinc-800">{{ $anggota->nomor_anggota }}</span>
                        &middot; HP: <span class="font-mono tabular-nums font-medium text-zinc-800">{{ $anggota->no_hp }}</span>
                        &middot; Gabung <span class="tabular-nums">{{ $anggota->tanggal_gabung->translatedFormat('d M Y') }}</span>
                        @if ($anggota->tanggal_keluar)
                            &middot; Keluar <span class="tabular-nums text-rose-600 font-medium">{{ $anggota->tanggal_keluar->translatedFormat('d M Y') }}</span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-2.5 flex-wrap">
                    <a
                        href="{{ route('admin.anggota.edit', $anggota) }}"
                        class="rounded-xl border border-zinc-200/90 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition"
                    >
                        Edit Profil
                    </a>

                    <button
                        type="button"
                        @click="modalPasswordOpen = true"
                        class="rounded-xl border border-amber-500/25 bg-amber-500/10 px-4 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-500/15 transition cursor-pointer"
                    >
                        Reset Password
                    </button>

                    <button
                        type="button"
                        @click="modalStatusOpen = true"
                        class="rounded-xl px-4 py-2 text-xs font-semibold text-white shadow-xs transition cursor-pointer {{ $anggota->isAktif() ? 'bg-rose-600 hover:bg-rose-500' : 'bg-emerald-600 hover:bg-emerald-500' }}"
                    >
                        {{ $anggota->isAktif() ? 'Ubah Status (Soft-Exit)' : 'Re-aktivasi Anggota' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Ringkasan Simpanan 1-on-1 (Bento Grid) --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-emerald-950 text-white border border-emerald-500/20 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Total Simpanan Akhir</p>
                <p class="mt-2 font-mono tabular-nums text-2xl font-bold text-emerald-400">{{ format_rupiah($anggota->simpanan?->total_simpanan_akhir ?? 0) }}</p>
                <p class="mt-1.5 text-xs text-zinc-400">
                    SW terakhir: <span class="text-zinc-200">{{ format_periode($anggota->simpanan?->periode_sw_terakhir) }}</span>
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Simpanan Pokok</p>
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $anggota->simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS ? 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' : 'bg-rose-500/10 text-rose-700 border-rose-500/20' }}">
                        {{ $anggota->simpanan?->status_simpanan_pokok === \App\Models\SimpananAnggota::STATUS_POKOK_LUNAS ? 'LUNAS' : 'BELUM BAYAR' }}
                    </span>
                </div>
                <p class="mt-2 font-mono tabular-nums text-xl font-bold text-zinc-950">
                    {{ format_rupiah($anggota->simpanan?->nominal_simpanan_pokok ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500">
                    Wajib SW: <span class="font-medium text-zinc-700">{{ $anggota->wajib_simpanan ? 'Ya' : 'Tidak' }}</span>
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Saldo Awal SW (Excel)</p>
                <p class="mt-2 font-mono tabular-nums text-xl font-bold text-zinc-950">
                    {{ format_rupiah($anggota->simpanan?->saldo_sw_awal ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500">Saldo pindahan tutup buku.</p>
            </div>

            <div class="rounded-2xl bg-white border border-zinc-200/80 p-5 shadow-xs">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total Simpanan Wajib</p>
                <p class="mt-2 font-mono tabular-nums text-xl font-bold text-zinc-950">
                    {{ format_rupiah($anggota->simpanan?->total_simpanan_wajib ?? 0) }}
                </p>
                <p class="mt-1.5 text-xs text-zinc-500">Akumulasi saldo awal + setoran SW.</p>
            </div>
        </section>

        {{-- Daftar Pinjaman Anggota --}}
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h3 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Daftar Pinjaman ({{ $anggota->pinjaman->count() }})</h3>
                <p class="text-xs text-zinc-500">Pinjaman uang syariah (0%) dan pinjaman barang milik anggota.</p>
            </div>

            @if ($anggota->pinjaman->isEmpty())
                <div class="p-8 text-center text-sm text-zinc-500">
                    Anggota ini belum memiliki catatan pinjaman.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Nomor &amp; Jenis</th>
                                <th class="px-5 py-3 text-left">Tanggal Cair</th>
                                <th class="px-5 py-3 text-right">Total Tagihan</th>
                                <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                                <th class="px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($anggota->pinjaman as $pinjaman)
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5">
                                        <p class="font-mono tabular-nums font-semibold text-zinc-950">
                                            {{ $pinjaman->nomor_pinjaman }}
                                            <span class="ml-1 inline-flex items-center rounded-full border px-2 py-0.5 font-sans text-[10px] font-semibold {{ $pinjaman->jenis_pinjaman === \App\Models\Pinjaman::JENIS_BARANG ? 'bg-zinc-900/5 text-zinc-900 border-zinc-900/15' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
                                                {{ $pinjaman->jenis_pinjaman }}
                                            </span>
                                        </p>
                                        @if ($pinjaman->nama_barang)
                                            <p class="text-xs text-zinc-500 mt-0.5">{{ $pinjaman->nama_barang }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-600 tabular-nums whitespace-nowrap">
                                        {{ $pinjaman->tanggal_cair->translatedFormat('d M Y') }} ({{ $pinjaman->tenor_bulan }} bln)
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums text-zinc-700 whitespace-nowrap">
                                        {{ format_rupiah($pinjaman->total_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-bold {{ $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN ? 'text-rose-600' : 'text-emerald-600' }} whitespace-nowrap">
                                        {{ format_rupiah($pinjaman->sisa_tagihan) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $pinjaman->status === \App\Models\Pinjaman::STATUS_BERJALAN ? 'bg-amber-500/10 text-amber-700 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' }}">
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
        <section class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-zinc-100">
                <h3 class="text-sm sm:text-base font-bold tracking-tight text-zinc-950">Riwayat Perubahan Status Keanggotaan</h3>
                <p class="text-xs text-zinc-500">Rekam jejak audit proses Soft-Exit dan Re-aktivasi anggota.</p>
            </div>

            @if ($anggota->riwayatStatus->isEmpty())
                <div class="p-8 text-center text-sm text-zinc-500">
                    Belum ada riwayat perubahan status untuk anggota ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Waktu Perubahan</th>
                                <th class="px-5 py-3 text-left">Perubahan Status</th>
                                <th class="px-5 py-3 text-left">Alasan</th>
                                <th class="px-5 py-3 text-left">Diubah / Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($anggota->riwayatStatus as $riwayat)
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5 text-zinc-600 tabular-nums whitespace-nowrap">
                                        {{ ($riwayat->tanggal_disetujui ?? $riwayat->created_at)->translatedFormat('d M Y, H:i') }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="font-semibold text-zinc-700">{{ $riwayat->status_sebelumnya }}</span>
                                        <span class="mx-1 text-zinc-400">&rarr;</span>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $riwayat->status_baru === \App\Models\RiwayatStatusAnggota::STATUS_AKTIF ? 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20' : 'bg-rose-500/10 text-rose-700 border-rose-500/20' }}">
                                            {{ $riwayat->status_baru }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-700">
                                        {{ $riwayat->alasan ?: '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-zinc-700 whitespace-nowrap">
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
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/50 backdrop-blur-sm"
            @click.self="modalStatusOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-status-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-2xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="modal-status-title" class="text-lg font-bold tracking-tight text-zinc-950">
                            Ubah Status Keanggotaan (Soft-Exit)
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500">
                            Data anggota berstatus KELUAR tidak dihapus, tetapi akses login otomatis diblokir.
                        </p>
                    </div>
                    <button type="button" @click="modalStatusOpen = false" aria-label="Tutup modal" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                @if ($errors->ubahStatus->any())
                    <div class="rounded-xl bg-rose-500/10 border border-rose-500/20 p-3 text-xs text-rose-700">
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
                        <label for="status_baru" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Status Baru</label>
                        <select
                            id="status_baru"
                            name="status_baru"
                            x-model="statusBaru"
                            required
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 bg-white px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                            <option value="{{ \App\Models\User::STATUS_KELUAR }}">
                                KELUAR (Nonaktifkan / Soft-Exit){{ $isOnlyActiveAdmin ? ' — Terkunci' : '' }}
                            </option>
                            <option value="{{ \App\Models\User::STATUS_AKTIF }}">
                                AKTIF (Aktifkan Kembali)
                            </option>
                        </select>

                        @if ($isOnlyActiveAdmin)
                            <div
                                x-show="statusBaru === '{{ \App\Models\User::STATUS_KELUAR }}'"
                                x-cloak
                                class="mt-2.5 flex items-start gap-2 rounded-xl bg-amber-500/10 border border-amber-500/20 px-3 py-2.5 text-xs text-amber-800"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 mt-0.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                    <path d="M12 8v4" />
                                    <path d="M12 16h.01" />
                                </svg>
                                <span>Status keanggotaan tidak dapat dinonaktifkan (KELUAR) karena akun ini adalah satu-satunya Admin yang aktif. Tambahkan akun Admin aktif lain terlebih dahulu untuk mencegah system lockout.</span>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label for="alasan" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Alasan Perubahan Status</label>
                        <textarea
                            id="alasan"
                            name="alasan"
                            rows="3"
                            required
                            placeholder="Tuliskan alasan pengunduran diri atau re-aktivasi untuk catatan audit..."
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >{{ old('alasan') }}</textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="modalStatusOpen = false"
                            class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="isOnlyActiveAdmin && statusBaru === '{{ \App\Models\User::STATUS_KELUAR }}'"
                            :class="isOnlyActiveAdmin && statusBaru === '{{ \App\Models\User::STATUS_KELUAR }}' ? 'bg-zinc-300 text-zinc-500 cursor-not-allowed' : 'bg-rose-600 hover:bg-rose-500 text-white cursor-pointer'"
                            class="rounded-xl px-4 py-2 text-xs font-semibold transition"
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
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/50 backdrop-blur-sm"
            @click.self="modalPasswordOpen = false"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-password-title"
        >
            <div class="w-full max-w-md rounded-2xl bg-white border border-zinc-200/80 p-6 shadow-2xl space-y-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="modal-password-title" class="text-lg font-bold tracking-tight text-zinc-950">
                            Reset Password Anggota
                        </h3>
                        <p class="mt-1 text-xs text-zinc-500">
                            Atur kata sandi baru untuk {{ $anggota->nama_lengkap }} (<span class="font-mono tabular-nums">{{ $anggota->no_hp }}</span>).
                        </p>
                    </div>
                    <button type="button" @click="modalPasswordOpen = false" aria-label="Tutup modal" class="rounded-lg p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18" />
                            <path d="m6 6 12 12" />
                        </svg>
                    </button>
                </div>

                @if ($errors->resetPassword->any())
                    <div class="rounded-xl bg-rose-500/10 border border-rose-500/20 p-3 text-xs text-rose-700">
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
                        <label for="password_baru" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Kata Sandi Baru</label>
                        <input
                            id="password_baru"
                            name="password_baru"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div>
                        <label for="password_baru_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">Konfirmasi Kata Sandi Baru</label>
                        <input
                            id="password_baru_confirmation"
                            name="password_baru_confirmation"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Ulangi kata sandi baru"
                            class="mt-1.5 block w-full rounded-xl border border-zinc-200 px-3.5 py-2.5 text-sm text-zinc-950 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                        >
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            @click="modalPasswordOpen = false"
                            class="rounded-xl border border-zinc-200 px-4 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-xs font-semibold text-white cursor-pointer"
                        >
                            Simpan Kata Sandi Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
