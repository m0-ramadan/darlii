<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentProfile extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'years_experience' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AgentProfileTranslation::class);
    }

    public function translation(?string $locale = null): ?AgentProfileTranslation
    {
        $targetLocale = $locale ?: app()->getLocale();

        return $this->translations->firstWhere('locale', $targetLocale)
            ?? $this->translations->firstWhere('locale', config('app.fallback_locale', 'ar'))
            ?? $this->translations->first();
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->translation()?->display_name
            ?? $this->agency?->name
            ?? $this->user?->name
            ?? $this->slug;
    }

    protected static ?bool $hasAgentTypeColumn = null;
    protected static ?bool $hasContractTermsColumn = null;
    protected array $metaTransient = [];

    public static function hasAgentTypeColumn(): bool
    {
        if (static::$hasAgentTypeColumn === null) {
            try {
                static::$hasAgentTypeColumn = \Illuminate\Support\Facades\Schema::hasColumn('agent_profiles', 'agent_type');
            } catch (\Throwable $e) {
                static::$hasAgentTypeColumn = false;
            }
        }
        return static::$hasAgentTypeColumn;
    }

    public static function hasContractTermsColumn(): bool
    {
        if (static::$hasContractTermsColumn === null) {
            try {
                static::$hasContractTermsColumn = \Illuminate\Support\Facades\Schema::hasColumn('agent_profiles', 'contract_terms');
            } catch (\Throwable $e) {
                static::$hasContractTermsColumn = false;
            }
        }
        return static::$hasContractTermsColumn;
    }

    public function getAgentTypeAttribute(): string
    {
        if (array_key_exists('agent_type', $this->attributes) && filled($this->attributes['agent_type'])) {
            return (string) $this->attributes['agent_type'];
        }

        if (array_key_exists('agent_type', $this->metaTransient)) {
            return (string) $this->metaTransient['agent_type'];
        }

        if ($this->exists) {
            $val = setting("agent.type.{$this->id}");
            if (filled($val)) {
                return (string) $val;
            }
        }

        return 'individual';
    }

    public function setAgentTypeAttribute(?string $value): void
    {
        $val = in_array($value, ['individual', 'office', 'broker'], true) ? $value : 'individual';

        if (static::hasAgentTypeColumn()) {
            $this->attributes['agent_type'] = $val;
        }

        $this->metaTransient['agent_type'] = $val;

        if ($this->exists) {
            try {
                app(\App\Services\Settings\SettingsRepository::class)->set("agent.type.{$this->id}", $val);
            } catch (\Throwable $e) {
                // Ignore fallback save error
            }
        }
    }

    public function getContractTermsAttribute(): ?string
    {
        if (array_key_exists('contract_terms', $this->attributes)) {
            return $this->attributes['contract_terms'];
        }

        if (array_key_exists('contract_terms', $this->metaTransient)) {
            return $this->metaTransient['contract_terms'];
        }

        if ($this->exists) {
            return setting("agent.terms.{$this->id}");
        }

        return null;
    }

    public function setContractTermsAttribute(?string $value): void
    {
        if (static::hasContractTermsColumn()) {
            $this->attributes['contract_terms'] = $value;
        }

        $this->metaTransient['contract_terms'] = $value;

        if ($this->exists) {
            try {
                app(\App\Services\Settings\SettingsRepository::class)->set("agent.terms.{$this->id}", (string) $value);
            } catch (\Throwable $e) {
                // Ignore fallback save error
            }
        }
    }

    public function getAgentTypeLabelAttribute(): string
    {
        return match ($this->agent_type) {
            'office' => 'مكتب عقارات / شركة',
            'broker' => 'سمسار عادي',
            default => 'شخص / مسؤول فردي',
        };
    }

    public function getAgentTypeBadgeClassAttribute(): string
    {
        return match ($this->agent_type) {
            'office' => 'ap-badge--info',
            'broker' => 'ap-badge--gold',
            default => 'ap-badge--secondary',
        };
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }
}
