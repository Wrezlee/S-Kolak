<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <td colspan="14" style="font-size:14px; font-weight:bold; background-color:#2563EB; color:#ffffff;">
                DATA NERACA PANGAN - S-KOLAK KOTA KEDIRI
            </td>
        </tr>
        <tr>
            <td colspan="14" style="font-size:11px; color:#475569;">
                Dinas Ketahanan Pangan dan Pertanian Kota Kediri &middot; Diunduh {{ $generatedAt }}
            </td>
        </tr>
        <tr><td colspan="14"></td></tr>
        <tr style="background-color:#DBEAFE; font-weight:bold;">
            <td>No</td>
            <td>Periode</td>
            <td>Komoditas</td>
            <td>Stok Awal</td>
            <td>Produksi</td>
            <td>Masuk</td>
            <td>Keluar</td>
            <td>Keb. Rumah Tangga</td>
            <td>Keb. Non-RT</td>
            <td>Nilai Neraca</td>
            <td>Status</td>
            <td>Operator</td>
            <td>Verifikator</td>
            <td>Tanggal Input</td>
        </tr>
        @forelse ($rows as $r)
            <tr>
                <td>{{ $r['no'] }}</td>
                <td>{{ $r['periode'] }}</td>
                <td>{{ $r['komoditas'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $r['stok_awal'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $r['produksi'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $r['masuk'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $r['keluar'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $r['keb_rt'] }}</td>
                <td style="mso-number-format:'#,##0';">{{ $r['keb_non_rt'] }}</td>
                <td style="mso-number-format:'#,##0';font-weight:bold;">{{ $r['nilai_neraca'] }}</td>
                <td>{{ $r['status'] }}</td>
                <td>{{ $r['operator'] }}</td>
                <td>{{ $r['verifikator'] }}</td>
                <td>{{ $r['tanggal'] }}</td>
            </tr>
        @empty
            <tr><td colspan="14">Tidak ada data neraca pangan untuk filter yang dipilih.</td></tr>
        @endforelse
    </table>
</body>
</html>