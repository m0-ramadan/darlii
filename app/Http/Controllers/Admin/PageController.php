<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * إدارة صفحات الموقع الثابتة (من نحن، الشروط، الخصوصية…).
 */
class PageController extends Controller
{
    use InteractsWithAdminPanel;

    /** الحالات المسموح بها للصفحة. */
    private const STATUSES = ['draft', 'published'];

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('content.manage');

        $filters = $request->only(['q', 'status']);

        $pages = Page::query()
            ->with('translations')
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);

                $query->where(function ($inner) use ($term): void {
                    $inner->where('key', 'like', '%' . $term . '%')
                        ->orWhereHas('translations', function ($translation) use ($term): void {
                            $translation->where('title', 'like', '%' . $term . '%')
                                ->orWhere('slug', 'like', '%' . $term . '%');
                        });
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->latest('id')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.pages.index', [
            'pages' => $pages,
            'filters' => $filters,
            'statuses' => self::STATUSES,
            'title' => 'الصفحات',
            'subtitle' => 'إدارة صفحات الموقع الثابتة بالعربية مع ترجمة إنجليزية اختيارية',
        ]);
    }

    // ------------------------------------------------------------ الإنشاء
    public function create()
    {
        $this->authorizePermission('content.manage');

        return view('admin.pages.form', [
            'page' => new Page(['status' => 'draft']),
            'title' => 'إضافة صفحة جديدة',
            'subtitle' => 'أنشئ صفحة جديدة ثم انشرها متى شئت',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('content.manage');

        $data = $this->validated($request);

        $page = DB::transaction(function () use ($data): Page {
            $page = Page::query()->create([
                'key' => ($data['key'] ?? null) ?: null,
                'status' => $data['status'],
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            $this->syncTranslations($page, $data);

            return $page;
        });

        AuditLogger::log('page.created', $page, [], ['key' => $page->key, 'status' => $page->status]);

        return redirect()->route('admin.pages.index')->with('success', 'تم إنشاء الصفحة بنجاح.');
    }

    // ------------------------------------------------------------- التعديل
    public function edit(Page $page)
    {
        $this->authorizePermission('content.manage');

        return view('admin.pages.form', [
            'page' => $page->load('translations'),
            'title' => 'تعديل: ' . ($page->translation('ar')?->title ?? 'صفحة #' . $page->id),
            'subtitle' => 'حدّث محتوى الصفحة وإعدادات النشر والتحسين لمحركات البحث',
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $this->authorizePermission('content.manage');

        $data = $this->validated($request, $page);
        $old = $page->only(['key', 'status', 'published_at']);

        DB::transaction(function () use ($page, $data): void {
            $page->update([
                'key' => ($data['key'] ?? null) ?: null,
                'status' => $data['status'],
                'published_at' => $data['status'] === 'published' ? ($page->published_at ?? now()) : null,
            ]);

            $this->syncTranslations($page, $data);
        });

        AuditLogger::log('page.updated', $page, $old, $page->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث الصفحة بنجاح.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(Page $page)
    {
        $this->authorizePermission('content.manage');

        AuditLogger::log('page.deleted', $page, ['key' => $page->key, 'status' => $page->status], []);

        // الصفحة تستخدم الحذف الناعم، لذلك نُبقي الترجمات ليمكن استرجاعها لاحقاً.
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'تم حذف الصفحة.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function validated(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'key' => ['nullable', 'string', 'max:190', Rule::unique('pages', 'key')->ignore($page?->id)],
            'status' => ['required', Rule::in(self::STATUSES)],
            'translations.ar.title' => ['required', 'string', 'max:255'],
            'translations.ar.slug' => ['nullable', 'string', 'max:255'],
            'translations.ar.content' => ['nullable', 'string'],
            'translations.ar.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.ar.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.en.title' => ['nullable', 'string', 'max:255'],
            'translations.en.slug' => ['nullable', 'string', 'max:255'],
            'translations.en.content' => ['nullable', 'string'],
            'translations.en.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.en.seo_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Page $page, array $data): void
    {
        foreach (config('locales.admin_english_enabled') ? ['ar', 'en'] : ['ar'] as $locale) {
            $translation = (array) ($data['translations'][$locale] ?? []);

            if (blank($translation['title'] ?? null)) {
                continue;
            }

            $existing = $page->translations()->where('locale', $locale)->first();

            $translation['slug'] = $this->uniqueTranslationSlug(
                (string) (($translation['slug'] ?? '') ?: $translation['title']),
                $locale,
                $existing?->id
            );

            $page->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }

    /** عنوان عربي بلا مقابل لاتيني يعطي slug عشوائياً لأن العمود غير قابل للفراغ. */
    private function uniqueTranslationSlug(string $value, string $locale, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(12);
        $slug = $base;
        $counter = 2;

        while (PageTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
