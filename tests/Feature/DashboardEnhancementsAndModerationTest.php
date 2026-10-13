<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardEnhancementsAndModerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CoreSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUsersSeeder::class);
        $this->seed(SettingsSeeder::class);
        app(\App\Services\Settings\SettingsRepository::class)->flush();

        $this->admin = User::where('email', 'admin@darlii.eg')->first();
        $this->client = User::factory()->create([
            'email' => 'client@example.com',
            'name' => 'عميل تجريبي',
        ]);
        $this->currency = Currency::first();
    }

    private function createProperty(array $attributes = []): Property
    {
        $ref = $attributes['reference_code'] ?? 'PROP-' . Str::upper(Str::random(6));
        $property = Property::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'reference_code' => $ref,
            'currency_id' => $this->currency->id,
            'owner_id' => $this->admin->id,
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 150000,
            'published_at' => now(),
        ], $attributes));

        $property->translations()->createMany([
            ['locale' => 'en', 'name' => 'Property ' . $ref, 'slug' => 'property-' . strtolower($ref)],
            ['locale' => 'ar', 'name' => 'عقار تجريبي ' . $ref, 'slug' => 'aqar-tajribi-' . strtolower($ref)],
        ]);

        return $property;
    }

    public function test_single_clean_pagination_bar_rendered_in_admin(): void
    {
        // Create 20 properties so pagination triggers
        for ($i = 0; $i < 20; $i++) {
            $this->createProperty([
                'reference_code' => 'PAGINATE-' . $i,
            ]);
        }

        $this->actingAs($this->admin);
        $response = $this->get('/admin/properties');
        $response->assertOk();

        // Must contain unified pagination bar and NOT contain raw pagination.next / pagination.previous
        $response->assertSee('ap-pagination-bar', false);
        $response->assertDontSee('pagination.next', false);
        $response->assertDontSee('pagination.previous', false);
    }

    public function test_property_show_quick_actions_are_inline_in_same_row(): void
    {
        $property = $this->createProperty([
            'status' => 'pending_review',
        ]);

        $this->actingAs($this->admin);
        $response = $this->get('/admin/properties/' . $property->id);
        $response->assertOk();

        // Verify 3-column inline row container and the 3 action buttons
        $response->assertSee('grid-template-columns: 1fr 1fr 1fr', false);
        $response->assertSee('اعتماد ونشر العقار', false);
        $response->assertSee('تمييز العقار', false);
        $response->assertSee('حذف العقار', false);
    }

    public function test_admin_can_manage_agent_type_and_contract_terms(): void
    {
        $this->actingAs($this->admin);

        // 1. Create a broker with custom contract terms
        $responseStore = $this->post('/admin/agents', [
            'display_name_ar' => 'مكتب الأهرام للتسويق العقاري',
            'email' => 'ahram.office@darlii.eg',
            'phone' => '01012345678',
            'agent_type' => 'office',
            'contract_terms' => 'عمولة 2.5% من إجمالي البيع، 50% من أول شهر إيجار، السداد خلال 48 ساعة من توقيع العقد.',
            'status' => 'active',
        ]);
        $responseStore->assertRedirect();

        // 2. Verify agent index shows agent_type badge and contract terms
        $responseIndex = $this->get('/admin/agents');
        $responseIndex->assertOk();
        $responseIndex->assertSee('مكتب الأهرام للتسويق العقاري', false);
        $responseIndex->assertSee('مكتب عقارات / شركة', false);
        $responseIndex->assertSee('عمولة 2.5%', false);
    }

    public function test_client_added_property_is_held_under_pending_review_and_hidden_from_public(): void
    {
        $this->actingAs($this->client);

        // Client posts a property from user dashboard
        $responsePost = $this->post('/ar/dashboard/properties', [
            'purpose' => 'sale',
            'price' => 750000,
            'currency_id' => $this->currency->id,
            'reference_code' => 'CLIENT-PROP-101',
            'translations' => [
                'ar' => ['name' => 'شقة تمليك للبيع من المالك مباشرة', 'slug' => 'shqa-tamleek-lelbee-101'],
            ],
        ]);
        $responsePost->assertRedirect();

        // 1. Database status must be pending_review (not draft, not published)
        $this->assertDatabaseHas('properties', [
            'reference_code' => 'CLIENT-PROP-101',
            'status' => 'pending_review',
        ]);

        // 2. User dashboard properties index shows "تحت المراجعة" badge
        $responseUserList = $this->get('/ar/dashboard/properties');
        $responseUserList->assertOk();
        $responseUserList->assertSee('CLIENT-PROP-101', false);
        $responseUserList->assertSee('تحت المراجعة', false);

        // 3. Must NOT appear on public properties list
        $responsePublic = $this->get('/ar/properties');
        $responsePublic->assertDontSee('CLIENT-PROP-101', false);
        $responsePublic->assertDontSee('شقة تمليك للبيع من المالك مباشرة', false);

        // 4. Admin sees it in pending review list and can approve it
        $this->actingAs($this->admin);
        $responseAdminPending = $this->get('/admin/properties/pending');
        $responseAdminPending->assertOk();
        $responseAdminPending->assertSee('CLIENT-PROP-101', false);

        $property = Property::where('reference_code', 'CLIENT-PROP-101')->firstOrFail();
        $this->post('/admin/properties/' . $property->id . '/approve')->assertRedirect();

        // 5. Now it is published and appears in public search!
        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'published',
        ]);
        $responsePublicAfter = $this->get('/ar/properties');
        $responsePublicAfter->assertSee('شقة تمليك للبيع من المالك مباشرة', false);
    }
}
