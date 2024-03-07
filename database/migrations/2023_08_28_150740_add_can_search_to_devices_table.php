<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('can_search')->default(true); // This will create a boolean column with default value as false.
        });
    }


    public function down()
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('can_search');
        });
    }

};
