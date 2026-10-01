<?php

namespace App\Livewire;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use App\Support\DecimalInput;
use App\Support\UsageCalendar;
use Carbon\Carbon;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ElectricityHistory extends Component
{
    /** 'month' (halaman Riwayat) atau 'week' (kalender di dashboard). */
    #[Locked]
    public $mode = 'month';

    /** Bulan yang ditampilkan di kalender, format Y-m. */
    public $month;

    /** Hari Minggu awal minggu yang ditampilkan (mode week), format Y-m-d. */
    public $weekStart;

    /** Data yang sedang diedit; null bila tidak ada. */
    public $editingType = null;
    public $editingId = null;

    public $edit_date;
    public $edit_price;
    public $edit_kwh;
    public $edit_remaining;

    public $confirmingDeleteType = null;
    public $confirmingDeleteId = null;

    public function mount($mode = 'month')
    {
        $this->mode = $mode === 'week' ? 'week' : 'month';
        $this->month = now()->format('Y-m');
        $this->weekStart = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
    }

    public function previousPeriod()
    {
        if ($this->mode === 'week') {
            $this->weekStart = $this->currentWeek()->subWeek()->format('Y-m-d');
        } else {
            $this->month = $this->currentMonth()->subMonthNoOverflow()->format('Y-m');
        }
        $this->cancelEdit();
    }

    public function nextPeriod()
    {
        if ($this->mode === 'week') {
            $this->weekStart = $this->currentWeek()->addWeek()->format('Y-m-d');
        } else {
            $this->month = $this->currentMonth()->addMonthNoOverflow()->format('Y-m');
        }
        $this->cancelEdit();
    }

    public function goToToday()
    {
        $this->month = now()->format('Y-m');
        $this->weekStart = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        $this->cancelEdit();
    }

    public function editPurchase($id)
    {
        $purchase = ElectricityPurchase::findOrFail($id);

        $this->cancelDelete();
        $this->editingType = 'purchase';
        $this->editingId = $id;
        $this->edit_date = $purchase->created_at->format('Y-m-d');
        $this->edit_price = number_format($purchase->purchase_price, 0, ',', '.');
        $this->edit_kwh = $purchase->kwh_bought;
    }

    public function editCheck($id)
    {
        $check = ElectricityUsageCheck::findOrFail($id);

        $this->cancelDelete();
        $this->editingType = 'check';
        $this->editingId = $id;
        $this->edit_date = $check->created_at->format('Y-m-d');
        $this->edit_remaining = $check->kwh_remaining;
    }

    public function saveEdit()
    {
        // Nominal Rupiah: titik/koma hanya pemisah ribuan. kWh: koma maupun
        // titik boleh jadi desimal (keyboard HP berlokal Indonesia).
        $this->edit_price = $this->edit_price === null || $this->edit_price === ''
            ? null
            : preg_replace('/\D/', '', (string) $this->edit_price);
        $this->edit_kwh = DecimalInput::normalize($this->edit_kwh);
        $this->edit_remaining = DecimalInput::normalize($this->edit_remaining);

        if ($this->editingType === 'purchase') {
            $this->validate([
                'edit_date' => 'required|date|before_or_equal:today',
                'edit_price' => 'required|numeric|min:0',
                'edit_kwh' => 'required|numeric|min:0.01',
            ]);

            $purchase = ElectricityPurchase::findOrFail($this->editingId);
            $originalAt = $purchase->created_at->copy();
            $attachedChecks = $purchase->attachedChecks();
            $kwhDelta = round((float) $this->edit_kwh - $purchase->kwh_bought, 2);

            $purchase->purchase_price = $this->edit_price;
            $purchase->kwh_bought = $this->edit_kwh;
            // Tarif ikut dihitung ulang supaya tetap konsisten dengan nominal & kWh.
            $purchase->price_per_unit = round($this->edit_price / $this->edit_kwh, 2);
            $purchase->created_at = Carbon::parse($this->edit_date)->setTimeFrom($originalAt);
            $purchase->save();

            // Titik sisa sebelum/sesudah top-up ikut pindah tanggal. Kalau
            // tertinggal, kalkulator melihat saldo melonjak tanpa pembelian di
            // tanggal lama dan pembelian tanpa saldo naik di tanggal baru.
            // Saldo sesudah top-up (= sebelum + dibeli) ikut koreksi kWh; saldo
            // sebelum top-up dibaca dari meteran, jadi dibiarkan.
            $shift = $originalAt->diffInSeconds($purchase->created_at, false);

            foreach ($attachedChecks as $check) {
                $isAfterTopUp = $check->created_at->gte($originalAt);

                if ($shift === 0 && ($kwhDelta == 0 || ! $isAfterTopUp)) {
                    continue;
                }

                if ($isAfterTopUp && $kwhDelta != 0) {
                    $check->kwh_remaining = round($check->kwh_remaining + $kwhDelta, 2);
                }

                $check->created_at = $check->created_at->copy()->addSeconds($shift);
                $check->save();
            }
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
        $this->dispatch('history-updated');
    }

    public function cancelEdit()
    {
        $this->reset(['editingType', 'editingId', 'edit_date', 'edit_price', 'edit_kwh', 'edit_remaining']);
        $this->resetErrorBag();
    }

    public function confirmDelete($type, $id)
    {
        $this->cancelEdit();
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
        $this->dispatch('history-updated');
    }

    /** Properti publik bisa diubah dari browser; nilai rusak kembali ke bulan ini. */
    private function currentMonth(): Carbon
    {
        if (! is_string($this->month) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $this->month)) {
            $this->month = now()->format('Y-m');
        }

        return Carbon::createFromFormat('Y-m-d', $this->month . '-01')->startOfDay();
    }

    private function currentWeek(): Carbon
    {
        if (! is_string($this->weekStart) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->weekStart)
            || ! checkdate((int) substr($this->weekStart, 5, 2), (int) substr($this->weekStart, 8, 2), (int) substr($this->weekStart, 0, 4))) {
            $this->weekStart = now()->startOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        }

        return Carbon::createFromFormat('Y-m-d', $this->weekStart)->startOfWeek(Carbon::SUNDAY)->startOfDay();
    }

    public function render()
    {
        $setting = Setting::current();

        return view('livewire.electricity-history', [
            'calendar' => $this->mode === 'week'
                ? UsageCalendar::week($this->currentWeek())
                : UsageCalendar::month($this->currentMonth()),
            'weekdays' => UsageCalendar::WEEKDAYS,
            'thresholdHemat' => (float) $setting->threshold_hemat,
            'thresholdBoros' => (float) $setting->threshold_boros,
        ]);
    }
}
