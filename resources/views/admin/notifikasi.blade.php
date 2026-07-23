<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifikasi - S-KOLAK Kota Kediri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

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
    // Nilai default apabila controller belum mengirim data.
    $notifikasi = $notifikasi ?? collect([
        ['id' => 1, 'pesan' => 'Data neraca Telur Ayam Ras – Kota Kediri menunggu verifikasi.', 'waktu' => '10 menit lalu', 'baca' => false, 'tipe' => 'info'],
        ['id' => 2, 'pesan' => 'Data neraca Cabai Rawit – Kota Kediri menunggu verifikasi.',    'waktu' => '25 menit lalu', 'baca' => false, 'tipe' => 'info'],
        ['id' => 3, 'pesan' => 'Data Beras – Feb 2025 – Kota Kediri telah divalidasi.',          'waktu' => '2 jam lalu',    'baca' => true,  'tipe' => 'success'],
        ['id' => 4, 'pesan' => 'Data Kedelai – Mar 2025 dikembalikan untuk revisi.',              'waktu' => '3 jam lalu',    'baca' => true,  'tipe' => 'warning'],
        ['id' => 5, 'pesan' => 'Periode baru Apr 2025 telah dibuka oleh Admin.',                  'waktu' => '1 hari lalu',   'baca' => true,  'tipe' => 'info'],
    ]);

    $notifCount = $notifCount ?? collect($notifikasi)->where('baca', false)->count();
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
    $activeMenu = 'notifikasi';

    $tipeIcon = [
        'success' => ['bg' => 'bg-green-100',  'text' => 'text-green-600',  'path' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'warning' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'path' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/>'],
        'info'    => ['bg' => 'bg-blue-100',   'text' => 'text-blue-600',   'path' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>'],
    ];
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

        <div class="mx-3 mt-3 p-3 rounded-xl sidebar-label" style="background-color:#EFF6FF;">
            <p class="text-xs font-semibold text-blue-600">Admin</p>
            <p class="text-xs font-medium truncate mt-0.5" style="color:#1E3A5F;">Administrator</p>
        </div>

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
            <div class="flex-1">
                <h2 id="pageHeaderTitle" class="text-sm font-bold" style="color:#1E3A5F;">Notifikasi</h2>
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
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white" style="background-color:#2563EB;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main id="pageContent" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5">

            @if (session('status'))
                <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between flex-wrap gap-3">
                <h1 class="text-xl font-bold" style="color:#1E3A5F;">Notifikasi</h1>
                @if ($notifCount > 0)
                    <form method="POST" action="{{ Route::has('admin.notifikasi.baca-semua') ? route('admin.notifikasi.baca-semua') : '#' }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-xs font-semibold text-blue-600 hover:underline">
                            Tandai semua sudah dibaca
                        </button>
                    </form>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-blue-100 shadow-sm divide-y divide-blue-50">
                @forelse ($notifikasi as $n)
                    @php $ic = $tipeIcon[$n['tipe']] ?? $tipeIcon['info']; @endphp
                    <div class="px-5 py-4 flex items-start gap-4 transition-colors {{ !$n['baca'] ? 'bg-blue-50/40 cursor-pointer hover:bg-blue-100' : '' }}"
                        @if (!$n['baca'])
                        onclick="tandaiNotifDibaca(this, '{{ route('admin.notifikasi.baca', $n['id']) }}')"
                        @endif>
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $ic['bg'] }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] {{ $ic['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                {!! $ic['path'] !!}
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $n['pesan'] }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $n['waktu'] }}</p>
                        </div>
                        @if (!$n['baca'])
                            <div class="notif-unread-dot w-2.5 h-2.5 rounded-full bg-blue-500 flex-shrink-0 mt-1.5"></div>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-400 text-sm">Belum ada notifikasi.</div>
                @endforelse
            </div>

        
<!-- ====== Modal & script khusus halaman ini (dipindah ke dalam #pageContent supaya ikut ter-refresh saat navigasi SPA - lihat runScripts() di spa-nav.js) ====== -->
<script>
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
    // Guard: script ini sekarang dieksekusi ulang setiap kali halaman
    // dibuka lewat SPA nav (karena dipindah ke dalam #pageContent), jadi
    // pasang listener document sekali saja supaya tidak menumpuk dobel.
    if (!window.__notifDropdownOutsideClickBound) {
        window.__notifDropdownOutsideClickBound = true;
        document.addEventListener('click', function (e) {
            var dropdown = document.getElementById('notifDropdown');
            if (!dropdown || dropdown.classList.contains('hidden')) return;
            if (!dropdown.parentElement.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
    }
</script>
    
</main>
    </div>
</div>

<script src="{{ asset('js/sidebar-toggle.js') }}"></script>
    <script src="{{ asset('js/spa-nav.js') }}"></script>
</body>
</html>