<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly BillingService $billingService)
    {
    }

    public function index(Request $request): View
    {
        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $stats = $this->billingService->monthlyStats($month, $year);

        return view('dashboard.index', [
            'month' => $month,
            'year' => $year,
            'customersCount' => Customer::count(),
            'stats' => $stats,
        ]);
    }
}
