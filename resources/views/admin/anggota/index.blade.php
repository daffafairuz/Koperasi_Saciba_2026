@extends('layouts.admin')

@section('title', 'Data Anggota')
@section('header', 'Manajemen Data Anggota')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-zinc-950">Daftar Anggota Koperasi</h2>
                <p class="text-sm text-zinc-500 mt-0.5">
                    Kelola profil anggota, pendaftaran baru, reset kata sandi, dan proses soft-exit.
                </p>
            </div>
            <a
                href="{{ route('admin.anggota.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition self-start sm:self-auto"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <line x1="19" x2="19" y1="8" y2="14" />
                    <line x1="22" x2="16" y1="11" y2="11" />
                </svg>
                <span>Tambah Anggota</span>
            </a>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="rounded-2xl bg-white border border-zinc-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.anggota.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="flex-1 relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                    </div>
                    <input
                        type="search"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Cari nama lengkap, nomor anggota, atau nomor HP..."
                        class="w-full rounded-xl border border-zinc-200 pl-10 pr-4 py-2 text-sm text-zinc-950 placeholder:text-zinc-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <select
                        name="status"
                        class="rounded-xl border border-zinc-200 bg-white px-3.5 py-2 text-sm text-zinc-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 transition"
                    >
                        <option value="SEMUA" @selected($statusFilter === 'SEMUA')>Semua Status ({{ $totalAnggota }})</option>
                        <option value="{{ \App\Models\User::STATUS_AKTIF }}" @selected($statusFilter === \App\Models\User::STATUS_AKTIF)>Aktif ({{ $jumlahAktif }})</option>
                        <option value="{{ \App\Models\User::STATUS_KELUAR }}" @selected($statusFilter === \App\Models\User::STATUS_KELUAR)>Keluar ({{ $jumlahKeluar }})</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-zinc-900 hover:bg-zinc-800 px-4 py-2 text-sm font-semibold text-white transition cursor-pointer"
                    >
                        Cari
                    </button>

                    @if ($keyword !== '' || $statusFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.anggota.index') }}"
                            class="rounded-xl border border-zinc-200 px-3.5 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Anggota --}}
        <div class="rounded-2xl bg-white border border-zinc-200/80 shadow-xs overflow-hidden">
            @if ($daftarAnggota->isEmpty())
                <div class="p-10 text-center text-sm text-zinc-500">
                    Tidak ada data anggota yang sesuai dengan filter pencarian.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200/80 text-sm">
                        <thead class="bg-zinc-50/80 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Anggota</th>
                                <th class="px-5 py-3 text-left">Nomor HP</th>
                                <th class="px-5 py-3 text-center">Peran</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Saldo Simpanan Akhir</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 bg-white">
                            @foreach ($daftarAnggota as $anggota)
                                <tr class="hover:bg-zinc-50/70 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-zinc-950">{{ $anggota->nama_lengkap }}</p>
                                        <p class="text-xs text-zinc-500">
                                            <span class="font-mono tabular-nums">{{ $anggota->nomor_anggota }}</span>
                                            &middot; Gabung <span class="tabular-nums">{{ $anggota->tanggal_gabung->translatedFormat('d M Y') }}</span>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono tabular-nums text-zinc-700 whitespace-nowrap">
                                        {{ $anggota->no_hp }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md border px-2 py-0.5 text-[11px] font-semibold {{ $anggota->isAdmin() ? 'bg-zinc-900 text-emerald-400 border-zinc-800' : 'bg-zinc-100 text-zinc-700 border-zinc-200/80' }}">
                                            {{ $anggota->peran }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        @if ($anggota->isAktif())
                                            <span class="inline-flex items-center rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-rose-500/10 border border-rose-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700">
                                                KELUAR
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-mono tabular-nums font-semibold text-zinc-950 whitespace-nowrap">
                                        {{ format_rupiah($anggota->simpanan?->total_simpanan_akhir ?? 0) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2">
                                            <a
                                                href="{{ route('admin.anggota.show', $anggota) }}"
                                                class="rounded-lg border border-zinc-200/90 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-900 hover:text-white hover:border-zinc-900 transition"
                                            >
                                                Detail
                                            </a>
                                            <a
                                                href="{{ route('admin.anggota.edit', $anggota) }}"
                                                class="rounded-lg border border-zinc-200/90 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-700 hover:bg-zinc-50 transition"
                                            >
                                                Edit
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($daftarAnggota->hasPages())
                    <div class="px-5 py-4 border-t border-zinc-100">
                        {{ $daftarAnggota->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
