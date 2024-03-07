<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('startup')->nullable();
            $table->unsignedTinyInteger('relay_pulse_time')->nullable();
            $table->unsignedTinyInteger('lcd_brightness')->nullable();
            $table->unsignedTinyInteger('led_brightness')->nullable();
            $table->unsignedTinyInteger('msg_appear_time')->nullable();
            $table->unsignedTinyInteger('card_read_delay')->nullable();
            $table->unsignedTinyInteger('tariff_amount')->nullable();
            $table->tinyInteger('timezone')->nullable();
            $table->boolean('storage_disable')->default(false);
            $table->boolean('relay1_node')->default(false);
            $table->string('dev_name', 7)->nullable();
            $table->string('op_mode')->nullable();
            $table->string('dev_id', 7)->nullable()->index();
            $table->string('guest_msg_L1', 16)->nullable();
            $table->string('guest_msg_L2', 16)->nullable();
            $table->string('guest_msg_L3', 16)->nullable();
            $table->string('validity_msg_L1', 16)->nullable();
            $table->string('validity_msg_L2', 16)->nullable();
            $table->string('name')->nullable();
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->string('soft_version')->nullable();
            $table->string('hardware_version')->nullable();
            $table->string('url')->nullable();;
            $table->string('crc32')->nullable();;
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
