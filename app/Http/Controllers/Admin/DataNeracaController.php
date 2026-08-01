<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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
        $items = $this->filteredQuery($request)
            ->orderBy('periode')
            ->orderBy('id')
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

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('komoditas', fn ($q2) => $q2->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('operator', fn ($q2) => $q2->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('verifikator', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

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
     * Hapus data neraca pangan.
     * Beri notifikasi ke operator yang menginput & verifikator yang menangani
     * data tersebut (jika ada), supaya mereka tahu data tersebut sudah tidak ada.
     */
    public function destroy(NeracaPangan $neracaPangan)
    {
        $neracaPangan->load('komoditas');

        $namaKomoditas = $neracaPangan->komoditas->nama ?? 'data neraca pangan';
        $periode       = self::formatPeriode($neracaPangan->periode);
        $adminNama     = Auth::user()->name ?? 'Admin';

        $penerimaIds = collect([$neracaPangan->diinput_oleh, $neracaPangan->diverifikasi_oleh])
            ->filter()
            ->unique();

        $neracaPangan->delete();

        foreach ($penerimaIds as $userId) {
            Notifikasi::create([
                'user_id' => $userId,
                'judul'   => 'Data neraca pangan dihapus',
                'pesan'   => "{$adminNama} menghapus data {$namaKomoditas} periode {$periode}.",
                'dibaca'  => false,
            ]);
        }

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