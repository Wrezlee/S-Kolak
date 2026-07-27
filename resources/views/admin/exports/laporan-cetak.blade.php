<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Neraca Pangan - S-KOLAK</title>
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
        section + section { margin-top: 22px; }
        section h2 { font-size: 13px; margin: 0 0 4px; }
        section .rata-rata-label { font-size: 12px; font-weight: 700; color: #1E3A5F; margin: 0 0 8px; }

        table { width: 100%; border-collapse: collapse; font-size: 9.5px; table-layout: fixed; }
        th, td { border: 1px solid #DBEAFE; padding: 3px 4px; text-align: center; overflow-wrap: break-word; }
        th { background-color: #EFF6FF; font-weight: 600; }
        td:nth-child(2) { text-align: left; }
        td.num { text-align: center; font-variant-numeric: tabular-nums; }
        td.belum-tersedia { text-align: center; color: #94A3B8; }
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
            <h1>Neraca Pangan Kota Kediri</h1>
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

    {{-- ===================== TABEL 1: SESUAI FILTER BULAN/TAHUN ===================== --}}
    <section>
        <div class="meta">
            <p>Bulan: {{ $bulanLabel }}</p>
            <p>Tahun: {{ $filters['tahun'] }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Komoditas</th>
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
                @foreach ($laporanBulanan as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $r['nama'] }}</td>
                        @if ($r['tersedia'])
                            <td class="num">{{ number_format($r['stok_awal'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['produksi'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['masuk'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['keb_rt'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['keb_non_rt'], 0, ',', '.') }}</td>
                            <td class="num" style="font-weight:700;">{{ number_format($r['nilai_neraca'], 0, ',', '.') }}</td>
                        @else
                            <td colspan="7" class="belum-tersedia">Belum tersedia</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    {{-- ===================== TABEL 2: RATA-RATA TAHUNAN ===================== --}}
    <section>
        <p class="rata-rata-label">Neraca rata-rata tahun {{ $filters['tahun'] }}</p>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Komoditas</th>
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
                @foreach ($laporanTahunan as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $r['nama'] }}</td>
                        @if ($r['tersedia'])
                            <td class="num">{{ number_format($r['stok_awal'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['produksi'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['masuk'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['keluar'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['keb_rt'], 0, ',', '.') }}</td>
                            <td class="num">{{ number_format($r['keb_non_rt'], 0, ',', '.') }}</td>
                            <td class="num" style="font-weight:700;">{{ number_format($r['nilai_neraca'], 0, ',', '.') }}</td>
                        @else
                            <td colspan="7" class="belum-tersedia">Belum tersedia</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <footer>
        <div class="cetak-oleh">
            <p>Dicetak pada: {{ $generatedAt }} WIB</p>
            <p><strong>{{ $dicetakOleh }} - S-KOLAK</strong></p>
            <p>Sistem Ketersediaan Stok dan Laporan Aktual</p>
        </div>
    </footer>

</body>
</html>