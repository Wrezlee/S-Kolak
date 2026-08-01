<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - S-KOLAK Kota Kediri</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6" style="background-color:#EFF6FF;">

    <div class="w-full max-w-[460px] bg-white rounded-2xl shadow-lg border border-blue-100 p-10 flex flex-col gap-5">

        <div class="text-center mb-1">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-3">
                <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri" class="w-16 h-16 object-contain drop-shadow-sm">
            </div>
            <h2 class="text-2xl font-bold tracking-tight" style="color:#1E3A5F;">Reset Password</h2>
            <p class="text-sm text-slate-500 mt-1">Login ID: <span class="font-semibold" style="color:#1E3A5F;">{{ $loginId }}</span></p>
        </div>

        @if ($errors->any())
            <p class="text-xs text-red-600 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                {{ $errors->first() }}
            </p>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
            @csrf
            <input type="hidden" name="id" value="{{ $loginId }}">
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="flex flex-col gap-1.5">
                <label for="password" class="text-sm font-semibold" style="color:#1E3A5F;">Password Baru</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Minimal 8 karakter"
                    required
                    minlength="8"
                    autofocus
                    autocomplete="new-password"
                    oninput="skolakUpdatePasswordHints(this.value)"
                    class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-all"
                    style="border-color:#DBEAFE; background-color:#F8FBFF; color:#1E3A5F;"
                    onfocus="this.style.borderColor='#2563EB'"
                    onblur="this.style.borderColor='#DBEAFE'"
                >
                <ul class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-[11px] text-slate-400">
                    <li data-rule="length" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>Minimal 8 karakter</li>
                    <li data-rule="upper" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>1 Huruf besar</li>
                    <li data-rule="lower" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>1 Huruf kecil</li>
                    <li data-rule="number" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>1 Angka</li>
                    <li data-rule="symbol" class="flex items-center gap-1"><span class="dot w-1.5 h-1.5 rounded-full bg-slate-300"></span>1 Simbol</li>
                </ul>
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="password_confirmation" class="text-sm font-semibold" style="color:#1E3A5F;">Konfirmasi Password Baru</label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="Ulangi password baru"
                    required
                    minlength="8"
                    autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-all"
                    style="border-color:#DBEAFE; background-color:#F8FBFF; color:#1E3A5F;"
                    onfocus="this.style.borderColor='#2563EB'"
                    onblur="this.style.borderColor='#DBEAFE'"
                >
            </div>

            <script>
                // Indikator visual format password (besar/kecil/angka/simbol, min 8 karakter)
                function skolakUpdatePasswordHints(value) {
                    const rules = {
                        length: value.length >= 8,
                        upper:  /[A-Z]/.test(value),
                        lower:  /[a-z]/.test(value),
                        number: /[0-9]/.test(value),
                        symbol: /[^A-Za-z0-9]/.test(value),
                    };
                    Object.keys(rules).forEach(function (rule) {
                        const el = document.querySelector('li[data-rule="' + rule + '"] .dot');
                        if (!el) return;
                        el.classList.toggle('bg-green-500', rules[rule]);
                        el.classList.toggle('bg-slate-300', !rules[rule]);
                    });
                }
            </script>

            <button
                type="submit"
                class="w-full py-3.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-[0.98]"
                style="background-color:#2563EB; color:white;"
            >
                Simpan Password Baru
            </button>

            <div class="text-center -mt-1">
                <a href="{{ route('login') }}" class="text-xs font-medium transition-colors hover:underline" style="color:#2563EB;">
                    &larr; Kembali ke halaman login
                </a>
            </div>
        </form>
    </div>
</body>
</html>