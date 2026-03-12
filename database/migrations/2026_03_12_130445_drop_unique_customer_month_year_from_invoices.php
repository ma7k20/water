<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function ($table) {
            $table->dropUnique('invoices_customer_month_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function ($table) {
            $table->unique(['customer_id', 'month', 'year']);
        });
    }
};