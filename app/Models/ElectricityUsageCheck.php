<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityUsageCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_number',
        'kwh_remaining'
    ];
}
