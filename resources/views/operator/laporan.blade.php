<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan - S-KOLAK Kota Kediri</title>

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
                    'id'    => $n->id,
                    'pesan' => $n->pesan,
                    'waktu' => \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $tipe,
                ];
            })->values()
            : collect()
    )));
    $activeMenu = 'laporan';
    $userName = auth()->check() ? auth()->user()->name : 'Siti Rahayu, S.P';
    $firstName = trim(explode(',', $userName)[0]);

    // Nilai default filter apabila controller belum mengirim variabel $filters —
    // otomatis terisi ulang dari query string saat form filter disubmit (GET).
    $filters = $filters ?? [
        'tahun_awal'  => request('tahun_awal', ''),
        'bulan_awal'  => request('bulan_awal', ''),
        'tahun_akhir' => request('tahun_akhir', ''),
        'bulan_akhir' => request('bulan_akhir', ''),
        'komoditas_id'=> request('komoditas_id', ''),
    ];

    $tahunSekarang = (int) now()->year;
    $tahunList = range($tahunSekarang - 1, $tahunSekarang + 1);
    $bulanList = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agt','Sep','Okt','Nov','Des'];

    $komoditasList = $komoditasList ?? collect();

    $statusBadge = [
        'valid'    => ['label' => 'Valid',              'cls' => 'bg-green-50 text-green-700 border-green-200'],
        'menunggu' => ['label' => 'Menunggu Verifikasi', 'cls' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'revisi'   => ['label' => 'Perlu Revisi',        'cls' => 'bg-red-50 text-red-700 border-red-200'],
        'draft'    => ['label' => 'Draft',               'cls' => 'bg-slate-50 text-slate-600 border-slate-200'],
    ];

    // Data fallback (dipakai bila controller belum mengirim variabel $items) —
    // hanya untuk pratinjau tampilan, mengikuti pola yang sama dengan operator/data_neraca.blade.php.
    $rawItems = $items ?? collect([
        (object) ['id'=>1, 'periode'=>'Jan 2025', 'komoditas'=>'Beras',        'status'=>'valid',    'tanggal_input'=>'15 Jan 2025'],
        (object) ['id'=>2, 'periode'=>'Feb 2025', 'komoditas'=>'Beras',        'status'=>'valid',    'tanggal_input'=>'14 Feb 2025'],
        (object) ['id'=>3, 'periode'=>'Mar 2025', 'komoditas'=>'Beras',        'status'=>'valid',    'tanggal_input'=>'15 Mar 2025'],
        (object) ['id'=>4, 'periode'=>'Mei 2025', 'komoditas'=>'Beras',        'status'=>'valid',    'tanggal_input'=>'15 Mei 2025'],
        (object) ['id'=>5, 'periode'=>'Mar 2025', 'komoditas'=>'Kedelai',      'status'=>'revisi',   'tanggal_input'=>'08 Mar 2025'],
        (object) ['id'=>6, 'periode'=>'Jan 2025', 'komoditas'=>'Cabai Rawit',  'status'=>'valid',    'tanggal_input'=>'18 Jan 2025'],
        (object) ['id'=>7, 'periode'=>'Feb 2025', 'komoditas'=>'Cabai Rawit',  'status'=>'valid',    'tanggal_input'=>'14 Feb 2025'],
        (object) ['id'=>8, 'periode'=>'Jan 2025', 'komoditas'=>'Daging Sapi',  'status'=>'valid',    'tanggal_input'=>'17 Jan 2025'],
        (object) ['id'=>9, 'periode'=>'Feb 2025', 'komoditas'=>'Daging Sapi',  'status'=>'valid',    'tanggal_input'=>'14 Feb 2025'],
        (object) ['id'=>10,'periode'=>'Apr 2025', 'komoditas'=>'Gula Konsumsi','status'=>'draft',    'tanggal_input'=>'14 Apr 2025'],
    ]);

    // Normalisasi tiap baris (mendukung Eloquent Model \App\Models\NeracaPangan
    // maupun objek/array pratinjau) supaya template di bawah tidak perlu tahu sumber datanya.
    $normalisasiBarisLaporan = function ($n) {
        $isModel = $n instanceof \App\Models\NeracaPangan;

        if ($isModel) {
            return [
                'id'            => $n->id,
                'periode'       => \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode),
                'komoditas'     => $n->komoditas->nama ?? '-',
                'status'        => $n->status,
                'tanggal_input' => optional($n->created_at)->translatedFormat('d M Y'),
            ];
        }

        return (array) $n;
    };

    // Jika $items sudah berupa hasil paginate() dari controller, pakai through() supaya
    // info halaman (current page, total, dst.) tetap terjaga — collect()->map() akan
    // membuangnya menjadi Collection biasa dan mematikan pagination.
    if ($rawItems instanceof \Illuminate\Contracts\Pagination\Paginator) {
        $rows = $rawItems->through($normalisasiBarisLaporan);
    } else {
        $rows = collect($rawItems)->map($normalisasiBarisLaporan);
    }

    // Total & jumlah valid selalu dihitung dari seluruh data yang cocok filter (bukan
    // hanya baris di halaman aktif) — controller mengirim ini lewat $totalEntri/$dataValid;
    // fallback di bawah hanya dipakai untuk pratinjau tampilan tanpa controller.
    $totalEntri = $totalEntri ?? $rows->count();
    $dataValid  = $dataValid ?? $rows->where('status', 'valid')->count();
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
            <p class="text-xs font-semibold text-blue-600">Operator</p>
            <p class="text-xs font-medium truncate mt-0.5" style="color:#1E3A5F;">{{ $userName }}</p>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto mt-2">
            @php
                $menuItems = [
                    ['key' => 'dashboard',  'label' => 'Dashboard',           'route' => 'operator.dashboard',  'badge' => null],
                    ['key' => 'input',      'label' => 'Input Neraca Pangan', 'route' => 'operator.input',      'badge' => null],
                    ['key' => 'data',       'label' => 'Data Neraca Saya',    'route' => 'operator.data',       'badge' => $totalEntriSaya ?? 0],
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
                <h2 id="pageHeaderTitle" class="text-sm font-bold" style="color:#1E3A5F;">Laporan</h2>
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
                                     onclick="tandaiNotifDibaca(this, '{{ route('operator.notifikasi.baca', $n['id']) }}')"
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
                        <a href="{{ Route::has('operator.notifikasi') ? route('operator.notifikasi') : '#' }}" class="block text-center text-xs font-semibold text-blue-600 hover:bg-blue-100 py-2.5 border-t border-blue-50 transition-colors">Lihat Semua</a>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background-color:#2563EB;">
                    {{ strtoupper(substr($userName, 0, 1)) }}
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main id="pageContent" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5">

            {{-- Header + tombol cetak --}}
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold" style="color:#1E3A5F;">Laporan Neraca Saya</h1>
                    <p class="text-sm text-slate-500">Data yang diinput oleh {{ $firstName }}</p>
                </div>
                <a href="{{ Route::has('operator.laporan.cetak') ? route('operator.laporan.cetak', request()->query()) : '#' }}"
                   class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all" style="background-color:#2563EB;" target="_blank" rel="noopener">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Cetak PDF
                </a>
            </div>

            {{-- Filter --}}
            <form method="GET" action="{{ Route::has('operator.laporan') ? route('operator.laporan') : '#' }}" class="bg-white rounded-xl border border-blue-100 shadow-sm p-4">
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
                        <label class="text-xs text-slate-400">Komoditas</label>
                        <select name="komoditas_id" class="px-3 py-2 rounded-lg border border-blue-100 text-xs text-slate-700 bg-white outline-none focus:border-blue-400 min-w-[140px]">
                            <option value="">Semua</option>
                            @foreach ($komoditasList as $k)
                                <option value="{{ $k->id }}" @selected($filters['komoditas_id'] == $k->id)>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg text-xs text-white font-semibold flex items-center gap-1.5 hover:shadow-md transition-all" style="background-color:#2563EB;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
                        Terapkan
                    </button>
                    <a href="{{ Route::has('operator.laporan') ? route('operator.laporan') : '#' }}" class="px-3 py-2 rounded-lg border border-slate-200 text-xs text-slate-500 hover:bg-slate-50 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- Stat cards — gaya sama seperti kartu ringkasan di Dashboard --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 border-blue-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Total Entri</p>
                        <p class="text-2xl font-bold mt-0.5 text-black">{{ $totalEntri }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">sesuai filter</p>
                    </div>
                </div>
                <div class="bg-green-50 border-green-200 rounded-xl border shadow-sm p-5 flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">Data Valid</p>
                        <p class="text-2xl font-bold mt-0.5 text-black">{{ $dataValid }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">tervalidasi</p>
                    </div>
                </div>
            </div>

            {{-- Table --}}
            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-blue-50">
                    <p class="text-xs text-slate-500">Menampilkan {{ $totalEntri }} entri · Kota Kediri</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background-color:#F0F7FF;">
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Periode</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Komoditas</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $i => $n)
                                @php
                                    $badge = $statusBadge[$n['status']] ?? ['label' => ucfirst($n['status']), 'cls' => 'bg-slate-50 text-slate-600 border-slate-200'];
                                @endphp
                                <tr class="border-t border-blue-50 hover:bg-blue-50/20">
                                    <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium" style="color:#1E3A5F;">{{ $n['periode'] }}</td>
                                    <td class="px-4 py-3">{{ $n['komoditas'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badge['cls'] }}">{{ $badge['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{{ $n['tanggal_input'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-10 text-slate-400">Tidak ada data sesuai filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($rows, 'hasPages') && $rows->hasPages())
                    <div class="p-4 border-t border-blue-50">
                        {{ $rows->links() }}
                    </div>
                @endif
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