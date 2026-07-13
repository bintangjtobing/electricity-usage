<?php

namespace App\Livewire;

use App\Models\Setting;
use Livewire\Component;

class SettingsForm extends Component
{
    public $meter_number;
    public $owner_name;
    public $address;
    public $tariff_type;
    public $price_per_unit;
    public $payday_day;
    public $threshold_hemat;
    public $threshold_boros;
    public $low_kwh_alert;

    /** Untuk kalkulator tarif: nominal dibayar & kWh yang didapat dari struk. */
    public $calc_price;
    public $calc_kwh;

    protected function rules(): array
    {
        return [
            'meter_number' => 'required|string|max:50',
            'owner_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'tariff_type' => 'required|string|max:50',
            'price_per_unit' => 'required|numeric|min:1',
            'payday_day' => 'required|integer|min:1|max:28',
            'threshold_hemat' => 'required|numeric|min:0',
            'threshold_boros' => 'required|numeric|gt:threshold_hemat',
            'low_kwh_alert' => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'threshold_boros.gt' => 'Ambang boros harus lebih besar dari ambang hemat.',
        'payday_day.max' => 'Pilih tanggal 1-28 agar selalu ada di setiap bulan.',
    ];

    public function mount()
    {
        $this->fill(Setting::current()->only([
            'meter_number',
            'owner_name',
            'address',
            'tariff_type',
            'price_per_unit',
            'payday_day',
            'threshold_hemat',
            'threshold_boros',
            'low_kwh_alert',
        ]));
    }

    /**
     * Hitung tarif per kWh dari struk token: nominal dibayar / kWh diterima.
     * Angka ini sudah termasuk PPJ dan biaya lain, jadi lebih akurat daripada
     * tarif dasar PLN.
     */
    public function calculateRate()
    {
        $this->validate([
            'calc_price' => 'required|numeric|min:1',
            'calc_kwh' => 'required|numeric|min:0.01',
        ], [], [
            'calc_price' => 'nominal dibayar',
            'calc_kwh' => 'kWh diterima',
        ]);

        $this->price_per_unit = round($this->calc_price / $this->calc_kwh, 2);

        session()->flash('calc', 'Tarif dihitung: Rp ' . number_format($this->price_per_unit, 2, ',', '.') . ' / kWh. Klik Simpan untuk menerapkan.');
    }

    public function save()
    {
        $this->validate();

        Setting::current()->update([
            'meter_number' => $this->meter_number,
            'owner_name' => $this->owner_name,
            'address' => $this->address,
            'tariff_type' => $this->tariff_type,
            'price_per_unit' => $this->price_per_unit,
            'payday_day' => $this->payday_day,
            'threshold_hemat' => $this->threshold_hemat,
            'threshold_boros' => $this->threshold_boros,
            'low_kwh_alert' => $this->low_kwh_alert,
        ]);

        session()->flash('message', 'Pengaturan berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.settings-form');
    }
}
