<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyMedia;
use App\Models\PropertyStatusHistory;
use App\Models\PropertyType;
use App\Modules\Property\Domain\Enums\PropertyStatus;
use App\Services\Admin\AdminStatsService;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PropertyController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    // ------------------------------------------------------------- القوائم
    public function index(Request $request)
    {
        $this->authorizePermission('property.view');

        $properties = $this->applyFilters($this->scopedPropertyQuery(), $request)
            ->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.properties.index', $this->sharedData() + [
            'properties' => $properties,
            'filters' => $request->only(['q', 'status', 'purpose', 'assignment', 'category_id', 'location_id', 'owner_id', 'sort']),
            'title' => 'كل العقارات',
        ]);
    }

    public function pending(Request $request)
    {
        $this->authorizePermission('property.approve');

        $properties = $this->applyFilters($this->scopedPropertyQuery()->where('status', PropertyStatus::PendingReview->value), $request)
            ->oldest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.properties.index', $this->sharedData() + [
            'properties' => $properties,
            'filters' => $request->only(['q', 'purpose', 'category_id', 'location_id']),
            'title' => 'عقارات بانتظار المراجعة',
            'subtitle' => 'اعتمد أو ارفض العقارات المضافة من السماسرة والمسؤولين',
        ]);
    }

    public function featured(Request $request)
    {
        $this->authorizePermission('property.feature');

        $properties = $this->applyFilters($this->scopedPropertyQuery()->where('is_featured', true), $request)
            ->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.properties.index', $this->sharedData() + [
            'properties' => $properties,
            'filters' => $request->only(['q', 'status']),
            'title' => 'العقارات المميزة',
        ]);
    }

    public function assignments(Request $request)
    {
        $this->authorizePermission('property.assign');

        $properties = $this->applyFilters(
            $this->scopedPropertyQuery()->where(fn($q) => $q->whereNull('managed_by')->orWhereNull('owner_id')),
            $request
        )->latest()->paginate((int) setting('general.items_per_page', 15))->withQueryString();

        return view('admin.properties.assignments', $this->sharedData() + [
            'properties' => $properties,
            'filters' => $request->only(['q']),
        ]);
    }

    // ------------------------------------------------------------ الإنشاء
    public function create()
    {
        $this->authorizePermission('property.create');

        return view('admin.properties.form', $this->sharedData() + [
            'property' => new Property(['purpose' => 'sale', 'status' => PropertyStatus::Draft->value, 'currency_id' => Currency::query()->where('is_default', true)->value('id')]),
            'title' => 'إضافة عقار جديد',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('property.create');
        $data = $this->validated($request);

        $property = DB::transaction(function () use ($data, $request): Property {
            $property = Property::query()->create(array_merge($this->columnPayload($data), [
                'uuid' => (string) Str::uuid(),
                'reference_code' => ($data['reference_code'] ?? null) ?: $this->nextReference(),
                'owner_id' => $data['owner_id'] ?? $this->admin()->id,
                'created_by' => $this->admin()->id,
                'managed_by' => $data['managed_by'] ?? null,
                'assignment_status' => ($data['managed_by'] ?? null) ? 'assigned' : 'unassigned',
                'last_assigned_at' => ($data['managed_by'] ?? null) ? now() : null,
                'published_at' => ($data['status'] ?? 'draft') === 'published' ? now() : null,
                'sold_at' => ($data['status'] ?? 'draft') === 'sold' ? now() : null,
            ]));

            $property->setPriceMeta([
                'price_type' => $data['price_type'] ?? 'fixed',
                'price_notes' => $data['price_notes'] ?? null,
            ]);

            $this->syncTranslations($property, $data);
            $this->syncRelations($property, $data, $request);

            return $property;
        });

        AuditLogger::log('property.created', $property, [], ['reference_code' => $property->reference_code]);
        $this->logStatus($property, null, $property->status?->value ?? 'draft', 'إنشاء العقار');

        return redirect()->route('admin.properties.edit', $property)
            ->with('success', 'تم إنشاء العقار ' . $property->reference_code . ' بنجاح.');
    }

    // -------------------------------------------------------------- العرض
    public function show(Property $property)
    {
        $this->authorizePermission('property.view');
        $this->guardScope($property);

        $stats = AdminStatsService::for($this->admin());

        return view('admin.properties.show', $this->sharedData() + [
            'property' => $property->load(['translations', 'media', 'amenities.translations', 'owner', 'manager', 'creator', 'category.translations', 'location.translations', 'project.translations', 'currency', 'statusHistory']),
            'stats' => $stats->propertyStats($property),
            'title' => $property->displayName('ar'),
            'subtitle' => 'كود العقار: ' . $property->reference_code,
        ]);
    }

    public function stats(Property $property)
    {
        $this->authorizePermission('property.stats');
        $this->guardScope($property);

        $stats = AdminStatsService::for($this->admin());

        return view('admin.properties.stats', [
            'property' => $property->load(['translations', 'owner', 'manager', 'currency']),
            'stats' => $stats->propertyStats($property),
            'trend' => $stats->propertyTrend($property, 30),
            'leads' => $property->leads()->latest()->limit(20)->get(),
            'title' => 'إحصائيات: ' . $property->displayName('ar'),
        ]);
    }

    public function edit(Property $property)
    {
        $this->authorizePermission($this->admin()->hasPermission('property.update-any') ? 'property.update-any' : 'property.update-own');
        $this->guardScope($property);

        return view('admin.properties.form', $this->sharedData() + [
            'property' => $property->load(['translations', 'media', 'amenities']),
            'title' => 'تعديل: ' . $property->displayName('ar'),
            'subtitle' => 'كود العقار: ' . $property->reference_code,
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $this->authorizePermission($this->admin()->hasPermission('property.update-any') ? 'property.update-any' : 'property.update-own');
        $this->guardScope($property);

        $data = $this->validated($request, $property);
        $old = $property->only(['price', 'status', 'purpose', 'is_featured', 'managed_by', 'owner_id']);

        DB::transaction(function () use ($property, $data, $request): void {
            $payload = $this->columnPayload($data);
            if (isset($data['status'])) {
                if ($data['status'] === 'sold') {
                    $payload['sold_at'] = $property->sold_at ?? now();
                } else {
                    $payload['sold_at'] = null;
                }
            }
            $property->update($payload);
            $property->setPriceMeta([
                'price_type' => $data['price_type'] ?? 'fixed',
                'price_notes' => $data['price_notes'] ?? null,
            ]);
            $this->syncTranslations($property, $data);
            $this->syncRelations($property, $data, $request);
        });

        AuditLogger::log('property.updated', $property, $old, $property->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث بيانات العقار بنجاح.');
    }

    public function destroy(Property $property)
    {
        $this->authorizePermission('property.delete');
        $this->guardScope($property);

        AuditLogger::log('property.deleted', $property, ['reference_code' => $property->reference_code]);
        $property->delete();

        return redirect()->route('admin.properties.index')->with('success', 'تم حذف العقار.');
    }

    // ---------------------------------------------------- اعتماد / رفض
    public function approve(Property $property)
    {
        $this->authorizePermission('property.approve');
        $this->guardScope($property);

        $old = $property->status?->value;
        $property->update(['status' => PropertyStatus::Published->value, 'published_at' => now()]);
        $this->logStatus($property, $old, 'published', 'اعتماد من لوحة التحكم');
        AuditLogger::log('property.approved', $property, ['status' => $old], ['status' => 'published']);

        return back()->with('success', 'تم اعتماد ونشر العقار.');
    }

    public function reject(Request $request, Property $property)
    {
        $this->authorizePermission('property.approve');
        $this->guardScope($property);

        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
        $old = $property->status?->value;
        $property->update(['status' => PropertyStatus::Rejected->value, 'admin_note' => $data['reason']]);
        $this->logStatus($property, $old, 'rejected', $data['reason']);
        AuditLogger::log('property.rejected', $property, ['status' => $old], ['status' => 'rejected', 'reason' => $data['reason']]);

        return back()->with('success', 'تم رفض العقار وتسجيل السبب.');
    }

    public function toggleFeature(Property $property)
    {
        $this->authorizePermission('property.feature');
        $this->guardScope($property);

        $property->update([
            'is_featured' => ! $property->is_featured,
            'featured_until' => ! $property->is_featured ? now()->addDays(30) : null,
        ]);

        AuditLogger::log('property.featured', $property, [], ['is_featured' => $property->is_featured]);

        return back()->with('success', $property->is_featured ? 'تم تمييز العقار.' : 'تم إلغاء تمييز العقار.');
    }

    /**
     * إسناد العقار لمسؤول أو سمسار — هذا هو الربط الإجباري بين العقار وصاحبه.
     */
    public function assign(Request $request, Property $property)
    {
        $this->authorizePermission('property.assign');
        $this->guardScope($property);

        $data = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'managed_by' => ['nullable', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $allowed = $this->assignableUsers()->pluck('id')->all();
        abort_unless(in_array((int) $data['owner_id'], $allowed, true), 403, 'لا يمكنك الإسناد لهذا المستخدم.');

        if (! empty($data['managed_by'])) {
            abort_unless(in_array((int) $data['managed_by'], $allowed, true), 403, 'لا يمكنك الإسناد لهذا المسؤول.');
        }

        $old = $property->only(['owner_id', 'managed_by', 'assignment_status']);
        $property->update([
            'owner_id' => $data['owner_id'],
            'managed_by' => $data['managed_by'] ?? $data['owner_id'],
            'assignment_status' => 'assigned',
            'last_assigned_at' => now(),
            'admin_note' => $data['note'] ?? $property->admin_note,
        ]);

        AuditLogger::log('property.assigned', $property, $old, $property->only(['owner_id', 'managed_by']));

        return back()->with('success', 'تم إسناد العقار بنجاح.');
    }

    /** عمليات جماعية على العقارات المحددة. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:properties,id'],
            'action' => ['required', Rule::in(['approve', 'reject', 'feature', 'unfeature', 'delete', 'assign'])],
            'value' => ['nullable'],
        ]);

        $action = $data['action'];
        $this->authorizePermission(match ($action) {
            'approve', 'reject' => 'property.approve',
            'feature', 'unfeature' => 'property.feature',
            'delete' => 'property.delete',
            'assign' => 'property.assign',
        });

        $properties = Property::query()->whereIn('id', $data['ids'])->get();
        $done = 0;

        foreach ($properties as $property) {
            if (! $this->admin()->isAdmin() && ! in_array($property->owner_id, $this->admin()->visibleUserIds()->all(), true)) {
                continue;
            }

            match ($action) {
                'approve' => tap($property, fn($p) => $p->update(['status' => 'published', 'published_at' => now()])),
                'reject' => tap($property, fn($p) => $p->update(['status' => 'rejected'])),
                'feature' => tap($property, fn($p) => $p->update(['is_featured' => true])),
                'unfeature' => tap($property, fn($p) => $p->update(['is_featured' => false])),
                'delete' => tap($property, fn($p) => $p->delete()),
                'assign' => tap($property, fn($p) => $p->update([
                    'owner_id' => (int) $data['value'],
                    'managed_by' => (int) $data['value'],
                    'assignment_status' => 'assigned',
                    'last_assigned_at' => now(),
                ])),
            };

            $done++;
        }

        AuditLogger::log('property.bulk', null, [], ['action' => $action, 'count' => $done]);

        return back()->with('success', 'تم تنفيذ الإجراء على ' . $done . ' عقار.');
    }

    // ------------------------------------------------------------- الوسائط
    public function uploadMedia(Request $request, Property $property)
    {
        $this->authorizePermission('property.update-any');
        $this->guardScope($property);

        $request->validate([
            'images' => ['required', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:' . ((int) setting('storage.max_upload_mb', 8)) * 1024],
        ]);

        $sort = (int) $property->media()->max('sort_order');
        $hasPrimary = $property->media()->where('is_primary', true)->exists();
        $count = 0;

        foreach ($request->file('images') as $image) {
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

        AuditLogger::log('property.media.uploaded', $property, [], ['count' => $count]);

        return back()->with('success', 'تم رفع ' . $count . ' صورة.');
    }

    public function primaryMedia(Property $property, PropertyMedia $media)
    {
        $this->authorizePermission('property.update-any');
        abort_unless((int) $media->property_id === (int) $property->id, 404);

        $property->media()->update(['is_primary' => false]);
        $media->update(['is_primary' => true]);

        return back()->with('success', 'تم تعيين الصورة الرئيسية.');
    }

    public function destroyMedia(Property $property, PropertyMedia $media)
    {
        $this->authorizePermission('property.update-any');
        abort_unless((int) $media->property_id === (int) $property->id, 404);

        $this->deleteUpload($media->path);
        $media->delete();

        return back()->with('success', 'تم حذف الصورة.');
    }

    // ------------------------------------------------------------- داخلي
    /** @return array<string, mixed> */
    private function sharedData(): array
    {
        return [
            'categories' => PropertyCategory::query()->with('translations')->get(),
            'types' => PropertyType::query()->with('translations')->get(),
            'locations' => Location::query()->with('translations')->orderBy('id')->get(),
            'amenities' => Amenity::query()->with('translations')->get(),
            'currencies' => Currency::query()->where('is_active', true)->get(),
            'projects' => Project::query()->with('translations')->get(),
            'statuses' => PropertyStatus::cases(),
            'assignableUsers' => $this->assignableUsers(),
            'managers' => $this->assignableManagers(),
        ];
    }

    private function applyFilters($query, Request $request)
    {
        return $query
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->where(function ($inner) use ($term): void {
                    $inner->where('reference_code', 'like', "%{$term}%")
                        ->orWhereHas('translations', fn($t) => $t->where('name', 'like', "%{$term}%")->orWhere('slug', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('purpose'), fn($q) => $q->where('purpose', $request->input('purpose')))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->filled('location_id'), fn($q) => $q->where('location_id', $request->input('location_id')))
            ->when($request->filled('owner_id'), fn($q) => $q->where(fn($inner) => $inner->where('owner_id', $request->input('owner_id'))->orWhere('managed_by', $request->input('owner_id'))))
            ->when($request->filled('assignment'), function ($q) use ($request): void {
                $request->input('assignment') === 'assigned'
                    ? $q->whereNotNull('managed_by')
                    : $q->whereNull('managed_by');
            })
            ->when($request->input('sort') === 'views', fn($q) => $q->orderByDesc('views_count'))
            ->when($request->input('sort') === 'price_asc', fn($q) => $q->orderBy('price'))
            ->when($request->input('sort') === 'price_desc', fn($q) => $q->orderByDesc('price'));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Property $property = null): array
    {
        $validated = $request->validate([
            'reference_code' => ['nullable', 'string', 'max:60', Rule::unique('properties', 'reference_code')->ignore($property?->id)],
            'purpose' => ['required', Rule::in(['sale', 'rent'])],
            'status' => ['required', Rule::in(array_map(fn($c) => $c->value, PropertyStatus::cases()))],
            'price_type' => ['nullable', Rule::in(['fixed', 'negotiable', 'contact'])],
            'price_notes' => ['nullable', 'string', 'max:5000'],
            'price' => [
                Rule::requiredIf(fn() => $request->input('price_type', 'fixed') !== 'contact'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'currency_id' => ['required', 'exists:currencies,id'],
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'property_type_id' => ['nullable', 'exists:property_types,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'managed_by' => ['nullable', 'exists:users,id'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'parking_spaces' => ['nullable', 'integer', 'min:0', 'max:100'],
            'floors_count' => ['nullable', 'integer', 'min:0', 'max:200'],
            'floor_number' => ['nullable', 'integer', 'min:-5', 'max:200'],
            'year_built' => ['nullable', 'integer', 'min:1800', 'max:2100'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'land_area' => ['nullable', 'numeric', 'min:0'],
            'area_unit' => ['nullable', Rule::in(['m2', 'ft2', 'acre', 'hectare'])],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_featured' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id'],
            'translations.ar.name' => ['required', 'string', 'max:190'],
            'translations.ar.slug' => ['nullable', 'string', 'max:190'],
            'translations.ar.short_description' => ['nullable', 'string', 'max:500'],
            'translations.ar.description' => ['nullable', 'string'],
            'translations.ar.address_text' => ['nullable', 'string', 'max:250'],
            'translations.ar.seo_title' => ['nullable', 'string', 'max:190'],
            'translations.ar.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.en.name' => ['nullable', 'string', 'max:190'],
            'translations.en.slug' => ['nullable', 'string', 'max:190'],
            'translations.en.short_description' => ['nullable', 'string', 'max:500'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.en.address_text' => ['nullable', 'string', 'max:250'],
            'translations.en.seo_title' => ['nullable', 'string', 'max:190'],
            'translations.en.seo_description' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['price_type'] = $validated['price_type'] ?? 'fixed';
        if ($validated['price_type'] === 'contact' && blank($validated['price'] ?? null)) {
            $validated['price'] = 0;
        }

        return $validated;
    }

    /** @param array<string, mixed> $data */
    private function columnPayload(array $data): array
    {
        $columns = [
            'reference_code',
            'purpose',
            'status',
            'price',
            'currency_id',
            'category_id',
            'property_type_id',
            'location_id',
            'project_id',
            'owner_id',
            'managed_by',
            'bedrooms',
            'bathrooms',
            'parking_spaces',
            'floors_count',
            'floor_number',
            'year_built',
            'area',
            'land_area',
            'area_unit',
            'latitude',
            'longitude',
            'is_featured',
            'expires_at',
            'sold_at',
            'admin_note',
        ];

        if (Property::hasPriceTypeColumn()) {
            $columns[] = 'price_type';
        }
        if (Property::hasPriceNotesColumn()) {
            $columns[] = 'price_notes';
        }

        return collect($data)->only($columns)->filter(fn($value, $key) => ! in_array($key, ['is_featured'], true) || $value !== null)->all();
    }

    /** @param array<string, mixed> $data */
    private function syncTranslations(Property $property, array $data): void
    {
        foreach ((array) ($data['translations'] ?? []) as $locale => $translation) {
            if (blank($translation['name'] ?? null)) {
                continue;
            }

            $translation['slug'] = $this->uniqueSlug(
                ($translation['slug'] ?? null) ?: $translation['name'],
                \App\Models\PropertyTranslation::class,
                'slug',
                $property->translations()->where('locale', $locale)->value('id')
            );

            $property->translations()->updateOrCreate(['locale' => $locale], $translation);
        }
    }

    /** @param array<string, mixed> $data */
    private function syncRelations(Property $property, array $data, Request $request): void
    {
        $property->amenities()->sync($data['amenities'] ?? []);

        if ($request->hasFile('images')) {
            $hasPrimary = $property->media()->where('is_primary', true)->exists();
            $sort = (int) $property->media()->max('sort_order');

            foreach ($request->file('images') as $index => $image) {
                if (! $image) {
                    continue;
                }

                $path = $this->uploadImage($image, 'properties/' . $property->id);

                if (! $path) {
                    continue;
                }

                $property->media()->create([
                    'path' => $path,
                    'disk' => $this->uploadDisk(),
                    'mime_type' => $image->getClientMimeType(),
                    'size' => $image->getSize(),
                    'sort_order' => ++$sort,
                    'is_primary' => ! $hasPrimary && $index === 0,
                ]);
            }
        }
    }

    private function nextReference(): string
    {
        $prefix = (string) setting('properties.reference_prefix', 'DAR');

        return $prefix . '-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
    }

    private function guardScope(Property $property): void
    {
        if ($this->admin()->isAdmin()) {
            return;
        }

        $allowed = $this->admin()->visibleUserIds()->all();
        abort_unless(
            in_array($property->owner_id, $allowed, true) || in_array($property->managed_by, $allowed, true),
            403,
            'هذا العقار ليس ضمن نطاقك.'
        );
    }

    private function logStatus(Property $property, ?string $old, string $new, ?string $reason = null): void
    {
        try {
            PropertyStatusHistory::query()->create([
                'property_id' => $property->id,
                'old_status' => $old,
                'new_status' => $new,
                'changed_by' => $this->admin()->id,
                'reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            // التاريخ اختياري — لا نوقف العملية.
        }
    }
}
