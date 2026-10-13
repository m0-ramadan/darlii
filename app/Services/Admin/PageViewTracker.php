<?php

namespace App\Services\Admin;

use App\Models\DailyStat;
use App\Models\Property;
use Illuminate\Support\Collection;

/**
 * Lightweight, privacy-friendly page analytics for the CMS statistics screens
 * ("إحصائيات لكل صفحة"). Only per-day counters are stored — never an IP,
 * user agent or any personal data.
 */
class PageViewTracker
{
    /** Arabic labels for the public sections that get counted. */
    public const SECTIONS = [
        'home' => 'الصفحة الرئيسية',
        'properties' => 'صفحة العقارات',
        'projects' => 'صفحة المشروعات',
        'blog' => 'المدونة',
        'agents' => 'صفحة السماسرة',
        'careers' => 'الوظائف',
        'news' => 'الأخبار',
        'wishlist' => 'المفضلة',
        'about-us' => 'من نحن',
        'contact-us' => 'تواصل معنا',
        'faqs' => 'الأسئلة الشائعة',
        'pricing-plans' => 'الخطط والأسعار',
    ];

    /**
     * Record one page view. Called from the public frontend only.
     */
    public static function track(string $section, array $segments = []): void
    {
        $section = $section !== '' ? $section : 'home';

        DailyStat::bump('page_views.' . $section, 1);
        DailyStat::bump('page_views_total', 1);

        // Per-listing counter used by "إحصائيات كل شقة".
        if ($section === 'properties' && isset($segments[0])) {
            $property = Property::query()
                ->whereHas('translations', fn ($q) => $q->where('slug', $segments[0]))
                ->first();

            if ($property) {
                DailyStat::bump('property_views.' . $property->id, 1);
                $property->increment('views_count');
            }
        }
    }

    /**
     * Views per public section for the last N days.
     *
     * @return Collection<int, array{section: string, label: string, views: int, today: int, share: float}>
     */
    public static function sectionReport(int $days = 30): Collection
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $rows = DailyStat::query()
            ->where('metric', 'like', 'page_views.%')
            ->where('metric', '!=', 'page_views_total')
            ->where('stat_date', '>=', $since)
            ->selectRaw('metric, SUM(value) as total')
            ->groupBy('metric')
            ->orderByDesc('total')
            ->get();

        $today = DailyStat::query()
            ->where('metric', 'like', 'page_views.%')
            ->where('metric', '!=', 'page_views_total')
            ->whereDate('stat_date', today())
            ->pluck('value', 'metric');

        $grandTotal = max(1, (int) $rows->sum('total'));

        return $rows->map(function (DailyStat $row) use ($today, $grandTotal): array {
            $section = str_replace('page_views.', '', $row->metric);

            return [
                'section' => $section,
                'label' => self::SECTIONS[$section] ?? $section,
                'views' => (int) $row->total,
                'today' => (int) ($today[$row->metric] ?? 0),
                'share' => round(((int) $row->total / $grandTotal) * 100, 1),
            ];
        });
    }

    /** Total public page views over the last N days. */
    public static function totalViews(int $days = 30): int
    {
        return (int) DailyStat::query()
            ->where('metric', 'page_views_total')
            ->where('stat_date', '>=', now()->subDays($days - 1)->startOfDay())
            ->sum('value');
    }

    /** Daily series for the total page views, used by the charts. */
    public static function dailySeries(int $days = 14): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $values = DailyStat::query()
            ->where('metric', 'page_views_total')
            ->where('stat_date', '>=', $start)
            ->pluck('value', 'stat_date');

        $labels = $series = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('d/m');
            $series[] = (int) ($values[$day->toDateString()] ?? 0);
        }

        return compact('labels', 'series');
    }
}
