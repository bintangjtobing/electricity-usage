<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Carbon\Carbon;
use Livewire\Component;

class ElectricityDashboard extends Component
{
    public $lastPurchase;
    public $lastCheck;
    public $remainingKwh;
    public $dailyAverage;
    public $kwhUsed;
    public $daysSinceLastPurchase;
    public $monthlyProjection;
    public $monthlyCost;
    public $tokenFrequency;
    public $nextMonthEstimate;
    public $usageIndicator;
    public $usageIndicatorColor;
    public $projectionToPayday;
    public $chartData;
    public $totalPurchased;
    public $averagePurchaseAmount;

    protected $listeners = ['refresh-dashboard' => 'refresh'];

    public function mount()
    {
        $this->calculateAnalytics();
    }

    public function refresh()
    {
        $this->calculateAnalytics();
    }

    public function calculateAnalytics()
    {
        $this->lastPurchase = ElectricityPurchase::latest()->first();
        $this->lastCheck = ElectricityUsageCheck::latest()->first();

        if ($this->lastPurchase && $this->lastCheck) {
            $this->remainingKwh = $this->lastCheck->kwh_remaining;

            // Calculate days since last purchase to now
            $purchaseDate = Carbon::parse($this->lastPurchase->created_at);
            $now = Carbon::now();
            $this->daysSinceLastPurchase = $purchaseDate->diffInDays($now);

            // Calculate totals
            $this->totalPurchased = ElectricityPurchase::sum('kwh_bought');
            $purchaseCount = ElectricityPurchase::count();
            $this->averagePurchaseAmount = $purchaseCount > 0 ? round($this->totalPurchased / $purchaseCount, 2) : 0;

            // Calculate actual usage from check history
            $this->calculateActualUsage();

            if ($this->dailyAverage > 0) {
                $this->monthlyProjection = round($this->dailyAverage * 30, 2);
                $this->monthlyCost = round($this->monthlyProjection * $this->lastPurchase->price_per_unit, 2);

                // Use average purchase amount for frequency calculation
                if ($this->averagePurchaseAmount > 0) {
                    $this->tokenFrequency = round($this->monthlyProjection / $this->averagePurchaseAmount, 2);
                }

                $historicalUsage = $this->getHistoricalDailyAverage();
                $this->nextMonthEstimate = round($historicalUsage * 30, 2);

                $this->setUsageIndicator($this->dailyAverage);
                $this->calculateProjectionToPayday();
                $this->prepareChartData();
            } else {
                // Set default values if no daily average
                $this->projectionToPayday = [
                    'targetMonth' => Carbon::now()->format('F'),
                    'daysUntilPayday' => 0,
                    'projectedUsage' => 0,
                    'remainingKwh' => $this->remainingKwh,
                    'needToBuy' => false
                ];
            }
        } else {
            // Set default values if no data
            $this->projectionToPayday = [
                'targetMonth' => Carbon::now()->format('F'),
                'daysUntilPayday' => 0,
                'projectedUsage' => 0,
                'remainingKwh' => 0,
                'needToBuy' => false
            ];
        }
    }

    private function calculateActualUsage()
    {
        // Get usage checks ordered by date
        $checks = ElectricityUsageCheck::orderBy('created_at', 'asc')->get();

        if ($checks->count() < 2) {
            $this->kwhUsed = 0;
            $this->dailyAverage = 0;
            return;
        }

        $totalUsage = 0;
        $totalDays = 0;

        // Calculate usage between consecutive checks
        for ($i = 1; $i < $checks->count(); $i++) {
            $prevCheck = $checks[$i - 1];
            $currCheck = $checks[$i];

            $prevKwh = $prevCheck->kwh_remaining;
            $currKwh = $currCheck->kwh_remaining;

            // If current kWh is higher than previous, a purchase was made
            if ($currKwh > $prevKwh) {
                // Find purchase between these checks
                $purchase = ElectricityPurchase::where('created_at', '>', $prevCheck->created_at)
                    ->where('created_at', '<=', $currCheck->created_at)
                    ->first();

                if ($purchase) {
                    // Usage = previous remaining - minimum before purchase + usage after purchase
                    $usageBeforePurchase = $prevKwh - ($currKwh - $purchase->kwh_bought);
                    $totalUsage += max(0, $usageBeforePurchase);
                }
            } else {
                // Normal usage (no purchase)
                $usage = $prevKwh - $currKwh;
                $totalUsage += max(0, $usage);
            }

            $days = Carbon::parse($prevCheck->created_at)->diffInDays(Carbon::parse($currCheck->created_at));
            $totalDays += $days;
        }

        // Calculate total kWh used from all purchases
        $allPurchases = ElectricityPurchase::sum('kwh_bought');
        $this->kwhUsed = max(0, $allPurchases - $this->remainingKwh);

        // Calculate daily average
        if ($totalDays > 0) {
            $this->dailyAverage = round($totalUsage / $totalDays, 2);
        } else {
            // Fallback: use first and last check
            $firstCheck = $checks->first();
            $lastCheck = $checks->last();
            $daysBetween = Carbon::parse($firstCheck->created_at)->diffInDays(Carbon::parse($lastCheck->created_at));

            if ($daysBetween > 0) {
                $this->dailyAverage = round($this->kwhUsed / $daysBetween, 2);
            } else {
                $this->dailyAverage = 0;
            }
        }
    }

