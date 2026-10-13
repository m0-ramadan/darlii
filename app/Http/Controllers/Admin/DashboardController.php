<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Services\Admin\AdminStatsService;

class DashboardController extends Controller
{
    use InteractsWithAdminPanel;

    public function __invoke()
    {
        $stats = AdminStatsService::for($this->admin());

        return view('admin.dashboard', [
            'overview' => $stats->overview(),
            'trend' => $stats->trend(14),
            'statusDistribution' => $stats->statusDistribution(),
            'topProperties' => $stats->topProperties(6),
            'topAgents' => $stats->topAgents(6),
            'recentProperties' => $stats->recentProperties(6),
            'recentLeads' => $stats->recentLeads(5),
            'propertyComparison' => $stats->monthComparison('properties'),
            'leadComparison' => $stats->monthComparison('leads'),
        ]);
    }
}
