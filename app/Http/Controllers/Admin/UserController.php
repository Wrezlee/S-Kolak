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
                      ->orWhere('login_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->string('role')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.manage-user', [
            'users'      => $users,
            'notifCount' => Notifikasi::where('user_id', $request->user()->id)
                ->where('dibaca', false)
                ->count(),
        ]);
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'login_id' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,login_id'],
            'name'     => ['required', 'string', 'max:150'],
            'role'     => ['required', Rule::in(['admin', 'operator', 'verifikator'])],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'login_id' => $validated['login_id'],
            'name'     => $validated['name'],
            'role'     => $validated['role'],
            'password' => Hash::make($validated['password']),
            'status'   => 'Aktif',
        ]);

        if (in_array($validated['role'], ['operator', 'verifikator'])) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul'   => 'Akun Anda telah dibuat',
                'pesan'   => "Akun Anda telah dibuat oleh admin dengan role {$validated['role']}. Selamat bergabung!",
                'dibaca'  => false,
            ]);
        }

        return back()->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data pengguna (nama, role, dan password jika diisi).
     * ID pengguna (login_id) tidak dapat diubah lewat form ini.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'role'     => ['required', Rule::in(['admin', 'operator', 'verifikator'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $roleLama = $user->role;
        $passwordDiubah = !empty($validated['password']);

        $user->name = $validated['name'];
        $user->role = $validated['role'];

        if ($passwordDiubah) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        // Beri tahu operator/verifikator yang bersangkutan jika ada perubahan
        // pada akun mereka (data diri, role, atau password).
        if (in_array($validated['role'], ['operator', 'verifikator'])) {
            $perubahan = [];

            if ($roleLama !== $validated['role']) {
                $perubahan[] = "role menjadi {$validated['role']}";
            }
            if ($passwordDiubah) {
                $perubahan[] = 'password';
            }

            $pesan = $perubahan
                ? 'Admin memperbarui akun Anda: ' . implode(' dan ', $perubahan) . '.'
                : 'Admin memperbarui data akun Anda.';

            Notifikasi::create([
                'user_id' => $user->id,
                'judul'   => 'Akun Anda diperbarui',
                'pesan'   => $pesan,
                'dibaca'  => false,
            ]);
        }

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