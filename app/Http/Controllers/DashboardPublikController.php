<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use App\Models\NeracaPangan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardPublikController extends Controller
{
    /**
     * Nama bulan dalam Bahasa Indonesia (dipakai untuk format tampilan & parsing filter).
     */
    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $komoditasList = Komoditas::orderBy('nama')->pluck('nama');

        $query = NeracaPangan::with('komoditas')
            // Hanya data yang sudah diverifikasi (valid) yang ditampilkan ke publik
            ->where('status', 'valid');

        // Filter rentang periode (tahun awal/bulan awal - tahun akhir/bulan akhir)
        if ($this->filterAktif($request, 'tahun_awal', 'bulan_awal')) {
            $awal = $this->buatTanggalPeriode($request->tahun_awal, $request->bulan_awal);
            $query->whereDate('periode', '>=', $awal->startOfMonth());
        }

        if ($this->filterAktif($request, 'tahun_akhir', 'bulan_akhir')) {
            $akhir = $this->buatTanggalPeriode($request->tahun_akhir, $request->bulan_akhir);
            $query->whereDate('periode', '<=', $akhir->endOfMonth());
        }

        // Filter komoditas
        if ($request->filled('komoditas') && $request->komoditas !== 'Semua') {
            $query->whereHas('komoditas', fn ($q) => $q->where('nama', $request->komoditas));
        }

        $records = $query->orderByDesc('periode')->get();

        // Susun baris tabel + hitung nilai neraca & status stok untuk masing-masing baris
        $rows = $records->values()->map(function (NeracaPangan $item, int $index) {
            $nilaiNeraca = $this->hitungNilaiNeraca($item);

            return [
                'no' => $index + 1,
                'periode' => $this->formatPeriode($item->periode),
                'periode_key' => Carbon::parse($item->periode)->format('Y-m'),
                'komoditas' => $item->komoditas->nama,
                'stok_awal' => (float) $item->stok_awal,
                'produksi' => (float) $item->produksi,
                'masuk' => (float) $item->masuk,
                'keluar' => (float) $item->keluar,
                'keb_rt' => (float) $item->kebutuhan_rumah_tangga,
                'keb_non_rt' => (float) $item->kebutuhan_non_rumah_tangga,
                'nilai_neraca' => $nilaiNeraca,
                'status' => $this->statusStok($nilaiNeraca, $item),
            ];
        });

        // Kartu ringkasan
        $summary = [
            'total_komoditas' => $rows->pluck('komoditas')->unique()->count(),
            'aman' => $rows->where('status', 'Aman')->count(),
            'waspada' => $rows->where('status', 'Waspada')->count(),
            'rentan' => $rows->where('status', 'Rentan')->count(),
        ];

        // Data untuk grafik tren (kumulatif nilai neraca seluruh komoditas per periode)
        [$trendLabels, $trendValues] = $this->hitungTren($records);

        // Detail data terbaru untuk kartu & grafik detail di sisi kanan
        $detailData = null;
        $terbaru = $records->sortByDesc('periode')->first();

        if ($terbaru) {
            $nilaiNeraca = $this->hitungNilaiNeraca($terbaru);

            $detailData = [
                'komoditas' => $terbaru->komoditas->nama,
                'periode' => $this->formatPeriode($terbaru->periode),
                'status' => $this->statusStok($nilaiNeraca, $terbaru),
                'stok_awal' => (float) $terbaru->stok_awal,
                'produksi' => (float) $terbaru->produksi,
                'masuk' => (float) $terbaru->masuk,
                'keluar' => (float) $terbaru->keluar,
                'keb_rt' => (float) $terbaru->kebutuhan_rumah_tangga,
                'keb_non_rt' => (float) $terbaru->kebutuhan_non_rumah_tangga,
                'nilai_neraca' => $nilaiNeraca,
                'ketahanan_hari' => $this->hitungKetahananHari($nilaiNeraca, $terbaru),
            ];
        }

        $waktuTerbaru = $records->max('updated_at');
        $lastUpdated = $waktuTerbaru ? $this->formatTanggalWaktu($waktuTerbaru) : '-';

        // ===============================
        // Data untuk Filter Dashboard
        // ===============================

        // Daftar bulan
        $bulanList = array_values(self::BULAN);

        // Daftar tahun berdasarkan data database
        $tahunList = NeracaPangan::selectRaw('YEAR(periode) as tahun')
            ->distinct()
            ->orderBy('tahun')
            ->pluck('tahun');

        // Jika database masih kosong
        if ($tahunList->isEmpty()) {
            $tahunSekarang = now()->year;
            $tahunList = collect(range($tahunSekarang - 2, $tahunSekarang + 1));
        }

        // Menyimpan nilai filter yang dipilih
        $q = $request->all();

        return view('public.dashboard', compact(
            'komoditasList',
            'bulanList',
            'tahunList',
            'q',
            'rows',
            'summary',
            'trendLabels',
            'trendValues',
            'detailData',
            'lastUpdated',
        ));
    }

    /**
     * Cek apakah pasangan filter tahun+bulan aktif (bukan kosong / "Semua").
     */
    private function filterAktif(Request $request, string $tahunKey, string $bulanKey): bool
    {
        return $request->filled($tahunKey) && $request->$tahunKey !== 'Semua'
            && $request->filled($bulanKey) && $request->$bulanKey !== 'Semua';
    }

    /**
     * Konversi input tahun + nama bulan (Indonesia) menjadi objek Carbon.
     */
    private function buatTanggalPeriode(string $tahun, string $namaBulan): Carbon
    {
        $bulan = array_search($namaBulan, self::BULAN, true) ?: 1;

        return Carbon::createFromDate((int) $tahun, (int) $bulan, 1);
    }

    /**
     * Format tanggal periode menjadi "Bulan Tahun", contoh: Januari 2026.
     */
    private function formatPeriode(string|Carbon $tanggal): string
    {
        $tanggal = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return self::BULAN[$tanggal->month] . ' ' . $tanggal->year;
    }

    /**
     * Format tanggal + jam untuk info "Data diperbarui".
     */
    private function formatTanggalWaktu(string|Carbon $tanggal): string
    {
        $tanggal = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return $tanggal->day . ' ' . self::BULAN[$tanggal->month] . ' ' . $tanggal->year
            . ', ' . $tanggal->format('H:i') . ' WIB';
    }

    /**
     * Nilai neraca = ketersediaan (stok awal + produksi + masuk - keluar)
     * dikurangi total kebutuhan (rumah tangga + non rumah tangga).
     */
    private function hitungNilaiNeraca(NeracaPangan $item): float
    {
        return (float) $item->stok_awal
            + (float) $item->produksi
            + (float) $item->masuk
            - (float) $item->keluar
            - (float) $item->kebutuhan_rumah_tangga
            - (float) $item->kebutuhan_non_rumah_tangga;
    }

    /**
     * Klasifikasi status stok berdasarkan rasio nilai neraca terhadap total kebutuhan:
     * - surplus >= 20% dari kebutuhan  -> Aman
     * - surplus 0% - 20%               -> Waspada
     * - defisit (nilai neraca negatif) -> Rentan
     *
     * Catatan: ambang batas 20% ini asumsi, sesuaikan dengan ketentuan yang berlaku
     * di instansi/tugasmu jika ada rumus baku.
     */
    private function statusStok(float $nilaiNeraca, NeracaPangan $item): string
    {
        $totalKebutuhan = (float) $item->kebutuhan_rumah_tangga + (float) $item->kebutuhan_non_rumah_tangga;

        if ($totalKebutuhan <= 0) {
            return $nilaiNeraca >= 0 ? 'Aman' : 'Rentan';
        }

        $rasio = $nilaiNeraca / $totalKebutuhan;

        if ($rasio >= 0.2) {
            return 'Aman';
        }

        if ($rasio >= 0) {
            return 'Waspada';
        }

        return 'Rentan';
    }

    /**
     * Perkiraan jumlah hari ketahanan pangan = nilai neraca / rata-rata kebutuhan harian.
     * Kebutuhan bulanan (RT + non-RT) dibagi 30 hari.
     */
    private function hitungKetahananHari(float $nilaiNeraca, NeracaPangan $item): int
    {
        $kebutuhanHarian = ((float) $item->kebutuhan_rumah_tangga + (float) $item->kebutuhan_non_rumah_tangga) / 30;

        if ($kebutuhanHarian <= 0) {
            return 0;
        }

        return (int) max(0, round($nilaiNeraca / $kebutuhanHarian));
    }

    /**
     * Hitung tren nilai neraca kumulatif (seluruh komoditas) per periode bulan,
     * diurutkan dari periode paling lama ke paling baru untuk grafik garis.
     */
    private function hitungTren($records): array
    {
        $perBulan = $records
            ->groupBy(fn (NeracaPangan $item) => Carbon::parse($item->periode)->format('Y-m'))
            ->sortKeys()
            ->map(fn ($group) => $group->sum(fn (NeracaPangan $item) => $this->hitungNilaiNeraca($item)));

        $labels = [];
        $values = [];
        $kumulatif = 0;

        foreach ($perBulan as $key => $totalBulanIni) {
            $tanggal = Carbon::createFromFormat('Y-m', $key);
            $labels[] = self::BULAN[$tanggal->month] . ' ' . $tanggal->year;

            $kumulatif += $totalBulanIni;
            $values[] = round($kumulatif, 2);
        }

        return [$labels, $values];
    }
}