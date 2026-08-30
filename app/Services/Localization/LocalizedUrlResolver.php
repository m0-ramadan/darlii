<?php

namespace App\Services\Localization;

use App\Models\CareerTranslation;
use App\Models\PostTranslation;
use App\Models\ProjectTranslation;
use App\Models\PropertyTranslation;
use Throwable;

class LocalizedUrlResolver
{
    private array $cache = [];

    public function forLocale(string $path, string $locale): string
    {
        $supported = config('locales.supported', ['ar']);
        if (! in_array($locale, $supported, true)) {
            return trim($path, '/');
        }

        $segments = array_values(array_filter(
            explode('/', trim($path, '/')),
            static fn (string $segment): bool => $segment !== ''
        ));

        if ($segments === []) {
            return $locale;
        }

        if (in_array(strtolower($segments[0]), $supported, true)) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        return $this->translateEntityPath(implode('/', $segments));
    }

    public function translateEntityPath(string $path): string
    {
        if (! preg_match('~^(en|ar|fr|id|vi)/(properties|projects|blog|news|careers)/([^/?#]+)$~i', $path, $matches)) {
            return $path;
        }

        [, $locale, $type, $slug] = $matches;
        $cacheKey = strtolower($locale . '|' . $type . '|' . $slug);

        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        [$model, $foreignKey] = match (strtolower($type)) {
            'properties' => [PropertyTranslation::class, 'property_id'],
            'projects' => [ProjectTranslation::class, 'project_id'],
            'blog', 'news' => [PostTranslation::class, 'post_id'],
            'careers' => [CareerTranslation::class, 'career_id'],
        };

        try {
            $entityId = $model::where('slug', $slug)->value($foreignKey);
            $translatedSlug = $entityId
                ? $model::where($foreignKey, $entityId)->where('locale', strtolower($locale))->value('slug')
                : null;
        } catch (Throwable) {
            $translatedSlug = null;
        }

        return $this->cache[$cacheKey] = $translatedSlug
            ? strtolower($locale) . '/' . strtolower($type) . '/' . $translatedSlug
            : $path;
    }

    public function propertyIdForSlug(string $slug): ?int
    {
        $cacheKey = 'property-id|' . $slug;
        if (array_key_exists($cacheKey, $this->cache)) {
            return $this->cache[$cacheKey];
        }

        try {
            $id = PropertyTranslation::where('slug', $slug)->value('property_id');
        } catch (Throwable) {
            $id = null;
        }

        return $this->cache[$cacheKey] = $id ? (int) $id : null;
    }

    public function legacyMirrorPath(string $locale, string $path): string
    {
        if (! preg_match('~^(properties|projects|blog|news|careers)/([^/?#]+)$~i', $path, $matches)) {
            return $path;
        }

        [, $type, $slug] = $matches;
        [$model, $foreignKey] = match (strtolower($type)) {
            'properties' => [PropertyTranslation::class, 'property_id'],
            'projects' => [ProjectTranslation::class, 'project_id'],
            'blog', 'news' => [PostTranslation::class, 'post_id'],
            'careers' => [CareerTranslation::class, 'career_id'],
        };

        try {
            $entityId = $model::where('slug', $slug)->value($foreignKey);
            $englishSlug = $entityId
                ? $model::where($foreignKey, $entityId)->where('locale', 'en')->value('slug')
                : null;
        } catch (Throwable) {
            $englishSlug = null;
        }

        return $englishSlug ? strtolower($type) . '/' . $englishSlug : $path;
    }
}
