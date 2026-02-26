<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('period_key', 7)->nullable()->after('year');
            $table->enum('whatsapp_status', ['pending', 'sent', 'failed'])->default('pending')->after('new_balance');
            $table->timestamp('whatsapp_sent_at')->nullable()->after('whatsapp_status');
            $table->text('whatsapp_error')->nullable()->after('whatsapp_sent_at');
            $table->boolean('is_locked')->default(false)->after('whatsapp_error');

            $table->unique(['customer_id', 'month', 'year'], 'invoices_customer_month_year_unique');
            $table->index('period_key');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_customer_month_year_unique');
            $table->dropIndex(['period_key']);
            $table->dropColumn([
                'period_key',
                'whatsapp_status',
                'whatsapp_sent_at',
                'whatsapp_error',
                'is_locked',
            ]);
        });
    }
};
