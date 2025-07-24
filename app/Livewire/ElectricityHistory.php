<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Livewire\Component;
use Livewire\WithPagination;

class ElectricityHistory extends Component
{
    use WithPagination;

    public $activeTab = 'purchases';

    public function render()
    {
        $purchases = ElectricityPurchase::latest()->paginate(10);
        $checks = ElectricityUsageCheck::latest()->paginate(10);

        return view('livewire.electricity-history', [
            'purchases' => $purchases,
            'checks' => $checks
        ]);
    }
}