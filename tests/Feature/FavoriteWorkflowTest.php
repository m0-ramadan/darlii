<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\User;
use Database\Seeders\CoreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FavoriteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_state_filters_invalid_properties_and_uses_arabic_translation(): void
    {
        [$published, $draft] = $this->properties();

        $this->getJson("/ar/favorites/state?ids={$published->id},{$draft->id},99999")
            ->assertOk()
            ->assertJsonPath('authenticated', false)
            ->assertJsonPath('count', 1)
            ->assertJsonPath('active_ids.0', $published->id)
            ->assertJsonPath('properties.0.name', 'عقار مميز')
            ->assertJsonPath('properties.0.url', config('app.url') . "/ar/properties/عقار-مميز-first");
    }

    public function test_authenticated_favorites_are_idempotent_mergeable_and_removable(): void
    {
        [$first, , $second] = $this->properties();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson("/en/favorites/properties/{$first->id}")->assertOk()->assertJsonPath('count', 1);
        $this->actingAs($user)->postJson("/en/favorites/properties/{$first->id}")->assertOk()->assertJsonPath('count', 1);
        $this->assertSame(1, Favorite::where('user_id', $user->id)->count());

        $this->actingAs($user)->postJson('/en/favorites/merge', ['property_ids' => [$first->id, $second->id]])
            ->assertOk()
            ->assertJsonPath('count', 2);
        $this->assertSame(2, Favorite::where('user_id', $user->id)->count());

        $this->actingAs($user)->deleteJson("/en/favorites/properties/{$first->id}")
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonMissing(['id' => $first->id]);
    }

    private function properties(): array
    {
        $this->seed(CoreSeeder::class);
        $currency = Currency::where('code', 'USD')->firstOrFail();
        $make = function (string $status, string $reference) use ($currency): Property {
            $property = Property::create([
                'uuid' => (string) Str::uuid(),
                'reference_code' => $reference,
                'currency_id' => $currency->id,
                'purpose' => 'sale',
                'status' => $status,
                'price' => 150000,
                'published_at' => now(),
            ]);
            $property->translations()->createMany([
                ['locale' => 'en', 'name' => $reference, 'slug' => strtolower($reference)],
                ['locale' => 'ar', 'name' => 'عقار مميز', 'slug' => 'عقار-مميز-' . strtolower($reference)],
            ]);

            return $property;
        };

        return [$make('published', 'FIRST'), $make('draft', 'DRAFT'), $make('published', 'SECOND')];
    }
}
