<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Anggota') — Koperasi Jasa Berkah Saciba</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    @php
        $user = auth()->user();
        $navItems = [
            ['label' => 'Beranda', 'route' => 'anggota.dashboard', 'active' => 'anggota.dashboard'],
            ['label' => 'Simpanan', 'route' => 'anggota.simpanan', 'active' => 'anggota.simpanan'],
            ['label' => 'Pinjaman', 'route' => 'anggota.pinjaman', 'active' => 'anggota.pinjaman'],
            ['label' => 'Riwayat', 'route' => 'anggota.riwayat', 'active' => 'anggota.riwayat'],
        ];
    @endphp

    {{-- Top Header & Desktop Navigation --}}
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200/80 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3">
                <a href="{{ route('anggota.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <div class="h-9 w-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 truncate">Koperasi Jasa Berkah Saciba</p>
                        <p class="text-xs text-slate-500 truncate">{{ $user->nama_lengkap }} &middot; {{ $user->nomor_anggota }}</p>
                    </div>
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden md:flex items-center gap-1" aria-label="Navigasi Portal Anggota">
                    @foreach ($navItems as $item)
                        @php $isActive = request()->routeIs($item['active']); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="px-3.5 py-2 rounded-xl text-sm font-medium transition {{ $isActive ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                {{-- Right Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if ($user->isAdmin())
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-amber-50 border border-amber-200 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                            </svg>
                            <span class="hidden sm:inline">Kembali ke Panel Admin</span>
                            <span class="sm:hidden">Panel Admin</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24 md:pb-10">
        @yield('content')
    </main>

    {{-- Mobile Fixed Bottom Navigation Bar --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-slate-200 shadow-lg" aria-label="Navigasi Bawah Anggota">
        <div class="grid grid-cols-4 h-16">
            <a
                href="{{ route('anggota.dashboard') }}"
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium {{ request()->routeIs('anggota.dashboard') ? 'text-emerald-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Beranda</span>
            </a>

            <a
                href="{{ route('anggota.simpanan') }}"
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium {{ request()->routeIs('anggota.simpanan') ? 'text-emerald-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                </svg>
                <span>Simpanan</span>
            </a>

            <a
                href="{{ route('anggota.pinjaman') }}"
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium {{ request()->routeIs('anggota.pinjaman') ? 'text-emerald-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
                <span>Pinjaman</span>
            </a>

            <a
                href="{{ route('anggota.riwayat') }}"
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-medium {{ request()->routeIs('anggota.riwayat') ? 'text-emerald-600 font-semibold' : 'text-slate-500' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Riwayat</span>
            </a>
        </div>
    </nav>
</body>
</html>
