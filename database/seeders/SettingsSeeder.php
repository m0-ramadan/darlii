<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds every key used by the CMS settings + integrations screens so the
 * pages are populated (and editable) from the first login.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->definitions() as $index => $row) {
            Setting::query()->updateOrCreate(
                ['key' => $row['key']],
                [
                    'group' => $row['group'],
                    'value' => Setting::query()->where('key', $row['key'])->value('value') ?? ($row['value'] ?? ''),
                    'type' => $row['type'] ?? 'text',
                    'label_ar' => $row['label'] ?? null,
                    'help_ar' => $row['help'] ?? null,
                    'is_public' => $row['public'] ?? false,
                    'sort_order' => $index,
                ]
            );
        }
    }

    /** @return list<array<string, mixed>> */
    private function definitions(): array
    {
        return [
            // ---------------------------------------------------------- general
            ['key' => 'general.site_name', 'group' => 'general', 'value' => 'دارلي | Darlii', 'label' => 'اسم الموقع', 'public' => true],
            ['key' => 'general.tagline', 'group' => 'general', 'value' => 'مكانك يبدأ هنا', 'label' => 'الشعار النصي', 'public' => true],
            ['key' => 'general.description', 'group' => 'general', 'type' => 'textarea', 'value' => 'منصة دارلي - السوق العقاري الأبرز في مصر لشراء وبيع وتأجير العقارات والمشروعات.', 'label' => 'وصف الموقع', 'public' => true],
            ['key' => 'general.currency_symbol', 'group' => 'general', 'value' => 'ج.م', 'label' => 'رمز العملة', 'public' => true],
            ['key' => 'general.currency_decimals', 'group' => 'general', 'type' => 'number', 'value' => '0', 'label' => 'عدد الخانات العشرية'],
            ['key' => 'general.default_locale', 'group' => 'general', 'value' => 'ar', 'label' => 'اللغة الافتراضية'],
            ['key' => 'general.language_ar_enabled', 'group' => 'general', 'type' => 'boolean', 'value' => '1', 'label' => 'تفعيل اللغة العربية', 'help' => 'اللغة الأساسية للموقع.', 'public' => true],
            ['key' => 'general.language_en_enabled', 'group' => 'general', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل اللغة الإنجليزية', 'help' => 'عند التعطيل يختفي محوّل الإنجليزية ويُمنع الوصول إلى روابط /en.', 'public' => true],
            ['key' => 'general.timezone', 'group' => 'general', 'value' => 'Africa/Cairo', 'label' => 'المنطقة الزمنية'],
            ['key' => 'general.items_per_page', 'group' => 'general', 'type' => 'number', 'value' => '15', 'label' => 'عدد العناصر في الصفحة'],
            ['key' => 'general.maintenance_mode', 'group' => 'maintenance', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل وضع الصيانة', 'help' => 'يظهر صفحة الصيانة للزوار فقط، وتظل لوحة التحكم عاملة.'],
            ['key' => 'maintenance.title', 'group' => 'maintenance', 'value' => 'الموقع تحت الصيانة', 'label' => 'عنوان الصفحة'],
            ['key' => 'maintenance.message', 'group' => 'maintenance', 'type' => 'textarea', 'value' => 'نعمل حاليًا على تحسين تجربتك. سنعود قريبًا، شكرًا لتفهمك.', 'label' => 'رسالة الصيانة'],

            // --------------------------------------------------------- branding
            ['key' => 'branding.logo_ar', 'group' => 'branding', 'type' => 'image', 'value' => 'themes/homzen/images/logo/darli-logo-ar.png', 'label' => 'اللوجو (عربي)', 'public' => true],
            ['key' => 'branding.logo_en', 'group' => 'branding', 'type' => 'image', 'value' => 'themes/homzen/images/logo/darli-logo-en.png', 'label' => 'اللوجو (إنجليزي)', 'public' => true],
            ['key' => 'branding.logo_dark_ar', 'group' => 'branding', 'type' => 'image', 'value' => 'themes/homzen/images/logo/darli-logo-ar-dark.png', 'label' => 'اللوجو الداكن (عربي)', 'public' => true],
            ['key' => 'branding.logo_dark_en', 'group' => 'branding', 'type' => 'image', 'value' => 'themes/homzen/images/logo/darli-logo-en-dark.png', 'label' => 'اللوجو الداكن (إنجليزي)', 'public' => true],
            ['key' => 'branding.favicon', 'group' => 'branding', 'type' => 'image', 'value' => 'themes/homzen/images/logo/favicon.png', 'label' => 'أيقونة الموقع (Favicon)', 'public' => true],
            ['key' => 'branding.primary_color', 'group' => 'branding', 'type' => 'color', 'value' => '#0F3D2E', 'label' => 'اللون الأساسي', 'public' => true],
            ['key' => 'branding.gold_color', 'group' => 'branding', 'type' => 'color', 'value' => '#C8A45C', 'label' => 'اللون الذهبي', 'public' => true],

            // ---------------------------------------------------------- contact
            ['key' => 'contact.phone', 'group' => 'contact', 'value' => '+20 10 34905812', 'label' => 'رقم الهاتف', 'public' => true],
            ['key' => 'contact.whatsapp', 'group' => 'contact', 'value' => '+20 10 34905812', 'label' => 'واتساب', 'public' => true],
            ['key' => 'contact.email', 'group' => 'contact', 'value' => 'info@darlii.eg', 'label' => 'البريد الإلكتروني', 'public' => true],
            ['key' => 'contact.address_ar', 'group' => 'contact', 'value' => 'شارع صلاح سالم (تقاطع كورنيش النيل)، مدينة بني سويف، محافظة بني سويف', 'label' => 'العنوان (عربي)', 'public' => true],
            ['key' => 'contact.address_en', 'group' => 'contact', 'value' => 'Salah Salem St (Nile Corniche intersection), Beni Suef City, Egypt', 'label' => 'العنوان (إنجليزي)', 'public' => true],
            ['key' => 'contact.facebook', 'group' => 'contact', 'value' => 'https://facebook.com', 'label' => 'فيسبوك', 'public' => true],
            ['key' => 'contact.twitter', 'group' => 'contact', 'value' => 'https://x.com', 'label' => 'إكس (تويتر)', 'public' => true],
            ['key' => 'contact.instagram', 'group' => 'contact', 'value' => 'https://instagram.com', 'label' => 'إنستجرام', 'public' => true],
            ['key' => 'contact.youtube', 'group' => 'contact', 'value' => 'https://youtube.com', 'label' => 'يوتيوب', 'public' => true],
            ['key' => 'contact.linkedin', 'group' => 'contact', 'value' => 'https://linkedin.com', 'label' => 'لينكدإن', 'public' => true],
            ['key' => 'contact.tiktok', 'group' => 'contact', 'value' => 'https://tiktok.com', 'label' => 'تيك توك', 'public' => true],

            // --------------------------------------------------------------- seo
            ['key' => 'seo.title_ar', 'group' => 'seo', 'value' => 'دارلي | مكانك يبدأ هنا', 'label' => 'عنوان SEO (عربي)', 'public' => true],
            ['key' => 'seo.title_en', 'group' => 'seo', 'value' => 'Darlii | Your Place Starts Here', 'label' => 'عنوان SEO (إنجليزي)', 'public' => true],
            ['key' => 'seo.description_ar', 'group' => 'seo', 'type' => 'textarea', 'value' => 'منصة دارلي - السوق العقاري الأبرز في مصر.', 'label' => 'وصف SEO (عربي)', 'public' => true],
            ['key' => 'seo.description_en', 'group' => 'seo', 'type' => 'textarea', 'value' => 'Darlii - Egypt premier real estate platform.', 'label' => 'وصف SEO (إنجليزي)', 'public' => true],
            ['key' => 'seo.keywords', 'group' => 'seo', 'type' => 'textarea', 'value' => 'عقارات, شقق للبيع, فيلات, مشروعات عقارية, مصر', 'label' => 'الكلمات المفتاحية', 'public' => true],
            ['key' => 'seo.robots', 'group' => 'seo', 'type' => 'textarea', 'value' => "User-agent: *\nAllow: /", 'label' => 'ملف robots.txt', 'public' => true],
            ['key' => 'seo.sitemap_enabled', 'group' => 'seo', 'type' => 'boolean', 'value' => '1', 'label' => 'تفعيل خريطة الموقع'],

            // -------------------------------------------------------------- mail
            ['key' => 'mail.mailer', 'group' => 'mail', 'value' => 'smtp', 'label' => 'نوع الإرسال', 'help' => 'smtp أو log للتجربة'],
            ['key' => 'mail.host', 'group' => 'mail', 'value' => 'smtp.mailtrap.io', 'label' => 'SMTP Host'],
            ['key' => 'mail.port', 'group' => 'mail', 'type' => 'number', 'value' => '587', 'label' => 'SMTP Port'],
            ['key' => 'mail.username', 'group' => 'mail', 'value' => '', 'label' => 'SMTP Username'],
            ['key' => 'mail.password', 'group' => 'mail', 'type' => 'password', 'value' => '', 'label' => 'SMTP Password'],
            ['key' => 'mail.encryption', 'group' => 'mail', 'value' => 'tls', 'label' => 'التشفير', 'help' => 'tls أو ssl أو فارغ'],
            ['key' => 'mail.from_address', 'group' => 'mail', 'value' => 'no-reply@darlii.eg', 'label' => 'بريد المرسل'],
            ['key' => 'mail.from_name', 'group' => 'mail', 'value' => 'دارلي', 'label' => 'اسم المرسل'],
            ['key' => 'mail.notify_new_property', 'group' => 'mail', 'type' => 'boolean', 'value' => '1', 'label' => 'إشعار عند إضافة عقار جديد'],
            ['key' => 'mail.notify_new_lead', 'group' => 'mail', 'type' => 'boolean', 'value' => '1', 'label' => 'إشعار عند وصول عميل محتمل'],

            // --------------------------------------------------------------- sms
            ['key' => 'sms.enabled', 'group' => 'sms', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل الرسائل النصية'],
            ['key' => 'sms.provider', 'group' => 'sms', 'value' => 'whatsapp_cloud', 'label' => 'مزود الخدمة', 'help' => 'whatsapp_cloud أو twilio أو sms_misr'],
            ['key' => 'sms.api_key', 'group' => 'sms', 'type' => 'password', 'value' => '', 'label' => 'API Key'],
            ['key' => 'sms.api_secret', 'group' => 'sms', 'type' => 'password', 'value' => '', 'label' => 'API Secret'],
            ['key' => 'sms.sender_id', 'group' => 'sms', 'value' => 'Darlii', 'label' => 'اسم المرسل'],
            ['key' => 'sms.whatsapp_number', 'group' => 'sms', 'value' => '', 'label' => 'رقم واتساب للأعمال'],
            ['key' => 'sms.template_new_lead', 'group' => 'sms', 'type' => 'textarea', 'value' => 'تم استلام طلبك يا {name}، سيتواصل معك فريقنا قريباً.', 'label' => 'قالب رسالة العميل الجديد'],

            // -------------------------------------------------------- analytics
            ['key' => 'analytics.ga_enabled', 'group' => 'analytics', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل Google Analytics', 'public' => true],
            ['key' => 'analytics.ga_measurement_id', 'group' => 'analytics', 'value' => '', 'label' => 'GA4 Measurement ID', 'help' => 'مثال: G-XXXXXXX', 'public' => true],
            ['key' => 'analytics.gtm_id', 'group' => 'analytics', 'value' => '', 'label' => 'Google Tag Manager ID', 'public' => true],
            ['key' => 'analytics.pixel_enabled', 'group' => 'analytics', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل Facebook Pixel', 'public' => true],
            ['key' => 'analytics.facebook_pixel_id', 'group' => 'analytics', 'value' => '', 'label' => 'Facebook Pixel ID', 'public' => true],
            ['key' => 'analytics.tiktok_pixel_id', 'group' => 'analytics', 'value' => '', 'label' => 'TikTok Pixel ID', 'public' => true],
            ['key' => 'analytics.search_console', 'group' => 'analytics', 'value' => '', 'label' => 'Google Search Console Verification', 'public' => true],

            // ----------------------------------------------------------- maps
            ['key' => 'maps.google_enabled', 'group' => 'maps', 'type' => 'boolean', 'value' => '1', 'label' => 'تفعيل خرائط جوجل', 'public' => true],
            ['key' => 'maps.google_api_key', 'group' => 'maps', 'type' => 'password', 'value' => env('GOOGLE_MAPS_API_KEY', ''), 'label' => 'Google Maps API Key', 'public' => true],
            ['key' => 'maps.default_lat', 'group' => 'maps', 'value' => '30.0444', 'label' => 'خط عرض افتراضي', 'public' => true],
            ['key' => 'maps.default_lng', 'group' => 'maps', 'value' => '31.2357', 'label' => 'خط طول افتراضي', 'public' => true],
            ['key' => 'maps.default_zoom', 'group' => 'maps', 'type' => 'number', 'value' => '12', 'label' => 'مستوى التقريب', 'public' => true],

            // -------------------------------------------------------- payments
            ['key' => 'payments.currency', 'group' => 'payments', 'value' => 'EGP', 'label' => 'عملة الدفع'],
            ['key' => 'payments.paymob_enabled', 'group' => 'payments', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل Paymob'],
            ['key' => 'payments.paymob_api_key', 'group' => 'payments', 'type' => 'password', 'value' => '', 'label' => 'Paymob API Key'],
            ['key' => 'payments.paymob_integration_id', 'group' => 'payments', 'value' => '', 'label' => 'Paymob Integration ID'],
            ['key' => 'payments.stripe_enabled', 'group' => 'payments', 'type' => 'boolean', 'value' => '0', 'label' => 'تفعيل Stripe'],
            ['key' => 'payments.stripe_key', 'group' => 'payments', 'type' => 'password', 'value' => '', 'label' => 'Stripe Publishable Key'],
            ['key' => 'payments.stripe_secret', 'group' => 'payments', 'type' => 'password', 'value' => '', 'label' => 'Stripe Secret Key'],

            // --------------------------------------------------------- storage
            ['key' => 'storage.default_disk', 'group' => 'storage', 'value' => 'public', 'label' => 'قرص التخزين الافتراضي', 'help' => 'public أو local أو s3'],
            ['key' => 'storage.max_upload_mb', 'group' => 'storage', 'type' => 'number', 'value' => '8', 'label' => 'أقصى حجم للصورة (ميجابايت)'],
            ['key' => 'storage.image_width', 'group' => 'storage', 'type' => 'number', 'value' => '1600', 'label' => 'أقصى عرض للصورة'],
            ['key' => 'storage.watermark_enabled', 'group' => 'storage', 'type' => 'boolean', 'value' => '0', 'label' => 'إضافة علامة مائية'],
            ['key' => 'storage.s3_key', 'group' => 'storage', 'type' => 'password', 'value' => '', 'label' => 'AWS Access Key ID'],
            ['key' => 'storage.s3_secret', 'group' => 'storage', 'type' => 'password', 'value' => '', 'label' => 'AWS Secret Access Key'],
            ['key' => 'storage.s3_region', 'group' => 'storage', 'value' => 'eu-west-1', 'label' => 'AWS Region'],
            ['key' => 'storage.s3_bucket', 'group' => 'storage', 'value' => '', 'label' => 'AWS Bucket'],
            ['key' => 'storage.s3_url', 'group' => 'storage', 'value' => '', 'label' => 'CDN / Custom URL'],

            // ------------------------------------------------------ properties
            ['key' => 'properties.auto_approve', 'group' => 'properties', 'type' => 'boolean', 'value' => '0', 'label' => 'نشر العقارات تلقائياً بدون مراجعة'],
            ['key' => 'properties.require_agent_assignment', 'group' => 'properties', 'type' => 'boolean', 'value' => '1', 'label' => 'إلزام إسناد العقار لمسؤول أو سمسار'],
            ['key' => 'properties.featured_limit', 'group' => 'properties', 'type' => 'number', 'value' => '6', 'label' => 'عدد العقارات المميزة المسموح بها'],
            ['key' => 'properties.expiry_days', 'group' => 'properties', 'type' => 'number', 'value' => '90', 'label' => 'مدة انتهاء العقار (أيام)'],
            ['key' => 'properties.reference_prefix', 'group' => 'properties', 'value' => 'DAR', 'label' => 'بادئة كود العقار'],
            ['key' => 'properties.contact_display_mode', 'group' => 'properties', 'type' => 'select', 'value' => 'platform_only', 'label' => 'طريقة عرض بيانات التواصل في تفاصيل العقار', 'help' => 'platform_only: أرقام دارلي فقط | platform_with_agent_name: اسم السمسار مع أرقام دارلي | agent_direct: بيانات السمسار مباشرة'],

            // ------------------------------------------------------- leads
            ['key' => 'leads.auto_assign_agent', 'group' => 'leads', 'type' => 'boolean', 'value' => '1', 'label' => 'إسناد العميل المحتمل تلقائياً لصاحب العقار'],
            ['key' => 'leads.notify_assigned', 'group' => 'leads', 'type' => 'boolean', 'value' => '1', 'label' => 'إشعار المسؤول عند إسناد عميل له'],

            // ----------------------------------------------------------- panel
            ['key' => 'panel.primary_color', 'group' => 'panel', 'type' => 'color', 'value' => '#0F3D2E', 'label' => 'اللون الأساسي للوحة'],
            ['key' => 'panel.accent_color', 'group' => 'panel', 'type' => 'color', 'value' => '#C8A45C', 'label' => 'اللون الثانوي للوحة'],
            ['key' => 'panel.sidebar_style', 'group' => 'panel', 'value' => 'dark', 'label' => 'نمط القائمة الجانبية'],
            ['key' => 'panel.show_dashboard_link', 'group' => 'panel', 'type' => 'boolean', 'value' => '1', 'label' => 'إظهار رابط لوحة التحكم في الموقع'],
        ];
    }
}
