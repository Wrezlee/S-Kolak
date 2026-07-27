<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Admin\DataNeracaController;
use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    private const BULAN_INDO = [
        'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'Mei' => 5, 'Jun' => 6,
        'Jul' => 7, 'Agt' => 8, 'Sep' => 9, 'Okt' => 10, 'Nov' => 11, 'Des' => 12,
    ];

    public function index(Request $request)
    {
        $operatorId = $request->user()->id;

        $filters = [
            'tahun_awal'   => $request->query('tahun_awal', ''),
            'bulan_awal'   => $request->query('bulan_awal', ''),
            'tahun_akhir'  => $request->query('tahun_akhir', ''),
            'bulan_akhir'  => $request->query('bulan_akhir', ''),
            'komoditas_id' => $request->query('komoditas_id', ''),
        ];

        $query = NeracaPangan::with(['komoditas', 'verifikator'])
            ->where('diinput_oleh', $operatorId)
            ->where('status', '!=', 'draft');

        if ($awal = $this->buildPeriodeAwal($filters['tahun_awal'], $filters['bulan_awal'])) {
            $query->where('periode', '>=', $awal);
        }

        if ($akhir = $this->buildPeriodeAkhir($filters['tahun_akhir'], $filters['bulan_akhir'])) {
            $query->where('periode', '<=', $akhir);
        }

        if (! empty($filters['komoditas_id'])) {
            $query->where('komoditas_id', $filters['komoditas_id']);
        }

        $items = (clone $query)->orderByDesc('periode')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('operator.laporan', [
            'filters'        => $filters,
            'items'          => $items,
            'totalEntri'     => (clone $query)->count(),
            'dataValid'      => (clone $query)->where('status', 'valid')->count(),
            'komoditasList'  => Komoditas::where('status', 'Aktif')->orderBy('nama')->get(),
            'notifCount'     => Notifikasi::where('user_id', $operatorId)
                ->where('dibaca', false)
                ->count(),
            'totalEntriSaya' => NeracaPangan::where('diinput_oleh', $operatorId)->count(),
        ]);
    }


    public function cetak(Request $request)
    {
        $operatorId = $request->user()->id;

        $filters = [
            'tahun_awal'   => $request->query('tahun_awal', ''),
            'bulan_awal'   => $request->query('bulan_awal', ''),
            'tahun_akhir'  => $request->query('tahun_akhir', ''),
            'bulan_akhir'  => $request->query('bulan_akhir', ''),
            'komoditas_id' => $request->query('komoditas_id', ''),
        ];

        $query = NeracaPangan::with(['komoditas', 'verifikator'])
            ->where('diinput_oleh', $operatorId)
            ->where('status', '!=', 'draft');

        if ($awal = $this->buildPeriodeAwal($filters['tahun_awal'], $filters['bulan_awal'])) {
            $query->where('periode', '>=', $awal);
        }

        if ($akhir = $this->buildPeriodeAkhir($filters['tahun_akhir'], $filters['bulan_akhir'])) {
            $query->where('periode', '<=', $akhir);
        }

        if (! empty($filters['komoditas_id'])) {
            $query->where('komoditas_id', $filters['komoditas_id']);
        }

        $items = $query->orderBy('periode')->orderBy('id')->get();

        // Samakan dengan Laporan Cetak Admin: setiap baris menampilkan rincian
        // stok/produksi/masuk/keluar/kebutuhan beserta Nilai Neraca yang dihitung,
        // bukan sekadar daftar periode & komoditas.
        $rows = $items->map(function (NeracaPangan $n) {
            return [
                'komoditas'    => $n->komoditas->nama ?? '-',
                'periode'      => DataNeracaController::formatPeriode($n->periode),
                'status'       => $n->status,
                'stok_awal'    => (float) $n->stok_awal,
                'produksi'     => (float) $n->produksi,
                'masuk'        => (float) $n->masuk,
                'keluar'       => (float) $n->keluar,
                'keb_rt'       => (float) $n->kebutuhan_rumah_tangga,
                'keb_non_rt'   => (float) $n->kebutuhan_non_rumah_tangga,
                'nilai_neraca' => DataNeracaController::hitungNilaiNeraca($n),
            ];
        })->values();

        return view('operator.laporan-cetak', [
            'namaOperator' => $request->user()->name,
            'loginIdCetak' => $request->user()->login_id,
            'items'        => $rows,
            'tahun'        => $this->resolveTahun($filters),
            'generatedAt'  => DataNeracaController::formatTanggalIndo(now(), true),
        ]);
    }

    /**
     * Tentukan label "Tahun" untuk kop cetak: memakai rentang tahun_awal-tahun_akhir
     * jika filter periode aktif & berbeda, satu tahun jika sama, atau tahun berjalan jika tidak difilter.
     */
    private function resolveTahun(array $filters): string
    {
        $awal = $filters['tahun_awal'] ?? '';
        $akhir = $filters['tahun_akhir'] ?? '';

        if ($awal && $akhir && $awal !== $akhir) {
            return "{$awal} - {$akhir}";
        }

        if ($awal) {
            return (string) $awal;
        }

        if ($akhir) {
            return (string) $akhir;
        }

        return (string) now()->year;
    }

    private function buildPeriodeAwal(?string $tahun, ?string $bulan): ?Carbon
    {
        if (empty($tahun) || empty($bulan) || ! isset(self::BULAN_INDO[$bulan])) {
            return null;
        }

        return Carbon::create((int) $tahun, self::BULAN_INDO[$bulan], 1)->startOfMonth();
    }

    private function buildPeriodeAkhir(?string $tahun, ?string $bulan): ?Carbon
    {
        if (empty($tahun) || empty($bulan) || ! isset(self::BULAN_INDO[$bulan])) {
            return null;
        }

        return Carbon::create((int) $tahun, self::BULAN_INDO[$bulan], 1)->endOfMonth();
    }
}