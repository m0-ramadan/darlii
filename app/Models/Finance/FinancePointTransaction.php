<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancePointTransaction extends Model
{
    use HasFactory;

    protected $table = 'finance_point_transactions';

    protected $fillable = [
        'user_id', 'point_type_id', 'points', 'balance_after', 'source', 'description',
        'rule_id', 'reference_type', 'reference_id', 'created_by', 'occurred_at', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'balance_after' => 'integer',
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pointType(): BelongsTo
    {
        return $this->belongsTo(FinancePointType::class, 'point_type_id');
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(FinancePointRule::class, 'rule_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sourceLabel(): string
    {
        return match ($this->source) {
            'manual' => 'تعديل يدوي',
            'recurring' => 'نقاط دورية',
            'deposit' => 'وديعة',
            'adjustment' => 'تسوية',
            'reversal' => 'عكس حركة',
            default => $this->source,
        };
    }
}
