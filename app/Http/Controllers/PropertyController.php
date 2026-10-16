<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\Location;
use App\Modules\Search\Application\Queries\PropertySearchQuery;
use App\Services\Admin\PageViewTracker;
use App\Services\LegacyFrontend\LegacyFrontendRenderer;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct(private readonly LegacyFrontendRenderer $renderer) {}

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $search = app(PropertySearchQuery::class);

        // Normalize filters (support 'k' alias for 'q', and resolve 'location' name to 'location_id' if needed)
        $filterData = $request->only(['q', 'k', 'purpose', 'category_id', 'location_id', 'location', 'min_price', 'max_price', 'bedrooms', 'bathrooms', 'sort']);
        if (empty($filterData['q']) && !empty($filterData['k'])) {
            $filterData['q'] = $filterData['k'];
        }

        if (empty($filterData['location_id']) && !empty($filterData['location'])) {
            $locName = trim((string) $filterData['location']);
            $matched = Location::whereHas('translations', fn ($q) => $q->where('name', 'like', "%{$locName}%"))->first();
            if ($matched) {
                $filterData['location_id'] = $matched->id;
            }
        }

        $properties = $search
            ->build($filterData)
            ->paginate(12)
            ->withQueryString();

        // Data for map pins matching the search filters
        $mapProperties = (clone $search->build(array_merge($filterData, ['sort' => 'newest'])))
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->limit(150)
            ->get()
            ->map(function ($property) use ($locale) {
                $tr = $property->translation($locale);
                $isRent = $property->purpose === 'rent';
                if ($property->isPriceOnContact()) {
                    $priceStr = $locale === 'ar' ? 'السعر عند التواصل' : 'Price on request';
                } else {
                    $priceStr = number_format((float) $property->price, 0) . ' ' . ($locale === 'ar' ? 'جنيه' : 'EGP');
                    if ($isRent) {
                        $priceStr .= $locale === 'ar' ? ' / شهرياً' : ' / mo';
                    }
                }

                return [
                    'id' => $property->id,
                    'name' => $tr?->name ?? $property->reference_code,
                    'slug' => $tr?->slug,
                    'url' => route('properties.show', ['locale' => $locale, 'slug' => $tr?->slug ?? $property->id]),
                    'lat' => (float) $property->latitude,
                    'lng' => (float) $property->longitude,
                    'price' => (float) $property->price,
                    'formatted_price' => $priceStr,
                    'purpose' => $property->purpose,
                    'purpose_label' => $isRent ? ($locale === 'ar' ? 'للإيجار' : 'For rent') : ($locale === 'ar' ? 'للبيع' : 'For sale'),
                    'is_sold' => $property->isSold(),
                    'sold_label' => $locale === 'ar' ? 'تم البيع' : 'Sold',
                    'category' => $property->category?->translation($locale)?->name ?? '',
                    'location' => $tr?->address_text ?: ($property->location?->translation($locale)?->name ?? ''),
                    'image' => $property->primaryImageUrl($locale),
                    'bedrooms' => $property->bedrooms ?? 0,
                    'bathrooms' => $property->bathrooms ?? 0,
                    'area' => $property->area ? rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.') : null,
                ];
            });

        // Sidebar/filter data comes from the CMS so the filters always match
        // what the admin panel actually has.
        $categories = PropertyCategory::query()->with('translations')->get();

        $locations = Location::query()
            ->with('translations')
            ->whereIn('type', ['city', 'district'])
            ->orderBy('id')
            ->get();

        $currentCity = $locale === 'ar' ? 'بني سويف' : 'Beni Suef';
        if (!empty($filterData['location_id'])) {
            $selectedLoc = $locations->firstWhere('id', (int) $filterData['location_id']);
            if ($selectedLoc) {
                $currentCity = $selectedLoc->translation($locale)?->name ?? $currentCity;
            }
        }

        return view('properties.index', [
            'properties' => $properties,
            'mapProperties' => $mapProperties,
            'categories' => $categories,
            'locations' => $locations,
            'currentCity' => $currentCity,
            'shell' => $this->shell('properties'),
        ]);
    }

    public function show(?string $param1 = null, ?string $param2 = null)
    {
        $slug = $param2 ?? $param1 ?? '';
        $locale = $param2 ? $param1 : (app()->getLocale() ?: config('locales.default', 'ar'));

        $property = Property::with([
            'translations', 'currency', 'media', 'amenities.translations',
            'project.translations', 'location.translations', 'category.translations', 'owner',
        ])
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();

        if (! $property) {
            $html = $this->renderer->render(request(), 'ar/properties/' . $slug)
                ?? $this->renderer->render(request(), $locale . '/properties/' . $slug);
            abort_if($html === null, 404);
            return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
        }

        abort_unless(
            in_array($property->status?->value, ['published', 'sold'], true) || $property->isSold() || auth()->id() === $property->owner_id,
            404
        );

        PageViewTracker::track('properties', [$slug]);

        $similar = Property::query()
            ->with(['translations', 'media', 'currency', 'category.translations', 'location.translations'])
            ->where('id', '!=', $property->id)
            ->whereIn('status', ['published', 'sold'])
            ->when($property->category_id, fn ($q) => $q->where('category_id', $property->category_id))
            ->latest()
            ->limit(3)
            ->get();

        return view('properties.show', [
            'property' => $property,
            'similar' => $similar,
            'shell' => $this->shell('properties/' . $slug) ?? $this->shell('properties'),
        ]);
    }

    /** @return array{head:string, footer:string}|null */
    private function shell(string $page): ?array
    {
        return $this->renderer->shell(
            app()->getLocale(),
            $page,
            LegacyFrontendRenderer::listingsMarker()
        ) ?? $this->renderer->shell(
            app()->getLocale(),
            'properties/property-for-sale-johannesburg-south-africa',
            LegacyFrontendRenderer::listingsMarker()
        ) ?? $this->renderer->shell(
            app()->getLocale(),
            'properties',
            LegacyFrontendRenderer::listingsMarker()
        );
    }
}
