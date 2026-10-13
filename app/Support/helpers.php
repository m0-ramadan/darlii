<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('admin_route')) {
    /**
     * Build a route URL for the admin panel while transparently skipping the
     * "{locale}" parameter that the public localized routes require. Admin
     * routes themselves are NOT locale prefixed (the locale comes from the
     * signed-in user's preference), so a naive route() call would leak
     * ?locale=... into every admin URL.
     *
     * Like route(), it accepts a single model instance as the second argument:
     *   admin_route('admin.properties.show', $property)
     */
    function admin_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        try {
            if ($parameters instanceof \Illuminate\Database\Eloquent\Model) {
                $parameters = [$parameters];
            } elseif (! is_array($parameters)) {
                $parameters = array_filter([$parameters], static fn ($value) => $value !== null);
            }

            $route = Route::getRoutes()->getByName($name);

            if ($route && ! in_array('locale', $route->parameterNames(), true)) {
                unset($parameters['locale']);
            }

            return route($name, $parameters, $absolute);
        } catch (\Throwable $e) {
            return '#';
        }
    }
}

if (! function_exists('setting')) {
    /**
     * Read a value from the CMS settings store (cached per request/forever).
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(\App\Services\Settings\SettingsRepository::class)->get($key, $default);
    }
}

if (! function_exists('setting_label')) {
    /**
     * Human readable Arabic label derived from a settings key, used as a
     * fallback when the stored label_ar is empty (e.g. new/extra fields).
     */
    function setting_label(string $key): string
    {
        $segment = str_contains($key, '.') ? substr($key, strrpos($key, '.') + 1) : $key;

        return match ($segment) {
            'enabled' => 'مُفعّل',
            'api_key', 'key' => 'مفتاح API',
            'api_secret', 'secret' => 'المفتاح السري',
            'username' => 'اسم المستخدم',
            'password' => 'كلمة المرور',
            'host' => 'الخادم (Host)',
            'port' => 'المنفذ (Port)',
            'region' => 'المنطقة (Region)',
            'bucket' => 'اسم الحاوية (Bucket)',
            'url' => 'الرابط',
            'sender_id' => 'اسم المرسل',
            'default_disk' => 'قرص التخزين',
            'measurement_id' => 'معرّف القياس GA4',
            'pixel_id' => 'معرّف البكسل',
            'api_key_paymob' => 'مفتاح Paymob',
            default => ucwords(str_replace(['_', '-'], ' ', $segment)),
        };
    }
}

if (! function_exists('money_eg')) {
    /**
     * Format an amount using the admin panel's default currency.
     */
    function money_eg(float|int|string|null $amount, ?string $symbol = null): string
    {
        $amount = (float) $amount;
        $symbol ??= (string) setting('general.currency_symbol', 'ج.م');
        $decimals = (int) setting('general.currency_decimals', 0);

        return number_format($amount, $decimals) . ' ' . $symbol;
    }
}

if (! function_exists('status_label')) {
    /**
     * Arabic label for any of the status enums/strings used across the CMS.
     */
    function status_label(string|\BackedEnum|null $status): string
    {
        $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return match ($value) {
            'draft' => 'مسودة',
            'pending_review' => 'بانتظار المراجعة',
            'published' => 'منشور',
            'rejected' => 'مرفوض',
            'sold' => 'تم البيع',
            'rented' => 'مُؤجر',
            'expired' => 'منتهي',
            'archived' => 'مؤرشف',
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'pending' => 'معلّق',
            'suspended' => 'موقوف',
            'available' => 'متاح',
            'reserved' => 'محجوز',
            'new' => 'جديد',
            'contacted' => 'تم التواصل',
            'qualified' => 'مؤهل',
            'won' => 'تم البيع',
            'lost' => 'مفقود',
            'requested' => 'مطلوب',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $value !== '' ? $value : '—',
        };
    }
}

if (! function_exists('status_color')) {
    /**
     * Bootstrap-ish colour token for a status badge.
     */
    function status_color(string|\BackedEnum|null $status): string
    {
        $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return match ($value) {
            'published', 'active', 'confirmed', 'won', 'available', 'completed', 'sold' => 'success',
            'pending_review', 'pending', 'requested', 'new' => 'warning',
            'rejected', 'suspended', 'lost', 'cancelled', 'expired' => 'danger',
            'rented', 'archived' => 'secondary',
            'draft', 'inactive', 'reserved' => 'info',
            default => 'secondary',
        };
    }
}

if (! function_exists('user_role_label')) {
    /**
     * Arabic label for a role slug.
     */
    function user_role_label(?string $slug): string
    {
        return match ($slug) {
            'super-admin' => 'مدير النظام (سوبر أدمن)',
            'admin' => 'أدمن',
            'manager', 'property-manager' => 'مسؤول',
            'agent' => 'سمسار',
            'content-manager' => 'مسؤول محتوى',
            'support' => 'الدعم',
            'finance' => 'الحسابات',
            'agency-manager' => 'مدير وكالة',
            'customer' => 'عميل',
            default => $slug ? str_replace('-', ' ', $slug) : '—',
        };
    }
}
