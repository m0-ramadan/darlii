<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePointType extends Model
{
    use HasFactory;

    protected $table = 'finance_point_types';

    protected $fillable = [
        'name', 'slug', 'description', 'category', 'unit_label', 'default_points',
        'is_active', 'sort_order', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'default_points' => 'integer', 'sort_order' => 'integer'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(FinancePointRule::class, 'point_type_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancePointTransaction::class, 'point_type_id');
    }

    public function balances(): HasMany
    {
        return $this->hasMany(FinancePointBalance::class, 'point_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
