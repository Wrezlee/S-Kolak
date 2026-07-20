<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ForgotPasswordController extends Controller
{
    /** Token reset kadaluarsa setelah 60 menit. */
    private const EXPIRE_MINUTES = 60;

    /**
     * Tampilkan form "Lupa Password" (input Login ID).
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Proses permintaan reset password: buat token & tampilkan tautan reset.
     *
     * Catatan: sistem ini login memakai Login ID (bukan email) dan belum
     * terhubung ke server email, sehingga tautan reset ditampilkan langsung
     * di layar (bisa disalin/dikirim manual oleh admin ke pengguna terkait).
     * Jika nanti sudah ada mailer aktif, cukup ganti bagian ini dengan
     * Mail::to(...)->send(...) tanpa mengubah struktur token di atas.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
        ]);

        $user = User::where('login_id', $request->input('id'))->first();

        if (! $user) {
            return back()
                ->withErrors(['id' => 'Login ID tidak ditemukan.'])
                ->onlyInput('id');
        }

        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['login_id' => $user->login_id],
            ['token' => Hash::make($plainToken), 'created_at' => now()]
        );

        $resetUrl = route('password.reset', ['token' => $plainToken]) . '?id=' . urlencode($user->login_id);

        return redirect()->route('password.request')->with([
            'reset_url' => $resetUrl,
            'reset_id'  => $user->login_id,
        ]);
    }

    /**
     * Tampilkan form set password baru, jika token valid & belum kadaluarsa.
     */
    public function showResetForm(Request $request, string $token)
    {
        $loginId = $request->query('id', '');

        $record = DB::table('password_reset_tokens')->where('login_id', $loginId)->first();

        if (! $record || ! Hash::check($token, $record->token) || $this->isExpired($record->created_at)) {
            return redirect()->route('password.request')
                ->withErrors(['id' => 'Tautan reset password tidak valid atau sudah kadaluarsa. Silakan minta tautan baru.']);
        }

        return view('auth.reset-password', [
            'token'   => $token,
            'loginId' => $loginId,
        ]);
    }

    /**
     * Simpan password baru.
     */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'id'                    => 'required|string',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('login_id', $data['id'])->first();

        if (! $record || ! Hash::check($data['token'], $record->token) || $this->isExpired($record->created_at)) {
            return redirect()->route('password.request')
                ->withErrors(['id' => 'Tautan reset password tidak valid atau sudah kadaluarsa. Silakan minta tautan baru.']);
        }

        $user = User::where('login_id', $data['id'])->first();

        if (! $user) {
            return redirect()->route('password.request')
                ->withErrors(['id' => 'Pengguna tidak ditemukan.']);
        }

        $user->forceFill(['password' => $data['password']])->save();

        DB::table('password_reset_tokens')->where('login_id', $data['id'])->delete();

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan login dengan password baru Anda.');
    }

    private function isExpired($createdAt): bool
    {
        if (! $createdAt) {
            return true;
        }

        return Carbon::parse($createdAt)->addMinutes(self::EXPIRE_MINUTES)->isPast();
    }
}