<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Riwayat Verifikasi - S-KOLAK Kota Kediri</title>

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
    $userName = auth()->check() ? auth()->user()->name : 'Verifikator';
    $komoditasNama = $item->komoditas->nama ?? '-';
    $operatorNama = $item->operator->name ?? '-';
    $verifikatorNama = $item->verifikator->name ?? '-';
    $tanggalInput = optional($item->created_at)->translatedFormat('d M Y');
    $tanggalVerifikasi = optional($item->diverifikasi_pada)->translatedFormat('d M Y');
    $periode = $item->periode instanceof \Illuminate\Support\Carbon
        ? $item->periode
        : \Illuminate\Support\Carbon::parse($item->periode);
    $nilai = $item->nilai_neraca;

    $badge = $item->status === 'valid'
        ? ['label' => 'Valid',        'cls' => 'bg-green-50 text-green-700 border-green-200']
        : ['label' => 'Perlu Revisi', 'cls' => 'bg-red-50 text-red-700 border-red-200'];

    $fields = [
        ['label' => 'Stok Awal',          'val' => $item->stok_awal,                  'color' => 'text-slate-700'],
        ['label' => 'Produksi',           'val' => $item->produksi,                   'color' => 'text-blue-600'],
        ['label' => 'Barang Masuk',       'val' => $item->masuk,                      'color' => 'text-blue-600'],
        ['label' => 'Barang Keluar',      'val' => $item->keluar,                     'color' => 'text-red-600'],
        ['label' => 'Keb. Rumah Tangga',  'val' => $item->kebutuhan_rumah_tangga,     'color' => 'text-orange-600'],
        ['label' => 'Keb. Non-RT',        'val' => $item->kebutuhan_non_rumah_tangga, 'color' => 'text-orange-600'],
    ];
@endphp

<div class="flex h-screen overflow-hidden">
    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="h-14 border-b border-blue-100 bg-white flex items-center px-4 gap-3 flex-shrink-0 shadow-sm">
            <div class="flex-1">
                <h2 class="text-sm font-bold" style="color:#1E3A5F;">Detail Riwayat Verifikasi</h2>
                <p class="text-xs text-slate-400">Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            </div>
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background-color:#2563EB;">
                {{ strtoupper(substr($userName, 0, 1)) }}
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            <div class="max-w-2xl mx-auto space-y-5">

                <div class="flex items-center gap-3">
                    <a href="{{ route('verifikator.riwayat') }}" class="p-2 rounded-xl hover:bg-blue-50 text-blue-500 transition-colors text-sm flex items-center gap-1">
                        ← Kembali
                    </a>
                    <h1 class="text-xl font-bold" style="color:#1E3A5F;">Detail Riwayat Verifikasi</h1>
                </div>

                <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-6 space-y-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-base font-bold" style="color:#1E3A5F;">{{ $komoditasNama }}</h2>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $periode->translatedFormat('F Y') }} · Kota Kediri</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badge['cls'] }}">{{ $badge['label'] }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl p-3" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Operator</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $operatorNama }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Tanggal Input</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $tanggalInput }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Diverifikasi Oleh</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $verifikatorNama }}</p>
                        </div>
                        <div class="rounded-xl p-3" style="background-color:#F0F7FF;">
                            <p class="text-xs text-slate-400">Tanggal Verifikasi</p>
                            <p class="text-sm font-semibold mt-0.5 text-black">{{ $tanggalVerifikasi ?? '-' }}</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Data Neraca Pangan</h3>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($fields as $f)
                                <div class="rounded-xl p-3 border border-blue-50">
                                    <p class="text-xs text-slate-400">{{ $f['label'] }}</p>
                                    <p class="text-sm font-bold font-mono mt-0.5 {{ $f['color'] }}">{{ number_format($f['val'], 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl p-4 border-2" style="border-color:{{ $nilai > 0 ? '#86EFAC' : '#FCA5A5' }}; background-color:{{ $nilai > 0 ? '#F0FDF4' : '#FEF2F2' }};">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-slate-600">Nilai Neraca Pangan</p>
                            <p class="text-2xl font-bold font-mono text-black">{{ number_format($nilai, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    @if ($item->keterangan)
                        <div class="rounded-xl p-3 border {{ $item->status === 'revisi' ? 'bg-orange-50 border-orange-200' : 'bg-slate-50 border-slate-100' }}">
                            <p class="text-xs font-semibold mb-1" style="color: {{ $item->status === 'revisi' ? '#C2410C' : '#64748B' }};">
                                {{ $item->status === 'revisi' ? 'Catatan Verifikator (Alasan Revisi)' : 'Catatan Verifikator' }}
                            </p>
                            <p class="text-sm" style="color: {{ $item->status === 'revisi' ? '#9A3412' : '#475569' }};">{{ $item->keterangan }}</p>
                        </div>
                    @endif

                    @if ($item->status === 'revisi')
                        <div class="rounded-xl p-4 bg-blue-50 border border-blue-200">
                            <p class="text-xs text-blue-700 font-semibold mb-1">Info</p>
                            <p class="text-xs text-blue-600">Data ini telah dikembalikan ke operator untuk diperbaiki. Status akan berubah kembali menjadi "Menunggu Verifikasi" setelah operator mengirim ulang.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>