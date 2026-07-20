<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - S-KOLAK Kota Kediri</title>
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
            <h2 class="text-2xl font-bold tracking-tight" style="color:#1E3A5F;">Lupa Password</h2>
            <p class="text-sm text-slate-500 mt-1">Masukkan Login ID Anda untuk membuat tautan reset password.</p>
        </div>

        @if (session('reset_url'))
            <div class="rounded-xl bg-green-50 border border-green-200 px-4 py-4 text-xs text-green-800 space-y-2">
                <p class="font-semibold">Tautan reset password untuk ID <span class="font-bold">{{ session('reset_id') }}</span> berhasil dibuat.</p>
                <p class="text-green-700">Sistem belum terhubung ke server email, jadi salin tautan di bawah ini dan berikan ke pengguna terkait (berlaku {{ 60 }} menit):</p>
                <div class="flex items-center gap-2">
                    <input id="resetUrlInput" type="text" readonly value="{{ session('reset_url') }}"
                           class="flex-1 px-3 py-2 rounded-lg border border-green-300 bg-white text-[11px] text-slate-700 outline-none">
                    <button type="button" onclick="copyResetUrl()" class="px-3 py-2 rounded-lg text-xs font-semibold text-white flex-shrink-0" style="background-color:#16A34A;">
                        Salin
                    </button>
                </div>
                <a href="{{ session('reset_url') }}" class="inline-block text-xs font-semibold underline" style="color:#2563EB;">Buka tautan reset password &rarr;</a>
            </div>
        @endif

        @if ($errors->any())
            <p class="text-xs text-red-600 flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                {{ $errors->first() }}
            </p>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <div class="flex flex-col gap-1.5">
                <label for="id" class="text-sm font-semibold" style="color:#1E3A5F;">Login ID</label>
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

            <button
                type="submit"
                class="w-full py-3.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-sm hover:shadow-md active:scale-[0.98]"
                style="background-color:#2563EB; color:white;"
            >
                Buat Tautan Reset Password
            </button>

            <div class="text-center -mt-1">
                <a href="{{ route('login') }}" class="text-xs font-medium transition-colors hover:underline" style="color:#2563EB;">
                    &larr; Kembali ke halaman login
                </a>
            </div>
        </form>
    </div>

    <script>
        function copyResetUrl() {
            const input = document.getElementById('resetUrlInput');
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard && navigator.clipboard.writeText(input.value);
        }
    </script>
</body>
</html>