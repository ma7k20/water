<?php

namespace App\Http\Controllers;

use App\Models\BillingCycle;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class BillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly WhatsAppService $whatsAppService
    ) {
    }

    public function index(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $billingDate = $request->input('billing_date') ?: now()->toDateString();

        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        $existingInvoiceCustomerIds = Invoice::whereDate('billing_date', $billingDate)->pluck('customer_id')->all();
        $cycle = BillingCycle::where('month', $month)->where('year', $year)->first();

        return view('billing.index', compact('customers', 'month', 'year', 'billingDate', 'existingInvoiceCustomerIds', 'cycle'));
    }

    public function generateMonthly(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'billing_date' => 'required|date',
                'readings' => 'required|array',
                'readings.*' => 'nullable|numeric|min:0',
            ]);

            $summary = $this->billingService->issueInvoicesByDate(
                $validated['readings'],
                $validated['billing_date']
            );

            return back()->with('success', "Êã ÅÕÏÇÑ {$summary['issued']} ÝÇÊæÑÉ ÈÊÇÑíÎ {$validated['billing_date']}¡ æÊÎØí {$summary['skipped']} ãÔÊÑß ÈÏæä ÞÑÇÁÉ.");
        } catch (Throwable $e) {
            Log::error('Generate monthly failed', ['message' => $e->getMessage()]);

            return back()->withErrors(['general' => 'ÍÏË ÎØÃ ÃËäÇÁ ÅÕÏÇÑ ÇáÝæÇÊíÑ.'])->withInput();
        }
    }

    public function invoices(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);

        $invoices = Invoice::with('customer')
            ->where('month', $month)
            ->where('year', $year)
            ->orderByDesc('billing_date')
            ->orderByDesc('id')
            ->get();

        return view('billing.invoices', compact('invoices', 'month', 'year'));
    }

    public function sendWhatsApp(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
            ]);

            $result = $this->whatsAppService->sendMonthlyInvoicesToAdmin(
                (int) $validated['month'],
                (int) $validated['year']
            );

            return back()->with('success', "Êã ÅÑÓÇá {$result['sent']} ÝÇÊæÑÉ¡ æÝÔá {$result['failed']}.");
        } catch (Throwable $e) {
            Log::error('Send WhatsApp failed', ['message' => $e->getMessage()]);

            return back()->withErrors(['general' => 'ÍÏË ÎØÃ ÃËäÇÁ ÅÑÓÇá ÇáÝæÇÊíÑ ÚÈÑ æÇÊÓÇÈ.'])->withInput();
        }
    }

    public function closeMonth(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2000|max:2100',
            ]);

            $this->billingService->closeMonth((int) $validated['month'], (int) $validated['year']);

            return back()->with('success', 'Êã ÅÛáÇÞ ÇáÔåÑ ÈäÌÇÍ.');
        } catch (Throwable $e) {
            Log::error('Close month failed', ['message' => $e->getMessage()]);

            return back()->withErrors(['general' => 'ÍÏË ÎØÃ ÃËäÇÁ ÅÛáÇÞ ÇáÔåÑ.'])->withInput();
        }
    }
}
