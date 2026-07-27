<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <td colspan="9" style="font-size:11px; font-weight:bold;">PEMERINTAH KOTA KEDIRI</td>
        </tr>
        <tr>
            <td colspan="9" style="font-size:14px; font-weight:bold; background-color:#2563EB; color:#ffffff;">
                DINAS KETAHANAN PANGAN DAN PERTANIAN
            </td>
        </tr>
        <tr>
            <td colspan="9" style="font-size:11px; color:#475569;">Bidang Ketahanan Pangan</td>
        </tr>
        <tr>
            <td colspan="9" style="font-size:13px; font-weight:bold;">LAPORAN NERACA PANGAN</td>
        </tr>
        <tr>
            <td style="font-size:11px; color:#475569;">Bulan</td>
            <td colspan="8" style="font-size:11px;">{{ $bulanLabel }}</td>
        </tr>
        <tr>
            <td style="font-size:11px; color:#475569;">Tahun</td>
            <td colspan="8" style="font-size:11px;">{{ $filters['tahun'] }}</td>
        </tr>
        <tr>
            <td colspan="9" style="font-size:11px; color:#475569;">Dicetak pada {{ $generatedAt }}</td>
        </tr>
        <tr><td colspan="9"></td></tr>

        {{-- ===================== TABEL 1: SESUAI FILTER BULAN/TAHUN ===================== --}}
        <tr style="background-color:#DBEAFE; font-weight:bold;">
            <td>No</td>
            <td>Komoditas</td>
            <td>Stok Awal</td>
            <td>Produksi</td>
            <td>Masuk</td>
            <td>Keluar</td>
            <td>Keb. Rumah Tangga</td>
            <td>Keb. Non-RT</td>
            <td>Nilai Neraca</td>
        </tr>
        @foreach ($laporanBulanan as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r['nama'] }}</td>
                @if ($r['tersedia'])
                    <td style="mso-number-format:'#,##0';">{{ $r['stok_awal'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['produksi'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['masuk'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['keluar'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['keb_rt'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['keb_non_rt'] }}</td>
                    <td style="mso-number-format:'#,##0';font-weight:bold;">{{ $r['nilai_neraca'] }}</td>
                @else
                    <td colspan="7" style="text-align:center;">Belum tersedia</td>
                @endif
            </tr>
        @endforeach

        <tr><td colspan="9"></td></tr>
        <tr><td colspan="9"></td></tr>

        {{-- ===================== TABEL 2: RATA-RATA TAHUNAN ===================== --}}
        <tr>
            <td style="font-size:11px; color:#475569;">Tahun</td>
            <td colspan="8" style="font-size:11px;">{{ $filters['tahun'] }} datanya berupa rata rata tahunan</td>
        </tr>
        <tr>
            <td colspan="9" style="font-size:11px; color:#475569;">Dicetak pada {{ $generatedAt }}</td>
        </tr>
        <tr>
            <td colspan="9" style="font-size:10px; color:#475569; font-style:italic;">(Datanya rata-rata dari Januari - Desember)</td>
        </tr>
        <tr style="background-color:#DBEAFE; font-weight:bold;">
            <td>No</td>
            <td>Komoditas</td>
            <td>Stok Awal</td>
            <td>Produksi</td>
            <td>Masuk</td>
            <td>Keluar</td>
            <td>Keb. Rumah Tangga</td>
            <td>Keb. Non-RT</td>
            <td>Nilai Neraca</td>
        </tr>
        @foreach ($laporanTahunan as $i => $r)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $r['nama'] }}</td>
                @if ($r['tersedia'])
                    <td style="mso-number-format:'#,##0';">{{ $r['stok_awal'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['produksi'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['masuk'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['keluar'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['keb_rt'] }}</td>
                    <td style="mso-number-format:'#,##0';">{{ $r['keb_non_rt'] }}</td>
                    <td style="mso-number-format:'#,##0';font-weight:bold;">{{ $r['nilai_neraca'] }}</td>
                @else
                    <td colspan="7" style="text-align:center;">Belum tersedia</td>
                @endif
            </tr>
        @endforeach

        <tr><td colspan="9"></td></tr>
        <tr>
            <td colspan="9" style="font-size:11px;">Diunduh oleh: {{ $dicetakOleh }}</td>
        </tr>
    </table>
</body>
</html>
