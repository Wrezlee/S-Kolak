<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - S-KOLAK Kota Kediri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #appSidebar { transition: width .2s ease; }
        #appSidebar.sidebar-collapsed { width: 76px !important; }
        #appSidebar.sidebar-collapsed .sidebar-label { display: none !important; }
        #appSidebar.sidebar-collapsed .p-4.border-b.border-blue-50 { justify-content: center; }
        #appSidebar.sidebar-collapsed nav a { justify-content: center; }
        #appSidebar.sidebar-collapsed .p-3.border-t.border-blue-50 button { justify-content: center; }
        #appSidebar.sidebar-collapsed #sidebarToggleIcon { transform: rotate(180deg); }
    </style>
</head>
<body class="h-screen overflow-hidden" style="background-color:#F5F9FF;">

@php
    // Nilai default apabila controller belum mengirim data — hapus/ganti
    // setelah controller Anda menyediakan variabel-variabel ini.
    $summary = $summary ?? [
        'total'    => 37,
        'valid'    => 33,
        'menunggu' => 2,
        'revisi'   => 1,
    ];

    $trendLabels = $trendLabels ?? ['Agt 2024','Sep 2024','Okt 2024','Nov 2024','Des 2024','Jan 2025','Feb 2025','Mar 2025','Apr 2025'];
    $trendValues = $trendValues ?? [780, 845, 920, 880, 960, 782, 989, 1040, 1125];

    $statusPie = $statusPie ?? [
        ['label' => 'Valid',               'value' => $summary['valid'],    'color' => '#16A34A'],
        ['label' => 'Menunggu Verifikasi', 'value' => $summary['menunggu'], 'color' => '#EA580C'],
        ['label' => 'Perlu Revisi',        'value' => $summary['revisi'],   'color' => '#DC2626'],
    ];

    $stokBars = $stokBars ?? [
        ['name' => 'Beras',          'nilai' => 577],
        ['name' => 'Jagung',         'nilai' => 220],
        ['name' => 'Gula Pasir',     'nilai' => 97],
        ['name' => 'Minyak Goreng',  'nilai' => 124],
        ['name' => 'Bawang Merah',   'nilai' => 37],
        ['name' => 'Cabai Merah',    'nilai' => 30],
        ['name' => 'Telur Ayam',     'nilai' => 70],
        ['name' => 'Kedelai',        'nilai' => 80],
    ];
    $stokMax = collect($stokBars)->max('nilai') ?: 1;
    $stokColors = ['#2563EB','#1D4ED8','#60A5FA','#1E40AF','#3B82F6','#93C5FD','#2563EB','#60A5FA'];

    $aktivitas = $aktivitas ?? [
        ['tipe' => 'info',    'pesan' => 'Data neraca Telur Ayam Ras – Kota Kediri menunggu verifikasi.', 'waktu' => '10 menit lalu', 'baca' => false],
        ['tipe' => 'info',    'pesan' => 'Data neraca Cabai Rawit – Kota Kediri menunggu verifikasi.',    'waktu' => '25 menit lalu', 'baca' => false],
        ['tipe' => 'success', 'pesan' => 'Data Beras – Feb 2025 – Kota Kediri telah divalidasi.',          'waktu' => '2 jam lalu',    'baca' => true],
        ['tipe' => 'warning', 'pesan' => 'Data Kedelai – Mar 2025 dikembalikan untuk revisi.',              'waktu' => '3 jam lalu',    'baca' => true],
        ['tipe' => 'info',    'pesan' => 'Periode baru Apr 2025 telah dibuka oleh Admin.',                  'waktu' => '1 hari lalu',   'baca' => true],
    ];

    $notifCount = $notifCount ?? 2;
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
                    'id'    => $n->id,
                    'pesan' => $n->pesan,
                    'waktu' => \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $tipe,
                ];
            })->values()
            : collect()
    )));
    $activeMenu = $activeMenu ?? 'dashboard';
@endphp

