<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Neraca Saya - S-KOLAK</title>
    <style>
        @page { size: 215mm 330mm; margin: 16mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1E3A5F; margin: 0; padding: 24px; font-size: 10.5px; }

        header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #2563EB; padding-bottom: 12px; margin-bottom: 16px; }
        header h1 { font-size: 16px; margin: 0 0 2px; }
        header p { margin: 0; color: #64748B; font-size: 11px; }
        header .brand { display: flex; align-items: center; gap: 8px; }
        header .brand img { width: 40px; height: 40px; object-fit: contain; flex-shrink: 0; }
        header .brand-text { text-align: right; }

        .meta { margin-bottom: 14px; font-size: 10.5px; color: #475569; }
        .meta p { margin: 2px 0; }

        table { width: 100%; border-collapse: collapse; font-size: 9.5px; table-layout: fixed; }
        th, td { border: 1px solid #DBEAFE; padding: 3px 4px; text-align: center; overflow-wrap: break-word; }
        th { background-color: #EFF6FF; font-weight: 600; }
        td:nth-child(2) { text-align: left; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-valid { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
        .badge-menunggu { background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA; }
        .badge-revisi { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
        td.num { text-align: center; font-variant-numeric: tabular-nums; }
        .no-print { margin-bottom: 16px; }

        footer { margin-top: 28px; }
        .cetak-oleh { border-top: 1px solid #DBEAFE; padding-top: 10px; font-size: 11px; }
        .cetak-oleh p { margin: 2px 0; }
        .footer-note { margin-top: 12px; font-size: 9.5px; color: #94A3B8; text-align: center; }

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
            <h1>Laporan Neraca Pangan Saya</h1>
            <p>Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
            <p>Bidang Ketahanan Pangan</p>
        </div>
        <div class="brand">
            @if (file_exists(public_path('images/logo-kediri.png')))
                <img src="{{ asset('images/logo-kediri.png') }}" alt="Logo Kota Kediri">
            @endif
            <div class="brand-text">
                <p><strong>S-KOLAK</strong></p>
                <p>Kota Kediri</p>
            </div>
        </div>
    </header>

    @php
        $statusBadge = [
            'valid'    => ['label' => 'Valid',              'cls' => 'badge-valid'],
            'menunggu' => ['label' => 'Menunggu Verifikasi', 'cls' => 'badge-menunggu'],
            'revisi'   => ['label' => 'Perlu Revisi',        'cls' => 'badge-revisi'],
        ];
    @endphp

    <div class="meta">
        <p>Tahun: {{ $tahun }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Komoditas</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Stok Awal</th>
                <th>Produksi</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Keb. Rumah Tangga</th>
                <th>Keb. Non-RT</th>
                <th>Neraca</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $i => $n)
                @php
                    $badge = $statusBadge[$n['status']] ?? ['label' => ucfirst($n['status']), 'cls' => ''];
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $n['komoditas'] }}</td>
                    <td>{{ $n['periode'] }}</td>
                    <td><span class="badge {{ $badge['cls'] }}">{{ $badge['label'] }}</span></td>
                    <td class="num">{{ number_format($n['stok_awal'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($n['produksi'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($n['masuk'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($n['keluar'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($n['keb_rt'], 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($n['keb_non_rt'], 0, ',', '.') }}</td>
                    <td class="num" style="font-weight:700;">{{ number_format($n['nilai_neraca'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="11" style="text-align:center;color:#94A3B8;">Tidak ada data sesuai filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        <div class="cetak-oleh">
            <p>Dicetak pada: {{ $generatedAt }} WIB</p>
            <p><strong>{{ $namaOperator }}</strong> (ID: <strong>{{ $loginIdCetak }}</strong>)</p>
            <p>Sistem Ketersediaan Stok dan Laporan Aktual</p>
        </div>
        <p class="footer-note">
            Dokumen ini dihasilkan otomatis oleh S-KOLAK — Sistem Ketersediaan Stok dan Laporan Aktual, Kota Kediri.
        </p>
    </footer>

</body>
</html>