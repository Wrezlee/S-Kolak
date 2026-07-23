<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard operator: hanya menampilkan data neraca pangan
     * milik operator yang sedang login (diinput_oleh = auth()->id()).
     */
    public function index(Request $request)
    {
        $operatorId = $request->user()->id;

        return view('operator.dashboard', [
            'summary'     => $this->getSummary($operatorId),
            'totalEntri'  => NeracaPangan::where('diinput_oleh', $operatorId)->count(),
            'dataTerbaru' => $this->getDataTerbaru($operatorId),
            'cardItems'   => $this->getCardItems($operatorId),
            'notifCount'  => Notifikasi::where('user_id', $operatorId)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Ringkasan jumlah data neraca pangan milik operator, per status.
     */
    private function getSummary(int $operatorId): array
    {
        $base = NeracaPangan::where('diinput_oleh', $operatorId);

        return [
            'total'    => (clone $base)->count(),
            'valid'    => (clone $base)->where('status', 'valid')->count(),
            'menunggu' => (clone $base)->where('status', 'menunggu')->count(),
            'revisi'   => (clone $base)->where('status', 'revisi')->count(),
        ];
    }

    /**
     * 10 data neraca pangan terbaru milik operator, lengkap dengan
     * relasi komoditas untuk ditampilkan di tabel dashboard.
     */
    private function getDataTerbaru(int $operatorId)
    {
        return NeracaPangan::with('komoditas')
            ->where('diinput_oleh', $operatorId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /** Nama bulan Indonesia (singkatan), dipakai untuk format label "Jan 2025" dsb. */
    private const BULAN_INDO = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Daftar data neraca pangan milik operator (sampai 100 terbaru), dipakai
     * untuk mengisi modal "Lihat detail" pada tiap stat card di dashboard.
     */
    private function getCardItems(int $operatorId): array
    {
        return NeracaPangan::with('komoditas')
            ->where('diinput_oleh', $operatorId)
            ->orderByDesc('created_at')
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
