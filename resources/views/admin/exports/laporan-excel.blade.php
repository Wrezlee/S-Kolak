<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <td colspan="13" style="font-size:14px; font-weight:bold; background-color:#2563EB; color:#ffffff;">
                LAPORAN NERACA PANGAN - S-KOLAK KOTA KEDIRI
            </td>
        </tr>
        <tr>
            <td colspan="13" style="font-size:11px; color:#475569;">
                Dinas Ketahanan Pangan dan Pertanian Kota Kediri &middot; Diunduh {{ $generatedAt }}
            </td>
        </tr>
        @if ($filters['tahun_awal'] || $filters['tahun_akhir'] || $filters['status'])
            <tr>
                <td colspan="13" style="font-size:11px; color:#475569;">
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
                </td>
            </tr>
        @endif
        <tr><td colspan="13"></td></tr>
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
        </tr>
        @forelse ($rows as $r)
            <tr>
                <td>{{ $r['no'] }}</td>
                <td style="mso-number-format:'\@';">{{ $r['periode'] }}</td>
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
            </tr>
        @empty
            <tr><td colspan="13">Tidak ada data sesuai filter yang dipilih.</td></tr>
        @endforelse
    </table>
</body>
</html>
