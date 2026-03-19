<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly WhatsAppService $whatsAppService
    ) {
    }

    public function index(Request $request): View|JsonResponse
    {
        $query = $request->get('q', '');
        $customers = Customer::when($query, function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('meter_number', 'like', "%{$query}%");
        })
        ->limit(100)
        ->get();

        if ($request->ajax()) {
            return response()->json($customers);
        }

        return view('notifications.index', compact('customers'));
    }

    public function send(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        $customerIds = array_filter($request->input('customer_ids', []));
        $message = $request->input('message');
        
        // إذا كان "الكل"، احصل على كل العملاء
        if ($request->has('all_customers') && $request->input('all_customers') == '1') {
            $customerIds = Customer::pluck('id')->toArray();
        }

        if (empty($customerIds)) {
            return back()->with('error', 'يرجى اختيار عملاء واحد على الأقل.');
        }

        $result = $this->whatsAppService->sendGeneralNotifications($customerIds, $message);

        $successMsg = "تم إرسال الإشعارات: " . 
                     "{$result['sent']} ناجح، " . 
                     "{$result['failed']} فاشل، " .
                     "{$result['skipped']} بدون رقم";

        return back()->with('success', $successMsg);
    }
}

