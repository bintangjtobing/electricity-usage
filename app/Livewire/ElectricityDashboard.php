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
            
            $purchaseDate = Carbon::parse($this->lastPurchase->created_at);
            $checkDate = Carbon::parse($this->lastCheck->created_at);
            
            $this->daysSinceLastPurchase = $purchaseDate->diffInDays($checkDate);
            
            if ($this->daysSinceLastPurchase > 0) {
                $this->kwhUsed = $this->lastPurchase->kwh_bought - $this->remainingKwh;
                $this->dailyAverage = round($this->kwhUsed / $this->daysSinceLastPurchase, 2);
                
                $this->monthlyProjection = round($this->dailyAverage * 30, 2);
                $this->monthlyCost = round($this->monthlyProjection * $this->lastPurchase->price_per_unit, 2);
                
                if ($this->lastPurchase->kwh_bought > 0) {
                    $this->tokenFrequency = round(30 / ($this->lastPurchase->kwh_bought / $this->dailyAverage), 2);
                }
                
                $historicalUsage = $this->getHistoricalDailyAverage();
                $this->nextMonthEstimate = round($historicalUsage * 30, 2);
                
                $this->setUsageIndicator($this->dailyAverage);
            }
        }
    }

    private function getHistoricalDailyAverage()
    {
        $purchases = ElectricityPurchase::orderBy('created_at', 'desc')->take(3)->get();
        $checks = ElectricityUsageCheck::orderBy('created_at', 'desc')->take(10)->get();
        
        if ($purchases->count() < 2 || $checks->count() < 2) {
            return $this->dailyAverage ?? 7;
        }

        $totalDays = 0;
        $totalUsage = 0;

        for ($i = 0; $i < $purchases->count() - 1; $i++) {
            $purchase1 = $purchases[$i];
            $purchase2 = $purchases[$i + 1];
            
            $days = Carbon::parse($purchase1->created_at)->diffInDays(Carbon::parse($purchase2->created_at));
            $usage = $purchase2->kwh_bought;
            
            $totalDays += $days;
            $totalUsage += $usage;
        }

        return $totalDays > 0 ? round($totalUsage / $totalDays, 2) : $this->dailyAverage;
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

    public function render()
    {
        return view('livewire.electricity-dashboard');
    }
}