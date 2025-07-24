<?php

namespace App\Livewire;

use App\Models\ElectricityUsageCheck;
use Livewire\Component;

class ElectricityUsageCheckForm extends Component
{
    public $kwh_remaining;
    public $meter_number = '86281730696';

    protected $rules = [
        'kwh_remaining' => 'required|numeric|min:0'
    ];

    public function submit()
    {
        $this->validate();

        ElectricityUsageCheck::create([
            'meter_number' => $this->meter_number,
            'kwh_remaining' => $this->kwh_remaining
        ]);

        session()->flash('message', 'Pengecekan sisa listrik berhasil dicatat!');
        $this->reset(['kwh_remaining']);
    }

    public function render()
    {
        return view('livewire.electricity-usage-check-form');
    }
}
