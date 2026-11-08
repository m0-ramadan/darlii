@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($rules->total()) }}</span></h2>
            <div class="d-flex gap-2">
                <form method="post" action="{{ admin_route('admin.finance.point-rules.run-due') }}" data-confirm="تشغيل كل القواعد المستحقة الآن؟">@csrf<button class="ap-btn ap-btn--ghost ap-btn--sm">تشغيل المستحق</button></form>
                <a href="{{ admin_route('admin.finance.point-rules.create') }}" class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة قاعدة</a>
            </div>
        </div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="اسم القاعدة"></div>
                <div class="ap-field"><label class="ap-label">الحالة</label><select name="active" class="ap-select ap-select--sm"><option value="">الكل</option><option value="1" @selected(($filters['active'] ?? '') === '1')>مفعلة</option><option value="0" @selected(($filters['active'] ?? '') === '0')>معطلة</option></select></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.finance.point-rules.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>القاعدة</th><th>النوع</th><th>النقاط</th><th>التكرار</th><th>الفئة</th><th>المستفيدون</th><th>آخر تشغيل</th><th>التشغيل القادم</th><th>الحالة</th><th>إجراءات</th></tr></thead>
                    <tbody>
                    @forelse ($rules as $rule)
                        <tr>
                            <td><strong>{{ $rule->name }}</strong>@if($rule->description)<div class="text-muted" style="font-size:11.5px">{{ \Illuminate\Support\Str::limit($rule->description, 50) }}</div>@endif</td>
                            <td>{{ $rule->pointType?->name ?? '—' }}</td>
                            <td><strong>{{ number_format($rule->points) }}</strong></td>
                            <td>{{ $rule->frequencyLabel() }} / {{ $rule->interval_count }}</td>
                            <td>{{ $rule->target_type === 'role' ? ($rule->target_role ?: '—') : ($rule->target_type === 'users' ? 'مستخدمون محددون' : 'كل المستخدمين') }}</td>
                            <td>{{ $rule->target_type === 'users' ? number_format($rule->users_count) : '—' }}</td>
                            <td>{{ $rule->last_run_at?->format('Y/m/d H:i') ?? '—' }}</td>
                            <td>{{ $rule->next_run_at?->format('Y/m/d H:i') ?? '—' }}</td>
                            <td><span class="ap-badge ap-badge--{{ $rule->is_active ? 'success' : 'warn' }}">{{ $rule->is_active ? 'مفعلة' : 'معطلة' }}</span></td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="post" action="{{ admin_route('admin.finance.point-rules.run', $rule) }}" data-confirm="تشغيل القاعدة الآن ومنح النقاط للمستهدفين؟">@csrf<button class="ap-btn ap-btn--sm">تشغيل</button></form>
                                    <a href="{{ admin_route('admin.finance.point-rules.edit', $rule) }}" class="ap-btn ap-btn--ghost ap-btn--sm">تعديل</a>
                                    <form method="post" action="{{ admin_route('admin.finance.point-rules.toggle', $rule) }}">@csrf<button class="ap-btn ap-btn--ghost ap-btn--sm">{{ $rule->is_active ? 'تعطيل' : 'تفعيل' }}</button></form>
                                    <form method="post" action="{{ admin_route('admin.finance.point-rules.destroy', $rule) }}" data-confirm="حذف القاعدة؟">@csrf @method('delete')<button class="ap-btn ap-btn--danger ap-btn--sm">حذف</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="ap-empty">لا توجد قواعد نقاط دورية.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($rules->hasPages())<div class="ap-pagination">{{ $rules->links() }}</div>@endif
    </div>
@endsection
