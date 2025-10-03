<?php

namespace App\Livewire;

use App\Models\ElectricityUsageCheck;
use App\Models\ElectricityPurchase;
use Livewire\Component;
use Carbon\Carbon;

class KwhConfirmationModal extends Component
{
    public $showModal = false;
    public $lastKwhValue = 0;
    public $predictedKwhValue = 0;
    public $showInputField = false;
    public $newKwhValue = '';
    public $meterNumber = '';
    public $hoursSinceLastCheck = 0;
    public $dailyAverage = 0;
    
    public function mount()
    {
        // Check if user clicked "Ask later" in this session
        if (session('kwh_confirmation_ask_later')) {
            return;
        }

        // Get last usage check
        $lastCheck = ElectricityUsageCheck::latest()->first();

        if ($lastCheck) {
            $this->lastKwhValue = $lastCheck->kwh_remaining;
            $this->meterNumber = $lastCheck->meter_number;

            // Calculate hours since last check
            $lastCheckTime = Carbon::parse($lastCheck->created_at);
            $this->hoursSinceLastCheck = $lastCheckTime->diffInHours(Carbon::now());

            // Calculate daily average usage
            $this->dailyAverage = $this->calculateDailyAverage();

            // Calculate predicted remaining kWh
            $daysSinceLastCheck = $this->hoursSinceLastCheck / 24;
            $estimatedUsage = $this->dailyAverage * $daysSinceLastCheck;
            $this->predictedKwhValue = max(0, round($this->lastKwhValue - $estimatedUsage, 2));

            // Always show modal when user visits (unless they clicked "Ask later")
            $this->showModal = true;
        }
    }
    
    public function confirmYes()
    {
        // Save new usage check with predicted value
        ElectricityUsageCheck::create([
            'meter_number' => $this->meterNumber,
            'kwh_remaining' => $this->predictedKwhValue,
        ]);

        $this->showModal = false;
        $this->dispatch('usage-check-saved');

        // Refresh the dashboard
        $this->dispatch('refresh-dashboard');
    }
    
    public function confirmNo()
    {
        // Show input field for correct value
        $this->showInputField = true;
    }
    
    public function saveNewValue()
    {
        $this->validate([
            'newKwhValue' => 'required|numeric|min:0|max:9999.99',
        ]);
        
        // Save new usage check with new value
        ElectricityUsageCheck::create([
            'meter_number' => $this->meterNumber,
            'kwh_remaining' => $this->newKwhValue,
        ]);
        
        $this->showModal = false;
        $this->showInputField = false;
        $this->dispatch('usage-check-saved');
        
        // Refresh the dashboard
        $this->dispatch('refresh-dashboard');
    }
    
    public function askLater()
    {
        // Set session flag to not show modal again in this session
        session(['kwh_confirmation_ask_later' => true]);
        $this->showModal = false;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->showInputField = false;
        $this->newKwhValue = '';
    }

    private function calculateDailyAverage()
    {
        // Get usage checks ordered by date
        $checks = ElectricityUsageCheck::orderBy('created_at', 'asc')->get();

        if ($checks->count() < 2) {
            return 0;
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
                    // Usage = previous remaining - minimum before purchase
                    $usageBeforePurchase = $prevKwh - ($currKwh - $purchase->kwh_bought);
                    $totalUsage += max(0, $usageBeforePurchase);
                }
            } else {
                // Normal usage (no purchase)
                $usage = $prevKwh - $currKwh;
                $totalUsage += max(0, $usage);
            }

            $days = Carbon::parse($prevCheck->created_at)->diffInDays(Carbon::parse($currCheck->created_at));
            $totalDays += max(1, $days); // At least 1 day to avoid division by zero
        }

        // Calculate daily average
        if ($totalDays > 0) {
            return round($totalUsage / $totalDays, 2);
        }

        return 0;
    }

    public function render()
    {
        return view('livewire.kwh-confirmation-modal');
    }
}