<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Verifikasi - S-KOLAK Kota Kediri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .angka-neraca { font-variant-numeric: tabular-nums; letter-spacing: 0.01em; }
    </style>
</head>
<body class="h-screen overflow-hidden" style="background-color:#F5F9FF;">

@php
    $notifCount = $notifCount ?? 0;
    $pendingCount = $pendingCount ?? 0;
    $activeMenu = 'menunggu';
    $userName = auth()->check() ? auth()->user()->name : 'Verifikator';
    $komoditasNama = $item->komoditas->nama ?? '-';
    $operatorNama = $item->operator->name ?? '-';
    $tanggal = optional($item->created_at)->translatedFormat('d M Y');
    $periode = \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($item->periode);
    $nilai = $item->nilai_neraca;

    // Warna dibuat seragam hitam & font angka dibuat lebih mudah dibaca
    // (bukan lagi font-mono berwarna-warni per kategori).
    $fields = [
        ['label' => 'Stok Awal',          'val' => $item->stok_awal],
        ['label' => 'Produksi',           'val' => $item->produksi],
        ['label' => 'Barang Masuk',       'val' => $item->masuk],
        ['label' => 'Barang Keluar',      'val' => $item->keluar],
        ['label' => 'Keb. Rumah Tangga',  'val' => $item->kebutuhan_rumah_tangga],
        ['label' => 'Keb. Non-RT',        'val' => $item->kebutuhan_non_rumah_tangga],
    ];
@endphp

<div class="flex h-screen overflow-hidden">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="hidden md:flex flex-col flex-shrink-0 w-[240px] border-r border-blue-100 bg-white">
        <div class="p-4 border-b border-blue-50 flex items-center gap-3">
            @if (file_exists(public_path('images/logo-kediri.png')))
                <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri" class="w-9 h-9 object-contain flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">SK</div>
            @endif
            <div class="overflow-hidden">
                <p class="text-sm font-bold truncate" style="color:#1E3A5F;">S-KOLAK</p>
                <p class="text-xs text-slate-400 truncate">Kota Kediri</p>
            </div>
        </div>

        <div class="mx-3 mt-3 p-3 rounded-xl" style="background-color:#EFF6FF;">
            <p class="text-xs font-semibold text-blue-600">Verifikator</p>
            <p class="text-xs font-medium truncate mt-0.5" style="color:#1E3A5F;">{{ $userName }}</p>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto mt-2">
            @php
                $menuItems = [
                    ['key' => 'dashboard',  'label' => 'Dashboard',                 'route' => 'verifikator.dashboard',  'badge' => null],
                    ['key' => 'menunggu',   'label' => 'Data Menunggu Verifikasi',  'route' => 'verifikator.menunggu',   'badge' => $pendingCount],
                    ['key' => 'riwayat',    'label' => 'Riwayat Verifikasi',        'route' => 'verifikator.riwayat',    'badge' => null],
                    ['key' => 'notifikasi', 'label' => 'Notifikasi',                'route' => 'verifikator.notifikasi', 'badge' => $notifCount],
                ];
                $menuIcons = [
                    'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                    'menunggu'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'riwayat'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'notifikasi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
                ];
            @endphp

            @foreach ($menuItems as $mi)
                @php $isActive = $activeMenu === $mi['key']; @endphp
                <a href="{{ Route::has($mi['route']) ? route($mi['route']) : '#' }}"
                   class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all"
                   style="{{ $isActive ? 'background-color:#2563EB; color:white; font-weight:600;' : 'color:#475569;' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        {!! $menuIcons[$mi['key']] !!}
                    </svg>
                    <span class="truncate flex-1 text-left">{{ $mi['label'] }}</span>
                    @if ($mi['badge'])
                        <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                              style="{{ $isActive ? 'background-color:rgba(255,255,255,0.3); color:white;' : 'background-color:#FEF3C7; color:#B45309;' }}">
                            {{ $mi['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-blue-50">
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-red-500 hover:bg-red-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" transform="scale(-1,1) translate(-24,0)"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="h-14 border-b border-blue-100 bg-white flex items-center px-4 gap-3 flex-shrink-0 shadow-sm">
            <div class="flex-1">
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Detail Verifikasi</h2>
                <p class="text-xs text-slate-400">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ Route::has('verifikator.notifikasi') ? route('verifikator.notifikasi') : '#' }}" class="relative w-9 h-9 flex items-center justify-center rounded-xl hover:bg-blue-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="#1E3A5F" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    @if ($notifCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-orange-500"></span>
                    @endif
                </a>
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background-color:#2563EB;">
                    {{ strtoupper(substr($userName, 0, 1)) }}
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            <div class="max-w-2xl mx-auto space-y-5">

                <div class="text-left">
                    <a href="{{ route('verifikator.menunggu') }}" class="inline-flex items-center gap-1 text-sm text-blue-500 hover:bg-blue-50 rounded-lg px-2 py-1 -ml-2 transition-colors">
                        ← Kembali
                    </a>
                    <h1 class="text-xl font-bold text-left" style="color:#1E3A5F;">Detail Verifikasi</h1>
                </div>

                <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-6 space-y-5">
                    <div class="flex items-start justify-between">
                        <h2 class="text-base font-bold" style="color:#1E3A5F;">{{ $komoditasNama }}</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-orange-50 text-orange-700 border-orange-200">Menunggu Verifikasi</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl p-3" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Operator</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $operatorNama }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Tanggal Input</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $tanggal }}</p>
                        </div>
                        <div class="rounded-xl p-3 col-span-2" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Periode</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $periode }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Data Neraca Pangan</h3>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($fields as $f)
                                <div class="rounded-xl p-3 border border-blue-50">
                                    <p class="text-xs text-slate-400">{{ $f['label'] }}</p>
                                    <p class="angka-neraca text-base font-bold mt-0.5 text-black">{{ number_format($f['val'], 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl p-4 border-2" style="border-color:{{ $nilai > 0 ? '#86EFAC' : '#FCA5A5' }}; background-color:{{ $nilai > 0 ? '#F0FDF4' : '#FEF2F2' }};">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-600">Nilai Neraca Pangan</p>
                            <p class="angka-neraca text-2xl font-bold text-black">{{ number_format($nilai, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @if ($item->keterangan)
                        <div class="rounded-xl p-3 bg-slate-50 border border-slate-100">
                            <p class="text-xs text-slate-400 mb-1">Keterangan Operator</p>
                            <p class="text-sm text-slate-600">{{ $item->keterangan }}</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('verifikator.menunggu.update', $item->id) }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col gap-1">
                            <label for="catatan" class="text-xs font-semibold text-slate-600">Catatan Verifikator (Opsional)</label>
                            <textarea id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan atau alasan jika data perlu revisi..."
                                      class="px-3 py-2.5 rounded-xl border border-blue-100 text-sm text-slate-700 bg-blue-50/20 outline-none focus:border-blue-400 resize-none">{{ old('catatan') }}</textarea>
                            @error('catatan') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="submit" name="status" value="revisi"
                                    class="flex-1 py-3 rounded-xl text-sm font-semibold border-2 border-red-300 text-red-600 hover:bg-red-50 flex items-center justify-center gap-2 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tidak Valid (Revisi)
                            </button>
                            <button type="submit" name="status" value="valid"
                                    class="flex-1 py-3 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md flex items-center justify-center gap-2 transition-all" style="background-color:#16A34A;">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Validasi Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>