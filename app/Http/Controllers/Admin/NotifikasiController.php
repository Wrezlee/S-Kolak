<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $adminId = $request->user()->id;

        $notifikasi = Notifikasi::where('user_id', $adminId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($n) {
                return [
                    'id'    => $n->id,
                    'pesan' => $n->pesan,
                    'waktu' => Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $this->tentukanTipe($n->pesan),
                ];
            });

        return view('admin.notifikasi', [
            'notifikasi' => $notifikasi,
            'notifCount' => Notifikasi::where('user_id', $adminId)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(Notifikasi $notifikasi)
    {
        if (! $notifikasi->dibaca) {
            $notifikasi->update(['dibaca' => true, 'dibaca_pada' => now()]);
        }

        return back();
    }

    /**
     * Tandai seluruh notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead()
    {
        Notifikasi::where('dibaca', false)->update(['dibaca' => true, 'dibaca_pada' => now()]);

        return back()->with('status', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Tentukan tipe (ikon & warna) notifikasi berdasarkan isi pesan,
     * karena tabel `notifikasi` belum memiliki kolom `tipe` tersendiri.
     */
    private function tentukanTipe(string $pesan): string
    {
        $pesanLower = Str::lower($pesan);

        if (Str::contains($pesanLower, ['revisi', 'dikembalikan', 'ditolak'])) {
            return 'warning';
        }

        if (Str::contains($pesanLower, ['divalidasi', 'valid', 'disetujui'])) {
            return 'success';
        }

        return 'info';
    }
}