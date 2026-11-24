@extends('layouts.admin')

@section('title', 'Data Anggota')
@section('header', 'Manajemen Data Anggota')

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Header & Action --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Daftar Anggota Koperasi</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Kelola profil anggota, pendaftaran baru, reset kata sandi, dan proses soft-exit.
                </p>
            </div>
            <a
                href="{{ route('admin.anggota.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-emerald-700 transition self-start sm:self-auto"
            >
                <span>+ Tambah Anggota</span>
            </a>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-xs space-y-4">
            <form method="GET" action="{{ route('admin.anggota.index') }}" class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="flex-1">
                    <input
                        type="search"
                        name="q"
                        value="{{ $keyword }}"
                        placeholder="Cari nama lengkap, nomor anggota, atau nomor HP..."
                        class="w-full rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <select
                        name="status"
                        class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                    >
                        <option value="SEMUA" @selected($statusFilter === 'SEMUA')>Semua Status ({{ $totalAnggota }})</option>
                        <option value="{{ \App\Models\User::STATUS_AKTIF }}" @selected($statusFilter === \App\Models\User::STATUS_AKTIF)>Aktif ({{ $jumlahAktif }})</option>
                        <option value="{{ \App\Models\User::STATUS_KELUAR }}" @selected($statusFilter === \App\Models\User::STATUS_KELUAR)>Keluar ({{ $jumlahKeluar }})</option>
                    </select>

                    <button
                        type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition cursor-pointer"
                    >
                        Cari
                    </button>

                    @if ($keyword !== '' || $statusFilter !== 'SEMUA')
                        <a
                            href="{{ route('admin.anggota.index') }}"
                            class="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Anggota --}}
        <div class="rounded-2xl bg-white border border-slate-200 shadow-xs overflow-hidden">
            @if ($daftarAnggota->isEmpty())
                <div class="p-10 text-center text-sm text-slate-500">
                    Tidak ada data anggota yang sesuai dengan filter pencarian.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3 text-left">Anggota</th>
                                <th class="px-5 py-3 text-left">Nomor HP</th>
                                <th class="px-5 py-3 text-center">Peran</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Saldo Simpanan Akhir</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($daftarAnggota as $anggota)
                                <tr class="hover:bg-slate-50/70">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <p class="font-semibold text-slate-900">{{ $anggota->nama_lengkap }}</p>
                                        <p class="text-xs text-slate-500">
                                            {{ $anggota->nomor_anggota }} &middot; Gabung {{ $anggota->tanggal_gabung->translatedFormat('d M Y') }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-700 whitespace-nowrap">
                                        {{ $anggota->no_hp }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-semibold {{ $anggota->isAdmin() ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $anggota->peran }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        @if ($anggota->isAktif())
                                            <span class="inline-flex items-center rounded-full bg-emerald-100 border border-emerald-200 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-rose-100 border border-rose-200 px-2.5 py-0.5 text-[11px] font-semibold text-rose-800">
                                                KELUAR
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-slate-900 whitespace-nowrap">
                                        {{ format_rupiah($anggota->simpanan?->total_simpanan_akhir ?? 0) }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-2">
                                            <a
                                                href="{{ route('admin.anggota.show', $anggota) }}"
                                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-emerald-300 hover:text-emerald-700 transition"
                                            >
                                                Detail
                                            </a>
                                            <a
                                                href="{{ route('admin.anggota.edit', $anggota) }}"
                                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition"
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
                    <div class="px-5 py-4 border-t border-slate-100">
                        {{ $daftarAnggota->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
