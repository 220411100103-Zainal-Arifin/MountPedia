<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Membaca teks jam buka bebas (kolom rental_shops.opening_hours) menjadi status
 * "buka/tutup" saat ini. Hanya format yang pasti yang dikenali:
 *   "24 jam", "06.00–21.00", "06:00 - 21:00", "Setiap hari 07.00 s/d 22.00"
 * Teks lain (beberapa rentang, jadwal per hari, dsb.) mengembalikan null, sehingga
 * halaman hanya menampilkan teksnya tanpa menebak status.
 *
 * Perbandingan memakai zona waktu aplikasi (config/app.php 'timezone').
 */
final class OpeningHours
{
    private const RANGE = '/(\d{1,2})[.:](\d{2})\s*(?:-|–|—|s\/d|sd|sampai|hingga)\s*(\d{1,2})[.:](\d{2})/u';

    /** @return array{open: bool, label: string, detail: ?string}|null */
    public static function status(?string $text, ?CarbonInterface $now = null): ?array
    {
        $text = trim((string) $text);
        if ($text === '') {
            return null;
        }

        $lower = mb_strtolower($text);
        if (preg_match('/24\s*jam|24\/7|24 hours/u', $lower)) {
            return ['open' => true, 'label' => 'Buka 24 jam', 'detail' => null];
        }

        if (preg_match_all(self::RANGE, $lower, $m, PREG_SET_ORDER) !== 1) {
            return null; // tidak ada, atau lebih dari satu rentang: jangan menebak
        }

        [, $h1, $m1, $h2, $m2] = $m[0];
        $start = (int) $h1 * 60 + (int) $m1;
        $end = (int) $h2 * 60 + (int) $m2;
        if ($start === $end || $start > 1439 || $end > 1439) {
            return null;
        }

        $now ??= now();
        $current = $now->hour * 60 + $now->minute;
        $open = $start < $end
            ? ($current >= $start && $current < $end)
            : ($current >= $start || $current < $end); // lewat tengah malam

        $fmt = fn (int $min) => sprintf('%02d.%02d', intdiv($min, 60), $min % 60);

        return $open
            ? ['open' => true, 'label' => 'Buka sekarang', 'detail' => 'Tutup pukul '.$fmt($end)]
            : ['open' => false, 'label' => 'Tutup', 'detail' => 'Buka pukul '.$fmt($start)];
    }
}
