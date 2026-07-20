<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Neraca Pangan - S-KOLAK</title>
    <style>
        @page { size: A4 landscape; margin: 14mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1E3A5F; margin: 0; padding: 24px; font-size: 11px; }
        header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #2563EB; padding-bottom: 12px; margin-bottom: 16px; }
        header h1 { font-size: 16px; margin: 0 0 2px; }
        header p { margin: 0; color: #64748B; font-size: 11px; }
        .meta { margin-bottom: 16px; font-size: 11px; color: #475569; }
        table { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        th, td { border: 1px solid #DBEAFE; padding: 5px 7px; text-align: left; }
        th { background-color: #EFF6FF; font-weight: 600; }
        td.num { text-align: right; font-variant-numeric: tabular-nums; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-valid { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
        .badge-menunggu-verifikasi { background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA; }
        .badge-perlu-revisi { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
        .no-print { margin-bottom: 16px; }
        footer { margin-top: 24px; font-size: 10px; color: #94A3B8; text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 16px;background:#2563EB;color:white;border:none;border-radius:8px;font-size:13px;cursor:pointer;">
            Cetak / Simpan sebagai PDF
        </button>
    </div>

    <header>
        <div>
            <h1>Laporan Neraca Pangan</h1>
            <p>Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
        </div>
        <div style="text-align:right;">
            <p><strong>S-KOLAK</strong></p>
            <p>Kota Kediri</p>
        </div>
    </header>

    <div class="meta">
        <p>Dicetak pada: {{ $generatedAt }} WIB</p>
        @if ($filters['tahun_awal'] || $filters['tahun_akhir'] || $filters['status'])
            <p>
                Filter:
                @if ($filters['tahun_awal'] && $filters['bulan_awal'])
                    Periode {{ $filters['bulan_awal'] }} {{ $filters['tahun_awal'] }}
                    @if ($filters['tahun_akhir'] && $filters['bulan_akhir'])
                        s.d. {{ $filters['bulan_akhir'] }} {{ $filters['tahun_akhir'] }}
                    @endif
                @endif
                @if ($filters['status'])
                    &middot; Status: {{ ucfirst($filters['status']) }}
                @endif
            </p>
        @endif
        <p>Total entri: {{ count($rows) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Periode</th>
                <th>Komoditas</th>
                <th>Stok Awal</th>
                <th>Produksi</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Keb. Rumah Tangga</th>
                <th>Keb. Non-RT</th>
                <th>Nilai Neraca</th>
                <th>Status</th>
                <th>Operator</th>
                <th>Verifikator</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                @php
                    $statusCls = 'badge-' . \Illuminate\Support\Str::slug($r['status']);
                @endphp
                <tr>
                    <td>{{ $r['no'] }}</td>
                    <td>{{ $r['periode'] }}</td>
                    <td>{{ $r['komoditas'] }}</td>
                    <td class="num">{{ number_format($r['stok_awal'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($r['produksi'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($r['masuk'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($r['keb_rt'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($r['keb_non_rt'], 0, ',', '.') }}</td>
                    <td class="num" style="font-weight:700;">{{ number_format($r['nilai_neraca'], 0, ',', '.') }}</td>
                    <td><span class="badge {{ $statusCls }}">{{ $r['status'] }}</span></td>
                    <td>{{ $r['operator'] }}</td>
                    <td>{{ $r['verifikator'] }}</td>
                </tr>
            @empty
                <tr><td colspan="13" style="text-align:center;color:#94A3B8;">Tidak ada data sesuai filter yang dipilih.</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        Dokumen ini dihasilkan otomatis oleh S-KOLAK — Sistem Ketersediaan Stok dan Laporan Aktual, Kota Kediri.
    </footer>

</body>
</html>
