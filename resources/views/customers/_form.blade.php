@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">اسم المشترك</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">رقم العداد</label>
        <input type="text" name="meter_number" class="form-control" value="{{ old('meter_number', $customer->meter_number ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">الهاتف</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">نوع الفاتورة</label>
        <select name="service_type" class="form-select" required>
            <option value="water" @selected(old('service_type', $customer->service_type ?? 'water') === 'water')>مياه</option>
            <option value="electric" @selected(old('service_type', $customer->service_type ?? 'water') === 'electric')>كهرباء</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">سعر الوحدة</label>
        <input type="number" step="0.01" min="0" name="unit_price" class="form-control" value="{{ old('unit_price', $customer->unit_price ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">القراءة السابقة</label>
        <input type="number" step="0.01" min="0" name="previous_reading" class="form-control" value="{{ old('previous_reading', $customer->previous_reading ?? 0) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">تاريخ القراءة السابقة</label>
        <input type="date" name="previous_reading_date" class="form-control" value="{{ old('previous_reading_date', isset($customer->previous_reading_date) ? optional($customer->previous_reading_date)->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">الرصيد السابق</label>
        <input type="number" step="0.01" name="previous_balance" class="form-control" value="{{ old('previous_balance', $customer->previous_balance ?? 0) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">الحالة</label>
        <select name="status" class="form-select" required>
            <option value="active" @selected(old('status', $customer->status ?? 'active') === 'active')>فعال</option>
            <option value="stopped" @selected(old('status', $customer->status ?? 'active') === 'stopped')>موقوف</option>
        </select>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">حفظ</button>
    <a class="btn btn-outline-secondary" href="{{ route('customers.index') }}">رجوع</a>
</div>
