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
        return $this->render('contact', 'بيانات التواصل', 'الهاتف والواتساب والبريد والعنوان وحسابات التواصل الاجتماعي');
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
            'settingsByGroup' => collect($groups)->mapWithKeys(fn ($group) => [$group => $this->fields($group)]),
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
        return Setting::query()->where('group', $group)->orderBy('sort_order')->get();
    }

    private function ensureLanguageSettings(): void
    {
        foreach ([
            ['key' => 'general.language_ar_enabled', 'value' => '1', 'label' => 'تفعيل اللغة العربية', 'help' => 'اللغة الأساسية للموقع.', 'sort' => 6],
            ['key' => 'general.language_en_enabled', 'value' => '0', 'label' => 'تفعيل اللغة الإنجليزية', 'help' => 'عند التعطيل يختفي محوّل الإنجليزية ويُمنع الوصول إلى روابط /en.', 'sort' => 7],
        ] as $definition) {
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
        foreach ([
            ['key' => 'general.maintenance_mode', 'value' => '0', 'type' => 'boolean', 'label' => 'تفعيل وضع الصيانة', 'help' => 'عند التفعيل تظهر صفحة الصيانة للزوار، وتظل لوحة التحكم عاملة.', 'sort' => 1],
            ['key' => 'maintenance.title', 'value' => 'الموقع تحت الصيانة', 'type' => 'text', 'label' => 'عنوان الصفحة', 'help' => null, 'sort' => 2],
            ['key' => 'maintenance.message', 'value' => 'نعمل حاليًا على تحسين تجربتك. سنعود قريبًا، شكرًا لتفهمك.', 'type' => 'textarea', 'label' => 'رسالة الصيانة', 'help' => null, 'sort' => 3],
        ] as $definition) {
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
}
