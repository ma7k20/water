<?php

namespace App\Http\Controllers;

use App\Models\BalanceTransaction;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::orderByDesc('id')->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $data = $this->validatedData($request);
            Customer::create($data);

            return redirect()->route('customers.index')->with('success', 'ÊãÊ ÅÖÇÝÉ ÇáãÔÊÑß ÈäÌÇÍ.');
        } catch (Throwable $e) {
            Log::error('Customer store failed', ['message' => $e->getMessage()]);

            return back()->withErrors(['general' => 'ÍÏË ÎØÃ ÃËäÇÁ ÍÝÙ ÇáãÔÊÑß.'])->withInput();
        }
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        try {
            $data = $this->validatedData($request, $customer->id);
            $customer->update($data);

            return redirect()->route('customers.index')->with('success', 'Êã ÊÚÏíá ÈíÇäÇÊ ÇáãÔÊÑß.');
        } catch (Throwable $e) {
            Log::error('Customer update failed', [
                'customer_id' => $customer->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'ÍÏË ÎØÃ ÃËäÇÁ ÊÚÏíá ÈíÇäÇÊ ÇáãÔÊÑß.'])->withInput();
        }
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', 'Êã ÍÐÝ ÇáãÔÊÑß.');
    }

    public function topup(Request $request, Customer $customer): RedirectResponse
    {
        try {
            $data = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'note' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($customer, $data, $request) {
                $customer->update([
                    'previous_balance' => (float) $customer->previous_balance + (float) $data['amount'],
                ]);

                BalanceTransaction::create([
                    'customer_id' => $customer->id,
                    'amount' => $data['amount'],
                    'note' => $data['note'] ?? 'ÔÍä ÑÕíÏ íÏæí',
                    'created_by' => $request->user()?->id,
                ]);
            });

            Log::info('Customer balance topped up', [
                'customer_id' => $customer->id,
                'amount' => $data['amount'],
            ]);

            return back()->with('success', 'ÊãÊ ÅÖÇÝÉ ÇáÑÕíÏ ÈäÌÇÍ.');
        } catch (Throwable $e) {
            Log::error('Customer topup failed', [
                'customer_id' => $customer->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withErrors(['general' => 'ÍÏË ÎØÃ ÃËäÇÁ ÊÚÈÆÉ ÇáÑÕíÏ.'])->withInput();
        }
    }

    private function validatedData(Request $request, ?int $customerId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'meter_number' => 'required|string|max:100|unique:customers,meter_number,' . $customerId,
            'phone' => 'nullable|string|max:30',
            'service_type' => 'required|in:water,electric',
            'unit_price' => 'required|numeric|min:0',
            'previous_reading' => 'required|numeric|min:0',
            'previous_reading_date' => 'nullable|date',
            'previous_balance' => 'required|numeric',
            'status' => 'required|in:active,stopped',
        ]);
    }
}
