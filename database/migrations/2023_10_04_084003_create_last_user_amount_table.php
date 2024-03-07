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
        Schema::create('last_user_amount', function (Blueprint $table) {
            $table->id();  // This will create an auto-incrementing ID
            $table->unsignedBigInteger('user_id');
            $table->string('device_id'); // Assuming device_id is a string
            $table->integer('last_amount');
            $table->timestamps();  // Created at & Updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('last_user_amount');
    }
};
