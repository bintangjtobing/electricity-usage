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

    public function test_daily_average_only_uses_the_recent_window(): void
    {
        // Riwayat lama yang hemat (5 kWh/hari) tidak boleh menyeret rata-rata
        // untuk proyeksi; pola sekarang 20 kWh/hari. Di produksi, data
        // April-Juli membuat rata-rata 14,94 padahal sejak Agustus ~17.
        $this->check('2026-05-01 00:00:00', 500);
        $this->check('2026-07-10 00:00:00', 150);
        $this->purchase('2026-07-20 00:00:00', 560);
        $this->check('2026-08-01 00:00:00', 600);
        $this->check('2026-08-31 00:00:00', 0);

        $this->assertSame(20.0, UsageCalculator::dailyAverage());
        // Statistik sepanjang riwayat tetap tersedia untuk total terpakai.
        $this->assertSame(1060.0, UsageCalculator::stats()['totalUsage']);
    }

    public function test_recent_window_starts_at_the_reading_before_the_cutoff(): void
    {
        // Batas jendela (30 hari sebelum pembacaan terakhir) jatuh di tengah
        // selang 1-21 Agustus; selang itu dihitung utuh, bukan dibuang.
        $this->check('2026-07-01 00:00:00', 900);
        $this->check('2026-08-01 00:00:00', 400);
        $this->check('2026-08-21 00:00:00', 200);
        $this->check('2026-09-10 00:00:00', 0);

        // (400 - 0) / 40 hari.
        $this->assertSame(10.0, UsageCalculator::dailyAverage());
    }

    public function test_daily_average_uses_all_history_when_shorter_than_the_window(): void
    {
        $this->check('2026-09-01 00:00:00', 100);
        $this->check('2026-09-11 00:00:00', 40);

        $this->assertSame(6.0, UsageCalculator::dailyAverage());
    }

    public function test_estimated_remaining_subtracts_usage_since_last_reading(): void
    {
        $this->travelTo('2026-10-01 12:00:00');
        $this->check('2026-09-28 12:00:00', 100);

        $last = ElectricityUsageCheck::first();

        $this->assertSame(55.0, UsageCalculator::estimatedRemaining($last, 15.0));
        // Tidak pernah negatif: meteran berhenti di nol.
        $this->assertSame(0.0, UsageCalculator::estimatedRemaining($last, 50.0));
    }
}
