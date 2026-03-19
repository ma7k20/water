<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::post('/customers/{customer}/topup', [CustomerController::class, 'topup'])->name('customers.topup');

    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/generate', [BillingController::class, 'generateMonthly'])->name('billing.generate');
    Route::post('/billing/send-whatsapp', [BillingController::class, 'sendWhatsApp'])->name('billing.send_whatsapp');
    Route::post('/billing/send-low-balance', [BillingController::class, 'sendLowBalanceWhatsApp'])->name('billing.send_low_balance');
    Route::post('/billing/close-month', [BillingController::class, 'closeMonth'])->name('billing.close_month');
Route::get('/invoices', [BillingController::class, 'invoices'])->name('billing.invoices');
    Route::delete('/invoices/{invoice}', [BillingController::class, 'deleteInvoice'])->name('billing.invoice.delete');

    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/monthly/excel', [ReportController::class, 'monthlyExcel'])->name('reports.monthly.excel');
    Route::get('/reports/monthly/pdf', [ReportController::class, 'monthlyPdf'])->name('reports.monthly.pdf');
    Route::get('/reports/statement/{customer}', [ReportController::class, 'statement'])->name('reports.statement');
    
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');
});
