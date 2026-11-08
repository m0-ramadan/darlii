@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    <div class="ap-grid ap-grid--4" style="margin-bottom:18px;">
        <div class="ap-stat"><div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div><div><div class="ap-stat__label">إجمالي الطلبات</div><div class="ap-stat__value">{{ number_format(array_sum($counts)) }}</div></div></div>
        <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 20])</div><div><div class="ap-stat__label">جديد</div><div class="ap-stat__value">{{ number_format($counts['new'] ?? 0) }}</div></div></div>
        <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 20])</div><div><div class="ap-stat__label">قيد التنفيذ</div><div class="ap-stat__value">{{ number_format($counts['in_progress'] ?? 0) }}</div></div></div>
        <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div><div><div class="ap-stat__label">تم</div><div class="ap-stat__value">{{ number_format($counts['completed'] ?? 0) }}</div></div></div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 18]) {{ $title }} <span class="ap-badge">{{ number_format($requests->total()) }}</span></h2>
            <div class="d-flex gap-2">
                <a href="{{ admin_route('admin.customer-requests.stats') }}" class="ap-btn ap-btn--ghost ap-btn--sm">الإحصائيات</a>
                <a href="{{ admin_route('admin.customer-requests.create') }}" class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة طلب</a>
            </div>
        </div>
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="اسم/تليفون/احتياج"></div>
                <div class="ap-field"><label class="ap-label">الحالة</label><select name="status" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">العنوان</label><select name="location_id" class="ap-select ap-select--sm"><option value="">الكل</option>@foreach($locations as $loc)<option value="{{ $loc['id'] }}" @selected((string)($filters['location_id'] ?? '')===(string)$loc['id'])>{{ $loc['label'] }}</option>@endforeach</select></div>
                <div class="ap-field"><label class="ap-label">من</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <div class="ap-field"><label class="ap-label">إلى</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="ap-input ap-input--sm"></div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.customer-requests.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead><tr><th>العميل</th><th>الاحتياج</th><th>العنوان</th><th>الحالة</th><th>أضيف بواسطة</th><th>التاريخ</th><th>إجراء</th></tr></thead>
                    <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td><strong>{{ $req->customer_name }}</strong><div class="text-muted" dir="ltr">{{ $req->phone }}</div></td>
                            <td style="max-width:320px">{{ \Illuminate\Support\Str::limit($req->need, 120) }}@if($req->notes)<div class="text-muted" style="font-size:11.5px">ملاحظات: {{ \Illuminate\Support\Str::limit($req->notes, 60) }}</div>@endif</td>
                            <td>{{ $req->locationName() }}@if($req->address_details)<div class="text-muted" style="font-size:11.5px">{{ $req->address_details }}</div>@endif</td>
                            <td>
                                <form method="post" action="{{ admin_route('admin.customer-requests.status', $req) }}">@csrf @method('patch')
                                    <select name="status" class="ap-select ap-select--sm" style="min-width:120px" onchange="this.form.submit()">@foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected($req->status===$k)>{{ $v }}</option>@endforeach</select>
                                </form>
                            </td>
                            <td>{{ $req->creator?->name ?? '—' }}<div class="text-muted" style="font-size:11px">{{ $req->created_at?->format('Y/m/d H:i') }}</div></td>
                            <td>{{ $req->updated_at?->format('Y/m/d') }}</td>
                            <td><div class="d-flex gap-2 flex-wrap">
                                <a href="{{ admin_route('admin.customer-requests.edit', $req) }}" class="ap-btn ap-btn--ghost ap-btn--sm">تعديل</a>
                                <form method="post" action="{{ admin_route('admin.customer-requests.destroy', $req) }}" data-confirm="حذف الطلب؟">@csrf @method('delete')<button class="ap-btn ap-btn--danger ap-btn--sm">حذف</button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="ap-empty">لا توجد طلبات مطابقة.</div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requests->hasPages())<div class="ap-pagination">{{ $requests->links() }}</div>@endif
    </div>
@endsection
