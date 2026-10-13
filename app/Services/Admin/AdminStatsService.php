<?php

namespace App\Services\Admin;

use App\Models\Appointment;
use App\Models\DailyStat;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Every number shown on the CMS dashboard and statistics pages is produced
 * here, always respecting the signed-in user's scope (admin = everything,
 * manager = own team, agent = own records only).
 */
class AdminStatsService
{
    public function __construct(private User $user) {}

    public static function for(User $user): self
    {
        return new self($user);
    }

    /** Property query limited to what this user may see. */
    public function propertyScope(): Builder
    {
        $query = Property::query();

        if ($this->user->canSeeAllProperties()) {
            return $query;
        }

        if ($this->user->isManager()) {
            $ids = $this->user->visibleUserIds();

            return $query->where(fn (Builder $q) => $q->whereIn('owner_id', $ids)->orWhereIn('managed_by', $ids));
        }

        return $query->where('owner_id', $this->user->id);
    }

    public function leadScope(): Builder
    {
        $query = Lead::query();

        if ($this->user->canSeeAllLeads()) {
            return $query;
        }

        if ($this->user->isManager()) {
            return $query->whereIn('assigned_to', $this->user->visibleUserIds());
        }

        return $query->where('assigned_to', $this->user->id);
    }

    public function userScope(): Builder
    {
        $query = User::query();

        if ($this->user->isAdmin()) {
            return $query;
        }

        return $query->whereIn('id', $this->user->visibleUserIds());
    }

    /** @return array<string, int|float|string> */
    public function overview(): array
    {
        $properties = $this->propertyScope();

        $byStatus = (clone $properties)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $totalValue = (float) (clone $properties)->sum('price');
        $published = (clone $properties)->where('status', 'published');

        return [
            'properties_total' => (int) (clone $properties)->count(),
            'properties_published' => (int) (clone $properties)->where('status', 'published')->count(),
            'properties_pending' => (int) (clone $properties)->where('status', 'pending_review')->count(),
            'properties_draft' => (int) (clone $properties)->where('status', 'draft')->count(),
            'properties_rejected' => (int) (clone $properties)->where('status', 'rejected')->count(),
            'properties_sold' => (int) (clone $properties)->where('status', 'sold')->count(),
            'properties_rented' => (int) (clone $properties)->where('status', 'rented')->count(),
            'properties_featured' => (int) (clone $properties)->where('is_featured', true)->count(),
            'properties_today' => (int) (clone $properties)->whereDate('created_at', today())->count(),
            'properties_month' => (int) (clone $properties)->where('created_at', '>=', now()->startOfMonth())->count(),
            'properties_unassigned' => (int) (clone $properties)->where(fn (Builder $q) => $q->whereNull('managed_by')->whereNull('owner_id'))->count(),
            'properties_views' => (int) (clone $properties)->sum('views_count'),
            'properties_value' => $totalValue,
            'properties_avg_price' => $published->count() > 0 ? (float) (clone $properties)->where('status', 'published')->avg('price') : 0.0,
            'properties_by_status' => $byStatus,
            'projects_total' => (int) Project::query()->count(),
            'leads_total' => (int) $this->leadScope()->count(),
            'leads_new' => (int) $this->leadScope()->where('status', 'new')->count(),
            'leads_month' => (int) $this->leadScope()->where('created_at', '>=', now()->startOfMonth())->count(),
            'appointments_total' => (int) Appointment::query()->whereIn('agent_id', $this->user->visibleUserIds())->count(),
            'appointments_upcoming' => (int) Appointment::query()->whereIn('agent_id', $this->user->visibleUserIds())->where('scheduled_at', '>=', now())->count(),
            'reviews_pending' => (int) Review::query()->where('status', 'pending')->count(),
            'posts_total' => (int) Post::query()->count(),
            'users_total' => (int) $this->userScope()->count(),
            'users_admins' => (int) $this->userScope()->whereHas('roles', fn ($q) => $q->whereIn('slug', ['admin', 'super-admin']))->count(),
            'users_managers' => (int) $this->userScope()->whereHas('roles', fn ($q) => $q->whereIn('slug', ['manager', 'property-manager']))->count(),
            'users_agents' => (int) $this->userScope()->whereHas('roles', fn ($q) => $q->where('slug', 'agent'))->count(),
            'users_active' => (int) $this->userScope()->where('status', 'active')->count(),
        ];
    }

    /**
     * Last N days of activity for the dashboard chart.
     *
     * @return array{labels: list<string>, properties: list<int>, leads: list<int>, users: list<int>}
     */
    public function trend(int $days = 14): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $labels = $properties = $leads = $users = [];

        $propertyCounts = (clone $this->propertyScope())
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('day')->pluck('total', 'day')->all();

        $leadCounts = $this->leadScope()
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('day')->pluck('total', 'day')->all();

