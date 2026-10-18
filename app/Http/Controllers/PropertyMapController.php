<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Location;
use App\Models\Project;
use App\Models\Property;
use App\Modules\Search\Application\Queries\PropertySearchQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PropertyMapController extends Controller
{
    public function index(Request $request, ?string $locale = null): JsonResponse
    {
        $locale = $this->locale($request, $locale);
        $bounds = $this->bounds($request);
        $perPage = min(max((int) $request->query('per_page', 50), 1), 200);

        $query = Property::query()
            ->with(['translations', 'currency', 'media'])
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(fn ($builder) => $builder->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->whereBetween('latitude', [$bounds[0][0], $bounds[1][0]])
            ->whereBetween('longitude', [$bounds[0][1], $bounds[1][1]])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at');

        $properties = $query->paginate($perPage);
        $defaultCurrency = env('DEFAULT_CURRENCY', 'EGP');
        $currencyCode = strtoupper((string) $request->query(
            'currency',
            $request->hasSession() ? $request->session()->get('currency', $defaultCurrency) : $defaultCurrency
        ));
        $currency = Currency::where('code', $currencyCode)->first() ?? Currency::where('code', 'EGP')->first();

        return response()->json([
            'data' => collect($properties->items())->map(fn (Property $property) => $this->marker($property, $locale, $currency))->values(),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
                'bounds' => $bounds,
            ],
        ]);
    }

    /**
     * AJAX feed for the in-map filter. Returns every matching property that has
     * coordinates so the JS can rebuild the markers without reloading the page.
     */
    public function filter(Request $request): JsonResponse
    {
        $locale = $this->locale($request, null);

        $filters = $request->only([
            'q', 'purpose', 'category_id', 'category_group', 'student', 'location_id',
            'min_price', 'max_price', 'bedrooms', 'bathrooms', 'furnished',
            'nearby', 'nearby_location_id', 'features', 'sort',
        ]);

        if (empty($filters['purpose']) && $request->filled('type')) {
            $filters['purpose'] = $request->input('type');
        }

        if (empty($filters['location_id']) && $request->filled('location')) {
            $locName = trim((string) $request->input('location'));
            $matched = Location::query()
                ->whereHas('translations', fn ($query) => $query->where('name', 'like', "%{$locName}%"))
                ->first();

            if ($matched) {
                $filters['location_id'] = $matched->id;
            }
        }

        $properties = app(PropertySearchQuery::class)
            ->build($filters)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit(300)
            ->get();

        $data = $properties
            ->map(fn (Property $property) => $this->jsMarker($property, $locale))
            ->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'count' => $data->count(),
                'limited' => $data->count() >= 300,
            ],
        ]);
    }

    /** Marker payload consumed by themes/homzen/js/darli-properties-map.js. */
    private function jsMarker(Property $property, string $locale): array
    {
        $translation = $property->translation($locale);
        $isRent = $property->purpose === 'rent';

        if ($property->isPriceOnContact()) {
            $priceStr = $locale === 'ar' ? 'السعر عند التواصل' : 'Price on request';
        } else {
            $priceStr = number_format((float) $property->price, 0) . ' ' . ($locale === 'ar' ? 'جنيه' : 'EGP');
            if ($isRent) {
                $priceStr .= $locale === 'ar' ? ' / شهرياً' : ' / mo';
            }
        }

        $slug = $translation?->slug ?? $property->id;

        return [
            'id' => $property->id,
            'name' => $translation?->name ?? $property->reference_code,
            'slug' => $translation?->slug,
            'url' => route('properties.show', ['locale' => $locale, 'slug' => $slug]),
            'lat' => (float) $property->latitude,
            'lng' => (float) $property->longitude,
            'price' => (float) $property->price,
            'formatted_price' => $priceStr,
            'purpose' => $property->purpose,
            'purpose_label' => $isRent
                ? ($locale === 'ar' ? 'للإيجار' : 'For rent')
                : ($locale === 'ar' ? 'للبيع' : 'For sale'),
            'is_sold' => $property->isSold(),
            'sold_label' => $locale === 'ar' ? 'تم البيع' : 'Sold',
            'category' => $property->category?->translation($locale)?->name ?? '',
            'location' => $translation?->address_text
                ?: ($property->location?->translation($locale)?->name ?? ''),
            'image' => $property->primaryImageUrl($locale),
            'bedrooms' => $property->bedrooms ?? 0,
            'bathrooms' => $property->bathrooms ?? 0,
            'area' => $property->area
                ? rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.')
                : null,
        ];
    }

    public function governorates(Request $request, ?string $locale = null): JsonResponse
    {
        $locale = $this->locale($request, $locale);

        return response()->json([
            'default' => config('egypt_map.default', 'cairo'),
            'governorates' => collect(config('egypt_map.governorates', []))->map(function (array $item, string $key) use ($locale): array {
                return [
                    'key' => $key,
                    'name' => $item['name'][$locale] ?? $item['name']['en'],
                    'aliases' => array_values($item['name']),
                    'center' => $item['center'],
                    'bounds' => $item['bounds'],
                    'zoom' => $item['zoom'],
                ];
            })->values(),
        ]);
    }

    public function cities(Request $request, ?string $locale = null): JsonResponse
    {
        $locale = $this->locale($request, $locale);
        $search = mb_strtolower((string) $request->query('location', ''));
        $items = collect(config('egypt_map.governorates', []))
            ->map(fn (array $item, string $key): array => ['id' => $key, 'text' => $item['name'][$locale] ?? $item['name']['en']])
            ->filter(fn (array $item): bool => $search === '' || str_contains(mb_strtolower($item['text']), $search))
            ->values();

        if ($request->boolean('minimal', true)) {
            return response()->json(['data' => ['items' => $items, 'has_more' => false]]);
        }

        $html = $items->map(fn (array $item): string => '<div class="search-suggestion-item" data-value="' . e($item['id']) . '">' . e($item['text']) . '</div>')->implode('');

        return response()->json(['data' => $html]);
    }

    public function route(Request $request, ?string $locale = null): JsonResponse
    {
        $validated = $request->validate([
            'origin_lat' => ['required', 'numeric', 'between:-90,90'],
            'origin_lng' => ['required', 'numeric', 'between:-180,180'],
            'destination_lat' => ['required', 'numeric', 'between:-90,90'],
            'destination_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $key = (string) config('services.google_maps.key', '');
        abort_if($key === '', 503, 'Google Routes is not configured.');

        $response = Http::timeout(10)
            ->withHeaders([
                'X-Goog-Api-Key' => $key,
                'X-Goog-FieldMask' => 'routes.distanceMeters,routes.duration,routes.polyline.encodedPolyline',
            ])
            ->post('https://routes.googleapis.com/directions/v2:computeRoutes', [
                'origin' => ['location' => ['latLng' => ['latitude' => (float) $validated['origin_lat'], 'longitude' => (float) $validated['origin_lng']]]],
                'destination' => ['location' => ['latLng' => ['latitude' => (float) $validated['destination_lat'], 'longitude' => (float) $validated['destination_lng']]]],
                'travelMode' => 'DRIVE',
                'routingPreference' => 'TRAFFIC_AWARE',
                'languageCode' => $this->locale($request, $locale),
                'units' => 'METRIC',
            ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Unable to calculate the route.'], $response->status() >= 500 ? 502 : $response->status());
        }

        $route = $response->json('routes.0');
        abort_if(! is_array($route), 404, 'No route found.');

        return response()->json([
            'distance_meters' => $route['distanceMeters'] ?? 0,
            'duration' => $route['duration'] ?? null,
            'encoded_polyline' => $route['polyline']['encodedPolyline'] ?? null,
        ]);
    }

    private function bounds(Request $request): array
    {
        $keys = ['sw_lat', 'sw_lng', 'ne_lat', 'ne_lng'];
        if (collect($keys)->every(fn (string $key): bool => is_numeric($request->query($key)))) {
            $swLat = max(-90, min(90, (float) $request->query('sw_lat')));
            $swLng = max(-180, min(180, (float) $request->query('sw_lng')));
            $neLat = max(-90, min(90, (float) $request->query('ne_lat')));
            $neLng = max(-180, min(180, (float) $request->query('ne_lng')));

            if ($swLat < $neLat && $swLng < $neLng) {
                return [[$swLat, $swLng], [$neLat, $neLng]];
            }
        }

        $governorate = (string) $request->query('governorate', config('egypt_map.default', 'beni-suef'));

        return config('egypt_map.governorates.' . $governorate . '.bounds')
            ?? config('egypt_map.governorates.beni-suef.bounds');
    }

    private function locale(Request $request, ?string $locale): string
    {
        $supported = config('locales.supported', ['ar', 'en']);
        foreach ([$locale, $request->query('locale'), $request->cookie('homzen_locale')] as $candidate) {
            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                return $candidate;
            }
        }

        $refererPath = parse_url((string) $request->headers->get('referer'), PHP_URL_PATH);
        $refererLocale = explode('/', trim((string) $refererPath, '/'))[0] ?? null;

        return in_array($refererLocale, $supported, true) ? $refererLocale : app()->getLocale();
    }

    private function marker(Property $property, string $locale, ?Currency $currency): array
    {
        $translation = $property->translation($locale);
        $sourceRate = (float) ($property->currency?->exchange_rate ?: 1);
        $targetRate = (float) ($currency?->exchange_rate ?: 1);
        if ($property->isPriceOnContact()) {
            $formattedPrice = $locale === 'ar' ? 'السعر عند التواصل' : 'Price on request';
        } else {
            $price = (float) $property->price / $sourceRate * $targetRate;
            $num = number_format($price, $currency?->decimal_places ?? 0);
            if ($currency?->code === 'EGP') {
                $formattedPrice = $locale === 'ar' ? $num . ' ج.م' : $num . ' EGP';
            } else {
                $sym = $currency?->symbol ?? $property->currency?->symbol ?? '$';
                $formattedPrice = ($currency?->symbol_position ?? 'before') === 'after' ? $num . ' ' . $sym : $sym . $num;
            }
        }
        $slug = $translation?->slug ?? $property->id;
        $image = $property->media->first()?->path;

        return [
            'id' => $property->id,
            'latitude' => (float) $property->latitude,
            'longitude' => (float) $property->longitude,
            'name' => $translation?->name ?? $property->reference_code,
            'location' => $translation?->address_text ?? '',
            'image_thumb' => $image ? url('/' . ltrim($image, '/')) : $property->placeholderUrl($locale),
            'formatted_price' => $formattedPrice,
            'url' => url('/properties/' . $slug),
            'status_html' => '<span class="flag-tag primary">' . e(ucfirst($property->purpose)) . '</span>',
            'number_bedroom' => $property->bedrooms,
            'number_bathroom' => $property->bathrooms,
            'square' => $property->area,
            'square_text' => $property->area ? $property->area . ' ' . $property->area_unit : '',
            'map_icon' => '<div class="marker-card"><span>' . e($formattedPrice) . '</span></div>',
        ];
    }
}
