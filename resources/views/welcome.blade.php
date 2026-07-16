<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>S-KOLAK · Sistem Ketersediaan Stok dan Laporan Aktual</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">

    {{-- Navbar --}}
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-blue-700 flex items-center justify-center text-white text-sm font-bold">
                    SK
                </div>
                <div>
                    <p class="font-semibold leading-tight">S-KOLAK</p>
                    <p class="text-xs text-slate-500 leading-tight">Kota Kediri</p>
                </div>
            </div>
            <a href="#" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 transition">
                Masuk
            </a>
        </div>
    </header>

    {{-- Hero --}}
    <section class="bg-blue-700 text-white">
        <div class="max-w-7xl mx-auto px-6 pt-10 pb-24">
            <span class="inline-flex items-center gap-2 text-xs">
                <span class="rounded-full bg-white/15 px-3 py-1">Dashboard Publik</span>
                <span class="text-blue-100">Data terverifikasi · Kota Kediri</span>
            </span>

            <h1 class="mt-5 text-4xl md:text-5xl font-bold leading-tight max-w-2xl">
                Sistem Ketersediaan Stok dan Laporan Aktual
            </h1>

            <p class="mt-4 max-w-2xl text-blue-100">
                Informasi neraca pangan Kota Kediri yang transparan, terkini, dan telah melalui proses
                verifikasi oleh Dinas Ketahanan Pangan dan Pertanian Kota Kediri.
            </p>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-6 -mt-16 pb-16">

        {{-- Filter --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center gap-2 text-slate-700 font-semibold mb-5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter Data
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm text-slate-500 mb-1">Tahun Awal</label>
                    <select name="tahun_awal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option>Semua</option>
                        <option>2025</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-500 mb-1">Bulan Awal</label>
                    <select name="bulan_awal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option>Semua</option>
                        <option>Januari</option>
                        <option>Februari</option>
                        <option>Maret</option>
                        <option>April</option>
                        <option>Mei</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-500 mb-1">Tahun Akhir</label>
                    <select name="tahun_akhir" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option>Semua</option>
                        <option>2025</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-500 mb-1">Bulan Akhir</label>
                    <select name="bulan_akhir" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option>Semua</option>
                        <option>Januari</option>
                        <option>Februari</option>
                        <option>Maret</option>
                        <option>April</option>
                        <option>Mei</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-500 mb-1">Komoditas</label>
                    <select name="komoditas" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="Semua" {{ empty(request('komoditas')) || request('komoditas') === 'Semua' ? 'selected' : '' }}>Semua</option>
                        @foreach ($komoditasList as $k)
                            <option value="{{ $k }}" {{ request('komoditas') === $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ url()->current() }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset
                </a>
            </div>
        </form>

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-8">
            @php
                $cards = [
                    ['label' => 'Total Komoditas Terpantau', 'value' => $summary['total_komoditas'], 'color' => 'blue', 'icon' => 'box'],
                    ['label' => 'Komoditas Stok Aman', 'value' => $summary['aman'], 'color' => 'green', 'icon' => 'check'],
                    ['label' => 'Komoditas Stok Waspada', 'value' => $summary['waspada'], 'color' => 'amber', 'icon' => 'warning'],
                    ['label' => 'Komoditas Stok Rentan', 'value' => $summary['rentan'], 'color' => 'red', 'icon' => 'x'],
                ];
                $iconColorMap = [
                    'blue' => 'bg-blue-50 text-blue-600',
                    'green' => 'bg-green-50 text-green-600',
                    'amber' => 'bg-amber-50 text-amber-600',
                    'red' => 'bg-red-50 text-red-600',
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="h-10 w-10 rounded-full flex items-center justify-center {{ $iconColorMap[$card['color']] }}">
                        @if ($card['icon'] === 'box')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        @elseif ($card['icon'] === 'check')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        @elseif ($card['icon'] === 'warning')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a1 1 0 00.86 1.5h18.64a1 1 0 00.86-1.5L13.71 3.86a1 1 0 00-1.72 0z" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        @endif
                    </div>
                    <p class="mt-4 text-sm text-slate-500">{{ $card['label'] }}</p>
                    <p class="text-3xl font-bold mt-1">{{ $card['value'] }}</p>
                    <a href="#tabel-data" class="mt-3 inline-flex items-center gap-1 text-sm text-blue-700 hover:underline">
                        Lihat detail →
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-8">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <p class="font-semibold">Tren Nilai Neraca Seluruh Komoditas</p>
                <p class="text-xs text-slate-500 mb-4">Sumbu X: Periode (bulan) · Sumbu Y: Nilai neraca (kumulatif)</p>
                <canvas id="trendChart" height="220"></canvas>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold">{{ $detailData['komoditas'] ?? '-' }}</p>
                        <p class="text-xs text-slate-500">{{ $detailData['periode'] ?? '' }} · Ton</p>
                    </div>
                    @if ($detailData)
                        @php
                            $statusStyle = [
                                'Aman' => 'bg-green-50 text-green-700',
                                'Waspada' => 'bg-amber-50 text-amber-700',
                                'Rentan' => 'bg-red-50 text-red-700',
                            ][$detailData['status']];
                        @endphp
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $statusStyle }}">
                            ● {{ $detailData['status'] }}
                        </span>
                    @endif
                </div>

                <canvas id="detailChart" height="200" class="mt-4"></canvas>

                @if ($detailData)
                    <div class="mt-4 rounded-xl bg-green-50 p-4 flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500">Nilai Neraca</p>
                            <p class="font-bold text-green-700">{{ $detailData['nilai_neraca'] }} Ton</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-slate-500">Ketahanan Pangan</p>
                            <p class="font-bold text-green-700">{{ $detailData['ketahanan_hari'] }} hari</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div id="tabel-data" class="bg-white rounded-2xl border border-slate-200 shadow-sm mt-8 overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <div>
                    <p class="font-semibold">Informasi Neraca Pangan Kota Kediri</p>
                    <p class="text-xs text-slate-500">Menampilkan {{ $rows->count() }} data · telah melalui proses verifikasi</p>
                </div>
                <a href="#" class="inline-flex items-center gap-2 text-sm text-blue-700 hover:underline">
                    ⭳ Unduh (Excel)
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-y border-slate-200 text-slate-500 text-xs uppercase tracking-wide">
                            <th class="px-6 py-3 text-left font-medium">No</th>
                            <th class="px-4 py-3 text-left font-medium">Periode</th>
                            <th class="px-4 py-3 text-left font-medium">Komoditas</th>
                            <th class="px-4 py-3 text-right font-medium">Stok Awal</th>
                            <th class="px-4 py-3 text-right font-medium">Produksi</th>
                            <th class="px-4 py-3 text-right font-medium">Masuk</th>
                            <th class="px-4 py-3 text-right font-medium">Keluar</th>
                            <th class="px-4 py-3 text-right font-medium">Keb. RT</th>
                            <th class="px-4 py-3 text-right font-medium">Keb. Non-RT</th>
                            <th class="px-4 py-3 text-right font-medium">Nilai Neraca</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            @php
                                $badge = [
                                    'Aman' => 'bg-green-50 text-green-700',
                                    'Waspada' => 'bg-amber-50 text-amber-700',
                                    'Rentan' => 'bg-red-50 text-red-700',
                                ][$row['status']];
                                $nilaiColor = $row['nilai_neraca'] < 0 ? 'text-red-600' : 'text-slate-800';
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50">
                                <td class="px-6 py-3 text-blue-700">{{ $row['no'] }}</td>
                                <td class="px-4 py-3">{{ $row['periode'] }}</td>
                                <td class="px-4 py-3 font-medium">{{ $row['komoditas'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['stok_awal'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['produksi'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['masuk'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['keluar'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['keb_rt'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['keb_non_rt'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right tabular-nums font-medium {{ $nilaiColor }}">{{ number_format($row['nilai_neraca'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2.5 py-1 rounded-full {{ $badge }}">
                                        ● {{ $row['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
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
                <p class="font-semibold">S-KOLAK · Kota Kediri</p>
                <p class="text-xs text-slate-500">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <p class="text-xs text-slate-500">© {{ date('Y') }} Pemerintah Kota Kediri. Seluruh data bersumber dari sistem neraca pangan resmi.</p>
        </div>
    </footer>

    <script>
        const trendLabels = @json($trendLabels);
        const trendValues = @json($trendValues);

        new Chart(document.getElementById('trendChart'), {
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