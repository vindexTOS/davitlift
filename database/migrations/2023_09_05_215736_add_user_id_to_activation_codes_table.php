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
        Schema::table('activation_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');

            // If you want to create a foreign key constraint:
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activation_codes', function (Blueprint $table) {
            // If you added a foreign key constraint, you should drop it first:
            $table->dropForeign(['user_id']);

            $table->dropColumn('user_id');
        });
    }
};
