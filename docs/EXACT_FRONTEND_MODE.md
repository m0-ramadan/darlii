# Exact Frontend Mode

## الهدف
هذا المشروع يتعامل مع ملفات Homzen المرفوعة على أنها **Frontend ثابت ونهائي**. Laravel مسؤول عن الـBackend فقط.

## قاعدة أساسية
لا يتم إعادة تصميم أو إعادة كتابة الواجهة الأصلية.

- `storage/app/legacy-site/` يحتوي صفحات HTML الأصلية كما تم استخراجها من ملفات ZIP.
- `public/themes/` هو نفس Theme assets الأصلي.
- `public/vendor/` هو نفس Vendor frontend assets الأصلي.
- `storage/app/public/` يحتوي الصور والخطوط والوسائط الأصلية المتوفرة.

`LegacyFrontendRenderer` يقرأ HTML الأصلي ويجري فقط تعديلات Runtime غير بصرية:

1. توجيه روابط Backend القديمة من `homzen.botble.com` إلى Laravel المحلي.
2. استبدال CSRF token القديم بـLaravel CSRF token الحالي.
3. لا يغير classes أو CSS أو ترتيب DOM أو النصوص أو تصميم العناصر.

## Backend compatibility endpoints
Laravel يستقبل النماذج الأصلية بدون تغيير الـHTML:

- Newsletter subscribe
- Contact form
- Consultation / Schedule Tour
- Reviews
- Currency switch
- Login / Register
- Leads

## صفحات موجودة حرفيًا في الملفات
الـmirror يحتوي نسخًا كاملة لصفحات كثيرة، منها:

- Homepage 1–5
- Properties lists / details
- Projects lists / details
- Agent details
- Blog list / news details
- Career details
- About us
- Contact us
- FAQs
- Pricing
- Privacy / Cookie policy
- Login / Register / Password request
- Wishlist
- صفحات اللغات `en`, `ar`, `fr`, `id`, `vi`

## صفحات لم يوفرها الـmirror كملف index مستقل
بسبب طريقة حفظ الموقع، بعض directory URLs لا يوجد لها HTML index مستقل داخل الملفات المرفوعة، وأبرزها list page لبعض الأقسام مثل Agents/Careers. لا يتم اختراع تصميم بديل واعتباره أصليًا. إذا توفر HTML الأصلي لهذه الصفحات يمكن إضافته مباشرة للـlegacy-site بدون تغيير باقي النظام.

## Localized Homepage
الـmirror لا يستطيع الاحتفاظ بملف `/ar` وفي نفس الوقت بمجلد `ar/`. لذلك نستخدم snapshots الموجودة أصلًا تحت:

`{locale}/currency/switch/USD`

وهي نفس Homepage الأصلية بعد اختيار اللغة/العملة، وتحافظ على RTL والترجمة الأصلية.

## مهم
لا تستخدم Views الجديدة البسيطة كواجهة عامة إذا كان الهدف هو التطابق مع الموقع الأصلي. هي موجودة فقط كـbackend/admin fallback أثناء التطوير.
