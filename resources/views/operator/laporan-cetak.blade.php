<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Neraca Saya - S-KOLAK</title>
    <style>
        @page { size: A4; margin: 18mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1E3A5F; margin: 0; padding: 24px; font-size: 12px; }
        header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #2563EB; padding-bottom: 12px; margin-bottom: 16px; }
        header h1 { font-size: 16px; margin: 0 0 2px; }
        header p { margin: 0; color: #64748B; font-size: 11px; }
        .meta { margin-bottom: 16px; font-size: 11px; color: #475569; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #DBEAFE; padding: 6px 8px; text-align: left; }
        th { background-color: #EFF6FF; font-weight: 600; }
        td.num { text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-valid { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }
        .badge-menunggu { background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA; }
        .badge-revisi { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
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
            <h1>Laporan Neraca Pangan Saya</h1>
            <p>Dinas Ketahanan Pangan dan Pertanian Kota Kediri</p>
        </div>
        <div style="text-align:right;">
            <p><strong>S-KOLAK</strong></p>
            <p>Kota Kediri</p>
        </div>
    </header>

    <div class="meta">
        <p>Dicetak oleh: <strong>{{ $namaOperator }}</strong> (ID: <strong>{{ $loginIdCetak }}</strong>)</p>
        <p>Dicetak pada: {{ \Illuminate\Support\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB</p>
        <p>Total entri: {{ $items->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Periode</th>
                <th>Komoditas</th>
                <th>Status</th>
                <th>Verifikator</th>
                <th>Tanggal Input</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $i => $n)
                @php
                    $statusCls = match ($n->status) {
                        'valid'    => 'badge-valid',
                        'menunggu' => 'badge-menunggu',
                        'revisi'   => 'badge-revisi',
                        default    => '',
                    };
                    $statusLabel = match ($n->status) {
                        'valid'    => 'Valid',
                        'menunggu' => 'Menunggu Verifikasi',
                        'revisi'   => 'Perlu Revisi',
                        default    => ucfirst($n->status),
                    };
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode) }}</td>
                    <td>{{ $n->komoditas->nama ?? '-' }}</td>
                    <td><span class="badge {{ $statusCls }}">{{ $statusLabel }}</span></td>
                    <td>{{ $n->verifikator->name ?? '—' }}</td>
                    <td>{{ optional($n->created_at)->translatedFormat('d M Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#94A3B8;">Tidak ada data sesuai filter.</td></tr>
            @endforelse
        </tbody>
    </table>

    <footer>
        Dokumen ini dihasilkan otomatis oleh S-KOLAK — Sistem Ketersediaan Stok dan Laporan Aktual, Kota Kediri.
    </footer>

</body>
</html>