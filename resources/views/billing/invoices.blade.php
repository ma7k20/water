@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4 class="mb-0">أرشيف الفواتير</h4>
    <form class="filter-form" method="GET">
        <input type="number" name="month" min="1" max="12" class="form-control" value="{{ $month }}" >
        <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}" >
        <button class="btn btn-outline-primary">عرض</button>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th>الاسم</th>
                <th>الفترة</th>
                <th>تاريخ الدورة</th>
                <th>السابق</th>
                <th>الحالي</th>
                <th>الاستهلاك</th>
                <th>المبلغ</th>
                <th>الرصيد الجديد</th>
                <th>حالة الإرسال</th>
            </tr>
            </thead>
            <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->customer->name }}</td>
                    <td>{{ $invoice->month }}/{{ $invoice->year }}</td>
                    <td>{{ optional($invoice->billing_date)->format('Y-m-d') }}</td>
                    <td>{{ $invoice->previous_reading }}</td>
                    <td>{{ $invoice->current_reading }}</td>
                    <td>{{ $invoice->consumption }}</td>
                    <td>{{ number_format((float) $invoice->amount, 2) }}</td>
                    <td class="{{ $invoice->new_balance < 0 ? 'text-danger fw-bold' : '' }}">{{ number_format((float) $invoice->new_balance, 2) }}</td>
                    <td>
                        @if($invoice->whatsapp_status === 'sent')
                            <span class="badge text-bg-success">تم</span>
                        @elseif($invoice->whatsapp_status === 'failed')
                            <span class="badge text-bg-danger">فشل</span>
                            @if($invoice->whatsapp_error)
                                <div class="text-danger small mt-1">{{ $invoice->whatsapp_error }}</div>
                            @endif
                        @else
                            <span class="badge text-bg-secondary">معلق</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center">لا توجد فواتير لهذه الفترة.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
