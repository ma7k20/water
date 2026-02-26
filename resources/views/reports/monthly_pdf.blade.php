<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تقرير شهري {{ $month }}/{{ $year }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: right; }
    </style>
</head>
<body onload="window.print()">
    <h3>تقرير الفواتير الشهري - {{ $month }}/{{ $year }}</h3>
    <p>عدد الفواتير: {{ $stats['count'] }} | إجمالي الاستهلاك: {{ number_format($stats['total_consumption'], 2) }} | إجمالي المبالغ: {{ number_format($stats['total_amount'], 2) }}</p>
    <table>
        <thead>
        <tr>
            <th>المشترك</th>
            <th>الاستهلاك</th>
            <th>المبلغ</th>
            <th>الرصيد الجديد</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->customer?->name }}</td>
                <td>{{ $invoice->consumption }}</td>
                <td>{{ number_format((float) $invoice->amount, 2) }}</td>
                <td>{{ number_format((float) $invoice->new_balance, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
