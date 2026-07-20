<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DataNeracaController extends Controller
{
    /** Nama bulan Indonesia lengkap, dipakai untuk format periode "Juli 2026". */
    private const BULAN_INDO = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const STATUS_LABEL = [
        'valid'    => 'Valid',
        'menunggu' => 'Menunggu Verifikasi',
        'revisi'   => 'Perlu Revisi',
    ];

    /**
     * Tampilkan seluruh data neraca pangan (khusus admin, lintas komoditas & operator).
     */
    public function index(Request $request)
    {
        $items = $this->filteredQuery($request)
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.data-neraca', [
            'items'      => $items,
            'notifCount' => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Query dasar dengan filter yang sama dipakai index(), exportExcel(), dan cetak(),
     * supaya hasil export selalu konsisten dengan filter yang sedang aktif di halaman.
     */
    private function filteredQuery(Request $request)
    {
        $query = NeracaPangan::with(['komoditas', 'operator', 'verifikator']);

        if ($request->filled('komoditas_id')) {
            $query->where('komoditas_id', $request->input('komoditas_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('periode')) {
            $query->whereYear('periode', Carbon::parse($request->input('periode'))->year)
                  ->whereMonth('periode', Carbon::parse($request->input('periode'))->month);
        }

        return $query;
    }

    /**
     * Susun baris data neraca (format array datar) untuk keperluan export Excel & PDF.
     */
    private function exportRows(Request $request): array
    {
        $items = $this->filteredQuery($request)
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->get();

        return $items->map(function (NeracaPangan $n, $i) {
            $nilaiNeraca = self::hitungNilaiNeraca($n);

            return [
                'no'           => $i + 1,
                'periode'      => self::formatPeriode($n->periode),
                'komoditas'    => $n->komoditas->nama ?? '-',
                'stok_awal'    => (float) $n->stok_awal,
                'produksi'     => (float) $n->produksi,
                'masuk'        => (float) $n->masuk,
                'keluar'       => (float) $n->keluar,
                'keb_rt'       => (float) $n->kebutuhan_rumah_tangga,
                'keb_non_rt'   => (float) $n->kebutuhan_non_rumah_tangga,
                'nilai_neraca' => (float) $nilaiNeraca,
                'status'       => self::STATUS_LABEL[$n->status] ?? ucfirst($n->status),
                'operator'     => $n->operator->name ?? '-',
                'verifikator'  => $n->verifikator->name ?? '-',
                'tanggal'      => $n->created_at ? self::formatTanggalIndo($n->created_at) : '-',
            ];
        })->all();
    }

    /**
     * Export data neraca pangan (sesuai filter aktif) ke file Excel (.xls).
     *
     * Dibuat tanpa dependensi tambahan: berupa tabel HTML yang disajikan dengan
     * header MIME Excel, sehingga langsung terbuka rapi di Microsoft Excel /
     * Google Sheets lengkap dengan header berwarna dan angka ter-format.
     */
    public function exportExcel(Request $request)
    {
        $rows = $this->exportRows($request);
        $generatedAt = self::formatTanggalIndo(now(), true);

        $html = view('admin.exports.data-neraca-excel', [
            'rows'        => $rows,
            'generatedAt' => $generatedAt,
        ])->render();

        $filename = 'data-neraca-pangan-' . now()->format('Y-m-d_His') . '.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Versi cetak (print-friendly) dari data neraca pangan, memakai filter query string
     * yang sama dengan halaman index. Sama seperti "Cetak PDF" di Laporan Operator: tidak
     * memakai library PDF terpisah (mis. DomPDF) — halaman ini dicetak lewat dialog print
     * browser (window.print()), sehingga tidak bergantung pada extension/paket tambahan
     * yang mungkin belum terpasang di server.
     */
    public function cetak(Request $request)
    {
        $rows = $this->exportRows($request);
        $generatedAt = self::formatTanggalIndo(now(), true);

        return view('admin.exports.data-neraca-cetak', [
            'rows'        => $rows,
            'generatedAt' => $generatedAt,
        ]);
    }

    /**
     * Hapus data neraca pangan.
     */
    public function destroy(NeracaPangan $neracaPangan)
    {
        $neracaPangan->delete();

        return back()->with('status', 'Data neraca pangan berhasil dihapus.');
    }

    /**
     * Hitung nilai neraca: stok awal + produksi + masuk - keluar - kebutuhan RT - kebutuhan non-RT.
     */
    public static function hitungNilaiNeraca(NeracaPangan $n): float
    {
        return (float) $n->stok_awal
            + (float) $n->produksi
            + (float) $n->masuk
            - (float) $n->keluar
            - (float) $n->kebutuhan_rumah_tangga
            - (float) $n->kebutuhan_non_rumah_tangga;
    }

    /**
     * Format kolom periode (DATE, disimpan tanggal 1 tiap bulan) menjadi "Juli 2026".
     */
    public static function formatPeriode($periode): string
    {
        $date = $periode instanceof Carbon ? $periode : Carbon::parse($periode);

        return (self::BULAN_INDO[(int) $date->month] ?? $date->month) . ' ' . $date->year;
    }

    /**
     * Format tanggal lengkap berbahasa Indonesia, mis. "18 Juli 2026" atau
     * (dengan waktu) "18 Juli 2026, 08:59". Sengaja tidak memakai
     * ->translatedFormat()/Carbon::setLocale(), karena hasilnya masih bisa
     * jatuh ke bahasa Inggris jika extension "intl" tidak terpasang di
     * server — array BULAN_INDO di atas membuat hasilnya selalu konsisten
     * berbahasa Indonesia di server mana pun.
     */
    public static function formatTanggalIndo($date, bool $withTime = false): string
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        $hasil = $date->day . ' ' . (self::BULAN_INDO[(int) $date->month] ?? $date->month) . ' ' . $date->year;

        if ($withTime) {
            $hasil .= ', ' . $date->format('H:i');
        }

        return $hasil;
    }
}