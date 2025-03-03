<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//php artisan migrate --path=database/migrations/2025_03_03_143933_device_earn_company_id_manager_id.php

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('device_earn', function (Blueprint $table) {
           
            $table->integer('manager_id')->nullable()->default(null) ;
          });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
