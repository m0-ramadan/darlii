<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use App\Modules\Lead\Domain\Enums\LeadStatus;
use App\Services\Admin\AdminStatsService;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * العملاء المحتملون: كل الاستفسارات الواردة مع الفلترة، المتابعة،
 * الإسناد للسماسرة وسجل الأنشطة. كل الاستعلامات مقيّدة بنطاق المستخدم.
 */
class LeadController extends Controller
{    use InteractsWithAdminPanel;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizeLeadView();

        $base = AdminStatsService::for($this->admin())->leadScope();

        $leads = (clone $base)
            ->with(['property.translations', 'project.translations'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->input('q'));

                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhere('reference_code', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('assigned_to'), fn ($query) => $query->where('assigned_to', $request->input('assigned_to')))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->input('source')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to')))
            ->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        $sources = (clone $base)
            ->select('source')
            ->distinct()
            ->pluck('source')
            ->filter()
            ->values();

        return view('admin.leads.index', [
            'leads' => $leads,
            'filters' => $request->only(['q', 'status', 'assigned_to', 'source', 'from', 'to']),
            'statuses' => LeadStatus::cases(),
            'statusLabels' => $this->statusLabels(),
            'sources' => $sources,
            'assignableUsers' => $this->assignableAgents(),
            'userNames' => $this->userNames($leads->pluck('assigned_to')->all()),
            'title' => 'العملاء المحتملون',
            'subtitle' => 'متابعة كل الاستفسارات الواردة وإسنادها للسماسرة',
        ]);
    }

    // -------------------------------------------------------------- التفاصيل
    public function show(Lead $lead)
    {
        $this->authorizeLeadView();
        $this->guardLeadScope($lead);

        $lead->load(['property.translations', 'project.translations', 'activities']);

        $activities = $lead->activities->sortByDesc('created_at')->values();

        return view('admin.leads.show', [
            'lead' => $lead,
            'activities' => $activities,
            'statuses' => LeadStatus::cases(),
            'statusLabels' => $this->statusLabels(),
            'assignableUsers' => $this->assignableAgents(),
            'userNames' => $this->userNames(
                $activities->pluck('user_id')->merge([$lead->assigned_to])->all()
            ),
            'activityTypes' => [
                'note' => 'ملاحظة',
                'call' => 'مكالمة هاتفية',
                'email' => 'بريد إلكتروني',
                'meeting' => 'اجتماع',
            ],
            'title' => 'تفاصيل العميل: ' . $lead->name,
            'subtitle' => 'كود العميل: ' . $lead->reference_code,
        ]);
    }

