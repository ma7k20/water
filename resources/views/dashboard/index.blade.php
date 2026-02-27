@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4 class="mb-0">لوحة التحكم</h4>
    <form class="filter-form" method="GET">
        <input type="number" name="month" class="form-control" min="1" max="12" value="{{ $month }}" >
        <input type="number" name="year" class="form-control" min="2000" max="2100" value="{{ $year }}" >
        <button class="btn btn-outline-primary">تحديث</button>
    </form>
</div>

<div class="row g-3">
    <div class="col-md-4"><div class="card p-3"><div>عدد المشتركين</div><div class="stat-number">{{ $customersCount }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div>فواتير الشهر</div><div class="stat-number">{{ $stats['count'] }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div>إجمالي الاستهلاك</div><div class="stat-number">{{ number_format($stats['total_consumption'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div>إجمالي المبالغ المخصومة</div><div class="stat-number">{{ number_format($stats['total_amount'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div>إجمالي الأرصدة السالبة</div><div class="stat-number text-danger">{{ number_format($stats['negative_balances_total'], 2) }}</div></div></div>
    <div class="col-md-4"><div class="card p-3"><div>الحسابات السالبة</div><div class="stat-number text-danger">{{ $stats['negative_accounts_count'] }}</div></div></div>
</div>

<div class="mt-4 actions-row">
    <a class="btn btn-primary" href="{{ route('billing.index', ['month' => $month, 'year' => $year]) }}">إدخال القراءات</a>
    <a class="btn btn-outline-dark" href="{{ route('reports.monthly', ['month' => $month, 'year' => $year]) }}">عرض التقرير الشهري</a>
</div>
@endsection
