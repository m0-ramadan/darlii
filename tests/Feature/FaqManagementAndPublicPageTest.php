<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqManagementAndPublicPageTest extends TestCase
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
        $this->seed(\Database\Seeders\ContentPagesSeeder::class);
        app(\App\Services\Settings\SettingsRepository::class)->flush();

        $this->admin = User::where('email', 'admin@darlii.eg')->first();
    }

    public function test_public_faq_page_renders_with_exact_elements(): void
    {
        $response = $this->get('/ar/faqs');
        $response->assertOk();

        // Hero badge & title
        $response->assertSee('الأسئلة الشائعة والأجوبة الوافية', false);
        $response->assertSee('مركز المساعدة', false);
        $response->assertSee('faqSearchInput', false);

        // CTA buttons
        $response->assertSee('تواصل معنا الآن', false);
        $response->assertSee('محادثة واتساب سريعة', false);
    }

    public function test_admin_can_access_faqs_index_and_see_default_questions(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/faqs');
        $response->assertOk();
        $response->assertSee('الأسئلة الشائعة', false);
        $response->assertSee('قائمة الأسئلة', false);
        $response->assertSee('إعدادات نصوص الصفحة والترويسة', false);
    }

    public function test_admin_can_access_faqs_settings_tab(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/faqs?tab=settings');
        $response->assertOk();
        $response->assertSee('إعدادات ترويسة الصفحة (Hero Section)', false);
        $response->assertSee('إعدادات صندوق الدعم والتواصل السفلي', false);
        $response->assertSee('faq_hero_title', false);
        $response->assertSee('faq_cta_title', false);
    }

    public function test_admin_can_update_faq_page_settings(): void
    {
        $payload = [
            'faq_hero_badge' => 'دليل الاستفسارات',
            'faq_hero_title' => 'الأسئلة الأكثر تكراراً',
            'faq_hero_subtitle' => 'كل ما يهمك معرفته عن العقارات',
            'faq_search_placeholder' => 'ابحث عن أي سؤال...',
            'faq_cta_badge' => 'هل تحتاج لمساعدة؟',
            'faq_cta_title' => 'تحدث مع مستشارينا',
            'faq_cta_desc' => 'نحن هنا لمساعدتك على مدار الساعة',
            'faq_cta_btn_text' => 'اتصل بنا',
            'faq_cta_btn_url' => '/ar/contact-us',
            'faq_cta_whatsapp_text' => 'واتساب فوري',
        ];

        $response = $this->actingAs($this->admin)->put('/admin/faqs/settings', $payload);
        $response->assertRedirect('/admin/faqs?tab=settings');
        $response->assertSessionHas('success');

        // Verify changes reflect on public page
        $publicResponse = $this->get('/ar/faqs');
        $publicResponse->assertOk();
        $publicResponse->assertSee('دليل الاستفسارات', false);
        $publicResponse->assertSee('الأسئلة الأكثر تكراراً', false);
        $publicResponse->assertSee('تحدث مع مستشارينا', false);
        $publicResponse->assertSee('اتصل بنا', false);
        $publicResponse->assertSee('واتساب فوري', false);
    }

    public function test_admin_can_create_new_faq_item(): void
    {
        $newItem = [
            'category' => 'الشراء والاستثمار العقاري',
            'question' => 'هل يمكن تسجيل الشقة في الشهر العقاري ببني سويف؟',
            'answer' => 'نعم، يمكن تسجيل كافة الوحدات المرخصة وفق قانون الشهر العقاري الجديد برقم كودي موحد.',
        ];

        $response = $this->actingAs($this->admin)->post('/admin/faqs', $newItem);
        $response->assertRedirect('/admin/faqs');
        $response->assertSessionHas('success');

        // Check new question appears on public page
        $publicResponse = $this->get('/ar/faqs');
        $publicResponse->assertOk();
        $publicResponse->assertSee('هل يمكن تسجيل الشقة في الشهر العقاري ببني سويف؟', false);
        $publicResponse->assertSee('نعم، يمكن تسجيل كافة الوحدات المرخصة', false);
    }

    public function test_admin_can_edit_and_update_existing_faq_item(): void
    {
        // First ensure an item exists
        $this->actingAs($this->admin)->get('/admin/faqs');

        $updateData = [
            'category' => 'التمويل والأقساط',
            'question' => 'سؤال محدث: ما هي مدة التقسيط المتاحة؟',
            'answer' => 'تصل فترات التقسيط إلى 10 سنوات بدون فوائد لدى المطورين المعتمدين.',
        ];

        $response = $this->actingAs($this->admin)->put('/admin/faqs/1', $updateData);
        $response->assertRedirect('/admin/faqs');
        $response->assertSessionHas('success');

        // Verify update on public page
        $publicResponse = $this->get('/ar/faqs');
        $publicResponse->assertOk();
        $publicResponse->assertSee('سؤال محدث: ما هي مدة التقسيط المتاحة؟', false);
    }

    public function test_admin_can_delete_faq_item(): void
    {
        // Ensure items exist
        $this->actingAs($this->admin)->get('/admin/faqs');

        $response = $this->actingAs($this->admin)->delete('/admin/faqs/1');
        $response->assertRedirect('/admin/faqs');
        $response->assertSessionHas('success');
    }
}
