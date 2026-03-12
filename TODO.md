# Fix Unique Constraint Error - Multiple Cycles per Month


## Status: Complete ✅ Migration + App safeguard implemented


### Steps:
- [x] Edit migration 2026_02_26_130000_allow_multiple_cycles_per_month_invoices.php (uncomment dropUnique)
- [ ] Git add/commit/push to trigger Render deployment & migration
- [ ] Verify production "Generate monthly" works without duplicate key error
- [ ] Test multiple invoices same month different billing_date

**Note:** Local can't migrate (no pgsql driver), fix via git push to Render.

**Current Migration up():**
```php
Schema::table('invoices', function (Blueprint $table) {
    $table->dropUnique('invoices_customer_month_year_unique');
    $table->unique(['customer_id', 'billing_date'], 'invoices_customer_billing_date_unique');
});
