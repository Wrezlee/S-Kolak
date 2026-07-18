<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Notifikasi;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    /**
     * Tampilkan daftar komoditas.
     */
    public function index()
    {
        return view('admin.master_komoditas', [
            'komoditas'  => Komoditas::orderBy('nama')->get(),
            'notifCount' => Notifikasi::where('dibaca', false)->count(),
        ]);
    }

    /**
     * Simpan komoditas baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:komoditas,nama'],
        ]);

        Komoditas::create([
            'nama' => $validated['nama'],
        ]);

        return back()->with('status', 'Komoditas baru berhasil ditambahkan.');
    }

    /**
     * Perbarui nama komoditas.
     */
    public function update(Request $request, Komoditas $komoditas)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:komoditas,nama,' . $komoditas->id],
        ]);

        $komoditas->update(['nama' => $validated['nama']]);

        return back()->with('status', 'Komoditas berhasil diperbarui.');
    }

    /**
     * Hapus komoditas.
     */
    public function destroy(Komoditas $komoditas)
    {
        // Cegah penghapusan komoditas yang masih punya data neraca terkait.
        if ($komoditas->neracaPangan()->exists()) {
            return back()->withErrors([
                'delete' => 'Komoditas ini tidak dapat dihapus karena masih memiliki data neraca pangan terkait.',
            ]);
        }

        $komoditas->delete();

        return back()->with('status', 'Komoditas berhasil dihapus.');
    }
}