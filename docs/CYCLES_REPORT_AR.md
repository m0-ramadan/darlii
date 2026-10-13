# تقرير السايكلز (الدورات التشغيلية) — منصة دارلي / Homzen Laravel Rebuild

> **المشروع:** `real_state` (دارلي / Darlii) — Laravel 13 + قالب Homzen الأصلي
> **تاريخ التقرير:** 2026-10-12
> **نطاق التقرير:** حصر كل السايكلز (Business Cycles) الموجودة فعلياً في الموقع + حالة التنفيذ الحقيقية لكل واحدة.

---

## 0. طريقة العمل ومصادر الحقيقة

"السايكلز" مصطلح رسمي داخل المشروع نفسه، ومش مسمّى من عندي. المصدران هما:

| المصدر | المحتوى |
|---|---|
| `docs/ARCHITECTURE.md` (سطر 5) | *"Cycles: Identity → Agent/Agency → Property → Moderation → Publishing → Search → Lead → Appointment → Review. Project inventory is a separate cycle but properties may link to projects. Billing uses immutable credit-ledger records rather than a naked balance field."* |
| `docs/ORIGINAL_AUDIT_BLUEPRINT.md` (§11 "Business cycles, separated") | يعرّف 12 دورة مرقّمة A → L بخطواتها التفصيلية |

**اتحاد المصدرين = 14 سايكل مستقل**، وده اللي التقرير بيغطيه بالكامل.

كل حكم في التقرير ده مبني على فحص الكود الفعلي (routes / controllers / models / migrations / enums / services / tests)، مش على نقل من التوثيق.

### بيئة التشغيل (متحقَّق منها)
- الحاويات شغّالة: `homzen_app` (PHP 8.3.35)، `homzen_db` (MySQL 8.4)، `homzen_redis`، `homzen_node`.
- **مهم:** PHP على المضيف 8.2.27 بينما المشروع يتطلب `>= 8.3` — لذلك تشغيل `php artisan` لازم يكون **جوه حاوية `homzen_app`**، وإلا هيطلع `platform_check` fatal error.

---

## 1. الملخص التنفيذي

- الموقع مبني كـ **modular monolith**، وفيه **14 سايكل** معرّفين رسمياً.
- **سايكل واحد فقط مكتمل ومغطّى باختبارات فعلاً: المفضلة (Wishlist).**
- **الكتلة الأساسية للتجارة (نشر العقار → المراجعة → النشر) منفّذة كويس** وهي أنضج جزء في المشروع.
- **سايكل الدفع/الرصيد (Credits & Payment) = صفر تنفيذ.** الجداول والـ migration موجودة، لكن مفيش ولا سطر كود بيستخدم `Order` أو `CreditLedger`. ودي أخطر فجوة لأن نشر العقار المفروض بيستهلك رصيد.
- **مفيش طبقة إشعارات ولا Jobs ولا Mailers خالص** في المشروع — وده بيعطّل خطوات أساسية في سايكل الاستفسارات (Lead) والمواعيد (Appointment) والتقييمات.
- **نتيجة تشغيل الاختبارات الحقيقية** (جوه `homzen_app`):

```
Tests:  1 failed, 25 passed (203 assertions) — 7.61s
```

- الاختبار الفاشل: `PreferencePersistenceTest::test_locale_and_currency_are_saved_independently`
  السبب: متوقع `EUR` من الكوكي لكن الناتج `EGP` (`tests/Feature/PreferencePersistenceTest.php:21`) → تضارب بين الكوكي والعملة الافتراضية في سايكل العملات.
- **9 سايكلز من 14 عندهم صفر اختبارات** (Identity, Agent/Agency, Moderation, Project, Lead, Appointment, Review, Billing, Content, Mortgage).

---

## 2. الجدول الرئيسي — كل السايكلز وحالتها

**دلالة الرموز:** 🟢 منفّذ ويعمل · 🟡 منفّذ جزئياً · 🔴 هيكل/جداول فقط بدون تنفيذ

