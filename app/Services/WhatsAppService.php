<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMonthlyInvoicesToAdmin(int $month, int $year): array
    {
        $provider = strtolower((string) config('services.whatsapp.provider', 'webjs'));
        $adminPhone = config('services.whatsapp.admin_phone');

        if (!$adminPhone) {
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
            $response = $provider === 'webjs'
                ? $this->sendViaWebJs($adminPhone, $message)
                : $this->sendViaCloudApi($adminPhone, $message);

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
            $lines[] = 'يرجى ارسال 200 شيكل لحساب بنك فلسطين 0592116407 فادي حمد لاستمرار خدمة المياه';
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
            $lines[] = 'يرجى ارسال 200 شيكل لحساب بنك فلسطين 0592116407 فادي حمد لاستمرار خدمة الكهرباء';
        }

        return implode("\n", $lines);
    }
}
