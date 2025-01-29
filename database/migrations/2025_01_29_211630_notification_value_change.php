<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//php artisan migrate --path=database/migrations/2025_01_29_211630_notification_value_change.php
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('meta-data', 'meta_data');
            $table->renameColumn('message-type', 'message_type');

            // Update the column types if necessary
            $table->string('meta_data')->nullable()->change();
            $table->string('message_type')->default(\App\Enums\NotificationType::general)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('meta_data', 'meta-data');
            $table->renameColumn('message_type', 'message-type');
        });
    }
};
