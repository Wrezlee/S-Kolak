<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class FormatWaktu
{
    /**
     * Format sebuah waktu menjadi teks relatif berbahasa Indonesia,
     * mis. "Baru saja", "2 jam yang lalu", "Kemarin", "3 hari yang lalu".
     * Dipakai di halaman "Data Menunggu Verifikasi" agar tanggal input
     * lebih mudah dipahami sekilas dibanding tanggal absolut.
     *
     * Lebih dari 6 hari yang lalu akan jatuh ke format tanggal biasa
     * (mis. "14 Apr 2025") supaya tetap presisi untuk data yang lama.
     */
    public static function relatif($waktu): string
    {
        $waktu = Carbon::parse($waktu);
        $sekarang = Carbon::now();

        $detik = (int) floor($waktu->diffInSeconds($sekarang));
        if ($detik < 60) {
            return 'Baru saja';
        }

        $menit = (int) floor($waktu->diffInMinutes($sekarang));
        if ($menit < 60) {
            return $menit . ' menit yang lalu';
        }

        if ($waktu->isToday()) {
            $jam = (int) floor($waktu->diffInHours($sekarang));
            return $jam . ' jam yang lalu';
        }

        if ($waktu->isYesterday()) {
            return 'Kemarin';
        }

        $hari = (int) floor($waktu->diffInDays($sekarang));
        if ($hari < 7) {
            return $hari . ' hari yang lalu';
        }

        return $waktu->translatedFormat('d M Y');
    }
}