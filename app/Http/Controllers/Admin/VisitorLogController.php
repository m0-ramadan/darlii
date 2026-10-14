<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\SiteVisitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorLogController extends Controller
{
    use InteractsWithAdminPanel;
    public function index(Request $request): View
    {
        $this->authorizePermission('stats.view');

        $query = SiteVisitor::query()->with('user')->latest('last_active_at');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('visitor_id', 'like', "%{$search}%")
                    ->orWhere('city_or_markaz', 'like', "%{$search}%")
                    ->orWhere('governorate', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($gov = $request->input('governorate')) {
            $query->where('governorate', $gov);
        }

        if ($markaz = $request->input('markaz')) {
            $query->where('city_or_markaz', 'like', "%{$markaz}%");
        }

        if ($device = $request->input('device_type')) {
            $query->where('device_type', $device);
        }

        $visitors = $query->paginate(25)->withQueryString();

        // إحصائيات عامة
        $totalVisitors = SiteVisitor::count();
        $todayVisitors = SiteVisitor::whereDate('last_active_at', today())->count();
        $mobileCount = SiteVisitor::where('device_type', 'mobile')->count();
        $mobilePercentage = $totalVisitors > 0 ? round(($mobileCount / $totalVisitors) * 100) : 0;

        $topGovRow = SiteVisitor::selectRaw('governorate, count(*) as count')
            ->whereNotNull('governorate')
            ->groupBy('governorate')
            ->orderByDesc('count')
            ->first();
        $topGovernorate = $topGovRow ? $topGovRow->governorate : 'غير محدد بعد';

        $topMarkazRow = SiteVisitor::selectRaw('city_or_markaz, count(*) as count')
            ->whereNotNull('city_or_markaz')
            ->groupBy('city_or_markaz')
            ->orderByDesc('count')
            ->first();
        $topMarkaz = $topMarkazRow ? $topMarkazRow->city_or_markaz : 'غير محدد بعد';

        // قائمة المحافظات المتوفرة للفلترة
        $availableGovernorates = SiteVisitor::whereNotNull('governorate')
            ->distinct()
            ->pluck('governorate')
            ->sort()
            ->values();

        return view('admin.visitors.index', [
            'visitors' => $visitors,
            'totalVisitors' => $totalVisitors,
            'todayVisitors' => $todayVisitors,
            'mobilePercentage' => $mobilePercentage,
            'topGovernorate' => $topGovernorate,
            'topMarkaz' => $topMarkaz,
            'availableGovernorates' => $availableGovernorates,
            'filters' => $request->only(['q', 'governorate', 'markaz', 'device_type']),
        ]);
    }

    public function show(SiteVisitor $visitor): View
    {
        $this->authorizePermission('stats.view');

        $visitor->load(['user', 'hits' => fn($q) => $q->take(50)]);

        return view('admin.visitors.show', [
            'visitor' => $visitor,
        ]);
    }

    public function destroy(SiteVisitor $visitor): RedirectResponse
    {
        $this->authorizePermission('stats.view');

        $visitor->delete();

        return back()->with('success', 'تم حذف سجل الزائر بنجاح.');
    }
}
