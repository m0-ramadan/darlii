<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDeposit extends Model
{
    use HasFactory;

    protected $table = 'finance_deposits';

    protected $fillable = [
        'user_id', 'depositor_name', 'depositor_phone', 'amount', 'currency', 'method', 'reference', 'status', 'notes',
        'admin_notes', 'approved_by', 'approved_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function depositorDisplayName(): string
    {
        return $this->depositor_name ?: ($this->user?->name ?: 'مودع غير مسجل');
    }

    public function depositorDisplayPhone(): string
    {
        return $this->depositor_phone ?: ($this->user?->phone ?: '—');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'قيد المراجعة',
            'approved' => 'معتمدة',
            'rejected' => 'مرفوضة',
            default => $this->status,
        };
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'wallet' => 'محفظة إلكترونية',
            'check' => 'شيك',
            default => 'أخرى',
        };
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
