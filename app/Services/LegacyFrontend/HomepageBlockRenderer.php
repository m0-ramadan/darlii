<?php

namespace App\Services\LegacyFrontend;

use App\Models\AgentProfile;
use App\Models\Currency;
use App\Models\Favorite;
use App\Models\Location;
use App\Models\Post;
use App\Models\Property;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HomepageBlockRenderer
{
    public const BLOCKS = [
        'properties',
        'services',
        'location',
        'agents',
        'testimonials',
        'blog-posts',
        'call-to-action',
    ];

    public function render(string $name, array $attributes, string $locale, ?string $shortcodeId = null): string
    {
        $shared = [
            'attributes' => $attributes,
            'locale' => $locale,
            'shortcodeId' => $shortcodeId ?: 'homepage-' . $name,
        ];

        return match ($name) {
            'properties' => view('legacy.blocks.properties', $shared + [
                'properties' => $this->properties($attributes, $locale),
                'style' => in_array((string) ($attributes['style'] ?? '5'), ['5', '6'], true)
                    ? (string) ($attributes['style'] ?? '5')
                    : '5',
                'backgroundImage' => $this->storageUrl($attributes['background_image'] ?? null),
            ])->render(),
            'services' => view('legacy.blocks.services', $shared + [
                'services' => $this->services($attributes, $locale),
                'style' => (string) ($attributes['style'] ?? '4'),
                'backgroundImage' => $this->storageUrl($attributes['background_image'] ?? null),
            ])->render(),
            'location' => view('legacy.blocks.locations', $shared + [
                'locations' => $this->locations($locale),
            ])->render(),
            'agents' => view('legacy.blocks.agents', $shared + [
                'agents' => $this->agents($attributes, $locale),
            ])->render(),
            'testimonials' => view('legacy.blocks.testimonials', $shared + [
                'testimonials' => $this->testimonials($attributes, $locale),
            ])->render(),
            'blog-posts' => view('legacy.blocks.blog-posts', $shared + [
                'posts' => $this->posts($attributes, $locale),
            ])->render(),
            'call-to-action' => view('legacy.blocks.call-to-action', $shared + [
                'image' => $this->storageUrl($attributes['image'] ?? null),
                'buttonUrl' => $this->localizedUrl((string) ($attributes['button_url'] ?? '/contact'), $locale),
            ])->render(),
            default => '',
        };
    }

    private function properties(array $attributes, string $locale): Collection
    {
        $limit = min(max((int) ($attributes['limit'] ?? 6), 1), 12);
        $query = Property::query()
            ->with(['translations', 'currency', 'media', 'category.translations', 'owner.agentProfile'])
            ->whereIn('status', ['published', 'sold'])
            ->where(fn ($builder) => $builder->whereNull('expires_at')->orWhere('expires_at', '>', now()));

        if (! empty($attributes['is_featured'])) {
            $query->where('is_featured', true)
                ->where(fn ($builder) => $builder->whereNull('featured_until')->orWhere('featured_until', '>', now()));
        }

        $favoriteIds = auth()->check()
            ? Favorite::query()
                ->where('user_id', auth()->id())
                ->where('favoritable_type', Property::class)
                ->pluck('favoritable_id')
                ->map(fn ($id) => (int) $id)
                ->all()
            : [];
        $targetCurrency = $this->currency();

        return $query->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(function (Property $property) use ($locale, $favoriteIds, $targetCurrency): array {
                $translation = $property->translation($locale);
                $category = $property->category?->translation($locale);
                $agent = $property->owner?->agentProfile;
                $areaUnit = $property->area_unit === 'm2' ? 'm²' : $property->area_unit;
                $purpose = $this->purpose($property->purpose, $locale);

                return [
                    'id' => $property->id,
                    'name' => $translation?->name ?? $property->reference_code,
                    'url' => '/' . $locale . '/properties/' . ($translation?->slug ?? $property->id),
                    'image' => $this->mediaUrl($property->media->first()?->path),
                    'address' => $translation?->address_text ?? '',
                    'category' => $category?->name ?? $purpose['label'],
                    'purpose' => $purpose['label'],
                    'purpose_class' => $purpose['class'],
                    'featured' => (bool) $property->is_featured,
                    'is_sold' => $property->isSold(),
                    'bedrooms' => $property->bedrooms ?? 0,
                    'bathrooms' => $property->bathrooms ?? 0,
                    'area' => $property->area ? rtrim(rtrim(number_format((float) $property->area, 2, '.', ''), '0'), '.') . ' ' . $areaUnit : '',
                    'price' => $this->price($property, $targetCurrency),
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                    'owner_name' => $property->owner?->name ?? $this->copy('Agent', $locale),
                    'owner_avatar' => $this->mediaUrl($agent?->avatar_path),
                    'owner_verified' => (bool) $agent?->verified_at,
                    'favorite' => in_array($property->id, $favoriteIds, true),
                ];
            });
    }

    private function services(array $attributes, string $locale): Collection
    {
        $quantity = min(max((int) ($attributes['services_quantity'] ?? 0), 0), 12);

        return collect(range(1, $quantity))->map(fn (int $index): array => [
            'title' => (string) ($attributes["services_title_{$index}"] ?? ''),
            'description' => (string) ($attributes["services_description_{$index}"] ?? ''),
            'image' => $this->storageUrl($attributes["services_icon_image_{$index}"] ?? null),
            'icon' => $this->serviceIcon((string) ($attributes["services_icon_{$index}"] ?? ''), $index),
            'button_label' => (string) ($attributes["services_button_label_{$index}"] ?? $this->copy('Learn More', $locale)),
            'button_url' => $this->localizedUrl((string) ($attributes["services_button_url_{$index}"] ?? '/contact'), $locale),
        ])->filter(fn (array $service): bool => $service['title'] !== '')->values();
    }

    private function locations(string $locale): Collection
    {
        return Location::query()
            ->with('translations')
            ->where('type', 'governorate')
            ->where('country_code', 'EG')
            ->where('status', true)
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->values()
            ->map(function (Location $location, int $index) use ($locale): array {
                $translation = $location->translation($locale);

                return [
                    'name' => $translation?->name ?? '',
                    'key' => $translation?->slug ?? 'cairo',
                    'url' => '/' . $locale . '/properties?governorate=' . urlencode($translation?->slug ?? 'cairo'),
                    'image' => '/storage/locations/' . ($index % 2 === 0 ? '5-400x560.jpg' : '3-400x560.jpg'),
                ];
            });
    }

    private function agents(array $attributes, string $locale): Collection
    {
        $ids = collect(explode(',', (string) ($attributes['account_ids'] ?? '')))
            ->filter(fn (string $id): bool => ctype_digit(trim($id)))
            ->map(fn (string $id): int => (int) $id)
            ->take(12)
            ->values();
        $query = AgentProfile::query()
            ->with(['translations', 'user'])
            ->where('status', 'active');

        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids);
        }

        $agents = $query->limit($ids->isNotEmpty() ? $ids->count() : 3)->get();
        if ($ids->isNotEmpty()) {
            $agents = $agents->sortBy(fn (AgentProfile $agent) => $ids->search($agent->id))->values();
        }

        return $agents->map(function (AgentProfile $agent) use ($locale): array {
            $translation = $agent->translation($locale);
            $name = $translation?->display_name ?? $agent->user?->name ?? $agent->slug;

            return [
                'name' => $name,
                'url' => '/' . $locale . '/agents/' . $agent->slug,
                'image' => $this->mediaUrl($agent->avatar_path),
                'email' => $agent->user?->email,
                'phone' => $agent->user?->phone,
                'whatsapp' => $agent->whatsapp,
                'facebook' => $agent->facebook,
                'instagram' => $agent->instagram,
                'linkedin' => $agent->linkedin,
                'verified' => (bool) $agent->verified_at,
                'properties_count' => Property::query()->where('owner_id', $agent->user_id)->where('status', 'published')->count(),
            ];
        });
    }

    private function testimonials(array $attributes, string $locale): Collection
    {
        $ids = collect(explode(',', (string) ($attributes['testimonial_ids'] ?? '')))
            ->filter(fn (string $id): bool => ctype_digit(trim($id)))
            ->map(fn (string $id): int => (int) $id)
            ->values();
        $query = Testimonial::query()->with('translations')->where('status', true);
        if ($ids->isNotEmpty()) {
            $query->whereIn('id', $ids);
        }

        return $query->orderBy('sort_order')->limit(12)->get()->map(function (Testimonial $testimonial) use ($locale): array {
            $translation = $testimonial->translation($locale);

            return [
                'name' => $translation?->name ?? '',
                'position' => $translation?->position ?? '',
                'content' => $translation?->content ?? '',
                'image' => $this->mediaUrl($testimonial->image_path),
                'rating' => min(max($testimonial->rating, 1), 5),
            ];
        });
    }

    private function posts(array $attributes, string $locale): Collection
    {
        $limit = min(max((int) ($attributes['limit'] ?? 3), 1), 12);

        return Post::query()
            ->with(['translations', 'author'])
            ->where('status', 'published')
            ->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(function (Post $post) use ($locale): array {
                $translation = $post->translation($locale);

                return [
                    'title' => $translation?->title ?? '',
                    'excerpt' => Str::limit(strip_tags((string) ($translation?->excerpt ?? '')), 130),
                    'url' => '/' . $locale . '/blog/' . ($translation?->slug ?? $post->id),
                    'image' => $this->mediaUrl($post->featured_image),
                    'date' => $post->published_at?->translatedFormat('M d, Y') ?? '',
                    'author' => $post->author?->name ?? 'System Admin',
                ];
            });
    }

    private function currency(): ?Currency
    {
        $code = request()->hasSession() ? request()->session()->get('currency') : null;

        return Currency::query()
            ->where('code', $code ?: '')
            ->where('is_active', true)
            ->first()
            ?? Currency::query()->where('is_default', true)->first()
            ?? Currency::query()->where('is_active', true)->first();
    }

    private function price(Property $property, ?Currency $target): string
    {
        $sourceRate = (float) ($property->currency?->exchange_rate ?: 1);
        $targetRate = (float) ($target?->exchange_rate ?: 1);
        $amount = (float) $property->price / $sourceRate * $targetRate;
        $number = number_format($amount, $target?->decimal_places ?? 0);
        $locale = app()->getLocale();
        if ($target?->code === 'EGP') {
            $symbol = $locale === 'ar' ? 'ج.م' : 'EGP';
        } else {
            $symbol = $target?->symbol ?? $property->currency?->symbol ?? '$';
        }

        return ($target?->symbol_position ?? 'before') === 'after' ? $number . ' ' . $symbol : $symbol . $number;
    }

    private function purpose(string $purpose, string $locale): array
    {
        $labels = [
            'sale' => ['en' => 'Selling', 'ar' => 'للبيع', 'fr' => 'À vendre', 'id' => 'Dijual', 'vi' => 'Đang bán'],
            'rent' => ['en' => 'Renting', 'ar' => 'للإيجار', 'fr' => 'À louer', 'id' => 'Disewakan', 'vi' => 'Cho thuê'],
        ];

        return [
            'label' => $labels[$purpose][$locale] ?? $labels[$purpose]['en'] ?? ucfirst($purpose),
            'class' => $purpose === 'rent' ? 'renting' : 'selling',
        ];
    }

    private function copy(string $copy, string $locale): string
    {
        return match ($copy) {
            'Agent' => ['ar' => 'الوكيل', 'fr' => 'Agent', 'id' => 'Agen', 'vi' => 'Đại lý'][$locale] ?? 'Agent',
            'Learn More' => ['ar' => 'اعرف المزيد', 'fr' => 'En savoir plus', 'id' => 'Pelajari lebih lanjut', 'vi' => 'Tìm hiểu thêm'][$locale] ?? 'Learn More',
            default => $copy,
        };
    }

    private function localizedUrl(string $url, string $locale): string
    {
        if (Str::startsWith($url, ['http://', 'https://', '#', 'mailto:', 'tel:'])) {
            return $url;
        }

        $path = '/' . ltrim($url, '/');

        return '/' . $locale . ($path === '/' ? '' : $path);
    }

    private function storageUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        return '/storage/' . ltrim(preg_replace('~^storage/~', '', $path), '/');
    }

    private function mediaUrl(?string $path): string
    {
        if (! is_string($path) || trim($path) === '') {
            return app()->getLocale() === 'ar'
                ? '/themes/homzen/images/darli-placeholder.png'
                : '/themes/homzen/images/darli-placeholder-en.png';
        }

        return '/' . ltrim($path, '/');
    }

    private function serviceIcon(string $icon, int $index): string
    {
        return match (true) {
            str_contains($icon, 'currency') => 'icon-sale-home',
            str_contains($icon, 'building') => 'icon-commercial',
            str_contains($icon, 'settings') => 'icon-faders',
            str_contains($icon, 'chart') => 'icon-review',
            str_contains($icon, 'credit') => 'icon-secure',
            str_contains($icon, 'briefcase') => 'icon-practice',
            str_contains($icon, 'truck') => 'icon-support',
            default => ['icon-home', 'icon-rent-home', 'icon-sale-home'][$index % 3],
        };
    }
}
