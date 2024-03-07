<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('device_earn', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->decimal('earnings', 8, 2)->nullable(); // Adjust precision and scale as needed
            $table->string('month_year');
            $table->timestamps();

            $table->foreign('device_id')->references('id')->on('devices')->onDelete('cascade'); // Assuming you have a devices table.
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_earns');
    }
};
