<?php

namespace Tests\Feature;

use App\Livewire\ElectricityPurchaseForm;
use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_purchase_date_is_today(): void
    {
        Livewire::test(ElectricityPurchaseForm::class)
            ->assertSet('purchase_date', now()->format('Y-m-d'));
    }

    public function test_date_input_is_prefilled_with_today(): void
    {
        // Livewire tidak mengisi value input saat render awal, jadi tanpa
        // atribut value= kotak tanggalnya tampil kosong walau propertinya terisi.
        Livewire::test(ElectricityPurchaseForm::class)
            ->assertSeeHtml('value="' . now()->format('Y-m-d') . '"');
    }

    public function test_purchase_saved_with_today_when_date_untouched(): void
    {
        Livewire::test(ElectricityPurchaseForm::class)
            ->set('purchase_price_formatted', '250000')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertTrue(ElectricityPurchase::first()->created_at->isToday());
        $this->assertTrue(ElectricityUsageCheck::first()->created_at->isToday());
    }

    public function test_backdated_purchase_is_stored_on_chosen_date(): void
    {
        Livewire::test(ElectricityPurchaseForm::class)
            ->set('purchase_price_formatted', '1000000')
            ->set('purchase_date', '2026-06-10')
            ->call('submit')
            ->assertHasNoErrors();

        $purchase = ElectricityPurchase::first();
        $check = ElectricityUsageCheck::first();

        $this->assertSame('2026-06-10', $purchase->created_at->format('Y-m-d'));
        // Pembelian dan usage-check otomatis harus punya timestamp sama,
        // karena dashboard mencocokkan keduanya dalam rentang 60 menit.
        $this->assertSame('2026-06-10', $check->created_at->format('Y-m-d'));
        $this->assertSame(629.30, round((float) $purchase->kwh_bought, 2));
    }

    public function test_future_date_is_rejected(): void
    {
        Livewire::test(ElectricityPurchaseForm::class)
            ->set('purchase_price_formatted', '250000')
            ->set('purchase_date', now()->addDay()->format('Y-m-d'))
            ->call('submit')
            ->assertHasErrors('purchase_date');

        $this->assertSame(0, ElectricityPurchase::count());
    }

    public function test_backdated_purchase_uses_balance_as_of_that_date(): void
    {
        // Saldo 50 kWh pada 1 Juni, lalu 10 kWh pada 1 Juli.
        ElectricityUsageCheck::forceCreate([
            'meter_number' => '50220822832',
            'kwh_remaining' => 50,
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);
        ElectricityUsageCheck::forceCreate([
            'meter_number' => '50220822832',
            'kwh_remaining' => 10,
            'created_at' => '2026-07-01 08:00:00',
            'updated_at' => '2026-07-01 08:00:00',
        ]);

        // Pembelian dicatat mundur ke 10 Juni -> harus dihitung dari saldo 50,
        // bukan dari saldo terbaru (10).
        Livewire::test(ElectricityPurchaseForm::class)
            ->set('kwh_bought', 100)
            ->set('purchase_date', '2026-06-10')
            ->call('submit')
            ->assertHasNoErrors();

        $newCheck = ElectricityUsageCheck::whereDate('created_at', '2026-06-10')->first();

        $this->assertNotNull($newCheck);
        $this->assertSame(150.0, (float) $newCheck->kwh_remaining);
    }
}
