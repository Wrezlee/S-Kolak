<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Neraca Pangan - S-KOLAK Kota Kediri</title>

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
    // Nilai default apabila controller belum mengirim data.
    $filters = $filters ?? ['tahun_awal' => '', 'bulan_awal' => '', 'tahun_akhir' => '', 'bulan_akhir' => '', 'status' => ''];

    $ringkasan = $ringkasan ?? ['totalValid' => 33, 'surplus' => 29, 'defisit' => 4, 'totalEntri' => 37];

    $rekapKomoditas = $rekapKomoditas ?? collect([
        ['nama' => 'Bawang Merah',    'total' => 3, 'valid' => 3],
        ['nama' => 'Bawang Putih',    'total' => 3, 'valid' => 3],
        ['nama' => 'Beras',           'total' => 5, 'valid' => 5],
        ['nama' => 'Cabai Besar',     'total' => 3, 'valid' => 2],
        ['nama' => 'Cabai Rawit',     'total' => 3, 'valid' => 3],
        ['nama' => 'Daging Ayam Ras', 'total' => 3, 'valid' => 3],
        ['nama' => 'Daging Sapi',     'total' => 3, 'valid' => 3],
        ['nama' => 'Gula Konsumsi',   'total' => 3, 'valid' => 2],
        ['nama' => 'Jagung',          'total' => 3, 'valid' => 3],
        ['nama' => 'Kedelai',         'total' => 2, 'valid' => 1],
        ['nama' => 'Minyak Goreng',   'total' => 3, 'valid' => 3],
        ['nama' => 'Telur Ayam Ras',  'total' => 3, 'valid' => 2],
    ]);

    $nilaiValidTable = $nilaiValidTable ?? collect([]);
    $detail = $detail ?? null;

    $entriPerKomoditas = $entriPerKomoditas ?? [
        'labels'   => $rekapKomoditas->pluck('nama')->all(),
        'valid'    => $rekapKomoditas->pluck('valid')->all(),
        'menunggu' => array_fill(0, $rekapKomoditas->count(), 0),
        'revisi'   => array_fill(0, $rekapKomoditas->count(), 0),
    ];

    $trenBulanan = $trenBulanan ?? [
        'labels' => ['Agt 2024','Sep 2024','Okt 2024','Nov 2024','Des 2024','Jan 2025','Feb 2025','Mar 2025','Apr 2025'],
        'nilai'  => [780, 845, 920, 880, 960, 782, 989, 1040, 1125],
    ];

    $perbandinganNilai = $perbandinganNilai ?? collect([
        ['nama' => 'Beras', 'nilai' => 577], ['nama' => 'Jagung', 'nilai' => 220],
        ['nama' => 'Minyak Goreng', 'nilai' => 124], ['nama' => 'Kedelai', 'nilai' => 80],
        ['nama' => 'Telur Ayam Ras', 'nilai' => 70], ['nama' => 'Bawang Merah', 'nilai' => 37],
        ['nama' => 'Cabai Rawit', 'nilai' => 30], ['nama' => 'Gula Konsumsi', 'nilai' => 97],
    ]);

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
    $activeMenu = 'laporan';

    $tahunList = ['2025', '2026'];
    $bulanList = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

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
                   class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all"
                   style="{{ $isActive ? 'background-color:#2563EB; color:white; font-weight:600;' : 'color:#475569;' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[17px] h-[17px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        {!! $menuIcons[$item['key']] !!}
                    </svg>
                    <span class="truncate flex-1 text-left sidebar-label">{{ $item['label'] }}</span>
                    @if ($item['badge'])
                        <span class="ml-auto text-xs px-1.5 py-0.5 rounded-full font-bold sidebar-label"
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Laporan</h2>
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
                        <a href="{{ Route::has('admin.notifikasi') ? route('admin.notifikasi') : '#' }}" class="block text-center text-xs font-semibold text-blue-600 hover:bg-blue-50 py-2.5 border-t border-blue-50 transition-colors">Lihat Semua</a>
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

            {{-- Header + tombol ekspor --}}
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold" style="color:#1E3A5F;">Laporan Neraca Pangan</h1>
                    <p class="text-sm text-slate-500">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.laporan.export.excel', request()->query()) }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold border-2 border-blue-300 text-blue-700 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Ekspor Excel
                    </a>
                    <a href="{{ route('admin.laporan.cetak', request()->query()) }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-sm" style="background-color:#2563EB;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Cetak PDF
                    </a>
                </div>
            </div>

            {{-- Filter --}}
            <form id="filterForm" method="GET" action="{{ route('admin.laporan') }}" class="bg-white rounded-xl border border-blue-100 shadow-sm p-4">
                <input type="hidden" name="tab" id="filterTabInput" value="ringkasan">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                    <span class="text-xs font-semibold text-slate-600">Filter Laporan</span>
                </div>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400">Tahun Awal</label>
                        <select name="tahun_awal" class="px-3 py-2 rounded-lg border border-blue-100 text-xs text-slate-700 bg-white outline-none focus:border-blue-400 min-w-[100px]">
                            <option value="">Semua</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t }}" @selected($filters['tahun_awal'] == $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400">Bulan Awal</label>
                        <select name="bulan_awal" class="px-3 py-2 rounded-lg border border-blue-100 text-xs text-slate-700 bg-white outline-none focus:border-blue-400 min-w-[100px]">
                            <option value="">Semua</option>
                            @foreach ($bulanList as $b)
                                <option value="{{ $b }}" @selected($filters['bulan_awal'] == $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400">Tahun Akhir</label>
                        <select name="tahun_akhir" class="px-3 py-2 rounded-lg border border-blue-100 text-xs text-slate-700 bg-white outline-none focus:border-blue-400 min-w-[100px]">
                            <option value="">Semua</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t }}" @selected($filters['tahun_akhir'] == $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400">Bulan Akhir</label>
                        <select name="bulan_akhir" class="px-3 py-2 rounded-lg border border-blue-100 text-xs text-slate-700 bg-white outline-none focus:border-blue-400 min-w-[100px]">
                            <option value="">Semua</option>
                            @foreach ($bulanList as $b)
                                <option value="{{ $b }}" @selected($filters['bulan_akhir'] == $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs text-slate-400">Status</label>
                        <select name="status" class="px-3 py-2 rounded-lg border border-blue-100 text-xs text-slate-700 bg-white outline-none focus:border-blue-400 min-w-[110px]">
                            <option value="">Semua</option>
                            <option value="valid"    @selected($filters['status'] == 'valid')>Valid</option>
                            <option value="menunggu" @selected($filters['status'] == 'menunggu')>Menunggu</option>
                            <option value="revisi"   @selected($filters['status'] == 'revisi')>Revisi</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs text-white font-semibold flex items-center gap-1.5 hover:shadow-md transition-all" style="background-color:#2563EB;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                        Terapkan Filter
                    </button>
                    <a href="{{ route('admin.laporan') }}" class="px-3 py-2 rounded-lg border border-slate-200 text-xs text-slate-500 hover:bg-slate-50 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- Tabs --}}
            <div class="flex gap-1 bg-white rounded-xl border border-blue-100 shadow-sm p-1" id="tabNav">
                <button type="button" data-tab="ringkasan" class="tab-btn flex-1 py-2 rounded-lg text-xs font-semibold transition-all text-white shadow-sm" style="background-color:#2563EB;">
                    Ringkasan Eksekutif
                </button>
                <button type="button" data-tab="detail" class="tab-btn flex-1 py-2 rounded-lg text-xs font-semibold transition-all text-slate-500 hover:text-blue-600">
                    Laporan Detail
                </button>
                <button type="button" data-tab="grafik" class="tab-btn flex-1 py-2 rounded-lg text-xs font-semibold transition-all text-slate-500 hover:text-blue-600">
                    Grafik &amp; Visualisasi
                </button>
            </div>

            {{-- ===================== TAB: RINGKASAN EKSEKUTIF ===================== --}}
            <div id="tab-ringkasan" class="tab-panel space-y-4">

                {{-- KPI cards --}}
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-green-50 border-green-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                        <div class="p-3 rounded-xl bg-green-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Data Valid</p>
                            <p class="text-2xl font-bold mt-0.5 text-black">{{ $ringkasan['totalValid'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">siap publikasi</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border-blue-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                        <div class="p-3 rounded-xl bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Komoditas Surplus</p>
                            <p class="text-2xl font-bold mt-0.5 text-black">{{ $ringkasan['surplus'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">neraca positif</p>
                        </div>
                    </div>
                    <div class="bg-red-50 border-red-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                        <div class="p-3 rounded-xl bg-red-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Komoditas Defisit</p>
                            <p class="text-2xl font-bold mt-0.5 text-black">{{ $ringkasan['defisit'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">neraca negatif</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border-blue-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                        <div class="p-3 rounded-xl bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Entri Tercatat</p>
                            <p class="text-2xl font-bold mt-0.5 text-black">{{ $ringkasan['totalEntri'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">semua status</p>
                        </div>
                    </div>
                </div>

                {{-- Rekap per komoditas --}}
                <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Rekap per Komoditas – Kota Kediri</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @forelse ($rekapKomoditas as $k)
                            <div class="rounded-xl p-4 border border-blue-100">
                                <div class="flex items-center gap-2 mb-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                                    <h4 class="text-xs font-bold leading-tight" style="color:#1E3A5F;">{{ $k['nama'] }}</h4>
                                </div>
                                <div class="space-y-1 text-xs">
                                    <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="font-bold text-slate-700">{{ $k['total'] }}</span></div>
                                    <div class="flex justify-between"><span class="text-slate-500">Valid</span><span class="font-bold text-black">{{ $k['valid'] }}</span></div>
                                </div>
                                <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-blue-600"
                                         style="width:{{ $k['total'] > 0 ? ($k['valid'] / $k['total']) * 100 : 0 }}%;"></div>
                                </div>
                            </div>
                        @empty
                            <p class="col-span-full text-center text-slate-400 text-sm py-6">Belum ada komoditas terdaftar.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Nilai neraca valid --}}
                <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Nilai Neraca Komoditas Valid – Kota Kediri</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr style="background-color:#F0F7FF;">
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600">Periode</th>
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600">Komoditas</th>
                                    <th class="px-4 py-2.5 text-right font-semibold text-slate-600">Nilai Neraca</th>
                                    <th class="px-4 py-2.5 text-left font-semibold text-slate-600">Kondisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($nilaiValidTable as $n)
                                    <tr class="border-t border-blue-50 hover:bg-blue-50/20">
                                        <td class="px-4 py-2.5 text-slate-500">{{ $n['periode'] }}</td>
                                        <td class="px-4 py-2.5 font-medium" style="color:#1E3A5F;">{{ $n['komoditas'] }}</td>
                                        <td class="px-4 py-2.5 text-right font-mono font-bold text-black">{{ number_format($n['nilai'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium border {{ $n['surplus'] ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-600 border-red-200' }}">
                                                {{ $n['surplus'] ? '▲ Surplus' : '▼ Defisit' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data valid.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ===================== TAB: LAPORAN DETAIL ===================== --}}
            <div id="tab-detail" class="tab-panel space-y-4 hidden">
                <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-blue-50">
                        <p class="text-xs text-slate-500">
                            @if ($detail)
                                Menampilkan {{ $detail->count() }} dari {{ $detail->total() }} entri sesuai filter
                            @else
                                Menampilkan seluruh entri sesuai filter
                            @endif
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr style="background-color:#F0F7FF;">
                                    <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">No</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Periode</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Komoditas</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Stok Awal</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Produksi</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Masuk</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Keluar</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Keb. RT</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Keb. Non-RT</th>
                                    <th class="px-3 py-3 text-right font-semibold text-slate-600 whitespace-nowrap">Nilai Neraca</th>
                                    <th class="px-3 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (($detail->items() ?? []) as $i => $n)
                                    @php
                                        $nilai = \App\Http\Controllers\Admin\DataNeracaController::hitungNilaiNeraca($n);
                                        $periodeLabel = \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode);
                                        $badge = $statusBadge[$n->status] ?? ['label' => ucfirst($n->status), 'cls' => 'bg-slate-50 text-slate-600 border-slate-200'];
                                    @endphp
                                    <tr class="border-t border-blue-50 hover:bg-blue-50/20">
                                        <td class="px-3 py-3 text-slate-400">{{ $detail->firstItem() + $i }}</td>
                                        <td class="px-3 py-3 font-medium" style="color:#1E3A5F;">{{ $periodeLabel }}</td>
                                        <td class="px-3 py-3">{{ $n->komoditas->nama ?? '-' }}</td>
                                        <td class="px-3 py-3 text-right font-mono">{{ number_format($n->stok_awal, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-mono">{{ number_format($n->produksi, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-mono">{{ number_format($n->masuk, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-mono">{{ number_format($n->keluar, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-mono">{{ number_format($n->kebutuhan_rumah_tangga, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-mono">{{ number_format($n->kebutuhan_non_rumah_tangga, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3 text-right font-mono font-bold text-black">{{ number_format($nilai, 0, ',', '.') }}</td>
                                        <td class="px-3 py-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badge['cls'] }}">{{ $badge['label'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="11" class="px-4 py-10 text-center text-slate-400">Tidak ada data sesuai filter.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($detail && $detail->hasPages())
                        <div class="p-4 border-t border-blue-50">
                            {{ $detail->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- ===================== TAB: GRAFIK & VISUALISASI ===================== --}}
            <div id="tab-grafik" class="tab-panel space-y-5 hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Entri per Komoditas – Kota Kediri</h3>
                        <canvas id="chartEntriKomoditas" height="220"></canvas>
                    </div>
                    <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Tren Neraca Bulanan</h3>
                        <canvas id="chartTrenBulanan" height="220"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-blue-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold mb-4" style="color:#1E3A5F;">Perbandingan Nilai Neraca per Komoditas (Data Valid)</h3>
                    <canvas id="chartPerbandingan" height="220"></canvas>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    // ── Tab switching ──
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanels = { ringkasan: document.getElementById('tab-ringkasan'), detail: document.getElementById('tab-detail'), grafik: document.getElementById('tab-grafik') };
    const filterTabInput = document.getElementById('filterTabInput');
    let chartsRendered = false;

    function activateTab(tab) {
        tabButtons.forEach(btn => {
            const isActive = btn.dataset.tab === tab;
            btn.classList.toggle('text-white', isActive);
            btn.classList.toggle('shadow-sm', isActive);
            btn.classList.toggle('text-slate-500', !isActive);
            btn.style.backgroundColor = isActive ? '#2563EB' : '';
        });
        Object.entries(tabPanels).forEach(([key, panel]) => panel.classList.toggle('hidden', key !== tab));
        if (filterTabInput) filterTabInput.value = tab;

        if (tab === 'grafik' && !chartsRendered) {
            renderCharts();
            chartsRendered = true;
        }
    }

    tabButtons.forEach(btn => btn.addEventListener('click', () => activateTab(btn.dataset.tab)));

    @php $initialTab = request('tab', 'ringkasan'); @endphp
    activateTab(@json($initialTab));

    // ── Charts ──
    function renderCharts() {
        new Chart(document.getElementById('chartEntriKomoditas'), {
            type: 'bar',
            data: {
                labels: @json($entriPerKomoditas['labels']),
                datasets: [
                    { label: 'Valid',    data: @json($entriPerKomoditas['valid']),    backgroundColor: '#16A34A', stack: 'a' },
                    { label: 'Menunggu', data: @json($entriPerKomoditas['menunggu']), backgroundColor: '#EA580C', stack: 'a' },
                    { label: 'Revisi',   data: @json($entriPerKomoditas['revisi']),   backgroundColor: '#DC2626', stack: 'a' },
                ]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { font: { size: 9 }, maxRotation: 40, minRotation: 40 } },
                    y: { stacked: true, beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#EFF6FF' } },
                }
            }
        });

        new Chart(document.getElementById('chartTrenBulanan'), {
            type: 'line',
            data: {
                labels: @json($trenBulanan['labels']),
                datasets: [{
                    data: @json($trenBulanan['nilai']),
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.12)',
                    fill: true, tension: 0, pointRadius: 4, pointBackgroundColor: '#2563EB',
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#EFF6FF' } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });

        const perbandinganColors = ['#2563EB','#1D4ED8','#60A5FA','#1E40AF','#3B82F6','#93C5FD','#2563EB','#60A5FA'];
        const perbandinganData = @json($perbandinganNilai->pluck('nilai'));
        new Chart(document.getElementById('chartPerbandingan'), {
            type: 'bar',
            data: {
                labels: @json($perbandinganNilai->pluck('nama')),
                datasets: [{
                    label: 'Nilai Neraca',
                    data: perbandinganData,
                    backgroundColor: perbandinganData.map((_, i) => perbandinganColors[i % perbandinganColors.length]),
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: '#EFF6FF' } },
                }
            }
        });
    }
</script>

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
    <script src="{{ asset('js/sidebar-toggle.js') }}"></script>
</body>
</html>