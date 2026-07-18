<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Operator - S-KOLAK Kota Kediri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-screen overflow-hidden" style="background-color:#F5F9FF;">

@php
    // Nilai default apabila controller belum mengirim data.
    $summary = $summary ?? ['total' => 10, 'valid' => 8, 'menunggu' => 0, 'revisi' => 1];
    $totalEntri = $totalEntri ?? $summary['total'];

    $dataTerbaru = $dataTerbaru ?? collect([
        ['periode' => 'Jan 2025', 'komoditas' => 'Beras',        'nilai' => 525,   'status' => 'valid',    'tanggal' => '15 Jan 2025'],
        ['periode' => 'Feb 2025', 'komoditas' => 'Beras',        'nilai' => 577,   'status' => 'valid',    'tanggal' => '14 Feb 2025'],
        ['periode' => 'Mar 2025', 'komoditas' => 'Beras',        'nilai' => 588,   'status' => 'valid',    'tanggal' => '15 Mar 2025'],
        ['periode' => 'Mei 2025', 'komoditas' => 'Beras',        'nilai' => 686,   'status' => 'valid',    'tanggal' => '15 Mei 2025'],
        ['periode' => 'Mar 2025', 'komoditas' => 'Kedelai',      'nilai' => 80,    'status' => 'revisi',   'tanggal' => '08 Mar 2025'],
        ['periode' => 'Jan 2025', 'komoditas' => 'Cabai Rawit',  'nilai' => 900,   'status' => 'valid',    'tanggal' => '18 Jan 2025'],
        ['periode' => 'Feb 2025', 'komoditas' => 'Cabai Rawit',  'nilai' => -260,  'status' => 'valid',    'tanggal' => '14 Feb 2025'],
        ['periode' => 'Jan 2025', 'komoditas' => 'Daging Sapi',  'nilai' => 200,   'status' => 'valid',    'tanggal' => '17 Jan 2025'],
        ['periode' => 'Feb 2025', 'komoditas' => 'Daging Sapi',  'nilai' => -1570, 'status' => 'valid',    'tanggal' => '14 Feb 2025'],
        ['periode' => 'Apr 2025', 'komoditas' => 'Gula Konsumsi','nilai' => 5150,  'status' => 'draft',    'tanggal' => '14 Apr 2025'],
    ]);

    $notifCount = $notifCount ?? 1;
    $notifDropdownItems = $notifDropdownItems ?? (isset($aktivitas) ? collect($aktivitas)->take(5)->values() : (isset($notifikasi) ? collect($notifikasi)->take(5)->values() : (
        auth()->check()
            ? \App\Models\Notifikasi::where('user_id', auth()->id())->latest()->take(5)->get()->map(function ($n) {
                $pesanLower = \Illuminate\Support\Str::lower($n->pesan);
                $tipe = 'info';
                if (\Illuminate\Support\Str::contains($pesanLower, ['revisi', 'dikembalikan', 'ditolak'])) {
                    $tipe = 'warning';
                } elseif (\Illuminate\Support\Str::contains($pesanLower, ['divalidasi', 'valid', 'disetujui'])) {
                    $tipe = 'success';
                }
                return [
                    'pesan' => $n->pesan,
                    'waktu' => \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $tipe,
                ];
            })->values()
            : collect()
    )));
    $activeMenu = 'dashboard';
    $userName = auth()->check() ? auth()->user()->name : 'Operator';
    $firstName = trim(explode(',', $userName)[0]);

    $statusBadge = [
        'valid'    => ['label' => 'Valid',        'cls' => 'bg-green-50 text-green-700 border-green-200'],
        'menunggu' => ['label' => 'Menunggu',     'cls' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'revisi'   => ['label' => 'Perlu Revisi', 'cls' => 'bg-red-50 text-red-700 border-red-200'],
        'draft'    => ['label' => 'Draft',        'cls' => 'bg-slate-50 text-slate-600 border-slate-200'],
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
            <p class="text-xs font-semibold text-blue-600">Operator</p>
            <p class="text-xs font-medium truncate mt-0.5" style="color:#1E3A5F;">{{ $userName }}</p>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto mt-2">
            @php
                $menuItems = [
                    ['key' => 'dashboard',  'label' => 'Dashboard',           'route' => 'operator.dashboard',  'badge' => null],
                    ['key' => 'input',      'label' => 'Input Neraca Pangan', 'route' => 'operator.input',      'badge' => null],
                    ['key' => 'data',       'label' => 'Data Neraca Saya',    'route' => 'operator.data',       'badge' => $totalEntri],
                    ['key' => 'laporan',    'label' => 'Laporan',             'route' => 'operator.laporan',    'badge' => null],
                    ['key' => 'notifikasi', 'label' => 'Notifikasi',          'route' => 'operator.notifikasi', 'badge' => $notifCount],
                ];
                $menuIcons = [
                    'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                    'input'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
                    'data'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>',
                    'laporan'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                    'notifikasi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
                ];
            @endphp

            @foreach ($menuItems as $item)
                @php $isActive = $activeMenu === $item['key']; @endphp
                <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                   class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all"
                   style="{{ $isActive ? 'background-color:#2563EB; color:white; font-weight:600;' : 'color:#475569;' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        {!! $menuIcons[$item['key']] !!}
                    </svg>
                    <span class="truncate flex-1 text-left">{{ $item['label'] }}</span>
                    @if ($item['badge'])
                        <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold"
                              style="{{ $isActive ? 'background-color:rgba(255,255,255,0.3); color:white;' : 'background-color:#FEF3C7; color:#B45309;' }}">
                            {{ $item['badge'] }}
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

        {{-- Topbar --}}
        <header class="h-14 border-b border-blue-100 bg-white flex items-center px-4 gap-3 flex-shrink-0 shadow-sm">
            <div class="flex-1">
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Dashboard Operator</h2>
                <p class="text-xs text-slate-400">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <button type="button" onclick="toggleNotifDropdown(event)" class="relative w-9 h-9 flex items-center justify-center rounded-xl hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="#1E3A5F" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        @if ($notifCount > 0)
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-orange-500"></span>
                        @endif
                    </button>

                    {{-- Popup notifikasi singkat --}}
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-xl border border-blue-100 shadow-lg z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-blue-50 flex items-center justify-between">
                            <h4 class="text-sm font-bold" style="color:#1E3A5F;">Notifikasi</h4>
                            @if ($notifCount > 0)
                                <span class="text-xs px-1.5 py-0.5 rounded-full font-bold" style="background-color:#FEF3C7; color:#B45309;">{{ $notifCount }} baru</span>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-blue-50">
                            @forelse ($notifDropdownItems as $n)
                                <div class="px-4 py-3 flex items-start gap-3 {{ !($n['baca'] ?? true) ? 'bg-blue-50/40' : '' }}">
                                    <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 {{ ($n['tipe'] ?? '') === 'success' ? 'bg-green-100' : (($n['tipe'] ?? '') === 'warning' ? 'bg-orange-100' : 'bg-blue-100') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px] {{ ($n['tipe'] ?? '') === 'success' ? 'text-green-600' : (($n['tipe'] ?? '') === 'warning' ? 'text-orange-600' : 'text-blue-600') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-slate-700 leading-snug">{{ $n['pesan'] ?? '-' }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $n['waktu'] ?? '' }}</p>
                                    </div>
                                    @if (!($n['baca'] ?? true))
                                        <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></div>
                                    @endif
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-xs text-slate-400">Tidak ada notifikasi.</div>
                            @endforelse
                        </div>
                        <a href="{{ Route::has('operator.notifikasi') ? route('operator.notifikasi') : '#' }}" class="block text-center text-xs font-semibold text-blue-600 hover:bg-blue-50 py-2.5 border-t border-blue-50 transition-colors">Lihat Semua</a>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background-color:#2563EB;">
                    {{ strtoupper(substr($userName, 0, 1)) }}
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5">

            <div>
                <h1 class="text-xl font-bold" style="color:#1E3A5F;">Dashboard Operator</h1>
                <p class="text-sm text-slate-500">Selamat datang, {{ $firstName }}</p>
            </div>

            {{-- Alert revisi --}}
            @if ($summary['revisi'] > 0)
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-orange-200 bg-orange-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] text-orange-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/>
                    </svg>
                    <p class="text-sm text-orange-700 font-medium">
                        Ada <strong>{{ $summary['revisi'] }}</strong> data yang perlu direvisi. Segera perbaiki dan kirim ulang untuk verifikasi.
                    </p>
                </div>
            @endif

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-blue-50 border-blue-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Data Saya</p>
                        <p class="text-2xl font-bold mt-0.5 text-black">{{ $summary['total'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">semua status</p>
                    </div>
                </div>
                <div class="bg-green-50 border-green-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Sudah Valid</p>
                        <p class="text-2xl font-bold mt-0.5 text-black">{{ $summary['valid'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">terverifikasi</p>
                    </div>
                </div>
                <div class="bg-yellow-50 border-yellow-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-yellow-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Menunggu Verifikasi</p>
                        <p class="text-2xl font-bold mt-0.5 text-black">{{ $summary['menunggu'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">dalam antrian</p>
                    </div>
                </div>
                <div class="bg-red-50 border-red-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Perlu Revisi</p>
                        <p class="text-2xl font-bold mt-0.5 text-black">{{ $summary['revisi'] }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">dikembalikan</p>
                    </div>
                </div>
            </div>

            {{-- Data terbaru --}}
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm">
                <div class="p-4 border-b border-blue-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold" style="color:#1E3A5F;">Data Neraca Terbaru Saya</h3>
                    <span class="text-xs text-slate-400">{{ $totalEntri }} entri</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background-color:#F0F7FF;">
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Periode</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Komoditas</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Nilai Neraca</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Tanggal Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($dataTerbaru as $n)
                                @php
                                    $isModel = $n instanceof \App\Models\NeracaPangan;
                                    $periodeLabel = $isModel ? \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode) : $n['periode'];
                                    $komoditasNama = $isModel ? ($n->komoditas->nama ?? '-') : $n['komoditas'];
                                    $nilai = $isModel ? \App\Http\Controllers\Admin\DataNeracaController::hitungNilaiNeraca($n) : $n['nilai'];
                                    $status = $isModel ? $n->status : $n['status'];
                                    $tanggal = $isModel ? optional($n->created_at)->translatedFormat('d M Y') : $n['tanggal'];
                                    $badge = $statusBadge[$status] ?? ['label' => ucfirst($status), 'cls' => 'bg-slate-50 text-slate-600 border-slate-200'];
                                @endphp
                                <tr class="border-t border-blue-50 hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3 font-medium" style="color:#1E3A5F;">{{ $periodeLabel }}</td>
                                    <td class="px-4 py-3">{{ $komoditasNama }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-black">{{ number_format($nilai, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badge['cls'] }}">{{ $badge['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{{ $tanggal }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-8 text-slate-400">Belum ada data yang diinput.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    function toggleNotifDropdown(e) {
        e.stopPropagation();
        var dropdown = document.getElementById('notifDropdown');
        if (!dropdown) return;
        dropdown.classList.toggle('hidden');
    }
    document.addEventListener('click', function (e) {
        var dropdown = document.getElementById('notifDropdown');
        if (!dropdown || dropdown.classList.contains('hidden')) return;
        if (!dropdown.parentElement.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
</body>
</html>