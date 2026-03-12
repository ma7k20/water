# خطة نهائية - إضافة حذف فاتورة + إعادة قراءة (بالعربي)

**فهمت المتطلب:**
حذف فاتورة → revert previous_reading + balance → إمكانية إدخال قراءة جديدة وإصدار فاتورة أخرى.

**الخطوات:**
1. **BillingController.php**
   - دالة `deleteInvoice($id)`: 
     - تحقق !is_locked
     - احذف invoice
     - revert customer: previous_reading = invoice.previous_reading, previous_balance = invoice.previous_balance
     - success message
2. **routes/web.php**
   ```
   Route::delete('/billing/invoices/{invoice}', [BillingController::class, 'deleteInvoice'])->name('billing.invoice.delete');
   ```
3. **invoices.blade.php**
   - عمود actions مع زر:
     ```
     @if(!$invoice->is_locked)
     <form method="POST" action="{{ route('billing.invoice.delete', $invoice) }}" onsubmit="return confirm('تأكيد حذف؟ سيتم إعادة القراءة السابقة')">
     @csrf @method('DELETE')
     <button class="btn btn-sm btn-danger">حذف وإعادة قراءة</button>
     </form>
     @endif
     ```
4. **BillingService.php** (اختياري): `deleteInvoice(Invoice $invoice)`

**تابع:** commit/push → Render migrate (if needed) → test.

**موافق نبدأ؟**

