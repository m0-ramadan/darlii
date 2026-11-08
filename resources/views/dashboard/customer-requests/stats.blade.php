@extends('layouts.public-shell')

@section('title', 'إحصائيات طلبات العملاء')

@push('head')
    <link rel="stylesheet" href="{{ asset('themes/darlii/css/dashboard-requests.css') }}?v=1.0.0">
@endpush

@section('content')
    <div class="darli-dash-wrapper cr-page">
        <div class="darli-dash-hero"><div class="container">
            <div class="darli-dash-breadcrumb"><a href="{{ route('dashboard.index') }}">لوحة التحكم</a><span class="sep">›</span><a href="{{ route('dashboard.customer-requests.index') }}">طلبات العملاء</a><span class="sep">›</span><span>الإحصائيات</span></div>
            <div class="cr-head"><div><h1 class="cr-title">إحصائيات طلبات العملاء</h1><p class="cr-sub">توزيع الطلبات حسب الحالة والموقع والشهر.</p></div><a href="{{ route('dashboard.customer-requests.create') }}" class="cr-btn cr-btn--gold">+ إضافة طلب</a></div>
        </div></div>

        <div class="container">
            <div class="cr-shell">
                @include('dashboard.customer-requests._sidebar')
                <div class="cr-main">
                    <div class="cr-card cr-filter">
                        <form method="get">
                            <div class="cr-field"><label>من</label><input type="date" name="from" value="{{ $from }}" class="cr-input"></div>
                            <div class="cr-field"><label>إلى</label><input type="date" name="to" value="{{ $to }}" class="cr-input"></div>
                            <button class="cr-btn cr-btn--sm">تحديث</button>
                            <a href="{{ route('dashboard.customer-requests.stats') }}" class="cr-btn cr-btn--ghost cr-btn--sm">إعادة تعيين</a>
                        </form>
                    </div>

                    <div class="cr-stats">
                        <div class="cr-stat"><div class="cr-stat__label">إجمالي الطلبات</div><div class="cr-stat__value">{{ number_format($total) }}</div></div>
                        <div class="cr-stat"><div class="cr-stat__label">جديد</div><div class="cr-stat__value">{{ number_format((int)($byStatus['new'] ?? 0)) }}</div></div>
                        <div class="cr-stat"><div class="cr-stat__label">قيد التنفيذ</div><div class="cr-stat__value">{{ number_format((int)($byStatus['in_progress'] ?? 0)) }}</div></div>
                        <div class="cr-stat"><div class="cr-stat__label">تم</div><div class="cr-stat__value">{{ number_format((int)($byStatus['completed'] ?? 0)) }}</div></div>
                    </div>

                    <div class="cr-grid2">
                        <div class="cr-card" style="padding:16px">
                            <h2 class="cr-title" style="font-size:17px;margin-bottom:12px">حسب الحالة</h2>
                            @foreach($statuses as $key=>$label)
                                <div class="cr-side-stat" style="padding:8px 0"><span>{{ $label }}</span><strong>{{ number_format((int)($byStatus[$key] ?? 0)) }}</strong></div>
                            @endforeach
                        </div>
                        <div class="cr-card" style="padding:16px">
                            <h2 class="cr-title" style="font-size:17px;margin-bottom:12px">الأشهر</h2>
                            @php $max = max(1, collect($monthly)->max('total')); @endphp
                            @foreach($monthly as $row)
                                <div style="margin-bottom:9px">
                                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px"><span>{{ $row['label'] }}</span><strong>{{ $row['total'] }}</strong></div>
                                    <div style="background:#f1f5f9;border-radius:6px;height:7px"><div class="cr-bar" style="width:{{ max(2, round(($row['total']/$max)*100)) }}%"></div></div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="cr-card" style="margin-top:16px">
                        <div style="padding:14px 16px"><h2 class="cr-title" style="font-size:17px">حسب العنوان</h2></div>
                        <div class="cr-table-wrap"><table class="cr-table">
                            <thead><tr><th>العنوان</th><th>عدد الطلبات</th><th>النسبة</th></tr></thead>
                            <tbody>
                            @forelse($byLocation as $row)
                                <tr><td>{{ $row->locationName() }}</td><td><strong>{{ number_format($row->total) }}</strong></td><td>{{ $total > 0 ? round(($row->total / $total) * 100, 1) : 0 }}%</td></tr>
                            @empty
                                <tr><td colspan="3"><div class="cr-empty">لا توجد بيانات.</div></td></tr>
                            @endforelse
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
