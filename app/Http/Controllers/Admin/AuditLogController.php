<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * سجل النشاط — يعرض كل الأحداث المسجّلة في جدول audit_logs.
 *
 * ملاحظة: موديل AuditLog لا يحتوي على علاقة user()، لذلك نجمع أسماء
 * المستخدمين المعنيين باستعلام واحد على جدول users ونمرّرها للعرض
 * باسم $userNames (بدون أي تعديل على الموديل).
 */
class AuditLogController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $this->authorizePermission('audit.view');

        $filters = [
            'q' => trim((string) $request->input('q')) ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'from' => $this->parseDate($request->input('from')),
            'to' => $this->parseDate($request->input('to')),
        ];

        $query = AuditLog::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($filters['q'] !== null) {
            $query->where('event', 'like', '%' . $filters['q'] . '%');
        }

        if ($filters['user_id'] !== null) {
            $query->where('user_id', $filters['user_id']);
        }

        if ($filters['from'] !== null) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }

        if ($filters['to'] !== null) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $logs = $query
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        // أسماء المستخدمين المعنيين — استعلام واحد لتفادي مشكلة N+1.
        $userNames = User::query()
            ->whereIn('id', $logs->getCollection()->pluck('user_id')->filter()->unique()->values()->all())
            ->pluck('name', 'id')
            ->all();

        $users = User::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.audit.index', compact('logs', 'filters', 'users', 'userNames') + [
            'title' => 'سجل النشاط',
        ]);
    }

    /** يحوّل قيمة تاريخ مُدخلة إلى Y-m-d مع تجاهل القيم غير الصحيحة. */
    private function parseDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
