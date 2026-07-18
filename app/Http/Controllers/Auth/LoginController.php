<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /** Role yang punya guard session sendiri-sendiri (lihat config/auth.php). */
    private const ROLE_GUARDS = ['admin', 'operator', 'verifikator'];

    public function showLoginForm()
    {
        return view('auth.login'); // pastikan file login.blade.php ada di resources/views/auth/
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'id'       => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login_id', $credentials['id'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['id' => 'ID atau password tidak sesuai.'])->onlyInput('id');
        }

        // Login lewat guard khusus role user ini (admin/operator/verifikator),
        // bukan guard 'web' default. Dengan begini status login tiap role
        // disimpan terpisah dalam session, jadi login sebagai role lain di
        // tab lain tidak akan menggeser/menimpa login role ini.
        $guard = in_array($user->role, self::ROLE_GUARDS, true) ? $user->role : 'web';

        Auth::guard($guard)->login($user);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        return match ($user->role) {
            'admin'       => redirect()->route('admin.dashboard'),
            'operator'    => redirect()->route('operator.dashboard'),
            'verifikator' => redirect()->route('verifikator.dashboard'),
            default       => redirect()->route('dashboard'),
        };
    }

    public function logout(Request $request)
    {
        // Logout hanya dari guard role yang sedang aktif di tab ini,
        // supaya login role lain (di tab lain, browser sama) tidak ikut terputus.
        foreach ([...self::ROLE_GUARDS, 'web'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }
}