@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($wallets->total()) }}</span></h2></div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="اسم أو بريد"></div>
                <div class="ap-field"><label class="ap-label">الترتيب</label><select name="sort" class="ap-select ap-select--sm"><option value="points" @selected(($filters['sort'] ?? '')==='points')>أعلى نقاط</option><option value="cash" @selected(($filters['sort'] ?? '')==='cash')>أعلى رصيد نقدي</option></select></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.finance.balances.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>المستخدم</th><th>النقاط</th><th>الرصيد النقدي</th><th>إجمالي الودائع</th><th>إجمالي المكتسب</th><th>إجمالي المستهلك</th><th>إجراء</th></tr></thead>
                    <tbody>
                    @forelse ($wallets as $wallet)
                        <tr>
                            <td><a href="{{ admin_route('admin.finance.balances.show', $wallet->user_id) }}">{{ $wallet->user?->name ?? '—' }}</a><div class="text-muted" style="font-size:11px">{{ $wallet->user?->email }}</div></td>
                            <td><strong>{{ number_format($wallet->points_balance) }}</strong></td>
                            <td>{{ number_format((float) $wallet->cash_balance, 2) }} ج.م</td>
                            <td>{{ number_format((float) $wallet->total_deposited, 2) }} ج.م</td>
                            <td>{{ number_format($wallet->total_points_earned) }}</td>
                            <td>{{ number_format($wallet->total_points_spent) }}</td>
                            <td><a class="ap-btn ap-btn--sm" href="{{ admin_route('admin.finance.balances.show', $wallet->user_id) }}">كشف حساب</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="ap-empty">لا توجد أرصدة.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($wallets->hasPages())<div class="ap-pagination">{{ $wallets->links() }}</div>@endif
    </div>
@endsection
