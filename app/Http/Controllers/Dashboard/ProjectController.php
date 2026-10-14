<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Project;
use App\Models\ProjectTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user->isCompany() && ! $user->isAdmin()) {
            abort(403, 'هذا القسم مخصص للشركات والوكالات العقارية فقط.');
        }

        $agencyId = $user->agency?->id;
        $projects = Project::with(['translations', 'location.translations'])
            ->where(function ($q) use ($user, $agencyId) {
                $q->where('owner_id', $user->id);
                if ($agencyId) {
                    $q->orWhere('agency_id', $agencyId);
                }
            })
            ->latest()
            ->paginate(15);

        return view('dashboard.projects.index', compact('projects', 'user'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if (! $user->isCompany() && ! $user->isAdmin()) {
            abort(403, 'هذا القسم مخصص للشركات والوكالات العقارية فقط.');
        }

        if ($user->status === 'pending') {
            return redirect()
                ->route('dashboard.index', ['locale' => app()->getLocale()])
                ->with('status_warning', 'حساب الشركة قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك إضافة مشاريع جديدة حتى يتم تفعيل الحساب.');
        }

        $locations = Location::with('translations')->get();
        $amenities = Amenity::with('translations')->get();

        return view('dashboard.projects.create', compact('locations', 'amenities'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->isCompany() && ! $user->isAdmin()) {
            abort(403, 'هذا القسم مخصص للشركات والوكالات العقارية فقط.');
        }

        if ($user->status === 'pending') {
            return redirect()
                ->route('dashboard.index', ['locale' => app()->getLocale()])
                ->with('status_warning', 'حساب الشركة قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك إضافة مشاريع جديدة حتى يتم تفعيل الحساب.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'investor_name' => ['nullable', 'string', 'max:190'],
            'blocks_count' => ['nullable', 'integer', 'min:0'],
            'flats_count' => ['nullable', 'integer', 'min:0'],
            'floors_count' => ['nullable', 'integer', 'min:0'],
            'open_sale_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['exists:amenities,id'],
        ]);

        $project = DB::transaction(function () use ($validated, $user, $request) {
            $prefix = (string) setting('projects.reference_prefix', 'PRJ');
            $refCode = $prefix . '-' . strtoupper(Str::random(6));

            $p = Project::create([
                'uuid' => (string) Str::uuid(),
                'reference_code' => $refCode,
                'owner_id' => $user->id,
                'agency_id' => $user->agency?->id,
                'location_id' => $validated['location_id'] ?? null,
                'investor_name' => $validated['investor_name'] ?? ($user->agency?->name ?: $user->name),
                'blocks_count' => $validated['blocks_count'] ?? null,
                'flats_count' => $validated['flats_count'] ?? null,
                'floors_count' => $validated['floors_count'] ?? null,
                'open_sale_date' => $validated['open_sale_date'] ?? null,
                'completion_date' => $validated['completion_date'] ?? null,
                'status' => 'draft',
                'published_at' => null,
            ]);

            $slug = Str::slug($validated['name']) ?: 'project-' . $p->id;
            if (ProjectTranslation::where('slug', $slug)->exists()) {
                $slug .= '-' . $p->id;
            }

            foreach (['ar', 'en'] as $loc) {
                ProjectTranslation::create([
                    'project_id' => $p->id,
                    'locale' => $loc,
                    'name' => $validated['name'],
                    'slug' => $slug,
                    'short_description' => $validated['short_description'] ?? null,
                    'description' => $validated['description'] ?? null,
                ]);
            }

            if (! empty($validated['amenities'])) {
                $p->amenities()->sync($validated['amenities']);
            }

            return $p;
        });

        return redirect()
            ->route('dashboard.projects.index', ['locale' => app()->getLocale()])
            ->with('success', 'تم إنشاء المشروع بنجاح (' . $project->reference_code . ') وسيتم مراجعته وتفعيله من قبل الإدارة.');
    }
}

