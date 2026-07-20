<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 24px 28px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1E293B; font-size: 10px; }
        .header { display: flex; align-items: center; margin-bottom: 4px; }
        h1 { font-size: 16px; color: #1E3A5F; margin: 0 0 2px 0; }
        p.subtitle { font-size: 10px; color: #64748B; margin: 0 0 14px 0; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background-color: #2563EB; color: #ffffff; text-align: left;
            padding: 6px 5px; font-size: 9px; text-transform: uppercase;
        }
        tbody td {
            padding: 5px; border-bottom: 1px solid #E2E8F0; font-size: 9.5px;
        }
        tbody tr:nth-child(even) { background-color: #F5F9FF; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block; padding: 2px 6px; border-radius: 8px; font-size: 8.5px;
        }
        .badge-valid    { background-color: #DCFCE7; color: #15803D; }
        .badge-menunggu { background-color: #FFEDD5; color: #C2410C; }
        .badge-revisi   { background-color: #FEE2E2; color: #B91C1C; }
        .footer { margin-top: 10px; font-size: 8.5px; color: #94A3B8; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>Data Neraca Pangan - S-KOLAK Kota Kediri</h1>
            <p class="subtitle">Dinas Ketahanan Pangan dan Pertanian Kota Kediri &middot; Diunduh {{ $generatedAt }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Periode</th>
                <th>Komoditas</th>
                <th class="text-right">Stok Awal</th>
                <th class="text-right">Produksi</th>
                <th class="text-right">Masuk</th>
                <th class="text-right">Keluar</th>
                <th class="text-right">Keb. RT</th>
                <th class="text-right">Keb. Non-RT</th>
                <th class="text-right">Nilai Neraca</th>
                <th>Status</th>
                <th>Operator</th>
                <th>Verifikator</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                @php
                    $badgeClass = match ($r['status']) {
                        'Valid' => 'badge-valid',
                        'Menunggu Verifikasi' => 'badge-menunggu',
                        'Perlu Revisi' => 'badge-revisi',
                        default => '',
                    };
                @endphp
                <tr>
                    <td>{{ $r['no'] }}</td>
                    <td>{{ $r['periode'] }}</td>
                    <td>{{ $r['komoditas'] }}</td>
                    <td class="text-right">{{ number_format($r['stok_awal'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['produksi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['masuk'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['keb_rt'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($r['keb_non_rt'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($r['nilai_neraca'], 0, ',', '.') }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $r['status'] }}</span></td>
                    <td>{{ $r['operator'] }}</td>
                    <td>{{ $r['verifikator'] }}</td>
                    <td>{{ $r['tanggal'] }}</td>
                </tr>
            @empty
                <tr><td colspan="14">Tidak ada data neraca pangan untuk filter yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Total {{ count($rows) }} data &middot; Dicetak otomatis oleh sistem S-KOLAK</div>
</body>
</html>