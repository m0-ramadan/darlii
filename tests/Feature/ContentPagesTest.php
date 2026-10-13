<?php

namespace Tests\Feature;

use App\Models\Career;
use App\Models\Lead;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\ContentPagesSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            CoreSeeder::class,
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            AdminUsersSeeder::class,
            ContentPagesSeeder::class,
        ]);
    }

    public function test_public_faqs_page_renders_with_accordion_and_content(): void
    {
        $response = $this->get('/ar/faqs');

        $response->assertOk();
        $response->assertSee('الأسئلة الشائعة والأجوبة الوافية', false);
        $response->assertSee('كيف أضمن أمان المعاملة القانونية عند شراء عقار عبر دارلي؟', false);
        $response->assertSee('ما هي أفضل المناطق الواعدة للاستثمار العقاري في بني سويف حالياً؟', false);
    }

    public function test_public_privacy_policy_page_renders_with_content(): void
    {
        $response = $this->get('/ar/privacy-policy');

        $response->assertOk();
        $response->assertSee('سياسة الخصوصية وحماية البيانات', false);
        $response->assertSee('المعلومات والبيانات التي نقوم بجمعها', false);
        $response->assertSee('كيف نستخدم معلوماتك', false);
    }

    public function test_public_careers_page_renders_with_jobs_and_filter(): void
    {
        $response = $this->get('/ar/careers');

        $response->assertOk();
        $response->assertSee('الوظائف الشاغرة وفرص العمل', false);
        $response->assertSee('مستشار مبيعات واستثمار عقاري أول', false);
        $response->assertSee('مدينة بني سويف', false);
    }

    public function test_public_career_detail_page_renders(): void
    {
        $career = Career::with('translations')->first();
        $slug = $career->translation('ar')?->slug;

        $response = $this->get('/ar/careers/' . $slug);

        $response->assertOk();
        $response->assertSee($career->translation('ar')->title, false);
    }

    public function test_public_contact_us_page_renders_with_office_details(): void
    {
        $response = $this->get('/ar/contact-us');

        $response->assertOk();
        $response->assertSee('تواصل معنا', false);
        $response->assertSee('+20 10 34905812', false);
        $response->assertSee('شارع صلاح سالم', false);
        $response->assertSee('contact@darlii.eg', false);
    }

    public function test_public_contact_us_form_submission_creates_lead(): void
    {
        $response = $this->post('/ar/contact-us', [
            'name' => 'محمد أحمد إبراهيم',
            'phone' => '01011223344',
            'email' => 'mohamed.ibrahim@example.com',
            'subject' => 'استفسار عن شراء عقار',
            'message' => 'أرغب في الاستفسار عن شقق سكنية متاحة في حي الزهور ببني سويف بمساحة 140 متر.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('leads', [
            'name' => 'محمد أحمد إبراهيم',
            'phone' => '01011223344',
            'email' => 'mohamed.ibrahim@example.com',
            'source' => 'contact-page',
            'status' => 'new',
        ]);
    }

    public function test_admin_can_view_careers_and_toggle_status(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->first();
        $career = Career::first();
        $initialStatus = $career->status;

        $response = $this->actingAs($admin)->get('/admin/careers');
        $response->assertOk();
        $response->assertSee('الوظائف وفرص العمل', false);

        $toggleResponse = $this->actingAs($admin)->post("/admin/careers/{$career->id}/status");
        $toggleResponse->assertRedirect();

        $this->assertNotEquals($initialStatus, $career->fresh()->status);
    }

    public function test_admin_can_view_career_applications(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->first();

        // Create a lead from career source
        Lead::create([
            'reference_code' => 'CAR-001',
            'name' => 'محمود مصطفى',
            'phone' => '01099887766',
            'email' => 'mahmoud@example.com',
            'message' => 'سيرة ذاتية لوظيفة مستشار عقاري',
            'source' => 'career',
            'status' => 'new',
        ]);

        $response = $this->actingAs($admin)->get('/admin/careers/applications');
        $response->assertOk();
        $response->assertSee('طلبات التقديم للوظائف', false);
        $response->assertSee('محمود مصطفى', false);
    }

    public function test_admin_can_view_pages_list_with_content_pages(): void
    {
        $admin = User::where('email', 'admin@darlii.eg')->first();

        $response = $this->actingAs($admin)->get('/admin/pages');
        $response->assertOk();
        $response->assertSee('privacy-policy', false);
        $response->assertSee('faqs', false);
        $response->assertSee('contact-us', false);
    }
}
