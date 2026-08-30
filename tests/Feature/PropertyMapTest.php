<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Property;
use Database\Seeders\CoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
            ->assertJsonPath('default', 'cairo');
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
