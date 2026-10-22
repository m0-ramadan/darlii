<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Favorite;
use App\Models\Property;
use App\Services\LegacyFrontend\LegacyFrontendRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(private readonly LegacyFrontendRenderer $renderer) {}

    /** صفحة "المحفوظات": تعرض مفضلة المستخدم من قاعدة البيانات (أو فارغة للزائر). */
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $properties = collect();

        if ($user = $request->user()) {
            $ids = Favorite::query()
                ->where('user_id', $user->id)
                ->where('favoritable_type', Property::class)
                ->latest('id')
                ->pluck('favoritable_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($ids !== []) {
                $order = array_flip($ids);
                $properties = Property::query()
                    ->with(['translations', 'media', 'currency', 'location.translations', 'category.translations'])
                    ->whereIn('id', $ids)
                    ->whereIn('status', ['published', 'sold'])
                    ->get()
                    ->sortBy(fn (Property $property) => $order[$property->id] ?? PHP_INT_MAX)
                    ->values();
            }
        }

        return view('favorites.index', [
            'properties' => $properties,
            'locale' => $locale,
            'shell' => $this->shell($locale),
        ]);
    }

    /** @return array{head:string,footer:string}|null */
    private function shell(string $locale): ?array
    {
        return $this->renderer->shell($locale, 'properties', LegacyFrontendRenderer::listingsMarker())
            ?? $this->renderer->shell($locale, 'blog', LegacyFrontendRenderer::listingsMarker());
    }

    public function state(Request $request, ?string $locale = null): JsonResponse
    {
        $locale = $locale ?: app()->getLocale();
        $ids = $request->user()
            ? Favorite::query()
                ->where('user_id', $request->user()->id)
                ->where('favoritable_type', Property::class)
                ->pluck('favoritable_id')
                ->all()
            : $this->validatedGuestIds($request);

        return $this->stateResponse($request, $locale, $ids);
    }

    public function store(Request $request, Property $property): JsonResponse
    {
        $locale = app()->getLocale();
        abort_unless(in_array($property->status->value, ['published', 'sold'], true), 404);

        Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'favoritable_type' => Property::class,
            'favoritable_id' => $property->id,
        ]);

        return $this->stateResponse($request, $locale, $this->userFavoriteIds($request));
    }

    public function destroy(Request $request, Property $property): JsonResponse
    {
        $locale = app()->getLocale();

        Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('favoritable_type', Property::class)
            ->where('favoritable_id', $property->id)
            ->delete();

        return $this->stateResponse($request, $locale, $this->userFavoriteIds($request));
    }

    public function merge(Request $request, ?string $locale = null): JsonResponse
    {
        $locale = $locale ?: app()->getLocale();
        $data = $request->validate([
            'property_ids' => ['array', 'max:100'],
            'property_ids.*' => ['integer', 'distinct', 'exists:properties,id'],
        ]);

        $ids = Property::query()
            ->whereIn('id', $data['property_ids'] ?? [])
            ->whereIn('status', ['published', 'sold'])
            ->pluck('id');

        foreach ($ids as $id) {
            Favorite::firstOrCreate([
                'user_id' => $request->user()->id,
                'favoritable_type' => Property::class,
                'favoritable_id' => $id,
            ]);
        }

        return $this->stateResponse($request, $locale, $this->userFavoriteIds($request));
    }

    private function stateResponse(Request $request, string $locale, array $ids): JsonResponse
    {
        $properties = Property::query()
            ->with(['translations', 'currency', 'media', 'owner'])
            ->whereIn('id', array_slice(array_values(array_unique(array_map('intval', $ids))), 0, 100))
            ->whereIn('status', ['published', 'sold'])
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get();

        $activeIds = $properties->pluck('id')->values()->all();
        $currency = Currency::where('code', $request->session()->get('currency', 'USD'))->first();

        return response()->json([
            'authenticated' => $request->user() !== null,
            'active_ids' => $activeIds,
            'count' => count($activeIds),
            'properties' => $properties->map(fn (Property $property) => $this->propertyData($property, $locale, $currency))->values(),
        ]);
    }

    private function propertyData(Property $property, string $locale, ?Currency $currency): array
    {
        $translation = $property->translation($locale);
        $sourceRate = (float) ($property->currency?->exchange_rate ?: 1);
        $targetRate = (float) ($currency?->exchange_rate ?: 1);
        $price = (float) $property->price / $sourceRate * $targetRate;
        $image = $property->primaryImageUrl($locale);

        return [
            'id' => $property->id,
            'name' => $translation?->name ?? $property->reference_code,
            'slug' => $translation?->slug,
            'url' => url('/properties/' . ($translation?->slug ?? $property->id)),
            'image' => $image,
            'address' => $translation?->address_text,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'area' => $property->area,
            'area_unit' => $property->area_unit,
            'purpose' => $property->purpose,
            'featured' => $property->is_featured,
            'agent' => $property->owner?->name,
            'price' => ($currency?->symbol ?? $property->currency?->symbol ?? '$') . number_format($price, $currency?->decimal_places ?? 0),
        ];
    }

    private function userFavoriteIds(Request $request): array
    {
        return Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('favoritable_type', Property::class)
            ->pluck('favoritable_id')
            ->all();
    }

    private function validatedGuestIds(Request $request): array
    {
        $ids = array_filter(explode(',', (string) $request->query('ids')));

        return array_slice(array_values(array_unique(array_filter(
            array_map('intval', $ids),
            static fn (int $id): bool => $id > 0
        ))), 0, 100);
    }
}
