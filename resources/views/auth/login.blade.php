<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Koperasi Jasa Berkah Saciba</title>

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

    <style>
        body {
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
        }
        .obsidian-glow {
            background-image:
                radial-gradient(circle at 18% 15%, rgba(16, 185, 129, 0.22), transparent 46%),
                radial-gradient(circle at 85% 88%, rgba(5, 150, 105, 0.18), transparent 44%);
        }
        .subtle-grid {
            background-size: 32px 32px;
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
        }
    </style>
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased selection:bg-emerald-500/20 selection:text-emerald-900">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12">
        {{-- Left Panel: Obsidian Neo-Fintech Showcase --}}
        <section class="relative hidden lg:flex lg:col-span-6 xl:col-span-7 flex-col justify-between bg-zinc-950 text-white p-10 xl:p-14 overflow-hidden obsidian-glow">
            <div class="pointer-events-none absolute inset-0 subtle-grid"></div>

            {{-- Brand Lockup --}}
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shadow-lg shadow-emerald-950/60 ring-1 ring-emerald-400/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
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
                    <div>
                        <p class="text-sm font-bold tracking-tight text-white">Koperasi Jasa Berkah Saciba</p>
                        <p class="font-mono text-[11px] uppercase tracking-widest text-emerald-400/90">Digital Ledger &amp; Portal</p>
                    </div>
                </div>

                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 font-mono text-[11px] font-medium text-emerald-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                    SISTEM AKTIF
                </span>
            </div>

            {{-- Main Value Proposition --}}
            <div class="relative z-10 max-w-xl space-y-8 my-auto py-10">
                <div class="space-y-4">
                    <h1 class="text-4xl xl:text-5xl font-extrabold tracking-tight leading-[1.12] text-white">
                        Transparansi Keuangan Koperasi, <span class="text-emerald-400">Presisi &amp; Real-Time.</span>
                    </h1>
                    <p class="text-base text-zinc-400 leading-relaxed">
                        Pantau akumulasi simpanan, jadwal angsuran syariah, hingga rincian setiap struk pembayaran dalam satu antarmuka terpadu yang terverifikasi.
                    </p>
                </div>

                {{-- 3 Transparency Pillars --}}
                <div class="grid grid-cols-1 gap-3">
                    <div class="flex items-start gap-3.5 rounded-2xl bg-white/[0.03] border border-white/10 p-4 backdrop-blur-xs">
                        <div class="h-9 w-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                                <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Rekap Simpanan Terintegrasi</p>
                            <p class="text-xs text-zinc-400 mt-0.5">Simpanan Pokok, Simpanan Wajib, dan riwayat tagihan bulanan tersinkronisasi otomatis.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 rounded-2xl bg-white/[0.03] border border-white/10 p-4 backdrop-blur-xs">
                        <div class="h-9 w-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Pembiayaan Syariah &amp; Audit Transparan</p>
                            <p class="text-xs text-zinc-400 mt-0.5">Pinjaman uang margin 0% dan pembiayaan barang dengan jadwal angsuran serta bukti struk permanen.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 rounded-2xl bg-white/[0.03] border border-white/10 p-4 backdrop-blur-xs">
                        <div class="h-9 w-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <path d="M14 2v6h6" />
                                <path d="M16 13H8" />
                                <path d="M16 17H8" />
                                <path d="M10 9H8" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Rincian Alokasi Pembayaran Jelas</p>
                            <p class="text-xs text-zinc-400 mt-0.5">Setiap setoran anggota menampilkan pemecahan pos Simpanan Pokok, Wajib, maupun Cicilan.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Metadata --}}
            <div class="relative z-10 flex items-center justify-between pt-6 border-t border-white/10 text-xs text-zinc-500">
                <span>&copy; {{ date('Y') }} Koperasi Jasa Berkah Saciba</span>
                <span class="font-mono tabular-nums text-zinc-400">SACIBA // LEDGER</span>
            </div>
        </section>

        {{-- Right Panel: Crisp White Auth Workspace --}}
        <section class="lg:col-span-6 xl:col-span-5 flex flex-col justify-center bg-white lg:bg-zinc-50 px-5 py-10 sm:px-10 lg:px-12">
            <div class="w-full max-w-md mx-auto">
                {{-- Mobile Brand Header --}}
                <div class="lg:hidden mb-8 rounded-2xl bg-zinc-950 text-white p-5 obsidian-glow border border-emerald-500/20">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
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
                        <div>
                            <h1 class="text-base font-bold tracking-tight text-white">Koperasi Jasa Berkah Saciba</h1>
                            <p class="font-mono text-[11px] text-emerald-400">Portal Anggota &amp; Pengurus</p>
                        </div>
                    </div>
                </div>

                {{-- Login Card --}}
                <div class="bg-white rounded-2xl lg:border lg:border-zinc-200/80 lg:shadow-xs lg:p-8">
                    <div class="mb-7">
                        <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-emerald-600">Akses Portal</p>
                        <h2 class="mt-1.5 text-2xl font-bold tracking-tight text-zinc-950">Masuk ke Akun Anda</h2>
                        <p class="mt-1 text-sm text-zinc-500">
                            Gunakan nomor HP terdaftar dan kata sandi keanggotaan Anda.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-xl bg-rose-500/10 border border-rose-500/20 p-4 text-sm text-rose-700" role="alert">
                            <div class="flex items-start gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12.01" y1="16" y2="16" />
                                </svg>
                                <ul class="space-y-1 text-xs sm:text-sm font-medium">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.process') }}" class="space-y-5" novalidate>
                        @csrf

                        <div>
                            <label for="no_hp" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">
                                Nomor HP
                            </label>
                            <div class="mt-2 relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="14" height="20" x="5" y="2" rx="2" ry="2" />
                                        <path d="M12 18h.01" />
                                    </svg>
                                </div>
                                <input
                                    id="no_hp"
                                    name="no_hp"
                                    type="tel"
                                    inputmode="tel"
                                    autocomplete="tel"
                                    required
                                    autofocus
                                    value="{{ old('no_hp') }}"
                                    placeholder="081200000001"
                                    class="block w-full rounded-xl border @error('no_hp') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-zinc-200 focus:border-emerald-500 focus:ring-emerald-500 @enderror bg-white pl-10 pr-4 py-2.5 font-mono tabular-nums text-sm text-zinc-950 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-0 transition"
                                >
                            </div>
                            <p class="mt-1.5 text-xs text-zinc-500">
                                Format <span class="font-mono text-zinc-700">08...</span>, <span class="font-mono text-zinc-700">628...</span>, atau <span class="font-mono text-zinc-700">8...</span> otomatis disesuaikan.
                            </p>
                        </div>

                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-zinc-600">
                                Kata Sandi
                            </label>
                            <div class="mt-2 relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 pl-3.5 flex items-center text-zinc-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                    placeholder="Masukkan kata sandi"
                                    class="block w-full rounded-xl border @error('password') border-rose-400 focus:border-rose-500 focus:ring-rose-500/20 @else border-zinc-200 focus:border-emerald-500 focus:ring-emerald-500 @enderror bg-white pl-10 pr-11 py-2.5 text-sm text-zinc-950 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-offset-0 transition"
                                >
                                <button
                                    type="button"
                                    id="togglePassword"
                                    aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute inset-y-0 right-0 px-3.5 flex items-center text-zinc-400 hover:text-zinc-700 focus:outline-none cursor-pointer transition"
                                >
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49" />
                                        <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242" />
                                        <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143" />
                                        <path d="m2 2 20 20" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-0.5">
                            <label for="remember" class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    value="1"
                                    @checked(old('remember'))
                                    class="h-4 w-4 rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                <span class="text-sm text-zinc-600">Ingat Saya</span>
                            </label>
                        </div>

                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-zinc-900 hover:bg-zinc-800 active:bg-zinc-950 px-4 py-3 text-sm font-semibold text-white shadow-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition cursor-pointer"
                        >
                            <span>Masuk ke Sistem</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </button>
                    </form>

                    <div class="mt-7 pt-5 border-t border-zinc-100">
                        <div class="flex items-start gap-2.5 text-xs text-zinc-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-zinc-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 16v-4" />
                                <path d="M12 8h.01" />
                            </svg>
                            <p>Pendaftaran anggota baru dan reset kata sandi dikelola langsung oleh Pengurus Koperasi.</p>
                        </div>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs text-zinc-400 lg:hidden">
                    &copy; {{ date('Y') }} Koperasi Jasa Berkah Saciba
                </p>
            </div>
        </section>
    </div>

    <script>
        const toggleButton = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                eyeIcon?.classList.toggle('hidden', isHidden);
                eyeOffIcon?.classList.toggle('hidden', !isHidden);
            });
        }
    </script>
</body>
</html>
