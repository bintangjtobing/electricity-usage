<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_number',
        'owner_name',
        'tariff_type',
        'purchase_price',
        'kwh_bought',
        'kwh_before_purchase',
        'price_per_unit'
    ];

    protected $casts = [
        'purchase_price' => 'float',
        'kwh_bought' => 'float',
        'kwh_before_purchase' => 'float',
        'price_per_unit' => 'float',
    ];
}
