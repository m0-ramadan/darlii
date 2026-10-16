<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Admin\AuditLogger;
use App\Services\Settings\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    /** Tabs shown across every settings screen. */
    public const TABS = [
        'general' => ['label' => 'عام', 'route' => 'admin.settings.general', 'icon' => 'settings', 'permission' => 'settings.manage'],
        'maintenance' => ['label' => 'وضع الصيانة', 'route' => 'admin.settings.maintenance', 'icon' => 'alert', 'permission' => 'settings.manage'],
        'branding' => ['label' => 'الهوية', 'route' => 'admin.settings.branding', 'icon' => 'photo', 'permission' => 'settings.manage'],
        'contact' => ['label' => 'التواصل', 'route' => 'admin.settings.contact', 'icon' => 'phone', 'permission' => 'settings.manage'],
        'footer' => ['label' => 'الفوتر', 'route' => 'admin.settings.footer', 'icon' => 'layout', 'permission' => 'settings.manage'],
        'announcements' => ['label' => 'الإعلانات العلوية', 'route' => 'admin.settings.announcements', 'icon' => 'alert', 'permission' => 'settings.manage'],
        'services' => ['label' => 'صفحة خدماتنا', 'route' => 'admin.settings.services', 'icon' => 'briefcase', 'permission' => 'settings.manage'],
        'partners' => ['label' => 'شركاؤنا ووكلائنا', 'route' => 'admin.settings.partners', 'icon' => 'users', 'permission' => 'settings.manage'],
        'seo' => ['label' => 'SEO', 'route' => 'admin.settings.seo', 'icon' => 'search', 'permission' => 'settings.manage'],
        'mail' => ['label' => 'البريد', 'route' => 'admin.settings.mail', 'icon' => 'mail', 'permission' => 'settings.integrations'],
        'integrations' => ['label' => 'التكاملات', 'route' => 'admin.settings.integrations', 'icon' => 'plug', 'permission' => 'settings.integrations'],
        'storage' => ['label' => 'التخزين', 'route' => 'admin.settings.storage', 'icon' => 'download', 'permission' => 'settings.integrations'],
        'properties' => ['label' => 'العقارات', 'route' => 'admin.settings.properties', 'icon' => 'building', 'permission' => 'settings.manage'],
        'panel' => ['label' => 'لوحة التحكم', 'route' => 'admin.settings.panel', 'icon' => 'layout', 'permission' => 'settings.manage'],
    ];

    public function __construct(private SettingsRepository $settings) {}

    public function general()
    {
        $this->ensureLanguageSettings();
        $this->ensureMaintenanceSettings();

        return $this->render('general', 'الإعدادات العامة', 'بيانات الموقع الأساسية، العملة، اللغة والمنطقة الزمنية');
    }

    public function maintenance()
    {
        $this->ensureMaintenanceSettings();

        return $this->render('maintenance', 'وضع الصيانة', 'إيقاف واجهة الموقع مؤقتًا مع بقاء لوحة التحكم متاحة');
    }

    public function branding()
    {
        return $this->render('branding', 'الهوية البصرية', 'الشعارات والأيقونات والألوان الرسمية للمنصة');
    }

    public function contact()
    {
        $this->ensureContactSettings();

        return $this->render('contact', 'بيانات التواصل', 'الهاتف والواتساب والبريد والعنوان وحسابات التواصل الاجتماعي ومحتوى صفحة اتصل بنا');
    }

    public function footer()
    {
        $this->ensureFooterSettings();

        return $this->render('footer', 'إعدادات الفوتر', 'النبذة التعريفية، روابط الفوتر، نصوص النشرة الإخبارية وحقوق النشر');
    }

    public function announcements()
    {
        $this->ensureAnnouncementsSettings();

        return $this->render('announcements', 'شريط الإعلانات العلوي', 'تفعيل وتخصيص نصوص الإعلانات والعروض الترويجية في أعلى الموقع');
    }

    public function services()
    {
        $this->ensureServicesSettings();

        return $this->render('services', 'إعدادات صفحة خدماتنا', 'تخصيص العناوين والوصف والشارات وصندوق الدعوة للإدراج في صفحة خدماتنا');
    }

    public function partners()
    {
        $this->ensurePartnersSettings();

        return $this->render('partners', 'إعدادات شركاؤنا ووكلائنا', 'تخصيص شارة وعنوان ووصف قسم الوكلاء والشركات في الصفحة الرئيسية مع إدارة الشركاء');
    }

    public function seo()
    {
        return $this->render('seo', 'تحسين محركات البحث', 'العناوين والأوصاف والكلمات المفتاحية وخريطة الموقع');
    }

    public function mail()
    {
        return $this->render('mail', 'إعدادات البريد', 'إعدادات SMTP والإشعارات البريدية للمنصة');
    }

    public function integrations()
    {
        $groups = ['analytics', 'maps', 'sms', 'payments'];

        return view('admin.settings.integrations', [
            'groups' => $groups,
            'settingsByGroup' => collect($groups)->mapWithKeys(fn($group) => [$group => $this->fields($group)]),
            'tabs' => self::TABS,
            'active' => 'integrations',
            'title' => 'التكاملات',
            'subtitle' => 'ربط المنصة بخدمات Google وFacebook وواتساب وبوابات الدفع',
        ]);
    }

    public function storage()
    {
        return $this->render('storage', 'إعدادات التخزين', 'مكان تخزين الصور والملفات — محلي أو Amazon S3', 'storage');
    }

    public function properties()
    {
        return $this->render('properties', 'إعدادات العقارات', 'الاعتماد التلقائي، الإسناد، المدة والتمييز', 'properties');
    }

    public function panel()
    {
        return $this->render('panel', 'إعدادات لوحة التحكم', 'ألوان ومظهر لوحة التحكم', 'panel', 'settings.manage');
    }

    // --------------------------------------------------------------- الحفظ
    public function update(Request $request, string $group)
    {
        $this->authorizePermission(in_array($group, ['mail', 'storage', 'integrations', 'analytics', 'maps', 'sms', 'payments'], true)
            ? 'settings.integrations'
            : 'settings.manage');

        $definitions = $this->fields($group);
        abort_if($definitions->isEmpty(), 404, 'مجموعة الإعدادات غير موجودة.');

        $payload = [];
        $uploaded = [];

        foreach ($definitions as $definition) {
            $key = $definition->key;
            $type = $definition->type;
            // HTML/PHP turn dots in field names into underscores, so accept both
            // spellings for every setting key.
            $input = str_replace('.', '_', $key);

            if (in_array($type, ['image', 'file'], true)) {
                $request->validate([$input => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg,gif,ico', 'max:8192']]);

                if ($request->hasFile($input)) {
                    $path = $this->uploadImage($request->file($input), 'branding');
                    if ($path) {
                        $payload[$key] = $path;
                        $uploaded[$key] = $path;
                    }
                }

                continue;
            }

            if ($type === 'boolean') {
                $payload[$key] = $request->boolean($input) ? '1' : '0';
                continue;
            }

            if (! $request->has($input)) {
                continue;
            }

            $value = $request->input($input);

            if (in_array($type, ['password', 'secret'], true) && ($value === null || $value === '')) {
                continue; // لا تستبدل المفتاح المحفوظ بقيمة فارغة
            }

            $payload[$key] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        }

        $this->settings->setMany($payload);

        AuditLogger::log('settings.updated', null, [], ['group' => $group, 'keys' => array_keys($payload)]);

        return back()->with('success', 'تم حفظ إعدادات "' . ($definitions->first()->group ?? $group) . '" بنجاح.');
    }

    /** اختبار الاتصال بالبريد أو التخزين السحابي. */
    public function testConnection(Request $request)
    {
        $this->authorizePermission('settings.integrations');

        $data = $request->validate(['service' => ['required', 'in:mail,s3,storage']]);

        try {
            if ($data['service'] === 'mail') {
                $transport = \Symfony\Component\Mailer\Transport::fromDsn(sprintf(
                    'smtp://%s:%s@%s:%s',
                    urlencode((string) setting('mail.username', '')),
                    urlencode((string) setting('mail.password', '')),
                    setting('mail.host', '127.0.0.1'),
                    setting('mail.port', 587)
                ));
                $transport->__toString();

                return back()->with('success', 'تم التحقق من إعدادات SMTP: الاتصال مهيأ للخادم ' . setting('mail.host') . ':' . setting('mail.port') . '.');
            }

            $disk = $data['service'] === 's3' ? 's3' : $this->uploadDisk();
            Storage::disk($disk)->put('.darlii-connection-test', (string) now());
            $exists = Storage::disk($disk)->exists('.darlii-connection-test');
            Storage::disk($disk)->delete('.darlii-connection-test');

            return $exists
                ? back()->with('success', 'تم الاتصال بقرص التخزين "' . $disk . '" بنجاح.')
                : back()->with('error', 'تعذّر الكتابة على قرص التخزين "' . $disk . '".');
        } catch (\Throwable $e) {
            return back()->with('error', 'فشل الاتصال: ' . $e->getMessage());
        }
    }

    // --------------------------------------------------------------- داخلي
    private function render(string $group, string $title, string $subtitle, ?string $permissionGroup = null, string $permission = 'settings.manage')
    {
        $this->authorizePermission($permission);

        return view('admin.settings.form', [
            'group' => $group,
            'fields' => $this->fields($group),
            'tabs' => self::TABS,
            'active' => $permissionGroup ?? $group,
            'title' => $title,
            'subtitle' => $subtitle,
        ]);
    }

    /** إعدادات المجموعة من قاعدة البيانات، مع تعريف الحقول الافتراضية. */
    private function fields(string $group)
    {
        $fields = Setting::query()->where('group', $group)->orderBy('sort_order')->get();

        if (! config('locales.admin_english_enabled')) {
            $fields = $fields->reject(fn(Setting $setting): bool => str_ends_with($setting->key, '_en'))->values();
        }

        return $fields;
    }

    private function ensureLanguageSettings(): void
    {
        foreach (
            [
                ['key' => 'general.language_ar_enabled', 'value' => '1', 'label' => 'تفعيل اللغة العربية', 'help' => 'اللغة الأساسية للموقع.', 'sort' => 6],
                ['key' => 'general.language_en_enabled', 'value' => '0', 'label' => 'تفعيل اللغة الإنجليزية', 'help' => 'عند التعطيل يختفي محوّل الإنجليزية ويُمنع الوصول إلى روابط /en.', 'sort' => 7],
            ] as $definition
        ) {
            Setting::query()->firstOrCreate(
                ['key' => $definition['key']],
                [
                    'group' => 'general',
                    'value' => $definition['value'],
                    'type' => 'boolean',
                    'label_ar' => $definition['label'],
                    'help_ar' => $definition['help'],
                    'is_public' => true,
                    'sort_order' => $definition['sort'],
                ]
            );
        }

        $this->settings->flush();
    }

    private function ensureMaintenanceSettings(): void
    {
        foreach (
            [
                ['key' => 'general.maintenance_mode', 'value' => '0', 'type' => 'boolean', 'label' => 'تفعيل وضع الصيانة', 'help' => 'عند التفعيل تظهر صفحة الصيانة للزوار، وتظل لوحة التحكم عاملة.', 'sort' => 1],
                ['key' => 'maintenance.title', 'value' => 'الموقع تحت الصيانة', 'type' => 'text', 'label' => 'عنوان الصفحة', 'help' => null, 'sort' => 2],
                ['key' => 'maintenance.message', 'value' => 'نعمل حاليًا على تحسين تجربتك. سنعود قريبًا، شكرًا لتفهمك.', 'type' => 'textarea', 'label' => 'رسالة الصيانة', 'help' => null, 'sort' => 3],
            ] as $definition
        ) {
            Setting::query()->firstOrCreate(
                ['key' => $definition['key']],
                [
                    'group' => 'maintenance',
                    'value' => $definition['value'],
                    'type' => $definition['type'],
                    'label_ar' => $definition['label'],
                    'help_ar' => $definition['help'],
                    'is_public' => false,
                    'sort_order' => $definition['sort'],
                ]
            );
        }

        Setting::query()->where('key', 'general.maintenance_mode')->update([
            'group' => 'maintenance',
            'label_ar' => 'تفعيل وضع الصيانة',
            'help_ar' => 'عند التفعيل تظهر صفحة الصيانة للزوار، وتظل لوحة التحكم عاملة.',
            'sort_order' => 1,
        ]);

        $this->settings->flush();
    }

    private function ensureFooterSettings(): void
    {
        $definitions = [
            ['key' => 'footer.about_text_ar', 'value' => 'متخصصون في تقديم أرقى الجولات والخدمات العقارية لكل من يحتاجها. تواصل معنا', 'type' => 'textarea', 'label' => 'نبذة عن المنصة في الفوتر (عربي)', 'help' => 'النص التعريفي الذي يظهر في العمود الأول من الفوتر.', 'sort' => 1],
            ['key' => 'footer.col2_title_ar', 'value' => 'فئات', 'type' => 'text', 'label' => 'عنوان العمود الثاني (فئات)', 'help' => null, 'sort' => 2],
            ['key' => 'footer.col2_link1_text_ar', 'value' => 'خطط الأسعار', 'type' => 'text', 'label' => 'نص الرابط 1 (خطط الأسعار)', 'help' => null, 'sort' => 3],
            ['key' => 'footer.col2_link2_text_ar', 'value' => 'خدماتنا', 'type' => 'text', 'label' => 'نص الرابط 2 (خدماتنا)', 'help' => null, 'sort' => 4],
            ['key' => 'footer.col2_link3_text_ar', 'value' => 'من نحن', 'type' => 'text', 'label' => 'نص الرابط 3 (من نحن)', 'help' => null, 'sort' => 5],
            ['key' => 'footer.col2_link4_text_ar', 'value' => 'اتصل بنا', 'type' => 'text', 'label' => 'نص الرابط 4 (اتصل بنا)', 'help' => null, 'sort' => 6],
            ['key' => 'footer.col3_title_ar', 'value' => 'شركتنا', 'type' => 'text', 'label' => 'عنوان العمود الثالث (شركتنا)', 'help' => null, 'sort' => 7],
            ['key' => 'footer.col3_link1_text_ar', 'value' => 'عقارات للبيع', 'type' => 'text', 'label' => 'نص الرابط 1 (عقارات للبيع)', 'help' => null, 'sort' => 8],
            ['key' => 'footer.col3_link2_text_ar', 'value' => 'عقارات للإيجار', 'type' => 'text', 'label' => 'نص الرابط 2 (عقارات للإيجار)', 'help' => null, 'sort' => 9],
            ['key' => 'footer.col3_link3_text_ar', 'value' => 'سياسة الخصوصية', 'type' => 'text', 'label' => 'نص الرابط 3 (سياسة الخصوصية)', 'help' => null, 'sort' => 10],
            ['key' => 'footer.col3_link4_text_ar', 'value' => 'وكلاؤنا', 'type' => 'text', 'label' => 'نص الرابط 4 (وكلاؤنا)', 'help' => null, 'sort' => 11],
            ['key' => 'footer.newsletter_title_ar', 'value' => 'النشرة الإخبارية', 'type' => 'text', 'label' => 'عنوان النشرة الإخبارية', 'help' => null, 'sort' => 12],
            ['key' => 'footer.newsletter_subtitle_ar', 'value' => 'جرعتك الأسبوعية/الشهرية من المعرفة والإلهام', 'type' => 'text', 'label' => 'وصف النشرة الإخبارية', 'help' => null, 'sort' => 13],
            ['key' => 'footer.newsletter_placeholder_ar', 'value' => 'أدخل بريدك الإلكتروني', 'type' => 'text', 'label' => 'نص حقل البريد الإلكتروني', 'help' => null, 'sort' => 14],
            ['key' => 'footer.copyright_ar', 'value' => '© 2026 دارلي. جميع الحقوق محفوظة.', 'type' => 'text', 'label' => 'حقوق النشر في أسفل الفوتر', 'help' => null, 'sort' => 15],
            ['key' => 'footer.terms_text_ar', 'value' => 'شروط الاستخدام', 'type' => 'text', 'label' => 'رابط شروط الاستخدام (أسفل الفوتر)', 'help' => null, 'sort' => 16],
            ['key' => 'footer.privacy_text_ar', 'value' => 'سياسة الخصوصية', 'type' => 'text', 'label' => 'رابط سياسة الخصوصية (أسفل الفوتر)', 'help' => null, 'sort' => 17],
            ['key' => 'footer.cookie_text_ar', 'value' => 'سياسة ملفات تعريف الارتباط', 'type' => 'text', 'label' => 'رابط ملفات تعريف الارتباط (أسفل الفوتر)', 'help' => null, 'sort' => 18],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'footer',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'help_ar' => $def['help'],
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }

        $this->settings->flush();
    }

    private function ensureServicesSettings(): void
    {
        $definitions = [
            ['key' => 'services.hero_title', 'value' => 'خدماتنا', 'type' => 'text', 'label' => 'العنوان الرئيسي لصفحة الخدمات', 'help' => null, 'sort' => 1],
            ['key' => 'services.hero_subtitle', 'value' => "حلول عقارية متكاملة تلبي جميع احتياجاتك\nفي بني سويف وما حولها", 'type' => 'textarea', 'label' => 'الوصف التوضيحي في ترويسة الخدمات', 'help' => null, 'sort' => 2],
            ['key' => 'services.why_tag', 'value' => 'خدماتنا', 'type' => 'text', 'label' => 'شارة قسم لماذا تختار دارلي', 'help' => null, 'sort' => 3],
            ['key' => 'services.why_title', 'value' => 'لماذا تختار دارلي؟', 'type' => 'text', 'label' => 'عنوان قسم لماذا تختار دارلي', 'help' => null, 'sort' => 4],
            ['key' => 'services.why_desc', 'value' => 'نقدم لك حلولاً عقارية متكاملة وخبرة محلية عميقة في سوق بني سويف لمساعدتك في تحقيق أهدافك العقارية بسهولة وأمان.', 'type' => 'textarea', 'label' => 'وصف قسم لماذا تختار دارلي', 'help' => null, 'sort' => 5],
            ['key' => 'services.badge_title', 'value' => 'مستقبلك العقاري يبدأ هنا', 'type' => 'text', 'label' => 'عنوان شارة فيلا دارلي', 'help' => null, 'sort' => 6],
            ['key' => 'services.badge_desc', 'value' => 'شريكك الموثوق في شراء وبيع وتأجير العقارات في بني سويف', 'type' => 'textarea', 'label' => 'وصف شارة فيلا دارلي', 'help' => null, 'sort' => 7],
            ['key' => 'services.s1_title', 'value' => 'شراء منزل', 'type' => 'text', 'label' => 'عنوان بطاقة الخدمة 1', 'help' => null, 'sort' => 8],
            ['key' => 'services.s1_desc', 'value' => 'اكتشف أفضل المنازل المعروضة للبيع في بني سويف مع دعم كامل طوال رحلتك الشرائية.', 'type' => 'textarea', 'label' => 'وصف بطاقة الخدمة 1', 'help' => null, 'sort' => 9],
            ['key' => 'services.s1_btn', 'value' => 'اعرف المزيد', 'type' => 'text', 'label' => 'زر بطاقة الخدمة 1', 'help' => null, 'sort' => 10],
            ['key' => 'services.s2_title', 'value' => 'استئجار منزل', 'type' => 'text', 'label' => 'عنوان بطاقة الخدمة 2', 'help' => null, 'sort' => 11],
            ['key' => 'services.s2_desc', 'value' => 'مجموعة متنوعة من المنازل والشقق للإيجار بما يناسب احتياجاتك وميزانيتك.', 'type' => 'textarea', 'label' => 'وصف بطاقة الخدمة 2', 'help' => null, 'sort' => 12],
            ['key' => 'services.s2_btn', 'value' => 'اعرف المزيد', 'type' => 'text', 'label' => 'زر بطاقة الخدمة 2', 'help' => null, 'sort' => 13],
            ['key' => 'services.s3_title', 'value' => 'بيع منزل', 'type' => 'text', 'label' => 'عنوان بطاقة الخدمة 3', 'help' => null, 'sort' => 14],
            ['key' => 'services.s3_desc', 'value' => 'قم ببيع عقارك بأفضل سعر مع تسويق احترافي ووصول واسع للمشترين المحتملين.', 'type' => 'textarea', 'label' => 'وصف بطاقة الخدمة 3', 'help' => null, 'sort' => 15],
            ['key' => 'services.s3_btn', 'value' => 'اعرف المزيد', 'type' => 'text', 'label' => 'زر بطاقة الخدمة 3', 'help' => null, 'sort' => 16],
            ['key' => 'services.testi_tag', 'value' => 'آراء عملائنا', 'type' => 'text', 'label' => 'شارة قسم آراء العملاء', 'help' => null, 'sort' => 17],
            ['key' => 'services.testi_title', 'value' => 'ما يقوله الناس', 'type' => 'text', 'label' => 'عنوان قسم آراء العملاء', 'help' => null, 'sort' => 18],
            ['key' => 'services.testi_subtitle', 'value' => 'نفخر بثقة عملائنا ونسعى دائمًا لتقديم أفضل تجربة عقارية في بني سويف.', 'type' => 'textarea', 'label' => 'وصف قسم آراء العملاء', 'help' => null, 'sort' => 19],
            ['key' => 'services.cta_tag', 'value' => 'كن جزءاً من مجتمع دارلي', 'type' => 'text', 'label' => 'شارة صندوق الدعوة للإدراج', 'help' => null, 'sort' => 20],
            ['key' => 'services.cta_title', 'value' => 'قم بإدراج عقاراتك على Darli الآن', 'type' => 'text', 'label' => 'عنوان صندوق الدعوة للإدراج', 'help' => null, 'sort' => 21],
            ['key' => 'services.cta_subtitle', 'value' => 'وصل إلى آلاف الباحثين عن العقارات في بني سويف وابدأ في تحقيق أفضل العوائد لعقارك.', 'type' => 'textarea', 'label' => 'وصف صندوق الدعوة للإدراج', 'help' => null, 'sort' => 22],
            ['key' => 'services.cta_btn_text', 'value' => 'ابدأ الآن', 'type' => 'text', 'label' => 'نص زر صندوق الإدراج', 'help' => null, 'sort' => 23],
            ['key' => 'services.cta_f1', 'value' => "وصول لعدد كبير\nمن المشترين والمستأجرين", 'type' => 'textarea', 'label' => 'الميزة 1 في صندوق الإدراج', 'help' => null, 'sort' => 24],
            ['key' => 'services.cta_f2', 'value' => "خطوات بسيطة\nوسريعة للإدراج", 'type' => 'textarea', 'label' => 'الميزة 2 في صندوق الإدراج', 'help' => null, 'sort' => 25],
            ['key' => 'services.cta_f3', 'value' => "دعم متخصص\nعلى مدار الساعة", 'type' => 'textarea', 'label' => 'الميزة 3 في صندوق الإدراج', 'help' => null, 'sort' => 26],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'services',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'help_ar' => $def['help'],
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }

        $this->settings->flush();
    }

    private function ensurePartnersSettings(): void
    {
        $definitions = [
            ['key' => 'partners.tag', 'value' => '— شركاؤنا المميزون —', 'type' => 'text', 'label' => 'شارة قسم الشركاء', 'help' => 'تظهر أعلى عنوان القسم', 'sort' => 1],
            ['key' => 'partners.title', 'value' => 'تعرف على وكلائنا', 'type' => 'text', 'label' => 'العنوان الرئيسي للقسم', 'help' => null, 'sort' => 2],
            ['key' => 'partners.subtitle', 'value' => 'تعاون مع نخبة من شركات الوساطة العقارية الموثوقة في مختلف مناطق المملكة', 'type' => 'textarea', 'label' => 'الوصف التوضيحي للقسم', 'help' => null, 'sort' => 3],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'partners',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'help_ar' => $def['help'],
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }

        $this->settings->flush();
    }

    private function ensureAnnouncementsSettings(): void
    {
        $definitions = [
            ['key' => 'announcements.enabled', 'value' => '1', 'type' => 'boolean', 'label' => 'تفعيل شريط الإعلانات العلوي', 'help' => 'إظهار أو إخفاء شريط الإعلانات المتحرك في أعلى الموقع بالكامل', 'sort' => 1],
            ['key' => 'announcements.autoplay_delay', 'value' => '5000', 'type' => 'number', 'label' => 'مدة عرض كل إعلان (بالميلي ثانية)', 'help' => 'مثال: 5000 تعني 5 ثوانٍ قبل الانتقال للإعلان التالي', 'sort' => 2],
            ['key' => 'announcements.item_1', 'value' => 'استفد من العروض لفترة محدودة على المنازل الفاخرة ذات الميزات المذهلة!', 'type' => 'text', 'label' => 'الإعلان الأول', 'help' => 'النص الترويجي الأول في الشريط', 'sort' => 3],
            ['key' => 'announcements.item_2', 'value' => 'استكشف قوائم عقاراتنا الجديدة والمثيرة المتوفرة الآن في مواقع رئيسية!', 'type' => 'text', 'label' => 'الإعلان الثاني', 'help' => 'النص الترويجي الثاني في الشريط (اتركه فارغاً إن لم ترغب به)', 'sort' => 4],
            ['key' => 'announcements.item_3', 'value' => 'اكتشف منزل أحلامك من خلال أحدث قوائمنا وخدماتنا المخصصة.', 'type' => 'text', 'label' => 'الإعلان الثالث', 'help' => 'النص الترويجي الثالث في الشريط (اتركه فارغاً إن لم ترغب به)', 'sort' => 5],
            ['key' => 'announcements.item_4', 'value' => 'انضم إلينا في فعاليات البيت المفتوح الحصرية واعثر على عقارك المثالي.', 'type' => 'text', 'label' => 'الإعلان الرابع', 'help' => 'النص الترويجي الرابع في الشريط (اتركه فارغاً إن لم ترغب به)', 'sort' => 6],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'announcements',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'help_ar' => $def['help'],
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }

        $this->settings->flush();
    }

    private function ensureContactSettings(): void
    {
        $definitions = [
            ['key' => 'contact.hero_badge', 'value' => 'فريق خدمة العملاء والاستشارات', 'type' => 'text', 'label' => 'شارة أعلى صفحة اتصل بنا', 'help' => 'الشارة العلوية باللون الأحمر أعلى عنوان الصفحة', 'sort' => 1],
            ['key' => 'contact.hero_title', 'value' => 'تواصل معنا - منصة دارلي العقارية', 'type' => 'text', 'label' => 'عنوان صفحة اتصل بنا', 'help' => 'العنوان الرئيسي الذي يظهر في أعلى الصفحة', 'sort' => 2],
            ['key' => 'contact.hero_desc', 'value' => 'نحن هنا دائماً لاستقبال استفساراتكم ومساعدتكم في اختيار عقاركم المثالي في محافظة بني سويف بكل سهولة واحترافية', 'type' => 'textarea', 'label' => 'وصف صفحة اتصل بنا', 'help' => 'النص التوضيحي أسفل العنوان الرئيسي', 'sort' => 3],
            ['key' => 'contact.working_hours', 'value' => 'يومياً من 9:00 ص حتى 9:00 م (الجمعة من 4 م إلى 9 م)', 'type' => 'text', 'label' => 'مواعيد العمل الرسمية', 'help' => 'تظهر في بطاقة المقر الرئيسي على صفحة اتصل بنا', 'sort' => 4],
            ['key' => 'contact.map_embed_url', 'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d111244.75704179374!2d31.0261352!3d29.0660727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x145a27845bc8bbcb%3A0xb36ef554a938c35b!2z2KjZhtmKINiz2YjZitmB2Iwg2YXYsdmD2LIg2KjZhtmKINiz2YjZitmB2Iwg2KjZhtmKINiz2YjZitmB2IwgNjc3NDAwMQ!5e0!3m2!1sar!2seg!4v1710000000000!5m2!1sar!2seg', 'type' => 'textarea', 'label' => 'رابط خريطة Google المضمنة (Embed URL)', 'help' => 'رابط تضمين الخريطة src في أسفل صفحة اتصل بنا', 'sort' => 5],
        ];

        foreach ($definitions as $def) {
            Setting::query()->firstOrCreate(
                ['key' => $def['key']],
                [
                    'group' => 'contact',
                    'value' => $def['value'],
                    'type' => $def['type'],
                    'label_ar' => $def['label'],
                    'help_ar' => $def['help'],
                    'is_public' => true,
                    'sort_order' => $def['sort'],
                ]
            );
        }

        $this->settings->flush();
    }
}
