<?php

namespace Database\Seeders;

use App\Models\ElectricityPurchase;
use App\Models\ElectricityUsageCheck;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ElectricityDataSeeder extends Seeder
{
    public function run()
    {
        // Seed purchases based on screenshot data
        ElectricityPurchase::create([
            'meter_number' => '86281730696',
            'owner_name' => 'I MADE WIRAHADI KESUMA 9',
            'tariff_type' => 'R1 / 1300 VA',
            'purchase_price' => 250000,
            'kwh_bought' => 157.4,
            'price_per_unit' => 1588.31,
            'created_at' => Carbon::parse('2025-07-15 14:51:27'),
            'updated_at' => Carbon::parse('2025-07-15 14:51:27'),
        ]);

        // Add more historical purchases
        ElectricityPurchase::create([
            'meter_number' => '86281730696',
            'owner_name' => 'I MADE WIRAHADI KESUMA 9',
            'tariff_type' => 'R1 / 1300 VA',
            'purchase_price' => 200000,
            'kwh_bought' => 125.9,
            'price_per_unit' => 1588.31,
            'created_at' => Carbon::parse('2025-06-28 10:30:00'),
            'updated_at' => Carbon::parse('2025-06-28 10:30:00'),
        ]);

        ElectricityPurchase::create([
            'meter_number' => '86281730696',
            'owner_name' => 'I MADE WIRAHADI KESUMA 9',
            'tariff_type' => 'R1 / 1300 VA',
            'purchase_price' => 150000,
            'kwh_bought' => 94.5,
            'price_per_unit' => 1588.31,
            'created_at' => Carbon::parse('2025-06-10 15:20:00'),
            'updated_at' => Carbon::parse('2025-06-10 15:20:00'),
        ]);

        // Seed usage checks based on screenshot data
        ElectricityUsageCheck::create([
            'meter_number' => '86281730696',
            'kwh_remaining' => 114.67,
            'created_at' => Carbon::parse('2025-07-19 15:02:53'),
            'updated_at' => Carbon::parse('2025-07-19 15:02:53'),
        ]);

        ElectricityUsageCheck::create([
            'meter_number' => '86281730696',
            'kwh_remaining' => 91,
            'created_at' => Carbon::parse('2025-07-21 22:49:07'),
            'updated_at' => Carbon::parse('2025-07-21 22:49:07'),
        ]);

        ElectricityUsageCheck::create([
            'meter_number' => '86281730696',
            'kwh_remaining' => 71.28,
            'created_at' => Carbon::parse('2025-07-23 15:58:47'),
            'updated_at' => Carbon::parse('2025-07-23 15:58:47'),
        ]);

        ElectricityUsageCheck::create([
            'meter_number' => '86281730696',
            'kwh_remaining' => 62.4,
            'created_at' => Carbon::parse('2025-07-24 11:31:10'),
            'updated_at' => Carbon::parse('2025-07-24 11:31:10'),
        ]);

        // Add more checks for better analytics
        ElectricityUsageCheck::create([
            'meter_number' => '86281730696',
            'kwh_remaining' => 143.5,
            'created_at' => Carbon::parse('2025-07-16 09:00:00'),
            'updated_at' => Carbon::parse('2025-07-16 09:00:00'),
        ]);

        ElectricityUsageCheck::create([
            'meter_number' => '86281730696',
            'kwh_remaining' => 130.2,
            'created_at' => Carbon::parse('2025-07-17 18:30:00'),
            'updated_at' => Carbon::parse('2025-07-17 18:30:00'),
        ]);
    }
}