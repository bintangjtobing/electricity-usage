<?php

namespace Tests\Feature;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Support\UsageCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageCalendarTest extends TestCase
{
    use RefreshDatabase;

    private function check(string $at, float $remaining, bool $estimated = false): ElectricityUsageCheck
    {
        return ElectricityUsageCheck::forceCreate([
            'meter_number' => 'M1',
            'kwh_remaining' => $remaining,
            'is_estimated' => $estimated,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    private function purchase(string $at, float $bought, float $price): ElectricityPurchase
    {
        return ElectricityPurchase::forceCreate([
            'meter_number' => 'M1',
            'owner_name' => 'X',
            'tariff_type' => 'R1',
            'purchase_price' => $price,
            'kwh_bought' => $bought,
            'price_per_unit' => round($price / $bought, 2),
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    /** @return array<string, array> hari dalam grid, diindeks Y-m-d */
    private function days(string $month): array
    {
        $calendar = UsageCalendar::month(Carbon::parse($month));

        return collect($calendar['weeks'])->flatten(1)->keyBy('key')->all();
    }

    public function test_grid_runs_sunday_to_saturday_and_covers_the_month(): void
    {
        $calendar = UsageCalendar::month(Carbon::parse('2026-10-01'));
        $first = $calendar['weeks'][0][0];
        $last = collect($calendar['weeks'])->last()[6];

        $this->assertSame('Oktober 2026', $calendar['label']);
        $this->assertSame('2026-09-27', $first['key']);
        $this->assertTrue($first['date']->isSunday());
        $this->assertSame('2026-10-31', $last['key']);
        $this->assertFalse($first['inMonth']);
    }

    public function test_usage_is_spread_evenly_across_the_days_between_readings(): void
    {
        $this->check('2026-09-01 00:00:00', 100);
        $this->check('2026-09-03 00:00:00', 60);

        $days = $this->days('2026-09-01');

        $this->assertSame(20.0, $days['2026-09-01']['usage']);
        $this->assertSame(20.0, $days['2026-09-02']['usage']);
        $this->assertNull($days['2026-09-03']['usage']);
    }

    public function test_estimated_points_do_not_create_fake_zero_days(): void
    {
        // Titik estimasi "sisa terakhir + dibeli" (seperti 1-15 Juni di produksi)
        // membuat selang sebelumnya tampak 0 kWh. Yang dihitung hanya bacaan nyata.
        $this->check('2026-09-01 00:00:00', 100);
        $this->check('2026-09-02 00:00:00', 100, estimated: true);
        $this->check('2026-09-03 00:00:00', 60);

        $days = $this->days('2026-09-01');

        $this->assertSame(20.0, $days['2026-09-01']['usage']);
        $this->assertSame(20.0, $days['2026-09-02']['usage']);
    }

    public function test_partially_recorded_day_reports_a_fair_daily_rate(): void
    {
        $this->check('2026-09-01 12:00:00', 100);
        $this->check('2026-09-02 12:00:00', 80);

        $days = $this->days('2026-09-01');

        // Setengah hari tercatat: 10 kWh, setara laju 20 kWh/hari.
        $this->assertSame(10.0, $days['2026-09-01']['usage']);
        $this->assertSame(43200, $days['2026-09-01']['coveredSeconds']);
        $this->assertSame('12:00', $days['2026-09-01']['coveredFrom']->format('H:i'));
        $this->assertSame('2026-09-02 00:00', $days['2026-09-01']['coveredUntil']->format('Y-m-d H:i'));
        $this->assertSame('00:00', $days['2026-09-02']['coveredFrom']->format('H:i'));
        $this->assertSame('12:00', $days['2026-09-02']['coveredUntil']->format('H:i'));
        $this->assertSame(20.0, $days['2026-09-01']['rate']);
    }

    public function test_purchase_day_groups_items_labels_points_and_sums_the_month(): void
    {
        $this->check('2026-09-24 12:00:00', 130);
        $pre = $this->check('2026-10-01 11:59:59', 10);
        $this->purchase('2026-10-01 12:00:00', 314.65, 500000);
        $post = $this->check('2026-10-01 12:00:01', 324.65);
        $this->check('2026-10-02 12:00:00', 304.65);

        $calendar = UsageCalendar::month(Carbon::parse('2026-10-01'));
        $day = collect($calendar['weeks'])->flatten(1)->firstWhere('key', '2026-10-01');

        $this->assertCount(1, $day['purchases']);
        $this->assertCount(2, $day['checks']);
        $this->assertSame('sebelum top-up', $calendar['roles'][$pre->id]);
        $this->assertSame('sesudah top-up', $calendar['roles'][$post->id]);

        $totals = $calendar['totals'];
        $this->assertSame(1, $totals['purchaseCount']);
        $this->assertSame(500000.0, (float) $totals['purchasePrice']);
        $this->assertSame(3, $totals['checkCount']);
        $this->assertSame(304.65, $totals['lastCheck']->kwh_remaining);
        // 24 Sep-1 Okt: 120 kWh / 7 hari; 1-2 Okt: 20 kWh / 1 hari. Oktober
        // mendapat 1 Okt penuh + 2 Okt sampai 12:00.
        $this->assertSame(1.5, $totals['coveredDays']);
    }
}
