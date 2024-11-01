<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // php artisan migrate --path=database/migrations/2024_11_01_225037_add_fixed_phone_amount_to_device.php
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->string('fixed_phone_amount')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('fixed_phone_amount');

        });
    }
};
