<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard verifikator: ringkasan seluruh data neraca pangan di sistem
     * (bukan hanya milik verifikator yang login), karena data "menunggu
     * verifikasi" bisa ditinjau oleh verifikator mana pun.
     */
    public function index(Request $request)
    {
        return view('verifikator.dashboard', [
            'summary'    => $this->getSummary(),
            'cardItems'  => $this->getCardItems(),
            'notifCount' => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Ringkasan jumlah data neraca pangan per status, lintas seluruh sistem.
     */
    private function getSummary(): array
    {
        return [
            'total'    => NeracaPangan::count(),
            'valid'    => NeracaPangan::where('status', 'valid')->count(),
            'menunggu' => NeracaPangan::where('status', 'menunggu')->count(),
            'revisi'   => NeracaPangan::where('status', 'revisi')->count(),
        ];
    }

    /** Nama bulan Indonesia (singkatan), dipakai untuk format label "Jan 2025" dsb. */
    private const BULAN_INDO = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Daftar data neraca pangan terbaru (lintas seluruh sistem, sampai 100
     * data), dipakai untuk mengisi modal "Lihat detail" pada tiap stat card.
     */
    private function getCardItems(): array
    {
        return NeracaPangan::with('komoditas')
            ->latest('periode')
            ->limit(100)
            ->get()
            ->map(fn ($item) => [
                'komoditas'    => $item->komoditas->nama ?? '-',
                'periode'      => (self::BULAN_INDO[(int) \Illuminate\Support\Carbon::parse($item->periode)->month] ?? '') . ' ' . \Illuminate\Support\Carbon::parse($item->periode)->year,
                'nilai_neraca' => $item->nilai_neraca,
                'status'       => $item->status,
            ])
            ->all();
    }
}