<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceExpense extends Model
{
    use HasFactory;

    protected $table = 'finance_expenses';

    public const CATEGORIES = [
        'rent' => 'إيجارات',
        'salaries' => 'رواتب وأجور',
        'marketing' => 'تسويق وإعلانات',
        'maintenance' => 'صيانة وتشغيل',
        'utilities' => 'مرافق (كهرباء/مياه/إنترنت)',
        'commissions' => 'عمولات',
        'office' => 'مصروفات مكتبية',
        'other' => 'أخرى',
    ];

    public const METHODS = [
        'cash' => 'نقدي',
        'bank_transfer' => 'تحويل بنكي',
        'wallet' => 'محفظة إلكترونية',
        'check' => 'شيك',
        'other' => 'أخرى',
    ];

    protected $fillable = [
        'title', 'category', 'amount', 'currency', 'method', 'reference',
        'expense_date', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }
}