| # | السايكل | المرجع | الحالة | الاختبارات |
|---|---|---|---|---|
| 1 | Identity — الهوية والمصادقة | ARCH, B | 🟡 | 0 |
| 2 | Agent / Agency — الوكلاء والوكالات | ARCH, B | 🟡 | 0 |
| 3 | Property Publishing + Moderation — نشر العقار والمراجعة | ARCH, C | 🟢 | 2 |
| 4 | Project Publishing — نشر المشروعات | ARCH, D | 🟡 | 0 |
| 5 | Discovery / Search / Map — البحث والاستكشاف والخريطة | A | 🟡 | 3 |
| 6 | Lead / Consultation — الاستفسارات (CRM) | E | 🟡 | 0 |
| 7 | Appointment — المواعيد | ARCH | 🟡 | 0 |
| 8 | Review — التقييمات | F | 🟡 | 0 |
| 9 | Wishlist — المفضلة | G | 🟢 | 2 ✅ |
| 10 | Credits / Payment — الرصيد والدفع | H, ARCH | 🔴 | 0 |
| 11 | Content / CMS — نشر المحتوى | I | 🟡 | 0 |
| 12 | Localization — التوطين | J | 🟡 | ~9 |
| 13 | Currency — العملات | K | 🟡 | 1 ❌ |
| 14 | Mortgage Calculator — حاسبة التمويل | L | 🔴 | 0 |

**الإجمالي:** 🟢 2 · 🟡 9 · 🔴 3

---

## 3. تفصيل كل سايكل

### 🟡 1. Identity — الهوية والمصادقة
**الخطوات المرجعية:** تسجيل → تفعيل إيميل/موبايل → دخول → استرجاع كلمة السر.

**المنفّذ:**
- `app/Http/Controllers/AuthController.php` — 8 دوال: `loginForm, login, registerForm, register, logout, forgotForm, forgot, resetForm, reset`.
- `routes/web.php:79-88` — مجموعة `guest` كلها بـ `throttle:5,1`، وصفحات `password/request`, `reset-password/{token}`.
- تفعيل الإيميل: `verification.notice`, `email/verify/{id}/{hash}`, `email/verification-notification` (`routes/web.php:109-118`).
- جداول: `2026_08_27_000001_create_identity_tables.php` + `2026_08_27_000008_add_preferences_to_users_table.php`.

**الناقص:**
- 🔴 **تسجيل الدخول الاجتماعي وهمي**: `routes/web.php:88` — أي provider (facebook/google/github/linkedin) مجرد `redirect()->route('login')`. مفيش Socialite ولا callback handling.
- 🔴 تفعيل الموبايل (OTP/SMS) غير موجود.
- 🔴 صفر اختبارات.

---

### 🟡 2. Agent / Agency — الوكلاء والوكالات
**الخطوات المرجعية:** تسجيل → إكمال بروفايل الوكيل → موافقة إدارية/KYC → وصول للوحة.

**المنفّذ:**
- Models: `AgentProfile`, `AgentProfileTranslation`, `Agency` — وفيه حقل `verified_at` في `AgentProfile`.
- عام: `/agents` و `/agents/{slug}` (`AgentController`).
- إدارة: `routes/admin.php:75-95` — CRUD للمستخدمين + `hierarchy` + `toggleStatus` + `resetPassword` + `assignManager`، و RBAC كامل (roles + `syncPermissions`).
- `app/Support/PermissionCatalog.php` لتصنيف الصلاحيات.

**الناقص:**
- 🔴 مفيش **لوحة وكالة** (Agency dashboard) ولا دعوات أعضاء.
- 🔴 مفيش **سايكل KYC/موافقة إدارية** للوكيل — `verified_at` موجود كحقل بس مفيش أي كود بيكتب فيه.
- 🔴 الوكيل مش قادر يعدّل بروفايله بنفسه.
- 🔴 صفر اختبارات.

---

