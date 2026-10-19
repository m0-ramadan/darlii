<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Currency;
use App\Models\FcmToken;
use App\Models\Project;
use App\Models\Property;
use App\Models\User;
use App\Services\Notifications\FcmNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * اختبارات انحدار للأخطاء المذكورة في تقرير 10–18 أكتوبر 2026.
 * كل اختبار يغطي خطأً محددًا كان يظهر في السجلات.
 */
class ReportedIssuesRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    /**
     * #8/#9/#10/#12/#44: كل صفحات لوحة الإدارة تُفتح بدون أخطاء
     * (admin_route مع Model، الصلاحيات، المرافق، سجل الزوار…).
     */
    public function test_all_admin_pages_render_without_errors(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $urls = [
            '/admin',
            '/admin/properties',
            '/admin/properties/pending',
            '/admin/properties/assignments',
            '/admin/projects',
            '/admin/users',
            '/admin/users/hierarchy',
            '/admin/roles',
            '/admin/posts',
            '/admin/reviews',
            '/admin/locations',
            '/admin/amenities',
            '/admin/categories',
            '/admin/faqs',
            '/admin/careers',
            '/admin/agents',
            '/admin/leads',
            '/admin/appointments',
            '/admin/visitors',
            '/admin/media',
            '/admin/pages',
            '/admin/testimonials',
            '/admin/notifications',
            '/admin/audit',
            '/admin/stats',
            '/admin/settings',
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    /** #33/#45: إنشاء عقار من لوحة العميل بدون reference_code أو slug. */
    public function test_dashboard_property_creation_generates_reference_code_and_slug(): void
    {
        $user = User::where('email', 'admin@example.com')->firstOrFail();
        $currency = Currency::where('code', 'EGP')->firstOrFail();

        $this->actingAs($user)
            ->post('/dashboard/properties', [
                'purpose' => 'sale',
                'price' => 1_000_000,
                'currency_id' => $currency->id,
                'translations' => [
                    'ar' => ['name' => 'شقة تجريبية للتقرير'],
                ],
            ])
            ->assertRedirect();

        $property = Property::query()
            ->whereHas('translations', fn ($query) => $query->where('name', 'شقة تجريبية للتقرير'))
            ->first();

        $this->assertNotNull($property, 'The property must be created.');
        $this->assertNotEmpty($property->reference_code);
        $this->assertNotEmpty($property->translation('ar')?->slug);
    }

    /** #13: إنشاء مشروع إداري بدون إرسال reference_code. */
    public function test_admin_project_creation_without_reference_code(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/projects', [
                'status' => 'draft',
                'translations' => [
                    'ar' => ['name' => 'مشروع تجريبي للتقرير'],
                ],
            ])
            ->assertRedirect();

        $project = Project::query()->latest('id')->first();
        $this->assertNotNull($project);
        $this->assertNotEmpty($project->reference_code);
    }

    /** #54/#55: إضافة مرفق باسم عربي وحالة منطقية. */
    public function test_admin_amenity_creation_uses_translation_and_boolean_status(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->post('/admin/amenities', [
                'key' => 'report-test-amenity',
                'status' => '1',
                'translations' => [
                    'ar' => ['name' => 'مرفق تجريبي للتقرير'],
                ],
            ])
            ->assertRedirect();

        $amenity = Amenity::where('key', 'report-test-amenity')->first();
        $this->assertNotNull($amenity);
        $this->assertTrue((bool) $amenity->status);
        $this->assertSame('مرفق تجريبي للتقرير', $amenity->translation('ar')?->name);
    }

    /** #34: تصدير الإحصائيات لا يرمي Array to string conversion. */
    public function test_admin_stats_export_serializes_array_metrics(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/stats/export');
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('properties_by_status', $csv);
        $this->assertStringNotContainsString('Array', $csv);
    }

    /** #58: إشعارات FCM تستخدم HTTP v1 وليس الواجهة القديمة (404). */
    public function test_fcm_uses_http_v1_endpoint_and_prunes_unregistered_tokens(): void
    {
        $user = User::where('email', 'admin@example.com')->firstOrFail();

        FcmToken::create([
            'user_id' => $user->id,
            'token' => 'valid-device-token',
            'device_type' => 'web_push',
        ]);
        FcmToken::create([
            'user_id' => $user->id,
            'token' => 'expired-device-token',
            'device_type' => 'web_push',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => 3600,
            ]),
            'https://fcm.googleapis.com/v1/projects/*/messages:send' => Http::sequence()
                ->push(['name' => 'projects/khademate2/messages/1'])
                ->push([
                    'error' => [
                        'status' => 'NOT_FOUND',
                        'message' => 'Requested entity was not found.',
                        'details' => [['errorCode' => 'UNREGISTERED']],
                    ],
                ], 404),
        ]);

        $result = app(FcmNotificationService::class)->send([
            'title' => 'عقار جديد',
            'body' => 'شقة جديدة في بني سويف',
            'target_type' => 'all',
        ]);

        $this->assertSame(1, $result['sent_count']);
        $this->assertSame('sent', $result['fcm_status']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'fcm.googleapis.com/v1/projects/'));

        // يجب حذف الرمز المنتهي فقط والإبقاء على الصالح.
        $this->assertDatabaseHas('fcm_tokens', ['token' => 'valid-device-token']);
        $this->assertDatabaseMissing('fcm_tokens', ['token' => 'expired-device-token']);
    }
}
