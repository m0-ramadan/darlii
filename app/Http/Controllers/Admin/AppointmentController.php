<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * المواعيد: كل زيارات المعاينة والمكالمات المجدولة، مع تعديل الحالة
 * والموعد وإمكانية الحذف. العرض مقيّد بنطاق المستخدم (الوكيل يرى مواعيده).
 */
class AppointmentController extends Controller
{    use InteractsWithAdminPanel;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('appointment.manage');

        $appointments = $this->scopedAppointments()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('scheduled_at', $request->input('date')))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('scheduled_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('scheduled_at', '<=', $request->input('to')))
            ->orderByDesc('scheduled_at')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'filters' => $request->only(['status', 'date', 'from', 'to']),
            'statuses' => ['requested', 'confirmed', 'completed', 'cancelled'],
            'statusLabels' => $this->statusLabels(),
            'leadNames' => $this->leadNames($appointments->pluck('lead_id')->all()),
            'propertyNames' => $this->propertyNames($appointments->pluck('property_id')->all()),
            'agentNames' => $this->userNames($appointments->pluck('agent_id')->all()),
            'title' => 'المواعيد',
            'subtitle' => 'كل مواعيد المعاينة والمكالمات المجدولة مع العملاء',
        ]);
    }

    // -------------------------------------------------------------- التحديث
    public function update(Request $request, Appointment $appointment)
    {
        $this->authorizePermission('appointment.manage');
        $this->guardScope($appointment);

        $data = $request->validate([
            'status' => ['required', Rule::in(['requested', 'confirmed', 'completed', 'cancelled'])],
            'scheduled_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $old = $appointment->only(['status', 'scheduled_at']);

        $appointment->update([
            'status' => $data['status'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'notes' => $data['notes'] ?? $appointment->notes,
        ]);

        AuditLogger::log('appointment.updated', $appointment, $old, $appointment->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث الموعد بنجاح.');
    }

    // --------------------------------------------------------------- الحذف
    public function destroy(Appointment $appointment)
    {
        $this->authorizePermission('appointment.manage');
        $this->guardScope($appointment);

        AuditLogger::log('appointment.deleted', $appointment, ['status' => $appointment->status]);
        $appointment->delete();

        return back()->with('success', 'تم حذف الموعد.');
    }

    // ------------------------------------------------------------- داخلي
    private function scopedAppointments()
    {
        $query = Appointment::query();
        $user = $this->admin();

        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereIn('agent_id', $user->visibleUserIds());
    }

    private function guardScope(Appointment $appointment): void
    {
        if ($this->admin()->isAdmin()) {
            return;
        }

        $visible = $this->admin()->visibleUserIds();

        abort_unless(
            $appointment->agent_id !== null && $visible->contains((int) $appointment->agent_id),
            403,
            'هذا الموعد ليس ضمن نطاقك.'
        );
    }

    /**
     * أسماء العملاء — باستعلام واحد بدل إضافة علاقة على النموذج.
     *
     * @param  array<int, int|null>  $ids
     * @return array<int, string>
     */
    private function leadNames(array $ids): array
    {
        $ids = $this->cleanIds($ids);

        if ($ids === []) {
            return [];
        }

        return Lead::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    /**
     * @param  array<int, int|null>  $ids
     * @return array<int, string>
     */
    private function propertyNames(array $ids): array
    {
        $ids = $this->cleanIds($ids);

        if ($ids === []) {
            return [];
        }

        return Property::query()
            ->with('translations')
            ->whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn (Property $property) => [$property->id => $property->displayName('ar')])
            ->all();
    }

    /**
     * @param  array<int, int|null>  $ids
     * @return array<int, string>
     */
    private function userNames(array $ids): array
    {
        $ids = $this->cleanIds($ids);

        if ($ids === []) {
            return [];
        }

        return User::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    /**
     * @param  array<int, int|null>  $ids
     * @return array<int, int>
     */
    private function cleanIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }

    /** @return array<string, string> */
    private function statusLabels(): array
    {
        return [
            'requested' => 'مطلوب',
            'confirmed' => 'مؤكد',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];
    }
}
