<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PropertyCategory;
use App\Services\Admin\AdminStatsService;
use App\Services\Admin\PageViewTracker;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatsController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $this->authorizePermission('stats.view');
        $stats = AdminStatsService::for($this->admin());

        return view('admin.stats.index', [
            'overview' => $stats->overview(),
            'growth' => $stats->monthlyGrowth(12),
            'trend' => $stats->trend(30),
            'statusDistribution' => $stats->statusDistribution(),
            'topProperties' => $stats->topProperties(10),
            'topAgents' => $stats->topAgents(10),
            'propertyComparison' => $stats->monthComparison('properties'),
            'leadComparison' => $stats->monthComparison('leads'),
            'userComparison' => $stats->monthComparison('users'),
            'pageViews' => PageViewTracker::sectionReport(30),
            'pageViewsTotal' => PageViewTracker::totalViews(30),
            'pageViewsSeries' => PageViewTracker::dailySeries(14),
        ]);
    }

    public function properties(Request $request)
    {
        $this->authorizePermission('stats.view');
        $stats = AdminStatsService::for($this->admin());

        $byCategory = $this->scope($stats)
            ->selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->with('category.translations')
            ->get()
            ->map(fn ($row) => [
                'label' => $row->category?->translation('ar')?->name ?? 'غير محدد',
                'value' => (int) $row->total,
            ]);

        $byLocation = $this->scope($stats)
            ->selectRaw('location_id, count(*) as total')
            ->groupBy('location_id')
            ->with('location.translations')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->location?->translation('ar')?->name ?? 'غير محدد',
                'value' => (int) $row->total,
            ]);

        $byPurpose = $this->scope($stats)
            ->selectRaw('purpose, count(*) as total')
            ->groupBy('purpose')
            ->pluck('total', 'purpose');

        return view('admin.stats.properties', [
            'overview' => $stats->overview(),
            'byCategory' => $byCategory,
            'byLocation' => $byLocation,
            'byPurpose' => $byPurpose,
            'topProperties' => $stats->topProperties(15),
            'statusDistribution' => $stats->statusDistribution(),
            'categories' => PropertyCategory::query()->with('translations')->get(),
            'locations' => Location::query()->with('translations')->limit(200)->get(),
        ]);
    }

    public function users()
    {
        $this->authorizePermission('stats.view');
        $stats = AdminStatsService::for($this->admin());

        return view('admin.stats.users', [
            'overview' => $stats->overview(),
            'breakdown' => $stats->teamBreakdown(),
            'topAgents' => $stats->topAgents(15),
            'userComparison' => $stats->monthComparison('users'),
            'trend' => $stats->trend(30),
        ]);
    }

    /** CSV export of the current statistics scope. */
    public function export(Request $request): StreamedResponse
    {
        $this->authorizePermission('stats.view');
        $stats = AdminStatsService::for($this->admin());
        $overview = $stats->overview();

        return response()->streamDownload(function () use ($overview, $stats): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel reads Arabic correctly.
            fputcsv($out, ['المؤشر', 'القيمة']);
            foreach ($overview as $key => $value) {
                fputcsv($out, [$key, is_float($value) ? round($value, 2) : $value]);
            }
            fputcsv($out, []);
            fputcsv($out, ['كود العقار', 'الاسم', 'الحالة', 'السعر', 'المشاهدات']);
            foreach ($stats->topProperties(500) as $property) {
                fputcsv($out, [
                    $property->reference_code,
                    $property->displayName('ar'),
                    status_label($property->status),
                    $property->price,
                    $property->views_count,
                ]);
            }
            fclose($out);
        }, 'darlii-stats-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function scope(AdminStatsService $stats)
    {
        return $stats->propertyScope()->with(['category.translations', 'location.translations']);
    }
}
