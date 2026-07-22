<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Input Neraca Pangan - S-KOLAK Kota Kediri</title>

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
        .skolak-select {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1rem; padding-right: 2.25rem;
        }
        /* Input angka tanpa tombol naik/turun (spinner), berlaku di semua browser */
        .no-spinner::-webkit-outer-spin-button,
        .no-spinner::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .no-spinner { -moz-appearance: textfield; }
    </style>
</head>
<body class="h-screen overflow-hidden" style="background-color:#F5F9FF;">

@php
    $notifCount = $notifCount ?? 0;
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
    $activeMenu = 'input';
    $userName = auth()->check() ? auth()->user()->name : 'Operator';
    $firstName = trim(explode(',', $userName)[0]);

    // Nama bulan Indonesia — dipakai untuk isi <select> Bulan Neraca.
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // Tahun berjalan diambil otomatis dari fungsi date(), dipakai sebagai
    // nilai default input Tahun Neraca (bukan lagi dropdown).
    $tahunSekarang = date('Y');

    $komoditasList = $komoditasList ?? collect();
    $justSubmitted = session('justSubmitted', false);
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
                $totalEntri = $totalEntri ?? 0;
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Input Neraca Pangan</h2>
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
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">

            @if ($justSubmitted)
                <div class="max-w-3xl mx-auto bg-white rounded-xl border border-blue-100 shadow-sm p-10 flex flex-col items-center text-center gap-2">
                    <div class="w-16 h-16 rounded-full bg-green-50 flex items-center justify-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold" style="color:#1E3A5F;">Data Berhasil Dikirim untuk Verifikasi</h2>
                    <p class="text-sm text-slate-500">Verifikator akan segera memeriksa data Anda.</p>
                    <a href="{{ route('operator.input') }}"
                       class="mt-3 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all" style="background-color:#2563EB;">
                        Input Data Baru
                    </a>
                </div>
            @else

            @if (session('status'))
                <div class="mb-5 flex items-center gap-2 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2 px-4 py-3 rounded-xl bg-red-50 border border-red-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                    <div class="text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="max-w-3xl mx-auto space-y-1 mb-5">
                <h1 class="text-xl font-bold" style="color:#1E3A5F;">Input Neraca Pangan</h1>
                <p class="text-sm text-slate-500">Masukkan data neraca pangan Kota Kediri dengan lengkap dan benar.</p>
            </div>

            <form method="POST" action="{{ route('operator.input.store') }}" id="formInputNeraca" class="max-w-3xl mx-auto bg-white rounded-xl border border-blue-100 shadow-sm p-6 space-y-6">
                @csrf

                {{-- ===== Identitas Data ===== --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-bold border-b border-blue-50 pb-2" style="color:#1E3A5F;">Identitas Data</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Tahun Neraca <span class="text-red-500">*</span></label>
                            <input type="number" inputmode="numeric" name="tahun" id="tahun" required
                                   value="{{ old('tahun', $tahunSekarang) }}"
                                   class="no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            @error('tahun') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Bulan Neraca <span class="text-red-500">*</span></label>
                            <select name="bulan" id="bulan" required
                                    class="skolak-select w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                                <option value="" disabled {{ old('bulan') ? '' : 'selected' }}>Pilih Bulan</option>
                                @foreach ($namaBulan as $angka => $nama)
                                    <option value="{{ $angka }}" {{ (int) old('bulan') === $angka ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                            @error('bulan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Komoditas <span class="text-red-500">*</span></label>
                            <select name="komoditas_id" id="komoditas_id" required
                                    class="skolak-select w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                                <option value="" disabled {{ old('komoditas_id') ? '' : 'selected' }}>Pilih Komoditas</option>
                                @foreach ($komoditasList as $komoditas)
                                    <option value="{{ $komoditas->id }}" {{ (int) old('komoditas_id') === $komoditas->id ? 'selected' : '' }}>{{ $komoditas->nama }}</option>
                                @endforeach
                            </select>
                            @error('komoditas_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Satuan</label>
                            <div class="w-full px-3 py-2.5 rounded-lg border border-blue-100 bg-blue-50/40 text-sm text-slate-500 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Semua data dalam satuan <strong class="text-slate-700 ml-1">Ton</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Data Neraca ===== --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-bold border-b border-blue-50 pb-2" style="color:#1E3A5F;">Data Neraca</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Stok Awal <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" autocomplete="off" name="stok_awal" id="stok_awal"
                                   value="{{ old('stok_awal', 0) }}" required
                                   class="angka-neraca no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            <p id="hintStokAwal" class="text-xs text-blue-500 mt-1"></p>
                            @error('stok_awal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Produksi <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" autocomplete="off" name="produksi" id="produksi"
                                   value="{{ old('produksi', 0) }}" required
                                   class="angka-neraca no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            @error('produksi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Barang Masuk <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" autocomplete="off" name="masuk" id="masuk"
                                   value="{{ old('masuk', 0) }}" required
                                   class="angka-neraca no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            @error('masuk') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Barang Keluar <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" autocomplete="off" name="keluar" id="keluar"
                                   value="{{ old('keluar', 0) }}" required
                                   class="angka-neraca no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            @error('keluar') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Kebutuhan Rumah Tangga <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" autocomplete="off" name="kebutuhan_rumah_tangga" id="kebutuhan_rumah_tangga"
                                   value="{{ old('kebutuhan_rumah_tangga', 0) }}" required
                                   class="angka-neraca no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            @error('kebutuhan_rumah_tangga') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Kebutuhan Non Rumah Tangga <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" autocomplete="off" name="kebutuhan_non_rumah_tangga" id="kebutuhan_non_rumah_tangga"
                                   value="{{ old('kebutuhan_non_rumah_tangga', 0) }}" required
                                   class="angka-neraca no-spinner w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                            @error('kebutuhan_non_rumah_tangga') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all" style="background-color:#2563EB;">
                    Kirim untuk Verifikasi
                </button>
            </form>

            @endif
        </main>
    </div>
</div>

<script>
    (function () {
        const stokAwalUrl = @json(route('operator.input.stok-awal'));

        const fieldTahun = document.getElementById('tahun');
        const fieldBulan = document.getElementById('bulan');
        const fieldKomoditas = document.getElementById('komoditas_id');
        const fieldStokAwal = document.getElementById('stok_awal');
        const hintStokAwal = document.getElementById('hintStokAwal');

        const angkaFields = document.querySelectorAll('.angka-neraca');

        // Hanya izinkan digit dan satu tanda titik desimal — tanpa tombol naik/turun
        // karena input pakai type="text", bukan type="number".
        angkaFields.forEach(function (el) {
            el.addEventListener('input', function () {
                let v = el.value.replace(/[^0-9.]/g, '');
                const firstDot = v.indexOf('.');
                if (firstDot !== -1) {
                    v = v.slice(0, firstDot + 1) + v.slice(firstDot + 1).replace(/\./g, '');
                }
                el.value = v;
            });
            el.addEventListener('focus', function () {
                if (el.value === '0') el.value = '';
            });
            el.addEventListener('blur', function () {
                if (el.value === '') el.value = '0';
            });
        });

        // Auto-isi Stok Awal dari nilai neraca (stok akhir) bulan sebelumnya,
        // untuk komoditas yang sama — dipanggil setiap kali tahun/bulan/komoditas berubah.
        function ambilStokAwalSebelumnya() {
            const tahun = fieldTahun.value;
            const bulan = fieldBulan.value;
            const komoditasId = fieldKomoditas.value;

            hintStokAwal.textContent = '';

            if (!tahun || !bulan || !komoditasId) return;

            const url = stokAwalUrl + '?' + new URLSearchParams({
                tahun: tahun,
                bulan: bulan,
                komoditas_id: komoditasId,
            });

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.found) {
                        fieldStokAwal.value = data.stok_awal;
                        hintStokAwal.textContent = 'Otomatis diisi dari akhir periode ' + data.periode;
                    }
                })
                .catch(function () { /* diamkan bila gagal, operator tetap bisa isi manual */ });
        }

        [fieldTahun, fieldBulan, fieldKomoditas].forEach(function (el) {
            if (el) el.addEventListener('change', ambilStokAwalSebelumnya);
        });
    })();
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