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

    public function index(Request $request)
    {
        $filters = [
            'tahun_awal'  => $request->input('tahun_awal', ''),
            'bulan_awal'  => $request->input('bulan_awal', ''),
            'tahun_akhir' => $request->input('tahun_akhir', ''),
            'bulan_akhir' => $request->input('bulan_akhir', ''),
            'status'      => $request->input('status', ''),
        ];

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

        $nilaiValidTable = $validItems->sortByDesc('periode')->values()->map(function ($n) {
            $nilai = DataNeracaController::hitungNilaiNeraca($n);

            return [
                'periode'   => DataNeracaController::formatPeriode($n->periode),
                'komoditas' => $n->komoditas->nama ?? '-',
                'nilai'     => $nilai,
                'surplus'   => $nilai > 0,
            ];
        });

        // ── Laporan Detail (mengikuti filter) ──
        $startDate = null;
        $endDate = null;

        if ($filters['tahun_awal'] && $filters['bulan_awal']) {
            $bulanNum = array_search($filters['bulan_awal'], self::BULAN_ABBR);
            if ($bulanNum) {
                $startDate = Carbon::create((int) $filters['tahun_awal'], $bulanNum, 1)->startOfMonth();
            }
        }

        if ($filters['tahun_akhir'] && $filters['bulan_akhir']) {
            $bulanNum = array_search($filters['bulan_akhir'], self::BULAN_ABBR);
            if ($bulanNum) {
                $endDate = Carbon::create((int) $filters['tahun_akhir'], $bulanNum, 1)->endOfMonth();
            }
        }

        $detailQuery = NeracaPangan::with('komoditas')->orderByDesc('periode');

        if ($startDate) {
            $detailQuery->where('periode', '>=', $startDate);
        }
        if ($endDate) {
            $detailQuery->where('periode', '<=', $endDate);
        }
        if ($filters['status']) {
            $detailQuery->where('status', $filters['status']);
        }

        $detail = $detailQuery->paginate(15)->withQueryString();

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

        $latestValidIds = NeracaPangan::where('status', 'valid')
            ->selectRaw('MAX(id) as id')
            ->groupBy('komoditas_id')
            ->pluck('id');

        $perbandinganNilai = NeracaPangan::with('komoditas')
            ->whereIn('id', $latestValidIds)
            ->get()
            ->map(fn ($n) => [
                'nama'  => $n->komoditas->nama ?? '-',
                'nilai' => DataNeracaController::hitungNilaiNeraca($n),
            ])
            ->sortByDesc('nilai')
            ->values();

        return view('admin.laporan', [
            'filters'           => $filters,
            'ringkasan'         => $ringkasan,
            'rekapKomoditas'    => $rekapKomoditas,
            'nilaiValidTable'   => $nilaiValidTable,
            'detail'            => $detail,
            'entriPerKomoditas' => $entriPerKomoditas,
            'trenBulanan'       => $trenBulanan,
            'perbandinganNilai' => $perbandinganNilai,
            'notifCount'        => Notifikasi::where('user_id', auth()->id())
                ->where('dibaca', false)
                ->count(),
        ]);
    }
}