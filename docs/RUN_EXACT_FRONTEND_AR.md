# تشغيل النسخة المطابقة للواجهة الأصلية

## لو أنت تستبدل النسخة الحالية
لا تمسح MySQL volume إذا عندك بيانات مهمة.

```bash
docker compose down
```

انسخ محتويات النسخة الجديدة مكان المشروع الحالي ثم:

```bash
export HOST_UID=$(id -u)
export HOST_GID=$(id -g)
docker compose up -d --build
```

الـApp يستخدم Port 8001 افتراضيًا ويمكن تغييره:

```bash
APP_PORT=8010 docker compose up -d
```

## تجهيز Laravel

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

إذا `public/storage` موجود مسبقًا:

```bash
docker compose exec app php artisan storage:unlink || true
docker compose exec app php artisan storage:link
```

## افتح الواجهة الأصلية

```text
http://localhost:8001/en
http://localhost:8001/ar
http://localhost:8001/fr
http://localhost:8001/id
http://localhost:8001/vi
```

العقارات:

```text
http://localhost:8001/en/properties
```

مثال Property detail:

```text
http://localhost:8001/en/properties/private-estate-magnificent-views
```

Projects:

```text
http://localhost:8001/en/projects
```

## التحقق من أن الواجهة الأصلية موجودة

```bash
docker compose exec app php artisan test --filter=ExactFrontendTest
```

## الـBackend
الـHTML الأصلي يحتفظ بنفس الـforms والـJS. وقت إخراج الصفحة Laravel يغير فقط:

- الـCSRF token القديم.
- روابط الـbackend القديمة `homzen.botble.com` إلى التطبيق المحلي.

لا يتم تعديل CSS classes أو Layout أو ملفات Theme.

## فحص ملفات الواجهة
ملف:

```text
docs/EXACT_FRONTEND_SHA256.txt
```

يحتوي SHA256 للنسخ الأصلية المضمنة في المشروع.
