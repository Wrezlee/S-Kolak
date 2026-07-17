<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S-KOLAK · Sistem Ketersediaan Stok dan Laporan Aktual</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Fallback: compiles Tailwind classes directly in the browser, so the
             page always looks right even if `npm run build` hasn't been re-run
             after this blade file was edited. Remove this once your asset
             build is confirmed up to date. --}}
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    {{-- Reusable styling for native <select> so they look like real dropdowns
         even without extra JS (custom chevron + consistent focus ring) --}}
    <style>
        .skolak-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.25rem;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    {{-- Navbar --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(file_exists(public_path('images/logo-kediri.png')))
                    <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri" class="h-9 w-9 object-contain flex-shrink-0">
                @else
                    <div class="h-9 w-9 rounded-full bg-blue-700 flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                        SK
                    </div>
                @endif
                <div class="leading-tight">
                    <p class="font-bold text-slate-800">S-KOLAK</p>
                    <p class="text-xs text-slate-400">Kota Kediri</p>
                </div>
            </div>
            <a href="{{ Route::has('login') ? route('login') : '#' }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 hover:shadow-md active:scale-[0.98] transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                Masuk
            </a>
        </div>
    </header>

    {{-- Hero + Filter — everything inside this <section> shares the same blue
         background, so the blue extends all the way down past the filter card. --}}
    <section class="bg-gradient-to-br from-blue-800 via-blue-700 to-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-6 pt-12 pb-10">
            <span class="inline-flex items-center gap-2 text-xs">
                <span class="rounded-full bg-white/15 px-3 py-1 font-medium">Dashboard Publik</span>
                <span class="text-blue-100">Data terverifikasi · Kota Kediri</span>
            </span>

            <h1 class="mt-4 text-4xl md:text-5xl font-bold leading-tight max-w-2xl">
                Sistem Ketersediaan Stok dan Laporan Aktual
            </h1>

            <p class="mt-4 max-w-2xl text-blue-100 text-sm md:text-base">
                Informasi neraca pangan Kota Kediri yang transparan, terkini, dan telah melalui proses
                verifikasi oleh Dinas Ketahanan Pangan dan Pertanian Kota Kediri.
            </p>

            {{-- Filter card, still inside the blue section so the blue background
                 continues underneath and around it --}}
            <form method="GET" class="mt-8 bg-white text-slate-800 rounded-2xl shadow-lg p-6">
                <div class="flex items-center gap-2 text-slate-700 font-semibold mb-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Data
                </div>

                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun Awal</label>
                        <select name="tahun_awal" class="skolak-select w-full rounded-lg border border-blue-100 bg-blue-50/30 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                            <option value="Semua" {{ empty($q['tahun_awal'] ?? null) || ($q['tahun_awal'] ?? '') === 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t }}" {{ ($q['tahun_awal'] ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan Awal</label>
                        <select name="bulan_awal" class="skolak-select w-full rounded-lg border border-blue-100 bg-blue-50/30 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                            <option value="Semua" {{ empty($q['bulan_awal'] ?? null) || ($q['bulan_awal'] ?? '') === 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach ($bulanList as $b)
                                <option value="{{ $b }}" {{ ($q['bulan_awal'] ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun Akhir</label>
                        <select name="tahun_akhir" class="skolak-select w-full rounded-lg border border-blue-100 bg-blue-50/30 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                            <option value="Semua" {{ empty($q['tahun_akhir'] ?? null) || ($q['tahun_akhir'] ?? '') === 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach ($tahunList as $t)
                                <option value="{{ $t }}" {{ ($q['tahun_akhir'] ?? '') == $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan Akhir</label>
                        <select name="bulan_akhir" class="skolak-select w-full rounded-lg border border-blue-100 bg-blue-50/30 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                            <option value="Semua" {{ empty($q['bulan_akhir'] ?? null) || ($q['bulan_akhir'] ?? '') === 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach ($bulanList as $b)
                                <option value="{{ $b }}" {{ ($q['bulan_akhir'] ?? '') === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Komoditas</label>
                        <select name="komoditas" class="skolak-select w-full rounded-lg border border-blue-100 bg-blue-50/30 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition-colors">
                            <option value="Semua" {{ empty(request('komoditas')) || request('komoditas') === 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach ($komoditasList as $k)
                                <option value="{{ $k }}" {{ request('komoditas') === $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 hover:shadow-md active:scale-[0.98] transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Terapkan Filter
                    </button>
                    <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 px-4 py-2.5 text-sm font-medium text-blue-700 hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </section>

    @php

    $rowsCollection = collect($rows);

    $modalData = [
        'terpantau' => [
            'title' => 'Semua Komoditas Terpantau',
            'items' => $rowsCollection->unique('komoditas')->values()
        ],
        'aman' => [
            'title' => 'Komoditas Stok Aman',
            'items' => $rowsCollection->where('status','Aman')->values()
        ],
        'waspada' => [
            'title' => 'Komoditas Stok Waspada',
            'items' => $rowsCollection->where('status','Waspada')->values()
        ],
        'rentan' => [
            'title' => 'Komoditas Stok Rentan',
            'items' => $rowsCollection->where('status','Rentan')->values()
        ],
    ];

    $statusBadgeMap = [
        'Aman' => 'bg-green-50 text-green-700 border border-green-200',
        'Waspada' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'Rentan' => 'bg-red-50 text-red-700 border border-red-200',
    ];

    @endphp

    <main class="max-w-7xl mx-auto px-6 pb-16">

        {{-- Summary cards — square icon badges + matching border colors, same
             palette used in the Figma Make version (blue / green / yellow / red) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
            @php
                $cards = [
                    ['key' => 'terpantau', 'label' => 'Total Komoditas Terpantau', 'value' => $summary['total_komoditas'], 'color' => 'blue',  'icon' => 'box'],
                    ['key' => 'aman',      'label' => 'Komoditas Stok Aman',       'value' => $summary['aman'],            'color' => 'green', 'icon' => 'check'],
                    ['key' => 'waspada',   'label' => 'Komoditas Stok Waspada',    'value' => $summary['waspada'],         'color' => 'yellow','icon' => 'warning'],
                    ['key' => 'rentan',    'label' => 'Komoditas Stok Rentan',     'value' => $summary['rentan'],          'color' => 'red',    'icon' => 'x'],
                ];
                $cardColorMap = [
                    'blue'   => ['card' => 'bg-blue-50 border-blue-200',   'icon' => 'bg-blue-100 text-blue-600',   'link' => 'text-blue-600'],
                    'green'  => ['card' => 'bg-green-50 border-green-200', 'icon' => 'bg-green-100 text-green-600', 'link' => 'text-green-600'],
                    'yellow' => ['card' => 'bg-yellow-50 border-yellow-200','icon' => 'bg-yellow-100 text-yellow-600','link' => 'text-yellow-600'],
                    'red'    => ['card' => 'bg-red-50 border-red-200',     'icon' => 'bg-red-100 text-red-600',     'link' => 'text-red-600'],
                ];
            @endphp

            @foreach ($cards as $card)
                @php $cc = $cardColorMap[$card['color']]; @endphp
                <button type="button" onclick="openModal('{{ $card['key'] }}')"
                        class="text-left w-full {{ $cc['card'] }} border rounded-xl shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition-all">
                    <div class="h-9 w-9 rounded-lg flex items-center justify-center {{ $cc['icon'] }} mb-3">
                        @if ($card['icon'] === 'box')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-8.25-4.5-8.25 4.5m16.5 0l-8.25 4.5m8.25-4.5v9l-8.25 4.5m0-9L3.75 7.5m8.25 4.5v9M3.75 7.5v9l8.25 4.5" />
                            </svg>
                        @elseif ($card['icon'] === 'check')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @elseif ($card['icon'] === 'warning')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z" />
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mb-0.5">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
                    <span class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold {{ $cc['link'] }}">
                        Lihat detail →
                    </span>
                </button>
            @endforeach
        </div>

        {{-- Modal daftar komoditas per status --}}
        @foreach ($modalData as $key => $modal)
            <div id="modal-{{ $key }}" class="fixed inset-0 z-50 items-center justify-center p-4" style="background-color:rgba(0,0,0,0.4); display:none;" onclick="closeModal('{{ $key }}')">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[80vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                        <p class="font-semibold">{{ $modal['title'] }}</p>
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
                                    ● {{ $item['status'] }}
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
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-6">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <p class="font-semibold text-slate-800">Tren Nilai Neraca Seluruh Komoditas</p>
                <p class="text-xs text-slate-500 mb-4">Sumbu X: Periode (bulan) · Sumbu Y: Nilai neraca (kumulatif)</p>
                @if (count($trendLabels) === 0)
                    <div class="flex items-center justify-center h-[220px] text-slate-400 text-sm">
                        Tidak ada data sesuai filter.
                    </div>
                @else
                    <canvas id="trendChart" height="220"></canvas>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $detailData['komoditas'] ?? '-' }}</p>
                        <p class="text-xs text-slate-500">{{ $detailData['periode'] ?? '' }} · Ton</p>
                    </div>
                    @if ($detailData)
                        @php
                            $statusStyle = [
                                'Aman' => 'bg-green-50 text-green-700 border border-green-200',
                                'Waspada' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                                'Rentan' => 'bg-red-50 text-red-700 border border-red-200',
                            ][$detailData['status']];
                        @endphp
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusStyle }}">
                            ● {{ $detailData['status'] }}
                        </span>
                    @endif
                </div>

                @if ($detailData)
                    <canvas id="detailChart" height="200" class="mt-4"></canvas>

                    <div class="mt-4 rounded-xl bg-green-50 border border-green-200 p-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500">Nilai Neraca</p>
                            <p class="font-bold text-green-700">{{ number_format($detailData['nilai_neraca'], 0, ',', '.') }} Ton</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500">Ketahanan Pangan</p>
                            <p class="font-bold text-green-700">
                                {{ $detailData['ketahanan_hari'] > 0 ? $detailData['ketahanan_hari'].' hari' : 'Stok habis' }}
                            </p>
                        </div>
                    </div>
                @else
                    <div class="mt-6 flex items-center justify-center text-slate-300 text-xs text-center h-32">
                        Pilih komoditas pada filter<br>untuk melihat detail breakdown
                    </div>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div id="tabel-data" class="bg-white rounded-2xl shadow-sm border border-slate-100 mt-8 overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="font-semibold text-slate-800">Informasi Neraca Pangan Kota Kediri</p>
                    <p class="text-xs text-slate-500">Menampilkan {{ $rows->count() }} data · telah melalui proses verifikasi</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-y border-slate-200 bg-blue-50/40 text-slate-500 text-xs uppercase tracking-wide">
                            <th class="px-6 py-3 text-left font-semibold">No</th>
                            <th class="px-4 py-3 text-left font-semibold">Periode</th>
                            <th class="px-4 py-3 text-left font-semibold">Komoditas</th>
                            <th class="px-4 py-3 text-right font-semibold">Stok Awal</th>
                            <th class="px-4 py-3 text-right font-semibold">Produksi</th>
                            <th class="px-4 py-3 text-right font-semibold">Masuk</th>
                            <th class="px-4 py-3 text-right font-semibold">Keluar</th>
                            <th class="px-4 py-3 text-right font-semibold">Keb. RT</th>
                            <th class="px-4 py-3 text-right font-semibold">Keb. Non-RT</th>
                            <th class="px-4 py-3 text-right font-semibold">Nilai Neraca</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                $badge = $statusBadgeMap[$row['status']] ?? 'bg-slate-50 text-slate-600 border border-slate-200';
                                $nilaiColor = $row['nilai_neraca'] < 0 ? 'text-red-600' : 'text-slate-800';
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-blue-50/30 transition-colors">
                                <td class="px-6 py-3 text-slate-400">{{ $row['no'] }}</td>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $row['periode'] }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $row['komoditas'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['stok_awal'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['produksi'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['masuk'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['keluar'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['keb_rt'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['keb_non_rt'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-bold {{ $nilaiColor }}">{{ number_format($row['nilai_neraca'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full {{ $badge }}">
                                        ● {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-10 text-slate-400 text-sm">Tidak ada data sesuai filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 text-xs text-slate-400 border-t border-slate-100">
                Data diperbarui: {{ $lastUpdated }} · Sumber: Dinas Ketahanan Pangan dan Pertanian Kota Kediri
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-blue-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-6 flex items-center justify-between text-sm">
            <div>
                <p class="font-semibold text-slate-800">S-KOLAK · Kota Kediri</p>
                <p class="text-xs text-slate-500">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <p class="text-xs text-slate-500">© {{ date('Y') }} Pemerintah Kota Kediri. Seluruh data bersumber dari sistem neraca pangan resmi.</p>
        </div>
    </footer>

    <script>
        function openModal(key) {
            document.getElementById('modal-' + key).style.display = 'flex';
        }
        function closeModal(key) {
            document.getElementById('modal-' + key).style.display = 'none';
        }

        const trendLabels = @json($trendLabels);
        const trendValues = @json($trendValues);

        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Nilai Neraca (kumulatif)',
                        data: trendValues,
                        borderColor: '#1d4ed8',
                        backgroundColor: 'rgba(29, 78, 216, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        @if ($detailData)
        new Chart(document.getElementById('detailChart'), {
            type: 'bar',
            data: {
                labels: ['Stok Awal', 'Produksi', 'Masuk', 'Keluar', 'Keb. RT', 'Keb. Non-RT'],
                datasets: [{
                    data: [
                        {{ $detailData['stok_awal'] }},
                        {{ $detailData['produksi'] }},
                        {{ $detailData['masuk'] }},
                        {{ $detailData['keluar'] }},
                        {{ $detailData['keb_rt'] }},
                        {{ $detailData['keb_non_rt'] }}
                    ],
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
        @endif
    </script>
</body>
</html>