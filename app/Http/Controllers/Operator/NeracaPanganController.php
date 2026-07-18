<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Admin\DataNeracaController;
use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\NeracaPangan;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NeracaPanganController extends Controller
{
    /**
     * Halaman "Data Neraca Saya": seluruh data neraca pangan milik
     * operator yang sedang login (diinput_oleh = auth()->id()).
     *
     * Draft tidak ikut dikirim ke view karena belum diajukan untuk
     * verifikasi — belum relevan ditampilkan di halaman ini. Urutan
     * "revisi selalu di atas" ditangani di sisi Blade (operator/data_neraca.blade.php),
     * jadi di sini query cukup diurutkan berdasarkan periode terbaru.
     */
    public function index(Request $request)
    {
        $operatorId = $request->user()->id;

        $items = NeracaPangan::with(['komoditas', 'verifikator'])
            ->where('diinput_oleh', $operatorId)
            ->where('status', '!=', 'draft')
            ->orderByDesc('periode')
            ->orderByDesc('id')
            ->get();

        return view('operator.data-neraca', [
            'items'      => $items,
            'notifCount' => Notifikasi::where('user_id', $operatorId)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Tampilkan form input neraca pangan.
     */
    public function create()
    {
        return view('operator.input-neraca', [
            'komoditasList' => Komoditas::where('status', 'Aktif')->orderBy('nama')->get(),
        ]);
    }

    /**
     * Simpan data neraca pangan baru dan langsung ajukan untuk verifikasi.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun'                      => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'                      => ['required', 'integer', 'min:1', 'max:12'],
            'komoditas_id'               => ['required', 'exists:komoditas,id'],
            'stok_awal'                  => ['required', 'numeric', 'min:0'],
            'produksi'                   => ['required', 'numeric', 'min:0'],
            'masuk'                      => ['required', 'numeric', 'min:0'],
            'keluar'                     => ['required', 'numeric', 'min:0'],
            'kebutuhan_rumah_tangga'     => ['required', 'numeric', 'min:0'],
            'kebutuhan_non_rumah_tangga' => ['required', 'numeric', 'min:0'],
        ], [], [
            'komoditas_id' => 'komoditas',
        ]);

        $periode = Carbon::create($validated['tahun'], $validated['bulan'], 1);

        $sudahAda = NeracaPangan::where('komoditas_id', $validated['komoditas_id'])
            ->whereYear('periode', $periode->year)
            ->whereMonth('periode', $periode->month)
            ->exists();

        if ($sudahAda) {
            return back()->withInput()->withErrors([
                'komoditas_id' => 'Data neraca untuk komoditas dan periode ini sudah pernah diinput.',
            ]);
        }

        $neracaPangan = NeracaPangan::create([
            'komoditas_id'               => $validated['komoditas_id'],
            'periode'                    => $periode,
            'stok_awal'                  => $validated['stok_awal'],
            'produksi'                   => $validated['produksi'],
            'masuk'                      => $validated['masuk'],
            'keluar'                     => $validated['keluar'],
            'kebutuhan_rumah_tangga'     => $validated['kebutuhan_rumah_tangga'],
            'kebutuhan_non_rumah_tangga' => $validated['kebutuhan_non_rumah_tangga'],
            'status'                     => 'menunggu',
            'diinput_oleh'               => $request->user()->id,
            'diajukan_pada'              => now(),
        ]);

        $this->notifikasiVerifikator($neracaPangan, $request->user()->name);

        return redirect()->route('operator.input')
            ->with('justSubmitted', true);
    }

    /**
     * Kirim notifikasi ke seluruh user berperan verifikator bahwa ada
     * data neraca pangan baru yang menunggu ditinjau.
     */
    private function notifikasiVerifikator(NeracaPangan $neracaPangan, string $namaOperator): void
    {
        $komoditasNama = $neracaPangan->komoditas->nama ?? optional(Komoditas::find($neracaPangan->komoditas_id))->nama ?? 'data neraca pangan';

        $verifikatorIds = \App\Models\User::where('role', 'verifikator')->pluck('id');

        foreach ($verifikatorIds as $verifikatorId) {
            Notifikasi::create([
                'user_id' => $verifikatorId,
                'judul'   => 'Data baru menunggu verifikasi',
                'pesan'   => "{$namaOperator} mengirim data {$komoditasNama} untuk diverifikasi.",
                'dibaca'  => false,
            ]);
        }
    }

    /**
     * Perbaiki data neraca pangan yang dikembalikan verifikator (status "revisi"),
     * lalu ajukan ulang untuk verifikasi. Dipanggil dari modal "Revisi" di halaman
     * Data Neraca Saya (operator/data_neraca.blade.php).
     *
     * Periode & komoditas tidak bisa diubah di sini — operator hanya memperbaiki
     * angka-angka neraca. Kalau memang salah periode/komoditas, harus dihapuskan
     * lewat Admin lalu diinput ulang dari awal.
     */
    public function update(Request $request, NeracaPangan $neracaPangan)
    {
        abort_unless($neracaPangan->diinput_oleh === $request->user()->id, 403);

        if ($neracaPangan->status !== 'revisi') {
            return back()->with('status', 'Hanya data berstatus "Perlu Revisi" yang dapat diperbaiki.');
        }

        $validated = $request->validate([
            'stok_awal'                  => ['required', 'numeric', 'min:0'],
            'produksi'                   => ['required', 'numeric', 'min:0'],
            'masuk'                      => ['required', 'numeric', 'min:0'],
            'keluar'                     => ['required', 'numeric', 'min:0'],
            'kebutuhan_rumah_tangga'     => ['required', 'numeric', 'min:0'],
            'kebutuhan_non_rumah_tangga' => ['required', 'numeric', 'min:0'],
        ]);

        $neracaPangan->update([
            'stok_awal'                  => $validated['stok_awal'],
            'produksi'                   => $validated['produksi'],
            'masuk'                      => $validated['masuk'],
            'keluar'                     => $validated['keluar'],
            'kebutuhan_rumah_tangga'     => $validated['kebutuhan_rumah_tangga'],
            'kebutuhan_non_rumah_tangga' => $validated['kebutuhan_non_rumah_tangga'],
            'status'                     => 'menunggu',
            'diajukan_pada'              => now(),
            // Reset hasil verifikasi sebelumnya — data ini menunggu ditinjau ulang
            // dari awal oleh verifikator (catatan lama tetap tersimpan sbg riwayat).
            'diverifikasi_oleh'          => null,
            'diverifikasi_pada'          => null,
        ]);

        $this->notifikasiVerifikator($neracaPangan, $request->user()->name);

        return redirect()->route('operator.data')
            ->with('status', 'Data berhasil diperbaiki dan dikirim ulang untuk verifikasi.');
    }

    /**
     * AJAX: ambil nilai neraca (stok akhir) bulan sebelumnya untuk komoditas yang sama,
     * dipakai untuk auto-isi field Stok Awal pada bulan berjalan.
     */
    public function stokAwal(Request $request)
    {
        $validated = $request->validate([
            'komoditas_id' => ['required', 'integer'],
            'tahun'        => ['required', 'integer'],
            'bulan'        => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $periodeSebelumnya = Carbon::create($validated['tahun'], $validated['bulan'], 1)->subMonth();

        $dataSebelumnya = NeracaPangan::where('komoditas_id', $validated['komoditas_id'])
            ->whereYear('periode', $periodeSebelumnya->year)
            ->whereMonth('periode', $periodeSebelumnya->month)
            ->first();

        if (! $dataSebelumnya) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'     => true,
            'stok_awal' => DataNeracaController::hitungNilaiNeraca($dataSebelumnya),
            'periode'   => DataNeracaController::formatPeriode($dataSebelumnya->periode),
        ]);
    }
}