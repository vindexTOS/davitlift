<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// php artisan migrate --path=database/migrations/2024_10_10_235813_notifications.php

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("notifications", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->boolean('isRead')->default(false);
            $table->string('message');
            $table->string('meta-data')->default(null);
            $table->string('message-type')->default(\App\Enums\NotificationType::general);
            $table->unsignedBigInteger('device_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
   Schema::dropIfExists('notifications');    }
};
