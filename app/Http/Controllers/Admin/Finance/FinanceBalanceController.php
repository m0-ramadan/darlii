<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceWallet;
use App\Models\User;
use App\Services\Finance\FinanceService;
use Illuminate\Http\Request;

class FinanceBalanceController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $wallets = FinanceWallet::query()
            ->with('user')
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->input('sort') === 'cash', fn ($q) => $q->orderByDesc('cash_balance'), fn ($q) => $q->orderByDesc('points_balance'))
            ->paginate(25)->withQueryString();

        return view('admin.finance.balances.index', [
            'wallets' => $wallets,
            'filters' => $request->only(['q', 'sort']),
            'title' => 'أرصدة المستخدمين',
            'subtitle' => 'أرصدة النقاط والنقد لكل مستخدم',
        ]);
    }

    public function show(User $user, FinanceService $service)
    {
        abort_if($user->isSuperAdmin(), 404);

        $wallet = $service->walletFor($user);

        return view('admin.finance.balances.show', [
            'user' => $user,
            'wallet' => $wallet,
            'balances' => $user->pointBalances()->with('pointType')->orderByDesc('balance')->get(),
            'transactions' => $user->pointTransactions()->with('pointType')->latest('occurred_at')->limit(25)->get(),
            'deposits' => $user->deposits()->latest()->limit(15)->get(),
            'title' => 'مالية المستخدم: ' . $user->name,
            'subtitle' => 'كشف حساب النقاط والودائع',
        ]);
    }
}
