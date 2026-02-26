<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'billing_date')) {
                $table->date('billing_date')->nullable()->after('period_key');
            }
        });

        DB::statement("UPDATE invoices SET billing_date = STR_TO_DATE(CONCAT(year, '-', month, '-01'), '%Y-%m-%d') WHERE billing_date IS NULL");

        Schema::table('invoices', function (Blueprint $table) {
            //$table->dropUnique('invoices_customer_month_year_unique');
            $table->unique(['customer_id', 'billing_date'], 'invoices_customer_billing_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_customer_billing_date_unique');
            $table->unique(['customer_id', 'month', 'year'], 'invoices_customer_month_year_unique');
            $table->dropColumn('billing_date');
        });
    }
};
