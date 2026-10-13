<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Lightweight activity logger for the CMS ("سجل النشاط").
 */
class AuditLogger
{
    public static function log(string $event, ?Model $subject = null, array $old = [], array $new = [], ?string $description = null): void
    {
        try {
            AuditLog::query()->create([
                'user_id' => Auth::id(),
                'event' => $event,
                'auditable_type' => $subject ? $subject::class : null,
                'auditable_id' => $subject?->getKey(),
                'old_values' => $old ?: null,
                'new_values' => $new ?: null,
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 500) . ($description ? ' | ' . $description : ''),
            ]);
        } catch (\Throwable $e) {
            // Never let logging break a request.
        }
    }
}
