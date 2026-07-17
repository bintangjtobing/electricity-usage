<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use App\Support\UsageCalculator;
use Carbon\Carbon;
use Livewire\Component;

class ElectricityDashboard extends Component
{
    public $lastPurchase;
    public $lastCheck;
    public $remainingKwh = 0;
    public $dailyAverage = 0;
    public $kwhUsed = 0;
    public $daysSinceLastPurchase = 0;
    public $monthlyProjection = 0;
    public $monthlyCost = 0;
    public $tokenFrequency = 0;
    public $nextMonthEstimate = 0;
    public $usageIndicator;
    public $usageIndicatorColor;
    public $projectionToPayday;
    public $chartData;
    public $totalPurchased = 0;
    public $averagePurchaseAmount = 0;
    public $daysUntilEmpty;
    public $estimatedEmptyDate;
    public $thresholdHemat = 0;
    public $thresholdBoros = 0;
    public $locationLabel = 'properti kamu';
    public $lastCheckIsEstimated = false;

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
        $setting = Setting::current();

        $this->thresholdHemat = (float) $setting->threshold_hemat;
        $this->thresholdBoros = (float) $setting->threshold_boros;

        // Lokasi diambil dari Pengaturan (owner_name). Kalau masih placeholder
        // default ('-') pakai frasa generik supaya tidak tampil "di - kamu".
        $this->locationLabel = ($setting->owner_name && $setting->owner_name !== '-')
            ? $setting->owner_name
            : 'properti kamu';

        $this->lastPurchase = ElectricityPurchase::latest()->first();
        $this->lastCheck = ElectricityUsageCheck::latest()->first();

        $this->projectionToPayday = $this->emptyProjection($setting);

        if (! $this->lastCheck) {
            return;
        }

        $this->remainingKwh = $this->lastCheck->kwh_remaining;
        $this->lastCheckIsEstimated = (bool) $this->lastCheck->is_estimated;

        if ($this->lastPurchase) {
            $this->daysSinceLastPurchase = (int) floor(
                Carbon::parse($this->lastPurchase->created_at)->diffInHours(now()) / 24
            );
        }

        $this->totalPurchased = (float) ElectricityPurchase::sum('kwh_bought');
        $purchaseCount = ElectricityPurchase::count();
        $this->averagePurchaseAmount = $purchaseCount > 0
            ? round($this->totalPurchased / $purchaseCount, 2)
            : 0;

        $this->calculateActualUsage();

        if ($this->dailyAverage > 0) {
            $this->monthlyProjection = round($this->dailyAverage * 30, 2);
            // Biaya proyeksi memakai tarif yang berlaku sekarang, bukan tarif
            // pembelian terakhir, supaya tetap benar setelah tarif naik.
            $this->monthlyCost = round($this->monthlyProjection * $setting->price_per_unit, 2);
            $this->nextMonthEstimate = $this->monthlyProjection;

            if ($this->averagePurchaseAmount > 0) {
                $this->tokenFrequency = round($this->monthlyProjection / $this->averagePurchaseAmount, 2);
            }

            $this->daysUntilEmpty = (int) round($this->remainingKwh / $this->dailyAverage);
            $this->estimatedEmptyDate = now()->addDays($this->daysUntilEmpty);
        }

        $this->setUsageIndicator($setting);
        $this->calculateProjectionToPayday($setting);
        $this->prepareChartData();
    }

    private function calculateActualUsage()
    {
        $stats = UsageCalculator::stats();

        $this->kwhUsed = $stats['totalUsage'];
        $this->dailyAverage = $stats['dailyAverage'];
    }

    private function setUsageIndicator(Setting $setting)
    {
        if ($this->dailyAverage <= 0) {
            $this->usageIndicator = 'BELUM ADA DATA';
            $this->usageIndicatorColor = 'bg-gray-500';
        } elseif ($this->dailyAverage < $setting->threshold_hemat) {
            $this->usageIndicator = 'HEMAT';
            $this->usageIndicatorColor = 'bg-green-500';
        } elseif ($this->dailyAverage <= $setting->threshold_boros) {
            $this->usageIndicator = 'STANDAR';
            $this->usageIndicatorColor = 'bg-yellow-500';
        } else {
            $this->usageIndicator = 'BOROS';
            $this->usageIndicatorColor = 'bg-red-500';
        }
    }

    private function calculateProjectionToPayday(Setting $setting)
    {
        $now = now();
        $payday = $setting->payday_day;

        $targetDate = $now->copy()->day <= $payday
            ? $now->copy()->startOfDay()->setDay($payday)
            : $now->copy()->startOfDay()->addMonthNoOverflow()->setDay($payday);

        $daysUntilPayday = (int) ceil($now->diffInHours($targetDate) / 24);
        $projectedUsage = $this->dailyAverage * $daysUntilPayday;
        $remainingOnPayday = $this->remainingKwh - $projectedUsage;

        $this->projectionToPayday = [
            'paydayDay' => $payday,
            'targetMonth' => $targetDate->translatedFormat('F'),
            'targetDate' => $targetDate->translatedFormat('d F Y'),
            'daysUntilPayday' => $daysUntilPayday,
            'projectedUsage' => round($projectedUsage, 2),
            'remainingKwh' => round($remainingOnPayday, 2),
            'needToBuy' => $remainingOnPayday < $setting->low_kwh_alert,
        ];
    }

    private function emptyProjection(Setting $setting): array
    {
        return [
            'paydayDay' => $setting->payday_day,
            'targetMonth' => now()->translatedFormat('F'),
            'targetDate' => now()->translatedFormat('d F Y'),
            'daysUntilPayday' => 0,
            'projectedUsage' => 0,
            'remainingKwh' => 0,
            'needToBuy' => false,
        ];
    }

    private function prepareChartData()
    {
        $checks = ElectricityUsageCheck::orderBy('created_at', 'asc')->get();

        $labels = [];
        $kwhData = [];
        $purchaseData = [];
        $dailyUsageData = [];

        foreach ($checks as $index => $check) {
            $labels[] = Carbon::parse($check->created_at)->format('d M');
            $kwhData[] = (float) $check->kwh_remaining;

            if ($index === 0) {
                $purchaseData[] = null;
                $dailyUsageData[] = 0;
                continue;
            }

            $prev = $checks[$index - 1];

            $bought = UsageCalculator::kwhBoughtBetween($prev->created_at, $check->created_at);
            $purchaseData[] = $bought > 0 ? $bought : null;

            $usage = UsageCalculator::usageBetween($prev, $check);
            $days = Carbon::parse($prev->created_at)->diffInHours(Carbon::parse($check->created_at)) / 24;

            $dailyUsageData[] = $days > 0 ? max(0, round($usage / $days, 2)) : 0;
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
