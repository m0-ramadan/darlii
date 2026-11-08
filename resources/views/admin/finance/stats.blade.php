@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')

    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field"><label class="ap-label">من تاريخ</label><input type="date" name="from" value="{{ $from }}" class="ap-input ap-input--sm"></div>
                <div class="ap-field"><label class="ap-label">إلى تاريخ</label><input type="date" name="to" value="{{ $to }}" class="ap-input ap-input--sm"></div>
                <button class="ap-btn ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'search', 'size' => 16]) تحديث</button>
                <a href="{{ admin_route('admin.finance.stats.export', ['from' => $from, 'to' => $to]) }}" class="ap-btn ap-btn--ghost ap-btn--sm">@include('admin.partials.icon', ['iconName' => 'download', 'size' => 16]) تصدير CSV</a>
            </form>
        </div>
    </div>

    <div class="ap-grid ap-grid--4" style="margin-bottom:18px;">
        <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 20])</div><div><div class="ap-stat__label">ودائع معتمدة</div><div class="ap-stat__value">{{ number_format($summary['total_deposited'], 2) }} <small>ج.م</small></div></div></div>
        <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--danger">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 20])</div><div><div class="ap-stat__label">إجمالي المصروفات</div><div class="ap-stat__value">{{ number_format($summary['expenses'], 2) }} <small>ج.م</small></div></div></div>
        <div class="ap-stat"><div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 20])</div><div><div class="ap-stat__label">الصافي النقدي</div><div class="ap-stat__value" style="color:{{ $summary['net_cash'] >= 0 ? '#16a34a' : '#dc2626' }}">{{ number_format($summary['net_cash'], 2) }} <small>ج.م</small></div></div></div>
        <div class="ap-stat"><div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'alert', 'size' => 20])</div><div><div class="ap-stat__label">ودائع معلقة ({{ number_format($summary['pending_deposits']) }})</div><div class="ap-stat__value">{{ number_format($summary['pending_amount'], 2) }} <small>ج.م</small></div></div></div>
        @if($pointsEnabled)
            <div class="ap-stat"><div><div class="ap-stat__label">نقاط ممنوحة</div><div class="ap-stat__value">{{ number_format($summary['points_issued']) }}</div></div></div>
            <div class="ap-stat"><div><div class="ap-stat__label">نقاط مستهلكة</div><div class="ap-stat__value">{{ number_format($summary['points_spent']) }}</div></div></div>
            <div class="ap-stat"><div><div class="ap-stat__label">أرصدة النقاط</div><div class="ap-stat__value">{{ number_format($summary['points_balance']) }}</div></div></div>
        @endif
    </div>

    @php
        $maxValue = 1;
        foreach ($monthly as $row) { $maxValue = max($maxValue, (float)$row['deposits'], (float)$row['expenses'], $pointsEnabled ? $row['earned'] : 0); }
    @endphp
    <div class="ap-card" style="margin-bottom:18px;">
        <div class="ap-card__head"><h2 class="ap-card__title">الحركة الشهرية</h2></div>
        <div class="ap-card__body">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                    <tr>
                        <th>الشهر</th>
                        <th>ودائع معتمدة</th>
                        <th>مصروفات</th>
                        <th>الصافي</th>
                        @if($pointsEnabled)<th>نقاط مكتسبة</th><th>نقاط مستهلكة</th>@endif
                        <th style="width:160px;">المقارنة</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($monthly as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>{{ number_format((float)$row['deposits'], 2) }}</td>
                            <td style="color:#dc2626">{{ number_format((float)$row['expenses'], 2) }}</td>
                            <td style="color:{{ $row['net'] >= 0 ? '#16a34a' : '#dc2626' }};font-weight:700">{{ number_format((float)$row['net'], 2) }}</td>
                            @if($pointsEnabled)<td>{{ number_format($row['earned']) }}</td><td>{{ number_format($row['spent']) }}</td>@endif
                            <td>
                                <div style="display:flex;gap:4px;align-items:center;">
                                    <div title="ودائع" style="height:8px;background:#16a34a;border-radius:4px;width:{{ max(2, round(((float)$row['deposits']/$maxValue)*60)) }}px"></div>
                                    <div title="مصروفات" style="height:8px;background:#dc2626;border-radius:4px;width:{{ max(2, round(((float)$row['expenses']/$maxValue)*60)) }}px"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2">
        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">المصروفات حسب التصنيف</h2></div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap"><table class="ap-table">
                    <thead><tr><th>التصنيف</th><th>العدد</th><th>الإجمالي</th><th>النسبة</th></tr></thead>
                    <tbody>
                    @forelse($expensesByCategory as $row)
                        <tr><td>{{ \App\Models\Finance\FinanceExpense::CATEGORIES[$row->category] ?? $row->category }}</td><td>{{ number_format($row->count) }}</td><td>{{ number_format((float)$row->total, 2) }} ج.م</td><td>{{ $summary['expenses'] > 0 ? round(((float)$row->total / $summary['expenses']) * 100, 1) : 0 }}%</td></tr>
                    @empty
                        <tr><td colspan="4"><div class="ap-empty">لا توجد مصروفات.</div></td></tr>
                    @endforelse
                    </tbody>
                </table></div>
            </div>
        </div>

        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">الودائع حسب الحالة</h2></div>
            <div class="ap-card__body">
                @foreach(['pending'=>'قيد المراجعة','approved'=>'معتمدة','rejected'=>'مرفوضة'] as $key=>$label)
                    @php $row = $depositStatuses->get($key); @endphp
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #eef2f6;">
                        <span>{{ $label }} ({{ number_format((int)($row->count ?? 0)) }})</span>
                        <strong>{{ number_format((float)($row->total ?? 0), 2) }} ج.م</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="margin-top:18px;">
        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">أعلى المودعين</h2></div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap"><table class="ap-table"><thead><tr><th>المودع</th><th>إجمالي الودائع</th></tr></thead><tbody>
                    @forelse($topDepositors as $row)
                        <tr><td>{{ $row->manual_name ?: ($row->user?->name ?? 'مودع غير مسجل') }}</td><td>{{ number_format((float)$row->total, 2) }} ج.م</td></tr>
                    @empty
                        <tr><td colspan="2"><div class="ap-empty">لا توجد ودائع معتمدة.</div></td></tr>
                    @endforelse
                </tbody></table></div>
            </div>
        </div>

        @if($pointsEnabled)
            <div class="ap-card">
                <div class="ap-card__head"><h2 class="ap-card__title">توزيع النقاط حسب النوع</h2></div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap"><table class="ap-table"><thead><tr><th>النوع</th><th>مكتسبة</th><th>مستهلكة</th><th>الحركات</th></tr></thead><tbody>
                        @foreach($types as $type)
                            <tr><td>{{ $type->name }}</td><td>{{ number_format((int)$type->earned_points) }}</td><td>{{ number_format(abs((int)$type->spent_points)) }}</td><td>{{ number_format($type->transactions_count) }}</td></tr>
                        @endforeach
                    </tbody></table></div>
                </div>
            </div>
        @endif
    </div>
@endsection
