<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna dengan pencarian nama/ID dan filter role.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.manage_user', [
            'users'      => $users,
            'notifCount' => Notifikasi::where('dibaca', false)->count(),
        ]);
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'name'     => ['required', 'string', 'max:150'],
            'role'     => ['required', Rule::in(['Admin', 'Operator', 'Verifikator'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'username' => $validated['username'],
            'name'     => $validated['name'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status'   => 'Aktif',
        ]);

        return back()->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data pengguna (nama, role, dan password jika diisi).
     * ID pengguna (username) tidak dapat diubah lewat form ini.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'role'     => ['required', Rule::in(['Admin', 'Operator', 'Verifikator'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->role = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('status', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user)
    {
        // Cegah admin menghapus akunnya sendiri secara tidak sengaja.
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user->delete();

        return back()->with('status', 'Pengguna berhasil dihapus.');
    }
}