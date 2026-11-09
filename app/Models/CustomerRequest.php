<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerRequest extends Model
{
    use HasFactory;

    public const STATUSES = [
        'new' => 'جديد',
        'contacted' => 'تم التواصل',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'تم',
        'cancelled' => 'ملغي',
    ];

    protected $fillable = [
        'customer_name', 'phone', 'need', 'location_id', 'address_details',
        'status', 'notes', 'created_by', 'updated_by',
    ];

    /** الأدمن/السوبر أدمن يرون الكل، وأي حساب آخر يرى طلباته فقط. */
    public function scopeVisibleTo($query, User $user)
    {
        return $user->isAdmin() ? $query : $query->where('created_by', $user->id);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'contacted' => 'info',
            'in_progress' => 'warn',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function locationName(?string $locale = null): string
    {
        $location = $this->location;
        if (! $location) {
            return 'غير محدد';
        }

        $name = $location->translation($locale)?->name ?: ('#' . $location->id);
        $parent = $location->parent?->translation($locale)?->name;

        return $parent ? $parent . ' — ' . $name : $name;
    }
}