        $userCounts = $this->userScope()
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('day')->pluck('total', 'day')->all();

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('d/m');
            $properties[] = (int) ($propertyCounts[$key] ?? 0);
            $leads[] = (int) ($leadCounts[$key] ?? 0);
            $users[] = (int) ($userCounts[$key] ?? 0);
        }

        return compact('labels', 'properties', 'leads', 'users');
    }

    /** @return Collection<int, Property> */
    public function topProperties(int $limit = 8): Collection
    {
        return $this->propertyScope()
            ->with(['translations', 'owner', 'currency'])
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, User> */
    public function topAgents(int $limit = 8): Collection
    {
        return $this->userScope()
            ->with('roles')
            ->withCount('properties')
            ->whereHas('roles', fn ($q) => $q->where('slug', 'agent'))
            ->orderByDesc('properties_count')
            ->limit($limit)
            ->get();
    }

    /** @return array<string, int|float> */
    public function propertyStats(Property $property): array
    {
        return [
            'views' => (int) $property->views_count,
            'leads' => (int) $property->leads()->count(),
            'leads_new' => (int) $property->leads()->where('status', 'new')->count(),
            'favorites' => (int) DB::table('favorites')->where('favoritable_type', Property::class)->where('favoritable_id', $property->id)->count(),
            'reviews' => (int) $property->reviews()->count(),
            'appointments' => (int) $property->appointments()->count(),
            'history' => (int) $property->statusHistory()->count(),
            'age_days' => $property->created_at ? (int) $property->created_at->diffInDays(now()) : 0,
            'price' => (float) $property->price,
            'price_per_meter' => (float) ($property->area > 0 ? $property->price / $property->area : 0),
        ];
    }

    /**
     * 30 day view/lead trend for a single property.
     *
     * @return array{labels: list<string>, views: list<int>, leads: list<int>}
     */
    public function propertyTrend(Property $property, int $days = 30): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $labels = $views = $leads = [];

        $leadCounts = $property->leads()
            ->selectRaw('DATE(created_at) as day, count(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('day')->pluck('total', 'day')->all();

        $viewCounts = DailyStat::query()
            ->where('metric', 'property_views.' . $property->id)
            ->where('stat_date', '>=', $start)
            ->pluck('value', 'stat_date')
            ->all();

        for ($i = 0; $i < $days; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->toDateString();
            $labels[] = $day->format('d/m');
            $views[] = (int) ($viewCounts[$key] ?? 0);
            $leads[] = (int) ($leadCounts[$key] ?? 0);
        }

        return compact('labels', 'views', 'leads');
    }

    /**
     * Breakdown of properties per owner/manager/agent, used on the users page.
     *
     * @return Collection<int, User>
     */
    public function teamBreakdown(): Collection
    {
        return $this->userScope()
            ->with(['roles', 'manager'])
            ->withCount(['properties', 'subordinates'])
            ->orderByDesc('properties_count')
            ->limit(50)
            ->get();
    }

    /** Recent activity ticker for the dashboard. */
    public function recentProperties(int $limit = 6): Collection
    {
        return $this->propertyScope()->with(['translations', 'owner'])->latest()->limit($limit)->get();
    }

    public function recentLeads(int $limit = 6): Collection
    {
        return $this->leadScope()->with(['property.translations'])->latest()->limit($limit)->get();
    }

    /** Monthly comparison helper: this month vs last month. */
    public function monthComparison(string $model = 'properties'): array
    {
        $scope = match ($model) {
            'leads' => $this->leadScope(),
            'users' => $this->userScope(),
            default => $this->propertyScope(),
        };

        $thisMonth = (int) (clone $scope)->where('created_at', '>=', now()->startOfMonth())->count();
        $lastMonth = (int) (clone $scope)
            ->whereBetween('created_at', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])
            ->count();

        $change = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : ($thisMonth > 0 ? 100.0 : 0.0);

        return ['this_month' => $thisMonth, 'last_month' => $lastMonth, 'change' => $change];
    }

    /** @return array{labels: list<string>, values: list<int>} */
    public function statusDistribution(): array
    {
        $rows = (clone $this->propertyScope())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'labels' => $rows->keys()->map(fn ($s) => status_label($s))->values()->all(),
            'values' => $rows->values()->map(fn ($v) => (int) $v)->all(),
        ];
    }

    /** Growth numbers for the statistics page (12 months). */
    public function monthlyGrowth(int $months = 12): array
    {
        $labels = $properties = $leads = [];
        $start = Carbon::now()->subMonths($months - 1)->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $properties[] = (int) (clone $this->propertyScope())
                ->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
            $leads[] = (int) $this->leadScope()
                ->whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->count();
        }

        return compact('labels', 'properties', 'leads');
    }
}
