# خطوات تشغيل مشروع Homzen Laravel

## المتطلبات
- PHP 8.3 أو أحدث
- Composer 2
- MySQL 8+
- Node.js 20+ و npm
- Extensions: `pdo_mysql mbstring openssl tokenizer xml ctype json fileinfo curl`

## تشغيل محلي
1. فك ضغط المشروع.
2. ادخل للمجلد: `cd HomzenLaravel`.
3. نفذ: `composer install`.
4. انسخ البيئة: `cp .env.example .env`.
5. أنشئ قاعدة MySQL باسم `homzen_laravel` أو أي اسم آخر.
6. عدّل `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` داخل `.env`.
7. نفذ: `php artisan key:generate`.
8. نفذ: `php artisan migrate --seed`.
9. نفذ: `php artisan storage:link`.
10. نفذ: `npm install && npm run build`.
11. نفذ: `php artisan test`.
12. شغل: `php artisan serve`.
13. افتح `http://127.0.0.1:8000` وسيتم تحويلك إلى `/en`.

## حساب التطوير
`admin@example.com` / `ChangeMe123!`
غيّره أو احذفه قبل Production.

## استيراد الداتا المستعادة مرة أخرى
`php artisan legacy:import-catalog`

## فحص الصور الناقصة
`php artisan legacy:audit-assets`

## Queue
في التطوير: `php artisan queue:work`.
في Production استخدم Supervisor أو systemd لتشغيل العامل باستمرار.

## Scheduler
Cron كل دقيقة:
`* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1`

## Production
- `APP_ENV=production`
- `APP_DEBUG=false`
- Document root يجب أن يكون `public/`
- HTTPS إجباري
- يفضل Redis للـcache والـqueue
- نفذ `php artisan optimize`
- شغل النسخ الاحتياطي والمراقبة
