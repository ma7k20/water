@extends('layouts.app')

@section('content')
<h4 class="mb-3">كشف حساب المشترك: {{ $customer->name }}</h4>
<div class="alert alert-info py-2">
    الرصيد الحالي: <strong>{{ number_format((float) $customer->previous_balance, 2) }}</strong>
</div>
<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>الفترة</th>
                    <th>تاريخ الدورة</th>
                    <th>القراءة السابقة</th>
                    <th>القراءة الحالية</th>
                    <th>الاستهلاك</th>
                    <th>المبلغ</th>
                    <th>الرصيد الجديد</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td>{{ $invoice->month }}/{{ $invoice->year }}</td>
                        <td>{{ optional($invoice->billing_date)->format('Y-m-d') }}</td>
                        <td>{{ $invoice->previous_reading }}</td>
                        <td>{{ $invoice->current_reading }}</td>
                        <td>{{ $invoice->consumption }}</td>
                        <td>{{ number_format((float) $invoice->amount, 2) }}</td>
                        <td class="{{ $invoice->new_balance < 0 ? 'text-danger fw-bold' : '' }}">{{ number_format((float) $invoice->new_balance, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">لا يوجد بيانات حتى الآن.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
