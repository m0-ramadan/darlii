<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Cached read/write access to the grouped settings store that backs the
 * "الإعدادات" and "التكاملات" pages of the CMS.
 */
class SettingsRepository
{
    public const CACHE_KEY = 'darlii.settings.all';

    /** @var array<string, mixed>|null */
    private ?array $memo = null;

    /** @return array<string, mixed> */
    public function all(): array
    {
        if ($this->memo !== null) {
            return $this->memo;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return $this->memo = [];
            }

            return $this->memo = Cache::rememberForever(self::CACHE_KEY, function (): array {
                return Setting::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable $e) {
            return $this->memo = [];
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        return match ($this->type($key)) {
            'boolean' => (bool) $value,
            'integer', 'number' => (int) $value,
            'json', 'array' => json_decode((string) $value, true) ?? $default,
            default => $value,
        };
    }

    public function type(string $key): string
    {
        try {
            if (! Schema::hasTable('settings')) {
                return 'text';
            }

            return (string) (Setting::query()->where('key', $key)->value('type') ?? 'text');
        } catch (\Throwable $e) {
            return 'text';
        }
    }

    /** @return array<string, mixed> */
    public function group(string $group): array
    {
        try {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Setting::query()
                ->where('group', $group)
                ->orderBy('sort_order')
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function set(string $key, mixed $value): void
    {
        $payload = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;

        $setting = Setting::query()->firstOrNew(['key' => $key]);
        $setting->value = is_bool($payload) ? ($payload ? '1' : '0') : (string) $payload;

        if (! $setting->exists) {
            $setting->group = str_contains($key, '.') ? explode('.', $key)[0] : 'general';
        }

        $setting->save();

        $this->flush();
    }

    /** @param array<string, mixed> $values */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            $setting = Setting::query()->firstOrNew(['key' => $key]);
            $setting->value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

            if (! $setting->exists) {
                $setting->group = str_contains($key, '.') ? explode('.', $key)[0] : 'general';
            }

            $setting->save();
        }

        $this->flush();
    }

    public function flush(): void
    {
        $this->memo = null;
        Cache::forget(self::CACHE_KEY);
    }
}