### 🟢 3. Property Publishing + Moderation — نشر العقار والمراجعة
**الخطوات المرجعية:** مسودة → رفع ميديا → تسعير/مواصفات → تصنيف/مشروع/مرافق → التحقق من الرصيد → إرسال للمراجعة → موافقة/رفض → نشر → انتهاء/تجديد → أرشفة.

**المنفّذ (أنضج جزء في المشروع):**
- `app/Modules/Property/Domain/Enums/PropertyStatus.php` — 8 حالات: `draft, pending_review, published, rejected, sold, rented, expired, archived`.
- الـ Actions (`app/Modules/Property/Application/Actions/`):
  - `CreatePropertyAction` — داخل transaction، بيولّد `reference_code` (`PROP-YYYY-XXXXXXXX`) و `uuid`، وبيعمل translations لكل لغة.
  - `SubmitPropertyAction` — `draft → pending_review`.
  - `ApprovePropertyAction` — `→ published` + `published_at` + تسجيل في `property_status_histories`.
- لوحة الوكيل: `routes/web.php:124-128` — `dashboard/properties` index/create/store + `submit`.
- الإدارة: `routes/admin.php:42-61` — `pending`, `approve`, `reject`, `feature`, `assign`, `bulk`, وإدارة الميديا (`media`, `media/{media}/primary`, حذف).
- الرفض (`Admin/PropertyController::reject:224`) بيسجّل السبب في `admin_note` + status history + `AuditLogger`.
- سجل الحالة: `PropertyStatusHistory` + دالة `logStatus`.

**الناقص:**
- 🔴 **مفيش تحقق من الرصيد قبل الإرسال للمراجعة** — `Dashboard/PropertyController::submit` بينفّذ `SubmitPropertyAction` مباشرة بدون أي credit check. وده اللي بيفصل السايكل رقم 3 عن رقم 10.
- 🔴 **مفيش scheduler للانتهاء/التجديد التلقائي**. `expires_at` بيتفلتر في القراءة (`FavoriteController`, `PropertyMapController`, `HomepageBlockRenderer`) لكن **مفيش job بيحوّل العقار لـ `expired` أو `archived`**. `routes/console.php` فيه بس `queue:prune-batches` و `queue:prune-failed`.
- 🟡 عدم تناسق معماري: `Approve` في Action class لكن `Reject` مكتوب inline في الـ controller — بيخالف `docs/ARCHITECTURE.md` اللي بيقول القواعد في `Application/Actions`.
- 🔴 مفيش محرر متعدد الخطوات للميديا/المواصفات.

---

### 🟡 4. Project Publishing — نشر المشروعات
**الخطوات المرجعية:** إنشاء مشروع → محتوى مترجم → مستثمر/تواريخ/بلوكات/وحدات/أدوار → تصنيفات ومرافق ومخططات → ربط بعقارات → مراجعة → نشر.

**المنفّذ:**
- Models: `Project`, `ProjectTranslation`, `ProjectUnit`.
- عام: `/projects`, `/projects/{slug}`, `/ajax/projects/search`، و API `v1/projects` + `v1/projects/{id}`.
- إدارة: `routes/admin.php:65-71` — CRUD كامل (index/create/store/edit/update/destroy).

**الناقص:**
- 🔴 **مفيش سايكل مراجعة للمشروعات** — مفيش `pending` ولا `approve`/`reject` للمشروع، بعكس العقارات.
- 🔴 مفيش واجهة للوحدات (`ProjectUnit`) ولا البلوكات/الأدوار/المخططات ولا بيانات المستثمر.
- 🔴 صفر اختبارات.

---

### 🟡 5. Discovery / Search / Map — البحث والاستكشاف والخريطة
**الخطوات المرجعية:** تصفح → فلاتر → خريطة/قائمة → تفاصيل → مفضلة/مشاركة → تواصل/تقييم.

