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
        Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
    $table->integer('month');
    $table->integer('year');
    $table->decimal('previous_reading', 10, 2);
    $table->decimal('current_reading', 10, 2);
    $table->decimal('consumption', 10, 2);
    $table->decimal('unit_price', 8, 2);
    $table->decimal('amount', 10, 2);
    $table->decimal('previous_balance', 10, 2);
    $table->decimal('new_balance', 10, 2);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
