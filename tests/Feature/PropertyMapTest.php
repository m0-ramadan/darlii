<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Property;
use Database\Seeders\CoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PropertyMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_api_only_returns_published_properties_inside_bounds(): void
    {
        $this->seed(CoreSeeder::class);
        $inside = $this->property('INSIDE', 'published', 30.0444, 31.2357);
        $this->property('OUTSIDE', 'published', 31.2001, 29.9187);
        $this->property('DRAFT', 'draft', 30.05, 31.24);

        $this->getJson('/api/v1/properties/map?sw_lat=29.9&sw_lng=31.1&ne_lat=30.2&ne_lng=31.4&per_page=10&locale=ar')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $inside->id)
            ->assertJsonPath('data.0.url', config('app.url') . '/ar/properties/inside-ar');
    }

    public function test_governorates_endpoint_contains_all_egypt_governorates(): void
    {
        $this->getJson('/ar/map/governorates')
            ->assertOk()
            ->assertJsonCount(27, 'governorates')
            ->assertJsonPath('default', 'beni-suef');
    }

    public function test_route_endpoint_proxies_google_routes_without_exposing_the_key(): void
    {
        config(['services.google_maps.key' => 'test-key']);
        Http::fake(['https://routes.googleapis.com/*' => Http::response([
            'routes' => [[
                'distanceMeters' => 12500,
                'duration' => '1020s',
                'polyline' => ['encodedPolyline' => 'encoded-path'],
            ]],
        ])]);

        $this->getJson('/ar/map/route?origin_lat=29.1&origin_lng=31.1&destination_lat=29.2&destination_lng=31.2')
            ->assertOk()
            ->assertJsonPath('distance_meters', 12500)
            ->assertJsonPath('duration', '1020s')
            ->assertJsonMissingPath('key');
    }

    private function property(string $reference, string $status, float $latitude, float $longitude): Property
    {
        $property = Property::create([
            'uuid' => (string) Str::uuid(),
            'reference_code' => $reference,
            'currency_id' => Currency::where('code', 'USD')->value('id'),
            'purpose' => 'sale',
            'status' => $status,
            'price' => 100000,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'published_at' => now(),
        ]);
        $property->translations()->createMany([
            ['locale' => 'en', 'name' => $reference, 'slug' => strtolower($reference)],
            ['locale' => 'ar', 'name' => $reference . ' AR', 'slug' => strtolower($reference) . '-ar'],
        ]);

        return $property;
    }
}
