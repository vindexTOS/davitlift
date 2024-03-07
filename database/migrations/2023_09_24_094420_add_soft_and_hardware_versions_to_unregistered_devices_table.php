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
        Schema::table('unregistered_devices', function (Blueprint $table) {
            $table->string('soft_version')->nullable();
            $table->string('hardware_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unregistered_devices', function (Blueprint $table) {
            $table->dropColumn('soft_version');
            $table->dropColumn('hardware_version');
        });
    }
};
