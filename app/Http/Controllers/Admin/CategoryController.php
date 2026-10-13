<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyCategoryTranslation;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * إدارة تصنيفات العقارات (شقة، فيلا، أرض…) مع نموذج إضافة/تعديل داخل القائمة.
 */
class CategoryController extends Controller
{
    use InteractsWithAdminPanel;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('content.taxonomies');

        $filters = $request->only(['q', 'status']);

        $categories = PropertyCategory::query()
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

        // لا توجد علاقة properties() على الموديل، لذلك نحسب الاستخدام يدوياً.
        $propertyCounts = Property::query()
            ->selectRaw('category_id, COUNT(*) as total')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $editing = $request->filled('edit')
            ? PropertyCategory::query()->with('translations')->find($request->integer('edit'))
            : null;

        return view('admin.categories.index', [
            'categories' => $categories,
            'filters' => $filters,
            'propertyCounts' => $propertyCounts,
            'editing' => $editing,
            'title' => $editing ? 'تعديل تصنيف: ' . ($editing->translation('ar')?->name ?? '#' . $editing->id) : 'تصنيفات العقارات',
            'subtitle' => 'التصنيفات التي تظهر في الفلاتر وصفحات العقارات',
        ]);
    }

    // ------------------------------------------------------------ الإضافة
    public function store(Request $request)
    {
        $this->authorizePermission('content.taxonomies');

        $data = $this->validated($request);

        $category = DB::transaction(function () use ($data): PropertyCategory {
            $category = PropertyCategory::query()->create([
                'key' => $data['key'],
                'icon' => ($data['icon'] ?? null) ?: null,
                'status' => (bool) ($data['status'] ?? false),
            ]);

            $this->syncTranslations($category, $data);

            return $category;
        });

        AuditLogger::log('category.created', $category, [], ['key' => $category->key]);

        return redirect()->route('admin.categories.index')->with('success', 'تمت إضافة التصنيف بنجاح.');
    }

    // ------------------------------------------------------------- التعديل
    public function update(Request $request, PropertyCategory $category)
    {
        $this->authorizePermission('content.taxonomies');

        $data = $this->validated($request, $category);
        $old = $category->only(['key', 'icon', 'status']);

        DB::transaction(function () use ($category, $data): void {
            $category->update([
                'key' => $data['key'],
                'icon' => ($data['icon'] ?? null) ?: null,
                'status' => (bool) ($data['status'] ?? false),
            ]);

            $this->syncTranslations($category, $data);
        });

        AuditLogger::log('category.updated', $category, $old, $category->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث التصنيف بنجاح.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(PropertyCategory $category)
    {
        $this->authorizePermission('content.taxonomies');

        $properties = Property::query()->where('category_id', $category->id)->count();

        if ($properties > 0) {
            return back()->with('error', 'لا يمكن الحذف: ' . $properties . ' عقار مرتبط بهذا التصنيف. غيّر تصنيف العقارات أولاً.');
        }

        AuditLogger::log('category.deleted', $category, ['key' => $category->key], []);

        $category->translations()->delete();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'تم حذف التصنيف.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function validated(Request $request, ?PropertyCategory $category = null): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('property_categories', 'key')->ignore($category?->id)],
            'icon' => ['nullable', 'string', 'max:190'],
            'status' => ['nullable', 'boolean'],
            'translations.ar.name' => ['required', 'string', 'max:255'],
            'translations.ar.slug' => ['nullable', 'string', 'max:255'],
            'translations.en.name' => ['nullable', 'string', 'max:255'],
            'translations.en.slug' => ['nullable', 'string', 'max:255'],
        ], [
            'key.regex' => 'المفتاح يجب أن يكون بحروف إنجليزية صغيرة وأرقام وشرطات فقط، مثال: apartment.',
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(PropertyCategory $category, array $data): void
    {
        foreach (['ar', 'en'] as $locale) {
            $translation = (array) ($data['translations'][$locale] ?? []);

            if (blank($translation['name'] ?? null)) {
                continue;
            }

            $existing = $category->translations()->where('locale', $locale)->first();

            $translation['slug'] = $this->uniqueTranslationSlug(
                (string) (($translation['slug'] ?? '') ?: ($translation['name'] ?? '') ?: $data['key']),
                $locale,
                $existing?->id
            );

            $category->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }

    /** الاسم العربي لا ينتج slug لاتيني، لذلك نستعمل المفتاح كبديل ثم قيمة عشوائية. */
    private function uniqueTranslationSlug(string $value, string $locale, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(12);
        $slug = $base;
        $counter = 2;

        while (PropertyCategoryTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
