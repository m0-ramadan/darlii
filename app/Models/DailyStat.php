<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    protected $fillable = ['stat_date', 'metric', 'value'];

    protected function casts(): array
    {
        return ['stat_date' => 'date', 'value' => 'integer'];
    }

    public static function bump(string $metric, int $amount = 1, ?string $date = null): void
    {
        $date = $date ?: now()->toDateString();

        $stat = static::query()->whereDate('stat_date', $date)->where('metric', $metric)->first();

        if ($stat) {
            $stat->increment('value', $amount);
        } else {
            try {
                static::query()->create([
                    'stat_date' => $date,
                    'metric' => $metric,
                    'value' => $amount,
                ]);
            } catch (\Throwable) {
                static::query()->whereDate('stat_date', $date)->where('metric', $metric)->increment('value', $amount);
            }
        }
    }
}