**المنفّذ:**
- `app/Modules/Search/Application/Queries/PropertySearchQuery.php` — فلترة على: `q` (name/description في translations)، `purpose`, `category_id`, `location_id`, `min_price`, `max_price`, `bedrooms`، وترتيب `newest / price_asc / price_desc / popular`. دايماً `status = published`.
- الخريطة: `PropertyMapController` — `map`, `cities`, `governorates`, و `route` (بروكسي Google Routes بدون كشف المفتاح).
- API v1 (`routes/api.php`) بـ `throttle:60,1`: `properties`, `properties/{id}`, `properties/map`, `projects`, `projects/{id}`.
- خرائط مصر: `config/egypt_map.php` + `app/Support/BeniSuefCatalog.php` + seeders.

**الناقص:**
- 🔴 فلاتر ناقصة: مرافق (amenities)، حمامات، مساحة، مشروع، featured، وأي فلترة بعملة.
- 🔴 مفيش **Scout/Meilisearch** — البحث لسه `LIKE %..%` على جدول الترجمات (بيروح للـ full scan).
- 🔴 مفيش بحث محفوظ (saved searches) ولا تنبيهات عقارية ولا مقارنة.
- 🟡 الـ bounding-box موجود في `v1/properties/map` و `/ajax/properties/map` لكن بدون تجميع (clustering) ولا فلترة مرافق على الخريطة.
- ✅ مغطّى بـ 3 اختبارات (Map).

---

### 🟡 6. Lead / Consultation — الاستفسارات (CRM)
**الخطوات المرجعية:** إرسال استفسار → تحقق/rate-limit → تخزين → إشعار المعلن/الإدارة → تعيين → متابعة → ملاحظات/تاريخ → إغلاق (won/lost).

**المنفّذ:**
- `app/Modules/Lead/Domain/Enums/LeadStatus.php` — 8 حالات: `new, assigned, contacted, qualified, follow_up, won, lost, spam`.
- `LeadController::store` — `StoreLeadRequest` + `reference_code` (`LEAD-YYYY-XXXXXXXX`) + `status=new` + `ip_hash` (sha256 لـ IP + app key) + activity `created` + `throttle:10,1`.
- مسار قديم متوافق: `LegacyCompatibilityController::consult` بيعمل Lead + Activity، ولو فيه تاريخ بيولّد **Appointment** كمان.
- الإدارة: `routes/admin.php:154-160` — index/show/update/assign/activity/destroy.

**الناقص:**
- 🔴 **مفيش إشعار للمعلن ولا للإدارة** — لا Notifications ولا Mail ولا Jobs موجودين في المشروع أصلاً. وده بيقطع خطوة أساسية من السايكل.
- 🔴 مفيش تعيين تلقائي ولا قواعد توجيه (routing rules).
- 🔴 واجهة الـ timeline بسيطة (activities بس، بدون SLA/reminders).
- 🔴 صفر اختبارات.

---

### 🟡 7. Appointment — المواعيد
**الخطوات المرجعية:** طلب موعد → تأكيد → إعادة جدولة → إتمام → إلغاء / عدم حضور.

**المنفّذ:**
- `app/Modules/Appointment/Domain/Enums/AppointmentStatus.php` — 6 حالات: `requested, confirmed, rescheduled, completed, cancelled, no_show`.
- الإنشاء: **فقط** من فورم الاستشارة القديم (`LegacyCompatibilityController` سطر ~69)، بياخد التاريخ من `consult_custom_fields.1`.
- الإدارة: `routes/admin.php:163-166` — index/update/destroy.
- `Appointment` مستخدم كذلك في `AdminStatsService` و `Property`.

**الناقص:**
- 🔴 **مفيش مسار حجز عام** للمستخدم النهائي (اختيار slot/تاريخ متاح).
- 🔴 **مفيش تقويم UI** ولا تذكيرات ولا إشعارات.
- 🔴 مفيش سجل لتغيّر حالة الموعد (مفيش status history زي العقار).
- 🔴 صفر اختبارات.

---

