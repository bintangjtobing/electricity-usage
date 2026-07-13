<?php

namespace Tests\Feature;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use App\Support\UsageCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageCalculatorTest extends TestCase
{
    use RefreshDatabase;

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

    private function purchase(string $at, float $bought): void
    {
        ElectricityPurchase::forceCreate([
            'meter_number' => 'M1',
            'owner_name' => 'X',
            'tariff_type' => 'R1',
            'purchase_price' => $bought * 1589.07,
            'kwh_bought' => $bought,
            'price_per_unit' => 1589.07,
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    public function test_simple_consumption_between_two_readings(): void
    {
        $this->check('2026-06-01 08:00:00', 100);
        $this->check('2026-06-11 08:00:00', 40);

        $stats = UsageCalculator::stats();

        $this->assertSame(60.0, $stats['totalUsage']);
        $this->assertSame(10.0, $stats['totalDays']);
        $this->assertSame(6.0, $stats['dailyAverage']);
    }

    public function test_purchase_between_readings_is_accounted_for(): void
    {
        $this->check('2026-06-01 08:00:00', 100);
        $this->purchase('2026-06-06 08:00:00', 50);
        $this->check('2026-06-11 08:00:00', 90);

        // Terpakai = 100 + 50 - 90 = 60 kWh dalam 10 hari.
        $stats = UsageCalculator::stats();

        $this->assertSame(60.0, $stats['totalUsage']);
        $this->assertSame(6.0, $stats['dailyAverage']);
    }

    public function test_multiple_purchases_in_one_interval_are_all_counted(): void
    {
        $this->check('2026-06-01 08:00:00', 100);
        $this->purchase('2026-06-03 08:00:00', 50);
        $this->purchase('2026-06-07 08:00:00', 30);
        $this->check('2026-06-11 08:00:00', 120);

        // Kode lama hanya mengambil SATU pembelian (->first()), sehingga
        // 30 kWh kedua hilang dan pemakaian terhitung 30 kWh terlalu kecil.
        // Benar: 100 + 50 + 30 - 120 = 60 kWh.
        $stats = UsageCalculator::stats();

        $this->assertSame(60.0, $stats['totalUsage']);
    }

    public function test_fractional_days_are_preserved(): void
    {
        // Selang 1,5 hari (36 jam). diffInDays() lama memotong jadi 1 hari,
        // membuat rata-rata harian terlihat 1,5x lebih boros dari aslinya.
        $this->check('2026-06-01 00:00:00', 100);
        $this->check('2026-06-02 12:00:00', 70);

        $stats = UsageCalculator::stats();

        $this->assertSame(1.5, $stats['totalDays']);
        $this->assertSame(20.0, $stats['dailyAverage']);
    }

    public function test_two_readings_on_the_same_day_do_not_break_the_average(): void
    {
        // Kode lama: hari = 0 tapi pemakaian tetap dijumlahkan -> pembagi
        // mengecil dan rata-rata membengkak.
        $this->check('2026-06-01 08:00:00', 100);
        $this->check('2026-06-01 20:00:00', 95);
        $this->check('2026-06-02 08:00:00', 90);

        $stats = UsageCalculator::stats();

        $this->assertSame(10.0, $stats['totalUsage']);
        $this->assertSame(1.0, $stats['totalDays']);
        $this->assertSame(10.0, $stats['dailyAverage']);
    }
}
