<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteVisitor extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'first_visited_at' => 'datetime',
        'last_active_at' => 'datetime',
        'visits_count' => 'integer',
        'page_views_count' => 'integer',
        'accuracy_meters' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hits(): HasMany
    {
        return $this->hasMany(SiteVisitorHit::class, 'visitor_id')->latest();
    }

    /**
     * اسم المحافظة والمركز مدمجاً بشكل أنيق.
     */
    public function fullLocation(): string
    {
        $parts = array_filter([$this->governorate, $this->city_or_markaz, $this->district]);
        if (empty($parts)) {
            return $this->country_name ?: 'مصر';
        }

        return implode(' - ', $parts);
    }

    /**
     * رابط خرائط جوجل للموقع إن وُجدت الإحداثيات.
     */
    public function googleMapsUrl(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        return null;
    }
}

