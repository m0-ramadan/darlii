<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancePointBalance extends Model
{
    use HasFactory;

    protected $table = 'finance_point_balances';

    protected $fillable = [
        'user_id', 'point_type_id', 'balance', 'total_earned', 'total_spent', 'last_transaction_at',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'total_earned' => 'integer',
            'total_spent' => 'integer',
            'last_transaction_at' => 'datetime',
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
}
