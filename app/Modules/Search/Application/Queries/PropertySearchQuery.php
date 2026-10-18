<?php

namespace App\Modules\Search\Application\Queries;

use App\Models\Property;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;

class PropertySearchQuery
{
    public function build(array $filters): Builder
    {
        $q = Property::query()
            ->with(['translations', 'currency', 'media', 'amenities.translations'])
            ->whereIn('status', ['published', 'sold']);

        if (!empty($filters['q'])) {
            $term = $filters['q'];
            $q->whereHas('translations', function ($t) use ($term) {
                $t->where('name', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        if (!empty($filters['purpose'])) {
            $q->where('purpose', $filters['purpose']);
        }

        // Handle category_group (e.g. residential => [1,2,3,4])
        if (!empty($filters['category_group'])) {
            if ($filters['category_group'] === 'residential') {
                $q->whereIn('category_id', [1, 2, 3, 4]);
            } elseif ($filters['category_group'] === 'commercial') {
                $q->where('category_id', 6);
            } elseif ($filters['category_group'] === 'land') {
                $q->where('category_id', 5);
            }
        } elseif (!empty($filters['category_id'])) {
            if (is_string($filters['category_id']) && str_contains($filters['category_id'], ',')) {
                $ids = array_filter(array_map('intval', explode(',', $filters['category_id'])));
                $q->whereIn('category_id', $ids);
            } elseif (is_array($filters['category_id'])) {
                $q->whereIn('category_id', $filters['category_id']);
            } else {
                $q->where('category_id', $filters['category_id']);
            }
        }

        // Student housing filter
        if (!empty($filters['student'])) {
            $q->where(function ($sq) {
                $sq->whereHas('translations', function ($t) {
                    $t->where('name', 'like', '%طلاب%')
                        ->orWhere('description', 'like', '%طلاب%');
                });
            });
        }

        if (!empty($filters['location_id'])) {
            $location = Location::query()->find($filters['location_id']);
            if ($location?->type === 'city') {
                $q->whereIn('location_id', $location->children()->pluck('id')->prepend($location->id));
            } else {
                $q->where('location_id', $filters['location_id']);
            }
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $q->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $q->where('price', '<=', $filters['max_price']);
        }

        if (!empty($filters['bedrooms'])) {
            $q->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if (!empty($filters['bathrooms'])) {
            $q->where('bathrooms', '>=', $filters['bathrooms']);
        }

        // Amenities chosen in the advanced search panel (match all selected).
        if (!empty($filters['features'])) {
            $amenityIds = array_values(array_filter(array_map('intval', (array) $filters['features'])));

            foreach ($amenityIds as $amenityId) {
                $q->whereHas('amenities', fn ($amenity) => $amenity->where('amenities.id', $amenityId));
            }
        }

        if (($filters['furnished'] ?? '') === '1') {
            $q->whereHas('translations', function ($translation) {
                $translation->where(function ($text) {
                    $text->where('name', 'like', '%مفروش%')
                        ->orWhere('description', 'like', '%مفروش%')
                        ->orWhere('name', 'like', '%furnished%')
                        ->orWhere('description', 'like', '%furnished%');
                });
            });
        } elseif (($filters['furnished'] ?? '') === '0') {
            $q->whereDoesntHave('translations', function ($translation) {
                $translation->where(function ($text) {
                    $text->where('name', 'like', '%مفروش%')
                        ->orWhere('description', 'like', '%مفروش%')
                        ->orWhere('name', 'like', '%furnished%')
                        ->orWhere('description', 'like', '%furnished%');
                });
            });
        }

        if (!empty($filters['nearby'])) {
            $q->whereExists(function ($nearby) use ($filters) {
                $nearby->selectRaw('1')
                    ->from('property_nearby_places')
                    ->whereColumn('property_nearby_places.property_id', 'properties.id')
                    ->where('property_nearby_places.type', 'like', '%' . $filters['nearby'] . '%');
            });
        }

        if (!empty($filters['nearby_location_id'])) {
            $nearbyLocation = Location::query()->with('translations')->find($filters['nearby_location_id']);
            if ($nearbyLocation?->type === 'district') {
                $q->where('location_id', $nearbyLocation->id);
            } elseif ($nearbyLocation?->type === 'landmark') {
                $landmarkNames = $nearbyLocation->translations->pluck('name')->filter()->values();
                $q->whereExists(function ($nearby) use ($landmarkNames) {
                    $nearby->selectRaw('1')
                        ->from('property_nearby_places')
                        ->whereColumn('property_nearby_places.property_id', 'properties.id')
                        ->where(function ($types) use ($landmarkNames) {
                            foreach ($landmarkNames as $name) {
                                $types->orWhere('property_nearby_places.type', 'like', '%' . $name . '%');
                            }
                        });
                });
            }
        }

        return match ($filters['sort'] ?? 'newest') {
            'price_asc' => $q->orderBy('price'),
            'price_desc' => $q->orderByDesc('price'),
            'popular' => $q->orderByDesc('views_count'),
            default => $q->orderByDesc('published_at'),
        };
    }
}
