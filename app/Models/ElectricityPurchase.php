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
        'price_per_unit'
    ];
}
