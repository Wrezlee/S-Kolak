<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - S-KOLAK Kota Kediri</title>

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
    </style>
</head>
<body class="h-screen overflow-hidden" style="background-color:#F5F9FF;">

@php
    $notifCount = $notifCount ?? 2;
    $activeMenu = 'users';
    $roleBadge = [
        'admin'       => 'bg-purple-50 text-purple-700 border-purple-200',
        'operator'    => 'bg-blue-50 text-blue-700 border-blue-200',
        'verifikator' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
    ];
    $roleLabel = [
        'admin'       => 'Admin',
        'operator'    => 'Operator',
        'verifikator' => 'Verifikator',
    ];
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
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Manajemen Pengguna</h2>
                <p class="text-xs text-slate-400">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ Route::has('admin.notifikasi') ? route('admin.notifikasi') : '#' }}" class="relative w-9 h-9 flex items-center justify-center rounded-xl hover:bg-blue-50 transition-colors">
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
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-5">

            @if (session('status'))
                <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-green-50 border border-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 4.5-4.5m4.5 2.25a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('status') }}</p>
                </div>
            @endif

            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold" style="color:#1E3A5F;">Manajemen Pengguna</h1>
                    <p class="text-sm text-slate-500">{{ $users->total() }} pengguna terdaftar dalam sistem</p>
                </div>
                <button type="button" onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow-sm hover:shadow-md transition-all" style="background-color:#2563EB;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Pengguna
                </button>
            </div>

            <div class="bg-white rounded-xl border border-blue-100 shadow-sm">
                {{-- Search & filter --}}
                <form method="GET" class="p-4 border-b border-blue-50 flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2 flex-1 min-w-[180px] px-3 py-2 rounded-lg border border-blue-100 bg-blue-50/30">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-[15px] h-[15px] text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari nama atau ID pengguna..."
                               class="flex-1 text-sm bg-transparent outline-none text-slate-700 placeholder-slate-400">
                    </div>
                    <select name="role" onchange="this.form.submit()" class="skolak-select px-3 py-2 rounded-lg border border-blue-100 text-sm text-slate-600 outline-none">
                        <option value="">Semua Role</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="operator" {{ request('role') === 'operator' ? 'selected' : '' }}>Operator</option>
                        <option value="verifikator" {{ request('role') === 'verifikator' ? 'selected' : '' }}>Verifikator</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border border-blue-200 text-blue-600 hover:bg-blue-50 transition-colors">Cari</button>
                    @if (request('search') || request('role'))
                        <a href="{{ route('admin.users') }}" class="text-xs text-slate-400 hover:text-slate-600">Reset</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr style="background-color:#F0F7FF;">
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Role</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Login Terakhir</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $i => $user)
                                @php
                                    // Dibangun via json_encode manual (bukan @json()) karena directive
                                    // @json() Blade rusak untuk array dengan lebih dari satu key —
                                    // isinya di-explode berdasarkan koma sehingga terpotong.
                                    $editPayload = json_encode([
                                        'id'       => $user->id,
                                        'username' => $user->login_id,
                                        'name'     => $user->name,
                                        'role'     => $user->role,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);

                                    $deletePayload = json_encode([
                                        'id'   => $user->id,
                                        'name' => $user->name,
                                    ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG);
                                @endphp
                                <tr class="border-t border-blue-50 hover:bg-blue-50/30 transition-colors">
                                    <td class="px-4 py-3 text-slate-400">{{ $users->firstItem() + $i }}</td>
                                    <td class="px-4 py-3 font-mono text-blue-600">{{ $user->login_id }}</td>
                                    <td class="px-4 py-3 font-medium" style="color:#1E3A5F;">{{ $user->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium border {{ $roleBadge[$user->role] ?? 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                            {{ $roleLabel[$user->role] ?? ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">
                                        {{ $user->last_login_at ? \Illuminate\Support\Carbon::parse($user->last_login_at)->translatedFormat('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1.5">
                                            <button type="button"
                                                    onclick='openEdit({!! $editPayload !!})'
                                                    class="p-1.5 rounded-lg border border-blue-200 text-blue-500 hover:bg-blue-50 transition-colors" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                            </button>
                                            <button type="button"
                                                    onclick='openDelete({!! $deletePayload !!})'
                                                    class="p-1.5 rounded-lg border border-red-100 text-red-400 hover:bg-red-50 transition-colors" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-[13px] h-[13px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">Tidak ada pengguna ditemukan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="p-4 border-t border-blue-50">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</div>

{{-- ============ MODAL TAMBAH PENGGUNA ============ --}}
<div id="modalTambah" class="{{ $errors->has('login_id') ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold" style="color:#1E3A5F;">Tambah Pengguna Baru</h3>
            <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs text-slate-500 block mb-1">ID Pengguna <span class="text-red-500">*</span></label>
                <input type="text" name="login_id" value="{{ old('login_id') }}" placeholder="cth. op004" required
                       class="w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
                @error('login_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap pengguna" required
                       class="w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Role <span class="text-red-500">*</span></label>
                <select name="role" required class="skolak-select w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="operator" {{ old('role', 'operator') === 'operator' ? 'selected' : '' }}>Operator</option>
                    <option value="verifikator" {{ old('role') === 'verifikator' ? 'selected' : '' }}>Verifikator</option>
                </select>
                @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Password Awal <span class="text-red-500">*</span></label>
                <input type="password" name="password" placeholder="Minimal 8 karakter" required
                       class="w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
                @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-xs text-slate-500 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-xs text-white font-semibold" style="background-color:#2563EB;">Tambah</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL EDIT PENGGUNA ============ --}}
<div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold" style="color:#1E3A5F;">Edit Pengguna</h3>
            <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="editForm" method="POST" action="" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs text-slate-500 block mb-1">ID Pengguna</label>
                <input id="editUsername" type="text" disabled class="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs bg-slate-50 text-slate-400">
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Nama Lengkap</label>
                <input id="editName" name="name" type="text" required class="w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Role</label>
                <select id="editRole" name="role" class="skolak-select w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
                    <option value="admin">Admin</option>
                    <option value="operator">Operator</option>
                    <option value="verifikator">Verifikator</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500 block mb-1">Password Baru (opsional)</label>
                <input name="password" type="password" placeholder="Kosongkan jika tidak diubah"
                       class="w-full px-3 py-2 rounded-lg border border-blue-200 text-xs outline-none focus:border-blue-400">
            </div>
            <div class="flex gap-2 justify-end pt-2">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-200 text-xs text-slate-500 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg text-xs text-white font-semibold" style="background-color:#2563EB;">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL HAPUS PENGGUNA ============ --}}
<div id="modalDelete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xs p-6 space-y-4">
        <h3 class="text-sm font-bold text-red-600">Hapus Pengguna</h3>
        <p class="text-xs text-slate-600">Yakin ingin menghapus <strong id="deleteName"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
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
    const usersBaseUrl = @json(url('admin/users'));

    function openEdit(user) {
        document.getElementById('editUsername').value = user.username;
        document.getElementById('editName').value = user.name;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editForm').action = usersBaseUrl + '/' + user.id;
        document.getElementById('modalEdit').classList.remove('hidden');
    }

    function openDelete(user) {
        document.getElementById('deleteName').textContent = user.name;
        document.getElementById('deleteForm').action = usersBaseUrl + '/' + user.id;
        document.getElementById('modalDelete').classList.remove('hidden');
    }
</script>

</body>
</html>