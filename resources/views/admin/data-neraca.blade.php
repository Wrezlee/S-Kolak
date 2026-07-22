<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Data Neraca Pangan - S-KOLAK Kota Kediri</title>

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
                    'pesan' => $n->pesan,
                    'waktu' => \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $tipe,
                ];
            })->values()
            : collect()
    )));
    $activeMenu = 'data';

    $statusBadge = [
        'valid'    => ['label' => 'Valid',               'cls' => 'bg-green-50 text-green-700 border-green-200'],
        'menunggu' => ['label' => 'Menunggu Verifikasi',  'cls' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'revisi'   => ['label' => 'Perlu Revisi',         'cls' => 'bg-red-50 text-red-700 border-red-200'],
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Data Neraca Pangan</h2>
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
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5">

            @if (session('status'))
                <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold" style="color:#1E3A5F;">Data Neraca Pangan</h1>
                    <p class="text-sm text-slate-500">Seluruh data neraca pangan Kota Kediri</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ Route::has('admin.data.export.excel') ? route('admin.data.export.excel', request()->query()) : '#' }}" class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-blue-200 text-blue-600 text-sm hover:bg-blue-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Excel
                    </a>
                    <a href="{{ Route::has('admin.data.export.pdf') ? route('admin.data.export.pdf', request()->query()) : '#' }}" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold text-white" style="background-color:#2563EB;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[14px] h-[14px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        PDF
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background-color:#F0F7FF;">
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">No</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Periode</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Komoditas</th>
                                <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Nilai Neraca</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Status</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Operator</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Tanggal</th>
                                <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $i => $n)
                                @php
                                    $nilaiNeraca = \App\Http\Controllers\Admin\DataNeracaController::hitungNilaiNeraca($n);
                                    $periodeLabel = \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode);
                                    $badge = $statusBadge[$n->status] ?? ['label' => ucfirst($n->status), 'cls' => 'bg-slate-50 text-slate-600 border-slate-200'];

                                    // json_encode manual (bukan @json()) — directive @json() Blade rusak
                                    // untuk array dengan lebih dari satu key (di-explode berdasarkan koma).
                                    $detailPayload = json_encode([
                                        'komoditas'    => $n->komoditas->nama ?? '-',
                                        'periode'      => $periodeLabel,
                                        'statusLabel'  => $badge['label'],
                                        'statusCls'    => $badge['cls'],
                                        'stokAwal'     => (float) $n->stok_awal,
                                        'produksi'     => (float) $n->produksi,
                                        'masuk'        => (float) $n->masuk,
                                        'keluar'       => (float) $n->keluar,
                                        'kebRT'        => (float) $n->kebutuhan_rumah_tangga,
                                        'kebNonRT'     => (float) $n->kebutuhan_non_rumah_tangga,
                                        'nilaiNeraca'  => (float) $nilaiNeraca,
                                        'operator'     => $n->operator->name ?? '-',
                                        'verifikator'  => $n->verifikator->name ?? '—',
                                        'tanggalInput' => optional($n->created_at)->locale('id')->translatedFormat('d F Y'),
                                        'keterangan'   => $n->keterangan ?? '',
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);

                                    $deletePayload = json_encode([
                                        'id'    => $n->id,
                                        'label' => ($n->komoditas->nama ?? '-') . ' – ' . $periodeLabel,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);
                                @endphp
                                <tr class="border-t border-blue-50 hover:bg-blue-50/20 transition-colors">
                                    <td class="px-3 py-3 text-slate-400">{{ $items->firstItem() + $i }}</td>
                                    <td class="px-3 py-3 font-medium" style="color:#1E3A5F;">{{ $periodeLabel }}</td>
                                    <td class="px-3 py-3" style="color:#1E3A5F;">{{ $n->komoditas->nama ?? '-' }}</td>
                                    <td class="px-3 py-3 text-right font-mono font-bold text-black">{{ number_format($nilaiNeraca, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badge['cls'] }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-500 truncate max-w-[140px]">{{ $n->operator->name ?? '-' }}</td>
                                    <td class="px-3 py-3 text-slate-400">{{ optional($n->created_at)->translatedFormat('d M Y') }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex gap-1.5">
                                            <button type="button" onclick='openDetail({!! $detailPayload !!})'
                                                    class="px-2 py-1 rounded-lg border border-blue-200 text-blue-600 text-xs hover:bg-blue-100 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[11px] h-[11px] inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>Detail
                                            </button>
                                            <button type="button" onclick='openDelete({!! $deletePayload !!})'
                                                    class="px-2 py-1 rounded-lg border border-red-100 text-red-500 text-xs hover:bg-red-100 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[11px] h-[11px] inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data neraca pangan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($items->hasPages())
                    <div class="p-4 border-t border-blue-50">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

{{-- ============ MODAL DETAIL ============ --}}
<div id="modalDetail" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-blue-50">
            <div>
                <h3 id="detailKomoditas" class="text-sm font-bold" style="color:#1E3A5F;"></h3>
                <p id="detailPeriode" class="text-xs text-slate-400"></p>
            </div>
            <div class="flex items-center gap-2">
                <span id="detailStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"></span>
                <button type="button" onclick="document.getElementById('modalDetail').classList.add('hidden')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="rounded-lg bg-blue-50/50 px-3 py-2">
                    <p class="text-slate-400 mb-0.5">Stok Awal</p>
                    <p id="detailStokAwal" class="font-semibold font-mono" style="color:#1E3A5F;"></p>
                </div>
                <div class="rounded-lg bg-blue-50/50 px-3 py-2">
                    <p class="text-slate-400 mb-0.5">Produksi</p>
                    <p id="detailProduksi" class="font-semibold font-mono" style="color:#1E3A5F;"></p>
                </div>
                <div class="rounded-lg bg-blue-50/50 px-3 py-2">
                    <p class="text-slate-400 mb-0.5">Pemasukan</p>
                    <p id="detailMasuk" class="font-semibold font-mono" style="color:#1E3A5F;"></p>
                </div>
                <div class="rounded-lg bg-blue-50/50 px-3 py-2">
                    <p class="text-slate-400 mb-0.5">Pengeluaran</p>
                    <p id="detailKeluar" class="font-semibold font-mono" style="color:#1E3A5F;"></p>
                </div>
                <div class="rounded-lg bg-blue-50/50 px-3 py-2">
                    <p class="text-slate-400 mb-0.5">Keb. Rumah Tangga</p>
                    <p id="detailKebRT" class="font-semibold font-mono" style="color:#1E3A5F;"></p>
                </div>
                <div class="rounded-lg bg-blue-50/50 px-3 py-2">
                    <p class="text-slate-400 mb-0.5">Keb. Non-RT</p>
                    <p id="detailKebNonRT" class="font-semibold font-mono" style="color:#1E3A5F;"></p>
                </div>
            </div>

            <div id="detailNilaiBox" class="rounded-xl px-4 py-3 border-2">
                <p class="text-xs text-slate-500 mb-1">Nilai Neraca</p>
                <p id="detailNilaiNeraca" class="text-xl font-bold font-mono text-black"></p>
            </div>

            <div class="text-xs space-y-1 text-slate-500">
                <div class="flex justify-between"><span>Operator</span><span id="detailOperator" class="font-medium text-slate-700"></span></div>
                <div class="flex justify-between"><span>Verifikator</span><span id="detailVerifikator" class="font-medium text-slate-700"></span></div>
                <div class="flex justify-between"><span>Tanggal Input</span><span id="detailTanggal" class="font-medium text-slate-700"></span></div>
            </div>

            <div id="detailKeteranganBox" class="hidden rounded-xl p-3 bg-slate-50 border border-slate-100">
                <p class="text-xs text-slate-400 mb-1">Keterangan</p>
                <p id="detailKeterangan" class="text-sm text-slate-600"></p>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL HAPUS ============ --}}
<div id="modalDelete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs p-6 space-y-4">
        <h3 class="text-sm font-bold text-red-600">Hapus Data Neraca</h3>
        <p class="text-xs text-slate-600">Yakin ingin menghapus data <strong id="deleteLabel"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="flex gap-2 justify-end">
                <button type="button" onclick="document.getElementById('modalDelete').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-xs text-slate-500 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-xs text-white font-semibold bg-red-500 hover:bg-red-600">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    const dataBaseUrl = @json(url('admin/data'));

    function fmtNumber(n) {
        return Number(n).toLocaleString('id-ID');
    }

    function openDetail(d) {
        document.getElementById('detailKomoditas').textContent = d.komoditas;
        document.getElementById('detailPeriode').textContent = d.periode;

        const statusBadge = document.getElementById('detailStatusBadge');
        statusBadge.textContent = d.statusLabel;
        statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' + d.statusCls;

        document.getElementById('detailStokAwal').textContent = fmtNumber(d.stokAwal);
        document.getElementById('detailProduksi').textContent = fmtNumber(d.produksi);
        document.getElementById('detailMasuk').textContent = fmtNumber(d.masuk);
        document.getElementById('detailKeluar').textContent = fmtNumber(d.keluar);
        document.getElementById('detailKebRT').textContent = fmtNumber(d.kebRT);
        document.getElementById('detailKebNonRT').textContent = fmtNumber(d.kebNonRT);
        document.getElementById('detailNilaiNeraca').textContent = fmtNumber(d.nilaiNeraca);

        const nilaiBox = document.getElementById('detailNilaiBox');
        if (d.nilaiNeraca >= 0) {
            nilaiBox.className = 'rounded-xl px-4 py-3 border-2 border-green-200 bg-green-50';
        } else {
            nilaiBox.className = 'rounded-xl px-4 py-3 border-2 border-red-200 bg-red-50';
        }

        document.getElementById('detailOperator').textContent = d.operator;
        document.getElementById('detailVerifikator').textContent = d.verifikator;
        document.getElementById('detailTanggal').textContent = d.tanggalInput;

        const ketBox = document.getElementById('detailKeteranganBox');
        if (d.keterangan) {
            document.getElementById('detailKeterangan').textContent = d.keterangan;
            ketBox.classList.remove('hidden');
        } else {
            ketBox.classList.add('hidden');
        }

        document.getElementById('modalDetail').classList.remove('hidden');
    }

    function openDelete(item) {
        document.getElementById('deleteLabel').textContent = item.label;
        document.getElementById('deleteForm').action = dataBaseUrl + '/' + item.id;
        document.getElementById('modalDelete').classList.remove('hidden');
    }
</script>

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
    document.addEventListener('click', function (e) {
        var dropdown = document.getElementById('notifDropdown');
        if (!dropdown || dropdown.classList.contains('hidden')) return;
        if (!dropdown.parentElement.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>
</body>
</html>