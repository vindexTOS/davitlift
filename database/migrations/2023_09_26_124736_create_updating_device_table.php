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
        Schema::create('updating_device', function (Blueprint $table) {
            $table->id();
            $table->string('dev_id'); // for a string
            $table->unsignedBigInteger('device_id');
            $table->string('previous_version')->nullable();
            $table->string('new_version')->nullable();
            $table->string('status');
            $table->boolean('is_checked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('updating_device');
    }
};