    // -------------------------------------------------------------- التحديث
    public function update(Request $request, Lead $lead)
    {
        $this->authorizePermission('lead.manage');
        $this->guardLeadScope($lead);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_map(fn (LeadStatus $status) => $status->value, LeadStatus::cases()))],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $assignmentChanged = ($data['assigned_to'] ?? null) !== null
            && (int) $data['assigned_to'] !== (int) $lead->assigned_to;

        if ($assignmentChanged) {
            $this->authorizePermission('lead.assign');
            $this->assertAssignable((int) $data['assigned_to']);
        }

        $old = $lead->only(['status', 'assigned_to', 'message']);

        $lead->update([
            'status' => $data['status'],
            'message' => $data['message'] ?? $lead->message,
            'assigned_to' => $data['assigned_to'] ?? $lead->assigned_to,
        ]);

        if ($assignmentChanged && $lead->assigned_to !== null) {
            $this->logActivity(
                $lead,
                'assigned',
                'تم إسناد العميل إلى ' . ($this->userNames([$lead->assigned_to])[$lead->assigned_to] ?? 'مستخدم')
            );
        }

        AuditLogger::log('lead.updated', $lead, $old, $lead->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث بيانات العميل بنجاح.');
    }

    // --------------------------------------------------------------- الإسناد
    public function assign(Request $request, Lead $lead)
    {
        $this->authorizePermission('lead.assign');
        $this->guardLeadScope($lead);

        $data = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $this->assertAssignable((int) $data['assigned_to']);

        $old = $lead->assigned_to;
        $lead->update(['assigned_to' => $data['assigned_to']]);

        $assigneeName = $this->userNames([$lead->assigned_to])[$lead->assigned_to] ?? 'مستخدم';
        $this->logActivity($lead, 'assigned', 'تم إسناد العميل إلى ' . $assigneeName . '.');

        AuditLogger::log('lead.assigned', $lead, ['assigned_to' => $old], ['assigned_to' => $lead->assigned_to]);

        return back()->with('success', 'تم إسناد العميل بنجاح.');
    }

    // --------------------------------------------------------------- النشاط
    public function activity(Request $request, Lead $lead)
    {
        $this->authorizePermission('lead.manage');
        $this->guardLeadScope($lead);

        $data = $request->validate([
            'type' => ['nullable', Rule::in(['note', 'call', 'email', 'meeting'])],
            'description' => ['required', 'string', 'max:2000'],
        ], [], ['description' => 'الوصف']);

        $type = $data['type'] ?? 'note';

        $this->logActivity($lead, $type, $data['description']);

        AuditLogger::log('lead.activity', $lead, [], ['type' => $type]);

        return back()->with('success', 'تم تسجيل النشاط بنجاح.');
    }

    // --------------------------------------------------------------- الحذف
    public function destroy(Lead $lead)
    {
        $this->authorizePermission('lead.manage');
        $this->guardLeadScope($lead);

        AuditLogger::log('lead.deleted', $lead, ['reference_code' => $lead->reference_code]);
        $lead->delete();

        return redirect(admin_route('admin.leads.index'))->with('success', 'تم حذف العميل.');
    }

    // ------------------------------------------------------------- داخلي
    /** القراءة متاحة لمن يرى الكل أو من يرى عملاءه فقط. */
    private function authorizeLeadView(): void
    {
        $this->authorizePermission($this->admin()->canSeeAllLeads() ? 'lead.view-all' : 'lead.view-own');
    }

    private function guardLeadScope(Lead $lead): void
    {
        if ($this->admin()->canSeeAllLeads()) {
            return;
        }

        $visible = $this->admin()->visibleUserIds();

        abort_unless(
            $lead->assigned_to !== null && $visible->contains((int) $lead->assigned_to),
            403,
            'هذا العميل ليس ضمن نطاقك.'
        );
    }

    private function assertAssignable(int $userId): void
    {
        $allowed = $this->assignableAgents()->pluck('id')->map(fn ($id) => (int) $id)->all();

        abort_unless(in_array($userId, $allowed, true), 403, 'لا يمكنك الإسناد لهذا المستخدم.');
    }

    /** @return Collection<int, User> */
    private function assignableAgents(): Collection
    {
        $user = $this->admin();

        $query = User::query()->where('status', 'active');

        if (! $user->isAdmin()) {
            $query->whereIn('id', $user->visibleUserIds());
        }

        return $query->orderBy('name')->get();
    }

    /**
     * أسماء المستخدمين المطلوبة للعرض — يتم حلها باستعلام واحد بدل إضافة
     * علاقة على نموذج Lead.
     *
     * @param  array<int, int|null>  $ids
     * @return array<int, string>
     */
    private function userNames(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return [];
        }

        return User::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    private function logActivity(Lead $lead, string $type, string $description): void
    {
        LeadActivity::query()->create([
            'lead_id' => $lead->id,
            'user_id' => $this->admin()->id,
            'type' => $type,
            'description' => $description,
        ]);
    }

    /** @return array<string, string> */
    private function statusLabels(): array
    {
        return [
            'new' => 'جديد',
            'assigned' => 'مُسند',
            'contacted' => 'تم التواصل',
            'qualified' => 'مؤهل',
            'follow_up' => 'متابعة',
            'won' => 'تم البيع',
            'lost' => 'مفقود',
            'spam' => 'مزعج',
        ];
    }
}
