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
        Schema::table('device_earn', function (Blueprint $table) {
            // Drop the month_year column
            $table->dropColumn('month_year');

            // Add month and year columns
            $table->integer('month')->unsigned();
            $table->integer('year')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_earn', function (Blueprint $table) {
            // Drop the month and year columns
            $table->dropColumn('month');
            $table->dropColumn('year');

            // Add back the month_year column
            $table->string('month_year');
        });
    }
};
