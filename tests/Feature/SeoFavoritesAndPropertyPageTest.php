<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoFavoritesAndPropertyPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function publishedProperty(array $attributes = []): Property
    {
        $currency = Currency::where('code', 'EGP')->firstOrFail();

        $property = Property::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'reference_code' => 'SEO-' . strtoupper(Str::random(6)),
            'currency_id' => $currency->id,
            'purpose' => 'sale',
            'status' => 'published',
            'price' => 1_250_000,
            'area' => 120,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'latitude' => 29.0661,
            'longitude' => 31.0994,
            'published_at' => now(),
        ], $attributes));

        $property->translations()->create([
            'locale' => 'ar',
            'name' => 'شقة اختبار SEO',
            'slug' => 'seo-test-apartment',
            'description' => 'شقة واسعة للبيع في بني سويف بمساحة 120 مترًا.',
        ]);

        PropertyMedia::create([
            'property_id' => $property->id,
            'type' => 'image',
            'disk' => 'public',
            'path' => 'properties/seo-test-image.jpg',
            'is_primary' => true,
            'sort_order' => 1,
        ]);

        return $property->fresh(['translations', 'media', 'currency']);
    }

    public function test_property_page_has_dynamic_seo_tags_without_foreign_data(): void
    {
        $property = $this->publishedProperty();

        $response = $this->get('/properties/seo-test-apartment');
        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('<title>', $html);
        $this->assertStringContainsString('شقة اختبار SEO', $html);
        $this->assertStringContainsString('og:title', $html);
        $this->assertStringContainsString('og:url', $html);
        $this->assertStringContainsString('og:image', $html);
        $this->assertStringContainsString('properties/seo-test-image.jpg', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString(url('/properties/seo-test-apartment'), $html);
        $this->assertStringContainsString('RealEstateListing', $html);

        // البيانات الأجنبية من قالب هومزن يجب ألا تظهر.
        $this->assertStringNotContainsString('جوهانسبرغ', $html);
        $this->assertStringNotContainsString('south-africa', strtolower($html));
        $this->assertStringNotContainsString('darlii.botble.com', $html);
        $this->assertStringNotContainsString('هومزين', $html);
    }

    public function test_property_page_shows_save_button_video_and_map(): void
    {
        $property = $this->publishedProperty();
        $property->setVideoUrl('https://www.youtube.com/embed/abc12345678');

        $response = $this->get('/properties/seo-test-apartment');
        $response->assertOk();

        $response->assertSee('data-bb-toggle="add-to-wishlist"', false);
        $response->assertSee('darli-save-property', false);
        $response->assertSee('darli-video-card', false);
        $response->assertSee('darli-map-card', false);
        $response->assertSee('data-lat="29.0661"', false);
        $response->assertSee('data-lng="31.0994"', false);
        $response->assertSee('darli-property-details.css', false);
        $response->assertSee('youtube.com/embed/abc12345678', false);
    }

    public function test_properties_listing_has_clean_canonical_and_no_foreign_data(): void
    {
        $response = $this->get('/properties');
        $response->assertOk();

        $html = $response->getContent();
        $this->assertStringContainsString('rel="canonical" href="' . url('/properties') . '"', $html);
        $this->assertStringNotContainsString('جوهانسبرغ', $html);
        $this->assertStringNotContainsString('هومزين', $html);
    }

    public function test_authenticated_user_sees_saved_properties_page(): void
    {
        $property = $this->publishedProperty();
        $user = User::factory()->create();

        Favorite::create([
            'user_id' => $user->id,
            'favoritable_type' => Property::class,
            'favoritable_id' => $property->id,
        ]);

        $this->actingAs($user)->get('/favorites')
            ->assertOk()
            ->assertSee('شقة اختبار SEO', false)
            ->assertSee('data-favorite-card="' . $property->id . '"', false)
            ->assertSee('darli-favorites-page', false);

        $this->actingAs($user)->get('/wishlist')->assertOk();
    }

    public function test_guest_favorites_page_renders_empty_state_without_errors(): void
    {
        $this->get('/favorites')
            ->assertOk()
            ->assertSee('darli-favorites-page', false)
            ->assertSee('darli-fav-empty', false)
            ->assertSee('darli-favorites.js', false);
    }

    public function test_guest_cannot_write_to_favorites_endpoint_but_gets_a_clean_response(): void
    {
        $property = $this->publishedProperty();

        $this->postJson("/favorites/properties/{$property->id}")
            ->assertStatus(401);
    }

    public function test_favorites_page_is_noindex(): void
    {
        $this->get('/favorites')
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }
}
