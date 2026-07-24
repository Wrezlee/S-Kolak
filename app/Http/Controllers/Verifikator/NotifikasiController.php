<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class NotifikasiController extends Controller
{
    /**
     * Daftar notifikasi milik verifikator yang sedang login.
     */
    public function index(Request $request)
    {
        $verifikatorId = $request->user()->id;

        $notifikasi = Notifikasi::where('user_id', $verifikatorId)
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($n) {
                return [
                    'id'    => $n->id,
                    'pesan' => $n->pesan,
                    'waktu' => Carbon::parse($n->created_at)->diffForHumans(),
                    'baca'  => (bool) $n->dibaca,
                    'tipe'  => $this->tentukanTipe($n->pesan),
                ];
            });

        return view('verifikator.notifikasi', [
            'notifikasi' => $notifikasi,
            'notifCount' => Notifikasi::where('user_id', $verifikatorId)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     * Hanya pemilik notifikasi yang boleh menandainya.
     */
    public function markAsRead(Request $request, Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->user_id === $request->user()->id, 403);

        if (! $notifikasi->dibaca) {
            $notifikasi->update(['dibaca' => true, 'dibaca_pada' => now()]);
        }

        return back();
    }

    /**
     * Tandai seluruh notifikasi milik verifikator sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request)
    {
        Notifikasi::where('user_id', $request->user()->id)
            ->where('dibaca', false)
            ->update(['dibaca' => true, 'dibaca_pada' => now()]);

        return back()->with('status', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Tentukan tipe (ikon & warna) notifikasi berdasarkan isi pesan,
     * karena tabel `notifikasi` belum memiliki kolom `tipe` tersendiri.
     */
    private function tentukanTipe(string $pesan): string
    {
        $pesanLower = Str::lower($pesan);

        if (Str::contains($pesanLower, ['revisi', 'dikembalikan', 'ditolak', 'dihapus', 'hapus'])) {
            return 'warning';
        }

        if (Str::contains($pesanLower, ['divalidasi', 'valid', 'disetujui'])) {
            return 'success';
        }

        return 'info';
    }
}