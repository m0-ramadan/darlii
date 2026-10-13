<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Services\Admin\AdminStatsService;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * مكتبة الوسائط: عرض كل صور العقارات في شبكة مصغّرات، رفع صور جديدة
 * لعقار محدد وحذف الصور نهائياً من قرص التخزين.
 */
class MediaController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    // ------------------------------------------------------------- المكتبة
    public function index(Request $request)
    {
        $this->authorizePermission('media.manage');

        $query = $this->scopedMediaQuery()
            ->when($request->filled('type'), fn ($builder) => $builder->where('type', $request->input('type')))
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = trim((string) $request->input('q'));

                $propertyIds = Property::query()
                    ->where('reference_code', 'like', "%{$term}%")
                    ->orWhereHas('translations', function ($t) use ($term): void {
                        $t->where('name', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%");
                    })
                    ->pluck('id');

                $builder->whereIn('property_id', $propertyIds);
            });

        $totalCount = (int) (clone $query)->count();
        $totalSize = (int) (clone $query)->sum('size');

        $media = $query
            ->latest()
            ->paginate((int) setting('general.items_per_page', 24))
            ->withQueryString();

        $properties = $this->scopedPropertyList();

        return view('admin.media.index', [
            'media' => $media,
            'properties' => $properties,
            'propertiesById' => $properties->keyBy('id'),
            'types' => ['image' => 'صورة', 'video' => 'فيديو', 'document' => 'مستند', 'floor_plan' => 'مخطط'],
            'filters' => $request->only(['q', 'type', 'property_id']),
            'totalCount' => $totalCount,
            'totalSize' => $totalSize,
            'title' => 'مكتبة الوسائط',
            'subtitle' => 'كل صور العقارات في مكان واحد: رفع، استعراض وحذف',
        ]);
    }

    // --------------------------------------------------------------- الرفع
    public function store(Request $request)
    {
        $this->authorizePermission('media.manage');

        $maxKb = max(1, (int) setting('storage.max_upload_mb', 8)) * 1024;

        $data = $request->validate([
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'images' => ['required', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
        ], [], [
            'property_id' => 'العقار',
            'images' => 'الصور',
            'images.*' => 'الصورة',
        ]);

        $property = Property::query()->findOrFail($data['property_id']);
        $this->guardPropertyScope($property);

        $sort = (int) $property->media()->max('sort_order');
        $hasPrimary = $property->media()->where('is_primary', true)->exists();
        $count = 0;

        foreach ((array) $request->file('images') as $image) {
            $path = $this->uploadImage($image, 'properties/' . $property->id);

            if (! $path) {
                continue;
            }

            $property->media()->create([
                'type' => 'image',
                'disk' => $this->uploadDisk(),
                'path' => $path,
                'mime_type' => $image->getClientMimeType(),
                'size' => $image->getSize(),
                'sort_order' => ++$sort,
                'is_primary' => ! $hasPrimary && $count === 0,
            ]);

            $count++;
        }

        AuditLogger::log('media.uploaded', $property, [], ['count' => $count]);

        return back()->with('success', 'تم رفع ' . $count . ' صورة إلى مكتبة الوسائط.');
    }

    // --------------------------------------------------------------- الحذف
    public function destroy(PropertyMedia $media)
    {
        $this->authorizePermission('media.manage');
        $this->guardMediaScope($media);

        $this->deleteStoredFile($media);

        AuditLogger::log('media.deleted', null, ['path' => $media->path, 'property_id' => $media->property_id]);
        $media->delete();

        return back()->with('success', 'تم حذف الصورة نهائياً.');
    }

    // ------------------------------------------------------------- داخلي
    /** صور العقارات ضمن نطاق المستخدم الحالي فقط. */
    private function scopedMediaQuery()
    {
        $query = PropertyMedia::query();

        if (! $this->admin()->isAdmin()) {
            $query->whereIn('property_id', $this->scopedPropertyIds());
        }

        return $query;
    }

    /** @return \Illuminate\Support\Collection<int, Property> */
    private function scopedPropertyList()
    {
        $query = Property::query()->with('translations');

        if (! $this->admin()->isAdmin()) {
            $query->whereIn('id', $this->scopedPropertyIds());
        }

        return $query->orderByDesc('id')->get();
    }

    /** @return \Illuminate\Support\Collection<int, int> */
    private function scopedPropertyIds()
    {
        return AdminStatsService::for($this->admin())->propertyScope()->select('id')->pluck('id');
    }

    private function guardPropertyScope(Property $property): void
    {
        if ($this->admin()->isAdmin()) {
            return;
        }

        abort_unless($this->scopedPropertyIds()->contains((int) $property->id), 403, 'هذا العقار ليس ضمن نطاقك.');
    }

    private function guardMediaScope(PropertyMedia $media): void
    {
        if ($this->admin()->isAdmin()) {
            return;
        }

        abort_unless($this->scopedPropertyIds()->contains((int) $media->property_id), 403, 'هذه الصورة ليست ضمن نطاقك.');
    }

    /** حذف الملف الفعلي من قرصه مع تجاهل أي خطأ في القرص. */
    private function deleteStoredFile(PropertyMedia $media): void
    {
        $path = (string) $media->path;

        if ($path === '' || str_starts_with($path, 'http')) {
            return;
        }

        $disk = (string) ($media->disk ?: $this->uploadDisk());

        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            // قرص غير مهيأ — نكمل عملية حذف السجل.
        }

        if ($disk !== 'public') {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                // لا شيء.
            }
        }
    }
}
