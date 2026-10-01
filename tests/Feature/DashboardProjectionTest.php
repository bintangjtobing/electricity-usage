<?php

namespace Tests\Feature;

use App\Livewire\ElectricityDashboard;
use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardProjectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Setting::current()->update(['payday_day' => 25, 'low_kwh_alert' => 50]);
    }

    private function check(string $at, float $remaining): void
    {
        ElectricityUsageCheck::forceCreate([
            'meter_number' => 'M1',
            'kwh_remaining' => $remaining,
            'is_estimated' => false,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    public function test_projections_count_usage_since_the_last_reading(): void
    {
        // Pemakaian 20 kWh/hari; pembacaan terakhir (200 kWh) sudah 5 hari lalu,
        // jadi sisa sekarang ~100 kWh. Dulu proyeksi memakai 200 seolah-olah
        // baru dibaca, sehingga "cukup N hari lagi" kelebihan 5 hari.
        $this->travelTo('2026-10-01 12:00:00');
        // Pembelian sebelum pembacaan pertama: tidak masuk selang mana pun,
        // hanya supaya blok ringkasan (butuh pembelian terakhir) ikut dirender.
        ElectricityPurchase::forceCreate([
            'meter_number' => 'M1', 'owner_name' => 'X', 'tariff_type' => 'R1',
            'purchase_price' => 500000, 'kwh_bought' => 314.65, 'price_per_unit' => 1589.07,
            'created_at' => '2026-09-21 11:00:00', 'updated_at' => '2026-09-21 11:00:00',
        ]);
        $this->check('2026-09-21 12:00:00', 300);
        $this->check('2026-09-26 12:00:00', 200);

        $component = Livewire::test(ElectricityDashboard::class)
            ->assertSet('dailyAverage', 20.0)
            ->assertSet('remainingKwh', 200.0)
            ->assertSet('estimatedRemainingKwh', 100.0)
            ->assertSet('daysUntilEmpty', 5)
            ->assertSee('Dengan sisa sekitar')
            ->assertSee('100.00 kWh')
            ->assertSee('30 hari terakhir');

        // 25 Okt 00:00 dari 1 Okt 12:00 = 23,5 hari -> dibulatkan ke atas 24.
        $projection = $component->get('projectionToPayday');
        $this->assertSame(24, $projection['daysUntilPayday']);
        $this->assertSame(-380.0, $projection['remainingKwh']);
        $this->assertTrue($projection['needToBuy']);
    }
}
