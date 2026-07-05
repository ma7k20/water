# TODO

- [x] تحديد مصدر نص رسالة الإشعارات العامة.
- [x] فحص منطق حساب الضريبة أثناء إصدار الفواتير.
- [x] التأكد أن الضريبة تُخصم ضمن `new_balance` داخل `BillingService`.
- [ ] تعديل `Dashboard` ليعرض إجمالي الضريبة وإجمالي المبالغ المخصومة وصافي المبلغ حسب الضرائب.
- [ ] تعديل `BillingService@monthlyStats` لإضافة `total_tax` و `total_discounted` و `netAmount` إن لزم.
- [ ] تحديث `resources/views/dashboard/index.blade.php` لعرض القيم الجديدة.

