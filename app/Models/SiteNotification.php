<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteNotification extends Model
{
    use HasFactory;

    protected $table = 'site_notifications';

    protected $fillable = [
        'user_id',
        'target_type',
        'title',
        'body',
        'image_path',
        'action_url',
        'fcm_status',
        'sent_count',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'sent_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'storage/')) {
            return asset($this->image_path);
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }

    public function scopeForUser($query, ?int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('target_type', 'all');
            if ($userId) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}

