<?php

if (! function_exists('fmt_neraca')) {
    /**
     * Format angka neraca pangan.
     * Menampilkan sampai 2 angka desimal, tapi kalau desimalnya nol
     * (misal 91,00) maka dibulatkan tampil jadi "91" saja.
     */
    function fmt_neraca($value, int $maxDecimals = 2): string
    {
        $formatted = number_format((float) $value, $maxDecimals, ',', '.');

        if (str_contains($formatted, ',')) {
            [$intPart, $decPart] = explode(',', $formatted);
            $decPart = rtrim($decPart, '0');
            $formatted = $decPart === '' ? $intPart : $intPart.','.$decPart;
        }

        return $formatted;
    }
}
