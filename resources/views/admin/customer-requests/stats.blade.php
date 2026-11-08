@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    <div class="ap-card" style="margin-bottom:18px"><div class="ap-card__body">
        <form method="get" class="ap-filterbar">
            <div class="ap-field"><label class="ap-label">من</label><input type="date" name="from" value="{{ $from }}" class="ap-input ap-input--sm"></div>
            <div class="ap-field"><label class="ap-label">إلى</label><input type="date" name="to" value="{{ $to }}" class="ap-input ap-input--sm"></div>
            <button class="ap-btn ap-btn--sm">تحديث</button>
            <a href="{{ admin_route('admin.customer-requests.stats') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
        </form>
    </div></div>

    <div class="ap-grid ap-grid--4" style="margin-bottom:18px;">
        <div class="ap-stat"><div><div class="ap-stat__label">إجمالي الطلبات</div><div class="ap-stat__value">{{ number_format($total) }}</div></div></div>
        <div class="ap-stat"><div><div class="ap-stat__label">جديد</div><div class="ap-stat__value">{{ number_format((int)($byStatus['new'] ?? 0)) }}</div></div></div>
        <div class="ap-stat"><div><div class="ap-stat__label">قيد التنفيذ</div><div class="ap-stat__value">{{ number_format((int)($byStatus['in_progress'] ?? 0)) }}</div></div></div>
        <div class="ap-stat"><div><div class="ap-stat__label">تم</div><div class="ap-stat__value">{{ number_format((int)($byStatus['completed'] ?? 0)) }}</div></div></div>
    </div>

    <div class="ap-grid ap-grid--2">
        <div class="ap-card"><div class="ap-card__head"><h2 class="ap-card__title">حسب الحالة</h2></div><div class="ap-card__body">
            @foreach($statuses as $key=>$label)<div style="display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #eef2f6"><span>{{ $label }}</span><strong>{{ number_format((int)($byStatus[$key] ?? 0)) }}</strong></div>@endforeach
        </div></div>
        <div class="ap-card"><div class="ap-card__head"><h2 class="ap-card__title">الأشهر</h2></div><div class="ap-card__body">
            @php $max = max(1, collect($monthly)->max('total')); @endphp
            @foreach($monthly as $row)<div style="margin-bottom:10px"><div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span>{{ $row['label'] }}</span><strong>{{ $row['total'] }}</strong></div><div style="background:#f1f5f9;border-radius:6px;height:7px"><div style="height:7px;border-radius:6px;background:#111e33;width:{{ max(2, round(($row['total']/$max)*100)) }}%"></div></div></div>@endforeach
        </div></div>
    </div>

    <div class="ap-card" style="margin-top:18px"><div class="ap-card__head"><h2 class="ap-card__title">حسب العنوان</h2></div><div class="ap-card__body ap-card__body--tight">
        <table class="ap-table"><thead><tr><th>العنوان</th><th>عدد الطلبات</th><th>النسبة</th></tr></thead><tbody>
        @forelse($byLocation as $row)<tr><td>{{ $row->locationName() }}</td><td><strong>{{ number_format($row->total) }}</strong></td><td>{{ $total > 0 ? round(($row->total/$total)*100,1) : 0 }}%</td></tr>@empty<tr><td colspan="3"><div class="ap-empty">لا توجد بيانات.</div></td></tr>@endforelse
        </tbody></table>
    </div></div>
@endsection
