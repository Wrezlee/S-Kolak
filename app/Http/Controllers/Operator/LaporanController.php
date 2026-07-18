<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LaporanController extends Controller
{
    /** Nama bulan Indonesia (singkatan) -> nomor bulan, untuk parsing filter "Jan", "Feb", dst. */
    private const BULAN_INDO = [
        'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'Mei' => 5, 'Jun' => 6,
        'Jul' => 7, 'Agt' => 8, 'Sep' => 9, 'Okt' => 10, 'Nov' => 11, 'Des' => 12,
    ];

    /**
     * Halaman "Laporan Neraca Saya": data neraca pangan milik operator yang
     * sedang login, difilter berdasarkan rentang periode dan komoditas.
     * Draft tidak diikutsertakan karena belum diajukan untuk verifikasi.
     */
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

        $items = $query->orderByDesc('periode')->orderByDesc('id')->get();

        return view('operator.laporan', [
            'filters'       => $filters,
            'items'         => $items,
            'komoditasList' => Komoditas::where('status', 'Aktif')->orderBy('nama')->get(),
            'notifCount'    => Notifikasi::where('user_id', $operatorId)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Versi cetak (print-friendly) dari laporan, memakai filter query string yang sama.
     * Tidak memakai library PDF terpisah — halaman ini dicetak lewat dialog print browser
     * (window.print()), jadi tetap konsisten tanpa dependency tambahan.
     */
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

        $items = $query->orderByDesc('periode')->orderByDesc('id')->get();

        return view('operator.laporan-cetak', [
            'namaOperator'  => $request->user()->name,
            'loginIdCetak'  => $request->user()->login_id,
            'items'         => $items,
        ]);
    }

    /**
     * Tanggal awal rentang filter (tanggal 1 pada bulan/tahun awal).
     * Mengembalikan null kalau tahun atau bulan awal tidak diisi.
     */
    private function buildPeriodeAwal(?string $tahun, ?string $bulan): ?Carbon
    {
        if (empty($tahun) || empty($bulan) || ! isset(self::BULAN_INDO[$bulan])) {
            return null;
        }

        return Carbon::create((int) $tahun, self::BULAN_INDO[$bulan], 1)->startOfMonth();
    }

    /**
     * Tanggal akhir rentang filter (tanggal terakhir pada bulan/tahun akhir).
     * Mengembalikan null kalau tahun atau bulan akhir tidak diisi.
     */
    private function buildPeriodeAkhir(?string $tahun, ?string $bulan): ?Carbon
    {
        if (empty($tahun) || empty($bulan) || ! isset(self::BULAN_INDO[$bulan])) {
            return null;
        }

        return Carbon::create((int) $tahun, self::BULAN_INDO[$bulan], 1)->endOfMonth();
    }
}