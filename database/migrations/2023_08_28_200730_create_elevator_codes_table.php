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
        Schema::create('elevator_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');  // Stores the one-time code
            $table->string('user_id');
            $table->string('device_id');
            $table->timestamp('expires_at');  // Stores the expiration timestamp
            // Add any other necessary columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elevator_codes');
    }
};
