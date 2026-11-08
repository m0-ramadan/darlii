@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 20])</div>
            <div><div class="ap-stat__label">ودائع معتمدة</div><div class="ap-stat__value">{{ number_format($summary['total_deposited'], 2) }} <small>ج.م</small></div></div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'alert', 'size' => 20])</div>
            <div><div class="ap-stat__label">ودائع قيد المراجعة</div><div class="ap-stat__value">{{ number_format($summary['pending_deposits']) }}</div></div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--danger">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 20])</div>
            <div><div class="ap-stat__label">إجمالي المصروفات</div><div class="ap-stat__value">{{ number_format($summary['expenses'], 2) }} <small>ج.م</small></div></div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 20])</div>
            <div><div class="ap-stat__label">الصافي النقدي</div><div class="ap-stat__value" style="color:{{ $summary['net_cash'] >= 0 ? '#16a34a' : '#dc2626' }}">{{ number_format($summary['net_cash'], 2) }} <small>ج.م</small></div></div>
        </div>
        @if($pointsEnabled)
            <div class="ap-stat"><div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div><div><div class="ap-stat__label">نقاط ممنوحة</div><div class="ap-stat__value">{{ number_format($summary['points_issued']) }}</div></div></div>
            <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 20])</div><div><div class="ap-stat__label">إجمالي أرصدة النقاط</div><div class="ap-stat__value">{{ number_format($summary['points_balance']) }}</div></div></div>
        @endif
    </div>

    <div class="ap-grid ap-grid--2">
        @if($pointsEnabled)
            <div class="ap-card">
                <div class="ap-card__head"><h2 class="ap-card__title">آخر حركات النقاط</h2><a href="{{ admin_route('admin.finance.point-transactions.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">كل الحركات</a></div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap"><table class="ap-table">
                        <thead><tr><th>المستخدم</th><th>النوع</th><th>النقاط</th><th>التاريخ</th></tr></thead>
                        <tbody>
                        @forelse ($recentTransactions as $tx)
                            <tr><td>{{ $tx->user?->name ?? '—' }}</td><td>{{ $tx->pointType?->name ?? '—' }}</td><td><strong style="color:{{ $tx->points >= 0 ? '#16a34a' : '#dc2626' }}">{{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}</strong></td><td>{{ $tx->occurred_at?->format('Y/m/d H:i') }}</td></tr>
                        @empty
                            <tr><td colspan="4"><div class="ap-empty">لا توجد حركات نقاط بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table></div>
                </div>
            </div>
        @endif

        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">آخر المصروفات</h2><a href="{{ admin_route('admin.finance.expenses.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">كل المصروفات</a></div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap"><table class="ap-table">
                    <thead><tr><th>البند</th><th>التصنيف</th><th>المبلغ</th><th>التاريخ</th></tr></thead>
                    <tbody>
                    @forelse ($recentExpenses as $expense)
                        <tr><td>{{ $expense->title }}</td><td>{{ $expense->categoryLabel() }}</td><td><strong>{{ number_format((float) $expense->amount, 2) }} ج.م</strong></td><td>{{ $expense->expense_date?->format('Y/m/d') }}</td></tr>
                    @empty
                        <tr><td colspan="4"><div class="ap-empty">لا توجد مصروفات بعد.</div></td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="margin-top:18px;">
        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">ودائع بانتظار الاعتماد</h2><a href="{{ admin_route('admin.finance.deposits.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">كل الودائع</a></div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap"><table class="ap-table">
                    <thead><tr><th>المودع</th><th>المبلغ</th><th>الطريقة</th><th></th></tr></thead>
                    <tbody>
                    @forelse ($pendingDeposits as $deposit)
                        <tr><td>{{ $deposit->depositorDisplayName() }}</td><td>{{ number_format((float) $deposit->amount, 2) }} {{ $deposit->currency }}</td><td>{{ $deposit->methodLabel() }}</td><td><a class="ap-btn ap-btn--sm" href="{{ admin_route('admin.finance.deposits.show', $deposit) }}">مراجعة</a></td></tr>
                    @empty
                        <tr><td colspan="4"><div class="ap-empty">لا توجد ودائع معلقة.</div></td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>

        @if($pointsEnabled)
            <div class="ap-card">
                <div class="ap-card__head"><h2 class="ap-card__title">أعلى أرصدة النقاط</h2><a href="{{ admin_route('admin.finance.balances.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">كل الأرصدة</a></div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap"><table class="ap-table">
                        <thead><tr><th>المستخدم</th><th>النقاط</th><th>الرصيد النقدي</th></tr></thead>
                        <tbody>
                        @forelse ($topBalances as $wallet)
                            <tr><td><a href="{{ admin_route('admin.finance.balances.show', $wallet->user_id) }}">{{ $wallet->user?->name ?? '—' }}</a></td><td><strong>{{ number_format($wallet->points_balance) }}</strong></td><td>{{ number_format((float) $wallet->cash_balance, 2) }} ج.م</td></tr>
                        @empty
                            <tr><td colspan="3"><div class="ap-empty">لا توجد أرصدة بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table></div>
                </div>
            </div>
        @endif
    </div>
@endsection
