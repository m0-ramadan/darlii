<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Services\Settings\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * دورة إنشاء العقار من لوحة العميل كاملة:
 * الفورم → الحفظ → الصور/المخطط/الفيديو → الحالة → مراجعة الإدارة → النشر.
 */
class DashboardPropertyLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        app(SettingsRepository::class)->flush();
        Property::clearFloorPlansCache();
        Storage::fake('public');
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    /** العميل العادي: إنشاء بعقار كامل ثم اعتماد الإدارة ثم ظهوره للعامة. */
    public function test_client_property_full_lifecycle(): void
    {
        $client = User::factory()->create(['status' => 'active']);
        $currency = Currency::where('code', 'EGP')->firstOrFail();

        $response = $this->actingAs($client)->post('/dashboard/properties', [
            'purpose' => 'sale',
            'price' => 1_500_000,
            'currency_id' => $currency->id,
            'area' => 140,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'translations' => [
                'ar' => [
                    'name' => 'شقة تمليك 140م سوبر لوكس بالحي الأول',
                    'description' => 'شقة بمدخل خاص وريسبشن 3 قطع ومطبخ أمريكان.',
                ],
            ],
            'images' => [
                $this->fakeImage('front.png'),
                $this->fakeImage('kitchen.png'),
            ],
            'floor_plan_name' => 'المخطط المعماري للشقة',
            'floor_plan_image' => $this->fakeImage('plan.png'),
            'floor_plan_description' => 'ريسبشن 3 قطع وغرف بحرية.',
            'video_url' => 'https://www.youtube.com/watch?v=abc12345678',
        ]);

        $response->assertRedirect(route('dashboard.properties.index'));
        $response->assertSessionHasNoErrors();

        $property = Property::query()
            ->whereHas('translations', fn ($query) => $query->where('name', 'شقة تمليك 140م سوبر لوكس بالحي الأول'))
            ->firstOrFail();

        // بيانات أساسية تم توليدها تلقائيًا.
        $this->assertSame($client->id, (int) $property->owner_id);
        $this->assertSame('pending_review', $property->status->value);
        $this->assertNotEmpty($property->reference_code);
        $this->assertStringStartsWith('PROP-', $property->reference_code);
        $this->assertNotEmpty($property->uuid);
        $this->assertNotEmpty($property->translation('ar')?->slug);
        $this->assertSame(1_500_000.0, (float) $property->price);

        // الصور: صورتان، الأولى أساسية، ومخزّنة فعليًا.
        $media = $property->media()->orderBy('sort_order')->get();
        $this->assertCount(2, $media);
        $this->assertTrue((bool) $media->first()->is_primary);
        $this->assertFalse((bool) $media->last()->is_primary);
        $this->assertSame('public', $media->first()->disk);
        Storage::disk('public')->assertExists($media->first()->path);

        // المخطط الهندسي محفوظ مع صورته.
        $plans = $property->floor_plans;
        $this->assertNotEmpty($plans);
        $this->assertSame('المخطط المعماري للشقة', $plans[0]['name']);
        $this->assertNotNull($plans[0]['image']);
        Storage::disk('public')->assertExists($plans[0]['image']);

        // رابط الفيديو محفوظ.
        $this->assertDatabaseHas('settings', [
            'group' => 'property_video_meta',
            'key' => 'video_' . $property->id,
            'value' => 'https://www.youtube.com/watch?v=abc12345678',
        ]);

        // تظهر في لوحة العميل بحالة "قيد المراجعة".
        $this->actingAs($client)->get('/dashboard/properties')
            ->assertOk()
            ->assertSee('شقة تمليك 140م سوبر لوكس بالحي الأول', false)
            ->assertSee('قيد المراجعة', false);

        // لا تظهر في الموقع العام قبل الاعتماد.
        $this->get('/properties')
            ->assertOk()
            ->assertDontSee('شقة تمليك 140م سوبر لوكس بالحي الأول', false);

        // الإدارة تراها في قائمة المراجعة.
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->actingAs($admin)->get('/admin/properties/pending')
            ->assertOk()
            ->assertSee('شقة تمليك 140م سوبر لوكس بالحي الأول', false);

        // الاعتماد والنشر.
        $this->actingAs($admin)->post("/admin/properties/{$property->id}/approve")->assertRedirect();
        $property->refresh();
        $this->assertSame('published', $property->status->value);
        $this->assertNotNull($property->published_at);

        // تظهر الآن في القائمة العامة وصفحة التفاصيل.
        $this->get('/properties')
            ->assertOk()
            ->assertSee('شقة تمليك 140م سوبر لوكس بالحي الأول', false);

        $this->get('/properties/' . $property->translation('ar')->slug)
            ->assertOk()
            ->assertSee('شقة تمليك 140م سوبر لوكس بالحي الأول', false);
    }

    /** الوكيل: الإنشاء يبقى مسودة، ثم يرسله للمراجعة، ثم يحدد البيع/الإتاحة. */
    public function test_agent_property_draft_submit_and_status_transitions(): void
    {
        $agent = User::factory()->create(['status' => 'active']);
        $agent->roles()->sync([Role::where('slug', 'agent')->firstOrFail()->id]);
        $currency = Currency::where('code', 'EGP')->firstOrFail();

        $this->actingAs($agent)->post('/dashboard/properties', [
            'purpose' => 'rent',
            'price' => 25_000,
            'currency_id' => $currency->id,
            'translations' => ['ar' => ['name' => 'شقة للإيجار بحي الأمل']],
        ])->assertRedirect(route('dashboard.properties.index'));

        $property = Property::query()
            ->whereHas('translations', fn ($query) => $query->where('name', 'شقة للإيجار بحي الأمل'))
            ->firstOrFail();

        $this->assertSame('draft', $property->status->value);

        $this->actingAs($agent)->post("/dashboard/properties/{$property->id}/submit")->assertRedirect();
        $this->assertSame('pending_review', $property->refresh()->status->value);

        $this->actingAs($agent)->post("/dashboard/properties/{$property->id}/sold")->assertRedirect();
        $property->refresh();
        $this->assertSame('sold', $property->status->value);
        $this->assertNotNull($property->sold_at);

        $this->actingAs($agent)->post("/dashboard/properties/{$property->id}/available")->assertRedirect();
        $property->refresh();
        $this->assertSame('published', $property->status->value);
        $this->assertNull($property->sold_at);
    }

    /** حساب قيد المراجعة لا يستطيع إضافة عقارات. */
    public function test_pending_account_cannot_create_property(): void
    {
        $pending = User::factory()->create(['status' => 'pending']);
        $currency = Currency::where('code', 'EGP')->firstOrFail();

        $this->actingAs($pending)->post('/dashboard/properties', [
            'purpose' => 'sale',
            'price' => 500_000,
            'currency_id' => $currency->id,
            'translations' => ['ar' => ['name' => 'محاولة غير مسموحة']],
        ])->assertRedirect(route('dashboard.index'));

        $this->assertDatabaseMissing('property_translations', ['name' => 'محاولة غير مسموحة']);
    }

    /** صورة PNG حقيقية (الـ GD غير مثبت في حاوية الاختبار). */
    private function fakeImage(string $name): UploadedFile
    {
        return new UploadedFile(
            public_path('themes/darlii/images/darli-placeholder.png'),
            $name,
            'image/png',
            null,
            true
        );
    }

    /** نفس الاسم مرتين لا يكسر قيد الـslug الفريد. */
    public function test_duplicate_names_get_unique_slugs(): void
    {
        $client = User::factory()->create(['status' => 'active']);
        $currency = Currency::where('code', 'EGP')->firstOrFail();

        $payload = [
            'purpose' => 'sale',
            'price' => 800_000,
            'currency_id' => $currency->id,
            'translations' => ['ar' => ['name' => 'شقة مكررة الاسم']],
        ];

        $this->actingAs($client)->post('/dashboard/properties', $payload)->assertSessionHasNoErrors();
        $this->actingAs($client)->post('/dashboard/properties', $payload)->assertSessionHasNoErrors();

        $slugs = Property::query()
            ->where('owner_id', $client->id)
            ->with('translations')
            ->get()
            ->flatMap(fn (Property $property) => $property->translations->pluck('slug'))
            ->filter()
            ->values();

        $this->assertCount(2, $slugs);
        $this->assertSame($slugs->unique()->count(), $slugs->count(), 'Slugs must be unique.');
    }
}
