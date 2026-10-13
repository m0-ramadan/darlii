<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminUsersSeeder;
use Database\Seeders\CoreSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyPricingAndNegotiationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();
        Property::clearPriceMetaCache();
        $this->seed(CoreSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUsersSeeder::class);

        $this->admin = User::where('email', 'admin@darlii.eg')->first();
        $this->currency = Currency::first();
    }

    public function test_can_create_property_with_negotiable_price_and_confidential_notes(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/properties', [
            'purpose' => 'sale',
            'status' => 'published',
            'price_type' => 'negotiable',
            'price' => 2500000,
            'price_notes' => 'العميل موافق ينزل لحد 2,200,000 كاش عند الجدية',
            'currency_id' => $this->currency->id,
            'reference_code' => 'NEG-100',
            'translations' => [
                'ar' => [
                    'name' => 'شقة فاخرة قابلة للتفاوض',
                    'slug' => 'luxury-apartment-negotiable',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $property = Property::where('reference_code', 'NEG-100')->first();
        $this->assertNotNull($property);
        $this->assertEquals('negotiable', $property->price_type);
        $this->assertTrue($property->isNegotiable());
        $this->assertFalse($property->isPriceOnContact());
        $this->assertEquals('العميل موافق ينزل لحد 2,200,000 كاش عند الجدية', $property->price_notes);

        // Admin show page displays confidential notes and negotiable badge
        $adminShow = $this->actingAs($this->admin)->get("/admin/properties/{$property->id}");
        $adminShow->assertOk();
        $adminShow->assertSee('قابل للتفاوض');
        $adminShow->assertSee('العميل موافق ينزل لحد 2,200,000 كاش عند الجدية');

        // Public show page displays price and badge, but NEVER confidential notes
        $publicShow = $this->get('/ar/properties/luxury-apartment-negotiable');
        $publicShow->assertOk();
        $publicShow->assertSee('قابل للتفاوض');
        $publicShow->assertSee('2,500,000 جنيه');
        $publicShow->assertDontSee('العميل موافق ينزل لحد 2,200,000 كاش عند الجدية');
        $publicShow->assertDontSee('ملاحظات سرية');
    }

    public function test_can_create_property_with_contact_price_type(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/properties', [
            'purpose' => 'sale',
            'status' => 'published',
            'price_type' => 'contact',
            'price' => 0,
            'price_notes' => 'السعر حسب المواصفات المطلوبة بعد المعاينة',
            'currency_id' => $this->currency->id,
            'reference_code' => 'CNT-200',
            'translations' => [
                'ar' => [
                    'name' => 'فيلا السعر عند التواصل',
                    'slug' => 'villa-price-on-contact',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $property = Property::where('reference_code', 'CNT-200')->first();
        $this->assertNotNull($property);
        $this->assertEquals('contact', $property->price_type);
        $this->assertTrue($property->isPriceOnContact());
        $this->assertFalse($property->isNegotiable());

        // Public show displays "السعر عند التواصل"
        $publicShow = $this->get('/ar/properties/villa-price-on-contact');
        $publicShow->assertOk();
        $publicShow->assertSee('السعر عند التواصل');
        $publicShow->assertDontSee('السعر حسب المواصفات المطلوبة بعد المعاينة');

        // Public index also displays "السعر عند التواصل"
        $publicIndex = $this->get('/ar/properties');
        $publicIndex->assertOk();
        $publicIndex->assertSee('السعر عند التواصل');
    }

    public function test_can_update_property_price_type_and_notes(): void
    {
        $property = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'UPD-300',
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 1500000,
            'currency_id' => $this->currency->id,
            'owner_id' => $this->admin->id,
            'created_by' => $this->admin->id,
        ]);
        $property->translations()->create([
            'locale' => 'ar',
            'name' => 'عقار تجريبي للتعديل',
            'slug' => 'test-property-to-update',
        ]);

        $this->assertEquals('fixed', $property->price_type);

        $response = $this->actingAs($this->admin)->put("/admin/properties/{$property->id}", [
            'purpose' => 'sale',
            'status' => 'published',
            'price_type' => 'negotiable',
            'price' => 1400000,
            'price_notes' => 'تم الاتفاق مع المالك على تخفيض السعر',
            'currency_id' => $this->currency->id,
            'reference_code' => 'UPD-300',
            'translations' => [
                'ar' => [
                    'name' => 'عقار تجريبي للتعديل',
                    'slug' => 'test-property-to-update',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $fresh = $property->fresh();
        $this->assertEquals('negotiable', $fresh->price_type);
        $this->assertEquals('تم الاتفاق مع المالك على تخفيض السعر', $fresh->price_notes);
    }

    public function test_sold_property_displays_green_sold_badge_on_public_pages_and_syncs_sold_at(): void
    {
        $property = Property::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference_code' => 'SOLD-777',
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 3000000,
            'currency_id' => $this->currency->id,
            'owner_id' => $this->admin->id,
            'created_by' => $this->admin->id,
        ]);
        $property->translations()->create([
            'locale' => 'ar',
            'name' => 'شقة فاخرة مباعة ببني سويف',
            'slug' => 'luxury-apartment-sold-in-beni-suef',
        ]);

        $this->assertFalse($property->isSold());
        $this->assertNull($property->sold_at);

        // Update to sold via admin
        $response = $this->actingAs($this->admin)->put("/admin/properties/{$property->id}", [
            'purpose' => 'sale',
            'status' => 'sold',
            'price' => 3000000,
            'currency_id' => $this->currency->id,
            'reference_code' => 'SOLD-777',
            'translations' => [
                'ar' => [
                    'name' => 'شقة فاخرة مباعة ببني سويف',
                    'slug' => 'luxury-apartment-sold-in-beni-suef',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $fresh = $property->fresh();
        $this->assertEquals('sold', $fresh->status?->value);
        $this->assertTrue($fresh->isSold());
        $this->assertNotNull($fresh->sold_at);

        // Public index shows property and "تم البيع" badge
        $publicIndex = $this->get('/ar/properties');
        $publicIndex->assertOk();
        $publicIndex->assertSee('شقة فاخرة مباعة ببني سويف');
        $publicIndex->assertSee('تم البيع');
        $publicIndex->assertSee('darli-badge-sold');

        // Public detail page is accessible (not 404) and shows "تم البيع" green badge
        $publicShow = $this->get('/ar/properties/luxury-apartment-sold-in-beni-suef');
        $publicShow->assertOk();
        $publicShow->assertSee('تم البيع');
        $publicShow->assertSee('status-badge-sold');
    }
}
