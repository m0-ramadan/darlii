# تشغيل HomzenLaravel باستخدام Docker

## لماذا Docker؟
المشروع يحتاج PHP 8.3+، ويمكن تشغيله داخل Docker حتى لو كان PHP على Kali هو 8.2.

## 1. التأكد من Docker
```bash
docker --version
docker compose version
```

## 2. الدخول إلى المشروع
```bash
cd "~/Videos/dashboard/بسم الله/HomzenLaravel"
```

## 3. اختياري: تمرير UID/GID لتجنب مشاكل الصلاحيات
```bash
export HOST_UID=$(id -u)
export HOST_GID=$(id -g)
```

## 4. بناء وتشغيل الـ containers
```bash
docker compose up -d --build
```

عند أول تشغيل، app container سيقوم بعمل `composer install` باستخدام PHP 8.3 وسيولد `APP_KEY` إذا لم يكن موجودًا.

تابع اللوج عند الحاجة:
```bash
docker compose logs -f app
```

## 5. تأكد من إصدار PHP داخل Docker
```bash
docker compose exec app php -v
```
يجب أن يظهر PHP 8.3.x.

## 6. إنشاء قاعدة البيانات والجداول والبيانات التجريبية/المستعادة
```bash
docker compose exec app php artisan migrate --seed
```

إذا كنت تريد حذف كل الجداول وإعادة بنائها أثناء التطوير فقط:
```bash
docker compose exec app php artisan migrate:fresh --seed
```

## 7. إنشاء storage link
```bash
docker compose exec app php artisan storage:link
```

## 8. تنظيف وتحسين Laravel
للتطوير:
```bash
docker compose exec app php artisan optimize:clear
```

لا تستخدم `php artisan optimize` أثناء التطوير إلا عند الحاجة؛ يفضل استخدامه في production.

## 9. تشغيل الاختبارات
```bash
docker compose exec app php artisan test
```

## 10. فتح الموقع
- Laravel: http://localhost:8000
- Vite: http://localhost:5173
- MySQL من الجهاز: 127.0.0.1:3307
- Redis من الجهاز: 127.0.0.1:6380

داخل Docker يجب استخدام اسم الخدمة `db` كـ DB_HOST وليس `127.0.0.1`.

## 11. أوامر مفيدة
```bash
# عرض الحالة
docker compose ps

# Logs
docker compose logs -f app
docker compose logs -f db

# Laravel shell
docker compose exec app php artisan tinker

# تشغيل queue worker يدويًا
docker compose exec app php artisan queue:work --tries=3

# استيراد الكاتالوج القديم
docker compose exec app php artisan legacy:import-catalog

# فحص الـ assets
docker compose exec app php artisan legacy:audit-assets

# Composer داخل PHP 8.3 container
docker compose exec app composer install

# إيقاف الخدمات
docker compose down

# إيقاف وحذف قاعدة بيانات Docker بالكامل - تحذير: يحذف البيانات
docker compose down -v
```

## مشاكل شائعة
### vendor/autoload.php غير موجود
نفّذ:
```bash
docker compose exec app composer install
```

### Connection refused إلى MySQL
تأكد أن `.env` أو container environment يستخدم:
```env
DB_HOST=db
DB_PORT=3306
```
داخل Docker، لا تستخدم localhost للوصول إلى MySQL container.

### Permission denied
```bash
sudo chown -R $(id -u):$(id -g) storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
```

### إعادة بناء PHP image
```bash
docker compose build --no-cache app
docker compose up -d
```
