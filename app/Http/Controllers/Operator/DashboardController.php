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
}