### 🟡 8. Review — التقييمات
**الخطوات المرجعية:** مستخدم مسجّل → تقييم/محتوى → تحقق → انتظار مراجعة → موافقة/رفض → تحديث المتوسط.

**المنفّذ:**
- `ReviewController::store` — تحقق `rating` بين 1 و 5، `title`, `comment`، و `updateOrCreate` على (user, reviewable) بحيث المستخدم ميعملش تقييمين لنفس العقار، والحالة `pending`، بـ `throttle:5,1`.
- الإدارة (`Admin/ReviewController`) — index بفلاتر status/rating + `approve` (بيضبط `approved_at`) + `reject` (بيصفّر `approved_at`) + delete، وكلها مع `AuditLogger::log`.
- مسار قديم متوافق: `POST /ajax/review/{slug}` بـ `throttle:5,1`.
- Model `Review` مع `reviewable_type` / `reviewable_id` (polymorphic).

**الناقص:**
- 🔴 **مفيش تحديث للـ aggregate rating** بعد الموافقة — مفيش أي كود بيحدّث متوسط تقييم العقار/المشروع (المطلوب في Blueprint §11-F الخطوة الأخيرة).
- 🔴 مفيش enum لحالة التقييم (strings مباشرة) بعكس Property/Lead/Appointment.
- 🔴 صفر اختبارات (رغم إن Blueprint §14 بيطلب review authorization/moderation).

---

### 🟢 9. Wishlist — المفضلة  *(السايكل الأنضج)*
**الخطوات المرجعية:** الزائر يستخدم session/local → المستخدم المسجّل يحفظ → دمج مفضلة الزائر بعد الدخول.

**المنفّذ:**
- `FavoriteController` — `state`, `store`, `destroy`, **`merge`** (دمج مفضلة الزائر بعد تسجيل الدخول)، `userFavoriteIds`, `validatedGuestIds`, `stateResponse`, `propertyData`.
- routes: `favorites.state` (عام)، و `favorites.store/destroy/merge` (محمي بـ `auth`).
- بيحترم العملة المختارة (`Currency::where('code', session('currency'))`) وترجمة العربية.
- ✅ **2 اختبارات ناجحة**: `test_authenticated_favorites_are_idempotent_mergeable_and_removable`, `test_guest_state_filters_invalid_properties_and_uses_arabic_translation` — وبيفحصوا الـ idempotency والدمج وفلترة العقارات غير الصالحة.

**الناقص:**
- 🟡 صفحة `/wishlist` نفسها لسه بتترندر من الملفات القديمة (`LegacyFrontendController::page`) مش من view حديث.

---

### 🔴 10. Credits / Payment — الرصيد والدفع  *(أخطر فجوة)*
**الخطوات المرجعية:** اختيار باقة → إنشاء order → بوابة دفع → تحقق webhook بشكل idempotent → تسجيل payment → قيد في credit ledger → فاتورة → النشر يستهلك رصيد داخل transaction.

**المنفّذ:**
- الجداول فقط: `database/migrations/2026_08_27_000006_create_billing_system_tables.php` (Order / Payment / CreditLedger).
- Models: `Order.php`, `Payment.php`, `CreditLedger.php` — موجودين كتعريفات بس.
- فيه تبويب إعدادات `admin/settings/integrations.blade.php` (واجهة بدون كود دفع خلفها).

**الناقص — كل حاجة:**
- 🔴 `Order` **صفر استخدام** في المشروع كله (مفيش أي controller/model بيستدعيه).
- 🔴 `CreditLedger` مذكور في ملفه هو و`docs/FILE_MANIFEST.tsv` فقط — **مفيش أي منطق رصيد**.
- 🔴 مفيش **أي بوابة دفع** (لا Stripe ولا PayPal ولا أي gateway) ولا webhooks.
- 🔴 مفيش فواتير، مفيش باقات/subscriptions، مفيش واجهة شراء.
- 🔴 **مفيش استهلاك رصيد عند النشر** (مفيش credit check في `SubmitPropertyAction`).
- 🔴 مفيش حماية concurrency لاستهلاك الرصيد (المطلوبة في Blueprint §14).

