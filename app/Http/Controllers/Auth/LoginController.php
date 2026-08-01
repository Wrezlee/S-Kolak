<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSessionBoundToBrowser;
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
        // bukan guard 'web' default. Setiap role punya akses/guard sendiri.
        $guard = in_array($user->role, self::ROLE_GUARDS, true) ? $user->role : 'web';

        // Satu browser hanya boleh punya satu sesi role aktif (alasan
        // keamanan data). Kalau ada role lain yang masih login di browser
        // ini, putuskan dulu sebelum login sebagai role yang baru.
        foreach ([...self::ROLE_GUARDS, 'web'] as $otherGuard) {
            if ($otherGuard !== $guard && Auth::guard($otherGuard)->check()) {
                Auth::guard($otherGuard)->logout();
            }
        }

        Auth::guard($guard)->login($user);
        $request->session()->regenerate();

        // Ikat sesi ini ke browser (User-Agent) yang dipakai saat login.
        // Kalau cookie sesi ini nanti dipakai/dipindah ke browser lain,
        // EnsureSessionBoundToBrowser akan menolaknya dan memaksa login
        // ulang, bukan langsung memberi akses.
        $request->session()->put(
            "browser_fingerprint_{$guard}",
            EnsureSessionBoundToBrowser::fingerprint($request)
        );

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