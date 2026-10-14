<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Property;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isCompany = $user->isCompany();
        $isBroker = $user->isBroker();
        $isPending = $user->status === 'pending';

        // Properties stats
        $propertiesQuery = Property::where('owner_id', $user->id);
        $totalProperties = (clone $propertiesQuery)->count();
        $activeProperties = (clone $propertiesQuery)->where('status', 'published')->count();
        $soldProperties = (clone $propertiesQuery)->where(function ($q) {
            $q->where('status', 'sold')->orWhereNotNull('sold_at');
        })->count();
        $pendingProperties = (clone $propertiesQuery)->where('status', 'pending_review')->count();
        $totalViews = (clone $propertiesQuery)->sum('views_count') ?? 0;

        // Projects stats (for companies / agencies)
        $totalProjects = 0;
        $agencyId = $user->agency?->id;
        if ($isCompany) {
            $projectsQuery = Project::where(function ($q) use ($user, $agencyId) {
                $q->where('owner_id', $user->id);
                if ($agencyId) {
                    $q->orWhere('agency_id', $agencyId);
                }
            });
            $totalProjects = $projectsQuery->count();
        }

        // Recent listings
        $recentProperties = Property::with(['translations', 'category', 'location'])
            ->where('owner_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'user',
            'isCompany',
            'isBroker',
            'isPending',
            'totalProperties',
            'activeProperties',
            'soldProperties',
            'pendingProperties',
            'totalViews',
            'totalProjects',
            'recentProperties'
        ));
    }
}