> ده معناه إن نموذج الربح في الموقع **غير موجود برمجياً** حالياً، رغم إن الـ schema جاهز.

---

### 🟡 11. Content / CMS — نشر المحتوى
**الخطوات المرجعية:** إنشاء page/post/career → ترجمة → SEO/slug → مسودة/مراجعة/نشر → sitemap/RSS/إبطال الكاش.

**المنفّذ:**
- Models: `Page`/`PageTranslation`, `Post`/`PostTranslation`, `Career`/`CareerTranslation`, `Testimonial`/`TestimonialTranslation`.
- إدارة: `routes/admin.php:99-123` — CRUD كامل للصفحات والمقالات وآراء العملاء.
- عام: `/blog`, `/blog/{slug}`, `/careers`, `/careers/{slug}`, `/news/{slug}`، و `/feed/{properties|projects|posts}`.
- `LegacyFrontendController::page` لتوافق الروابط القديمة + `LocalizedUrlResolver` للـ slugs المترجمة.
- إعدادات SEO موجودة في `SettingsSeeder` وتبويب `admin/settings/seo`.

**الناقص:**
- 🔴 **مفيش sitemap.xml** — مفيش route ولا command بيولّد sitemap.
- 🔴 مفيش **page builder** ولا إدارة قوائم (menus) ولا إعلانات (announcements UI).
- 🔴 مفيش **إبطال كاش** (cache invalidation) عند النشر.
- 🔴 مفيش سايكل مراجعة/موافقة للمحتوى (draft/review/publish).
- 🔴 صفر اختبارات.

---

### 🟡 12. Localization — التوطين
**الخطوات المرجعية:** تحديد/اختيار اللغة → تحميل صف الترجمة → RTL للعربية → الحفاظ على اللغة في الفورمات والإيميلات → hreflang/canonical.

**المنفّذ:**
- `config/locales.php` — `supported = ['ar','en']`، `default = ar`، `rtl = ['ar']`.
- التوجيه: `Route::prefix('{locale}')->whereIn('locale', config('locales.supported'))` مع middleware `set.locale` و `set.currency` (`routes/web.php:49-51`).
- `LocaleController` + `/language/switch/{locale}` + `LocalizedUrlResolver` + `VisitorPreferences`.
- `routes/admin.php` لا يعتمد على locale (لوحة الإدارة منفصلة).
- ✅ أكبر تغطية اختبارات في المشروع (~9 اختبارات): `LocalizationTest`, `HomepageBehaviorTest` (canonical + hreflang كامل), `PreferencePersistenceTest`, و `HomepageBlocksTest` (slug عربي).

**الناقص:**
- 🔴 **تناقض لغات حقيقي:** فولدرات `lang/` فيها **5 لغات** (`ar, en, fr, id, vi` بنفس الملفات الثلاثة: auth/messages/passwords)، لكن `config/locales.php` بتدعم **2 بس**. يعني fr/id/vi موجودين كملفات ومش موصولين بالتوجيه. رغم إن `docs/PROJECT_STATUS.md` بيدّعي "Five-language routing".
- 🔴 اختبار `test_locale_and_currency_are_saved_independently` **فاشل** حالياً.
- 🔴 مفيش hreflang/sitemap للصفحات الداخلية غير الصفحة الرئيسية.

---

### 🟡 13. Currency — العملات
**الخطوات المرجعية:** اختيار العملة → حفظ التفضيل → تحويل السعر للعرض → **عدم تغيير القيمة الأصلية المخزّنة**.

