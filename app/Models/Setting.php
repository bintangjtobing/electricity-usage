<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'meter_number',
        'owner_name',
        'address',
        'tariff_type',
        'price_per_unit',
        'payday_day',
        'threshold_hemat',
        'threshold_boros',
        'low_kwh_alert',
    ];

    protected $casts = [
        'price_per_unit' => 'float',
        'payday_day' => 'integer',
        'threshold_hemat' => 'float',
        'threshold_boros' => 'float',
        'low_kwh_alert' => 'float',
    ];

    /**
     * Satu-satunya baris setting. Dibuat dengan nilai default bila belum ada,
     * supaya aplikasi tetap jalan di database yang masih kosong.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'meter_number' => '-',
            'owner_name' => '-',
            'address' => '-',
            'tariff_type' => '-',
            'price_per_unit' => 1589.07,
        ]);
    }
}
