<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration /**
     * Run the migrations.
     */ {
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->integer('deviceTariffAmount')->nullable(); // Define the new column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('deviceTariffAmount'); // Drop the new column if needed
        });
    }
};
