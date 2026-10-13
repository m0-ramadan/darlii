<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Career extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CareerTranslation::class);
    }

    public function translation(?string $locale = null): ?CareerTranslation
    {
        $targetLocale = $locale ?: app()->getLocale();

        return $this->translations->firstWhere('locale', $targetLocale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale', 'ar'))
            ?? $this->translations->first();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function getTitleAttribute(): string
    {
        return $this->translation()?->title ?? 'وظيفة #' . $this->id;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
