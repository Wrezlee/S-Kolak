<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengunci sesi login ke "sidik jari" browser (User-Agent) tempat sesi itu
 * dibuat saat login (lihat LoginController::login()).
 *
 * Kalau session cookie yang sama dipakai/disalin ke browser lain, request
 * yang datang punya User-Agent berbeda dari yang tersimpan -> sesi dianggap
 * tidak valid untuk browser ini, user langsung di-logout dan diarahkan ke
 * halaman login (tidak langsung diberi akses).
 */
class EnsureSessionBoundToBrowser
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::getDefaultDriver();

        foreach (['admin', 'operator', 'verifikator', 'web'] as $g) {
            if (Auth::guard($g)->check()) {
                $guard = $g;
                break;
            }
        }

        if (! Auth::guard($guard)->check()) {
            return $next($request);
        }

        $sessionKey = "browser_fingerprint_{$guard}";
        $expected = $request->session()->get($sessionKey);
        $actual = self::fingerprint($request);

        if ($expected === null) {
            // Sesi lama (sebelum fitur ini ada) belum punya fingerprint —
            // simpan sekarang supaya mulai terlindungi dari titik ini.
            $request->session()->put($sessionKey, $actual);

            return $next($request);
        }

        if (! hash_equals($expected, $actual)) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'id' => 'Sesi login tidak berlaku di browser ini. Silakan login ulang.',
            ]);
        }

        return $next($request);
    }

    /**
     * Sidik jari sederhana berbasis User-Agent browser yang sedang dipakai.
     */
    public static function fingerprint(Request $request): string
    {
        return hash('sha256', $request->userAgent() ?? '');
    }
}
