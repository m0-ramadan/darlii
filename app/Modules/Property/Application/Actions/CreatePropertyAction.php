<?php

namespace App\Modules\Property\Application\Actions;

use App\Models\Property;
use App\Models\PropertyTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreatePropertyAction
{
    public function execute(array $data, int $ownerId): Property
    {
        return DB::transaction(function () use ($data, $ownerId): Property {
            $translations = $data['translations'] ?? [];

            unset(
                $data['translations'],
                $data['floor_plan_name'],
                $data['floor_plan_image'],
                $data['floor_plan_description'],
                $data['video_url'],
                $data['video_file'],
                $data['images'],
            );

            $data['owner_id'] = $ownerId;
            $data['status'] = 'draft';
            $data['reference_code'] = $data['reference_code']
                ?? ('PROP-' . now()->format('Y') . '-' . strtoupper(Str::random(8)));
            $data['uuid'] = $data['uuid'] ?? (string) Str::uuid();

            $property = Property::create($data);

            foreach ($translations as $locale => $translation) {
                if (! is_array($translation) || blank($translation['name'] ?? null)) {
                    continue;
                }

                // عمود slug إجباري وفريد لكل لغة، والعميل قد لا يرسله.
                $translation['slug'] = $this->uniqueSlug(
                    (string) (($translation['slug'] ?? null) ?: $translation['name']),
                    (string) $locale
                );

                $property->translations()->create($translation + ['locale' => $locale]);
            }

            return $property;
        });
    }

    private function uniqueSlug(string $value, string $locale): string
    {
        $base = Str::slug($value) ?: 'property';
        $slug = $base;
        $suffix = 1;

        while (PropertyTranslation::query()->where('locale', $locale)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$suffix);
        }

        return $slug;
    }
}
