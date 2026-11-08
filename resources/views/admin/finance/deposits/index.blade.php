@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($deposits->total()) }}</span></h2>
            <a href="{{ admin_route('admin.finance.deposits.create') }}" class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة وديعة</a>
        </div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="اسم/بريد/مرجع"></div>
                <div class="ap-field"><label class="ap-label">الحالة</label><select name="status" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">الطريقة</label><select name="method" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($methods as $k=>$v)<option value="{{ $k }}" @selected(($filters['method'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">من</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <div class="ap-field"><label class="ap-label">إلى</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.finance.deposits.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>#</th><th>المستخدم</th><th>المبلغ</th><th>الطريقة</th><th>المرجع</th><th>الحالة</th><th>التاريخ</th><th>إجراء</th></tr></thead>
                    <tbody>
                    @forelse ($deposits as $deposit)
                        <tr>
                            <td>{{ $deposit->id }}</td>
                            <td>
                                @if($deposit->user_id)
                                    <a href="{{ admin_route('admin.finance.balances.show', $deposit->user_id) }}">{{ $deposit->user?->name ?? '—' }}</a>
                                @else
                                    <strong>{{ $deposit->depositorDisplayName() }}</strong>
                                @endif
                                <div class="text-muted" style="font-size:11px">{{ $deposit->depositorDisplayPhone() }}{{ $deposit->user_id ? ' · مستخدم مسجل' : ' · غير مسجل' }}</div>
                            </td>
                            <td><strong>{{ number_format((float) $deposit->amount, 2) }} {{ $deposit->currency }}</strong></td>
                            <td>{{ $deposit->methodLabel() }}</td>
                            <td>{{ $deposit->reference ?: '—' }}</td>
                            <td><span class="ap-badge ap-badge--{{ $deposit->status === 'approved' ? 'success' : ($deposit->status === 'rejected' ? 'danger' : 'warn') }}">{{ $deposit->statusLabel() }}</span></td>
                            <td>{{ $deposit->created_at?->format('Y/m/d H:i') }}</td>
                            <td><a href="{{ admin_route('admin.finance.deposits.show', $deposit) }}" class="ap-btn ap-btn--sm">مراجعة</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="ap-empty">لا توجد ودائع مطابقة.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($deposits->hasPages())<div class="ap-pagination">{{ $deposits->links() }}</div>@endif
    </div>
@endsection
