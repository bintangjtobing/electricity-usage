<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectricityUsageCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_number',
        'kwh_remaining',
        'is_estimated'
    ];

    protected $casts = [
        'kwh_remaining' => 'float',
        'is_estimated' => 'boolean',
    ];

    public function scopeMeasured($query)
    {
        return $query->where('is_estimated', false);
    }

    /** Titik ini dicatat otomatis oleh form pembelian ({@see ElectricityPurchase::attachedChecks()}). */
    public function isFromPurchase(): bool
    {
        return ElectricityPurchase::whereBetween('created_at', [
            $this->created_at->copy()->subSeconds(ElectricityPurchase::ATTACHED_CHECK_SECONDS),
            $this->created_at->copy()->addSeconds(ElectricityPurchase::ATTACHED_CHECK_SECONDS),
        ])->exists();
    }
}
