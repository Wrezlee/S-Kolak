<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Neraca Saya - S-KOLAK</title>
    <style>
        @page { size: 215mm 330mm; margin: 18mm 14mm; }
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1E3A5F; margin: 0; padding: 24px; font-size: 11px; }

        .letterhead { text-align: center; border-bottom: 3px double #2563EB; padding-bottom: 10px; margin-bottom: 16px; }
        .letterhead .lembaga { margin: 0; font-size: 11px; letter-spacing: 0.5px; color: #475569; }
        .letterhead h1 { margin: 2px 0 0; font-size: 15px; letter-spacing: 0.3px; }
        .letterhead .bidang { margin: 1px 0 8px; font-size: 11px; color: #475569; }
        .letterhead h2 { margin: 4px 0 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .letterhead .tahun { margin: 2px 0 0; font-size: 11px; font-weight: 600; }

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

        footer { margin-top: 28px; }
        .cetak-oleh { border-top: 1px solid #DBEAFE; padding-top: 10px; font-size: 11px; }
        .footer-note { margin-top: 12px; font-size: 10px; color: #94A3B8; text-align: center; }

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

    <div class="letterhead">
        <p class="lembaga">PEMERINTAH KOTA KEDIRI</p>
        <h1>Dinas Ketahanan Pangan dan Pertanian</h1>
        <p class="bidang">Bidang Ketahanan Pangan</p>
        <h2>Laporan Neraca Pangan Saya</h2>
        <p class="tahun">Tahun {{ $tahun }}</p>
    </div>

    <div class="meta">
        <p>Dicetak pada: {{ \Illuminate\Support\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB</p>
        <p>Total entri: {{ $items->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Komoditas</th>
                <th>Periode</th>
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
                    <td>{{ $n->komoditas->nama ?? '-' }}</td>
                    <td>{{ \App\Http\Controllers\Admin\DataNeracaController::formatPeriode($n->periode) }}</td>
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
        <div class="cetak-oleh">
            Dicetak oleh: <strong>{{ $namaOperator }}</strong> (ID: <strong>{{ $loginIdCetak }}</strong>)
        </div>
        <p class="footer-note">
            Dokumen ini dihasilkan otomatis oleh S-KOLAK — Sistem Ketersediaan Stok dan Laporan Aktual, Kota Kediri.
        </p>
    </footer>

</body>
</html>
