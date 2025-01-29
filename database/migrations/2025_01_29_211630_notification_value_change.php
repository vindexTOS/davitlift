<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
//php artisan migrate --path=database/migrations/2025_01_29_211630_notification_value_change.php
return new class extends Migration
{
     /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Check if the old column exists before renaming
            if (Schema::hasColumn('notifications', 'meta-data')) {
                $table->renameColumn('meta-data', 'meta_data');
            }

            if (Schema::hasColumn('notifications', 'message-type')) {
                $table->renameColumn('message-type', 'message_type');
            }

            // Modify the column types safely
            DB::statement("ALTER TABLE notifications MODIFY COLUMN meta_data VARCHAR(255) NULL");
            DB::statement("ALTER TABLE notifications MODIFY COLUMN message_type VARCHAR(255) NOT NULL DEFAULT 'general'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'meta_data')) {
                $table->renameColumn('meta_data', 'meta-data');
            }

            if (Schema::hasColumn('notifications', 'message_type')) {
                $table->renameColumn('message_type', 'message-type');
            }
        });
    }
};
