@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4 class="mb-0">إدارة المشتركين</h4>
    <a class="btn btn-primary" href="{{ route('customers.create') }}">إضافة مشترك</a>
</div>

<div class="mb-3 actions-row">
    <form method="POST" action="{{ route('billing.send_low_balance') }}">
        @csrf
        <button class="btn btn-warning" onclick="return confirm('تأكيد إرسال تنبيهات انخفاض الرصيد؟')">إرسال تنبيهات انخفاض الرصيد</button>
    </form>
</div>

<div class="card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>النوع</th>
                    <th>العداد</th>
                    <th>سعر الوحدة</th>
                    <th>القراءة السابقة</th>
                    <th>تاريخ القراءة السابقة</th>
                    <th>الرصيد الحالي</th>
                    <th>الحالة</th>
                    <th>شحن رصيد</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->service_type === 'electric' ? 'كهرباء' : 'مياه' }}</td>
                    <td>{{ $customer->meter_number }}</td>
                    <td>{{ number_format((float) $customer->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $customer->previous_reading, 2) }}</td>
                    <td>{{ optional($customer->previous_reading_date)->format('Y-m-d') ?: '-' }}</td>
                    <td class="{{ $customer->previous_balance < 0 ? 'text-danger fw-bold' : '' }}">{{ number_format((float) $customer->previous_balance, 2) }}</td>
                    <td>{{ $customer->status === 'active' ? 'فعال' : 'موقوف' }}</td>
                    <td style="min-width:260px;">
                        <form method="POST" action="{{ route('customers.topup', $customer) }}" class="topup-form">
                            @csrf
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control form-control-sm" placeholder="المبلغ" required>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="ملاحظة">
                            <button class="btn btn-sm btn-success">إضافة</button>
                        </form>
                    </td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('customers.edit', $customer) }}">تعديل</a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('تأكيد حذف المشترك؟')">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center">لا يوجد مشتركين حالياً.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $customers->links() }}
</div>
@endsection
