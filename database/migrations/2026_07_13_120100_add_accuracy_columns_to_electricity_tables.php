<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electricity_usage_checks', function (Blueprint $table) {
            // Membedakan pembacaan meteran sungguhan dari nilai hasil prediksi,
            // supaya tebakan tidak ikut dipakai sebagai dasar analitik.
            $table->boolean('is_estimated')->default(false)->after('kwh_remaining');
        });

        Schema::table('electricity_purchases', function (Blueprint $table) {
            // Sisa kWh di meteran tepat sebelum token diisi. Tanpa ini, saldo
            // sesudah pembelian dihitung dari cek terakhir dan mengabaikan
            // pemakaian yang terjadi di antaranya.
            $table->decimal('kwh_before_purchase', 10, 2)->nullable()->after('kwh_bought');
        });
    }

    public function down(): void
    {
        Schema::table('electricity_usage_checks', function (Blueprint $table) {
            $table->dropColumn('is_estimated');
        });

        Schema::table('electricity_purchases', function (Blueprint $table) {
            $table->dropColumn('kwh_before_purchase');
        });
    }
};
