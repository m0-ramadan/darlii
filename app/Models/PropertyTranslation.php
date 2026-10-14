<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PropertyTranslation extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (PropertyTranslation $translation): void {
            if (filled($translation->slug)) {
                return;
            }

            $locale = (string) ($translation->locale ?: app()->getLocale());
            $baseSlug = Str::slug((string) $translation->name);

            if ($baseSlug === '') {
                $baseSlug = 'property-' . ($translation->property_id ?: Str::lower(Str::random(8)));
            }

            $slug = $baseSlug;
            $suffix = 2;

            while (static::query()->where('locale', $locale)->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $suffix;
                $suffix++;
            }

            $translation->slug = $slug;
        });
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
