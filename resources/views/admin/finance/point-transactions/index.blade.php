@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($transactions->total()) }}</span></h2>
            <a href="{{ admin_route('admin.finance.point-transactions.create') }}" class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) تعديل نقاط</a>
        </div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث بالمستخدم</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="الاسم أو البريد"></div>
                <div class="ap-field"><label class="ap-label">النوع</label><select name="point_type_id" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((string)($filters['point_type_id'] ?? '')===(string)$type->id)>{{ $type->name }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">المصدر</label><select name="source" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach(['manual'=>'يدوي','recurring'=>'دوري'] as $k=>$v)<option value="{{ $k }}" @selected(($filters['source'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">من</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <div class="ap-field"><label class="ap-label">إلى</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.finance.point-transactions.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>التاريخ</th><th>المستخدم</th><th>النوع</th><th>النقاط</th><th>الرصيد بعدها</th><th>المصدر</th><th>الوصف</th><th>بواسطة</th></tr></thead>
                    <tbody>
                    @forelse ($transactions as $tx)
                        <tr>
                            <td>{{ $tx->occurred_at?->format('Y/m/d H:i') }}</td>
                            <td><a href="{{ admin_route('admin.finance.balances.show', $tx->user_id) }}">{{ $tx->user?->name ?? '—' }}</a></td>
                            <td>{{ $tx->pointType?->name ?? '—' }}</td>
                            <td><strong style="color:{{ $tx->points >= 0 ? '#16a34a' : '#dc2626' }}">{{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}</strong></td>
                            <td>{{ number_format($tx->balance_after) }}</td>
                            <td><span class="ap-badge">{{ $tx->sourceLabel() }}</span></td>
                            <td>{{ $tx->description ?: '—' }}</td>
                            <td>{{ $tx->creator?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="ap-empty">لا توجد حركات مطابقة.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($transactions->hasPages())<div class="ap-pagination">{{ $transactions->links() }}</div>@endif
    </div>
@endsection
