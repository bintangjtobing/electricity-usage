<?php

namespace App\Livewire;

use App\Models\ElectricityUsageCheck;
use Livewire\Component;
use Carbon\Carbon;

class KwhConfirmationModal extends Component
{
    public $showModal = false;
    public $lastKwhValue = 0;
    public $showInputField = false;
    public $newKwhValue = '';
    public $meterNumber = '';
    public $hoursSinceLastCheck = 0;
    
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
            
            // Always show modal when user visits (unless they clicked "Ask later")
            $this->showModal = true;
        }
    }
    
    public function confirmYes()
    {
        // Save new usage check with same value
        ElectricityUsageCheck::create([
            'meter_number' => $this->meterNumber,
            'kwh_remaining' => $this->lastKwhValue,
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
    
    public function render()
    {
        return view('livewire.kwh-confirmation-modal');
    }
}