@extends('layouts.admin')

@section('title', 'إحصائيات العقارات')
@section('page_title', 'إحصائيات العقارات')
@section('page_subtitle', 'تحليل التصنيفات والمواقع والأغراض وأداء العقارات')

@section('page_actions')
    <a href="{{ admin_route('admin.stats.export') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'download', 'size' => 16]) تصدير CSV
    </a>
@endsection

@section('content')
    @php
        $categoryTotal = max(1, (int) $byCategory->sum('value'));
        $locationTotal = max(1, (int) $byLocation->sum('value'));
        $purposeTotal = max(1, (int) $byPurpose->sum());
        $statusTotal = max(1, array_sum($statusDistribution['values']));
        $statusColors = ['#0F3D2E', '#C8A45C', '#1d6fa5', '#b98900', '#c0392b', '#55636b'];
    @endphp

    {{-- ------------------------------------------------------------ تابات --}}
    <nav class="ap-tabs">
        <a href="{{ admin_route('admin.stats.index') }}" class="ap-tab">النظرة العامة</a>
        <a href="{{ admin_route('admin.stats.properties') }}" class="ap-tab is-active">إحصائيات العقارات</a>
        <a href="{{ admin_route('admin.stats.users') }}" class="ap-tab">إحصائيات المستخدمين</a>
    </nav>

    {{-- ------------------------------------------------------- مؤشرات KPI --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي العقارات</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_total']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['properties_month']) }} عقار هذا الشهر</div>
            </div>
        </div>
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
                <div class="ap-stat__hint">{{ number_format($overview['properties_rejected']) }} مرفوض</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'tag', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مُباع</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_sold']) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'home', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مُؤجر</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_rented']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">عقارات مميزة</div>
                <div class="ap-stat__value">{{ number_format($overview['properties_featured']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي قيمة العقارات</div>
                <div class="ap-stat__value" style="font-size: 18px;">{{ money_eg($overview['properties_value']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">متوسط السعر</div>
                <div class="ap-stat__value" style="font-size: 18px;">{{ money_eg($overview['properties_avg_price']) }}</div>
                <div class="ap-stat__hint">{{ number_format($overview['properties_views']) }} مشاهدة إجمالاً</div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------- التصنيفات والمواقع --}}
    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div class="ap-card">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'tag', 'size' => 18]) التوزيع حسب التصنيف</h2>
                    <p class="ap-card__subtitle">عدد التصنيفات المتاحة: {{ number_format($categories->count()) }}</p>
                </div>
            </div>
            <div class="ap-card__body">
                @forelse ($byCategory as $row)
                    @php $percent = (int) round(($row['value'] / $categoryTotal) * 100); @endphp
                    <div style="margin-bottom: 12px;">
                        <div class="d-flex justify-content-between" style="font-size: 12.8px; margin-bottom: 4px;">
                            <span>{{ $row['label'] }}</span>
                            <span class="fw-bold">{{ number_format($row['value']) }} ({{ $percent }}%)</span>
                        </div>
                        <div class="ap-progress">
                            <div class="ap-progress__bar" style="width: {{ $percent }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="ap-empty">
                        @include('admin.partials.icon', ['iconName' => 'tag', 'size' => 42])
                        <p>لا توجد بيانات تصنيفات.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="ap-card">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'map', 'size' => 18]) أعلى 10 مواقع</h2>
                    <p class="ap-card__subtitle">عدد المواقع المتاحة: {{ number_format($locations->count()) }}</p>
                </div>
            </div>
            <div class="ap-card__body">
                @forelse ($byLocation as $row)
                    @php $percent = (int) round(($row['value'] / $locationTotal) * 100); @endphp
                    <div style="margin-bottom: 12px;">
                        <div class="d-flex justify-content-between" style="font-size: 12.8px; margin-bottom: 4px;">
                            <span>{{ $row['label'] }}</span>
                            <span class="fw-bold">{{ number_format($row['value']) }} ({{ $percent }}%)</span>
                        </div>
                        <div class="ap-progress">
                            <div class="ap-progress__bar ap-progress__bar--gold" style="width: {{ $percent }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="ap-empty">
                        @include('admin.partials.icon', ['iconName' => 'map', 'size' => 42])
                        <p>لا توجد بيانات مواقع.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------ الغرض والحالة --}}
    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 18]) التوزيع حسب الغرض</h2>
            </div>
            <div class="ap-card__body">
                @forelse ($byPurpose as $purpose => $total)
                    @php
                        $purposeLabel = match ($purpose) {
                            'sale' => 'بيع',
                            'rent' => 'إيجار',
                            default => $purpose !== '' ? (string) $purpose : 'غير محدد',
                        };
                        $percent = (int) round(((int) $total / $purposeTotal) * 100);
                    @endphp
                    <div style="margin-bottom: 12px;">
                        <div class="d-flex justify-content-between" style="font-size: 12.8px; margin-bottom: 4px;">
                            <span>{{ $purposeLabel }}</span>
                            <span class="fw-bold">{{ number_format((int) $total) }} ({{ $percent }}%)</span>
                        </div>
                        <div class="ap-progress">
                            <div class="ap-progress__bar {{ $purpose === 'rent' ? 'ap-progress__bar--gold' : '' }}" style="width: {{ $percent }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="ap-empty">
                        @include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 42])
                        <p>لا توجد بيانات أغراض.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18]) توزيع الحالات</h2>
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
                        @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 42])
                        <p>لا توجد بيانات حالات.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ------------------------------------------- أفضل 15 عقاراً --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'eye', 'size' => 18]) أفضل 15 عقاراً حسب المشاهدات</h2>
            <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">كل العقارات</a>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>العقار</th>
                            <th>النوع</th>
                            <th>السعر</th>
                            <th>المشاهدات</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topProperties as $index => $property)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ admin_route('admin.properties.show', ['property' => $property]) }}" class="fw-bold">
                                        {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 38) }}
                                    </a>
                                    <div class="text-muted" style="font-size: 11px;">{{ $property->reference_code }}</div>
                                </td>
                                <td>
                                    <span class="ap-badge">{{ $property->purpose === 'rent' ? 'إيجار' : 'بيع' }}</span>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ $property->category?->translation('ar')?->name ?? '—' }}
                                    </div>
                                </td>
                                <td class="fw-bold">{{ money_eg($property->price) }}</td>
                                <td>{{ number_format($property->views_count) }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($property->status) }}">
                                        {{ status_label($property->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.properties.stats', ['property' => $property]) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="إحصائيات العقار">
                                            @include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 15]) إحصائيات
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'building', 'size' => 42])
                                        <p>لا توجد عقارات بعد.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
