<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceWallet extends Model
{
    use HasFactory;

    protected $table = 'finance_wallets';

    protected $fillable = [
        'user_id', 'cash_balance', 'total_deposited', 'points_balance',
        'total_points_earned', 'total_points_spent',
    ];

    protected function casts(): array
    {
        return [
            'cash_balance' => 'decimal:2',
            'total_deposited' => 'decimal:2',
            'points_balance' => 'integer',
            'total_points_earned' => 'integer',
            'total_points_spent' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