**المنفّذ:**
- `Currency` model: `code, name, symbol, exchange_rate, decimal_places, symbol_position, is_default, is_active`.
- `/currency/switch/{code}` — بيحفظ في `session('currency')` + `users.preferred_currency` (بـ `saveQuietly`) + كوكي `homzen_currency`.
- middleware `set.currency` على كل مسارات الـ locale.
- `exchange_rate` مستخدم فعلياً في العرض: `FavoriteController`, `PropertyMapController`, `Services/LegacyFrontend/HomepageBlockRenderer`.
- ✅ المبدأ الصحيح مطبّق: السعر الأصلي مخزّن في `properties.price` + `currency_id`، والتحويل بيحصل في العرض فقط — **مفيش overwrite للقيمة الأصلية**.

**الناقص:**
- 🔴 **اختبار فاشل**: `test_locale_and_currency_are_saved_independently` — متوقع `EUR` جه `EGP`. يعني فيه تعارض بين كوكي `homzen_currency` والعملة الافتراضية في `VisitorPreferences`/middleware.
- 🟡 التحويل متكرر في أكتر من مكان بدل service موحّد (`CurrencyConverter`) — خطر divergence.
- 🔴 مفيش تحديث لأسعار الصرف ولا caching ليها.

---

### 🔴 14. Mortgage Calculator — حاسبة التمويل
**الخطوات المرجعية:** خدمة حساب client-side بإعدادات مشتركة، بدون كتابة في قاعدة البيانات.

**المنفّذ:**
- **الويدجت القديم فقط** داخل `app/Services/LegacyFrontend/LegacyFrontendRenderer.php` (سطر ~1077 و ~1154): استبدال رمز `$` برمز العملة الحالية، وإعادة تلوين الويدجت للون البراند الذهبي.

**الناقص:**
- 🔴 مفيش **خدمة حساب مشتركة** ولا إعدادات مركزية (rate/term/down payment).
- 🔴 الحساب كله جوه JS القديم المنسوخ — غير قابل للاختبار ولا لإعادة الاستخدام في المشروعات/الوحدات.
- 🔴 مفيش أي اختبار.

---

## 4. تغطية الاختبارات التفصيلية

**نتيجة التشغيل الفعلي جوه `homzen_app` (PHP 8.3.35):** `1 failed, 25 passed (203 assertions)` في 7.61 ثانية.

| السايكل | عدد الاختبارات | الحالة |
|---|---|---|
| Localization | ~9 | ✅ (ما عدا اختبار العملة) |
| واجهات/ثيم (Frontend/Dark mode) | ~8 | ✅ |
| Discovery / Map | 3 | ✅ |
| Property Publishing | 2 | ✅ |
| Wishlist | 2 | ✅ |
| Currency | 1 | ❌ فاشل |
| Identity, Agent/Agency, Property Moderation, Project, Lead, Appointment, Review, Billing, Content, Mortgage | **0** | 🔴 |

**الاختبار الفاشل الوحيد:**
```
Tests\Feature\PreferencePersistenceTest > locale and currency are saved independently
Failed asserting that two strings are equal.
-'EUR'  +'EGP'
at tests/Feature/PreferencePersistenceTest.php:21
```
السبب: `tests/Feature/PreferencePersistenceTest.php:17` بيعمل `withCookie('homzen_currency','EUR')` ثم بيتوقع `assertSessionHas('currency','EUR')` لكن الجلسة بتحتوي `EGP` — يعني middleware/`VisitorPreferences` بتغلب الكوكي على العملة الافتراضية.

---

## 5. الفجوات مرتبة بالأولوية

### 🔴 حرجة (بتوقف نموذج العمل)
1. **سايكل الدفع/الرصيد غير منفّذ بالكامل** (سايكل 10) + **مفيش استهلاك رصيد عند نشر العقار** (سايكل 3). الكتابة في `property_status_histories` بتشتغل لكن الفلوس لأ.
2. **مفيش طبقة إشعارات/Jobs/Mailers** — بيقطع سايكل الـ Lead (إشعار المعلن) والمواعيد (تذكيرات) والتقييمات.
3. **مفيش scheduler للانتهاء/التجديد** — `expires_at` موجود وبيتفلتر بس مفيش job بيقفل العقارات المنتهية.

