<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ElectricityHistory extends Component
{
    use WithPagination;

    public $activeTab = 'purchases';

    /** Baris yang sedang diedit; null bila tidak ada. */
    public $editingType = null;
    public $editingId = null;

    public $edit_date;
    public $edit_price;
    public $edit_kwh;
    public $edit_remaining;

    public $confirmingDeleteType = null;
    public $confirmingDeleteId = null;

    public function updatedActiveTab()
    {
        $this->cancelEdit();
        $this->resetPage();
    }

    public function editPurchase($id)
    {
        $purchase = ElectricityPurchase::findOrFail($id);

        $this->editingType = 'purchase';
        $this->editingId = $id;
        $this->edit_date = $purchase->created_at->format('Y-m-d');
        $this->edit_price = $purchase->purchase_price;
        $this->edit_kwh = $purchase->kwh_bought;
    }

    public function editCheck($id)
    {
        $check = ElectricityUsageCheck::findOrFail($id);

        $this->editingType = 'check';
        $this->editingId = $id;
        $this->edit_date = $check->created_at->format('Y-m-d');
        $this->edit_remaining = $check->kwh_remaining;
    }

    public function saveEdit()
    {
        if ($this->editingType === 'purchase') {
            $this->validate([
                'edit_date' => 'required|date|before_or_equal:today',
                'edit_price' => 'required|numeric|min:0',
                'edit_kwh' => 'required|numeric|min:0.01',
            ]);

            $purchase = ElectricityPurchase::findOrFail($this->editingId);

            $purchase->purchase_price = $this->edit_price;
            $purchase->kwh_bought = $this->edit_kwh;
            // Tarif ikut dihitung ulang supaya tetap konsisten dengan nominal & kWh.
            $purchase->price_per_unit = round($this->edit_price / $this->edit_kwh, 2);
            $purchase->created_at = Carbon::parse($this->edit_date)->setTimeFrom($purchase->created_at);
            $purchase->save();
        } elseif ($this->editingType === 'check') {
            $this->validate([
                'edit_date' => 'required|date|before_or_equal:today',
                'edit_remaining' => 'required|numeric|min:0',
            ]);

            $check = ElectricityUsageCheck::findOrFail($this->editingId);

            $check->kwh_remaining = $this->edit_remaining;
            // Sudah dikoreksi manual, jadi bukan lagi tebakan.
            $check->is_estimated = false;
            $check->created_at = Carbon::parse($this->edit_date)->setTimeFrom($check->created_at);
            $check->save();
        }

        $this->cancelEdit();
        session()->flash('message', 'Data berhasil diperbarui.');
        $this->dispatch('refresh-dashboard');
    }

    public function cancelEdit()
    {
        $this->reset(['editingType', 'editingId', 'edit_date', 'edit_price', 'edit_kwh', 'edit_remaining']);
        $this->resetErrorBag();
    }

    public function confirmDelete($type, $id)
    {
        $this->confirmingDeleteType = $type;
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete()
    {
        $this->reset(['confirmingDeleteType', 'confirmingDeleteId']);
    }

    public function delete()
    {
        if ($this->confirmingDeleteType === 'purchase') {
            ElectricityPurchase::findOrFail($this->confirmingDeleteId)->delete();
        } elseif ($this->confirmingDeleteType === 'check') {
            ElectricityUsageCheck::findOrFail($this->confirmingDeleteId)->delete();
        }

        $this->cancelDelete();
        session()->flash('message', 'Data berhasil dihapus.');
        $this->dispatch('refresh-dashboard');
    }

    public function render()
    {
        return view('livewire.electricity-history', [
            'purchases' => ElectricityPurchase::latest()->paginate(10, ['*'], 'purchasePage'),
            'checks' => ElectricityUsageCheck::latest()->paginate(10, ['*'], 'checkPage'),
        ]);
    }
}
