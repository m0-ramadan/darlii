@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card" style="margin-bottom:18px;">
        <div class="ap-card__head">
            <h2 class="ap-card__title">{{ $user->name }} <small class="text-muted">{{ $user->email }}</small></h2>
            <div class="d-flex gap-2">
                <a href="{{ admin_route('admin.finance.point-transactions.create', ['user_id' => $user->id]) }}" class="ap-btn ap-btn--sm">تعديل النقاط</a>
                <a href="{{ admin_route('admin.finance.deposits.create', ['user_id' => $user->id]) }}" class="ap-btn ap-btn--sm">إضافة وديعة</a>
                <a href="{{ admin_route('admin.finance.balances.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a>
            </div>
        </div>
    </div>
    <div class="ap-grid ap-grid--4" style="margin-bottom:18px;">
        <div class="ap-stat"><div><div class="ap-stat__label">رصيد النقاط</div><div class="ap-stat__value">{{ number_format($wallet->points_balance) }}</div></div></div>
        <div class="ap-stat"><div><div class="ap-stat__label">الرصيد النقدي</div><div class="ap-stat__value">{{ number_format((float) $wallet->cash_balance, 2) }} <small>ج.م</small></div></div></div>
        <div class="ap-stat"><div><div class="ap-stat__label">إجمالي الودائع</div><div class="ap-stat__value">{{ number_format((float) $wallet->total_deposited, 2) }} <small>ج.م</small></div></div></div>
        <div class="ap-stat"><div><div class="ap-stat__label">إجمالي النقاط المكتسبة</div><div class="ap-stat__value">{{ number_format($wallet->total_points_earned) }}</div></div></div>
    </div>

    <div class="ap-grid ap-grid--2">
        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">أرصدة النقاط حسب النوع</h2></div>
            <div class="ap-card__body ap-card__body--tight">
                <table class="ap-table"><thead><tr><th>النوع</th><th>الرصيد</th><th>مكتسب</th><th>مستهلك</th></tr></thead><tbody>
                @forelse($balances as $balance)
                    <tr><td>{{ $balance->pointType?->name ?? '—' }}</td><td><strong>{{ number_format($balance->balance) }}</strong></td><td>{{ number_format($balance->total_earned) }}</td><td>{{ number_format($balance->total_spent) }}</td></tr>
                @empty
                    <tr><td colspan="4"><div class="ap-empty">لا توجد أرصدة نقاط.</div></td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">آخر الودائع</h2></div>
            <div class="ap-card__body ap-card__body--tight">
                <table class="ap-table"><thead><tr><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr></thead><tbody>
                @forelse($deposits as $deposit)
                    <tr><td>{{ number_format((float)$deposit->amount,2) }} {{ $deposit->currency }}</td><td>{{ $deposit->statusLabel() }}</td><td>{{ $deposit->created_at?->format('Y/m/d') }}</td></tr>
                @empty
                    <tr><td colspan="3"><div class="ap-empty">لا توجد ودائع.</div></td></tr>
                @endforelse
                </tbody></table>
            </div>
        </div>
    </div>

    <div class="ap-card" style="margin-top:18px;">
        <div class="ap-card__head"><h2 class="ap-card__title">آخر حركات النقاط</h2></div>
        <div class="ap-card__body ap-card__body--tight">
            <table class="ap-table"><thead><tr><th>التاريخ</th><th>النوع</th><th>النقاط</th><th>الرصيد بعدها</th><th>المصدر</th><th>الوصف</th></tr></thead><tbody>
            @forelse($transactions as $tx)
                <tr><td>{{ $tx->occurred_at?->format('Y/m/d H:i') }}</td><td>{{ $tx->pointType?->name }}</td><td><strong style="color:{{ $tx->points>=0 ? '#16a34a':'#dc2626' }}">{{ $tx->points>0?'+':'' }}{{ number_format($tx->points) }}</strong></td><td>{{ number_format($tx->balance_after) }}</td><td>{{ $tx->sourceLabel() }}</td><td>{{ $tx->description ?: '—' }}</td></tr>
            @empty
                <tr><td colspan="6"><div class="ap-empty">لا توجد حركات.</div></td></tr>
            @endforelse
            </tbody></table>
        </div>
    </div>
@endsection
