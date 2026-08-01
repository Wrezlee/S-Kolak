<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    /** Nama bulan Indonesia (singkatan), dipakai untuk pilihan filter & label grafik. */
    private const BULAN_ABBR = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /** Nama bulan Indonesia (lengkap), dipakai untuk judul/kop Laporan. */
    private const BULAN_LENGKAP = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $filters = $this->readFilters($request);

        $allItems = NeracaPangan::with('komoditas')->orderByDesc('periode')->get();

        // ── Ringkasan Eksekutif (selalu dari seluruh data, tidak tergantung filter) ──
        $validItems = $allItems->where('status', 'valid');
        $ringkasan = [
            'totalValid' => $validItems->count(),
            'surplus'    => $validItems->filter(fn ($n) => DataNeracaController::hitungNilaiNeraca($n) > 0)->count(),
            'defisit'    => $validItems->filter(fn ($n) => DataNeracaController::hitungNilaiNeraca($n) <= 0)->count(),
            'totalEntri' => $allItems->count(),
        ];

        $rekapKomoditas = Komoditas::orderBy('nama')->get()->map(function ($k) use ($allItems) {
            $milik = $allItems->where('komoditas_id', $k->id);

            return [
                'nama'  => $k->nama,
                'total' => $milik->count(),
                'valid' => $milik->where('status', 'valid')->count(),
            ];
        })->values();

        // Query terpisah (bukan dari $allItems yang sudah di-load penuh di atas) supaya
        // bisa di-paginate langsung di level database. Tetap tanpa filter tahun/bulan,
        // sesuai catatan di atas: Ringkasan Eksekutif selalu dari seluruh data valid.
        $nilaiValidTable = NeracaPangan::with('komoditas')
            ->where('status', 'valid')
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($n) {
                $nilai = DataNeracaController::hitungNilaiNeraca($n);

                return [
                    'periode'   => DataNeracaController::formatPeriode($n->periode),
                    'komoditas' => $n->komoditas->nama ?? '-',
                    'nilai'     => $nilai,
                    'surplus'   => $nilai > 0,
                ];
            });

        // ── Laporan Detail (2 tabel: sesuai filter bulan/tahun, & rata-rata tahunan) ──
        $laporanBulanan = $this->buildLaporanPeriode($filters['tahun'], $filters['bulan']);
        $laporanTahunan = $this->buildLaporanRataRata($filters['tahun']);

        // ── Grafik & Visualisasi (dari seluruh data, seperti pada desain) ──
        $komoditasList = Komoditas::orderBy('nama')->pluck('nama', 'id');

        $entriPerKomoditas = ['labels' => $komoditasList->values()->all(), 'valid' => [], 'menunggu' => [], 'revisi' => []];
        foreach ($komoditasList as $id => $nama) {
            $milik = $allItems->where('komoditas_id', $id);
            $entriPerKomoditas['valid'][]    = $milik->where('status', 'valid')->count();
            $entriPerKomoditas['menunggu'][] = $milik->where('status', 'menunggu')->count();
            $entriPerKomoditas['revisi'][]   = $milik->where('status', 'revisi')->count();
        }

        $trenRows = NeracaPangan::selectRaw('
                YEAR(periode) as tahun,
                MONTH(periode) as bulan,
                SUM(stok_awal + produksi + masuk - keluar - kebutuhan_rumah_tangga - kebutuhan_non_rumah_tangga) as nilai
            ')
            ->where('status', 'valid')
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')->orderBy('bulan')
            ->get();

        $trenBulanan = [
            'labels' => $trenRows->map(fn ($r) => (self::BULAN_ABBR[(int) $r->bulan] ?? $r->bulan) . ' ' . $r->tahun)->take(-9)->values()->all(),
            'nilai'  => $trenRows->pluck('nilai')->map(fn ($v) => (float) $v)->take(-9)->values()->all(),
        ];

        return view('admin.laporan', [
            'filters'           => $filters,
            'ringkasan'         => $ringkasan,
            'rekapKomoditas'    => $rekapKomoditas,
            'nilaiValidTable'   => $nilaiValidTable,
            'laporanBulanan'    => $laporanBulanan,
            'laporanTahunan'    => $laporanTahunan,
            'entriPerKomoditas' => $entriPerKomoditas,
            'trenBulanan'       => $trenBulanan,
            'notifCount'        => Notifikasi::where('user_id', auth()->id())
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Export Laporan (sesuai filter tahun/bulan aktif) ke file Excel (.xls).
     * Tanpa dependensi tambahan: tabel HTML disajikan dengan header MIME Excel.
     */
    public function exportExcel(Request $request)
    {
        $filters = $this->readFilters($request);
        $generatedAt = DataNeracaController::formatTanggalIndo(now(), true);

        $html = view('admin.exports.laporan-excel', [
            'filters'        => $filters,
            'bulanLabel'     => self::BULAN_LENGKAP[array_search($filters['bulan'], self::BULAN_ABBR)] ?? $filters['bulan'],
            'generatedAt'    => $generatedAt,
            'laporanBulanan' => $this->buildLaporanPeriode($filters['tahun'], $filters['bulan']),
            'laporanTahunan' => $this->buildLaporanRataRata($filters['tahun']),
            'dicetakOleh'    => $request->user()->name,
        ])->render();

        $filename = 'laporan-neraca-pangan-' . now()->format('Y-m-d_His') . '.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /**
     * Versi cetak (print-friendly) dari Laporan, memakai filter query string yang sama.
     * Sama seperti "Cetak PDF" di Laporan Operator: dicetak lewat dialog print browser
     * (window.print()), tanpa dependency PDF tambahan.
     */
    public function cetak(Request $request)
    {
        $filters = $this->readFilters($request);
        $generatedAt = DataNeracaController::formatTanggalIndo(now(), true);

        return view('admin.exports.laporan-cetak', [
            'filters'        => $filters,
            'bulanLabel'     => self::BULAN_LENGKAP[array_search($filters['bulan'], self::BULAN_ABBR)] ?? $filters['bulan'],
            'generatedAt'    => $generatedAt,
            'laporanBulanan' => $this->buildLaporanPeriode($filters['tahun'], $filters['bulan']),
            'laporanTahunan' => $this->buildLaporanRataRata($filters['tahun']),
            'dicetakOleh'    => $request->user()->name,
        ]);
    }

    /**
     * Ambil & normalisasi filter dari query string. Laporan hanya mengenal 2 filter:
     * tahun & bulan (tidak ada lagi rentang awal-akhir maupun status). Default ke
     * bulan & tahun berjalan supaya halaman selalu tampil dengan data yang relevan.
     */
    private function readFilters(Request $request): array
    {
        return [
            'tahun' => (string) $request->input('tahun', now()->year),
            'bulan' => (string) $request->input('bulan', self::BULAN_ABBR[(int) now()->month]),
        ];
    }

    /**
     * Susun baris Laporan untuk satu periode (bulan+tahun) persis seperti dipilih di
     * filter — satu baris per komoditas (semua komoditas master ditampilkan, walau
     * belum ada datanya untuk periode tsb).
     */
    private function buildLaporanPeriode(string $tahun, string $bulan): array
    {
        $bulanNum = array_search($bulan, self::BULAN_ABBR);

        $items = collect();
        if ($tahun && $bulanNum) {
            $awal = Carbon::create((int) $tahun, (int) $bulanNum, 1)->startOfMonth();
            $akhir = $awal->copy()->endOfMonth();

            $items = NeracaPangan::where('status', 'valid')
                ->whereBetween('periode', [$awal, $akhir])
                ->get();
        }

        return Komoditas::orderBy('nama')->get()->map(function (Komoditas $k) use ($items) {
            $data = $items->firstWhere('komoditas_id', $k->id);

            if (! $data) {
                return ['nama' => $k->nama, 'tersedia' => false];
            }

            return $this->baris($k->nama, [
                'stok_awal'  => (float) $data->stok_awal,
                'produksi'   => (float) $data->produksi,
                'masuk'      => (float) $data->masuk,
                'keluar'     => (float) $data->keluar,
                'keb_rt'     => (float) $data->kebutuhan_rumah_tangga,
                'keb_non_rt' => (float) $data->kebutuhan_non_rumah_tangga,
            ]);
        })->values()->all();
    }

    /**
     * Susun baris Laporan rata-rata tahunan — satu baris per komoditas, tiap kolom
     * dirata-rata dari seluruh entri valid komoditas tsb sepanjang tahun terpilih
     * (Januari s.d. Desember).
     */
    private function buildLaporanRataRata(string $tahun): array
    {
        $items = collect();
        if ($tahun) {
            $items = NeracaPangan::where('status', 'valid')
                ->whereYear('periode', $tahun)
                ->get();
        }

        return Komoditas::orderBy('nama')->get()->map(function (Komoditas $k) use ($items) {
            $milik = $items->where('komoditas_id', $k->id);

            if ($milik->isEmpty()) {
                return ['nama' => $k->nama, 'tersedia' => false];
            }

            return $this->baris($k->nama, [
                'stok_awal'  => (float) $milik->avg('stok_awal'),
                'produksi'   => (float) $milik->avg('produksi'),
                'masuk'      => (float) $milik->avg('masuk'),
                'keluar'     => (float) $milik->avg('keluar'),
                'keb_rt'     => (float) $milik->avg('kebutuhan_rumah_tangga'),
                'keb_non_rt' => (float) $milik->avg('kebutuhan_non_rumah_tangga'),
            ]);
        })->values()->all();
    }

    /**
     * Bentuk satu baris tabel Laporan (dipakai baik untuk baris per-periode maupun
     * baris rata-rata tahunan) sekaligus menghitung Nilai Neraca-nya.
     */
    private function baris(string $nama, array $nilai): array
    {
        $nilaiNeraca = $nilai['stok_awal'] + $nilai['produksi'] + $nilai['masuk']
            - $nilai['keluar'] - $nilai['keb_rt'] - $nilai['keb_non_rt'];

        return array_merge(['nama' => $nama, 'tersedia' => true], $nilai, [
            'nilai_neraca' => $nilaiNeraca,
        ]);
    }
}
