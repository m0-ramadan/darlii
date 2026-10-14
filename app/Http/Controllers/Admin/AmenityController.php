<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * إدارة المرافق والخدمات (مسبح، جراج، أمن…) مع نموذج إضافة/تعديل داخل القائمة.
 */
class AmenityController extends Controller
{
    use InteractsWithAdminPanel;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('content.taxonomies');

        $filters = $request->only(['q', 'status']);

        $amenities = Amenity::query()
            ->with('translations')
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);

                $query->where(function ($inner) use ($term): void {
                    $inner->where('key', 'like', '%' . $term . '%')
                        ->orWhereHas('translations', fn ($translation) => $translation->where('name', 'like', '%' . $term . '%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', (bool) $filters['status']))
            ->orderBy('id')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        // لا توجد علاقتا properties()/projects() على الموديل، فنقرأ جدولي الربط مباشرة.
        $propertyCounts = DB::table('property_amenity')
            ->selectRaw('amenity_id, COUNT(*) as total')
            ->groupBy('amenity_id')
            ->pluck('total', 'amenity_id');

        $projectCounts = DB::table('project_amenity')
            ->selectRaw('amenity_id, COUNT(*) as total')
            ->groupBy('amenity_id')
            ->pluck('total', 'amenity_id');

        $editing = $request->filled('edit')
            ? Amenity::query()->with('translations')->find($request->integer('edit'))
            : null;

        return view('admin.amenities.index', [
            'amenities' => $amenities,
            'filters' => $filters,
            'propertyCounts' => $propertyCounts,
            'projectCounts' => $projectCounts,
            'editing' => $editing,
            'title' => $editing ? 'تعديل مرفق: ' . ($editing->translation('ar')?->name ?? '#' . $editing->id) : 'المرافق والخدمات',
            'subtitle' => 'المرافق التي تُربط بالعقارات والمشروعات',
        ]);
    }

    // ------------------------------------------------------------ الإضافة
    public function store(Request $request)
    {
        $this->authorizePermission('content.taxonomies');

        $data = $this->validated($request);

        $amenity = DB::transaction(function () use ($data): Amenity {
            $amenity = Amenity::query()->create([
                'key' => $data['key'],
                'icon' => ($data['icon'] ?? null) ?: null,
                'status' => (bool) ($data['status'] ?? false),
            ]);

            $this->syncTranslations($amenity, $data);

            return $amenity;
        });

        AuditLogger::log('amenity.created', $amenity, [], ['key' => $amenity->key]);

        return redirect()->route('admin.amenities.index')->with('success', 'تمت إضافة المرفق بنجاح.');
    }

    // ------------------------------------------------------------- التعديل
    public function update(Request $request, Amenity $amenity)
    {
        $this->authorizePermission('content.taxonomies');

        $data = $this->validated($request, $amenity);
        $old = $amenity->only(['key', 'icon', 'status']);

        DB::transaction(function () use ($amenity, $data): void {
            $amenity->update([
                'key' => $data['key'],
                'icon' => ($data['icon'] ?? null) ?: null,
                'status' => (bool) ($data['status'] ?? false),
            ]);

            $this->syncTranslations($amenity, $data);
        });

        AuditLogger::log('amenity.updated', $amenity, $old, $amenity->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث المرفق بنجاح.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(Amenity $amenity)
    {
        $this->authorizePermission('content.taxonomies');

        AuditLogger::log('amenity.deleted', $amenity, ['key' => $amenity->key], []);

        DB::transaction(function () use ($amenity): void {
            // الموديل لا يملك علاقتي properties()/projects()، لذلك نفصل من جداول الربط.
            DB::table('property_amenity')->where('amenity_id', $amenity->id)->delete();
            DB::table('project_amenity')->where('amenity_id', $amenity->id)->delete();

            $amenity->translations()->delete();
            $amenity->delete();
        });

        return redirect()->route('admin.amenities.index')->with('success', 'تم حذف المرفق وفصله عن العقارات والمشروعات.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function validated(Request $request, ?Amenity $amenity = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('amenities', 'key')->ignore($amenity?->id)],
            'icon' => ['nullable', 'string', 'max:190'],
            'status' => ['nullable', 'boolean'],
            'translations.ar.name' => ['required', 'string', 'max:255'],
            'translations.en.name' => ['nullable', 'string', 'max:255'],
        ], [
            'key.regex' => 'المفتاح يجب أن يكون بحروف إنجليزية صغيرة وأرقام وشرطات فقط، مثال: swimming-pool.',
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Amenity $amenity, array $data): void
    {
        foreach (config('locales.admin_english_enabled') ? ['ar', 'en'] : ['ar'] as $locale) {
            $translation = (array) ($data['translations'][$locale] ?? []);

            if (blank($translation['name'] ?? null)) {
                continue;
            }

            // جدول ترجمات المرافق يحتوي على (locale, name) فقط بلا slug.
            $amenity->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }
}
