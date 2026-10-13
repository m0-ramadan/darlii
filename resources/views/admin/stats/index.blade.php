@extends('layouts.admin')

@section('title', 'الإحصائيات والتقارير')
@section('page_title', 'الإحصائيات والتقارير')
@section('page_subtitle', 'نظرة تحليلية على العقارات والعملاء المحتملين وفريق العمل')

@section('page_actions')
    <a href="{{ admin_route('admin.stats.export') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'download', 'size' => 16]) تصدير CSV
    </a>
@endsection

@section('content')
    @php
        $growthMax = max(1, max(array_merge($growth['properties'], $growth['leads'])));
        $trendMax = max(1, max(array_merge($trend['properties'], $trend['leads'], $trend['users'])));
        $statusTotal = max(1, array_sum($statusDistribution['values']));
        $statusColors = ['#0F3D2E', '#C8A45C', '#1d6fa5', '#b98900', '#c0392b', '#55636b'];
    @endphp

    {{-- ------------------------------------------------------------ تابات --}}
    <nav class="ap-tabs">
        <a href="{{ admin_route('admin.stats.index') }}" class="ap-tab is-active">النظرة العامة</a>
        <a href="{{ admin_route('admin.stats.properties') }}" class="ap-tab">إحصائيات العقارات</a>
        <a href="{{ admin_route('admin.stats.users') }}" class="ap-tab">إحصائيات المستخدمين</a>
    </nav>

    {{-- ------------------------------------------------------- مؤشرات KPI --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي العقارات</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_total']) }}</div>
                <div class="ap-stat__hint">
                    {{ number_format($overview['properties_published']) }} منشور ·
                    {{ number_format($overview['properties_pending']) }} بانتظار المراجعة
                    @if (($propertyComparison['change'] ?? 0) != 0)
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
                <div class="ap-stat__hint">متوسط سعر العقار المنشور {{ money_eg($overview['properties_avg_price']) }}</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'eye', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">مشاهدات العقارات</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_views']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['properties_featured']) }} عقار مميز</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value">{{ number_format($overview['leads_total']) }}</div>
                <div class="ap-stat__hint">
                    {{ number_format($overview['leads_new']) }} جديد ·
                    {{ number_format($overview['leads_month']) }} هذا الشهر
                    @if (($leadComparison['change'] ?? 0) != 0)
                        <span class="ap-stat__trend ap-stat__trend--{{ $leadComparison['change'] > 0 ? 'up' : 'down' }}">
                            ({{ $leadComparison['change'] > 0 ? '+' : '' }}{{ $leadComparison['change'] }}%)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">منشور</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_published']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['properties_month']) }} عقار هذا الشهر</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">بانتظار المراجعة</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_pending']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['properties_draft']) }} مسودة</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'tag', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مُباع</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_sold']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'home', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مُؤجر</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_rented']) }}</div>
            </div>
        </div>
    </div>

    {{-- -------------------------------------------------------------- الفريق --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي المستخدمين</div>
                <div class="ap-stat__value">{{ number_format($overview['users_total']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['users_active']) }} نشط</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المسؤولون</div>
                <div class="ap-stat__value">{{ number_format($overview['users_admins']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['users_managers']) }} مدير</div>
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
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مستخدمون جدد هذا الشهر</div>
                <div class="ap-stat__value">{{ number_format($userComparison['this_month']) }}</div>
                <div class="ap-stat__hint">
                    الشهر الماضي {{ number_format($userComparison['last_month']) }}
                    @if (($userComparison['change'] ?? 0) != 0)
                        <span class="ap-stat__trend ap-stat__trend--{{ $userComparison['change'] > 0 ? 'up' : 'down' }}">
                            ({{ $userComparison['change'] > 0 ? '+' : '' }}{{ $userComparison['change'] }}%)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------ نمو 12 شهراً --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <div>
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 18]) النمو خلال 12 شهراً</h2>
                <p class="ap-card__subtitle">العقارات الجديدة مقابل العملاء المحتملين</p>
            </div>
            <div class="d-flex gap-2">
                <span class="ap-badge ap-badge--info">عقارات</span>
                <span class="ap-badge ap-badge--gold">عملاء</span>
            </div>
        </div>
        <div class="ap-card__body">
            <div class="ap-bars">
                @foreach ($growth['labels'] as $index => $label)
                    <div class="ap-bars__item"
                        title="{{ $label }} — عقارات: {{ $growth['properties'][$index] }} / عملاء: {{ $growth['leads'][$index] }}">
                        <span class="ap-bars__value">{{ $growth['properties'][$index] ?: '' }}</span>
                        <div class="ap-bars__bar" style="height: {{ max(3, (int) round(($growth['properties'][$index] / $growthMax) * 100)) }}%;"></div>
                        <div class="ap-bars__bar ap-bars__bar--accent" style="height: {{ max(2, (int) round(($growth['leads'][$index] / $growthMax) * 100)) }}%;"></div>
                        <span class="ap-bars__label">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ----------------------------------------------------- آخر 30 يوماً --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <div>
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18]) النشاط اليومي في آخر 30 يوماً</h2>
                <p class="ap-card__subtitle">العقارات والعملاء المحتملون والمستخدمون الجدد</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <span class="ap-badge ap-badge--info">عقارات</span>
                <span class="ap-badge ap-badge--gold">عملاء</span>
                <span class="ap-badge ap-badge--secondary">مستخدمون</span>
            </div>
        </div>
        <div class="ap-card__body">
            <div class="ap-bars">
                @foreach ($trend['labels'] as $index => $label)
                    <div class="ap-bars__item"
                        title="{{ $label }} — عقارات: {{ $trend['properties'][$index] }} / عملاء: {{ $trend['leads'][$index] }} / مستخدمون: {{ $trend['users'][$index] }}">
                        <span class="ap-bars__value">{{ $trend['properties'][$index] ?: '' }}</span>
                        <div class="ap-bars__bar" style="height: {{ max(3, (int) round(($trend['properties'][$index] / $trendMax) * 100)) }}%;"></div>
                        <div class="ap-bars__bar ap-bars__bar--accent" style="height: {{ max(2, (int) round(($trend['leads'][$index] / $trendMax) * 100)) }}%;"></div>
                        <span class="ap-bars__label">{{ $index % 5 === 0 ? $label : '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ----------------------------- توزيع الحالات + أفضل العقارات --}}
    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 18]) توزيع حالات العقارات</h2>
            </div>
            <div class="ap-card__body">
                @forelse ($statusDistribution['labels'] as $index => $label)
                    @php
                        $value = $statusDistribution['values'][$index];
                        $percent = (int) round(($value / $statusTotal) * 100);
                    @endphp
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
                    <div class="ap-empty">
                        @include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 42])
                        <p>لا توجد بيانات.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 18]) أفضل العقارات</h2>
                <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">الكل</a>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead>
                            <tr>
                                <th>العقار</th>
                                <th>السعر</th>
                                <th>المشاهدات</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProperties as $property)
                                <tr>
                                    <td>
                                        {{-- نمرّر البارامترات كمصفوفة لأن admin_route() تقبل array $parameters --}}
                                        <a href="{{ admin_route('admin.properties.show', ['property' => $property]) }}" class="fw-bold">
                                            {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 32) }}
                                        </a>
                                        <div class="text-muted" style="font-size: 11px;">{{ $property->reference_code }}</div>
                                    </td>
                                    <td class="fw-bold">{{ money_eg($property->price) }}</td>
                                    <td>{{ number_format($property->views_count) }}</td>
                                    <td>
                                        <span class="ap-badge ap-badge--{{ status_color($property->status) }}">
                                            {{ status_label($property->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">لا توجد عقارات بعد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------- أفضل السماسرة --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) أفضل السماسرة</h2>
            <a href="{{ admin_route('admin.stats.users') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إحصائيات المستخدمين</a>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>السمسار</th>
                            <th>الدور</th>
                            <th>عدد العقارات</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topAgents as $agent)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $agent->avatar_url }}" alt="{{ $agent->name }}" class="ap-avatar ap-avatar--sm">
                                        <span>{{ $agent->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $agent->roleLabels() }}</td>
                                <td class="fw-bold">{{ number_format($agent->properties_count) }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($agent->status) }}">
                                        {{ status_label($agent->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'users', 'size' => 42])
                                        <p>لا يوجد سماسرة بعد.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------ إحصائيات صفحات الموقع --}}
    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div class="ap-card">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'eye', 'size' => 18]) مشاهدات صفحات الموقع</h2>
                    <p class="ap-card__subtitle">آخر 30 يوماً — إجمالي {{ number_format($pageViewsTotal ?? 0) }} مشاهدة</p>
                </div>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead>
                            <tr><th>الصفحة</th><th>المشاهدات (30 يوم)</th><th>اليوم</th><th>النسبة</th></tr>
                        </thead>
                        <tbody>
                            @forelse (($pageViews ?? collect()) as $row)
                                <tr>
                                    <td class="fw-bold">{{ $row['label'] }}</td>
                                    <td>{{ number_format($row['views']) }}</td>
                                    <td>{{ number_format($row['today']) }}</td>
                                    <td style="min-width: 120px;">
                                        <div class="ap-progress">
                                            <div class="ap-progress__bar" style="width: {{ min(100, $row['share']) }}%;"></div>
                                        </div>
                                        <span class="ap-help">{{ $row['share'] }}%</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="ap-empty">
                                            @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 42])
                                            <p>لم تُسجَّل مشاهدات بعد. تُحسب زيارات صفحات الموقع العامة تلقائياً.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18]) الزيارات اليومية (14 يوماً)</h2>
            </div>
            <div class="ap-card__body">
                @php $pvMax = max(1, max($pageViewsSeries['series'] ?? [0])); @endphp
                <div class="ap-bars">
                    @foreach (($pageViewsSeries['labels'] ?? []) as $i => $label)
                        <div class="ap-bars__item" title="{{ $label }}: {{ $pageViewsSeries['series'][$i] }} مشاهدة">
                            <span class="ap-bars__value">{{ $pageViewsSeries['series'][$i] ?: '' }}</span>
                            <div class="ap-bars__bar" style="height: {{ max(3, (int) round(($pageViewsSeries['series'][$i] / $pvMax) * 100)) }}%;"></div>
                            <span class="ap-bars__label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
                @if (($pageViewsTotal ?? 0) === 0)
                    <div class="ap-alert ap-alert--info" style="margin: 12px 0 0;">
                        <div>تصفّح الموقع العام ثم أعد تحميل الصفحة لرؤية الزيارات المسجّلة.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
