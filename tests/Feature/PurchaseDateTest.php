<?php

namespace Tests\Feature;

use App\Livewire\ElectricityPurchaseForm;
use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseDateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

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
        $this->assertSame('2026-06-10', $check->created_at->format('Y-m-d'));
        $this->assertSame(629.30, round($purchase->kwh_bought, 2));
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

    public function test_reading_before_topup_gives_exact_balance_and_is_not_estimated(): void
    {
        // Cek terakhir 50 kWh, tapi saat top-up meteran tinggal 12 kWh --
        // 38 kWh terpakai di antaranya. Saldo sesudahnya harus 12 + 100, bukan 50 + 100.
        ElectricityUsageCheck::forceCreate([
            'meter_number' => '50220822832',
            'kwh_remaining' => 50,
            'is_estimated' => false,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        Livewire::test(ElectricityPurchaseForm::class)
            ->set('kwh_bought', 100)
            ->set('kwh_before_purchase', 12)
            ->call('submit')
            ->assertHasNoErrors();

        $check = ElectricityUsageCheck::latest('id')->first();

        $this->assertSame(112.0, round($check->kwh_remaining, 2));
        $this->assertFalse($check->is_estimated);
    }

    public function test_reading_before_topup_stores_two_points_pre_and_post(): void
    {
        // Sisa sebelum beli diisi => grafik perlu dua titik: turun dulu ke 12
        // (pemakaian nyata sejak cek terakhir), lalu naik ke 112 setelah top-up.
        ElectricityUsageCheck::forceCreate([
            'meter_number' => '50220822832',
            'kwh_remaining' => 50,
            'is_estimated' => false,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        Livewire::test(ElectricityPurchaseForm::class)
            ->set('kwh_bought', 100)
            ->set('kwh_before_purchase', 12)
            ->call('submit')
            ->assertHasNoErrors();

        // Seed + pre + post = 3 titik.
        $this->assertSame(3, ElectricityUsageCheck::count());

        $checks = ElectricityUsageCheck::orderBy('created_at')->get();
        $pre = $checks[1];
        $post = $checks[2];

        $this->assertSame(12.0, round($pre->kwh_remaining, 2));
        $this->assertFalse((bool) $pre->is_estimated);
        $this->assertSame(112.0, round($post->kwh_remaining, 2));
        $this->assertFalse((bool) $post->is_estimated);

        // Urutan waktu harus pre < post supaya kalkulator menaruh pembelian di
        // selang yang benar (pemakaian 38 kWh di selang sebelum top-up).
        $this->assertTrue($pre->created_at->lt($post->created_at));

        $stats = \App\Support\UsageCalculator::stats();
        $this->assertSame(38.0, round($stats['totalUsage'], 2));
    }

    public function test_balance_is_flagged_estimated_when_reading_is_omitted(): void
    {
        ElectricityUsageCheck::forceCreate([
            'meter_number' => '50220822832',
            'kwh_remaining' => 50,
            'is_estimated' => false,
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        Livewire::test(ElectricityPurchaseForm::class)
            ->set('kwh_bought', 100)
            ->call('submit')
            ->assertHasNoErrors();

        $check = ElectricityUsageCheck::latest('id')->first();

        // Terpaksa memakai catatan terakhir; pemakaian di antaranya tak diketahui.
        $this->assertSame(150.0, round($check->kwh_remaining, 2));
        $this->assertTrue($check->is_estimated);
    }

    public function test_quick_amount_button_fills_price_and_kwh(): void
    {
        Livewire::test(ElectricityPurchaseForm::class)
            ->call('setAmount', 250000)
            ->assertSet('purchase_price', 250000.0)
            ->assertSet('kwh_bought', 157.32);
    }
}
