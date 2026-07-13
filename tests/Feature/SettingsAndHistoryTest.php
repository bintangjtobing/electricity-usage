<?php

namespace Tests\Feature;

use App\Livewire\ElectricityHistory;
use App\Livewire\ElectricityPurchaseForm;
use App\Livewire\SettingsForm;
use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsAndHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_settings_can_be_saved_without_a_deploy(): void
    {
        Livewire::test(SettingsForm::class)
            ->set('meter_number', '99999999999')
            ->set('owner_name', 'Pemilik Baru')
            ->set('address', 'Alamat Baru')
            ->set('tariff_type', 'R1 900 VA')
            ->set('price_per_unit', 1700)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('99999999999', Setting::current()->meter_number);
        $this->assertSame(1700.0, Setting::current()->price_per_unit);
    }

    public function test_purchase_form_picks_up_the_new_tariff_from_settings(): void
    {
        Setting::current()->update(['price_per_unit' => 2000, 'meter_number' => 'M-BARU']);

        Livewire::test(ElectricityPurchaseForm::class)
            ->assertSet('price_per_unit', 2000.0)
            ->assertSet('meter_number', 'M-BARU')
            ->set('purchase_price_formatted', '100000')
            ->assertSet('kwh_bought', 50.0);
    }

    public function test_rate_calculator_derives_tariff_from_a_receipt(): void
    {
        Livewire::test(SettingsForm::class)
            ->set('calc_price', 1000000)
            ->set('calc_kwh', 629.3)
            ->call('calculateRate')
            ->assertSet('price_per_unit', 1589.07);
    }

    public function test_boros_threshold_must_exceed_hemat(): void
    {
        Livewire::test(SettingsForm::class)
            ->set('threshold_hemat', 9)
            ->set('threshold_boros', 5)
            ->call('save')
            ->assertHasErrors('threshold_boros');
    }

    public function test_a_mistyped_reading_can_be_corrected(): void
    {
        $check = ElectricityUsageCheck::forceCreate([
            'meter_number' => 'M1',
            'kwh_remaining' => 999,
            'is_estimated' => true,
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);

        Livewire::test(ElectricityHistory::class)
            ->set('activeTab', 'checks')
            ->call('editCheck', $check->id)
            ->set('edit_remaining', 42.5)
            ->call('saveEdit')
            ->assertHasNoErrors();

        $check->refresh();

        $this->assertSame(42.5, $check->kwh_remaining);
        // Nilai yang sudah dikoreksi manual bukan lagi tebakan.
        $this->assertFalse($check->is_estimated);
    }

    public function test_editing_a_purchase_recalculates_its_tariff(): void
    {
        $purchase = ElectricityPurchase::forceCreate([
            'meter_number' => 'M1',
            'owner_name' => 'X',
            'tariff_type' => 'R1',
            'purchase_price' => 250000,
            'kwh_bought' => 157.32,
            'price_per_unit' => 1589.07,
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);

        Livewire::test(ElectricityHistory::class)
            ->call('editPurchase', $purchase->id)
            ->set('edit_price', 100000)
            ->set('edit_kwh', 50)
            ->call('saveEdit')
            ->assertHasNoErrors();

        $purchase->refresh();

        $this->assertSame(2000.0, $purchase->price_per_unit);
    }

    public function test_a_record_can_be_deleted(): void
    {
        $purchase = ElectricityPurchase::forceCreate([
            'meter_number' => 'M1',
            'owner_name' => 'X',
            'tariff_type' => 'R1',
            'purchase_price' => 250000,
            'kwh_bought' => 157.32,
            'price_per_unit' => 1589.07,
            'created_at' => '2026-06-01 08:00:00',
            'updated_at' => '2026-06-01 08:00:00',
        ]);

        Livewire::test(ElectricityHistory::class)
            ->call('confirmDelete', 'purchase', $purchase->id)
            ->call('delete');

        $this->assertSame(0, ElectricityPurchase::count());
    }
}