### 🟠 مهمة
4. اختبار العملة الفاشل + توحيد التحويل في service واحد.
5. تضارب اللغات: fr/id/vi موجودين في `lang/` ومش مفعّلين في `config/locales.php` (ومخالفة لتوثيق `PROJECT_STATUS.md`).
6. سايكل مراجعة للمشروعات غير موجود (العقارات عندها moderation والمشروعات لأ).
7. فلاتر البحث الناقصة + غياب محرك بحث حقيقي (Scout).
8. تحديث aggregate rating بعد الموافقة على التقييم.

### 🟡 تحسينات
9. توحيد النمط المعماري: نقل `reject` من الـ controller لـ `RejectPropertyAction` (زي `ApprovePropertyAction`).
10. sitemap.xml + إبطال الكاش عند نشر المحتوى.
11. مسار حجز مواعيد عام + تقويم.
12. تسجيل دخول اجتماعي حقيقي (Socialite) بدل الـ redirect الوهمي.
13. KYC/موافقة إدارية للوكلاء + لوحة وكالة.
14. حاسبة تمويل كخدمة مشتركة قابلة للاختبار.

---

## 6. خطة عمل مقترحة (بالترتيب)

| المرحلة | الشغل | السايكلز المتأثرة |
|---|---|---|
| **1** | إصلاح اختبار العملة + توحيد `CurrencyConverter` + حسم موضوع اللغات (fr/id/vi) | 12, 13 |
| **2** | طبقة إشعارات (Notifications + Mail + Queue) وربطها بالـ Lead والمواعيد | 6, 7 |
| **3** | سايكل الرصيد: باقات → Order → بوابة دفع → webhook idempotent → CreditLedger → استهلاك الرصيد داخل transaction عند النشر | 10, 3 |
| **4** | Scheduler: انتهاء/أرشفة/تجديد تلقائي للعقار | 3 |
| **5** | مراجعة المشروعات + الوحدات + مخططات الأدوار | 4 |
| **6** | توسيع فلاتر البحث + محرك بحث + بحث محفوظ وتنبيهات | 5 |
| **7** | aggregate rating + تأكيد سياسات التقييم | 8 |
| **8** | sitemap + إبطال كاش + قوائم/إعلانات | 11 |
| **9** | KYC ولوحة الوكالة + Socialite | 1, 2 |
| **10** | حجز مواعيد عام + تقويم + تذكيرات | 7 |

**معيار الجاهزية للإنتاج (من Blueprint §13-14):** مفيش سايكل تتحوّل للإنتاج قبل ما يكون عندها feature tests — حالياً **9 سايكلز كاملة من 14 عندهم صفر تغطية**، وكذلك **وحدة المراجعة (Moderation)** جوه سايكل العقار مالهاش أي اختبار (فيه اختبار لإنشاء المسودة بس).

---

## 7. الملفات المرجعية المهمة

| الملف | الدور |
|---|---|
| `docs/ARCHITECTURE.md` | تعريف السايكلز والحدود المعمارية |
| `docs/ORIGINAL_AUDIT_BLUEPRINT.md` | §11 تفصيل الدورات A–L، §13 ترتيب البناء، §14 الحد الأدنى للاختبار |
| `docs/PROJECT_STATUS.md` | ما تم تنفيذه وما يحتاج إكمال (فيه ادعاء "Five-language routing" غير مطابق للواقع) |
| `app/Modules/*/Domain/Enums/` | 3 enums فقط: PropertyStatus, LeadStatus, AppointmentStatus |
| `app/Modules/Property/Application/Actions/` | منطق سايكل نشر العقار |
| `routes/web.php`, `routes/admin.php`, `routes/api.php` | خريطة كل المسارات |
| `routes/console.php` | لا يوجد به أي scheduled job للسايكلز |
| `tests/Feature/` | 10 ملفات / 26 اختبار |
