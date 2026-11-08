<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinancePointTransaction;
use App\Models\Finance\FinancePointType;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;

class FinancePointTransactionController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $transactions = FinancePointTransaction::query()
            ->with(['user', 'pointType', 'creator'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('point_type_id'), fn ($q) => $q->where('point_type_id', $request->input('point_type_id')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->input('source')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('occurred_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('occurred_at', '<=', $request->input('to')))
            ->latest('occurred_at')->latest('id')
            ->paginate(25)->withQueryString();

        return view('admin.finance.point-transactions.index', [
            'transactions' => $transactions,
            'types' => FinancePointType::orderBy('name')->get(),
            'filters' => $request->only(['q', 'user_id', 'point_type_id', 'source', 'from', 'to']),
            'title' => 'حركات النقاط',
            'subtitle' => 'سجل كامل لكل إضافة أو خصم للنقاط',
        ]);
    }

    public function create(Request $request)
    {
        $users = User::query()->where('status', 'active')
            ->whereDoesntHave('roles', fn ($q) => $q->where('slug', 'super-admin'))
            ->orderBy('name')->get();

        return view('admin.finance.point-transactions.create', [
            'users' => $users,
            'types' => FinancePointType::active()->orderBy('sort_order')->orderBy('name')->get(),
            'selectedUser' => $request->integer('user_id') ?: null,
            'title' => 'تعديل نقاط مستخدم',
            'subtitle' => 'إضافة أو خصم نقاط مع تسجيلها في الدفتر',
        ]);
    }

    public function store(Request $request, FinanceService $service)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'point_type_id' => ['required', 'exists:finance_point_types,id'],
            'points' => ['required', 'integer', 'not_in:0', 'min:-1000000', 'max:1000000'],
            'description' => ['required', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $user = User::findOrFail($data['user_id']);
        abort_if($user->isSuperAdmin(), 403, 'لا يمكن تعديل نقاط السوبر أدمن.');
        $type = FinancePointType::findOrFail($data['point_type_id']);
        abort_unless($type->is_active, 422, 'نوع النقاط غير مفعل.');

        $transaction = $service->applyPoints($user, $type, (int) $data['points'], [
            'source' => 'manual',
            'description' => $data['description'],
            'created_by' => $this->admin()->id,
            'occurred_at' => ! empty($data['occurred_at']) ? \Illuminate\Support\Carbon::parse($data['occurred_at']) : now(),
        ]);

        AuditLogger::log('finance.points.adjusted', $transaction, [], $transaction->only(['user_id', 'point_type_id', 'points', 'source']));

        return redirect()->route('admin.finance.point-transactions.index')
            ->with('success', 'تم تسجيل حركة النقاط بنجاح.');
    }
}
