<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceDeposit;
use App\Models\Finance\FinanceExpense;
use App\Models\Finance\FinancePointTransaction;
use App\Models\Finance\FinancePointType;
use App\Models\Finance\FinanceWallet;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    use InteractsWithAdminPanel;

    public function index()
    {
        return view('admin.finance.index', [
            'summary' => $this->summary(),
            'recentTransactions' => FinancePointTransaction::with(['user', 'pointType'])
                ->latest('occurred_at')->limit(8)->get(),
            'pendingDeposits' => FinanceDeposit::with('user')->pending()->latest()->limit(6)->get(),
            'topBalances' => FinanceWallet::with('user')->orderByDesc('points_balance')->limit(6)->get(),
            'recentExpenses' => FinanceExpense::with('creator')->latest('expense_date')->latest('id')->limit(6)->get(),
            'pointsEnabled' => (bool) config('finance.points_enabled', false),
            'title' => 'الماليات',
            'subtitle' => 'النقاط والودائع والأرصدة — صلاحية السوبر أدمن فقط',
        ]);
    }

    public function stats(Request $request)
    {
        $from = $request->filled('from') ? \Illuminate\Support\Carbon::parse($request->input('from'))->startOfDay() : now()->subMonths(11)->startOfMonth();
        $to = $request->filled('to') ? \Illuminate\Support\Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();

        $types = FinancePointType::query()
            ->withSum(['transactions as earned_points' => fn ($q) => $q->where('points', '>', 0)], 'points')
            ->withSum(['transactions as spent_points' => fn ($q) => $q->where('points', '<', 0)], 'points')
            ->withCount('transactions')
            ->orderBy('sort_order')->orderBy('name')->get();

        $depositStatuses = FinanceDeposit::query()
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(amount),0) as total')
            ->groupBy('status')->get()->keyBy('status');

        return view('admin.finance.stats', [
            'summary' => $this->summary(),
            'monthly' => $this->monthlySeries($from, $to),
            'types' => $types,
            'depositStatuses' => $depositStatuses,
            'topBalances' => FinanceWallet::with('user')->orderByDesc('points_balance')->limit(10)->get(),
            'topDepositors' => FinanceDeposit::query()->where('status', 'approved')
                ->selectRaw('user_id, MAX(depositor_name) as manual_name, COALESCE(SUM(amount),0) as total')
                ->groupBy('user_id')->orderByDesc('total')->limit(8)->with('user')->get(),
            'expensesByCategory' => FinanceExpense::query()
                ->selectRaw('category, COUNT(*) as count, COALESCE(SUM(amount),0) as total')
                ->groupBy('category')->orderByDesc('total')->get(),
            'pointsEnabled' => (bool) config('finance.points_enabled', false),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'title' => 'الإحصائيات المالية',
            'subtitle' => 'تحليل شامل للنقاط والودائع والأرصدة',
        ]);
    }

    public function exportStats(Request $request): StreamedResponse
    {
        $from = $request->filled('from') ? \Illuminate\Support\Carbon::parse($request->input('from'))->startOfDay() : now()->subMonths(11)->startOfMonth();
        $to = $request->filled('to') ? \Illuminate\Support\Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();
        $monthly = $this->monthlySeries($from, $to);

        return response()->streamDownload(function () use ($monthly): void {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['الشهر', 'نقاط مكتسبة', 'نقاط مستهلكة', 'ودائع معتمدة', 'مصروفات', 'الصافي']);
            foreach ($monthly as $row) {
                fputcsv($out, [$row['label'], $row['earned'], $row['spent'], $row['deposits'], $row['expenses'], $row['net']]);
            }
            fclose($out);
        }, 'finance-stats-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function summary(): array
    {
        return [
            'points_issued' => (int) FinancePointTransaction::where('points', '>', 0)->sum('points'),
            'points_spent' => abs((int) FinancePointTransaction::where('points', '<', 0)->sum('points')),
            'points_balance' => (int) FinanceWallet::sum('points_balance'),
            'cash_balance' => (float) FinanceWallet::sum('cash_balance'),
            'total_deposited' => (float) FinanceDeposit::where('status', 'approved')->sum('amount'),
            'pending_deposits' => (int) FinanceDeposit::pending()->count(),
            'pending_amount' => (float) FinanceDeposit::pending()->sum('amount'),
            'wallets' => (int) FinanceWallet::count(),
            'types' => (int) FinancePointType::count(),
            'expenses' => (float) FinanceExpense::sum('amount'),
            'net_cash' => (float) FinanceDeposit::where('status', 'approved')->sum('amount') - (float) FinanceExpense::sum('amount'),
        ];
    }

    /** @return list<array{label:string,earned:int,spent:int,deposits:float}> */
    private function monthlySeries(\Illuminate\Support\Carbon $from, \Illuminate\Support\Carbon $to): array
    {
        $rows = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) {
            $start = $cursor->copy()->startOfMonth();
            $end = $cursor->copy()->endOfMonth();
            $rows[] = [
                'label' => $start->format('m/Y'),
                'earned' => (int) FinancePointTransaction::whereBetween('occurred_at', [$start, $end])->where('points', '>', 0)->sum('points'),
                'spent' => abs((int) FinancePointTransaction::whereBetween('occurred_at', [$start, $end])->where('points', '<', 0)->sum('points')),
                'deposits' => (float) FinanceDeposit::where('status', 'approved')->whereBetween('approved_at', [$start, $end])->sum('amount'),
                'expenses' => (float) FinanceExpense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount'),
                'net' => (float) FinanceDeposit::where('status', 'approved')->whereBetween('approved_at', [$start, $end])->sum('amount')
                    - (float) FinanceExpense::whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])->sum('amount'),
            ];
            $cursor->addMonth();
        }

        return $rows;
    }
}
