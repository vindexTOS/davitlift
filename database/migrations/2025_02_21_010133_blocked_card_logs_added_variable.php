<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//php artisan migrate --path=database/migrations/2025_02_21_010133_blocked_card_logs_added_variable.php

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('blockedcardlogs', function (Blueprint $table) {
            $table->string('meta_data')->default("") ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blockedcardlogs', function (Blueprint $table) {
            $table->dropColumn('meta_data');
        });
    }
};
