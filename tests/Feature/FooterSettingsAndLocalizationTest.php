<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSettingsAndLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUsersSeeder::class);
        $this->seed(SettingsSeeder::class);
        app(\App\Services\Settings\SettingsRepository::class)->flush();

        $this->admin = User::where('email', 'admin@darlii.eg')->first();
    }

    public function test_public_footer_renders_arabic_text_and_no_english_placeholders(): void
    {
        $response = $this->get('/ar/our-services');
        $response->assertOk();

        // Check Arabic texts appear in the footer
        $response->assertSee('متخصصون في تقديم أرقى الجولات والخدمات العقارية', false);
        $response->assertSee('خطط الأسعار', false);
        $response->assertSee('خدماتنا', false);
        $response->assertSee('من نحن', false);
        $response->assertSee('اتصل بنا', false);
        $response->assertSee('عقارات للبيع', false);
        $response->assertSee('عقارات للإيجار', false);
        $response->assertSee('سياسة الخصوصية', false);
        $response->assertSee('وكلاؤنا', false);
        $response->assertSee('النشرة الإخبارية', false);
        $response->assertSee('جرعتك الأسبوعية/الشهرية من المعرفة والإلهام', false);
        $response->assertSee('أدخل بريدك الإلكتروني', false);
        $response->assertSee('جميع الحقوق محفوظة', false);
        $response->assertSee('شروط الاستخدام', false);

        // Verify old English strings do not appear in the footer
        $response->assertDontSee('Specializes in providing high-class tours', false);
        $response->assertDontSee('Pricing Plans', false);
        $response->assertDontSee('Property For Sale', false);
        $response->assertDontSee('Property For Rent', false);
        $response->assertDontSee('Our Agents', false);
        $response->assertDontSee('Terms Of Services', false);
        $response->assertDontSee('Cookie Policy', false);
    }

    public function test_admin_can_access_footer_settings_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/settings/footer');
        $response->assertOk();
        $response->assertSee('إعدادات الفوتر', false);
        $response->assertSee('النبذة التعريفية، روابط الفوتر، نصوص النشرة الإخبارية وحقوق النشر', false);
        $response->assertSee('footer_about_text_ar', false);
        $response->assertSee('footer_col2_title_ar', false);
        $response->assertSee('footer_copyright_ar', false);
    }

    public function test_admin_can_update_footer_settings_and_reflects_on_frontend(): void
    {
        $this->actingAs($this->admin);

        // First visit to ensure footer settings are initialized
        $this->get('/admin/settings/footer')->assertOk();

        $responseUpdate = $this->put('/admin/settings/footer', [
            'footer_about_text_ar' => 'دارلي بوابتك الأولى لأفضل العقارات الحصرية في مصر',
            'footer_col2_title_ar' => 'أقسام الموقع',
            'footer_col2_link1_text_ar' => 'باقات الاشتراك',
            'footer_col2_link2_text_ar' => 'خدماتنا المميزة',
            'footer_col2_link3_text_ar' => 'عن المنصة',
            'footer_col2_link4_text_ar' => 'تواصل معنا الآن',
            'footer_col3_title_ar' => 'روابط هامة',
            'footer_col3_link1_text_ar' => 'شقق للبيع',
            'footer_col3_link2_text_ar' => 'شقق للإيجار',
            'footer_col3_link3_text_ar' => 'الشروط والأحكام',
            'footer_col3_link4_text_ar' => 'فريق العمل',
            'footer_newsletter_title_ar' => 'القائمة البريدية',
            'footer_newsletter_subtitle_ar' => 'اشترك ليصلك جديد العقارات أسبوعياً',
            'footer_newsletter_placeholder_ar' => 'اكتب بريدك هنا',
            'footer_copyright_ar' => '© 2026 شركة دارلي العقارية. جميع الحقوق محفوظة.',
            'footer_terms_text_ar' => 'وثيقة الشروط',
            'footer_privacy_text_ar' => 'الخصوصية والأمان',
            'footer_cookie_text_ar' => 'ملفات الكوكيز',
        ]);

        $responseUpdate->assertRedirect();
        $responseUpdate->assertSessionHas('success');

        // Check settings table updated
        $this->assertSame('دارلي بوابتك الأولى لأفضل العقارات الحصرية في مصر', setting('footer.about_text_ar'));
        $this->assertSame('باقات الاشتراك', setting('footer.col2_link1_text_ar'));

        // Check public page reflects the updated settings
        $responsePublic = $this->get('/ar/our-services');
        $responsePublic->assertOk();
        $responsePublic->assertSee('دارلي بوابتك الأولى لأفضل العقارات الحصرية في مصر', false);
        $responsePublic->assertSee('أقسام الموقع', false);
        $responsePublic->assertSee('باقات الاشتراك', false);
        $responsePublic->assertSee('خدماتنا المميزة', false);
        $responsePublic->assertSee('عن المنصة', false);
        $responsePublic->assertSee('تواصل معنا الآن', false);
        $responsePublic->assertSee('روابط هامة', false);
        $responsePublic->assertSee('شقق للبيع', false);
        $responsePublic->assertSee('شقق للإيجار', false);
        $responsePublic->assertSee('القائمة البريدية', false);
        $responsePublic->assertSee('اكتب بريدك هنا', false);
        $responsePublic->assertSee('© 2026 شركة دارلي العقارية. جميع الحقوق محفوظة.', false);
    }
}

