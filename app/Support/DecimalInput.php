<?php

namespace App\Support;

/**
 * Angka desimal yang diketik bebas, mis. dari keyboard HP berlokal Indonesia.
 *
 * Input sengaja bukan type="number": keyboard berlokal Indonesia mengetik koma,
 * dan browser lalu mengirim '' (yang ditolak MySQL strict untuk kolom decimal).
 */
final class DecimalInput
{
    /**
     * Teks bebas -> string angka bertitik, atau null kalau kosong.
     *
     * Koma maupun titik diterima; kalau keduanya ada, yang terakhir dianggap
     * desimal ("1.234,5" -> "1234.5"). Teks bukan angka dibiarkan supaya
     * ditolak validasi numeric.
     */
    public static function normalize($value): ?string
    {
        $value = str_replace(' ', '', (string) $value);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $thousands = strrpos($value, ',') > strrpos($value, '.') ? '.' : ',';
            $value = str_replace($thousands, '', $value);
        }

        return str_replace(',', '.', $value);
    }
}
