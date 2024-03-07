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
        Schema::table('device_user', function (Blueprint $table) {
            $table->timestamp('subscription')->nullable()->after('created_at'); // or wherever you want the new column to appear
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_user', function (Blueprint $table) {
            $table->dropColumn('subscription');
        });
    }
};
