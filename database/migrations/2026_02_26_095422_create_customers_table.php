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
        Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('meter_number')->nullable();
    $table->decimal('unit_price', 8, 2);
    $table->decimal('previous_reading', 10, 2)->default(0);
    $table->decimal('previous_balance', 10, 2)->default(0);
    $table->enum('status', ['active', 'stopped'])->default('active');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
