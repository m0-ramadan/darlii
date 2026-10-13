<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * إدارة آراء العملاء المعروضة في الموقع.
 */
class TestimonialController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('content.testimonials');

        $filters = $request->only(['q', 'status']);

        $testimonials = Testimonial::query()
            ->with('translations')
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);

                $query->whereHas('translations', function ($translation) use ($term): void {
                    $translation->where('name', 'like', '%' . $term . '%')
                        ->orWhere('content', 'like', '%' . $term . '%');
                });
            })
            ->when(filled($filters['status'] ?? null), fn ($query) => $query->where('status', (bool) $filters['status']))
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
            'filters' => $filters,
            'title' => 'آراء العملاء',
            'subtitle' => 'إدارة شهادات العملاء وصورهم وترتيب ظهورهم',
        ]);
    }

    // ------------------------------------------------------------ الإنشاء
    public function create()
    {
        $this->authorizePermission('content.testimonials');

        return view('admin.testimonials.form', [
            'testimonial' => new Testimonial(['rating' => 5, 'status' => true, 'sort_order' => 0]),
            'title' => 'إضافة رأي جديد',
            'subtitle' => 'أضف شهادة عميل جديدة بالعربية مع ترجمة إنجليزية اختيارية',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('content.testimonials');

        $data = $this->validated($request);

        $testimonial = DB::transaction(function () use ($data, $request): Testimonial {
            $testimonial = Testimonial::query()->create([
                'image_path' => $this->storeImage($request->file('image'), 'testimonials'),
                'rating' => (int) $data['rating'],
                'status' => (bool) ($data['status'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ]);

            $this->syncTranslations($testimonial, $data);

            return $testimonial;
        });

        AuditLogger::log('testimonial.created', $testimonial, [], [
            'rating' => $testimonial->rating,
            'status' => $testimonial->status,
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'تمت إضافة رأي العميل بنجاح.');
    }

    // ------------------------------------------------------------- التعديل
    public function edit(Testimonial $testimonial)
    {
        $this->authorizePermission('content.testimonials');

        return view('admin.testimonials.form', [
            'testimonial' => $testimonial->load('translations'),
            'title' => 'تعديل: ' . ($testimonial->translation('ar')?->name ?? 'رأي #' . $testimonial->id),
            'subtitle' => 'حدّث بيانات الرأي وحالته وترتيبه',
        ]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $this->authorizePermission('content.testimonials');

        $data = $this->validated($request);
        $old = $testimonial->only(['rating', 'status', 'sort_order', 'image_path']);

        DB::transaction(function () use ($testimonial, $data, $request): void {
            $payload = [
                'rating' => (int) $data['rating'],
                'status' => (bool) ($data['status'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
            ];

            if ($request->hasFile('image')) {
                $path = $this->storeImage($request->file('image'), 'testimonials');

                if ($path) {
                    $this->deleteUpload($testimonial->image_path);
                    $payload['image_path'] = $path;
                }
            } elseif ($request->boolean('remove_image')) {
                $this->deleteUpload($testimonial->image_path);
                $payload['image_path'] = null;
            }

            $testimonial->update($payload);
            $this->syncTranslations($testimonial, $data);
        });

        AuditLogger::log('testimonial.updated', $testimonial, $old, $testimonial->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث رأي العميل بنجاح.');
    }

    // -------------------------------------------------------------- الحذف
    public function destroy(Testimonial $testimonial)
    {
        $this->authorizePermission('content.testimonials');

        AuditLogger::log('testimonial.deleted', $testimonial, ['rating' => $testimonial->rating], []);

        $this->deleteUpload($testimonial->image_path);
        $testimonial->translations()->delete();
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'تم حذف رأي العميل.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $maxKb = max(1, (int) setting('storage.max_upload_mb', 8)) * 1024;

        return $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'status' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . $maxKb],
            'remove_image' => ['nullable', 'boolean'],
            'translations.ar.name' => ['required', 'string', 'max:255'],
            'translations.ar.position' => ['nullable', 'string', 'max:255'],
            'translations.ar.content' => ['required', 'string'],
            'translations.en.name' => ['nullable', 'string', 'max:255'],
            'translations.en.position' => ['nullable', 'string', 'max:255'],
            'translations.en.content' => ['nullable', 'string', 'required_with:translations.en.name'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Testimonial $testimonial, array $data): void
    {
        foreach (['ar', 'en'] as $locale) {
            $translation = (array) ($data['translations'][$locale] ?? []);

            if (blank($translation['name'] ?? null)) {
                continue;
            }

            $testimonial->translations()->updateOrCreate(['locale' => $locale], $translation);
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
}
