<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\Admin\AuditLogger;
use App\Services\Localization\LocaleRegistry;
use App\Services\Settings\SettingsRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * صفحة "اللغات" في لوحة التحكم: تفعيل/تعطيل اللغات، تحديد اللغة الافتراضية،
 * الترتيب، وتعديل بيانات كل لغة. هذه الصفحة هي المصدر الذي يقرأ منه الموقع
 * لغاته (انظر AppServiceProvider و LocaleRegistry).
 */
class LanguageController extends Controller
{
    use InteractsWithAdminPanel;

    public function __construct(
        private readonly LocaleRegistry $locales,
        private readonly SettingsRepository $settings,
    ) {}

    // ------------------------------------------------------------- القائمة
    public function index()
    {
        $this->authorizePermission('settings.manage');

        return view('admin.languages.index', [
            'languages' => Language::query()->orderBy('sort_order')->orderBy('code')->get(),
            'stats' => [
                'total' => Language::query()->count(),
                'active' => Language::query()->where('is_active', true)->count(),
            ],
            'title' => 'اللغات',
            'subtitle' => 'فعّل أو عطّل لغات الموقع، وحدّد اللغة الافتراضية والترتيب',
        ]);
    }

    // ------------------------------------------------------------ الإضافة
    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        $data = $request->validate([
            'code' => [
                'required', 'string', 'min:2', 'max:10',
                'regex:/^[a-z]{2}(-[a-z]{2,4})?$/',
                Rule::unique('languages', 'code'),
            ],
            'name' => ['required', 'string', 'max:60'],
            'native_name' => ['required', 'string', 'max:60'],
            'direction' => ['required', Rule::in(['ltr', 'rtl'])],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ], [
            'code.regex' => 'كود اللغة يجب أن يكون حرفين لاتينيين صغيرين مثل: ar أو en.',
            'code.unique' => 'هذا الكود مستخدم بالفعل.',
        ]);

        $language = Language::query()->create([
            'code' => $data['code'],
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'direction' => $data['direction'],
            'is_default' => false,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) ($data['sort_order'] ?? ((int) Language::query()->max('sort_order') + 1)),
        ]);

        AuditLogger::log('language.created', $language, [], $language->only(['code', 'name', 'is_active']));
        $this->afterChange();

        return back()->with('success', 'تمت إضافة اللغة "' . $language->native_name . '".');
    }

    // ------------------------------------------------------------ التعديل
    public function update(Request $request, Language $language): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'native_name' => ['required', 'string', 'max:60'],
            'direction' => ['required', Rule::in(['ltr', 'rtl'])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $old = $language->only(['name', 'native_name', 'direction', 'sort_order']);

        $language->update([
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'direction' => $data['direction'],
            'sort_order' => (int) ($data['sort_order'] ?? $language->sort_order),
        ]);

        AuditLogger::log('language.updated', $language, $old, $language->only(array_keys($old)));
        $this->afterChange();

        return back()->with('success', 'تم تحديث بيانات اللغة "' . $language->native_name . '".');
    }

    // -------------------------------------------------- تفعيل / تعطيل
    public function toggle(Language $language): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        if ($language->is_active) {
            if ($language->is_default) {
                return back()->with('error', 'لا يمكن تعطيل اللغة الافتراضية. حدّد لغة افتراضية أخرى أولاً.');
            }

            if ($this->locales->activeCount() <= 1) {
                return back()->with('error', 'يجب أن تبقى لغة واحدة مفعّلة على الأقل في الموقع.');
            }
        }

        $language->forceFill(['is_active' => ! $language->is_active])->save();

        AuditLogger::log('language.toggled', $language, [], [
            'code' => $language->code,
            'is_active' => (bool) $language->is_active,
        ]);
        $this->afterChange();

        return back()->with('success', $language->is_active
            ? 'تم تفعيل اللغة "' . $language->native_name . '".'
            : 'تم إيقاف اللغة "' . $language->native_name . '" ومنع الوصول إلى روابطها.');
    }

    // ------------------------------------------- اللغة الافتراضية
    public function setDefault(Language $language): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        if (! $language->is_active) {
            $language->forceFill(['is_active' => true])->save();
        }

        Language::query()->whereKeyNot($language->getKey())->update(['is_default' => false]);
        $language->forceFill(['is_default' => true])->save();

        AuditLogger::log('language.default', $language, [], ['code' => $language->code]);
        $this->afterChange();

        return back()->with('success', 'تم تعيين "' . $language->native_name . '" كلغة افتراضية للموقع.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(Language $language): RedirectResponse
    {
        $this->authorizePermission('settings.manage');

        if ($language->is_default) {
            return back()->with('error', 'لا يمكن حذف اللغة الافتراضية. عيّن لغة افتراضية أخرى ثم أعد المحاولة.');
        }

        if ($language->is_active) {
            return back()->with('error', 'أوقف اللغة أولاً ثم احذفها.');
        }

        if (Language::query()->count() <= 1) {
            return back()->with('error', 'لا يمكن حذف اللغة الوحيدة في الموقع.');
        }

        AuditLogger::log('language.deleted', $language, $language->only(['code', 'name']), []);

        $code = $language->code;
        $language->delete();
        $this->afterChange();

        return back()->with('success', 'تم حذف اللغة "' . $code . '".');
    }

    // ------------------------------------------------------------- داخلي
    /** مزامنة إعدادات اللغات القديمة وإعادة بناء كاش اللغات بعد أي تغيير. */
    private function afterChange(): void
    {
        $this->mirrorLegacySettings();
        $this->locales->flush();
    }

    /** إبقاء مفاتيح الإعدادات العامة (ar/en) متوافقة مع جدول اللغات. */
    private function mirrorLegacySettings(): void
    {
        try {
            $states = Language::query()->pluck('is_active', 'code')->all();
            $payload = [];

            if (array_key_exists('ar', $states)) {
                $payload['general.language_ar_enabled'] = $states['ar'] ? '1' : '0';
            }

            if (array_key_exists('en', $states)) {
                $payload['general.language_en_enabled'] = $states['en'] ? '1' : '0';
            }

            if ($payload !== []) {
                $this->settings->setMany($payload);
            }
        } catch (\Throwable $e) {
            // لا نُفشل الحفظ بسبب الإعدادات القديمة.
        }
    }
}
