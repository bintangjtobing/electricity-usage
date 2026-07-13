<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Carbon\Carbon;
use Livewire\Component;

class ElectricityPurchaseForm extends Component
{
    public $purchase_price;
    public $purchase_price_formatted = '';
    public $kwh_bought;
    public $purchase_date;
    public $meter_number = '50220822832';
    public $owner_name = 'Perdana Residence 002';
    public $tariff_type = 'R1T 2200 VA';
    public $price_per_unit;

    protected $rules = [
        'purchase_price' => 'required|numeric|min:50000',
        'kwh_bought' => 'required|numeric|min:1',
        'purchase_date' => 'required|date|before_or_equal:today'
    ];

    protected $messages = [
        'purchase_date.before_or_equal' => 'Tanggal pembelian tidak boleh di masa depan.'
    ];

    public function mount()
    {
        $this->price_per_unit = 1589.07;
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function updatedPurchasePriceFormatted($value)
    {
        // Remove formatting (commas, dots, spaces) and convert to number
        $cleanValue = preg_replace('/[^\d]/', '', $value);
        $this->purchase_price = (float) $cleanValue;
        
        // Format with thousands separator
        $this->purchase_price_formatted = number_format($this->purchase_price, 0, ',', '.');
        
        // Calculate kWh
        if ($this->purchase_price && $this->price_per_unit) {
            $this->kwh_bought = round($this->purchase_price / $this->price_per_unit, 2);
        }
    }

    public function updatedKwhBought()
    {
        if ($this->kwh_bought && $this->price_per_unit) {
            $this->purchase_price = round($this->kwh_bought * $this->price_per_unit, 2);
            $this->purchase_price_formatted = number_format($this->purchase_price, 0, ',', '.');
        }
    }

    public function submit()
    {
        $this->validate();

        // Tanggal pembelian jadi created_at, karena seluruh dashboard & grafik
        // memakai created_at sebagai tanggal transaksi. Jam diambil dari waktu
        // sekarang supaya urutan antar entri di hari yang sama tetap benar.
        $purchasedAt = Carbon::parse($this->purchase_date)->setTimeFrom(now());

        // Create purchase record
        $purchase = new ElectricityPurchase([
            'meter_number' => $this->meter_number,
            'owner_name' => $this->owner_name,
            'tariff_type' => $this->tariff_type,
            'purchase_price' => $this->purchase_price,
            'kwh_bought' => $this->kwh_bought,
            'price_per_unit' => $this->price_per_unit
        ]);
        $purchase->created_at = $purchasedAt;
        $purchase->updated_at = $purchasedAt;
        $purchase->save();

        // Get last usage check as of the purchase date (bukan yang paling baru),
        // supaya pembelian yang dicatat mundur tetap dihitung dari saldo saat itu
        $lastCheck = ElectricityUsageCheck::where('meter_number', $this->meter_number)
            ->where('created_at', '<=', $purchasedAt)
            ->latest()
            ->first();

        // Calculate new kWh remaining (last check + purchased kWh)
        $lastKwhRemaining = $lastCheck ? $lastCheck->kwh_remaining : 0;
        $newKwhRemaining = $lastKwhRemaining + $this->kwh_bought;

        // Auto-create new usage check with updated remaining
        $check = new ElectricityUsageCheck([
            'meter_number' => $this->meter_number,
            'kwh_remaining' => $newKwhRemaining,
        ]);
        $check->created_at = $purchasedAt;
        $check->updated_at = $purchasedAt;
        $check->save();

        session()->flash('message', 'Pembelian listrik berhasil dicatat!');
        $this->reset(['purchase_price', 'purchase_price_formatted', 'kwh_bought']);
        $this->purchase_date = now()->format('Y-m-d');

        // Refresh dashboard
        $this->dispatch('refresh-dashboard');
    }

    public function render()
    {
        return view('livewire.electricity-purchase-form');
    }
}