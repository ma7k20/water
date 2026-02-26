<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly BillingService $billingService)
    {
    }

    public function monthly(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $stats = $this->billingService->monthlyStats($month, $year);
        $invoices = Invoice::with('customer')
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('customer_id')
            ->get();

        return view('reports.monthly', compact('month', 'year', 'stats', 'invoices'));
    }

    public function statement(Customer $customer): View
    {
        $invoices = $this->billingService->customerStatement($customer->id);
        return view('reports.statement', compact('customer', 'invoices'));
    }

    public function monthlyExcel(Request $request): Response
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $rows = Invoice::with('customer')->where('month', $month)->where('year', $year)->get();

        $filename = "monthly-report-{$year}-{$month}.csv";
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = static function () use ($rows) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['الاسم', 'الشهر', 'تاريخ الدورة', 'الاستهلاك', 'المبلغ', 'الرصيد الجديد', 'حالة الواتساب']);
            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->customer?->name,
                    "{$row->month}/{$row->year}",
                    optional($row->billing_date)->format('Y-m-d'),
                    $row->consumption,
                    $row->amount,
                    $row->new_balance,
                    $row->whatsapp_status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function monthlyPdf(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $stats = $this->billingService->monthlyStats($month, $year);
        $invoices = Invoice::with('customer')->where('month', $month)->where('year', $year)->get();

        return view('reports.monthly_pdf', compact('month', 'year', 'stats', 'invoices'));
    }
}
