<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    $activeMenu = 'input';
    $userName = auth()->check() ? auth()->user()->name : 'Operator';
    $firstName = trim(explode(',', $userName)[0]);

    // Nama bulan Indonesia — dipakai untuk isi <select> Bulan Neraca.
    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // Rentang tahun dibangun dari tanggal server saat ini (date/now()), bukan angka tetap,
    // jadi daftar tahun otomatis mengikuti tahun berjalan (termasuk 2xxx dan seterusnya)
    // setiap kali halaman ini dibuka — tidak perlu diubah manual tiap tahun.
    $tahunSekarang = (int) now()->year;
    $daftarTahun = range($tahunSekarang + 1, $tahunSekarang - 5);

    $komoditasList = $komoditasList ?? collect();
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
                    ['key' => 'data',       'label' => 'Data Neraca Saya',    'route' => 'operator.data',       'badge' => null],
                    ['key' => 'riwayat',    'label' => 'Riwayat Verifikasi',  'route' => 'operator.riwayat',    'badge' => null],
                    ['key' => 'laporan',    'label' => 'Laporan',             'route' => 'operator.laporan',    'badge' => null],
                    ['key' => 'notifikasi', 'label' => 'Notifikasi',          'route' => 'operator.notifikasi', 'badge' => $notifCount],
                ];
                $menuIcons = [
                    'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                    'input'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
                    'data'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>',
                    'riwayat'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Input Neraca Pangan</h2>
                <p class="text-xs text-slate-400">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ Route::has('operator.notifikasi') ? route('operator.notifikasi') : '#' }}" class="relative w-9 h-9 flex items-center justify-center rounded-xl hover:bg-blue-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="#1E3A5F" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    @if ($notifCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-orange-500"></span>
                    @endif
                </a>
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white" style="background-color:#2563EB;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">

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
                            <select name="tahun" id="tahun" required
                                    class="skolak-select w-full px-3 py-2.5 rounded-lg border border-blue-200 text-sm outline-none focus:border-blue-400">
                                <option value="" disabled {{ old('tahun') ? '' : 'selected' }}>Pilih Tahun</option>
                                @foreach ($daftarTahun as $tahun)
                                    <option value="{{ $tahun }}" {{ (int) old('tahun') === $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                                @endforeach
                            </select>
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

                {{-- ===== Nilai Neraca (otomatis) ===== --}}
                <div id="cardNilaiNeraca" class="rounded-xl border p-4 flex items-center justify-between bg-slate-50 border-slate-200">
                    <div>
                        <p class="text-xs font-bold tracking-wide text-slate-500">NILAI NERACA PANGAN (OTOMATIS)</p>
                        <p class="text-xs text-slate-400 mt-0.5">Stok Awal + Produksi + Masuk &minus; Keluar &minus; Keb. RT &minus; Keb. Non-RT</p>
                    </div>
                    <div class="text-right">
                        <p id="nilaiNeraca" class="text-2xl font-bold text-black">0</p>
                        <p id="statusNeraca" class="text-xs font-semibold text-slate-500">Netral</p>
                    </div>
                </div>

                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all" style="background-color:#2563EB;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Kirim untuk Verifikasi
                </button>
            </form>
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
        const nilaiNeracaEl = document.getElementById('nilaiNeraca');
        const statusNeracaEl = document.getElementById('statusNeraca');
        const cardNeraca = document.getElementById('cardNilaiNeraca');

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
                hitungNilaiNeraca();
            });
            el.addEventListener('focus', function () {
                if (el.value === '0') el.value = '';
            });
            el.addEventListener('blur', function () {
                if (el.value === '') el.value = '0';
            });
        });

        function angka(id) {
            const v = parseFloat(document.getElementById(id).value);
            return isNaN(v) ? 0 : v;
        }

        function hitungNilaiNeraca() {
            const nilai = angka('stok_awal') + angka('produksi') + angka('masuk')
                - angka('keluar') - angka('kebutuhan_rumah_tangga') - angka('kebutuhan_non_rumah_tangga');

            nilaiNeracaEl.textContent = new Intl.NumberFormat('id-ID').format(nilai);

            cardNeraca.classList.remove('bg-slate-50', 'border-slate-200', 'bg-green-50', 'border-green-200', 'bg-red-50', 'border-red-200');
            if (nilai > 0) {
                cardNeraca.classList.add('bg-green-50', 'border-green-200');
                statusNeracaEl.textContent = 'Surplus';
                statusNeracaEl.className = 'text-xs font-semibold text-green-600';
            } else if (nilai < 0) {
                cardNeraca.classList.add('bg-red-50', 'border-red-200');
                statusNeracaEl.textContent = 'Defisit';
                statusNeracaEl.className = 'text-xs font-semibold text-red-600';
            } else {
                cardNeraca.classList.add('bg-slate-50', 'border-slate-200');
                statusNeracaEl.textContent = 'Netral';
                statusNeracaEl.className = 'text-xs font-semibold text-slate-500';
            }
        }

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
                        hitungNilaiNeraca();
                    }
                })
                .catch(function () { /* diamkan bila gagal, operator tetap bisa isi manual */ });
        }

        [fieldTahun, fieldBulan, fieldKomoditas].forEach(function (el) {
            el.addEventListener('change', ambilStokAwalSebelumnya);
        });

        hitungNilaiNeraca();
    })();
</script>

</body>
</html>