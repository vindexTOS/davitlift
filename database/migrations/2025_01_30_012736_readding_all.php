<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//php artisan migrate --path=database/migrations/2025_01_30_012736_readding_all.php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Notifications Table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->boolean('isRead')->default(false);
                $table->string('message');
                $table->string('meta_data')->nullable();
                $table->string('message_type')->default('general');
                $table->unsignedBigInteger('device_id')->nullable();
                $table->timestamps();
            });
        }

        // TBC Transactions Table
        if (!Schema::hasTable('tbctransactions')) {
            Schema::create('tbctransactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->integer('amount');
                $table->string('order_id');
                $table->string('FileId');
                $table->string('type');
                $table->timestamps();
            });
        }

        // Phone Numbers Table
        if (!Schema::hasTable('phonenumbers')) {
            Schema::create('phonenumbers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('number');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
