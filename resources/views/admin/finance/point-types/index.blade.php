@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($types->total()) }}</span></h2>
            <a href="{{ admin_route('admin.finance.point-types.create') }}" class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة نوع</a>
        </div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="الاسم أو الكود"></div>
                <div class="ap-field"><label class="ap-label">الحالة</label><select name="active" class="ap-select ap-select--sm"><option value="">الكل</option><option value="1" @selected(($filters['active'] ?? '') === '1')>مفعل</option><option value="0" @selected(($filters['active'] ?? '') === '0')>معطل</option></select></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.finance.point-types.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>النوع</th><th>الكود</th><th>التصنيف</th><th>القيمة الافتراضية</th><th>القواعد</th><th>الحركات</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                    <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <td><strong>{{ $type->name }}</strong>@if($type->description)<div class="text-muted" style="font-size:11.5px">{{ \Illuminate\Support\Str::limit($type->description, 60) }}</div>@endif</td>
                            <td dir="ltr">{{ $type->slug }}</td>
                            <td>{{ $type->category ?: '—' }}</td>
                            <td>{{ number_format($type->default_points) }} {{ $type->unit_label }}</td>
                            <td>{{ number_format($type->rules_count) }}</td>
                            <td>{{ number_format($type->transactions_count) }}</td>
                            <td><span class="ap-badge ap-badge--{{ $type->is_active ? 'success' : 'warn' }}">{{ $type->is_active ? 'مفعل' : 'معطل' }}</span></td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ admin_route('admin.finance.point-types.edit', $type) }}" class="ap-btn ap-btn--ghost ap-btn--sm">تعديل</a>
                                    <form method="post" action="{{ admin_route('admin.finance.point-types.toggle', $type) }}">@csrf<button class="ap-btn ap-btn--ghost ap-btn--sm">{{ $type->is_active ? 'تعطيل' : 'تفعيل' }}</button></form>
                                    <form method="post" action="{{ admin_route('admin.finance.point-types.destroy', $type) }}" data-confirm="حذف هذا النوع؟">@csrf @method('delete')<button class="ap-btn ap-btn--danger ap-btn--sm">حذف</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><div class="ap-empty">لا توجد أنواع نقاط.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($types->hasPages())<div class="ap-pagination">{{ $types->links() }}</div>@endif
    </div>
@endsection
