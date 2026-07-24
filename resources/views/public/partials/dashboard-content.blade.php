@php
    $rowsCollection = collect($rows);
    // $tableRows dipaginasi terpisah dari $rows — fallback ke $rows kalau controller lama belum mengirimnya.
    $tableRows = $tableRows ?? $rows;

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

{{-- Summary cards --}}
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
                class="text-left w-full {{ $cc['card'] }} border rounded-xl shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 hover:brightness-100 transition-all">
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
        <p class="font-semibold text-slate-800">Neraca Seluruh Komoditas</p>
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
                @forelse ($tableRows as $row)
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

    @if (method_exists($tableRows, 'hasPages') && $tableRows->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $tableRows->links() }}
        </div>
    @endif

    <div class="px-6 py-4 text-xs text-slate-400 border-t border-slate-100">
        Data diperbarui: {{ $lastUpdated }} · Sumber: Dinas Ketahanan Pangan dan Pertanian Kota Kediri
    </div>
</div>