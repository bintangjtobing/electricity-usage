<?php

namespace Tests\Feature;

use App\Livewire\ElectricityDashboard;
use App\Livewire\ElectricityHistory;
use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HistoryCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->travelTo('2026-10-05 09:00:00');
    }

    private function check(string $at, float $remaining, bool $estimated = false): ElectricityUsageCheck
    {
        return ElectricityUsageCheck::forceCreate([
            'meter_number' => 'M1', 'kwh_remaining' => $remaining, 'is_estimated' => $estimated,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    private function purchase(string $at): ElectricityPurchase
    {
        return ElectricityPurchase::forceCreate([
            'meter_number' => 'M1', 'owner_name' => 'X', 'tariff_type' => 'R1',
            'purchase_price' => 500000, 'kwh_bought' => 314.65, 'price_per_unit' => 1589.07,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    public function test_calendar_shows_current_month_with_badges(): void
    {
        $this->check('2026-09-24 12:00:00', 130);
        $this->check('2026-10-01 11:59:59', 10);
        $this->purchase('2026-10-01 12:00:00');
        $this->check('2026-10-01 12:00:01', 324.65);

        Livewire::test(ElectricityHistory::class)
            ->assertSet('month', '2026-10')
            ->assertSee('Oktober 2026')
            ->assertSee('Beli')
            ->assertSee('500rb')
            ->assertSee('Sisa 324.65')
            ->assertSee('Total Pemakaian')
            ->assertSee('sebelum top-up');
    }

    public function test_month_navigation(): void
    {
        Livewire::test(ElectricityHistory::class)
            ->call('previousMonth')
            ->assertSet('month', '2026-09')
            ->assertSee('September 2026')
            ->call('nextMonth')
            ->call('nextMonth')
            ->assertSet('month', '2026-11')
            ->call('goToToday')
            ->assertSet('month', '2026-10');
    }

    public function test_tampered_month_falls_back_to_today(): void
    {
        Livewire::test(ElectricityHistory::class)
            ->set('month', '2026-13')
            ->assertSee('Oktober 2026');
    }

    public function test_moving_a_purchase_moves_its_before_and_after_points(): void
    {
        $this->check('2026-09-20 12:00:00', 200);
        $pre = $this->check('2026-09-24 11:59:59', 10);
        $purchase = $this->purchase('2026-09-24 12:00:00');
        $post = $this->check('2026-09-24 12:00:01', 324.65);
        $unrelated = $this->check('2026-09-24 08:00:00', 40);

        Livewire::test(ElectricityHistory::class)
            ->call('editPurchase', $purchase->id)
            ->set('edit_date', '2026-09-23')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $this->assertSame('2026-09-23 12:00:00', $purchase->fresh()->created_at->toDateTimeString());
        $this->assertSame('2026-09-23 11:59:59', $pre->fresh()->created_at->toDateTimeString());
        $this->assertSame('2026-09-23 12:00:01', $post->fresh()->created_at->toDateTimeString());
        $this->assertSame('2026-09-24 08:00:00', $unrelated->fresh()->created_at->toDateTimeString());
    }

    public function test_edit_accepts_indonesian_number_formats(): void
    {
        $purchase = $this->purchase('2026-10-01 12:00:00');

        Livewire::test(ElectricityHistory::class)
            ->call('editPurchase', $purchase->id)
            ->assertSet('edit_price', '500.000')
            ->set('edit_kwh', '314,6')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $purchase->refresh();
        $this->assertSame(500000.0, $purchase->purchase_price);
        $this->assertSame(314.6, $purchase->kwh_bought);
    }

    public function test_chart_daily_usage_skips_estimates_and_top_up_pairs(): void
    {
        $this->check('2026-09-20 11:59:59', 200);
        $this->check('2026-09-21 11:59:59', 180, estimated: true);
        $this->check('2026-09-22 11:59:59', 160);
        $this->check('2026-09-24 11:59:59', 120);
        $this->purchase('2026-09-24 12:00:00');
        $this->check('2026-09-24 12:00:01', 434.65);

        $daily = Livewire::test(ElectricityDashboard::class)->get('chartData')['dailyUsage'];

        // Pertama: belum ada pembanding. Estimasi: null. 22 Sep diukur dari
        // 20 Sep (bacaan nyata), bukan dari titik estimasi. Pasangan top-up
        // (selang 2 detik) null, bukan anjlok ke 0.
        $this->assertSame([null, null, 20.0, 20.0, null], $daily);
    }
}
