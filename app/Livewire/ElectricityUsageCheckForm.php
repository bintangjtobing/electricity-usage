<?php

namespace App\Livewire;

use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use Carbon\Carbon;
use Livewire\Component;

class ElectricityUsageCheckForm extends Component
{
    public $kwh_remaining;
    public $check_date;
    public $meter_number;

    protected function rules(): array
    {
        return [
            'kwh_remaining' => 'required|numeric|min:0',
            'check_date' => 'required|date|before_or_equal:today',
        ];
    }

    protected $messages = [
        'check_date.before_or_equal' => 'Tanggal pengecekan tidak boleh di masa depan.',
    ];

    public function mount()
    {
        $this->meter_number = Setting::current()->meter_number;
        $this->check_date = now()->format('Y-m-d');
    }

    public function submit()
    {
        $this->validate();

        $checkedAt = Carbon::parse($this->check_date)->setTimeFrom(now());

        // Angka ini dibaca langsung dari meteran, jadi bukan estimasi.
        $check = new ElectricityUsageCheck([
            'meter_number' => $this->meter_number,
            'kwh_remaining' => $this->kwh_remaining,
            'is_estimated' => false,
        ]);
        $check->created_at = $checkedAt;
        $check->updated_at = $checkedAt;
        $check->save();

        session()->flash('message', 'Pengecekan sisa listrik berhasil dicatat!');
        $this->reset(['kwh_remaining']);
        $this->check_date = now()->format('Y-m-d');

        $this->dispatch('refresh-dashboard');
    }

    public function render()
    {
        return view('livewire.electricity-usage-check-form');
    }
}
