<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * إدارة مقالات المدونة مع صورة رئيسية وكاتب وترجمة عربية/إنجليزية.
 */
class PostController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    /** الحالات المسموح بها للمقال. */
    private const STATUSES = ['draft', 'published'];

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('content.manage');

        $filters = $request->only(['q', 'status']);

        $posts = Post::query()
            ->with(['translations', 'author'])
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);

                $query->where(function ($inner) use ($term): void {
                    $inner->whereHas('translations', function ($translation) use ($term): void {
                        $translation->where('title', 'like', '%' . $term . '%')
                            ->orWhere('slug', 'like', '%' . $term . '%');
                    })->orWhereHas('author', fn ($author) => $author->where('name', 'like', '%' . $term . '%'));
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', $filters['status']))
            ->latest('id')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'filters' => $filters,
            'statuses' => self::STATUSES,
            'title' => 'المقالات',
            'subtitle' => 'إدارة مقالات المدونة وصورها وكتّابها',
        ]);
    }

    // ------------------------------------------------------------ الإنشاء
    public function create()
    {
        $this->authorizePermission('content.manage');

        return view('admin.posts.form', [
            'post' => new Post(['status' => 'draft']),
            'title' => 'إضافة مقال جديد',
            'subtitle' => 'اكتب المقال بالعربية ثم أضف الترجمة الإنجليزية عند الحاجة',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('content.manage');

        $data = $this->validated($request);

        $post = DB::transaction(function () use ($data, $request): Post {
            $post = Post::query()->create([
                'author_id' => $this->admin()->id,
                'status' => $data['status'],
                'featured_image' => $this->storeImage($request->file('featured_image'), 'posts'),
                'published_at' => $data['status'] === 'published' ? now() : null,
            ]);

            $this->syncTranslations($post, $data);

            return $post;
        });

        AuditLogger::log('post.created', $post, [], ['status' => $post->status]);

        return redirect()->route('admin.posts.index')->with('success', 'تم إنشاء المقال بنجاح.');
    }

    // ------------------------------------------------------------- التعديل
    public function edit(Post $post)
    {
        $this->authorizePermission('content.manage');

        return view('admin.posts.form', [
            'post' => $post->load(['translations', 'author']),
            'title' => 'تعديل: ' . ($post->translation('ar')?->title ?? 'مقال #' . $post->id),
            'subtitle' => 'حدّث محتوى المقال والصورة الرئيسية وإعدادات النشر',
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorizePermission('content.manage');

        $data = $this->validated($request, $post);
        $old = $post->only(['status', 'published_at', 'featured_image']);

        DB::transaction(function () use ($post, $data, $request): void {
            $payload = [
                'status' => $data['status'],
                'published_at' => $data['status'] === 'published' ? ($post->published_at ?? now()) : null,
            ];

            if ($request->hasFile('featured_image')) {
                $path = $this->storeImage($request->file('featured_image'), 'posts');

                if ($path) {
                    $this->deleteUpload($post->featured_image);
                    $payload['featured_image'] = $path;
                }
            } elseif ($request->boolean('remove_featured_image')) {
                $this->deleteUpload($post->featured_image);
                $payload['featured_image'] = null;
            }

            $post->update($payload);
            $this->syncTranslations($post, $data);
        });

        AuditLogger::log('post.updated', $post, $old, $post->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث المقال بنجاح.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(Post $post)
    {
        $this->authorizePermission('content.manage');

        AuditLogger::log('post.deleted', $post, ['status' => $post->status], []);

        // المقال يستخدم الحذف الناعم، لذلك نُبقي الترجمات والصورة ليمكن استرجاعه.
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'تم حذف المقال.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function validated(Request $request, ?Post $post = null): array
    {
        $maxKb = max(1, (int) setting('storage.max_upload_mb', 8)) * 1024;

        return $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . $maxKb],
            'remove_featured_image' => ['nullable', 'boolean'],
            'translations.ar.title' => ['required', 'string', 'max:255'],
            'translations.ar.slug' => ['nullable', 'string', 'max:255'],
            'translations.ar.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.ar.content' => ['nullable', 'string'],
            'translations.ar.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.ar.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.en.title' => ['nullable', 'string', 'max:255'],
            'translations.en.slug' => ['nullable', 'string', 'max:255'],
            'translations.en.excerpt' => ['nullable', 'string', 'max:1000'],
            'translations.en.content' => ['nullable', 'string'],
            'translations.en.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.en.seo_description' => ['nullable', 'string', 'max:500'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Post $post, array $data): void
    {
        foreach (['ar', 'en'] as $locale) {
            $translation = (array) ($data['translations'][$locale] ?? []);

            if (blank($translation['title'] ?? null)) {
                continue;
            }

            $existing = $post->translations()->where('locale', $locale)->first();

            $translation['slug'] = $this->uniqueTranslationSlug(
                (string) (($translation['slug'] ?? '') ?: $translation['title']),
                $locale,
                $existing?->id
            );

            $post->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }

    /**
     * رفع الصورة عبر السمة UploadsImages مع بديل آمن: الدالة الأصلية تعتمد على
     * UploadedFile::validate() غير الموجودة في الإطار، والتحقق يتم هنا مسبقاً.
     */
    private function storeImage(?UploadedFile $file, string $folder): ?string
    {
        if (! $file) {
            return null;
        }

        try {
            return $this->uploadImage($file, $folder);
        } catch (\Throwable $e) {
            return $file->store($folder . '/' . now()->format('Y/m'), $this->uploadDisk());
        }
    }

    private function uniqueTranslationSlug(string $value, string $locale, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(12);
        $slug = $base;
        $counter = 2;

        while (PostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}