<div class="flex h-screen overflow-hidden">

    {{-- ============ SIDEBAR ============ --}}
    <aside id="appSidebar" class="hidden md:flex flex-col flex-shrink-0 relative border-r border-blue-100 bg-white" style="width:240px;">
        <script>(function(){var s=document.getElementById('appSidebar');if(s&&localStorage.getItem('skolak_sidebar_collapsed')==='1'){s.classList.add('sidebar-collapsed');}})();</script>
        <button type="button" onclick="toggleSidebar()" title="Ciutkan/luaskan sidebar"
            class="hidden md:flex absolute -right-3 top-8 w-6 h-6 rounded-full bg-white border border-blue-200 shadow-md items-center justify-center text-slate-400 hover:text-blue-600 hover:border-blue-400 transition-all z-20">
            <svg id="sidebarToggleIcon" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        {{-- Logo --}}
        <div class="p-4 border-b border-blue-50 flex items-center gap-3">
            @if (file_exists(public_path('images/logo-kediri.png')))
                <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri" class="w-9 h-9 object-contain flex-shrink-0">
            @else
                <div class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">SK</div>
            @endif
            <div class="overflow-hidden sidebar-label">
                <p class="text-sm font-bold truncate" style="color:#1E3A5F;">S-KOLAK</p>
                <p class="text-xs text-slate-400 truncate">Kota Kediri</p>
            </div>
        </div>

        {{-- Role (tanpa nama, hanya label "Administrator") --}}
        <div class="mx-3 mt-3 p-3 rounded-xl sidebar-label" style="background-color:#EFF6FF;">
            <p class="text-xs font-semibold text-blue-600">Admin</p>
            <p class="text-xs font-medium truncate mt-0.5" style="color:#1E3A5F;">Administrator</p>
        </div>

        {{-- Menu --}}
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto mt-2">
            @php
                $menuItems = [
                    ['key' => 'dashboard',  'label' => 'Dashboard',          'route' => 'admin.dashboard',  'badge' => null],
                    ['key' => 'users',      'label' => 'Manajemen Pengguna', 'route' => 'admin.users',      'badge' => null],
                    ['key' => 'komoditas',  'label' => 'Master Komoditas',   'route' => 'admin.komoditas',  'badge' => null],
                    ['key' => 'data',       'label' => 'Data Neraca Pangan', 'route' => 'admin.data',       'badge' => null],
                    ['key' => 'laporan',    'label' => 'Laporan',            'route' => 'admin.laporan',    'badge' => null],
                    ['key' => 'notifikasi', 'label' => 'Notifikasi',         'route' => 'admin.notifikasi', 'badge' => $notifCount],
                ];
                $menuIcons = [
                    'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                    'users'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
                    'komoditas'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
                    'data'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>',
                    'laporan'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
                    'notifikasi' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
                ];
            @endphp

            @foreach ($menuItems as $item)
                @php $isActive = $activeMenu === $item['key']; @endphp
                <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                   title="{{ $item['label'] }}"
                   class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isActive ? '' : 'text-slate-600 hover:bg-blue-100 hover:text-blue-700 hover:shadow-sm hover:translate-x-0.5' }}"
                   style="{{ $isActive ? 'background-color:#2563EB; color:white; font-weight:600;' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        {!! $menuIcons[$item['key']] !!}
                    </svg>
                    <span class="truncate flex-1 text-left sidebar-label">{{ $item['label'] }}</span>
                    @if ($item['badge'])
                        <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold sidebar-label notif-count-badge"
                              style="{{ $isActive ? 'background-color:rgba(255,255,255,0.3); color:white;' : 'background-color:#FEF3C7; color:#B45309;' }}">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Logout --}}
        <div class="p-3 border-t border-blue-50">
            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-red-500 hover:bg-red-100 hover:text-red-700 hover:shadow-sm hover:translate-x-0.5 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" transform="scale(-1,1) translate(-24,0)"/>
                    </svg>
                    <span class="sidebar-label">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Topbar --}}
        <header class="h-14 border-b border-blue-100 bg-white flex items-center px-4 gap-3 flex-shrink-0 shadow-sm">
            <button class="md:hidden" onclick="document.getElementById('mobileSidebar').classList.remove('hidden')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="#1E3A5F" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <div class="flex-1">
                <h2 id="pageHeaderTitle" class="text-sm font-bold" style="color:#1E3A5F;">Dashboard Admin</h2>
                <p class="text-xs text-slate-400">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <button type="button" onclick="toggleNotifDropdown(event)" class="relative w-9 h-9 flex items-center justify-center rounded-xl hover:bg-blue-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="#1E3A5F" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        @if ($notifCount > 0)
                            <span id="notifBellDot" class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-orange-500"></span>
                        @endif
                    </button>

                    {{-- Popup notifikasi singkat --}}
                    <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-xl border border-blue-100 shadow-lg z-50 overflow-hidden">
                        <div class="px-4 py-3 border-b border-blue-50 flex items-center justify-between">
                            <h4 class="text-sm font-bold" style="color:#1E3A5F;">Notifikasi</h4>
                            @if ($notifCount > 0)
                                <span class="text-xs px-1.5 py-0.5 rounded-full font-bold" style="background-color:#FEF3C7; color:#B45309;"><span class="notif-count-badge">{{ $notifCount }}</span> baru</span>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-blue-50">
                            @forelse ($notifDropdownItems as $n)
                                <div class="px-4 py-3 flex items-start gap-3 transition-colors {{ !($n['baca'] ?? true) ? 'bg-blue-50/40 cursor-pointer hover:bg-blue-100' : '' }}"
                                     @if (!($n['baca'] ?? true) && !empty($n['id']))
                                     onclick="tandaiNotifDibaca(this, '{{ route('admin.notifikasi.baca', $n['id']) }}')"
                                     @endif>
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
                                        <div class="notif-unread-dot w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></div>
                                    @endif
                                </div>
                            @empty
                                <div class="px-4 py-6 text-center text-xs text-slate-400">Tidak ada notifikasi.</div>
                            @endforelse
                        </div>
                        <a href="{{ Route::has('admin.notifikasi') ? route('admin.notifikasi') : '#' }}" class="block text-center text-xs font-semibold text-blue-600 hover:bg-blue-100 py-2.5 border-t border-blue-50 transition-colors">Lihat Semua</a>
                    </div>
                </div>
                {{-- Avatar generik, tanpa inisial nama --}}
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white" style="background-color:#2563EB;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main id="pageContent" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">

            <div>
                <h1 class="text-xl font-bold" style="color:#1E3A5F;">Dashboard Admin</h1>
                <p class="text-sm text-slate-500">Ringkasan sistem neraca pangan Kota Kediri</p>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                @php
                    $statCards = [
                        ['key' => 'total',    'label' => 'Total Entri Neraca',  'value' => $summary['total'],    'color' => 'blue',   'icon' => 'database'],
                        ['key' => 'valid',    'label' => 'Data Valid',           'value' => $summary['valid'],    'color' => 'green',  'icon' => 'check'],
                        ['key' => 'menunggu', 'label' => 'Menunggu Verifikasi',  'value' => $summary['menunggu'], 'color' => 'orange', 'icon' => 'clock'],
                        ['key' => 'revisi',   'label' => 'Perlu Revisi',         'value' => $summary['revisi'],   'color' => 'red',    'icon' => 'warning'],
                    ];
                    $statColorMap = [
                        'blue'   => ['card' => 'bg-blue-50 border-blue-200',     'icon' => 'bg-blue-100 text-blue-600',   'link' => 'text-blue-600'],
                        'green'  => ['card' => 'bg-green-50 border-green-200',   'icon' => 'bg-green-100 text-green-600', 'link' => 'text-green-600'],
                        'orange' => ['card' => 'bg-yellow-50 border-yellow-200', 'icon' => 'bg-yellow-100 text-yellow-600','link' => 'text-yellow-600'],
                        'red'    => ['card' => 'bg-red-50 border-red-200',       'icon' => 'bg-red-100 text-red-600',     'link' => 'text-red-600'],
                    ];
                    $statIcons = [
                        'database' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>',
                        'check'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'clock'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'warning'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/>',
                    ];

                    // Kelompokkan cardItems per status supaya tiap card bisa
                    // menampilkan daftar detail miliknya sendiri di modal,
                    // dengan pola yang sama seperti dashboard publik.
                    $cardItemsCollection = collect($cardItems ?? []);
                    $modalData = [
                        'total'    => ['title' => 'Semua Data Neraca',       'items' => $cardItemsCollection->values()],
                        'valid'    => ['title' => 'Data Valid',              'items' => $cardItemsCollection->where('status', 'valid')->values()],
                        'menunggu' => ['title' => 'Data Menunggu Verifikasi','items' => $cardItemsCollection->where('status', 'menunggu')->values()],
                        'revisi'   => ['title' => 'Data Perlu Revisi',       'items' => $cardItemsCollection->where('status', 'revisi')->values()],
                    ];
                    $statusBadgeMap = [
                        'valid'    => 'bg-green-50 text-green-700 border border-green-200',
                        'menunggu' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                        'revisi'   => 'bg-red-50 text-red-700 border border-red-200',
                    ];
                    $statusLabelMap = ['valid' => 'Valid', 'menunggu' => 'Menunggu', 'revisi' => 'Revisi'];
                @endphp
                @foreach ($statCards as $card)
                    @php $sc = $statColorMap[$card['color']]; @endphp
                    <button type="button" onclick="openModal('{{ $card['key'] }}')"
                            class="text-left w-full {{ $sc['card'] }} rounded-xl border shadow-sm p-5 flex items-start gap-4 hover:shadow-md hover:-translate-y-0.5 hover:brightness-100 transition-all">
                        <div class="p-3 rounded-xl {{ $sc['icon'] }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                {!! $statIcons[$card['icon']] !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">{{ $card['label'] }}</p>
                            <p class="text-2xl font-bold mt-0.5 text-black">{{ $card['value'] }}</p>
                            <span class="mt-1 inline-flex items-center gap-1 text-xs font-semibold {{ $sc['link'] }}">
                                Lihat detail →
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>

            {{-- Modal detail per stat card --}}
            @foreach ($modalData as $key => $modal)
                <div id="modal-{{ $key }}" class="fixed inset-0 z-50 items-center justify-center p-4" style="background-color:rgba(0,0,0,0.4); display:none;" onclick="closeModal('{{ $key }}')">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                            <p class="font-semibold" style="color:#1E3A5F;">{{ $modal['title'] }}</p>
                            <button type="button" onclick="closeModal('{{ $key }}')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="overflow-y-auto flex-1 p-4 space-y-2">
                            @forelse ($modal['items'] as $item)
                                <div class="flex items-center justify-between px-4 py-3 rounded-xl hover:bg-slate-50">
                                    <div>
                                        <p class="text-sm font-semibold">{{ $item['komoditas'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $item['periode'] }} · Neraca: {{ number_format($item['nilai_neraca'], 0, ',', '.') }}</p>
                                    </div>
                                    <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusBadgeMap[$item['status']] ?? 'bg-slate-50 text-slate-600' }}">
                                        ● {{ $statusLabelMap[$item['status']] ?? $item['status'] }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400 text-center py-6">Tidak ada data.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Tren Nilai Neraca Pangan – Kota Kediri</h3>
                    <canvas id="trendChart" height="90"></canvas>
                </div>

                <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Status Neraca Pangan</h3>
                    <div class="space-y-3 pt-2">
                        @php $statusTotal = collect($statusPie)->sum('value') ?: 1; @endphp
                        @foreach ($statusPie as $s)
                            <div class="flex items-center gap-3">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color:{{ $s['color'] }};"></span>
                                <span class="text-xs text-slate-500 flex-1">{{ $s['label'] }}</span>
                                <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full" style="width:{{ ($s['value']/$statusTotal)*100 }}%; background-color:{{ $s['color'] }};"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-700 w-5 text-right">{{ $s['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Stok per komoditas --}}
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Ketersediaan Stok per Komoditas – Data Valid</h3>
                <div class="space-y-2">
                    @foreach ($stokBars as $i => $d)
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-500 w-28 shrink-0 truncate" title="{{ $d['name'] }}">{{ $d['name'] }}</span>
                            <div class="flex-1 bg-slate-100 rounded-full h-4 overflow-hidden">
                                <div class="h-full rounded-full flex items-center justify-end pr-2"
                                     style="width:{{ ($d['nilai']/$stokMax)*100 }}%; background-color:{{ $stokColors[$i % count($stokColors)] }}; min-width:{{ $d['nilai'] > 0 ? '2.5rem' : '0' }};">
                                    @if ($d['nilai'] > 0)
                                        <span class="text-white text-xs font-semibold">{{ $d['nilai'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Aktivitas terbaru --}}
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm">
                <div class="p-4 border-b border-blue-50 flex items-center justify-between">
                    <h3 class="text-sm font-bold" style="color:#1E3A5F;">Aktivitas Terbaru</h3>
                    <a href="{{ Route::has('admin.notifikasi') ? route('admin.notifikasi') : '#' }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
                </div>
                <div class="divide-y divide-blue-50">
                    @foreach ($aktivitas as $n)
                        @php
                            $iconBg = $n['tipe'] === 'success' ? 'bg-green-100' : ($n['tipe'] === 'warning' ? 'bg-orange-100' : 'bg-blue-100');
                        @endphp
                        <div class="px-4 py-3 flex items-start gap-3 {{ !$n['baca'] ? 'bg-blue-50/40' : '' }}">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 {{ $iconBg }}">
                                @if ($n['tipe'] === 'success')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px] text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif ($n['tipe'] === 'warning')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px] text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px] text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-slate-700">{{ $n['pesan'] }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $n['waktu'] }}</p>
                            </div>
                            @if (!$n['baca'])
                                <div class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </main>
    </div>
</div>

{{-- Mobile sidebar overlay --}}
<div id="mobileSidebar" class="md:hidden fixed inset-0 z-50 hidden">
    <div class="w-[240px] h-full bg-white overflow-y-auto">
        <div class="p-4 border-b border-blue-50 flex items-center justify-between">
            <span class="text-sm font-bold" style="color:#1E3A5F;">S-KOLAK</span>
            <button onclick="document.getElementById('mobileSidebar').classList.add('hidden')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="#1E3A5F" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="p-3 space-y-0.5">
            @foreach ($menuItems as $item)
                <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}" class="block px-3 py-2.5 rounded-xl text-sm text-slate-600">{{ $item['label'] }}</a>
            @endforeach
        </nav>
    </div>
    <div class="flex-1 bg-black/30" onclick="document.getElementById('mobileSidebar').classList.add('hidden')" style="width:calc(100% - 240px); position:absolute; right:0; top:0; height:100%;"></div>
</div>

<script>
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [{
                data: @json($trendValues),
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37, 99, 235, 0.12)',
                fill: true,
                tension: 0,
                pointRadius: 4,
                pointBackgroundColor: '#2563EB',
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#EFF6FF' } },
                x: { grid: { display: false } }
            }
        }
    });
