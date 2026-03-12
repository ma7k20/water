# خطة إضافة حذف فاتورة + إعادة قراءة

**Information Gathered:**
- Invoice model: fillable OK, is_locked boolean
- invoices.blade.php: table with invoices, no delete
- BillingController: no delete method
- index.blade.php: generate form

**Plan:**
1. **BillingController**
   - `deleteInvoice($id)`: if !is_locked, delete + revert customer previous_reading/balance
2. **routes/web.php** 
   - Route::delete('/billing/invoices/{id}', [BillingController::class, 'deleteInvoice'])->name('billing.delete');
3. **invoices.blade.php**
   - Add delete button per row if !is_locked
4. **BillingService**
   - `deleteInvoice($invoice)`: logic revert
5. Update TODO.md

**Follow-up:** `php artisan migrate` on Render, test delete → regenerate reading.

موافق؟

