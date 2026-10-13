@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'كود العقار ' . $property->reference_code . ' — نظرة تفصيلية على الأداء')

@section('page_actions')
    <a href="{{ admin_route('admin.properties.show', $property) }}" class="ap-btn ap-btn--ghost ap-btn--sm">صفحة العقار</a>
    <a href="{{ admin_route('admin.properties.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a>
@endsection

@section('content')
    @php
        $maxTrend = max(1, max(array_merge($trend['views'], $trend['leads'])));
    @endphp

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'eye', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي المشاهدات</div>
                <div class="ap-stat__value">{{ number_format($stats['views']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value">{{ number_format($stats['leads']) }}</div>
                <div class="ap-stat__hint">{{ $stats['leads_new'] }} لم يتم التواصل بعد</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المفضلة</div>
                <div class="ap-stat__value">{{ number_format($stats['favorites']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المواعيد</div>
                <div class="ap-stat__value">{{ number_format($stats['appointments']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'money', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">السعر</div>
                <div class="ap-stat__value" style="font-size: 18px;">{{ money_eg($stats['price']) }}</div>
                <div class="ap-stat__hint">{{ money_eg($stats['price_per_meter']) }} / متر</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">التقييمات</div>
                <div class="ap-stat__value">{{ number_format($stats['reviews']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">تغييرات الحالة</div>
                <div class="ap-stat__value">{{ number_format($stats['history']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">عمر العقار</div>
                <div class="ap-stat__value" style="font-size: 20px;">{{ $stats['age_days'] }} يوم</div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <div>
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 18]) آخر 30 يوماً</h2>
                <p class="ap-card__subtitle">المشاهدات اليومية مقارنة بالعملاء المحتملين</p>
            </div>
            <div class="d-flex gap-2">
                <span class="ap-badge ap-badge--info">مشاهدات</span>
                <span class="ap-badge ap-badge--gold">عملاء</span>
            </div>
        </div>
        <div class="ap-card__body">
            <div class="ap-bars">
                @foreach ($trend['labels'] as $index => $label)
                    <div class="ap-bars__item" title="{{ $label }} — مشاهدات: {{ $trend['views'][$index] }} / عملاء: {{ $trend['leads'][$index] }}">
                        <span class="ap-bars__value">{{ $trend['views'][$index] ?: '' }}</span>
                        <div class="ap-bars__bar" style="height: {{ max(3, (int) round(($trend['views'][$index] / $maxTrend) * 100)) }}%;"></div>
                        <div class="ap-bars__bar ap-bars__bar--accent" style="height: {{ max(2, (int) round(($trend['leads'][$index] / $maxTrend) * 100)) }}%;"></div>
                        <span class="ap-bars__label">{{ $index % 5 === 0 ? $label : '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 18]) أحدث العملاء المحتملين لهذا العقار</h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr><th>الاسم</th><th>الهاتف</th><th>البريد</th><th>الحالة</th><th>التاريخ</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>{{ $lead->name }}</td>
                                <td dir="ltr">{{ $lead->phone ?? '—' }}</td>
                                <td dir="ltr">{{ $lead->email ?? '—' }}</td>
                                <td><span class="ap-badge ap-badge--{{ status_color($lead->status) }}">{{ status_label($lead->status) }}</span></td>
                                <td>{{ $lead->created_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">لا يوجد عملاء محتملون لهذا العقار بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
