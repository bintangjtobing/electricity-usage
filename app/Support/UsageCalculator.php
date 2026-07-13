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
     * Pemakaian antara dua pembacaan meteran:
     *
     *     pemakaian = sisa_awal + pembelian_di_antaranya - sisa_akhir
     *
     * Rumus ini benar untuk semua kasus -- termasuk beberapa pembelian dalam
     * satu selang -- tanpa perlu menebak dari naik/turunnya angka.
     *
     * @return array{totalUsage: float, totalDays: float, dailyAverage: float}
     */
    public static function stats(): array
    {
        $checks = ElectricityUsageCheck::orderBy('created_at', 'asc')->get();

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

    public static function dailyAverage(): float
    {
        return self::stats()['dailyAverage'];
    }

    public static function usageBetween(ElectricityUsageCheck $prev, ElectricityUsageCheck $curr): float
    {
        return (float) $prev->kwh_remaining
            + self::kwhBoughtBetween($prev->created_at, $curr->created_at)
            - (float) $curr->kwh_remaining;
    }

    /** Total kWh yang dibeli dalam selang (from, to]. */
    public static function kwhBoughtBetween($from, $to): float
    {
        return (float) ElectricityPurchase::where('created_at', '>', $from)
            ->where('created_at', '<=', $to)
            ->sum('kwh_bought');
    }
}
