<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Neraca Saya - S-KOLAK Kota Kediri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .no-spinner::-webkit-outer-spin-button,
        .no-spinner::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .no-spinner { -moz-appearance: textfield; }
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
                    'pesan' => $n->pesan,
                    'waktu' => \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $tipe,
                ];
            })->values()
            : collect()
    )));
    $activeMenu = 'data';
    $userName = auth()->check() ? auth()->user()->name : 'Siti Rahayu, S.P';
    $firstName = trim(explode(',', $userName)[0]);

    $statusBadge = [
        'valid'    => ['label' => 'Valid',        'cls' => 'bg-green-50 text-green-700 border-green-200'],
        'menunggu' => ['label' => 'Menunggu Verifikasi', 'cls' => 'bg-orange-50 text-orange-700 border-orange-200'],
        'revisi'   => ['label' => 'Perlu Revisi', 'cls' => 'bg-red-50 text-red-700 border-red-200'],
    ];

    // Data fallback (dipakai bila controller belum mengirim variabel $items) —
    // hanya untuk pratinjau tampilan, mengikuti pola yang sama dengan operator/dashboard.blade.php.
    $rawItems = $items ?? collect([
        (object) ['id'=>1, 'periode'=>'Jan 2025', 'komoditas'=>'Beras',        'satuan'=>'Ton', 'stok_awal'=>450,  'produksi'=>120, 'masuk'=>280, 'keluar'=>95,  'keb_rt'=>185,  'keb_non_rt'=>45,  'status'=>'valid',    'verifikator'=>'Budi Santoso, M.Si', 'keterangan'=>null, 'tanggal_input'=>'15 Jan 2025'],
        (object) ['id'=>2, 'periode'=>'Feb 2025', 'komoditas'=>'Beras',        'satuan'=>'Ton', 'stok_awal'=>525,  'produksi'=>130, 'masuk'=>260, 'keluar'=>100, 'keb_rt'=>190,  'keb_non_rt'=>48,  'status'=>'valid',    'verifikator'=>'Dewi Lestari, S.P',  'keterangan'=>null, 'tanggal_input'=>'14 Feb 2025'],
        (object) ['id'=>3, 'periode'=>'Mar 2025', 'komoditas'=>'Beras',        'satuan'=>'Ton', 'stok_awal'=>577,  'produksi'=>115, 'masuk'=>240, 'keluar'=>110, 'keb_rt'=>188,  'keb_non_rt'=>46,  'status'=>'valid',    'verifikator'=>'Budi Santoso, M.Si', 'keterangan'=>null, 'tanggal_input'=>'15 Mar 2025'],
        (object) ['id'=>4, 'periode'=>'Mei 2025', 'komoditas'=>'Beras',        'satuan'=>'Ton', 'stok_awal'=>628,  'produksi'=>140, 'masuk'=>270, 'keluar'=>105, 'keb_rt'=>195,  'keb_non_rt'=>52,  'status'=>'valid',    'verifikator'=>'Budi Santoso, M.Si', 'keterangan'=>null, 'tanggal_input'=>'15 Mei 2025'],
        (object) ['id'=>5, 'periode'=>'Mar 2025', 'komoditas'=>'Kedelai',      'satuan'=>'Ton', 'stok_awal'=>85,   'produksi'=>25,  'masuk'=>60,  'keluar'=>30,  'keb_rt'=>45,   'keb_non_rt'=>15,  'status'=>'revisi',   'verifikator'=>'Budi Santoso, M.Si', 'keterangan'=>'Angka produksi perlu dikonfirmasi ulang dengan data lapangan.', 'tanggal_input'=>'08 Mar 2025'],
        (object) ['id'=>6, 'periode'=>'Jan 2025', 'komoditas'=>'Cabai Rawit',  'satuan'=>'Kg',  'stok_awal'=>1800, 'produksi'=>600, 'masuk'=>1500,'keluar'=>700, 'keb_rt'=>1850, 'keb_non_rt'=>450, 'status'=>'valid',    'verifikator'=>'Budi Santoso, M.Si', 'keterangan'=>null, 'tanggal_input'=>'18 Jan 2025'],
        (object) ['id'=>7, 'periode'=>'Feb 2025', 'komoditas'=>'Cabai Rawit',  'satuan'=>'Kg',  'stok_awal'=>900,  'produksi'=>550, 'masuk'=>1400,'keluar'=>750, 'keb_rt'=>1900, 'keb_non_rt'=>460, 'status'=>'valid',    'verifikator'=>'Dewi Lestari, S.P',  'keterangan'=>null, 'tanggal_input'=>'14 Feb 2025'],
        (object) ['id'=>8, 'periode'=>'Jan 2025', 'komoditas'=>'Daging Sapi',  'satuan'=>'Kg',  'stok_awal'=>1800, 'produksi'=>500, 'masuk'=>1200,'keluar'=>600, 'keb_rt'=>2000, 'keb_non_rt'=>700, 'status'=>'valid',    'verifikator'=>'Budi Santoso, M.Si', 'keterangan'=>null, 'tanggal_input'=>'17 Jan 2025'],
        (object) ['id'=>9, 'periode'=>'Feb 2025', 'komoditas'=>'Daging Sapi',  'satuan'=>'Kg',  'stok_awal'=>200,  'produksi'=>480, 'masuk'=>1100,'keluar'=>580, 'keb_rt'=>2050, 'keb_non_rt'=>720, 'status'=>'valid',    'verifikator'=>'Dewi Lestari, S.P',  'keterangan'=>null, 'tanggal_input'=>'14 Feb 2025'],
        (object) ['id'=>10,'periode'=>'Apr 2025', 'komoditas'=>'Gula Konsumsi','satuan'=>'Kg',  'stok_awal'=>7450, 'produksi'=>0,   'masuk'=>6000,'keluar'=>2300,'keb_rt'=>4400, 'keb_non_rt'=>1600,'status'=>'draft',    'verifikator'=>null,                 'keterangan'=>null, 'tanggal_input'=>'14 Apr 2025'],
    ]);

    // Normalisasi setiap baris (mendukung Eloquent Model \App\Models\NeracaPangan maupun objek/array pratinjau)
    // supaya template di bawah tidak perlu tahu sumber datanya.
    $rows = collect($rawItems)->map(function ($n) {
        $isModel = $n instanceof \App\Models\NeracaPangan;

        if ($isModel) {
            return [
                'id'            => $n->id,
                'periode'       => \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode),
                'komoditas'     => $n->komoditas->nama ?? '-',
                'satuan'        => $n->komoditas->satuan ?? 'Ton',
                'stok_awal'     => (float) $n->stok_awal,
                'produksi'      => (float) $n->produksi,
                'masuk'         => (float) $n->masuk,
                'keluar'        => (float) $n->keluar,
                'keb_rt'        => (float) $n->kebutuhan_rumah_tangga,
                'keb_non_rt'    => (float) $n->kebutuhan_non_rumah_tangga,
                'nilai_neraca'  => \App\Http\Controllers\Admin\DataNeracaController::hitungNilaiNeraca($n),
                'status'        => $n->status,
                'verifikator'   => $n->verifikator->name ?? null,
                'keterangan'    => $n->keterangan,
                'tanggal_input' => optional($n->created_at)->translatedFormat('d M Y'),
            ];
        }

        $n = (array) $n;
        $nilai = ($n['stok_awal'] ?? 0) + ($n['produksi'] ?? 0) + ($n['masuk'] ?? 0)
               - ($n['keluar'] ?? 0) - ($n['keb_rt'] ?? 0) - ($n['keb_non_rt'] ?? 0);

        return array_merge($n, ['nilai_neraca' => $nilai]);
    });

    // Draft belum diajukan untuk verifikasi — tidak relevan ditampilkan di halaman ini.
    $rows = $rows->reject(fn ($r) => $r['status'] === 'draft');

    // Data "Perlu Revisi" selalu dinaikkan ke paling atas supaya langsung terlihat operator.
    $rows = $rows->sortByDesc(fn ($r) => $r['status'] === 'revisi' ? 1 : 0)->values();

    $jumlahRevisi = $rows->where('status', 'revisi')->count();
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

        {{-- Catatan: menu "Riwayat Verifikasi" sengaja dihapus dari navbar kiri sesuai permintaan. --}}
        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto mt-2">
            @php
                $menuItems = [
                    ['key' => 'dashboard',  'label' => 'Dashboard',           'route' => 'operator.dashboard',  'badge' => null],
                    ['key' => 'input',      'label' => 'Input Neraca Pangan', 'route' => 'operator.input',      'badge' => null],
                    ['key' => 'data',       'label' => 'Data Neraca Saya',    'route' => 'operator.data',       'badge' => $rows->count()],
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Data Neraca Saya</h2>
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

            @if (session('status'))
                <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <div>
                <h1 class="text-xl font-bold" style="color:#1E3A5F;">Data Neraca Saya</h1>
                <p class="text-sm text-slate-500">Seluruh data neraca pangan yang telah Anda ajukan · Kota Kediri</p>
            </div>

            {{-- Alert revisi --}}
            @if ($jumlahRevisi > 0)
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-orange-200 bg-orange-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] text-orange-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/>
                    </svg>
                    <p class="text-sm text-orange-700 font-medium">
                        Ada <strong>{{ $jumlahRevisi }}</strong> data yang perlu direvisi. Klik tombol <strong>Revisi</strong> untuk melihat alasan dari verifikator dan memperbaikinya.
                    </p>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background-color:#F0F7FF;">
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Periode</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Komoditas</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Tanggal Input</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $i => $n)
                                @php
                                    $badge = $statusBadge[$n['status']] ?? ['label' => ucfirst($n['status']), 'cls' => 'bg-slate-50 text-slate-600 border-slate-200'];
                                    $isRevisi = $n['status'] === 'revisi';

                                    // json_encode manual (bukan @json()) — directive @json() Blade rusak
                                    // untuk array dengan lebih dari satu key (di-explode berdasarkan koma).
                                    $payload = json_encode([
                                        'id'           => $n['id'],
                                        'komoditas'    => $n['komoditas'],
                                        'periode'      => $n['periode'],
                                        'satuan'       => $n['satuan'] ?? 'Ton',
                                        'statusLabel'  => $badge['label'],
                                        'statusCls'    => $badge['cls'],
                                        'stokAwal'     => (float) $n['stok_awal'],
                                        'produksi'     => (float) $n['produksi'],
                                        'masuk'        => (float) $n['masuk'],
                                        'keluar'       => (float) $n['keluar'],
                                        'kebRT'        => (float) $n['keb_rt'],
                                        'kebNonRT'     => (float) $n['keb_non_rt'],
                                        'nilaiNeraca'  => (float) $n['nilai_neraca'],
                                        'verifikator'  => $n['verifikator'] ?? '—',
                                        'tanggalInput' => $n['tanggal_input'],
                                        'keterangan'   => $n['keterangan'] ?? '',
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);
                                @endphp
                                <tr class="border-t border-blue-50 transition-colors {{ $isRevisi ? 'bg-red-50/40 hover:bg-red-50/60' : 'hover:bg-blue-50/20' }}">
                                    <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium" style="color:#1E3A5F;">{{ $n['periode'] }}</td>
                                    <td class="px-4 py-3 font-semibold" style="color:#1E3A5F;">{{ $n['komoditas'] }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badge['cls'] }}">
                                            @if ($isRevisi)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[11px] h-[11px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                                            @endif
                                            {{ $badge['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-400">{{ $n['tanggal_input'] }}</td>
                                    <td class="px-4 py-3">
                                        @if ($isRevisi)
                                            <button type="button" onclick='openRevisi({!! $payload !!})'
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white shadow-sm hover:shadow-md transition-all" style="background-color:#EA580C;">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/></svg>
                                                Revisi
                                            </button>
                                        @else
                                            <button type="button" onclick='openDetail({!! $payload !!})'
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-blue-200 text-blue-600 text-xs font-medium hover:bg-blue-50 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                                Detail
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-10 text-slate-400">Belum ada data yang diinput.</td></tr>
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
        </main>
    </div>
</div>

{{-- ============ MODAL DETAIL (read-only, status valid / menunggu) ============ --}}
<div id="modalDetail" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-blue-50">
            <div>
                <h3 id="detailKomoditas" class="text-sm font-bold" style="color:#1E3A5F;"></h3>
                <p id="detailPeriode" class="text-sm font-medium text-slate-500 mt-0.5"></p>
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
                <div class="flex justify-between"><span>Verifikator</span><span id="detailVerifikator" class="font-medium text-slate-700"></span></div>
                <div class="flex justify-between"><span>Tanggal Input</span><span id="detailTanggal" class="font-medium text-slate-700"></span></div>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL REVISI (gabungan: detail + alasan verifikator + form edit) ============ --}}
<div id="modalRevisi" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[92vh] overflow-y-auto">
        <div class="flex items-center justify-between px-5 py-4 border-b border-blue-50">
            <div>
                <h3 id="revisiKomoditas" class="text-sm font-bold" style="color:#1E3A5F;"></h3>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border bg-red-50 text-red-700 border-red-200">Perlu Revisi</span>
                <button type="button" onclick="document.getElementById('modalRevisi').classList.add('hidden')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div class="p-5 space-y-5">
            {{-- Alasan revisi dari verifikator --}}
            <div class="rounded-xl p-4 border border-orange-200 bg-orange-50">
                <p class="text-xs font-semibold text-orange-700 flex items-center gap-1.5 mb-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z"/></svg>
                    Alasan Revisi dari <span id="revisiVerifikator" class="font-bold"></span>
                </p>
                <p id="revisiKeterangan" class="text-sm text-orange-900 leading-relaxed"></p>
            </div>

            {{-- Form edit --}}
            <form id="revisiForm" method="POST" action="">
                @csrf
                @method('PUT')

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">Perbaiki Data Neraca</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Stok Awal</label>
                            <input type="number" step="any" name="stok_awal" id="revStokAwal" class="revisi-angka no-spinner w-full px-3 py-2 rounded-lg border border-blue-200 text-sm text-right font-mono outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Produksi</label>
                            <input type="number" step="any" name="produksi" id="revProduksi" class="revisi-angka no-spinner w-full px-3 py-2 rounded-lg border border-blue-200 text-sm text-right font-mono outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Barang Masuk</label>
                            <input type="number" step="any" name="masuk" id="revMasuk" class="revisi-angka no-spinner w-full px-3 py-2 rounded-lg border border-blue-200 text-sm text-right font-mono outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Barang Keluar</label>
                            <input type="number" step="any" name="keluar" id="revKeluar" class="revisi-angka no-spinner w-full px-3 py-2 rounded-lg border border-blue-200 text-sm text-right font-mono outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Keb. Rumah Tangga</label>
                            <input type="number" step="any" name="kebutuhan_rumah_tangga" id="revKebRT" class="revisi-angka no-spinner w-full px-3 py-2 rounded-lg border border-blue-200 text-sm text-right font-mono outline-none focus:border-blue-400">
                        </div>
                        <div>
                            <label class="text-xs text-slate-500 block mb-1">Keb. Non Rumah Tangga</label>
                            <input type="number" step="any" name="kebutuhan_non_rumah_tangga" id="revKebNonRT" class="revisi-angka no-spinner w-full px-3 py-2 rounded-lg border border-blue-200 text-sm text-right font-mono outline-none focus:border-blue-400">
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('modalRevisi').classList.add('hidden')"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2" style="background-color:#2563EB;">
                        Kirim Ulang untuk Verifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const operatorDataBaseUrl = @json(url('operator/data'));

    function fmtNumber(n) {
        return Number(n).toLocaleString('id-ID');
    }

    // ---------- Modal Detail (read-only) ----------
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
        document.getElementById('detailNilaiNeraca').textContent = fmtNumber(d.nilaiNeraca) + ' ' + (d.satuan || '');

        const nilaiBox = document.getElementById('detailNilaiBox');
        nilaiBox.className = 'rounded-xl px-4 py-3 border-2 ' + (d.nilaiNeraca >= 0 ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50');

        document.getElementById('detailVerifikator').textContent = d.verifikator || '—';
        document.getElementById('detailTanggal').textContent = d.tanggalInput;

        document.getElementById('modalDetail').classList.remove('hidden');
    }

    // ---------- Modal Revisi (detail + alasan + form edit, jadi satu) ----------

    function openRevisi(d) {
        document.getElementById('revisiKomoditas').textContent = d.komoditas;
        document.getElementById('revisiVerifikator').textContent = d.verifikator || 'Verifikator';
        document.getElementById('revisiKeterangan').textContent = d.keterangan || 'Verifikator tidak menyertakan catatan tambahan.';

        document.getElementById('revStokAwal').value = d.stokAwal;
        document.getElementById('revProduksi').value = d.produksi;
        document.getElementById('revMasuk').value = d.masuk;
        document.getElementById('revKeluar').value = d.keluar;
        document.getElementById('revKebRT').value = d.kebRT;
        document.getElementById('revKebNonRT').value = d.kebNonRT;

        document.getElementById('revisiForm').action = operatorDataBaseUrl + '/' + d.id;

        document.getElementById('modalRevisi').classList.remove('hidden');
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
</body>
</html>