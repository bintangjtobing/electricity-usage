<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use Livewire\Component;

class ElectricityPurchaseForm extends Component
{
    public $purchase_price;
    public $purchase_price_formatted = '';
    public $kwh_bought;
    public $meter_number = '86281730696';
    public $owner_name = 'I MADE WIRAHADI KESUMA 9';
    public $tariff_type = 'R1 / 1300 VA';
    public $price_per_unit;

    protected $rules = [
        'purchase_price' => 'required|numeric|min:50000',
        'kwh_bought' => 'required|numeric|min:1'
    ];

    public function mount()
    {
        $this->price_per_unit = 1588.31;
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

        ElectricityPurchase::create([
            'meter_number' => $this->meter_number,
            'owner_name' => $this->owner_name,
            'tariff_type' => $this->tariff_type,
            'purchase_price' => $this->purchase_price,
            'kwh_bought' => $this->kwh_bought,
            'price_per_unit' => $this->price_per_unit
        ]);

        session()->flash('message', 'Pembelian listrik berhasil dicatat!');
        $this->reset(['purchase_price', 'purchase_price_formatted', 'kwh_bought']);
    }

    public function render()
    {
        return view('livewire.electricity-purchase-form');
    }
}