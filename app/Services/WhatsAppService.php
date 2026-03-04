<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMonthlyInvoicesToAdmin(int $month, int $year): array
    {
        $provider = strtolower((string) config('services.whatsapp.provider', 'webjs'));
        $adminPhone = $this->normalizePhone((string) config('services.whatsapp.admin_phone'));

        if ($provider !== 'sms_gateway' && !$adminPhone) {
            throw new \RuntimeException('إعداد WHATSAPP_ADMIN_PHONE غير مكتمل في ملف البيئة.');
        }

        $invoices = Invoice::with('customer')
            ->where('month', $month)
            ->where('year', $year)
            ->where('whatsapp_status', '!=', 'sent')
            ->get();

        $sent = 0;
        $failed = 0;

        foreach ($invoices as $invoice) {
            $message = $this->formatInvoiceMessage($invoice);
            $recipient = $provider === 'sms_gateway'
                ? $this->normalizePhone((string) ($invoice->customer->phone ?? ''))
                : $adminPhone;

            if (!$recipient) {
                $invoice->update([
                    'whatsapp_status' => 'failed',
                    'whatsapp_error' => 'Missing or invalid customer phone number.',
                ]);
                $failed++;
                continue;
            }

            $response = $this->sendMessageByProvider($provider, $recipient, $message);

            if ($response->successful()) {
                $invoice->update([
                    'whatsapp_status' => 'sent',
                    'whatsapp_sent_at' => now(),
                    'whatsapp_error' => null,
                ]);
                $sent++;
            } else {
                $status = $response->status();
                $body = $response->body();
                $invoice->update([
                    'whatsapp_status' => 'failed',
                    'whatsapp_error' => "HTTP {$status}: {$body}",
                ]);
                $failed++;
            }
        }

        Log::info('WhatsApp monthly send completed', [
            'provider' => $provider,
            'month' => $month,
            'year' => $year,
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return ['sent' => $sent, 'failed' => $failed];
    }

    public function sendLowBalanceNotices(Collection $customers): array
    {
        $provider = strtolower((string) config('services.whatsapp.provider', 'webjs'));

        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($customers as $customer) {
            $rawPhone = $customer->phone ?? null;
            $phone = $this->normalizePhone($rawPhone);
            if (!$phone) {
                Log::warning('Low balance WhatsApp skipped: invalid phone', [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'raw_phone' => $rawPhone,
                ]);
                $skipped++;
                continue;
            }

            $message = $this->formatLowBalanceMessage($customer);
            $response = $this->sendMessageByProvider($provider, $phone, $message);

            if ($response->successful()) {
                $sent++;
            } else {
                Log::warning('Low balance WhatsApp failed', [
                    'customer_id' => $customer->id,
                    'name' => $customer->name,
                    'raw_phone' => $rawPhone,
                    'normalized_phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $failed++;
            }
        }

        Log::info('WhatsApp low balance send completed', [
            'provider' => $provider,
            'sent' => $sent,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        return ['sent' => $sent, 'failed' => $failed, 'skipped' => $skipped];
    }

    private function sendMessageByProvider(string $provider, string $to, string $message)
    {
        return match ($provider) {
            'webjs' => $this->sendViaWebJs($to, $message),
            'cloud' => $this->sendViaCloudApi($to, $message),
            'sms_gateway' => $this->sendViaSmsGateway($to, $message),
            default => throw new \RuntimeException("Unsupported WhatsApp provider: {$provider}"),
        };
    }

    private function sendViaWebJs(string $to, string $message)
    {
        $baseUrl = rtrim((string) config('services.whatsapp.webjs_base_url'), '/');
        $apiKey = config('services.whatsapp.webjs_api_key');

        if (!$baseUrl || !$apiKey) {
            throw new \RuntimeException('إعدادات whatsapp-web.js غير مكتملة: WHATSAPP_WEBJS_BASE_URL / WHATSAPP_WEBJS_API_KEY');
        }
        if (app()->environment('production') && preg_match('/localhost|127\.0\.0\.1/i', $baseUrl)) {
            throw new \RuntimeException('لا يمكن استخدام localhost لخدمة WhatsApp WebJS في بيئة الإنتاج. استخدم رابط خدمة عام أو غيّر المزود إلى cloud.');
        }

        try {
            return Http::timeout(20)
                ->acceptJson()
                ->withHeaders(['X-Api-Key' => $apiKey])
                ->post($baseUrl . '/api/send-text', [
                    'to' => $to,
                    'message' => $message,
                ]);
        } catch (ConnectionException $e) {
            throw new \RuntimeException("تعذر الاتصال بخدمة WhatsApp WebJS على {$baseUrl}: {$e->getMessage()}");
        }
    }

    private function sendViaCloudApi(string $to, string $message)
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.token');
        $apiVersion = config('services.whatsapp.api_version', 'v21.0');

        if (!$phoneNumberId || !$token) {
            throw new \RuntimeException('إعدادات WhatsApp Cloud API غير مكتملة.');
        }

        $url = "https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages";

        return Http::timeout(20)->withToken($token)->post($url, [
            'messaging_product' => 'whatsapp',
            'to' => preg_replace('/\D+/', '', $to),
            'type' => 'text',
            'text' => ['body' => $message],
        ]);
    }

    private function sendViaSmsGateway(string $to, string $message)
    {
        $url = (string) config('services.whatsapp.sms_gateway_url');
        $apiKey = (string) config('services.whatsapp.sms_gateway_api_key');
        $timeout = (int) config('services.whatsapp.sms_gateway_timeout', 20);

        if ($url === '') {
            throw new \RuntimeException('SMS_GATEWAY_URL is not configured.');
        }
        if (app()->environment('production') && preg_match('/localhost|127\.0\.0\.1/i', $url)) {
            throw new \RuntimeException('Cannot use localhost SMS gateway in production.');
        }

        try {
            $request = Http::timeout(max($timeout, 5))->acceptJson();
            if ($apiKey !== '') {
                $request = $request->withHeaders(['X-Api-Key' => $apiKey]);
            }

            return $request->post($url, [
                'phone' => preg_replace('/\D+/', '', $to),
                'message' => $message,
            ]);
        } catch (ConnectionException $e) {
            throw new \RuntimeException("Failed to connect to SMS gateway {$url}: {$e->getMessage()}");
        }
    }

    private function formatInvoiceMessage(Invoice $invoice): string
    {
        return ($invoice->service_type ?? 'water') === 'electric'
            ? $this->formatElectricityInvoiceMessage($invoice)
            : $this->formatWaterInvoiceMessage($invoice);
    }

    private function formatWaterInvoiceMessage(Invoice $invoice): string
    {
        $previousReadingDate = optional($invoice->previous_reading_date)->format('Y-m-d') ?: 'غير متوفر';
        $currentReadingDate = optional($invoice->billing_date)->format('Y-m-d') ?: 'غير متوفر';
        $newBalance = (float) $invoice->new_balance;

        $lines = [
            '💧 فاتورة مياه',
            'اسم المشترك: ' . $invoice->customer->name,
            'تاريخ القراءة السابقة: ' . $previousReadingDate,
            'القراءة السابقة: ' . number_format((float) $invoice->previous_reading, 2),
            'تاريخ القراءة الحالية: ' . $currentReadingDate,
            'القراءة الحالية: ' . number_format((float) $invoice->current_reading, 2),
            'كمية الاستهلاك: ' . number_format((float) $invoice->consumption, 2),
            'مبلغ الاستهلاك: ' . number_format((float) $invoice->amount, 2) . ' شيكل',
            'الرصيد المتبقي: ' . number_format($newBalance, 2) . ' شيكل',
        ];

        if ($newBalance < 50) {
            $lines[] = 'يرجى إرسال 200 شيكل لحساب بنك فلسطين 0592116407 فادي حمد لاستمرار خدمة المياه';
        }

        return implode("\n", $lines);
    }

    private function formatElectricityInvoiceMessage(Invoice $invoice): string
    {
        $newBalance = (float) $invoice->new_balance;

        $lines = [
            '⚡ فاتورة كهرباء',
            'الاسم: ' . $invoice->customer->name,
            'القراءة السابقة: ' . number_format((float) $invoice->previous_reading, 2),
            'القراءة الحالية: ' . number_format((float) $invoice->current_reading, 2),
            'الاستهلاك: ' . number_format((float) $invoice->consumption, 2),
            'مبلغ الاستهلاك: ' . number_format((float) $invoice->amount, 2) . ' شيكل',
            'المبلغ المتبقي: ' . number_format($newBalance, 2) . ' شيكل',
        ];

        if ($newBalance < 50) {
            $lines[] = 'يرجى إرسال 200 شيكل لحساب بنك فلسطين 0592116407 فادي حمد لاستمرار خدمة الكهرباء';
        }

        return implode("\n", $lines);
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '970' . substr($digits, 1);
        } elseif (strlen($digits) === 9 && str_starts_with($digits, '5')) {
            $digits = '970' . $digits;
        }

        if (strlen($digits) < 9) {
            return null;
        }

        return $digits;
    }

    private function formatLowBalanceMessage(Customer $customer): string
    {
        $service = ($customer->service_type ?? 'water') === 'electric' ? 'الكهرباء' : 'المياه';

        return "تنبيه: رصيدك أقل من 50 شيكل. يرجى تحويل المبلغ لتجنب فصل خدمة {$service}.";
    }
}
