@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4 class="mb-0">التقرير الشهري</h4>
    <form class="filter-form" method="GET">
        <input type="number" name="month" min="1" max="12" class="form-control" value="{{ $month }}" >
        <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}" >
        <button class="btn btn-outline-primary">عرض</button>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3">عدد الفواتير: <strong>{{ $stats['count'] }}</strong></div></div>
    <div class="col-md-3"><div class="card p-3">إجمالي الاستهلاك: <strong>{{ number_format($stats['total_consumption'], 2) }}</strong></div></div>
    <div class="col-md-3"><div class="card p-3">إجمالي المبالغ: <strong>{{ number_format($stats['total_amount'], 2) }}</strong></div></div>
    <div class="col-md-3"><div class="card p-3">الأرصدة السالبة الحالية: <strong class="text-danger">{{ number_format($stats['negative_balances_total'], 2) }}</strong></div></div>
</div>

<div class="mb-3 actions-row">
    <a class="btn btn-success" href="{{ route('reports.monthly.excel', ['month' => $month, 'year' => $year]) }}">تصدير Excel</a>
    <a class="btn btn-outline-dark" target="_blank" href="{{ route('reports.monthly.pdf', ['month' => $month, 'year' => $year]) }}">تصدير PDF</a>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead>
            <tr>
                <th>المشترك</th>
                <th>الاستهلاك</th>
                <th>المبلغ</th>
                <th>الرصيد الحالي</th>
                <th>كشف الحساب</th>
            </tr>
            </thead>
            <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->customer?->name }}</td>
                    <td>{{ $invoice->consumption }}</td>
                    <td>{{ number_format((float) $invoice->amount, 2) }}</td>
                    <td class="{{ (float) ($invoice->customer?->previous_balance ?? 0) < 0 ? 'text-danger' : '' }}">{{ number_format((float) ($invoice->customer?->previous_balance ?? 0), 2) }}</td>
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('reports.statement', $invoice->customer_id) }}">عرض</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
