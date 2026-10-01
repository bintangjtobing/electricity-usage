<?php

namespace App\Support;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Carbon\Carbon;

/**
 * Satu-satunya sumber kebenaran untuk perhitungan pemakaian listrik.
 *
 * Sebelumnya dashboard dan modal konfirmasi punya salinan logika masing-masing
 * yang perlahan berbeda dan sama-sama keliru.
 */
class UsageCalculator
{
    /**
     * Rata-rata untuk proyeksi hanya memakai sekian hari terakhir. Rata-rata
     * sepanjang riwayat terseret data lama: di produksi, catatan April-Juli
     * (hasil input awal, bukan bacaan meteran) menurunkannya ke 14,94 kWh/hari
     * padahal tiap selang sejak Agustus konsisten ~17 -- "cukup 22 hari" yang
     * kenyataannya cuma ~19.
     */
    public const RECENT_WINDOW_DAYS = 30;

    /**
     * Pemakaian antara dua pembacaan meteran:
     *
     *     pemakaian = sisa_awal + pembelian_di_antaranya - sisa_akhir
     *
     * Rumus ini benar untuk semua kasus -- termasuk beberapa pembelian dalam
     * satu selang -- tanpa perlu menebak dari naik/turunnya angka.
     *
     * Dengan $since, hitungan dimulai dari pembacaan terakhir pada/sebelum
     * $since, supaya selang yang melintasi batas tetap terhitung utuh.
     *
     * @return array{totalUsage: float, totalDays: float, dailyAverage: float}
     */
    public static function stats(?Carbon $since = null): array
    {
        $checks = ElectricityUsageCheck::orderBy('created_at', 'asc')->get();

        if ($since) {
            $anchor = $checks->last(fn ($check) => $check->created_at->lte($since));

            if ($anchor) {
                $checks = $checks
                    ->filter(fn ($check) => $check->created_at->gte($anchor->created_at))
                    ->values();
            }
        }

        if ($checks->count() < 2) {
            return ['totalUsage' => 0.0, 'totalDays' => 0.0, 'dailyAverage' => 0.0];
        }

        $totalUsage = 0.0;
        $totalDays = 0.0;

        for ($i = 1; $i < $checks->count(); $i++) {
            $prev = $checks[$i - 1];
            $curr = $checks[$i];

            $usage = self::usageBetween($prev, $curr);

            // Pecahan hari dipertahankan. diffInDays() memotong pecahan, sehingga
            // selang 1,9 hari terhitung 1 hari dan selang di hari yang sama jadi
            // 0 -- pembagi mengecil dan rata-rata terlihat lebih boros dari aslinya.
            $totalDays += Carbon::parse($prev->created_at)
                ->diffInHours(Carbon::parse($curr->created_at)) / 24;

            $totalUsage += max(0, $usage);
        }

        return [
            'totalUsage' => round($totalUsage, 2),
            'totalDays' => round($totalDays, 2),
            'dailyAverage' => $totalDays > 0 ? round($totalUsage / $totalDays, 2) : 0.0,
        ];
    }

    /** Rata-rata harian {@see RECENT_WINDOW_DAYS} hari terakhir, dasar semua proyeksi. */
    public static function dailyAverage(): float
    {
        $lastCheck = ElectricityUsageCheck::latest()->first();

        if (! $lastCheck) {
            return 0.0;
        }

        return self::stats(
            $lastCheck->created_at->copy()->subDays(self::RECENT_WINDOW_DAYS)
        )['dailyAverage'];
    }

    /**
     * Perkiraan sisa saat ini: pembacaan terakhir dikurangi pemakaian rata-rata
     * sejak pembacaan itu. Meteran berhenti di nol, jadi tidak pernah negatif.
     */
    public static function estimatedRemaining(ElectricityUsageCheck $lastCheck, float $dailyAverage): float
    {
        $daysSince = Carbon::parse($lastCheck->created_at)->diffInHours(now()) / 24;

        return max(0.0, round((float) $lastCheck->kwh_remaining - $dailyAverage * $daysSince, 2));
    }

    public static function usageBetween(ElectricityUsageCheck $prev, ElectricityUsageCheck $curr): float
    {
        return (float) $prev->kwh_remaining
            + self::kwhBoughtBetween($prev->created_at, $curr->created_at)
            - (float) $curr->kwh_remaining;
    }

    /**
     * Selang antar pembacaan meteran nyata (bukan estimasi), berurutan waktu.
     *
     * Titik estimasi dilewati karena nilainya tebakan -- mis. "sisa terakhir +
     * dibeli" menganggap tidak ada pemakaian sejak cek terakhir, sehingga selang
     * sebelum titik itu tampak 0 dan selang sesudahnya membengkak (1-15 Juni di
     * produksi). Rumus pemakaian berantai, jadi total di antara dua bacaan
     * nyata tetap sama persis.
     *
     * @return list<array{from: ElectricityUsageCheck, to: ElectricityUsageCheck, usage: float, seconds: int}>
     */
    public static function measuredIntervals(): array
    {
        $checks = ElectricityUsageCheck::measured()->orderBy('created_at', 'asc')->get();
        $intervals = [];

        for ($i = 1; $i < $checks->count(); $i++) {
            $from = $checks[$i - 1];
            $to = $checks[$i];

            $intervals[] = [
                'from' => $from,
                'to' => $to,
                'usage' => max(0.0, round(self::usageBetween($from, $to), 2)),
                'seconds' => $from->created_at->diffInSeconds($to->created_at),
            ];
        }

        return $intervals;
    }

    /** Total kWh yang dibeli dalam selang (from, to]. */
    public static function kwhBoughtBetween($from, $to): float
    {
        return (float) ElectricityPurchase::where('created_at', '>', $from)
            ->where('created_at', '<=', $to)
            ->sum('kwh_bought');
    }
}
