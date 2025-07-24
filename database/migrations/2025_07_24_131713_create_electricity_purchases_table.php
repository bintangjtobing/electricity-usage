<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('electricity_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number');
            $table->string('owner_name');
            $table->string('tariff_type');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('kwh_bought', 10, 2);
            $table->decimal('price_per_unit', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_purchases');
    }
};
