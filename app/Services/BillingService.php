<?php

namespace App\Services;

use App\Models\BillingCycle;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public function issueInvoicesByDate(array $readings, string $billingDate): array
    {
        $date = Carbon::parse($billingDate);
        $month = (int) $date->month;
        $year = (int) $date->year;

        $cycle = BillingCycle::firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['status' => 'open']
        );

        if ($cycle->status === 'closed') {
            throw ValidationException::withMessages([
                'cycle' => 'هذا الشهر مغلق ولا يمكن إصدار فواتير جديدة.',
            ]);
        }

        $customers = Customer::where('status', 'active')->get()->keyBy('id');
        $periodKey = sprintf('%04d-%02d', $year, $month);

        $summary = DB::transaction(function () use ($readings, $customers, $periodKey, $cycle, $date, $month, $year) {
            $issued = 0;
            $skipped = 0;

            foreach ($customers as $customer) {
                if (!array_key_exists($customer->id, $readings) || $readings[$customer->id] === null || $readings[$customer->id] === '') {
                    $skipped++;
                    continue;
                }

                $currentReading = (float) $readings[$customer->id];
                if ($currentReading < (float) $customer->previous_reading) {
                    throw ValidationException::withMessages([
                        "readings.{$customer->id}" => "القراءة الحالية للمشترك {$customer->name} أقل من القراءة السابقة.",
                    ]);
                }

                $existing = Invoice::where('customer_id', $customer->id)
                    ->whereDate('billing_date', $date->toDateString())
                    ->exists();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'duplicate' => "تم إصدار فاتورة للمشترك {$customer->name} بنفس تاريخ الدورة.",
                    ]);
                }

                $consumption = $currentReading - (float) $customer->previous_reading;
                $amount = $consumption * (float) $customer->unit_price;
                $newBalance = (float) $customer->previous_balance - $amount;

                Invoice::create([
                    'customer_id' => $customer->id,
                    'service_type' => $customer->service_type ?? 'water',
                    'month' => $month,
                    'year' => $year,
                    'period_key' => $periodKey,
                    'billing_date' => $date->toDateString(),
                    'previous_reading_date' => $customer->previous_reading_date,
                    'previous_reading' => $customer->previous_reading,
                    'current_reading' => $currentReading,
                    'consumption' => $consumption,
                    'unit_price' => $customer->unit_price,
                    'amount' => $amount,
                    'previous_balance' => $customer->previous_balance,
                    'new_balance' => $newBalance,
                    'whatsapp_status' => 'pending',
                    'is_locked' => false,
                ]);

                $customer->update([
                    'previous_reading' => $currentReading,
                    'previous_reading_date' => $date->toDateString(),
                    'previous_balance' => $newBalance,
                ]);

                $issued++;
            }

            $cycle->update([
                'status' => $issued > 0 ? 'issued' : $cycle->status,
                'issued_at' => $issued > 0 ? now() : $cycle->issued_at,
            ]);

            return [
                'issued' => $issued,
                'skipped' => $skipped,
            ];
        });

        Log::info('Invoices issued', [
            'billing_date' => $date->toDateString(),
            'issued' => $summary['issued'],
            'skipped' => $summary['skipped'],
        ]);

        return $summary;
    }

    public function closeMonth(int $month, int $year): void
    {
        $cycle = BillingCycle::firstOrCreate(
            ['month' => $month, 'year' => $year],
            ['status' => 'open']
        );

        $cycle->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        Invoice::where('month', $month)
            ->where('year', $year)
            ->update(['is_locked' => true]);

        Log::info('Billing cycle closed', ['month' => $month, 'year' => $year]);
    }

    public function monthlyStats(int $month, int $year): array
    {
        $invoices = Invoice::where('month', $month)->where('year', $year);

        return [
            'count' => $invoices->count(),
            'total_consumption' => (float) $invoices->sum('consumption'),
            'total_amount' => (float) $invoices->sum('amount'),
            'negative_balances_total' => (float) $invoices->where('new_balance', '<', 0)->sum(DB::raw('ABS(new_balance)')),
            'negative_accounts_count' => (int) Invoice::where('month', $month)->where('year', $year)->where('new_balance', '<', 0)->count(),
        ];
    }

    public function customerStatement(int $customerId): Collection
    {
        return Invoice::with('customer')
            ->where('customer_id', $customerId)
            ->orderBy('billing_date')
            ->orderBy('id')
            ->get();
    }
}
