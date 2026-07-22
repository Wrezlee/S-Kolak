<?php

namespace App\Http\Controllers\Verifikator;

use App\Http\Controllers\Controller;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use App\Models\RiwayatVerifikasiNeraca;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VerifikasiController extends Controller
{
    /**
     * Daftar data neraca pangan yang berstatus "menunggu" verifikasi.
     * Bisa ditinjau oleh verifikator mana pun (tidak difilter per user).
     */
    public function index(Request $request)
    {
        $pending = NeracaPangan::with(['komoditas', 'operator'])
            ->where('status', 'menunggu')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->whereHas('komoditas', fn ($q2) => $q2->where('nama', 'like', "%{$search}%"))
                      ->orWhereHas('operator', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('periode')
            ->get();

        return view('verifikator.data-menunggu-verif', [
            'pending'    => $pending,
            'notifCount' => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Halaman detail satu data neraca pangan untuk ditinjau & diverifikasi.
     */
    public function show(Request $request, NeracaPangan $neracaPangan)
    {
        $neracaPangan->load(['komoditas', 'operator']);

        return view('verifikator.verifikasi-detail', [
            'item'         => $neracaPangan,
            'pendingCount' => NeracaPangan::where('status', 'menunggu')->count(),
            'notifCount'   => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Daftar riwayat data neraca pangan yang sudah selesai diverifikasi
     * (valid maupun dikembalikan untuk revisi). Bisa dilihat oleh
     * verifikator mana pun, tidak difilter per user.
     */
    public function riwayat(Request $request)
    {
        $riwayat = NeracaPangan::with(['komoditas', 'operator'])
            ->sudahDiverifikasi()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->whereHas('komoditas', fn ($q2) => $q2->where('nama', 'like', "%{$search}%"))
                      ->orWhereHas('operator', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->get();

        return view('verifikator.riwayat', [
            'riwayat'    => $riwayat,
            'notifCount' => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Halaman detail (read-only) satu riwayat verifikasi.
     */
    public function riwayatShow(Request $request, NeracaPangan $neracaPangan)
    {
        abort_unless(in_array($neracaPangan->status, ['valid', 'revisi']), 404);

        $neracaPangan->load(['komoditas', 'operator', 'verifikator']);

        return view('verifikator.riwayat-detail', [
            'item'       => $neracaPangan,
            'notifCount' => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Proses hasil verifikasi: tandai valid atau kembalikan untuk revisi,
     * catat ke riwayat verifikasi, dan beri notifikasi ke operator terkait
     * serta ke seluruh admin (untuk pemantauan).
     */
    public function update(Request $request, NeracaPangan $neracaPangan)
    {
        $validated = $request->validate([
            'status'  => ['required', Rule::in(['valid', 'revisi'])],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $statusLama = $neracaPangan->status;

        $neracaPangan->update([
            'status'             => $validated['status'],
            'keterangan'         => $validated['catatan'] ?: $neracaPangan->keterangan,
            'diverifikasi_oleh'  => Auth::id(),
            'diverifikasi_pada'  => now(),
        ]);

        RiwayatVerifikasiNeraca::create([
            'neraca_pangan_id'    => $neracaPangan->id,
            'verifikator_id'      => Auth::id(),
            'status_lama'         => $statusLama,
            'status_baru'         => $validated['status'],
            'catatan'             => $validated['catatan'] ?? null,
            'tanggal_verifikasi'  => now(),
        ]);

        // Notifikasi ke operator yang menginput data ini.
        if ($neracaPangan->diinput_oleh) {
            Notifikasi::create([
                'user_id' => $neracaPangan->diinput_oleh,
                'judul'   => $validated['status'] === 'valid' ? 'Data divalidasi' : 'Data dikembalikan untuk revisi',
                'pesan'   => $validated['status'] === 'valid'
                    ? "Data {$neracaPangan->komoditas->nama} telah divalidasi oleh verifikator."
                    : "Data {$neracaPangan->komoditas->nama} dikembalikan untuk revisi.",
                'dibaca'  => false,
            ]);
        }

        // Notifikasi ke seluruh admin — sebelumnya tidak ada sama sekali,
        // sehingga halaman Notifikasi Admin selalu kosong.
        $adminIds = User::where('role', 'admin')->pluck('id');
        foreach ($adminIds as $adminId) {
            Notifikasi::create([
                'user_id' => $adminId,
                'judul'   => $validated['status'] === 'valid' ? 'Data divalidasi' : 'Data dikembalikan untuk revisi',
                'pesan'   => $validated['status'] === 'valid'
                    ? "{$request->user()->name} memvalidasi data {$neracaPangan->komoditas->nama}."
                    : "{$request->user()->name} mengembalikan data {$neracaPangan->komoditas->nama} untuk revisi.",
                'dibaca'  => false,
            ]);
        }

        return redirect()
            ->route('verifikator.menunggu')
            ->with('status', $validated['status'] === 'valid'
                ? 'Data berhasil divalidasi.'
                : 'Data dikembalikan untuk revisi.');
    }
}