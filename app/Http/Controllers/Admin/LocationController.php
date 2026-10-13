<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationTranslation;
use App\Models\Project;
use App\Models\Property;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * إدارة المواقع والمناطق (محافظات، مدن، أحياء…) مع نموذج إضافة/تعديل داخل
 * صفحة القائمة نفسها لأن المسارات لا تحتوي على صفحة منفصلة للنموذج.
 */
class LocationController extends Controller
{
    use InteractsWithAdminPanel;

    /** أنواع المواقع المدعومة — العمود locations.type نصي بطول 30. */
    private const TYPES = [
        'governorate' => 'محافظة',
        'city' => 'مدينة',
        'area' => 'منطقة',
        'district' => 'حي',
        'compound' => 'كمبوند',
    ];

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('content.taxonomies');

        $filters = $request->only(['q', 'type', 'status']);

        $locations = Location::query()
            ->with(['translations', 'parent.translations'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);

                $query->whereHas('translations', function ($translation) use ($term): void {
                    $translation->where('name', 'like', '%' . $term . '%')
                        ->orWhere('slug', 'like', '%' . $term . '%');
                });
            })
            ->when(filled($filters['type'] ?? null), fn ($query) => $query->where('type', $filters['type']))
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', (bool) $filters['status']))
            ->orderBy('type')
            ->orderBy('id')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        // لا توجد علاقة properties() على الموديل، لذلك نحسب الاستخدام يدوياً.
        $propertyCounts = Property::query()
            ->selectRaw('location_id, COUNT(*) as total')
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->pluck('total', 'location_id');

        $projectCounts = Project::query()
            ->selectRaw('location_id, COUNT(*) as total')
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->pluck('total', 'location_id');

        $editing = null;

        if ($request->filled('edit')) {
            $editing = Location::query()
                ->with('translations')
                ->find($request->integer('edit'));
        }

        return view('admin.locations.index', [
            'locations' => $locations,
            'filters' => $filters,
            'types' => self::TYPES,
            'propertyCounts' => $propertyCounts,
            'projectCounts' => $projectCounts,
            'editing' => $editing,
            'parents' => Location::query()
                ->with('translations')
                ->when($editing, fn ($query) => $query->whereKeyNot($editing->id))
                ->orderBy('type')
                ->orderBy('id')
                ->get(),
            'title' => $editing ? 'تعديل موقع: ' . ($editing->translation('ar')?->name ?? '#' . $editing->id) : 'المواقع والمناطق',
            'subtitle' => 'المحافظات والمدن والمناطق التي تُربط بها العقارات والمشروعات',
        ]);
    }

    // ------------------------------------------------------------ الإضافة
    public function store(Request $request)
    {
        $this->authorizePermission('content.taxonomies');

        $data = $this->validated($request);

        $location = DB::transaction(function () use ($data): Location {
            $location = Location::query()->create($this->columnPayload($data));

            $this->syncTranslations($location, $data);

            return $location;
        });

        AuditLogger::log('location.created', $location, [], ['type' => $location->type]);

        return redirect()->route('admin.locations.index')->with('success', 'تمت إضافة الموقع بنجاح.');
    }

    // ------------------------------------------------------------- التعديل
    public function update(Request $request, Location $location)
    {
        $this->authorizePermission('content.taxonomies');

        $data = $this->validated($request, $location);

        if (! empty($data['parent_id']) && $this->createsCycle($location, (int) $data['parent_id'])) {
            return back()->withInput()->with('error', 'لا يمكن جعل الموقع تابعاً لنفسه أو لأحد المواقع التابعة له.');
        }

        $old = $location->only(['parent_id', 'type', 'country_code', 'latitude', 'longitude', 'status']);

        DB::transaction(function () use ($location, $data): void {
            $location->update($this->columnPayload($data));
            $this->syncTranslations($location, $data);
        });

        AuditLogger::log('location.updated', $location, $old, $location->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث الموقع بنجاح.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(Location $location)
    {
        $this->authorizePermission('content.taxonomies');

        $properties = Property::query()->where('location_id', $location->id)->count();

        if ($properties > 0) {
            return back()->with('error', 'لا يمكن الحذف: ' . $properties . ' عقار مرتبط بهذا الموقع. انقل العقارات أولاً.');
        }

        $projects = Project::query()->where('location_id', $location->id)->count();

        if ($projects > 0) {
            return back()->with('error', 'لا يمكن الحذف: ' . $projects . ' مشروع مرتبط بهذا الموقع. انقل المشروعات أولاً.');
        }

        $children = $location->children()->count();

        if ($children > 0) {
            return back()->with('error', 'لا يمكن الحذف: يوجد ' . $children . ' موقع فرعي تابع لهذا الموقع.');
        }

        AuditLogger::log('location.deleted', $location, ['type' => $location->type], []);

        $location->translations()->delete();
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'تم حذف الموقع.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function validated(Request $request, ?Location $location = null): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:locations,id'],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'country_code' => ['nullable', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['nullable', 'boolean'],
            'translations.ar.name' => ['required', 'string', 'max:255'],
            'translations.ar.slug' => ['nullable', 'string', 'max:255'],
            'translations.en.name' => ['nullable', 'string', 'max:255'],
            'translations.en.slug' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function columnPayload(array $data): array
    {
        return [
            'parent_id' => ($data['parent_id'] ?? null) ?: null,
            'type' => $data['type'],
            'country_code' => ($data['country_code'] ?? null) ? strtoupper((string) $data['country_code']) : null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => (bool) ($data['status'] ?? false),
        ];
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Location $location, array $data): void
    {
        foreach (['ar', 'en'] as $locale) {
            $translation = (array) ($data['translations'][$locale] ?? []);

            if (blank($translation['name'] ?? null)) {
                continue;
            }

            $existing = $location->translations()->where('locale', $locale)->first();

            $translation['slug'] = $this->uniqueTranslationSlug(
                (string) (($translation['slug'] ?? '') ?: $translation['name']),
                $locale,
                $existing?->id
            );

            $location->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }

    /** هل سيؤدي هذا الأب إلى حلقة (الموقع أباً لنفسه أو لسليل منه)؟ */
    private function createsCycle(Location $location, int $parentId): bool
    {
        if ($parentId === (int) $location->id) {
            return true;
        }

        $current = Location::query()->find($parentId);
        $guard = 0;

        while ($current && $guard++ < 50) {
            if ((int) $current->id === (int) $location->id) {
                return true;
            }

            $current = $current->parent_id ? Location::query()->find($current->parent_id) : null;
        }

        return false;
    }

    /** العنوان العربي بلا مقابل لاتيني يعطي slug عشوائياً لأن العمود غير قابل للفراغ. */
    private function uniqueTranslationSlug(string $value, string $locale, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(12);
        $slug = $base;
        $counter = 2;

        while (LocationTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
