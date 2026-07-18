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
}