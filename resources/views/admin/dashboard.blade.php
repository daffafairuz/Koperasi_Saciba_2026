@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('header', 'Dashboard Pengurus Koperasi')

@section('content')
    <div class="max-w-4xl space-y-6">
        <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                        Panel Admin Aktif
                    </span>
                    <h2 class="mt-2 text-xl font-bold text-slate-900">
                        Selamat datang, {{ auth()->user()->nama_lengkap }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Kerangka Panel Admin Koperasi Jasa Berkah Saciba sudah siap. Modul operasional (CRUD anggota, pembayaran gabungan, pencairan pinjaman, dan laporan) akan dibangun pada Tahap 5.
                    </p>
                </div>

                @if (auth()->user()->wajib_simpanan)
                    <a
                        href="{{ route('anggota.dashboard') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition shrink-0"
                    >
                        Lihat Mode Anggota Saya
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white border border-slate-200 p-5">
                <p class="text-xs font-medium text-slate-500">Nomor Anggota</p>
                <p class="mt-1 text-lg font-bold text-slate-900">{{ auth()->user()->nomor_anggota }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-5">
                <p class="text-xs font-medium text-slate-500">Nomor HP</p>
                <p class="mt-1 text-lg font-bold text-slate-900">{{ auth()->user()->no_hp }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-5">
                <p class="text-xs font-medium text-slate-500">Hak Akses</p>
                <p class="mt-1 text-lg font-bold text-emerald-700">{{ auth()->user()->peran }}</p>
            </div>
        </div>
    </div>
@endsection
