<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinancePointType;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinancePointTypeController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $types = FinancePointType::query()
            ->withCount(['rules', 'transactions'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->where(fn ($x) => $x->where('name', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%")
                    ->orWhere('category', 'like', "%{$term}%"));
            })
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->input('active') === '1'))
            ->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.finance.point-types.index', [
            'types' => $types,
            'filters' => $request->only(['q', 'active']),
            'title' => 'أنواع النقاط',
            'subtitle' => 'أنواع النقاط وأوزانها وقيمتها الافتراضية',
        ]);
    }

    public function create()
    {
        return view('admin.finance.point-types.form', [
            'type' => new FinancePointType(['is_active' => true, 'unit_label' => 'نقطة', 'sort_order' => 0]),
            'title' => 'إضافة نوع نقاط',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['created_by'] = $this->admin()->id;

        $type = FinancePointType::create($data);
        AuditLogger::log('finance.point_type.created', $type, [], $type->only(['name', 'slug', 'default_points']));

        return redirect()->route('admin.finance.point-types.index')->with('success', 'تم إنشاء نوع النقاط.');
    }

    public function edit(FinancePointType $pointType)
    {
        return view('admin.finance.point-types.form', [
            'type' => $pointType,
            'title' => 'تعديل نوع النقاط',
        ]);
    }

    public function update(Request $request, FinancePointType $pointType)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $pointType->id);
        $old = $pointType->only(['name', 'slug', 'default_points', 'is_active']);
        $pointType->update($data);
        AuditLogger::log('finance.point_type.updated', $pointType, $old, $pointType->fresh()->only(array_keys($old)));

        return redirect()->route('admin.finance.point-types.index')->with('success', 'تم تحديث نوع النقاط.');
    }

    public function destroy(FinancePointType $pointType)
    {
        if ($pointType->transactions()->exists() || $pointType->rules()->exists()) {
            $pointType->update(['is_active' => false]);

            return back()->with('warning', 'النوع مرتبط بحركات أو قواعد، فتم تعطيله بدل حذفه.');
        }

        AuditLogger::log('finance.point_type.deleted', $pointType, $pointType->only(['name', 'slug']));
        $pointType->delete();

        return back()->with('success', 'تم حذف نوع النقاط.');
    }

    public function toggle(FinancePointType $pointType)
    {
        $pointType->update(['is_active' => ! $pointType->is_active]);
        AuditLogger::log('finance.point_type.toggled', $pointType, [], ['is_active' => $pointType->is_active]);

        return back()->with('success', $pointType->is_active ? 'تم تفعيل النوع.' : 'تم تعطيل النوع.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9\-_]+$/'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:80'],
            'unit_label' => ['required', 'string', 'max:40'],
            'default_points' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? null,
            'unit_label' => $data['unit_label'],
            'default_points' => (int) ($data['default_points'] ?? 0),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function uniqueSlug(string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        if ($base === '') {
            $base = 'type-' . Str::lower(Str::random(6));
        }
        $slug = $base;
        $i = 2;
        while (FinancePointType::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