    private function getHistoricalDailyAverage()
    {
        // Return the calculated daily average from actual usage
        return $this->dailyAverage ?? 0;
    }

    private function setUsageIndicator($dailyUsage)
    {
        if ($dailyUsage < 7) {
            $this->usageIndicator = 'HEMAT';
            $this->usageIndicatorColor = 'bg-green-500';
        } elseif ($dailyUsage == 7) {
            $this->usageIndicator = 'STANDAR';
            $this->usageIndicatorColor = 'bg-yellow-500';
        } else {
            $this->usageIndicator = 'BOROS';
            $this->usageIndicatorColor = 'bg-red-500';
        }
    }

    private function calculateProjectionToPayday()
    {
        $now = Carbon::now();
        $currentDay = $now->day;

        // Calculate target date (10th of next month if current date is after 10th)
        if ($currentDay <= 10) {
            $targetDate = Carbon::create($now->year, $now->month, 10);
            $targetMonth = $now->format('F');
        } else {
            $targetDate = Carbon::create($now->year, $now->month, 10)->addMonth();
            $targetMonth = $targetDate->format('F');
        }

        // Calculate days until payday
        $daysUntilPayday = $now->diffInDays($targetDate);

        // Calculate projected usage until payday
        $projectedUsage = $this->dailyAverage * $daysUntilPayday;

        // Calculate remaining kWh on payday
        $remainingOnPayday = $this->remainingKwh - $projectedUsage;

        $this->projectionToPayday = [
            'targetMonth' => $targetMonth,
            'daysUntilPayday' => $daysUntilPayday,
            'projectedUsage' => round($projectedUsage, 2),
            'remainingKwh' => round($remainingOnPayday, 2),
            'needToBuy' => $remainingOnPayday < 20 // If less than 20 kWh, likely need to buy before payday
        ];
    }

    private function prepareChartData()
    {
        // Get all checks and purchases
        $checks = ElectricityUsageCheck::orderBy('created_at', 'asc')->get();
        $purchases = ElectricityPurchase::orderBy('created_at', 'asc')->get();

        $labels = [];
        $kwhData = [];
        $purchaseData = [];
        $dailyUsageData = [];

        foreach ($checks as $index => $check) {
            $labels[] = Carbon::parse($check->created_at)->format('d M');
            $kwhData[] = $check->kwh_remaining;

            // Check if there's a purchase at this time
            $purchase = $purchases->first(function($p) use ($check) {
                return Carbon::parse($p->created_at)->diffInMinutes(Carbon::parse($check->created_at)) <= 60;
            });

            $purchaseData[] = $purchase ? $purchase->kwh_bought : null;

            // Calculate daily usage
            if ($index > 0) {
                $prevCheck = $checks[$index - 1];
                $currentKwh = $check->kwh_remaining;
                $prevKwh = $prevCheck->kwh_remaining;

                // If current > previous, there was a purchase - calculate usage before purchase
                if ($purchase) {
                    $usageBeforePurchase = $prevKwh - ($currentKwh - $purchase->kwh_bought);
                    $days = Carbon::parse($prevCheck->created_at)->diffInDays(Carbon::parse($check->created_at));
                    $dailyUsage = $days > 0 ? round($usageBeforePurchase / $days, 2) : 0;
                    $dailyUsageData[] = max(0, $dailyUsage);
                } else {
                    // Normal usage calculation
                    $usage = $prevKwh - $currentKwh;
                    $days = Carbon::parse($prevCheck->created_at)->diffInDays(Carbon::parse($check->created_at));
                    $dailyUsage = $days > 0 ? round($usage / $days, 2) : 0;
                    $dailyUsageData[] = max(0, $dailyUsage);
                }
            } else {
                $dailyUsageData[] = 0; // First data point has no previous to compare
            }
        }

        $this->chartData = [
            'labels' => $labels,
            'kwh' => $kwhData,
            'purchases' => $purchaseData,
            'dailyUsage' => $dailyUsageData,
        ];
    }

    public function render()
    {
        return view('livewire.electricity-dashboard');
    }
}
