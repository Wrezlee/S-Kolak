<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - S-KOLAK Kota Kediri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex" style="background-color:#EFF6FF;">

    <!-- LEFT: Illustration -->
    <div class="hidden lg:flex flex-col items-center justify-center" style="width:55%; background-color:#E0EDFF; padding:3rem;">
        <svg viewBox="0 0 480 400" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full max-w-md">
            <rect width="480" height="400" fill="#EFF6FF" rx="20" />
            <ellipse cx="240" cy="370" rx="220" ry="18" fill="#BFDBFE" opacity="0.5" />

            <rect x="60" y="200" width="130" height="120" rx="4" fill="#DBEAFE" stroke="#93C5FD" stroke-width="1.5" />
            <rect x="60" y="190" width="130" height="20" rx="3" fill="#3B82F6" />
            <rect x="90" y="255" width="35" height="65" rx="3" fill="#1D4ED8" />
            <rect x="145" y="240" width="25" height="30" rx="2" fill="#93C5FD" />
            <rect x="150" y="244" width="8" height="12" rx="1" fill="#DBEAFE" />
            <rect x="163" y="244" width="3" height="12" rx="1" fill="#2563EB" opacity="0.5" />
            <rect x="70" y="207" width="80" height="5" rx="2" fill="white" opacity="0.6" />

            <ellipse cx="75" cy="325" rx="12" ry="9" fill="#93C5FD" />
            <ellipse cx="97" cy="322" rx="12" ry="9" fill="#60A5FA" />
            <ellipse cx="86" cy="313" rx="12" ry="9" fill="#BFDBFE" />
            <ellipse cx="50" cy="328" rx="8" ry="6" fill="#DBEAFE" />

            <rect x="240" y="140" width="180" height="130" rx="10" fill="white" stroke="#93C5FD" stroke-width="1.5" />
            <rect x="240" y="140" width="180" height="22" rx="10" fill="#2563EB" />
            <rect x="240" y="152" width="180" height="10" fill="#2563EB" />
            <circle cx="253" cy="151" r="3" fill="#BFDBFE" />
            <circle cx="263" cy="151" r="3" fill="#93C5FD" />
            <circle cx="273" cy="151" r="3" fill="#60A5FA" />
            <rect x="260" y="220" width="12" height="35" rx="2" fill="#BFDBFE" />
            <rect x="276" y="210" width="12" height="45" rx="2" fill="#60A5FA" />
            <rect x="292" y="200" width="12" height="55" rx="2" fill="#2563EB" />
            <rect x="308" y="215" width="12" height="40" rx="2" fill="#93C5FD" />
            <rect x="324" y="205" width="12" height="50" rx="2" fill="#1D4ED8" />
            <rect x="340" y="195" width="12" height="60" rx="2" fill="#3B82F6" />
            <rect x="253" y="258" width="112" height="1.5" rx="1" fill="#DBEAFE" />
            <rect x="255" y="170" width="45" height="28" rx="4" fill="#EFF6FF" />
            <rect x="306" y="170" width="45" height="28" rx="4" fill="#EFF6FF" />
            <rect x="357" y="170" width="50" height="28" rx="4" fill="#EFF6FF" />
            <rect x="260" y="175" width="20" height="3" rx="1" fill="#93C5FD" />
            <rect x="260" y="181" width="30" height="5" rx="1" fill="#2563EB" />
            <rect x="311" y="175" width="20" height="3" rx="1" fill="#93C5FD" />
            <rect x="311" y="181" width="30" height="5" rx="1" fill="#2563EB" />
            <rect x="362" y="175" width="20" height="3" rx="1" fill="#93C5FD" />
            <rect x="362" y="181" width="30" height="5" rx="1" fill="#2563EB" />

            <ellipse cx="170" cy="310" rx="22" ry="12" fill="#DBEAFE" stroke="#93C5FD" stroke-width="1.5" />
            <ellipse cx="170" cy="307" rx="16" ry="8" fill="white" />
            <path d="M158 307 Q170 299 182 307" stroke="#BFDBFE" stroke-width="1.5" fill="none" />
            <line x1="205" y1="290" x2="205" y2="330" stroke="#93C5FD" stroke-width="2" />
            <ellipse cx="205" cy="283" rx="5" ry="8" fill="#60A5FA" />
            <ellipse cx="198" cy="293" rx="4" ry="6" fill="#60A5FA" transform="rotate(-30 198 293)" />
            <ellipse cx="212" cy="293" rx="4" ry="6" fill="#60A5FA" transform="rotate(30 212 293)" />

            <path d="M390 100 C390 90 380 82 370 82 C360 82 350 90 350 100 C350 115 370 130 370 130 C370 130 390 115 390 100Z" fill="#3B82F6" />
            <circle cx="370" cy="100" r="6" fill="white" />

            <rect x="20" y="240" width="20" height="80" rx="2" fill="#DBEAFE" opacity="0.6" />
            <rect x="15" y="225" width="30" height="20" rx="2" fill="#BFDBFE" opacity="0.6" />
            <rect x="420" y="250" width="18" height="70" rx="2" fill="#DBEAFE" opacity="0.6" />
            <rect x="415" y="238" width="28" height="16" rx="2" fill="#BFDBFE" opacity="0.6" />
            <rect x="440" y="265" width="15" height="55" rx="2" fill="#EFF6FF" opacity="0.8" />

            <path d="M230 90 C230 80 218 72 218 72 C218 72 206 80 206 90 C206 100 212 108 218 112 C224 108 230 100 230 90Z" fill="#DBEAFE" stroke="#3B82F6" stroke-width="1.5" />
            <path d="M213 90 L217 94 L224 86" stroke="#2563EB" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

            <path d="M195 275 Q220 260 240 270" stroke="#3B82F6" stroke-width="1.5" stroke-dasharray="4 3" fill="none" marker-end="url(#arrow)" />
            <defs>
                <marker id="arrow" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                    <path d="M0 0 L6 3 L0 6 Z" fill="#3B82F6" />
                </marker>
            </defs>

            <circle cx="420" cy="80" r="20" fill="#DBEAFE" opacity="0.5" />
            <circle cx="410" cy="70" r="12" fill="#BFDBFE" opacity="0.5" />
            <circle cx="50" cy="140" r="16" fill="#DBEAFE" opacity="0.4" />
            <circle cx="460" cy="200" r="10" fill="#BFDBFE" opacity="0.4" />
        </svg>

        <div class="text-center mt-8 flex flex-col items-center">
            <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri" class="w-20 h-20 object-contain mb-4 drop-shadow-md">
            <h1 class="text-2xl font-bold" style="color:#1E3A5F;">Sistem Ketersediaan Stok dan Laporan Aktual</h1>
            <p class="text-sm mt-2 font-semibold" style="color:#2563EB;">Kota Kediri</p>
            <div class="flex items-center justify-center gap-2 mt-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#60A5FA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
                <span class="text-xs text-slate-500">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</span>
            </div>
        </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-[460px] bg-white rounded-2xl shadow-lg border border-blue-100 p-10 flex flex-col gap-5">

            <!-- Logo & Title -->
            <div class="text-center mb-1">
                <div class="inline-flex items-center justify-center w-16 h-16 mb-3">
                    <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri" class="w-16 h-16 object-contain drop-shadow-sm">
                </div>
                <h2 class="text-3xl font-bold tracking-tight" style="color:#1E3A5F;">LOGIN</h2>
                <p class="text-sm text-slate-500 mt-1">S-KOLAK &middot; Kota Kediri</p>
            </div>

            {{-- Session status / error messages --}}
            @if (session('status'))
                <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-xs text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <p class="text-xs text-red-600 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5" id="loginForm">
                @csrf

                <!-- ID -->
                <div class="flex flex-col gap-1.5">
                    <label for="id" class="text-sm font-semibold" style="color:#1E3A5F;">ID</label>
                    <input
                        type="text"
                        name="id"
                        id="id"
                        value="{{ old('id') }}"
                        placeholder="Masukkan ID Anda"
                        required
                        autofocus
                        class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-all"
                        style="border-color:#DBEAFE; background-color:#F8FBFF; color:#1E3A5F;"
                        onfocus="this.style.borderColor='#2563EB'"
                        onblur="this.style.borderColor='#DBEAFE'"
                    >
                </div>

                <!-- Password -->
                <div class="flex flex-col gap-1.5">
                    <label for="password" class="text-sm font-semibold" style="color:#1E3A5F;">Password</label>
                    <div class="relative">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Masukkan password Anda"
                            required
                            class="w-full px-4 py-3 pr-12 rounded-xl border text-sm outline-none transition-all"
                            style="border-color:#DBEAFE; background-color:#F8FBFF; color:#1E3A5F;"
                            onfocus="this.style.borderColor='#2563EB'"
                            onblur="this.style.borderColor='#DBEAFE'"
                        >
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-500 transition-colors">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Login Button -->
                <button
                    type="submit"
                    id="loginButton"
                    class="w-full py-3.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-[0.98] disabled:hover:shadow-sm"
                    style="background-color:#2563EB; color:white;"
                >
                    <span id="loginButtonContent" class="flex items-center gap-2">Login</span>
                </button>

                <!-- Forgot -->
                <div class="text-center -mt-1">
                    <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="text-xs font-medium transition-colors hover:underline" style="color:#2563EB;">
                        Lupa password?
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginButton');
            const content = document.getElementById('loginButtonContent');
            if (btn.disabled) return;
            btn.disabled = true;
            btn.style.backgroundColor = '#93C5FD';
            content.innerHTML = `
                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Memproses...
            `;
        });

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
    </script>
</body>
</html>