<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceDeposit;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;

class FinanceDepositController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $deposits = FinanceDeposit::query()
            ->with(['user', 'approver'])
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->where(fn ($x) => $x->where('reference', 'like', "%{$term}%")
                    ->orWhere('depositor_name', 'like', "%{$term}%")
                    ->orWhere('depositor_phone', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->input('method')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.finance.deposits.index', [
            'deposits' => $deposits,
            'filters' => $request->only(['q', 'status', 'method', 'from', 'to']),
            'statuses' => ['pending' => 'قيد المراجعة', 'approved' => 'معتمدة', 'rejected' => 'مرفوضة'],
            'methods' => $this->methods(),
            'title' => 'الودائع',
            'subtitle' => 'تسجيل واعتماد ورفض ودائع المستخدمين',
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.finance.deposits.create', [
            'users' => $this->users(),
            'methods' => $this->methods(),
            'selectedUser' => $request->integer('user_id') ?: null,
            'title' => 'إضافة وديعة',
            'subtitle' => 'تسجيل وديعة جديدة بحالة قيد المراجعة',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'depositor_name' => ['nullable', 'string', 'max:120', \Illuminate\Validation\Rule::requiredIf(fn () => blank($request->input('user_id')))],
            'depositor_phone' => ['nullable', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
            'currency' => ['required', 'in:EGP'],
            'method' => ['required', 'in:cash,bank_transfer,wallet,check,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = ! empty($data['user_id']) ? User::findOrFail($data['user_id']) : null;
        if ($user) {
            abort_if($user->isSuperAdmin(), 403, 'لا يمكن تسجيل وديعة للسوبر أدمن.');
        }

        $depositorName = $data['depositor_name'] ?? $user?->name;
        $depositorPhone = $data['depositor_phone'] ?? $user?->phone;

        $deposit = FinanceDeposit::create([
            'user_id' => $user?->id,
            'depositor_name' => $depositorName,
            'depositor_phone' => $depositorPhone,
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency']),
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'created_by' => $this->admin()->id,
        ]);

        AuditLogger::log('finance.deposit.created', $deposit, [], $deposit->only(['user_id', 'amount', 'currency', 'method']));

        return redirect()->route('admin.finance.deposits.show', $deposit)->with('success', 'تم تسجيل الوديعة بحالة قيد المراجعة.');
    }

    public function show(FinanceDeposit $deposit)
    {
        $deposit->load(['user', 'approver', 'creator']);

        return view('admin.finance.deposits.show', [
            'deposit' => $deposit,
            'title' => 'تفاصيل الوديعة',
            'subtitle' => 'مراجعة الوديعة واعتمادها أو رفضها',
        ]);
    }

    public function approve(Request $request, FinanceDeposit $deposit, FinanceService $service)
    {
        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']]);
        $service->approveDeposit($deposit, $this->admin(), $data['admin_notes'] ?? null);
        AuditLogger::log('finance.deposit.approved', $deposit, ['status' => 'pending'], ['status' => 'approved', 'amount' => $deposit->amount]);

        return back()->with('success', 'تم اعتماد الوديعة وإضافتها لرصيد المستخدم.');
    }

    public function reject(Request $request, FinanceDeposit $deposit, FinanceService $service)
    {
        $data = $request->validate(['admin_notes' => ['nullable', 'string', 'max:2000']]);
        $service->rejectDeposit($deposit, $this->admin(), $data['admin_notes'] ?? null);
        AuditLogger::log('finance.deposit.rejected', $deposit, ['status' => 'pending'], ['status' => 'rejected']);

        return back()->with('success', 'تم رفض الوديعة.');
    }

    public function destroy(FinanceDeposit $deposit)
    {
        abort_unless($deposit->status === 'pending', 422, 'لا يمكن حذف وديعة تمت مراجعتها.');
        AuditLogger::log('finance.deposit.deleted', $deposit, $deposit->only(['user_id', 'amount', 'status']));
        $deposit->delete();

        return redirect()->route('admin.finance.deposits.index')->with('success', 'تم حذف الوديعة.');
    }

    private function users()
    {
        return User::query()->where('status', 'active')
            ->whereDoesntHave('roles', fn ($q) => $q->where('slug', 'super-admin'))
            ->orderBy('name')->get();
    }

    /** @return array<string,string> */
    private function methods(): array
    {
        return [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل بنكي',
            'wallet' => 'محفظة إلكترونية',
            'check' => 'شيك',
            'other' => 'أخرى',
        ];
    }
}
