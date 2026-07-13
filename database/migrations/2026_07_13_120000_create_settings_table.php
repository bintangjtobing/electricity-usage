<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('meter_number');
            $table->string('owner_name');
            $table->string('address');
            $table->string('tariff_type');
            $table->decimal('price_per_unit', 10, 2);
            $table->unsignedTinyInteger('payday_day')->default(10);
            $table->decimal('threshold_hemat', 8, 2)->default(5);
            $table->decimal('threshold_boros', 8, 2)->default(9);
            $table->decimal('low_kwh_alert', 8, 2)->default(20);
            $table->timestamps();
        });

        // Nilai awal diambil dari yang sebelumnya hardcoded di kode.
        DB::table('settings')->insert([
            'meter_number' => '50220822832',
            'owner_name' => 'Perdana Residence 002',
            'address' => 'Perdana Residence 2 No. C001, Jl. Danau Batur, Sumber Mulyorejo, Kec. Binjai Tim., Kota Binjai, Sumatera Utara 20734',
            'tariff_type' => 'R1T 2200 VA',
            'price_per_unit' => 1589.07,
            'payday_day' => 10,
            'threshold_hemat' => 5,
            'threshold_boros' => 9,
            'low_kwh_alert' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
