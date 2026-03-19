@extends('layouts.app')

@section('content')
<div class="page-header mb-3">
    <h4>إرسال إشعارات</h4>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('notifications.send') }}">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">نوع المستلمين</label>
                <select class="form-select recipient-type" name="recipient_type">
                    <option value="all">جميع العملاء ({{ $customers->count() }})</option>
                    <option value="selected">عملاء محددين</option>
                </select>
            </div>
            
            <div class="selected-customers mb-3" style="display: none;">
                <label class="form-label">اختر العملاء:</label>
                <div class="customer-list mt-2" style="max-height: 300px; overflow-y: auto;">
                    @foreach($customers as $customer)
                        <div class="form-check">
                            <input class="form-check-input customer-checkbox" type="checkbox" 
                                   name="customer_ids[]" value="{{ $customer->id }}" id="customer_{{ $customer->id }}">
                            <label class="form-check-label" for="customer_{{ $customer->id }}">
                                {{ $customer->name }} - {{ $customer->phone ?? 'بدون رقم' }} ({{ $customer->meter_number }})
                            </label>
                        </div>
                    @endforeach
                </div>
                <small class="text-muted">عدد المحددين: <span id="selected-count">0</span></small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">الرسالة <span class="text-danger">*</span></label>
                <textarea class="form-control" name="message" rows="8" 
                          placeholder="اكتب رسالتك هنا... مثال: عيد مبارك! أو تم رفع سعر الكوب إلى 2 شيكل" required></textarea>
                <div class="form-text">الحد الأقصى 1000 حرف</div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <input type="hidden" name="all_customers" id="all_customers" value="0">
        <button type="submit" class="btn btn-success btn-lg" id="send-btn" disabled>
            <i class="fas fa-paper-plane"></i> إرسال الإشعارات
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">رجوع</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.querySelector('.recipient-type');
    const selectedDiv = document.querySelector('.selected-customers');
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    const countSpan = document.getElementById('selected-count');
    const allHidden = document.getElementById('all_customers');
    const sendBtn = document.getElementById('send-btn');
    const messageTextarea = document.querySelector('textarea[name="message"]');
    
    // تغيير نوع المستلمين
    typeSelect.addEventListener('change', function() {
        if (this.value === 'all') {
            selectedDiv.style.display = 'none';
            allHidden.value = '1';
            updateSendButton();
        } else {
            selectedDiv.style.display = 'block';
            allHidden.value = '0';
            updateSendButton();
        }
    });
    
    // عد المحددين
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSendButton);
    });
    
    function updateSendButton() {
        const message = messageTextarea.value.trim();
        const count = document.querySelectorAll('.customer-checkbox:checked').length;
        const isAll = typeSelect.value === 'all';
        
        if (isAll || count > 0) {
            sendBtn.disabled = !message;
        } else {
            sendBtn.disabled = true;
        }
        
        countSpan.textContent = count;
    }
    
    // التحقق من الرسالة
    messageTextarea.addEventListener('input', updateSendButton);
    
    // إرسال للكل
    document.querySelector('input[name="all_customers"]').addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = false);
    });
});
</script>
@endsection

