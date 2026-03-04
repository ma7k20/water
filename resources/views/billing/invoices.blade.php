@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4 class="mb-0">أرشيف الفواتير</h4>
    <form class="filter-form" method="GET">
        <input type="number" name="month" min="1" max="12" class="form-control" value="{{ $month }}">
        <input type="number" name="year" min="2000" max="2100" class="form-control" value="{{ $year }}">
        <button class="btn btn-outline-primary">عرض</button>
    </form>
</div>

<div class="card p-3">
    <div class="mb-3 d-flex flex-wrap gap-2">
        <form method="POST" action="{{ route('billing.send_whatsapp') }}">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <button type="submit" class="btn btn-success">إرسال فواتير الشهر (الكل)</button>
        </form>

        <button type="button" id="send-selected" class="btn btn-outline-success">إرسال المحددين فقط</button>
    </div>

    <form id="send-selected-form" method="POST" action="{{ route('billing.send_whatsapp') }}" class="d-none">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
    </form>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
            <tr>
                <th style="width:50px;"><input type="checkbox" id="check-all"></th>
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
                    <td><input type="checkbox" class="customer-check" value="{{ $invoice->customer_id }}"></td>
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
                <tr><td colspan="10" class="text-center">لا توجد فواتير لهذه الفترة.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('check-all');
    const checks = Array.from(document.querySelectorAll('.customer-check'));
    const sendSelectedBtn = document.getElementById('send-selected');
    const selectedForm = document.getElementById('send-selected-form');

    function syncSendSelectedState() {
        sendSelectedBtn.disabled = !checks.some((item) => item.checked);
    }

    checkAll?.addEventListener('change', function () {
        checks.forEach((item) => {
            item.checked = checkAll.checked;
        });
        syncSendSelectedState();
    });

    checks.forEach((item) => {
        item.addEventListener('change', syncSendSelectedState);
    });

    sendSelectedBtn?.addEventListener('click', function () {
        selectedForm.querySelectorAll('input[name="customer_ids[]"]').forEach((node) => node.remove());

        checks.forEach((item) => {
            if (!item.checked) return;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'customer_ids[]';
            hidden.value = item.value;
            selectedForm.appendChild(hidden);
        });

        if (selectedForm.querySelectorAll('input[name="customer_ids[]"]').length === 0) {
            return;
        }

        selectedForm.submit();
    });

    syncSendSelectedState();
});
</script>
@endpush
