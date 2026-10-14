<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Project;
use App\Services\Admin\PageViewTracker;
use App\Services\LegacyFrontend\LegacyFrontendRenderer;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private readonly LegacyFrontendRenderer $renderer) {}

    public function index(Request $request)
    {
        $projects = Project::query()
            ->with(['translations', 'location.translations', 'properties.media', 'properties.translations'])
            ->where('status', 'published')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->input('q'));
                $query->whereHas('translations', fn ($t) => $t->where('name', 'like', "%{$term}%"));
            })
            ->when($request->filled('location_id'), function ($q) use ($request): void {
                $locId = (int) $request->input('location_id');
                $childIds = Location::where('parent_id', $locId)->pluck('id')->all();
                $ids = array_merge([$locId], $childIds);
                $q->whereIn('location_id', $ids);
            })
            ->latest('published_at')
            ->paginate(18)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'locations' => Location::query()
                ->with(['translations', 'children.translations'])
                ->where('parent_id', 16)
                ->orderBy('id')
                ->get(),
            'shell' => $this->shell(),
        ]);
    }

    public function show(?string $param1 = null, ?string $param2 = null)
    {
        $slug = $param2 ?? $param1 ?? '';
        $locale = $param2 ? $param1 : (app()->getLocale() ?: config('locales.default', 'ar'));

        $project = Project::with(['translations', 'amenities.translations', 'units', 'location.translations'])
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->firstOrFail();

        PageViewTracker::track('projects', [$slug]);

        $nearbyProjects = Project::query()
            ->with(['translations', 'location.translations'])
            ->where('id', '!=', $project->id)
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('projects.show', [
            'project' => $project,
            'nearbyProjects' => $nearbyProjects,
            'shell' => $this->shell(),
        ]);
    }

    /** @return array{head:string, footer:string}|null */
    private function shell(): ?array
    {
        return $this->renderer->shell(
            app()->getLocale(),
            'projects',
            LegacyFrontendRenderer::listingsMarker()
        );
    }
}
