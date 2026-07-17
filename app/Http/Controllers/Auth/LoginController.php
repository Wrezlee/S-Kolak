<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
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

        // Sesuaikan 'username' dengan nama kolom ID di tabel users Anda
        if (Auth::attempt(['username' => $credentials['id'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();

            return match (Auth::user()->role ?? null) {
                'admin'       => redirect()->route('admin.dashboard'),
                'operator'    => redirect()->route('operator.dashboard'),
                'verifikator' => redirect()->route('verifikator.dashboard'),
                default       => redirect()->route('dashboard'),
            };
        }

        return back()->withErrors(['id' => 'ID atau password tidak sesuai.'])->onlyInput('id');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard');
    }
}