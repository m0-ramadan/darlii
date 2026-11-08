<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePointRule extends Model
{
    use HasFactory;

    protected $table = 'finance_point_rules';

    protected $fillable = [
        'point_type_id', 'name', 'description', 'points', 'frequency', 'interval_count',
        'target_type', 'target_role', 'starts_at', 'ends_at', 'next_run_at', 'last_run_at',
        'is_active', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'interval_count' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function pointType(): BelongsTo
    {
        return $this->belongsTo(FinancePointType::class, 'point_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'finance_point_rule_user', 'rule_id', 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancePointTransaction::class, 'rule_id');
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('next_run_at')->orWhere('next_run_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency) {
            'daily' => 'يومي',
            'weekly' => 'أسبوعي',
            'monthly' => 'شهري',
            'yearly' => 'سنوي',
            default => $this->frequency,
        };
    }
}
