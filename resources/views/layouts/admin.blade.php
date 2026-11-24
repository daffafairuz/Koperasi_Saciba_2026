<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') — Koperasi Jasa Berkah Saciba</title>

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
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    @php
        $admin = auth()->user();
        $sidebarMenus = [
            ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'), 'ready' => true],
            ['label' => 'Data Anggota', 'href' => route('admin.anggota.index'), 'active' => request()->routeIs('admin.anggota.*'), 'ready' => true],
            ['label' => 'Pengaturan Koperasi', 'href' => route('admin.pengaturan.index'), 'active' => request()->routeIs('admin.pengaturan.*'), 'ready' => true],
            ['label' => 'Pinjaman', 'href' => '#', 'active' => false, 'ready' => false],
            ['label' => 'Input Pembayaran', 'href' => '#', 'active' => false, 'ready' => false],
            ['label' => 'Kas Usaha', 'href' => '#', 'active' => false, 'ready' => false],
        ];
    @endphp

    <div class="min-h-screen flex">
        {{-- Mobile Sidebar Overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
        ></div>

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-100 flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto shrink-0"
        >
            <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <div class="h-9 w-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-white truncate">Berkah Saciba</p>
                        <p class="text-[11px] text-emerald-400">Panel Pengurus</p>
                    </div>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white text-xl leading-none">
                    &times;
                </button>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto" aria-label="Navigasi Admin">
                @foreach ($sidebarMenus as $menu)
                    @if ($menu['ready'])
                        <a
                            href="{{ $menu['href'] }}"
                            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ $menu['active'] ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                        >
                            <span>{{ $menu['label'] }}</span>
                        </a>
                    @else
                        <a
                            href="{{ $menu['href'] }}"
                            onclick="return false;"
                            aria-disabled="true"
                            class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm text-slate-400 hover:bg-slate-800/50 transition cursor-not-allowed"
                        >
                            <span>{{ $menu['label'] }}</span>
                            <span class="rounded-md bg-slate-800 px-1.5 py-0.5 text-[10px] font-medium text-slate-400">Segera</span>
                        </a>
                    @endif
                @endforeach
            </nav>

            <div class="p-4 border-t border-slate-800 space-y-2.5">
                @if ($admin->wajib_simpanan)
                    <a
                        href="{{ route('anggota.dashboard') }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 px-3.5 py-2 text-xs font-semibold text-emerald-300 hover:bg-emerald-500/25 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span>Lihat Mode Anggota Saya</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-3.5 py-2 text-xs font-medium text-slate-300 hover:bg-rose-600 hover:text-white transition cursor-pointer"
                    >
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Area --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden rounded-lg p-2 text-slate-600 hover:bg-slate-100"
                        aria-label="Buka menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="text-base font-semibold text-slate-900 truncate">@yield('header', 'Dashboard Admin')</h1>
                </div>

                <div class="text-right shrink-0">
                    <p class="text-sm font-semibold text-slate-800">{{ $admin->nama_lengkap }}</p>
                    <p class="text-xs text-slate-500">{{ $admin->nomor_anggota }} &middot; {{ $admin->peran }}</p>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div class="max-w-7xl mx-auto mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 flex items-center justify-between gap-3" role="status">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
