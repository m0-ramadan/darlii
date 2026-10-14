@extends('layouts.admin')

@section('title', 'تفاصيل الزائر #' . substr($visitor->visitor_id, 0, 8))

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.visitors.index') }}" class="btn btn-sm btn-outline-secondary">
                    @include('admin.partials.icon', ['iconName' => 'arrow-right', 'size' => 14])
                    <span>الرجوع للقائمة</span>
                </a>
                <h1 class="h4 fw-bold m-0">تفاصيل الزائر: #{{ substr($visitor->visitor_id, 0, 12) }}</h1>
            </div>
            <p class="text-muted m-0 small">الموقع: {{ $visitor->fullLocation() }} • الـ IP: {{ $visitor->ip_address }}</p>
        </div>
        <div>
            <form method="POST" action="{{ route('admin.visitors.destroy', $visitor) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                    @include('admin.partials.icon', ['iconName' => 'trash-2', 'size' => 14])
                    <span>حذف السجل</span>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left: Location & Device Cards -->
        <div class="col-12 col-lg-5">
            <!-- Geographical Location Card -->
            <div class="ap-card border-0 shadow-sm bg-white mb-4">
                <div class="ap-card__header border-bottom p-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        @include('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 18])
                        <span>الموقع الجغرافي والمركز</span>
                    </h5>
                    @if($visitor->location_source === 'gps')
                        <span class="badge bg-success bg-opacity-10 text-success">إحداثيات GPS مباشرة</span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary">تحديد تقريبي عبر IP</span>
                    @endif
                </div>
                <div class="ap-card__body p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">المحافظة</span>
                            <strong class="text-dark fs-6">{{ $visitor->governorate ?: 'غير محددة' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">المركز / المدينة</span>
                            <strong class="text-primary fs-6">{{ $visitor->city_or_markaz ?: 'غير محدد' }}</strong>
                        </div>
                        @if($visitor->district)
                            <div class="col-12">
                                <span class="text-muted small d-block mb-1">الحي / المنطقة</span>
                                <strong class="text-dark">{{ $visitor->district }}</strong>
                            </div>
                        @endif
                        @if($visitor->formatted_address)
                            <div class="col-12">
                                <span class="text-muted small d-block mb-1">العنوان المنسق</span>
                                <div class="p-2 bg-light rounded small">{{ $visitor->formatted_address }}</div>
                            </div>
                        @endif
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">خط العرض (Latitude)</span>
                            <code>{{ $visitor->latitude ?: '—' }}</code>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">خط الطول (Longitude)</span>
                            <code>{{ $visitor->longitude ?: '—' }}</code>
                        </div>

                        @if($visitor->googleMapsUrl())
                            <div class="col-12 mt-3">
                                <a href="{{ $visitor->googleMapsUrl() }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2">
                                    @include('admin.partials.icon', ['iconName' => 'external-link', 'size' => 15])
                                    <span>فتح الموقع مباشرة على خرائط Google</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Device & Browser Card -->
            <div class="ap-card border-0 shadow-sm bg-white mb-4">
                <div class="ap-card__header border-bottom p-3">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        @include('admin.partials.icon', ['iconName' => 'smartphone', 'size' => 18])
                        <span>بيانات الجهاز والمتصفح</span>
                    </h5>
                </div>
                <div class="ap-card__body p-3">
                    <div class="row g-3">
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">نوع الجهاز</span>
                            <strong class="text-dark text-capitalize">{{ $visitor->device_type }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">الماركة والموديل</span>
                            <strong class="text-dark">{{ trim(($visitor->device_brand ?? '') . ' ' . ($visitor->device_model ?? '')) ?: '—' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">نظام التشغيل</span>
                            <strong class="text-dark">{{ $visitor->os ?: '—' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">المتصفح</span>
                            <strong class="text-dark">{{ $visitor->browser ?: '—' }}</strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">أبعاد الشاشة</span>
                            <code>{{ $visitor->screen_resolution ?: '—' }}</code>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block mb-1">لغة المتصفح</span>
                            <code>{{ $visitor->browser_language ?: '—' }}</code>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block mb-1">نص الـ User Agent</span>
                            <div class="p-2 bg-light rounded text-muted" style="font-size: 11px; word-break: break-all;">
                                {{ $visitor->user_agent }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Session details and Visited Pages History -->
        <div class="col-12 col-lg-7">
            <!-- Visit Overview Card -->
            <div class="ap-card border-0 shadow-sm bg-white mb-4">
                <div class="ap-card__header border-bottom p-3">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18])
                        <span>معلومات الجلسة والتصفح</span>
                    </h5>
                </div>
                <div class="ap-card__body p-3">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">عنوان الـ IP</span>
                            <code class="fs-6">{{ $visitor->ip_address }}</code>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">المستخدم المسجل</span>
                            @if($visitor->user)
                                <a href="{{ route('admin.users.edit', $visitor->user_id) }}" class="fw-bold text-primary">
                                    {{ $visitor->user->name }} ({{ $visitor->user->email }})
                                </a>
                            @else
                                <span class="text-muted">زائر غير مسجل (Guest)</span>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">أول زيارة</span>
                            <strong>{{ $visitor->first_visited_at ? $visitor->first_visited_at->format('Y-m-d H:i') : '—' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">آخر نشاط</span>
                            <strong>{{ $visitor->last_active_at ? $visitor->last_active_at->diffForHumans() : '—' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">إجمالي الزيارات</span>
                            <span class="badge bg-primary px-3 py-2 fs-6">{{ $visitor->visits_count }}</span>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">إجمالي الصفحات المستعرضة</span>
                            <span class="badge bg-info px-3 py-2 fs-6">{{ $visitor->page_views_count }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block mb-1">صفحة الدخول الأولى (Landing Page)</span>
                            <a href="{{ $visitor->landing_page }}" target="_blank" class="small text-truncate d-block">
                                {{ $visitor->landing_page ?: '—' }}
                            </a>
                        </div>
                        @if($visitor->referrer)
                            <div class="col-12">
                                <span class="text-muted small d-block mb-1">مصدر الإحالة (Referrer)</span>
                                <span class="small text-muted">{{ $visitor->referrer }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Page Hits Timeline -->
            <div class="ap-card border-0 shadow-sm bg-white">
                <div class="ap-card__header border-bottom p-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold m-0 d-flex align-items-center gap-2" style="font-size: 15px;">
                        @include('admin.partials.icon', ['iconName' => 'clock', 'size' => 18])
                        <span>سجل الصفحات التي زارها (آخر 50 صفحة)</span>
                    </h5>
                    <span class="badge bg-light text-muted border">{{ $visitor->hits->count() }} سجل</span>
                </div>
                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0 align-middle">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2">رابط الصفحة</th>
                                <th class="py-2" style="width: 140px;">الوقت</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visitor->hits as $hit)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ $hit->url }}" target="_blank" class="text-dark text-decoration-none small d-block text-truncate" style="max-width: 450px;" title="{{ $hit->url }}">
                                            {{ $hit->url }}
                                        </a>
                                        @if($hit->page_title)
                                            <div class="text-muted" style="font-size: 11px;">{{ $hit->page_title }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted">{{ $hit->created_at ? $hit->created_at->format('H:i:s - Y/m/d') : '—' }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted small">
                                        لا توجد صفحات إضافية مسجلة بعد.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

