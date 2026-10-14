<?php
namespace App\Models;
use App\Modules\Property\Domain\Enums\PropertyStatus;
use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\SoftDeletes;
class Property extends Model {use SoftDeletes; protected $guarded=[];
 protected function casts():array{return ['status'=>PropertyStatus::class,'price'=>'decimal:4','is_featured'=>'boolean','published_at'=>'datetime','expires_at'=>'datetime','featured_until'=>'datetime','last_assigned_at'=>'datetime','sold_at'=>'datetime','rented_at'=>'datetime'];}
 public function translations(){return $this->hasMany(PropertyTranslation::class);} public function translation(?string $locale=null){$locale=$locale?:app()->getLocale();return $this->translations->firstWhere('locale',$locale)??$this->translations->firstWhere('locale',config('app.fallback_locale'))??$this->translations->first();}
 public function amenities(){return $this->belongsToMany(Amenity::class,'property_amenity');} public function currency(){return $this->belongsTo(Currency::class);} public function category(){return $this->belongsTo(PropertyCategory::class,'category_id');} public function owner(){return $this->belongsTo(User::class,'owner_id');} public function location(){return $this->belongsTo(Location::class);} public function project(){return $this->belongsTo(Project::class);} public function media(){return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');} public function statusHistory(){return $this->hasMany(PropertyStatusHistory::class);}
 public function creator(){return $this->belongsTo(User::class,'created_by');}
 public function manager(){return $this->belongsTo(User::class,'managed_by');}
 public function propertyType(){return $this->belongsTo(PropertyType::class,'property_type_id');}
 public function leads(){return $this->hasMany(Lead::class);}
 public function reviews(){return $this->morphMany(Review::class,'reviewable');}
 public function appointments(){return $this->hasMany(Appointment::class);}
 /** Effective Arabic (or requested locale) name with a safe fallback. */
 public function displayName(?string $locale=null):string
 {
  return (string) ($this->translation($locale)?->name ?: ('#' . $this->reference_code));
 }
 public function slug(?string $locale=null):string
 {
  return (string) ($this->translation($locale)?->slug ?: $this->reference_code);
 }
 /** Arabic label for the assignment bucket. */
 public function assignmentLabel():string
 {
  return match ($this->assignment_status) {
   'assigned' => 'مُسند',
   'unassigned' => 'غير مُسند',
   default => (string) $this->assignment_status,
  };
 }
 public static function defaultPlaceholderPath(?string $locale = null): string {
        $loc = $locale ?: app()->getLocale();
        return ($loc === 'ar') ? 'themes/homzen/images/darli-placeholder.png' : 'themes/homzen/images/darli-placeholder-en.png';
    }
    public function placeholderUrl(?string $locale = null): string {
        return asset(self::defaultPlaceholderPath($locale));
    }
    /** Public URL of the primary image, checking the storage disk as well. */
    public function primaryImageUrl(?string $locale = null): string {
        $media = $this->relationLoaded('media') ? $this->media : $this->media()->get();
        $primary = $media->firstWhere('is_primary', true) ?: $media->first();

        if (! $primary) {
            return $this->placeholderUrl($locale);
        }

        if (str_starts_with((string) $primary->path, 'http')) {
            return $primary->path;
        }

        if (($primary->disk ?? 'public') === 'legacy') {
            return asset('legacy/' . ltrim($primary->path, '/'));
        }

        return asset('storage/' . ltrim($primary->path, '/'));
    }

    public function firstImageUrl(?string $locale = null): string {
        return $this->primaryImageUrl($locale);
    }

    protected static ?bool $hasPriceTypeColumn = null;
    protected static ?bool $hasPriceNotesColumn = null;
    protected static array $priceMetaCache = [];
    protected array $priceMetaTransient = [];

    protected static function booted(): void
    {
        static::saved(function (Property $property) {
            if (! empty($property->priceMetaTransient)) {
                $property->setPriceMeta($property->priceMetaTransient);
                $property->priceMetaTransient = [];
            }
            if (! empty($property->floorPlansTransient)) {
                $property->setFloorPlans($property->floorPlansTransient);
                $property->floorPlansTransient = [];
            }
        });
    }

    public static function hasPriceTypeColumn(): bool
    {
        if (static::$hasPriceTypeColumn === null) {
            try {
                static::$hasPriceTypeColumn = \Illuminate\Support\Facades\Schema::hasColumn('properties', 'price_type');
            } catch (\Throwable $e) {
                static::$hasPriceTypeColumn = false;
            }
        }
        return static::$hasPriceTypeColumn;
    }

    public static function hasPriceNotesColumn(): bool
    {
        if (static::$hasPriceNotesColumn === null) {
            try {
                static::$hasPriceNotesColumn = \Illuminate\Support\Facades\Schema::hasColumn('properties', 'price_notes');
            } catch (\Throwable $e) {
                static::$hasPriceNotesColumn = false;
            }
        }
        return static::$hasPriceNotesColumn;
    }

    public function getPriceTypeAttribute(): string
    {
        if (array_key_exists('price_type', $this->attributes) && filled($this->attributes['price_type'])) {
            return (string) $this->attributes['price_type'];
        }

        $meta = $this->getPriceMeta();
        return $meta['price_type'] ?? 'fixed';
    }

    public function setPriceTypeAttribute(?string $value): void
    {
        $type = in_array($value, ['fixed', 'negotiable', 'contact'], true) ? $value : 'fixed';
        if (static::hasPriceTypeColumn()) {
            $this->attributes['price_type'] = $type;
        }

        $meta = $this->getPriceMeta();
        $meta['price_type'] = $type;
        $this->setPriceMeta($meta);
    }

    public function getPriceNotesAttribute(): ?string
    {
        if (array_key_exists('price_notes', $this->attributes)) {
            return $this->attributes['price_notes'];
        }

        $meta = $this->getPriceMeta();
        return $meta['price_notes'] ?? null;
    }

    public function setPriceNotesAttribute(?string $value): void
    {
        if (static::hasPriceNotesColumn()) {
            $this->attributes['price_notes'] = $value;
        }

        $meta = $this->getPriceMeta();
        $meta['price_notes'] = $value;
        $this->setPriceMeta($meta);
    }

    public function isSold(): bool
    {
        $statusVal = $this->status instanceof \BackedEnum ? $this->status->value : (string) $this->status;
        return $statusVal === 'sold' || $this->sold_at !== null;
    }

    public function isNegotiable(): bool
    {
        return $this->price_type === 'negotiable';
    }

    public function isPriceOnContact(): bool
    {
        return $this->price_type === 'contact';
    }

    public function isFixedPrice(): bool
    {
        return $this->price_type === 'fixed' || blank($this->price_type);
    }

    public function priceTypeLabel(?string $locale = null): string
    {
        $isAr = ($locale ?: app()->getLocale()) === 'ar';
        return match ($this->price_type) {
            'negotiable' => $isAr ? 'قابل للتفاوض' : 'Negotiable',
            'contact' => $isAr ? 'يتم تحديده من خلال التواصل' : 'Price on request',
            default => $isAr ? 'محدد' : 'Fixed',
        };
    }

    public function displayPrice(?string $locale = null): string
    {
        $isAr = ($locale ?: app()->getLocale()) === 'ar';
        if ($this->isPriceOnContact()) {
            return $isAr ? 'السعر عند التواصل' : 'Price on request';
        }

        $currency = $isAr ? 'جنيه' : 'EGP';
        return number_format((float) $this->price, 0) . ' ' . $currency;
    }

    public function getPriceMeta(): array
    {
        if (! $this->exists || ! $this->id) {
            return array_merge(['price_type' => 'fixed', 'price_notes' => null], $this->priceMetaTransient);
        }

        if (isset(static::$priceMetaCache[$this->id])) {
            return static::$priceMetaCache[$this->id];
        }

        $setting = Setting::query()
            ->where('group', 'property_price_meta')
            ->where('key', 'property_' . $this->id)
            ->first();

        $data = ['price_type' => 'fixed', 'price_notes' => null];
        if ($setting && $setting->value) {
            $decoded = json_decode((string) $setting->value, true);
            if (is_array($decoded)) {
                $data = array_merge($data, $decoded);
            }
        }

        static::$priceMetaCache[$this->id] = $data;
        return $data;
    }

    public function setPriceMeta(array $meta): void
    {
        $meta = array_merge(['price_type' => 'fixed', 'price_notes' => null], $meta);

        if (! $this->exists || ! $this->id) {
            $this->priceMetaTransient = $meta;
            return;
        }

        static::$priceMetaCache[$this->id] = $meta;

        Setting::updateOrCreate(
            ['group' => 'property_price_meta', 'key' => 'property_' . $this->id],
            [
                'value' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'is_public' => false,
                'label_ar' => 'بيانات تسعير وتفاوض العقار #' . $this->id,
            ]
        );
    }

    public static function warmPriceMetaCache($properties): void
    {
        $ids = collect($properties)->pluck('id')->filter()->diff(array_keys(static::$priceMetaCache))->all();
        if (empty($ids)) {
            return;
        }
        $keys = array_map(fn ($id) => 'property_' . $id, $ids);
        $settings = Setting::query()->where('group', 'property_price_meta')->whereIn('key', $keys)->get();
        foreach ($settings as $setting) {
            $id = (int) str_replace('property_', '', (string) $setting->key);
            $decoded = json_decode((string) $setting->value, true);
            static::$priceMetaCache[$id] = is_array($decoded) ? $decoded : ['price_type' => 'fixed', 'price_notes' => null];
        }
        foreach ($ids as $id) {
            if (! isset(static::$priceMetaCache[$id])) {
                static::$priceMetaCache[$id] = ['price_type' => 'fixed', 'price_notes' => null];
            }
        }
    }

    public static function clearPriceMetaCache(): void
    {
        static::$priceMetaCache = [];
    }

    protected static array $floorPlansCache = [];
    protected array $floorPlansTransient = [];

    public function getFloorPlans(): array
    {
        if (! $this->exists || ! $this->id) {
            return $this->floorPlansTransient;
        }

        if (isset(static::$floorPlansCache[$this->id])) {
            return static::$floorPlansCache[$this->id];
        }

        $setting = Setting::query()
            ->where('group', 'property_floor_plans')
            ->whereIn('key', ['floor_plans_' . $this->id, 'property_' . $this->id])
            ->first();

        $plans = [];
        if ($setting && $setting->value) {
            $decoded = json_decode((string) $setting->value, true);
            if (is_array($decoded)) {
                $plans = $decoded;
            }
        }

        if (empty($plans)) {
            $mediaPlans = $this->relationLoaded('media')
                ? $this->media->where('type', 'floor_plan')
                : $this->media()->where('type', 'floor_plan')->get();

            foreach ($mediaPlans as $mp) {
                $plans[] = [
                    'name' => $mp->alt_text ?: 'المخطط الهندسي',
                    'bedrooms' => $this->bedrooms,
                    'bathrooms' => $this->bathrooms,
                    'area' => $this->area,
                    'image' => $mp->path,
                    'description' => $mp->caption,
                ];
            }
        }

        foreach ($plans as &$plan) {
            $img = $plan['image'] ?? null;
            if ($img) {
                if (str_starts_with($img, 'http') || str_starts_with($img, '/')) {
                    $plan['image_url'] = $img;
                } else {
                    $plan['image_url'] = asset('storage/' . ltrim($img, '/'));
                }
            } else {
                $plan['image_url'] = asset('storage/properties/floor.png');
            }
        }
        unset($plan);

        static::$floorPlansCache[$this->id] = $plans;
        return $plans;
    }

    public function getFloorPlansAttribute(): array
    {
        return $this->getFloorPlans();
    }

    public function setFloorPlans(array $plans): void
    {
        if (! $this->exists || ! $this->id) {
            $this->floorPlansTransient = $plans;
            return;
        }

        static::$floorPlansCache[$this->id] = $plans;

        Setting::updateOrCreate(
            ['group' => 'property_floor_plans', 'key' => 'floor_plans_' . $this->id],
            [
                'value' => json_encode($plans, JSON_UNESCAPED_UNICODE),
                'type' => 'json',
                'is_public' => true,
                'label_ar' => 'التخطيط الهندسي للعقار #' . $this->id,
            ]
        );

        // Clean up legacy key if existed under property_floor_plans
        Setting::query()
            ->where('group', 'property_floor_plans')
            ->where('key', 'property_' . $this->id)
            ->delete();
    }

    public static function clearFloorPlansCache(): void
    {
        static::$floorPlansCache = [];
    }
}

