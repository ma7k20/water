@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4 class="mb-0">إدخال قراءات سريعة (يمكن أكثر من مرة بالشهر)</h4>
    <form class="filter-form" method="GET">
        <input type="number" name="month" min="1" max="12" class="form-control" value="{{ $month }}" >
        <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}" >
        <input type="date" name="billing_date" class="form-control" value="{{ $billingDate }}">
        <button class="btn btn-outline-primary">عرض</button>
    </form>
</div>

<div class="mb-3 actions-row">
    <span class="badge text-bg-secondary">الشهر: {{ $month }}/{{ $year }}</span>
    <span class="badge text-bg-info">تاريخ الدورة: {{ $billingDate }}</span>
    @if($cycle && $cycle->status === 'closed')
        <span class="badge text-bg-danger">هذا الشهر مغلق</span>
    @elseif($cycle && $cycle->status === 'issued')
        <span class="badge text-bg-success">تم إصدار سابقاً داخل هذا الشهر</span>
    @else
        <span class="badge text-bg-warning">الشهر مفتوح</span>
    @endif
</div>

<div class="card p-3 mb-3">
    <form method="POST" action="{{ route('billing.generate') }}">
        @csrf
        <input type="hidden" name="billing_date" value="{{ $billingDate }}">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>المشترك</th>
                        <th>العداد</th>
                        <th>القراءة السابقة</th>
                        <th>الرصيد السابق</th>
                        <th>القراءة الحالية</th>
                        <th>ضريبة</th>
                        <th>الاستهلاك المباشر</th>
                        <th>الرصيد الجديد المباشر</th>
                        <th>حالة هذا التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->meter_number }}</td>
                            <td>{{ $customer->previous_reading }}</td>
                            <td>{{ number_format((float) $customer->previous_balance, 2) }}</td>
                            <td>
                                <input type="number"
                                       step="0.01"
                                       min="{{ $customer->previous_reading }}"
                                       class="form-control current-reading"
                                       name="readings[{{ $customer->id }}]"
                                       data-unit="{{ $customer->unit_price }}"
                                       data-prev="{{ $customer->previous_reading }}"
                                       data-balance="{{ $customer->previous_balance }}"
                                       @disabled(in_array($customer->id, $existingInvoiceCustomerIds) || ($cycle && $cycle->status === 'closed'))>
                            </td>
                            <td>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       class="form-control tax-input"
                                       name="taxes[{{ $customer->id }}]"
                                       value="0"
                                       data-balance="{{ $customer->previous_balance }}"
                                       @disabled(in_array($customer->id, $existingInvoiceCustomerIds) || ($cycle && $cycle->status === 'closed'))>
                            </td>
                            <td class="live-consumption">0.00</td>
                            <td class="live-balance">{{ number_format((float) $customer->previous_balance, 2) }}</td>
                            <td>
                                @if(in_array($customer->id, $existingInvoiceCustomerIds))
                                    <span class="badge text-bg-success">صدر</span>
                                @else
                                    <span class="badge text-bg-secondary">غير صادر</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button class="btn btn-primary" @disabled($cycle && $cycle->status === 'closed')>إصدار فواتير هذا التاريخ</button>
    </form>
</div>

<div class="actions-row">
    <form method="POST" action="{{ route('billing.send_whatsapp') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <button class="btn btn-success">إرسال جميع فواتير الشهر عبر واتساب</button>
    </form>

    <form method="POST" action="{{ route('billing.close_month') }}">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <button class="btn btn-outline-danger" onclick="return confirm('تأكيد إغلاق الشهر؟ بعد الإغلاق لا يمكن إصدار فواتير جديدة.')">إغلاق الشهر</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateRow(row) {
    const readingInput = row.querySelector('.current-reading');
    const taxInput = row.querySelector('.tax-input');
    const current = parseFloat((readingInput && readingInput.value) || 0);
    const prev = parseFloat((readingInput && readingInput.dataset.prev) || 0);
    const unit = parseFloat((readingInput && readingInput.dataset.unit) || 0);
    const prevBalance = parseFloat((readingInput && readingInput.dataset.balance) || 0);
    const tax = parseFloat((taxInput && taxInput.value) || 0);
    const consumption = Math.max(current - prev, 0);
    const amount = consumption * unit;
    const newBalance = prevBalance - amount - tax;

    row.querySelector('.live-consumption').textContent = consumption.toFixed(2);
    const balanceCell = row.querySelector('.live-balance');
    balanceCell.textContent = newBalance.toFixed(2);
    balanceCell.classList.toggle('text-danger', newBalance < 0);
}

document.querySelectorAll('.current-reading, .tax-input').forEach(function (input) {
    input.addEventListener('input', function () {
        const row = input.closest('tr');
        if (row) {
            updateRow(row);
        }
    });
});
</script>
@endpush
