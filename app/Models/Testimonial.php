<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function translations()
    {
        return $this->hasMany(TestimonialTranslation::class);
    }

    public function translation(?string $locale = null): ?TestimonialTranslation
    {
        $locale ??= app()->getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale'))
            ?? $this->translations->first();
    }
}
