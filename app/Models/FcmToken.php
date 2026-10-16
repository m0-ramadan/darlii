<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    use HasFactory;

    protected $table = 'fcm_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'user_agent',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getBrowserNameAttribute(): string
    {
        $ua = (string) $this->user_agent;
        if (str_contains($ua, 'HeadlessChrome')) return 'Chrome (Headless)';
        if (str_contains($ua, 'Edg/')) return 'Edge';
        if (str_contains($ua, 'OPR/') || str_contains($ua, 'Opera')) return 'Opera';
        if (str_contains($ua, 'SamsungBrowser')) return 'Samsung Internet';
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari') && !str_contains($ua, 'Chrome')) return 'Safari';
        return 'متصفح ويب';
    }

    public function getPlatformNameAttribute(): string
    {
        $ua = (string) $this->user_agent;
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'iPod')) return 'iOS';
        if (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        return 'غير محدد';
    }

    public function getIsMobileAttribute(): bool
    {
        $ua = (string) $this->user_agent;
        return (bool) preg_match('/(Mobile|Android|iPhone|iPad|iPod|webOS|BlackBerry|IEMobile|Opera Mini)/i', $ua);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->device_type) {
            'web_push' => 'إشعار ويب فوري (Web Push)',
            'web_visitor' => 'رمز زائر (Visitor Token)',
            'android' => 'تطبيق أندرويد (Android)',
            'ios' => 'تطبيق آيفون (iOS)',
            default => $this->device_type ?: 'غير محدد',
        };
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->device_type) {
            'web_push' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
            'web_visitor' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
            'android', 'ios' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
            default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25',
        };
    }

    public function getShortTokenAttribute(): string
    {
        $token = (string) $this->token;
        if (strlen($token) <= 24) {
            return $token;
        }
        return substr($token, 0, 12) . '...' . substr($token, -8);
    }
}

