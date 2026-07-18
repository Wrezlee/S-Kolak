<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Admin\DataNeracaController;
use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\NeracaPangan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NeracaPanganController extends Controller
{
    /**
     * Tampilkan form input neraca pangan.
     */
    public function create()
    {
        return view('operator.input_neraca', [
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

        NeracaPangan::create([
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

        return redirect()->route('operator.input')
            ->with('status', 'Data neraca pangan berhasil dikirim untuk verifikasi.');
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