<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('manager_id')->nullable(); // Assuming you want it after company_id
            $table->tinyInteger('type')->comment('1 = cashback, 2 = service fee');
            $table->timestamp('transaction_date');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            // Foreign Key Constraint
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_transactions', function (Blueprint $table) {
            Schema::dropIfExists('company_transactions');

        });
    }
};
