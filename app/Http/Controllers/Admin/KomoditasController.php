<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Komoditas;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;

class KomoditasController extends Controller
{
    /**
     * Tampilkan daftar komoditas, dengan pencarian berdasarkan nama.
     */
    public function index(Request $request)
    {
        $komoditas = Komoditas::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('nama', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->get();

        return view('admin.master-komoditas', [
            'komoditas'  => $komoditas,
            'notifCount' => Notifikasi::where('user_id', auth()->id())
                ->where('dibaca', false)
                ->count(),
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

        $this->notifikasiOperatorVerifikator(
            'Komoditas baru ditambahkan',
            "Admin menambahkan komoditas baru: {$validated['nama']}."
        );

        return back()->with('status', 'Komoditas baru berhasil ditambahkan.');
    }

    /**
     * Perbarui nama komoditas.
     */
    public function update(Request $request, Komoditas $komoditas)
    {
        $namaLama = $komoditas->nama;

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:komoditas,nama,' . $komoditas->id],
        ]);

        $komoditas->update(['nama' => $validated['nama']]);

        $this->notifikasiOperatorVerifikator(
            'Komoditas diperbarui',
            $namaLama === $validated['nama']
                ? "Admin memperbarui data komoditas {$validated['nama']}."
                : "Admin mengubah nama komoditas {$namaLama} menjadi {$validated['nama']}."
        );

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

        $namaKomoditas = $komoditas->nama;

        $komoditas->delete();

        $this->notifikasiOperatorVerifikator(
            'Komoditas dihapus',
            "Admin menghapus komoditas {$namaKomoditas}."
        );

        return back()->with('status', 'Komoditas berhasil dihapus.');
    }

    /**
     * Kirim notifikasi ke seluruh pengguna dengan role operator dan verifikator,
     * supaya semua aktivitas admin yang memengaruhi data master/neraca pangan
     * ikut terlihat di halaman notifikasi mereka.
     */
    private function notifikasiOperatorVerifikator(string $judul, string $pesan): void
    {
        $penerimaIds = User::whereIn('role', ['operator', 'verifikator'])->pluck('id');

        foreach ($penerimaIds as $userId) {
            Notifikasi::create([
                'user_id' => $userId,
                'judul'   => $judul,
                'pesan'   => $pesan,
                'dibaca'  => false,
            ]);
        }
    }
}