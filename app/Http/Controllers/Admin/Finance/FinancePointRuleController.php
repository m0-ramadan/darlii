<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinancePointRule;
use App\Models\Finance\FinancePointType;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class FinancePointRuleController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $rules = FinancePointRule::query()
            ->with('pointType')
            ->withCount(['users', 'transactions'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->where('name', 'like', "%{$term}%");
            })
            ->when($request->filled('active'), fn ($q) => $q->where('is_active', $request->input('active') === '1'))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.finance.point-rules.index', [
            'rules' => $rules,
            'filters' => $request->only(['q', 'active']),
            'title' => 'النقاط الدورية',
            'subtitle' => 'قواعد منح النقاط تلقائيًا حسب الجدولة والفئة',
        ]);
    }

    public function create()
    {
        return view('admin.finance.point-rules.form', $this->formData(new FinancePointRule([
            'frequency' => 'monthly', 'interval_count' => 1, 'target_type' => 'all', 'points' => 50, 'is_active' => true,
        ]), 'إضافة قاعدة نقاط دورية'));
    }

    public function store(Request $request, FinanceService $service)
    {
        $data = $this->validated($request);
        $data['created_by'] = $this->admin()->id;
        $data['next_run_at'] = $this->nextRunAt($data);

        $rule = FinancePointRule::create($data);
        $rule->users()->sync($data['user_ids'] ?? []);
        AuditLogger::log('finance.point_rule.created', $rule, [], $rule->only(['name', 'points', 'frequency', 'target_type']));

        return redirect()->route('admin.finance.point-rules.index')->with('success', 'تم إنشاء قاعدة النقاط الدورية.');
    }

    public function edit(FinancePointRule $pointRule)
    {
        $pointRule->load('users');

        return view('admin.finance.point-rules.form', $this->formData($pointRule, 'تعديل قاعدة النقاط الدورية'));
    }

    public function update(Request $request, FinancePointRule $pointRule)
    {
        $data = $this->validated($request);
        $data['next_run_at'] = $this->nextRunAt($data, $pointRule);
        $old = $pointRule->only(['name', 'points', 'frequency', 'target_type', 'is_active']);
        $pointRule->update($data);
        $pointRule->users()->sync($data['user_ids'] ?? []);
        AuditLogger::log('finance.point_rule.updated', $pointRule, $old, $pointRule->fresh()->only(array_keys($old)));

        return redirect()->route('admin.finance.point-rules.index')->with('success', 'تم تحديث قاعدة النقاط.');
    }

    public function destroy(FinancePointRule $pointRule)
    {
        if ($pointRule->transactions()->exists()) {
            $pointRule->update(['is_active' => false]);

            return back()->with('warning', 'القاعدة لها حركات سابقة، فتم تعطيلها بدل حذفها.');
        }

        AuditLogger::log('finance.point_rule.deleted', $pointRule, $pointRule->only(['name']));
        $pointRule->delete();

        return back()->with('success', 'تم حذف القاعدة.');
    }

    public function toggle(FinancePointRule $pointRule)
    {
        $pointRule->update(['is_active' => ! $pointRule->is_active]);
        AuditLogger::log('finance.point_rule.toggled', $pointRule, [], ['is_active' => $pointRule->is_active]);

        return back()->with('success', $pointRule->is_active ? 'تم تفعيل القاعدة.' : 'تم تعطيل القاعدة.');
    }

    public function runDue(FinanceService $service)
    {
        $rules = FinancePointRule::query()->due()->where('is_active', true)->with('pointType')->get();
        $total = 0;
        foreach ($rules as $rule) {
            if ($rule->pointType) {
                $total += $service->runRule($rule);
            }
        }

        AuditLogger::log('finance.point_rule.run_due', null, [], ['rules' => $rules->count(), 'awarded_users' => $total]);

        return back()->with('success', "تم تشغيل القواعد المستحقة: {$rules->count()} قاعدة، ومنح النقاط إلى {$total} مستخدمًا.");
    }

    public function runNow(FinancePointRule $pointRule, FinanceService $service)
    {
        $count = $service->runRule($pointRule, true);
        AuditLogger::log('finance.point_rule.ran', $pointRule, [], ['awarded_users' => $count]);

        return back()->with('success', "تم تشغيل القاعدة ومنح النقاط إلى {$count} مستخدمًا.");
    }

    /** @return array<string,mixed> */
    private function formData(FinancePointRule $rule, string $title): array
    {
        $roles = Role::query()->whereNotIn('slug', ['super-admin', 'admin'])->orderByDesc('level')->get();
        $users = User::query()->where('status', 'active')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']))
            ->orderBy('name')->get();

        return [
            'rule' => $rule,
            'types' => FinancePointType::active()->orderBy('sort_order')->orderBy('name')->get(),
            'roles' => $roles,
            'users' => $users,
            'title' => $title,
        ];
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'point_type_id' => ['required', 'exists:finance_point_types,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points' => ['required', 'integer', 'min:1', 'max:1000000'],
            'frequency' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'interval_count' => ['required', 'integer', 'min:1', 'max:12'],
            'target_type' => ['required', Rule::in(['all', 'role', 'users'])],
            'target_role' => ['nullable', 'string', 'exists:roles,slug'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($data['target_type'] === 'role' && empty($data['target_role'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['target_role' => 'اختر الدور المستهدف.']);
        }
        if ($data['target_type'] === 'role' && in_array($data['target_role'], ['super-admin', 'admin'], true)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['target_role' => 'لا يمكن استهداف السوبر أدمن أو الأدمن في النقاط الدورية.']);
        }
        if ($data['target_type'] === 'users') {
            if (empty($data['user_ids'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['user_ids' => 'اختر مستخدمًا واحدًا على الأقل.']);
            }
            $invalid = User::query()->whereIn('id', $data['user_ids'] ?? [])
                ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']))->exists();
            if ($invalid) {
                throw \Illuminate\Validation\ValidationException::withMessages(['user_ids' => 'لا يمكن استهداف السوبر أدمن أو الأدمن.']);
            }
        }

        return [
            'name' => $data['name'],
            'point_type_id' => $data['point_type_id'],
            'description' => $data['description'] ?? null,
            'points' => (int) $data['points'],
            'frequency' => $data['frequency'],
            'interval_count' => (int) $data['interval_count'],
            'target_type' => $data['target_type'],
            'target_role' => $data['target_type'] === 'role' ? $data['target_role'] : null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'user_ids' => $data['target_type'] === 'users' ? array_values(array_unique($data['user_ids'] ?? [])) : [],
        ];
    }

    private function nextRunAt(array $data, ?FinancePointRule $rule = null): ?Carbon
    {
        $start = ! empty($data['starts_at']) ? Carbon::parse($data['starts_at']) : now();
        if ($rule && $rule->exists && $rule->next_run_at && $rule->next_run_at->isFuture() && $rule->is_active) {
            return $rule->next_run_at;
        }

        return $start->isFuture() ? $start : now();
    }
}
