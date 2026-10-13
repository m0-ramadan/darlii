<?php

namespace App\Services\Localization;

use App\Models\Language;
use App\Services\Settings\SettingsRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * المصدر الوحيد للحقيقة بشأن لغات الموقع.
 *
 * تقرأ اللغات من جدول `languages` الذي تديره صفحة "اللغات" في لوحة التحكم،
 * مع الرجوع إلى مفاتيح الإعدادات القديمة (general.language_ar_enabled /
 * general.language_en_enabled) ثم إلى العربية فقط، حتى لا يتعطّل الموقع
 * أبداً إذا كان الجدول غير موجود أو فارغاً (مثل وقت تنفيذ الترحيلات).
 */
class LocaleRegistry
{
    public const CACHE_KEY = 'darlii.locales.registry';

    public function __construct(private readonly SettingsRepository $settings) {}

    /**
     * كل صفوف اللغات مع تخزين مؤقت.
     *
     * @return array<int, array{code:string,name:string,native_name:string,direction:string,is_default:bool,is_active:bool,sort_order:int}>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            try {
                if (! Schema::hasTable('languages')) {
                    return [];
                }

                return Language::query()
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->get()
                    ->map(fn (Language $language): array => [
                        'code' => (string) $language->code,
                        'name' => (string) $language->name,
                        'native_name' => (string) ($language->native_name ?: $language->name),
                        'direction' => $language->direction === 'rtl' ? 'rtl' : 'ltr',
                        'is_default' => (bool) $language->is_default,
                        'is_active' => (bool) $language->is_active,
                        'sort_order' => (int) $language->sort_order,
                    ])
                    ->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /**
     * الأكواد المعروفة كلها (المفعّلة والمعطّلة).
     * تُستخدم لتعريف المسارات حتى لا يحتاج تفعيل/تعطيل لغة إلى إعادة بناء كاش المسارات.
     *
     * @return array<int, string>
     */
    public function knownCodes(): array
    {
        $codes = array_values(array_unique(array_column($this->all(), 'code')));

        return $codes !== [] ? $codes : $this->activeCodes();
    }

    /**
     * الأكواد المفعّلة فقط — هي المسموح بعرضها في الموقع.
     *
     * @return array<int, string>
     */
    public function activeCodes(): array
    {
        $codes = array_values(array_unique(array_column(
            array_values(array_filter($this->all(), fn (array $row): bool => $row['is_active'])),
            'code',
        )));

        return $codes !== [] ? $codes : $this->fallbackCodes();
    }

    /** كود اللغة الافتراضية. */
    public function defaultCode(): string
    {
        $active = $this->activeCodes();

        foreach ($this->all() as $row) {
            if ($row['is_default'] && in_array($row['code'], $active, true)) {
                return $row['code'];
            }
        }

        return $active[0] ?? 'ar';
    }

    /**
     * أسماء اللغات المفعّلة (code => الاسم الأصلي) لمحوّل اللغة في الموقع.
     *
     * @return array<string, string>
     */
    public function names(): array
    {
        $active = $this->activeCodes();
        $names = [];

        foreach ($this->all() as $row) {
            if (in_array($row['code'], $active, true)) {
                $names[$row['code']] = $row['native_name'] !== '' ? $row['native_name'] : $row['code'];
            }
        }

        $ordered = [];

        foreach ($active as $code) {
            $ordered[$code] = $names[$code]
                ?? (string) config('locales.file_names.' . $code, strtoupper($code));
        }

        return $ordered;
    }

    /**
     * أكواد اللغات المكتوبة من اليمين إلى اليسار.
     *
     * @return array<int, string>
     */
    public function rtlCodes(): array
    {
        $rows = $this->all();

        if ($rows === []) {
            return ['ar'];
        }

        $rtl = [];

        foreach ($rows as $row) {
            if ($row['direction'] === 'rtl') {
                $rtl[] = $row['code'];
            }
        }

        return array_values(array_unique($rtl));
    }

    /** عدد اللغات المفعّلة فعلياً في قاعدة البيانات (بدون بدائل احتياطية). */
    public function activeCount(): int
    {
        try {
            return Schema::hasTable('languages')
                ? (int) Language::query()->where('is_active', true)->count()
                : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * مزامنة التفعيل/التعطيل من إعدادات "الإعدادات العامة" القديمة إلى جدول اللغات،
     * حتى تبقى الشاشتان (الإعدادات العامة وصفحة اللغات) متوافقتين.
     */
    public function syncFromLegacySettings(): void
    {
        try {
            if (! Schema::hasTable('languages')) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }

        $desired = [
            'ar' => (bool) $this->settings->get('general.language_ar_enabled', true),
            'en' => (bool) $this->settings->get('general.language_en_enabled', false),
        ];

        $changed = false;

        foreach ($desired as $code => $enabled) {
            $language = Language::query()->where('code', $code)->first();

            if (! $language || (bool) $language->is_active === $enabled) {
                continue;
            }

            // لا تسمح بتعطيل كل اللغات: يجب أن تبقى لغة واحدة مفعّلة على الأقل.
            if (! $enabled && $this->activeCount() <= 1) {
                continue;
            }

            $language->forceFill(['is_active' => $enabled])->save();
            $changed = true;
        }

        if ($changed) {
            $this->flush();
        }
    }

    /** إعادة بناء الكاش بعد أي تعديل على اللغات. */
    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * الأكواد الاحتياطية عند غياب جدول اللغات.
     *
     * @return array<int, string>
     */
    private function fallbackCodes(): array
    {
        $codes = [];

        if ($this->settings->get('general.language_ar_enabled', true)) {
            $codes[] = 'ar';
        }

        if ($this->settings->get('general.language_en_enabled', false)) {
            $codes[] = 'en';
        }

        return $codes !== [] ? $codes : ['ar'];
    }
}
