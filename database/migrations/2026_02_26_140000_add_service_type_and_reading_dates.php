<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('service_type', ['water', 'electric'])->default('water')->after('phone');
            $table->date('previous_reading_date')->nullable()->after('previous_reading');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('service_type', ['water', 'electric'])->default('water')->after('customer_id');
            $table->date('previous_reading_date')->nullable()->after('billing_date');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'previous_reading_date']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['service_type', 'previous_reading_date']);
        });
    }
};