</script>

<script>
    function openModal(key) {
        var el = document.getElementById('modal-' + key);
        if (el) el.style.display = 'flex';
    }
    function closeModal(key) {
        var el = document.getElementById('modal-' + key);
        if (el) el.style.display = 'none';
    }

    function tandaiNotifDibaca(el, url) {
        if (!url || el.dataset.done === '1') return;
        el.dataset.done = '1';
        el.classList.remove('bg-blue-50/40', 'cursor-pointer', 'hover:bg-blue-100');
        var dot = el.querySelector('.notif-unread-dot');
        if (dot) dot.remove();
        document.querySelectorAll('.notif-count-badge').forEach(function (badge) {
            var n = parseInt(badge.textContent, 10);
            if (!isNaN(n) && n > 1) {
                badge.textContent = n - 1;
            } else if (!isNaN(n)) {
                var wrap = badge.closest('span.sidebar-label, span:not(.notif-count-badge)') || badge.parentElement;
                if (wrap && wrap !== badge) { wrap.remove(); } else { badge.remove(); }
            }
        });
        if (document.querySelectorAll('.notif-unread-dot').length === 0) {
            var bellDot = document.getElementById('notifBellDot');
            if (bellDot) bellDot.remove();
        }
        var token = document.querySelector('meta[name="csrf-token"]');
        fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': token ? token.content : '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).catch(function () {});
    }

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
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>
    <script src="{{ asset('js/spa-nav.js') }}"></script>
</body>
</html>