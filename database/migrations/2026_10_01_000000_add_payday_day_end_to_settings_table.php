<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gajian bisa berupa rentang (mis. tanggal 1-4). Kosong = satu tanggal saja
     * (payday_day), jadi pengaturan lama tetap berlaku tanpa diubah.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('payday_day_end')->nullable()->after('payday_day');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('payday_day_end');
        });
    }
};
