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

            <h1 class="mt-4 text-4xl md:text-5xl font-bold leading-tight max-w-2xl">
                Sistem Ketersediaan Stok dan Laporan Aktual
            </h1>

            <p class="mt-4 max-w-2xl text-blue-100 text-sm md:text-base">
                sKOLAK adalah Sistem Informasi Neraca Pangan berdasarkan analisis prognosa data stok pangan di Kota Kediri. 
                Data disajikan bersumber dari hasil perekaman pemantauan data stok pangan dari pelaku usaha pangan di Kota Kediri.
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
                    <a href="{{ url()->current() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 px-4 py-2.5 text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </section>

    <main id="dashboard-content" class="max-w-7xl mx-auto px-6 pb-16">
        @include('public.partials.dashboard-content')
    </main>

    {{-- Footer --}}
    <footer class="bg-blue-50 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-6 py-6 flex items-center justify-between text-sm">
            <div class="flex items-center gap-3">
                <div>
                    <p class="font-semibold text-slate-800">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
                    <p class="text-xs text-slate-500">Bidang Ketahanan Pangan</p>
                </div>
            </div>
            <p class="text-xs text-slate-500">© {{ date('Y') }} Pemerintah Kota Kediri.</p>
        </div>
    </footer>

    <script>
        function openModal(key) {
            document.getElementById('modal-' + key).style.display = 'flex';
        }
        function closeModal(key) {
            document.getElementById('modal-' + key).style.display = 'none';
        }

        let trendChart = null;
        let detailChart = null;

        function renderCharts(trendLabels, trendValues, detailData) {
            if (trendChart) { trendChart.destroy(); trendChart = null; }
            if (detailChart) { detailChart.destroy(); detailChart = null; }

            const trendCanvas = document.getElementById('trendChart');
            if (trendCanvas) {
                trendChart = new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: trendLabels,
                        datasets: [{
                            label: 'Neraca (kumulatif)',
                            data: trendValues,
                            borderColor: '#1d4ed8',
                            backgroundColor: 'rgba(29, 78, 216, 0.1)',
                            fill: true,
                            tension: 0,
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }

            const detailCanvas = document.getElementById('detailChart');
            if (detailCanvas && detailData) {
                detailChart = new Chart(detailCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Stok Awal', 'Produksi', 'Masuk', 'Keluar', 'Keb. RT', 'Keb. Non-RT'],
                        datasets: [{
                            data: [
                                detailData.stok_awal,
                                detailData.produksi,
                                detailData.masuk,
                                detailData.keluar,
                                detailData.keb_rt,
                                detailData.keb_non_rt,
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
            }
        }

        // Render awal pakai data dari server (saat page load pertama)
        renderCharts(@json($trendLabels), @json($trendValues), @json($detailData));

        // ===============================
        // Filter tanpa reload (AJAX)
        // ===============================
        const filterForm = document.querySelector('form[method="GET"]');
        const dashboardContent = document.getElementById('dashboard-content');

        function submitFilter(params) {
            const url = window.location.pathname + '?' + params.toString();

            dashboardContent.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                dashboardContent.innerHTML = data.html;
                dashboardContent.style.opacity = '1';
                renderCharts(data.trendLabels, data.trendValues, data.detailData);
                history.pushState(null, '', url);
            })
            .catch(() => {
                // fallback: kalau AJAX gagal, submit biasa (reload)
                filterForm.submit();
            });
        }

        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const params = new URLSearchParams(new FormData(filterForm));
            submitFilter(params);
        });

        // Tombol Reset: kembalikan semua select ke "Semua" lalu terapkan lewat AJAX juga
        const resetLink = filterForm.querySelector('a[href]');
        if (resetLink) {
            resetLink.addEventListener('click', function (e) {
                e.preventDefault();
                filterForm.querySelectorAll('select').forEach(sel => { sel.value = 'Semua'; });
                submitFilter(new URLSearchParams());
            });
        }

        // Dukung tombol back/forward browser
        window.addEventListener('popstate', function () {
            const params = new URLSearchParams(window.location.search);
            submitFilter(params);
        });
    </script>
</body>
</html>