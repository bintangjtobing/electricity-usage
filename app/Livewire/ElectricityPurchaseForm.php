<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use Carbon\Carbon;
use Livewire\Component;

class ElectricityPurchaseForm extends Component
{
    public $purchase_price;
    public $purchase_price_formatted = '';
    public $kwh_bought;
    public $kwh_before_purchase;
    public $purchase_date;
    public $meter_number;
    public $owner_name;
    public $address;
    public $tariff_type;
    public $price_per_unit;

    /** Nominal yang paling sering dibeli, untuk tombol cepat. */
    public array $quickAmounts = [100000, 250000, 500000, 1000000];

    protected function rules(): array
    {
        return [
            'purchase_price' => 'required|numeric|min:20000',
            'kwh_bought' => 'required|numeric|min:1',
            'kwh_before_purchase' => 'nullable|numeric|min:0',
            'purchase_date' => 'required|date|before_or_equal:today',
        ];
    }

    protected $messages = [
        'purchase_date.before_or_equal' => 'Tanggal pembelian tidak boleh di masa depan.',
        'kwh_before_purchase.min' => 'Sisa kWh tidak boleh negatif.',
    ];

    public function mount()
    {
        $setting = Setting::current();

        $this->meter_number = $setting->meter_number;
        $this->owner_name = $setting->owner_name;
        $this->address = $setting->address;
        $this->tariff_type = $setting->tariff_type;
        $this->price_per_unit = $setting->price_per_unit;
        $this->purchase_date = now()->format('Y-m-d');
    }

    public function setAmount($amount)
    {
        $this->purchase_price_formatted = number_format($amount, 0, ',', '.');
        $this->updatedPurchasePriceFormatted($this->purchase_price_formatted);
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

        $purchase = new ElectricityPurchase([
            'meter_number' => $this->meter_number,
            'owner_name' => $this->owner_name,
            'tariff_type' => $this->tariff_type,
            'purchase_price' => $this->purchase_price,
            'kwh_bought' => $this->kwh_bought,
            'kwh_before_purchase' => $this->kwh_before_purchase,
            'price_per_unit' => $this->price_per_unit,
        ]);
        $purchase->created_at = $purchasedAt;
        $purchase->updated_at = $purchasedAt;
        $purchase->save();

        // Kalau sisa sebelum top-up diisi, saldo sesudahnya diketahui persis.
        // Kalau tidak, kita terpaksa mundur ke catatan terakhir sebelum tanggal
        // ini -- pemakaian di antaranya tidak diketahui, jadi hasilnya ditandai
        // sebagai estimasi.
        $isEstimated = $this->kwh_before_purchase === null || $this->kwh_before_purchase === '';

        if ($isEstimated) {
            $lastCheck = ElectricityUsageCheck::where('meter_number', $this->meter_number)
                ->where('created_at', '<=', $purchasedAt)
                ->latest()
                ->first();

            $baseKwh = $lastCheck ? $lastCheck->kwh_remaining : 0;
        } else {
            $baseKwh = (float) $this->kwh_before_purchase;
        }

        $check = new ElectricityUsageCheck([
            'meter_number' => $this->meter_number,
            'kwh_remaining' => round($baseKwh + $this->kwh_bought, 2),
            'is_estimated' => $isEstimated,
        ]);
        $check->created_at = $purchasedAt;
        $check->updated_at = $purchasedAt;
        $check->save();

        session()->flash('message', 'Pembelian listrik berhasil dicatat!');
        $this->reset(['purchase_price', 'purchase_price_formatted', 'kwh_bought', 'kwh_before_purchase']);
        $this->purchase_date = now()->format('Y-m-d');

        $this->dispatch('refresh-dashboard');
    }

    public function render()
    {
        return view('livewire.electricity-purchase-form');
    }
}
