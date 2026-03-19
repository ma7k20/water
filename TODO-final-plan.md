# خطة تنفيذ ميزة إرسال الإشعارات العامة في لوحة التحكم

## الخطوات:
- [x] 1. إنشاء NotificationController.php
- [x] 2. تعديل routes/web.php
- [x] 3. إنشاء resources/views/notifications/index.blade.php
- [x] 4. تعديل resources/views/dashboard/index.blade.php
- [x] 5. إضافة طريقة sendGeneralNotifications() في WhatsAppService.php (مشابهة لطريقة إرسال الفواتير)
- [x] 6. اختبار النموذج والإرسال ✅
- [x] 7. تحديث هذا الملف بالإنجازات ✅

**ملاحظة**: طريقة الإرسال ستكون مشابهة لإرسال الفواتير (استخدام نفس الـ provider مع رسالة مخصصة + تسجيل sent/failed).
