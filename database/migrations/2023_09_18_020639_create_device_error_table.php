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
        Schema::create('device_errors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('device_id'); // Foreign key for the device table
            $table->string('errorCode');
            $table->text('errorText');
            $table->timestamps();

            // Set up the foreign key constraint
            $table->foreign('device_id')
                ->references('id')
                ->on('devices')
                ->onDelete('cascade'); // This means if a device is deleted, its associated errors will also be deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_error');
    }
};
