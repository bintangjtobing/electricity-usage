<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use App\Support\DecimalInput;
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

    /** Nominal masih tebakan dari kWh (belum diisi user), jadi boleh ikut berubah. */
    public $priceEstimated = false;

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
        'kwh_before_purchase.numeric' => 'Sisa kWh harus berupa angka, mis. 12,40 atau 12.40.',
        'kwh_bought.numeric' => 'kWh harus berupa angka, mis. 314,70 atau 314.70.',
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

        $this->priceEstimated = false;

        // Calculate kWh
        if ($this->purchase_price && $this->price_per_unit) {
            $this->kwh_bought = round($this->purchase_price / $this->price_per_unit, 2);
        }
    }

    /**
     * Nominal = uang yang benar-benar dibayar, jadi tidak diubah saat kWh
     * diketik. Dulu kWh x tarif menimpa nominal: 500.000 tersimpan Rp 500.080.
     * Kalau kWh di struk berbeda, yang menyesuaikan tarif pembelian ini.
     * Hanya bila nominal belum diisi, nominal ditebak dari kWh.
     */
    public function updatedKwhBought()
    {
        $kwh = $this->kwhValue();

        if ($kwh && $this->price_per_unit && (! $this->purchase_price || $this->priceEstimated)) {
            $this->purchase_price = round($kwh * $this->price_per_unit);
            $this->purchase_price_formatted = number_format($this->purchase_price, 0, ',', '.');
            $this->priceEstimated = true;
        }
    }

    /** kWh yang diketik (koma/titik) sebagai angka, atau null kalau belum valid. */
    public function kwhValue(): ?float
    {
        $kwh = DecimalInput::normalize($this->kwh_bought);

        return is_numeric($kwh) ? (float) $kwh : null;
    }

    /** Tarif efektif pembelian ini (nominal / kWh, sudah termasuk PPJ). */
    public function effectiveRate(): ?float
    {
        $kwh = $this->kwhValue();

        return $this->purchase_price && $kwh > 0 ? round($this->purchase_price / $kwh, 2) : null;
    }

    public function submit()
    {
        $this->kwh_before_purchase = DecimalInput::normalize($this->kwh_before_purchase);
        $this->kwh_bought = DecimalInput::normalize($this->kwh_bought);

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
            // Tarif sebenarnya dari struk ini, bukan salinan Pengaturan.
            'price_per_unit' => round($this->purchase_price / $this->kwh_bought, 2),
        ]);
        $purchase->created_at = $purchasedAt;
        $purchase->updated_at = $purchasedAt;
        $purchase->save();

        // Kalau sisa sebelum top-up diisi, saldo sesudahnya diketahui persis.
        // Kalau tidak, kita terpaksa mundur ke catatan terakhir sebelum tanggal
        // ini -- pemakaian di antaranya tidak diketahui, jadi hasilnya ditandai
        // sebagai estimasi.
        $isEstimated = $this->kwh_before_purchase === null;

        if ($isEstimated) {
            // Sisa sebelum top-up tak diketahui: cukup satu titik (sesudah top-up),
            // ditandai estimasi karena pemakaian sebelumnya cuma tebakan.
            $lastCheck = ElectricityUsageCheck::where('meter_number', $this->meter_number)
                ->where('created_at', '<=', $purchasedAt)
                ->latest()
                ->first();

            $baseKwh = $lastCheck ? $lastCheck->kwh_remaining : 0;

            $this->recordCheck($baseKwh + $this->kwh_bought, true, $purchasedAt);
        } else {
            // Sisa sebelum top-up diketahui: simpan DUA titik supaya grafik
            // menampilkan penurunan nyata sebelum beli, lalu lonjakan sesudah
            // top-up. Detik digeser agar urutan pre-check < pembelian < post-check
            // terjaga untuk UsageCalculator (pembelian masuk ke selang yang benar).
            $baseKwh = (float) $this->kwh_before_purchase;

            $this->recordCheck($baseKwh, false, $purchasedAt->copy()->subSecond());
            $this->recordCheck($baseKwh + $this->kwh_bought, false, $purchasedAt->copy()->addSecond());
        }

        session()->flash('message', 'Pembelian listrik berhasil dicatat!');
        $this->reset(['purchase_price', 'purchase_price_formatted', 'kwh_bought', 'kwh_before_purchase', 'priceEstimated']);
        $this->purchase_date = now()->format('Y-m-d');

        $this->dispatch('refresh-dashboard');
    }

    /** Simpan satu titik pembacaan meteran pada waktu tertentu. */
    private function recordCheck(float $kwhRemaining, bool $isEstimated, Carbon $at): void
    {
        $check = new ElectricityUsageCheck([
            'meter_number' => $this->meter_number,
            'kwh_remaining' => round($kwhRemaining, 2),
            'is_estimated' => $isEstimated,
        ]);
        $check->created_at = $at;
        $check->updated_at = $at;
        $check->save();
    }

    public function render()
    {
        return view('livewire.electricity-purchase-form');
    }
}
