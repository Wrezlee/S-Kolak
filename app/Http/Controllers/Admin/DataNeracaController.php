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

    /**
     * Tampilkan seluruh data neraca pangan (khusus admin, lintas komoditas & operator).
     */
    public function index(Request $request)
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

        $items = $query->orderByDesc('periode')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.data-neraca', [
            'items'      => $items,
            'notifCount' => Notifikasi::where('dibaca', false)->count(),
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
}