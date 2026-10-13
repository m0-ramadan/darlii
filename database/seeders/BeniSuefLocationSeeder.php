<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Support\BeniSuefCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

/**
 * Seeds the Beni Suef Governorate location hierarchy (governorate → cities/markazes →
 * districts/villages) using Google Maps Geocoding API with real coordinates and
 * Arabic + English names.
 */
class BeniSuefLocationSeeder extends Seeder
{
    protected ?string $apiKey = null;
    protected array $cache = [];
    protected string $cacheFile = '';

    public function run(): void
    {
        $this->apiKey = env('GOOGLE_MAPS_API_KEY') ?: config('services.google.maps_api_key');
        $this->cacheFile = storage_path('app/benisuef_google_maps_cache.json');

        if (file_exists($this->cacheFile)) {
            $this->cache = json_decode((string) file_get_contents($this->cacheFile), true) ?: [];
        }

        $ids = [];
        $apiSuccessCount = 0;
        $totalLocations = 0;

        $allLocations = BeniSuefCatalog::allLocations();
        $this->command?->info("Starting Beni Suef Locations Seeder (" . count($allLocations) . " locations)...");
        if ($this->apiKey) {
            $this->command?->info("Google Maps Geocoding API connected.");
        } else {
            $this->command?->warn("No GOOGLE_MAPS_API_KEY found, using catalog fallback coordinates.");
        }

        foreach ($allLocations as $row) {
            $totalLocations++;
            $parentId = $row['parent'] ? ($ids[$row['parent']] ?? null) : null;

            $lat = (float) $row['lat'];
            $lng = (float) $row['lng'];

            // Geocode using Google Maps API for cities and districts
            if ($row['type'] !== 'governorate') {
                $geocoded = $this->geocodeLocation($row);
                if ($geocoded !== null) {
                    $lat = $geocoded['lat'];
                    $lng = $geocoded['lng'];
                    $apiSuccessCount++;
                }
            }

            $location = Location::query()
                ->whereHas('translations', fn ($q) => $q->where('slug', $row['key']))
                ->first() ?: new Location();

            $location->fill([
                'parent_id' => $parentId,
                'type' => $row['type'],
                'country_code' => 'EG',
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => true,
            ]);
            $location->save();

            foreach (['ar' => $row['ar'], 'en' => $row['en']] as $locale => $name) {
                $location->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['name' => $name, 'slug' => $row['key']]
                );
            }

            $location->translations()->whereNotIn('locale', ['ar', 'en'])->delete();

            $ids[$row['key']] = $location->id;
        }

        // Save cache to disk
        if (!empty($this->cache)) {
            @file_put_contents($this->cacheFile, json_encode($this->cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $this->command?->info("Beni Suef locations seeded successfully: {$totalLocations} locations ({$apiSuccessCount} geocoded with Google Maps).");
    }

    protected function geocodeLocation(array $row): ?array
    {
        $key = $row['key'];
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        if (!$this->apiKey) {
            return null;
        }

        $searchQuery = $row['ar'] . '، بني سويف، مصر';
        try {
            $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $searchQuery,
                'key' => $this->apiKey,
                'language' => 'ar',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'OK' && !empty($data['results'][0]['geometry']['location'])) {
                    $loc = $data['results'][0]['geometry']['location'];
                    $coords = [
                        'lat' => (float) $loc['lat'],
                        'lng' => (float) $loc['lng'],
                        'formatted_address' => $data['results'][0]['formatted_address'] ?? '',
                    ];
                    $this->cache[$key] = $coords;
                    $this->command?->line("  ✓ [Google Maps] {$row['ar']} -> {$coords['lat']}, {$coords['lng']}");
                    return $coords;
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback to default catalog coordinates
        }

        return null;
    }
}
