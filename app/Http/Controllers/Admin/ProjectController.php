<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * إدارة المشروعات العقارية (كبريات التطوير): قائمة، إنشاء، تعديل وحذف
 * مع ترجمات عربية/إنجليزية وربط المرافق والمواقع.
 */
class ProjectController extends Controller
{
    use InteractsWithAdminPanel;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('project.view');

        $projects = Project::query()
            ->with(['translations', 'location.translations'])
            ->withCount('units')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->input('q'));

                $query->where(function ($inner) use ($term): void {
                    $inner->where('reference_code', 'like', "%{$term}%")
                        ->orWhere('investor_name', 'like', "%{$term}%")
                        ->orWhereHas('translations', function ($t) use ($term): void {
                            $t->where('name', 'like', "%{$term}%")
                                ->orWhere('slug', 'like', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('is_featured'), fn ($query) => $query->where('is_featured', $request->boolean('is_featured')))
            ->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.projects.index', [
            'projects' => $projects,
            'filters' => $request->only(['q', 'status', 'is_featured']),
            'statusLabels' => $this->statusLabels(),
            'title' => 'كل المشروعات',
        ]);
    }

    // ------------------------------------------------------------ الإنشاء
    public function create()
    {
        $this->authorizePermission('project.manage');

        return view('admin.projects.form', $this->sharedData() + [
            'project' => new Project(['status' => 'draft', 'is_featured' => false]),
            'title' => 'إضافة مشروع جديد',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('project.manage');

        $data = $this->validated($request);

        $project = DB::transaction(function () use ($data): Project {
            $project = Project::query()->create([
                'uuid' => (string) Str::uuid(),
                'reference_code' => $data['reference_code'] ?: $this->nextReference(),
                'owner_id' => $this->admin()->id,
                'location_id' => $data['location_id'] ?? null,
                'investor_name' => $data['investor_name'] ?? null,
                'blocks_count' => $data['blocks_count'] ?? null,
                'flats_count' => $data['flats_count'] ?? null,
                'floors_count' => $data['floors_count'] ?? null,
                'open_sale_date' => $data['open_sale_date'] ?? null,
                'completion_date' => $data['completion_date'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => $data['status'],
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            $this->syncTranslations($project, $data);
            $project->amenities()->sync($data['amenities'] ?? []);

            return $project;
        });

        AuditLogger::log('project.created', $project, [], ['reference_code' => $project->reference_code]);

        return redirect(admin_route('admin.projects.index'))
            ->with('success', 'تم إنشاء المشروع ' . $project->reference_code . ' بنجاح.');
    }

    // -------------------------------------------------------------- التعديل
    public function edit(Project $project)
    {
        $this->authorizePermission('project.manage');

        return view('admin.projects.form', $this->sharedData() + [
            'project' => $project->load(['translations', 'amenities']),
            'title' => 'تعديل: ' . $this->projectLabel($project),
            'subtitle' => 'كود المشروع: ' . $project->reference_code,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizePermission('project.manage');

        $data = $this->validated($request, $project);
        $old = $project->only(['reference_code', 'status', 'is_featured', 'investor_name', 'location_id']);

        DB::transaction(function () use ($project, $data): void {
            $project->update([
                'reference_code' => $data['reference_code'] ?: $project->reference_code,
                'location_id' => $data['location_id'] ?? null,
                'investor_name' => $data['investor_name'] ?? null,
                'blocks_count' => $data['blocks_count'] ?? null,
                'flats_count' => $data['flats_count'] ?? null,
                'floors_count' => $data['floors_count'] ?? null,
                'open_sale_date' => $data['open_sale_date'] ?? null,
                'completion_date' => $data['completion_date'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'status' => $data['status'],
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'published_at' => $this->resolvePublishedAt($project, $data['status']),
            ]);

            $this->syncTranslations($project, $data);
            $project->amenities()->sync($data['amenities'] ?? []);
        });

        AuditLogger::log('project.updated', $project, $old, $project->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث بيانات المشروع بنجاح.');
    }

    public function destroy(Project $project)
    {
        $this->authorizePermission('project.manage');

        AuditLogger::log('project.deleted', $project, ['reference_code' => $project->reference_code]);
        $project->delete();

        return redirect(admin_route('admin.projects.index'))->with('success', 'تم حذف المشروع.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function sharedData(): array
    {
        return [
            'locations' => Location::query()->with('translations')->orderBy('id')->get(),
            'amenities' => Amenity::query()->with('translations')->orderBy('id')->get(),
            'statusLabels' => $this->statusLabels(),
        ];
    }

    /** @return array<string, string> */
    private function statusLabels(): array
    {
        return [
            'draft' => 'مسودة',
            'published' => 'منشور',
        ];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Project $project = null): array
    {
        return $request->validate([
            'reference_code' => ['nullable', 'string', 'max:60', Rule::unique('projects', 'reference_code')->ignore($project?->id)],
            'investor_name' => ['nullable', 'string', 'max:190'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'blocks_count' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'flats_count' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'floors_count' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'open_sale_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'translations.ar.name' => ['required', 'string', 'max:190'],
            'translations.ar.slug' => ['nullable', 'string', 'max:190'],
            'translations.ar.short_description' => ['nullable', 'string', 'max:500'],
            'translations.ar.description' => ['nullable', 'string'],
            'translations.ar.seo_title' => ['nullable', 'string', 'max:190'],
            'translations.ar.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.en.name' => ['nullable', 'string', 'max:190'],
            'translations.en.slug' => ['nullable', 'string', 'max:190'],
            'translations.en.short_description' => ['nullable', 'string', 'max:500'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.en.seo_title' => ['nullable', 'string', 'max:190'],
            'translations.en.seo_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Project $project, array $data): void
    {
        foreach ((array) ($data['translations'] ?? []) as $locale => $translation) {
            if ($locale === 'en' && ! config('locales.admin_english_enabled')) {
                continue;
            }

            if (blank($translation['name'] ?? null)) {
                continue;
            }

            $translation['slug'] = $this->uniqueSlug(
                ($translation['slug'] ?? null) ?: $translation['name'],
                ProjectTranslation::class,
                'slug',
                $project->translations()->where('locale', $locale)->value('id')
            );

            $project->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }

    private function resolvePublishedAt(Project $project, string $status): ?\Illuminate\Support\Carbon
    {
        if ($status !== 'published') {
            return null;
        }

        return $project->published_at ?: now();
    }

    private function nextReference(): string
    {
        do {
            $reference = 'PRJ-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (Project::withTrashed()->where('reference_code', $reference)->exists());

        return $reference;
    }

    /** توليد slug فريد مع حلقة تحقق من التكرار. */
    private function uniqueSlug(string $value, string $modelClass, string $column = 'slug', ?int $ignoreId = null): string
    {
        $slug = Str::slug($value) ?: Str::lower(Str::random(8));
        $base = $slug;
        $i = 2;

        while ($modelClass::query()
            ->where($column, $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    private function projectLabel(Project $project): string
    {
        return (string) ($project->translation('ar')?->name ?: $project->reference_code);
    }
}
