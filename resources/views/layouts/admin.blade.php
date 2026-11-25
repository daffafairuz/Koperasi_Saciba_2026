<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') — Koperasi Jasa Berkah Saciba</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                            mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
                        },
                        boxShadow: {
                            xs: '0 1px 2px 0 rgb(9 9 11 / 0.04)',
                        },
                    },
                },
            };
        </script>
    @endif

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased selection:bg-emerald-500/20 selection:text-emerald-900" x-data="{ sidebarOpen: false }">
    @php
        $admin = auth()->user();
        $inisialAdmin = collect(explode(' ', trim($admin->nama_lengkap)))
            ->filter()
            ->take(2)
            ->map(fn (string $kata): string => strtoupper(mb_substr($kata, 0, 1)))
            ->implode('');

        $menuGroups = [
            [
                'heading' => 'Ikhtisar',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'href' => route('admin.dashboard'),
                        'active' => request()->routeIs('admin.dashboard'),
                        'icon' => 'layout-dashboard',
                    ],
                ],
            ],
            [
                'heading' => 'Master Data',
                'items' => [
                    [
                        'label' => 'Data Anggota',
                        'href' => route('admin.anggota.index'),
                        'active' => request()->routeIs('admin.anggota.*'),
                        'icon' => 'users',
                    ],
                    [
                        'label' => 'Pengaturan Koperasi',
                        'href' => route('admin.pengaturan.index'),
                        'active' => request()->routeIs('admin.pengaturan.*'),
                        'icon' => 'sliders',
                    ],
                ],
            ],
            [
                'heading' => 'Operasional & Kas',
                'items' => [
                    [
                        'label' => 'Pinjaman',
                        'href' => route('admin.pinjaman.index'),
                        'active' => request()->routeIs('admin.pinjaman.*'),
                        'icon' => 'landmark',
                    ],
                    [
                        'label' => 'Input Pembayaran',
                        'href' => route('admin.pembayaran.index'),
                        'active' => request()->routeIs('admin.pembayaran.*'),
                        'icon' => 'receipt',
                    ],
                    [
                        'label' => 'Kas Usaha',
                        'href' => route('admin.kas-usaha.index'),
                        'active' => request()->routeIs('admin.kas-usaha.*'),
                        'icon' => 'wallet',
                    ],
                ],
            ],
        ];
    @endphp

    <div class="min-h-screen flex">
        {{-- Mobile Sidebar Overlay --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            @click="sidebarOpen = false"
            class="print:hidden fixed inset-0 z-40 bg-zinc-950/40 backdrop-blur-xs lg:hidden"
        ></div>

        {{-- Minimalist Light Neo-Fintech Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="print:hidden fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-zinc-200/80 flex flex-col transition-transform duration-200 ease-out lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen shrink-0"
        >
            {{-- Brand Header --}}
            <div class="h-16 px-5 flex items-center justify-between border-b border-zinc-200/80">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                    <div class="h-9 w-9 rounded-xl bg-zinc-950 text-emerald-400 flex items-center justify-center shrink-0 shadow-xs ring-1 ring-emerald-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18" />
                            <path d="M3 10h18" />
                            <path d="M5 6l7-3 7 3" />
                            <path d="M4 10v11" />
                            <path d="M20 10v11" />
                            <path d="M8 14v3" />
                            <path d="M12 14v3" />
                            <path d="M16 14v3" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold tracking-tight text-zinc-950 truncate">Berkah Saciba</p>
                        <p class="font-mono text-[10px] uppercase tracking-widest text-emerald-600 font-semibold">Admin Terminal</p>
                    </div>
                </a>

                <button
                    type="button"
                    @click="sidebarOpen = false"
                    aria-label="Tutup navigasi"
                    class="lg:hidden rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 cursor-pointer"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>

            {{-- Navigation Groups --}}
            <nav class="flex-1 px-3 py-5 space-y-6 overflow-y-auto" aria-label="Navigasi Admin">
                @foreach ($menuGroups as $group)
                    <div>
                        <p class="px-3 mb-2 font-mono text-[10px] font-semibold uppercase tracking-widest text-zinc-400">
                            {{ $group['heading'] }}
                        </p>
                        <div class="space-y-1">
                            @foreach ($group['items'] as $menu)
                                <a
                                    href="{{ $menu['href'] }}"
                                    class="group flex items-center justify-between gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition {{ $menu['active'] ? 'bg-zinc-900 text-white shadow-xs' : 'text-zinc-600 hover:bg-zinc-100/80 hover:text-zinc-950' }}"
                                >
                                    <span class="flex items-center gap-2.5 min-w-0">
                                        @switch($menu['icon'])
                                            @case('layout-dashboard')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $menu['active'] ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="7" height="9" x="3" y="3" rx="1" />
                                                    <rect width="7" height="5" x="14" y="3" rx="1" />
                                                    <rect width="7" height="9" x="14" y="12" rx="1" />
                                                    <rect width="7" height="5" x="3" y="16" rx="1" />
                                                </svg>
                                                @break
                                            @case('users')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $menu['active'] ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                                </svg>
                                                @break
                                            @case('sliders')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $menu['active'] ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="21" x2="14" y1="4" y2="4" />
                                                    <line x1="10" x2="3" y1="4" y2="4" />
                                                    <line x1="21" x2="12" y1="12" y2="12" />
                                                    <line x1="8" x2="3" y1="12" y2="12" />
                                                    <line x1="21" x2="16" y1="20" y2="20" />
                                                    <line x1="12" x2="3" y1="20" y2="20" />
                                                    <line x1="14" x2="14" y1="2" y2="6" />
                                                    <line x1="8" x2="8" y1="10" y2="14" />
                                                    <line x1="16" x2="16" y1="18" y2="22" />
                                                </svg>
                                                @break
                                            @case('landmark')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $menu['active'] ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="20" height="14" x="2" y="5" rx="2" />
                                                    <line x1="2" x2="22" y1="10" y2="10" />
                                                </svg>
                                                @break
                                            @case('receipt')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $menu['active'] ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                                                    <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                                                    <path d="M12 17.5v-11" />
                                                </svg>
                                                @break
                                            @case('wallet')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $menu['active'] ? 'text-emerald-400' : 'text-zinc-400 group-hover:text-zinc-700' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                                                    <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                                                </svg>
                                                @break
                                        @endswitch
                                        <span class="truncate">{{ $menu['label'] }}</span>
                                    </span>

                                    @if ($menu['active'])
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>

            {{-- Sidebar Footer Actions --}}
            <div class="p-3.5 border-t border-zinc-200/80 space-y-2 bg-zinc-50/50">
                @if ($admin->wajib_simpanan)
                    <a
                        href="{{ route('anggota.dashboard') }}"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-500/15 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span>Lihat Mode Anggota Saya</span>
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-zinc-200/90 bg-white px-3 py-2 text-xs font-semibold text-zinc-600 hover:bg-rose-500/10 hover:text-rose-700 hover:border-rose-500/20 transition cursor-pointer"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                            <polyline points="16 17 21 12 16 7" />
                            <line x1="21" x2="9" y1="12" y2="12" />
                        </svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Workspace --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Frosted Glass Sticky Header --}}
            <header class="print:hidden sticky top-0 z-30 h-16 backdrop-blur-md bg-white/80 border-b border-zinc-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden rounded-xl border border-zinc-200/80 bg-white p-2 text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 cursor-pointer"
                        aria-label="Buka menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" x2="20" y1="12" y2="12" />
                            <line x1="4" x2="20" y1="6" y2="6" />
                            <line x1="4" x2="20" y1="18" y2="18" />
                        </svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-bold tracking-tight text-zinc-950 truncate">@yield('header', 'Dashboard Admin')</h1>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="hidden sm:block text-right">
                        <p class="text-xs font-semibold text-zinc-900">{{ $admin->nama_lengkap }}</p>
                        <p class="font-mono tabular-nums text-[11px] text-zinc-500">
                            {{ $admin->nomor_anggota }} &middot; <span class="text-emerald-700 font-semibold">{{ $admin->peran }}</span>
                        </p>
                    </div>
                    <div class="h-9 w-9 rounded-xl bg-zinc-900 text-emerald-400 font-mono text-xs font-bold flex items-center justify-center ring-1 ring-zinc-800 shadow-xs">
                        {{ $inisialAdmin ?: 'AD' }}
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        class="max-w-7xl mx-auto mb-6 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 px-4 py-3.5 text-sm text-emerald-800 flex items-center justify-between gap-3"
                        role="status"
                    >
                        <div class="flex items-center gap-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" aria-label="Tutup notifikasi" class="text-emerald-700/70 hover:text-emerald-900 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
