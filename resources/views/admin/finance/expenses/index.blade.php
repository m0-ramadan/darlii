@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($expenses->total()) }}</span></h2>
            <a href="{{ admin_route('admin.finance.expenses.create') }}" class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة مصروف</a>
        </div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="البند/المرجع/ملاحظات"></div>
                <div class="ap-field"><label class="ap-label">التصنيف</label><select name="category" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($categories as $k=>$v)<option value="{{ $k }}" @selected(($filters['category'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">الطريقة</label><select name="method" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($methods as $k=>$v)<option value="{{ $k }}" @selected(($filters['method'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">من</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <div class="ap-field"><label class="ap-label">إلى</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.finance.expenses.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body" style="padding-top:0">
            <div class="ap-stat" style="max-width:320px">
                <div><div class="ap-stat__label">إجمالي المصروفات (نتائج الفلترة)</div><div class="ap-stat__value">{{ number_format($totalAmount, 2) }} <small>ج.م</small></div></div>
            </div>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>البند</th><th>التصنيف</th><th>المبلغ</th><th>الطريقة</th><th>التاريخ</th><th>المرجع</th><th>أضيف بواسطة</th><th>إجراءات</th></tr></thead>
                    <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td><strong>{{ $expense->title }}</strong>@if($expense->notes)<div class="text-muted" style="font-size:11.5px">{{ \Illuminate\Support\Str::limit($expense->notes, 60) }}</div>@endif</td>
                            <td>{{ $expense->categoryLabel() }}</td>
                            <td><strong>{{ number_format((float) $expense->amount, 2) }} {{ $expense->currency }}</strong></td>
                            <td>{{ $expense->methodLabel() }}</td>
                            <td>{{ $expense->expense_date?->format('Y/m/d') }}</td>
                            <td>{{ $expense->reference ?: '—' }}</td>
                            <td>{{ $expense->creator?->name ?? '—' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ admin_route('admin.finance.expenses.edit', $expense) }}" class="ap-btn ap-btn--ghost ap-btn--sm">تعديل</a>
                                    <form method="post" action="{{ admin_route('admin.finance.expenses.destroy', $expense) }}" data-confirm="حذف المصروف؟">@csrf @method('delete')<button class="ap-btn ap-btn--danger ap-btn--sm">حذف</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="ap-empty">لا توجد مصروفات مطابقة.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($expenses->hasPages())<div class="ap-pagination">{{ $expenses->links() }}</div>@endif
    </div>
@endsection
