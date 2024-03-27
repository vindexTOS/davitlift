<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_earn', function (Blueprint $table) {
            // Add the cashback column
            $table
                ->decimal('cashback', 8, 2)
                ->default(0)
                ->after('year');

            // Add the deviceTariff column
            $table
                ->decimal('deviceTariff', 8, 2)
                ->default(0)
                ->after('cashback');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_earn', function (Blueprint $table) {
            // Drop the cashback and deviceTariff columns if needed
            $table->dropColumn('cashback');
            $table->dropColumn('deviceTariff');
        });
    }
};
