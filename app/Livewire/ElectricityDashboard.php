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
            
            // Calculate actual usage from check history
            $this->calculateActualUsage();
            
            if ($this->dailyAverage > 0) {
                $this->monthlyProjection = round($this->dailyAverage * 30, 2);
                $this->monthlyCost = round($this->monthlyProjection * $this->lastPurchase->price_per_unit, 2);
                
                if ($this->lastPurchase->kwh_bought > 0) {
                    $this->tokenFrequency = round($this->monthlyProjection / $this->lastPurchase->kwh_bought, 2);
                }
                
                $historicalUsage = $this->getHistoricalDailyAverage();
                $this->nextMonthEstimate = round($historicalUsage * 30, 2);
                
                $this->setUsageIndicator($this->dailyAverage);
            }
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

    public function render()
    {
        return view('livewire.electricity-dashboard');
    }
}