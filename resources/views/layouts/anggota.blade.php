<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Anggota') — Koperasi Jasa Berkah Saciba</title>

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
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased selection:bg-emerald-500/20 selection:text-emerald-900">
    @php
        $user = auth()->user();
        $navItems = [
            ['label' => 'Beranda', 'route' => 'anggota.dashboard', 'active' => 'anggota.dashboard', 'icon' => 'home'],
            ['label' => 'Simpanan', 'route' => 'anggota.simpanan', 'active' => 'anggota.simpanan', 'icon' => 'wallet'],
            ['label' => 'Pinjaman', 'route' => 'anggota.pinjaman', 'active' => 'anggota.pinjaman', 'icon' => 'credit-card'],
            ['label' => 'Riwayat', 'route' => 'anggota.riwayat', 'active' => 'anggota.riwayat', 'icon' => 'clock'],
        ];
    @endphp

    {{-- Frosted Glass Top Header & Desktop Segmented Nav --}}
    <header class="sticky top-0 z-30 backdrop-blur-md bg-white/80 border-b border-zinc-200/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3">
                <a href="{{ route('anggota.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
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
                        <p class="text-sm font-bold tracking-tight text-zinc-950 truncate">Koperasi Jasa Berkah Saciba</p>
                        <p class="text-xs text-zinc-500 truncate">
                            {{ $user->nama_lengkap }} &middot; <span class="font-mono tabular-nums text-zinc-600">{{ $user->nomor_anggota }}</span>
                        </p>
                    </div>
                </a>

                {{-- Desktop Segmented Navigation --}}
                <nav class="hidden md:flex items-center gap-1 rounded-xl bg-zinc-100/90 border border-zinc-200/70 p-1" aria-label="Navigasi Portal Anggota">
                    @foreach ($navItems as $item)
                        @php $isActive = request()->routeIs($item['active']); @endphp
                        <a
                            href="{{ route($item['route']) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ $isActive ? 'bg-zinc-900 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950' }}"
                        >
                            @if ($isActive)
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            @endif
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                {{-- Right Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if ($user->isAdmin())
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-900 hover:bg-zinc-800 px-3 py-1.5 text-xs font-semibold text-white shadow-xs transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 19-7-7 7-7" />
                                <path d="M19 12H5" />
                            </svg>
                            <span class="hidden sm:inline">Kembali ke Panel Admin</span>
                            <span class="sm:hidden">Admin</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-zinc-200/90 bg-white px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-rose-500/10 hover:text-rose-700 hover:border-rose-500/20 transition cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-28 md:pb-12">
        @yield('content')
    </main>

    {{-- Mobile Floating Glass Bottom Navigation Bar --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 backdrop-blur-lg bg-white/90 border-t border-zinc-200 shadow-[0_-8px_24px_-12px_rgba(9,9,11,0.12)]" aria-label="Navigasi Bawah Anggota">
        <div class="grid grid-cols-4 h-16 max-w-md mx-auto">
            @foreach ($navItems as $item)
                @php $isActive = request()->routeIs($item['active']); @endphp
                <a
                    href="{{ route($item['route']) }}"
                    class="relative flex flex-col items-center justify-center gap-1 text-[11px] transition {{ $isActive ? 'text-zinc-950 font-semibold' : 'text-zinc-500 hover:text-zinc-800 font-medium' }}"
                >
                    @if ($isActive)
                        <span class="absolute top-0 h-0.5 w-8 rounded-full bg-emerald-500"></span>
                    @endif

                    @switch($item['icon'])
                        @case('home')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $isActive ? 'text-emerald-600' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                                <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            </svg>
                            @break
                        @case('wallet')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $isActive ? 'text-emerald-600' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                                <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                            </svg>
                            @break
                        @case('credit-card')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $isActive ? 'text-emerald-600' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="5" rx="2" />
                                <line x1="2" x2="22" y1="10" y2="10" />
                            </svg>
                            @break
                        @case('clock')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $isActive ? 'text-emerald-600' : 'text-zinc-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            @break
                    @endswitch
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
</body>
</html>
