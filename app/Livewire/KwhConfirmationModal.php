<?php

namespace App\Livewire;

use App\Models\ElectricityUsageCheck;
use App\Models\ElectricityPurchase;
use App\Support\UsageCalculator;
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
    
    /** Catatan dianggap basi setelah sekian hari tanpa pembacaan meteran. */
    private const STALE_AFTER_DAYS = 3;

    public function mount()
    {
        if (session('kwh_confirmation_ask_later')) {
            return;
        }

        $lastCheck = ElectricityUsageCheck::latest()->first();

        if (! $lastCheck) {
            return;
        }

        $this->lastKwhValue = $lastCheck->kwh_remaining;
        $this->meterNumber = $lastCheck->meter_number;
        $this->hoursSinceLastCheck = Carbon::parse($lastCheck->created_at)->diffInHours(now());
        $this->dailyAverage = $this->calculateDailyAverage();

        $daysSinceLastCheck = $this->hoursSinceLastCheck / 24;
        $estimatedUsage = $this->dailyAverage * $daysSinceLastCheck;
        $this->predictedKwhValue = max(0, round($this->lastKwhValue - $estimatedUsage, 2));

        // Dulu modal ini muncul di setiap kunjungan, bahkan ketika meteran baru
        // saja dicek -- tidak ada gunanya bertanya dan justru mengganggu.
        // Sekarang hanya muncul kalau angkanya memang perlu dikonfirmasi:
        // catatannya sudah basi, atau angka terakhir cuma hasil tebakan.
        $this->showModal = $daysSinceLastCheck >= self::STALE_AFTER_DAYS
            || (bool) $lastCheck->is_estimated;
    }
    
    public function confirmYes()
    {
        // Nilai ini hasil prediksi, bukan angka yang dibaca dari meteran.
        // Ditandai supaya bisa dibedakan dari pembacaan sungguhan di riwayat.
        ElectricityUsageCheck::create([
            'meter_number' => $this->meterNumber,
            'kwh_remaining' => $this->predictedKwhValue,
            'is_estimated' => true,
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
        
        // Angka ini dibaca langsung dari meteran oleh user, jadi bukan estimasi.
        ElectricityUsageCheck::create([
            'meter_number' => $this->meterNumber,
            'kwh_remaining' => $this->newKwhValue,
            'is_estimated' => false,
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
        return UsageCalculator::dailyAverage();
    }

    public function render()
    {
        return view('livewire.kwh-confirmation-modal');
    }
}