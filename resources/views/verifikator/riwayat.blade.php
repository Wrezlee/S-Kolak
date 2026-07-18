<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Verifikasi - S-KOLAK Kota Kediri</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-screen overflow-hidden" style="background-color:#F5F9FF;">

@php
    // Nilai default apabila controller belum mengirim data.
    $riwayat = $riwayat ?? collect([
        (object) ['id' => 1, 'status' => 'valid',
            'komoditas' => (object) ['nama' => 'Beras'],
            'operator'  => (object) ['name' => 'Siti Rahayu, S.P'],
            'periode'   => \Illuminate\Support\Carbon::parse('2025-01-01')],
        (object) ['id' => 2, 'status' => 'valid',
            'komoditas' => (object) ['nama' => 'Beras'],
            'operator'  => (object) ['name' => 'Siti Rahayu, S.P'],
            'periode'   => \Illuminate\Support\Carbon::parse('2025-02-01')],
        (object) ['id' => 10, 'status' => 'revisi',
            'komoditas' => (object) ['nama' => 'Kedelai'],
            'operator'  => (object) ['name' => 'Siti Rahayu, S.P'],
            'periode'   => \Illuminate\Support\Carbon::parse('2025-03-01')],
    ]);

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
                    'pesan' => $n->pesan,
                    'waktu' => \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $tipe,
                ];
            })->values()
            : collect()
    )));
    $activeMenu = 'riwayat';
    $userName = auth()->check() ? auth()->user()->name : 'Verifikator';
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
            <p class="text-xs font-semibold text-blue-600">Verifikator</p>
            <p class="text-xs font-medium truncate mt-0.5" style="color:#1E3A5F;">{{ $userName }}</p>
        </div>

        <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto mt-2">
            @php
                $menuItems = [
                    ['key' => 'dashboard',  'label' => 'Dashboard',                 'route' => 'verifikator.dashboard',  'badge' => null],
                    ['key' => 'menunggu',   'label' => 'Data Menunggu Verifikasi',  'route' => 'verifikator.menunggu',   'badge' => null],
                    ['key' => 'riwayat',    'label' => 'Riwayat Verifikasi',        'route' => 'verifikator.riwayat',    'badge' => null],
                    ['key' => 'notifikasi', 'label' => 'Notifikasi',                'route' => 'verifikator.notifikasi', 'badge' => $notifCount],
                ];
                $menuIcons = [
                    'dashboard'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
                    'menunggu'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'riwayat'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Riwayat Verifikasi</h2>
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
                        <a href="{{ Route::has('verifikator.notifikasi') ? route('verifikator.notifikasi') : '#' }}" class="block text-center text-xs font-semibold text-blue-600 hover:bg-blue-50 py-2.5 border-t border-blue-50 transition-colors">Lihat Semua</a>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background-color:#2563EB;">
                    {{ strtoupper(substr($userName, 0, 1)) }}
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5">

            <div>
                <h1 class="text-xl font-bold" style="color:#1E3A5F;">Riwayat Verifikasi</h1>
                <p class="text-sm text-slate-500">{{ $riwayat->count() }} data telah diverifikasi · Kota Kediri</p>
            </div>

            <div class="bg-white rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background-color:#F0F7FF;">
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Periode</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Komoditas</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Operator</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($riwayat as $i => $n)
                                @php
                                    $periode = $n->periode instanceof \Illuminate\Support\Carbon
                                        ? $n->periode
                                        : \Illuminate\Support\Carbon::parse($n->periode);
                                @endphp
                                <tr class="border-t border-blue-50 hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3 font-medium" style="color:#1E3A5F;">{{ $periode->translatedFormat('M Y') }}</td>
                                    <td class="px-4 py-3">{{ $n->komoditas->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $n->operator->name ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ Route::has('verifikator.riwayat.show') ? route('verifikator.riwayat.show', $n->id) : '#' }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-blue-200 text-blue-600 text-xs font-medium hover:bg-blue-50 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-[12px] h-[12px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-10 text-center text-slate-400">Belum ada riwayat verifikasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

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