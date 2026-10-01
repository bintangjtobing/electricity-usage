<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ElectricityPurchase extends Model
{
    use HasFactory;

    /**
     * Form pembelian mencatat titik sisa tepat di sekitar waktu pembelian
     * (sebelum: -1 detik, sesudah: +1 detik, atau satu titik estimasi di detik
     * yang sama). Titik dalam selisih ini dianggap milik pembelian tersebut.
     */
    public const ATTACHED_CHECK_SECONDS = 2;

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

    /** Titik sisa yang dicatat form pembelian bersama pembelian ini. */
    public function attachedChecks(): Collection
    {
        return ElectricityUsageCheck::whereBetween('created_at', [
            $this->created_at->copy()->subSeconds(self::ATTACHED_CHECK_SECONDS),
            $this->created_at->copy()->addSeconds(self::ATTACHED_CHECK_SECONDS),
        ])->orderBy('created_at')->get();
    }
}
