<?php

namespace App\Support;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Carbon\Carbon;

/**
 * Data kalender riwayat satu bulan: grid minggu (Minggu-Sabtu), dan untuk
 * tiap tanggal pembelian, pembacaan sisa, serta perkiraan kWh terpakai.
 */
class UsageCalendar
{
    public const WEEKDAYS = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];

    /**
     * @return array{
     *     label: string,
     *     weeks: list<list<array<string, mixed>>>,
     *     totals: array<string, mixed>,
     *     roles: array<int, string>,
     * }
     */
    public static function month(Carbon $month): array
    {
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $purchases = ElectricityPurchase::whereBetween('created_at', [$gridStart, $gridEnd])
            ->orderBy('created_at')->get();
        $checks = ElectricityUsageCheck::whereBetween('created_at', [$gridStart, $gridEnd])
            ->orderBy('created_at')->get();

        $usage = self::dailyUsage($gridStart, $gridEnd);
        $roles = self::checkRoles($checks, $purchases);

        $weeks = [];
        $totals = [
            'purchaseCount' => 0, 'purchasePrice' => 0.0, 'purchaseKwh' => 0.0,
            'usage' => 0.0, 'coveredSeconds' => 0,
            'checkCount' => 0, 'estimatedCount' => 0, 'lastCheck' => null,
        ];

        for ($date = $gridStart->copy(); $date->lte($gridEnd); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $inMonth = $date->month === $monthStart->month;

            $dayPurchases = $purchases->filter(fn ($p) => $p->created_at->format('Y-m-d') === $key)->values();
            $dayChecks = $checks->filter(fn ($c) => $c->created_at->format('Y-m-d') === $key)->values();
            $dayUsage = $usage[$key] ?? null;

            $day = [
                'date' => $date->copy(),
                'key' => $key,
                'inMonth' => $inMonth,
                'isToday' => $date->isToday(),
                'purchases' => $dayPurchases,
                'checks' => $dayChecks,
                'usage' => $dayUsage ? round($dayUsage['usage'], 2) : null,
                'coveredSeconds' => $dayUsage['seconds'] ?? 0,
                'coveredFrom' => $dayUsage['from'] ?? null,
                'coveredUntil' => $dayUsage['until'] ?? null,
                // Laju per 24 jam, supaya hari yang baru tercatat sebagian
                // (mis. hari ini) dinilai hemat/boros secara adil.
                'rate' => $dayUsage && $dayUsage['seconds'] > 0
                    ? round($dayUsage['usage'] / $dayUsage['seconds'] * 86400, 2)
                    : null,
                'intervals' => $dayUsage['intervals'] ?? [],
            ];

            if ($inMonth) {
                $totals['purchaseCount'] += $dayPurchases->count();
                $totals['purchasePrice'] += $dayPurchases->sum('purchase_price');
                $totals['purchaseKwh'] += $dayPurchases->sum('kwh_bought');
                $totals['usage'] += $dayUsage['usage'] ?? 0;
                $totals['coveredSeconds'] += $dayUsage['seconds'] ?? 0;
                $totals['checkCount'] += $dayChecks->count();
                $totals['estimatedCount'] += $dayChecks->where('is_estimated', true)->count();
                $totals['lastCheck'] = $dayChecks->last() ?? $totals['lastCheck'];
            }

            $weeks[intdiv($gridStart->diffInDays($date), 7)][] = $day;
        }

        $coveredDays = $totals['coveredSeconds'] / 86400;
        $totals['usage'] = round($totals['usage'], 2);
        $totals['coveredDays'] = round($coveredDays, 1);
        $totals['dailyAverage'] = $coveredDays > 0 ? round($totals['usage'] / $coveredDays, 2) : null;

        return [
            'label' => $monthStart->copy()->locale('id')->translatedFormat('F Y'),
            'weeks' => $weeks,
            'totals' => $totals,
            'roles' => $roles,
        ];
    }

    /**
     * kWh terpakai per tanggal. Pemakaian di antara dua bacaan meteran nyata
     * dibagi rata per detik, lalu dijumlah per tanggal -- tanggal tanpa
     * pembacaan pun tetap punya perkiraan.
     *
     * @return array<string, array{usage: float, seconds: int, from: Carbon, until: Carbon, intervals: list<array>}>
     */
    public static function dailyUsage(Carbon $from, Carbon $to): array
    {
        $days = [];

        foreach (UsageCalculator::measuredIntervals() as $interval) {
            $start = $interval['from']->created_at;
            $end = $interval['to']->created_at;

            if ($interval['seconds'] === 0 || $end->lte($from) || $start->gt($to)) {
                continue;
            }

            $perSecond = $interval['usage'] / $interval['seconds'];
            // max()/min() Carbon bisa mengembalikan objek pembandingnya sendiri;
            // copy() supaya startOfDay() tidak menggeser batas $from.
            $day = $start->copy()->max($from)->copy()->startOfDay();

            for (; $day->lt($end) && $day->lte($to); $day->addDay()) {
                $overlapStart = $day->copy()->max($start)->copy();
                $overlapEnd = $day->copy()->addDay()->min($end)->copy();
                $overlap = $overlapStart->diffInSeconds($overlapEnd);

                if ($overlap === 0) {
                    continue;
                }

                $key = $day->format('Y-m-d');
                $days[$key] ??= ['usage' => 0.0, 'seconds' => 0, 'from' => $overlapStart, 'until' => $overlapEnd, 'intervals' => []];
                $days[$key]['usage'] += $perSecond * $overlap;
                $days[$key]['seconds'] += $overlap;
                // Jam yang tercakup bacaan meteran, untuk label "Tercatat 00:00-12:08".
                $days[$key]['from'] = $days[$key]['from']->min($overlapStart)->copy();
                $days[$key]['until'] = $days[$key]['until']->max($overlapEnd)->copy();

                // Selang < 1 jam (pasangan sebelum/sesudah top-up) tidak
                // menjelaskan apa-apa di rincian, cukup ikut dijumlah.
                if ($interval['seconds'] >= 3600) {
                    $days[$key]['intervals'][] = $interval;
                }
            }
        }

        return $days;
    }

    /**
     * Label titik sisa yang dicatat form pembelian: sebelum atau sesudah top-up.
     *
     * @return array<int, string>  id pembacaan => label
     */
    private static function checkRoles($checks, $purchases): array
    {
        $roles = [];

        foreach ($checks as $check) {
            foreach ($purchases as $purchase) {
                $offset = $purchase->created_at->diffInSeconds($check->created_at, false);

                if (abs($offset) <= ElectricityPurchase::ATTACHED_CHECK_SECONDS) {
                    $roles[$check->id] = $offset < 0 ? 'sebelum top-up' : 'sesudah top-up';
                    break;
                }
            }
        }

        return $roles;
    }
}
