@extends('layouts.admin')

@section('title', 'لوحة التحكم')
@section('page_title', 'مرحباً ' . auth()->user()->name)
@section('page_subtitle', 'نظرة شاملة على أداء المنصة العقارية — ' . now()->translatedFormat('l j F Y'))

@section('page_actions')
    @can('property.create')
        <a href="{{ admin_route('admin.properties.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) عقار جديد
        </a>
    @endcan
    @can('stats.view')
        <a href="{{ admin_route('admin.stats.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 16]) التقارير
        </a>
    @endcan
@endsection

@section('content')
    @php
        $maxTrend = max(1, max(array_merge($trend['properties'], $trend['leads'], $trend['users'])));
        $statusColors = ['#0F3D2E', '#C8A45C', '#1d6fa5', '#b98900', '#c0392b', '#55636b'];
        $statusTotal = max(1, array_sum($statusDistribution['values']));
    @endphp

    {{-- ------------------------------------------------------ مؤشرات KPI --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي العقارات</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_total']) }}</div>
                <div class="ap-stat__hint">
                    {{ $overview['properties_published'] }} منشور · {{ $overview['properties_pending'] }} بانتظار المراجعة
                    @if ($propertyComparison['change'] != 0)
                        <span class="ap-stat__trend ap-stat__trend--{{ $propertyComparison['change'] > 0 ? 'up' : 'down' }}">
                            ({{ $propertyComparison['change'] > 0 ? '+' : '' }}{{ $propertyComparison['change'] }}% هذا الشهر)
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي قيمة العقارات</div>
                <div class="ap-stat__value" style="font-size: 19px;">{{ money_eg($overview['properties_value']) }}</div>
                <div class="ap-stat__hint">متوسط السعر {{ money_eg($overview['properties_avg_price']) }}</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'eye', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">مشاهدات العقارات</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_views']) }}</div>
                <div class="ap-stat__hint">{{ $overview['properties_featured'] }} عقار مميز</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value">{{ number_format($overview['leads_total']) }}</div>
                <div class="ap-stat__hint">{{ $overview['leads_new'] }} جديد · {{ $overview['leads_month'] }} هذا الشهر</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">منشور</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_published']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">بانتظار المراجعة</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_pending']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'edit', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مسودات</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_draft']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--danger">@include('admin.partials.icon', ['iconName' => 'x', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مرفوض / غير مُسند</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_rejected']) }} / {{ number_format($overview['properties_unassigned']) }}</div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------ المستخدمون والمواعيد --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي المستخدمين</div>
                <div class="ap-stat__value">{{ number_format($overview['users_total']) }}</div>
                <div class="ap-stat__hint">{{ $overview['users_active'] }} نشط</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المسؤولون</div>
                <div class="ap-stat__value">{{ number_format($overview['users_managers']) }}</div>
                <div class="ap-stat__hint">{{ $overview['users_admins'] }} أدمن</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">السماسرة</div>
                <div class="ap-stat__value">{{ number_format($overview['users_agents']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المواعيد</div>
                <div class="ap-stat__value">{{ number_format($overview['appointments_total']) }}</div>
                <div class="ap-stat__hint">{{ $overview['appointments_upcoming'] }} قادمة</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        {{-- ------------------------------------------------------ المخطط --}}
        <div class="ap-card" style="grid-column: span 2;">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 18]) النشاط في آخر 14 يوماً</h2>
                    <p class="ap-card__subtitle">العقارات الجديدة مقابل العملاء المحتملين</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="ap-badge ap-badge--info">عقارات</span>
                    <span class="ap-badge ap-badge--gold">عملاء</span>
                </div>
            </div>
            <div class="ap-card__body">
                <div class="ap-bars">
                    @foreach ($trend['labels'] as $index => $label)
                        <div class="ap-bars__item"
                            title="{{ $label }} — عقارات: {{ $trend['properties'][$index] }} / عملاء: {{ $trend['leads'][$index] }}">
                            <span class="ap-bars__value">{{ $trend['properties'][$index] ?: '' }}</span>
                            <div class="ap-bars__bar" style="height: {{ max(3, (int) round(($trend['properties'][$index] / $maxTrend) * 100)) }}%;"></div>
                            <div class="ap-bars__bar ap-bars__bar--accent" style="height: {{ max(2, (int) round(($trend['leads'][$index] / $maxTrend) * 100)) }}%;"></div>
                            <span class="ap-bars__label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ------------------------------------------------- توزيع الحالات --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 18]) توزيع حالات العقارات</h2>
            </div>
            <div class="ap-card__body">
                @forelse ($statusDistribution['labels'] as $index => $label)
                    @php $value = $statusDistribution['values'][$index]; $percent = round(($value / $statusTotal) * 100); @endphp
                    <div style="margin-bottom: 12px;">
                        <div class="d-flex justify-content-between" style="font-size: 12.8px; margin-bottom: 4px;">
                            <span>{{ $label }}</span>
                            <span class="fw-bold">{{ number_format($value) }} ({{ $percent }}%)</span>
                        </div>
                        <div class="ap-progress">
                            <div class="ap-progress__bar" style="width: {{ $percent }}%; background: {{ $statusColors[$index % count($statusColors)] }};"></div>
                        </div>
                    </div>
                @empty
                    <div class="ap-empty"><p>لا توجد بيانات.</p></div>
                @endforelse
            </div>
        </div>

        {{-- ------------------------------------------------- أفضل السماسرة --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 18]) أفضل السماسرة</h2>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>السمسار</th><th>عدد العقارات</th><th>الحالة</th></tr></thead>
                        <tbody>
                            @forelse ($topAgents as $agent)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $agent->avatar_url }}" alt="" class="ap-avatar ap-avatar--sm">
                                            <span>{{ $agent->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($agent->properties_count) }}</td>
                                    <td><span class="ap-badge ap-badge--{{ $agent->status === 'active' ? 'success' : 'secondary' }}">{{ status_label($agent->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">لا يوجد سماسرة بعد.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ------------------------------------------------- أحدث العقارات --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 18]) أحدث العقارات</h2>
                <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">الكل</a>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>العقار</th><th>المسؤول</th><th>الحالة</th></tr></thead>
                        <tbody>
                            @forelse ($recentProperties as $property)
                                <tr>
                                    <td>
                                        <a href="{{ admin_route('admin.properties.show', $property) }}" class="fw-bold">
                                            {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 30) }}
                                        </a>
                                        <div class="text-muted" style="font-size: 11px;">{{ money_eg($property->price) }}</div>
                                    </td>
                                    <td>{{ $property->manager?->name ?? $property->owner?->name ?? '—' }}</td>
                                    <td><span class="ap-badge ap-badge--{{ status_color($property->status) }}">{{ status_label($property->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">لا توجد عقارات.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ------------------------------------------------- أحدث العملاء --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 18]) أحدث العملاء المحتملين</h2>
                @can('lead.view-all')
                    <a href="{{ admin_route('admin.leads.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">الكل</a>
                @endcan
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>العميل</th><th>العقار</th><th>الحالة</th></tr></thead>
                        <tbody>
                            @forelse ($recentLeads as $lead)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $lead->name }}</span>
                                        <div class="text-muted" dir="ltr" style="font-size: 11px;">{{ $lead->phone ?? $lead->email }}</div>
                                    </td>
                                    <td>{{ \Illuminate\Support\Str::limit($lead->property?->displayName('ar') ?? '—', 24) }}</td>
                                    <td><span class="ap-badge ap-badge--{{ status_color($lead->status) }}">{{ status_label($lead->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">لا يوجد عملاء محتملون.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
