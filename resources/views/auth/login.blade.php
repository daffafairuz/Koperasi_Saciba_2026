<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Koperasi Jasa Berkah Saciba</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8 antialiased text-slate-800">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex flex-col items-center text-center">
            <div class="h-14 w-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-lg shadow-emerald-600/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                </svg>
            </div>
            <h1 class="mt-4 text-2xl font-bold tracking-tight text-slate-900">
                Koperasi Jasa Berkah Saciba
            </h1>
            <p class="mt-1 text-sm text-slate-600">
                Sistem Informasi Simpan Pinjam &amp; Portal Anggota
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/70 rounded-2xl border border-slate-100 sm:px-10">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">Masuk ke Akun Anda</h2>
                <p class="text-xs text-slate-500 mt-1">Gunakan nomor HP terdaftar dan kata sandi Anda.</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-sm text-red-700" role="alert">
                    <div class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />
                        </svg>
                        <ul class="space-y-1">
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
                    <label for="no_hp" class="block text-sm font-medium text-slate-700">
                        Nomor HP
                    </label>
                    <div class="mt-1.5">
                        <input
                            id="no_hp"
                            name="no_hp"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            required
                            autofocus
                            value="{{ old('no_hp') }}"
                            placeholder="Contoh: 081200000001"
                            class="block w-full rounded-xl border @error('no_hp') border-red-400 focus:border-red-500 focus:ring-red-200 @else border-slate-300 focus:border-emerald-500 focus:ring-emerald-200 @enderror px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition"
                        >
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500">
                        Format <span class="font-medium">08...</span>, <span class="font-medium">628...</span>, atau <span class="font-medium">8...</span> otomatis disesuaikan.
                    </p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">
                        Kata Sandi
                    </label>
                    <div class="mt-1.5 relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="Masukkan kata sandi"
                            class="block w-full rounded-xl border @error('password') border-red-400 focus:border-red-500 focus:ring-red-200 @else border-slate-300 focus:border-emerald-500 focus:ring-emerald-200 @enderror px-4 py-2.5 pr-16 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 transition"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 px-3.5 text-xs font-medium text-slate-500 hover:text-emerald-700 focus:outline-none"
                        >
                            Lihat
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        value="1"
                        @checked(old('remember'))
                        class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                    >
                    <label for="remember" class="ml-2 block text-sm text-slate-700">
                        Ingat Saya
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full flex justify-center items-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-200 active:bg-emerald-800 transition cursor-pointer"
                >
                    Masuk
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500">
                    Pendaftaran anggota baru dan reset kata sandi dikelola langsung oleh Pengurus Koperasi.
                </p>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Koperasi Jasa Berkah Saciba
        </p>
    </div>

    <script>
        const toggleButton = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (toggleButton && passwordInput) {
            toggleButton.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleButton.textContent = isHidden ? 'Tutup' : 'Lihat';
            });
        }
    </script>
</body>
</html>
