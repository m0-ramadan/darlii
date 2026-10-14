@extends('layouts.admin')

@section('title', 'سجل الزوار والمواقع الجغرافية')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                @include('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 28])
                <span>سجل الزوار والمواقع الجغرافية</span>
            </h1>
            <p class="text-muted m-0">تتبع تفصيلي لجميع زوار المنصة، المحافظات، المراكز، الأجهزة والمتصفحات لحظة بلحظة</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.visitors.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                @include('admin.partials.icon', ['iconName' => 'rotate-cw', 'size' => 16])
                <span>تحديث</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">إجمالي الزوار المسجلين</div>
                        <div class="fs-3 fw-bold text-dark">{{ number_format($totalVisitors) }}</div>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        @include('admin.partials.icon', ['iconName' => 'users', 'size' => 24])
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">زوار اليوم</div>
                        <div class="fs-3 fw-bold text-success">{{ number_format($todayVisitors) }}</div>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 24])
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">أعلى المحافظات زيارة</div>
                        <div class="fs-5 fw-bold text-dark text-truncate" title="{{ $topGovernorate }}">{{ $topGovernorate }}</div>
                        <div class="small text-muted text-truncate" title="{{ $topMarkaz }}">المركز الأكثر: {{ $topMarkaz }}</div>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        @include('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 24])
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ap-card border-0 shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">مستخدمو الموبايل</div>
                        <div class="fs-3 fw-bold text-info">{{ $mobilePercentage }}%</div>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        @include('admin.partials.icon', ['iconName' => 'smartphone', 'size' => 24])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="ap-card border-0 shadow-sm bg-white mb-4">
        <div class="ap-card__body p-3">
            <form method="GET" action="{{ route('admin.visitors.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            @include('admin.partials.icon', ['iconName' => 'search', 'size' => 16])
                        </span>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control border-start-0" placeholder="بحث بالـ IP أو المعرّف أو المستخدم...">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <select name="governorate" class="form-select">
                        <option value="">جميع المحافظات</option>
                        @foreach($availableGovernorates as $gov)
                            <option value="{{ $gov }}" {{ ($filters['governorate'] ?? '') === $gov ? 'selected' : '' }}>{{ $gov }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <input type="text" name="markaz" value="{{ $filters['markaz'] ?? '' }}" class="form-control" placeholder="فلترة بالمركز (مثال: ناصر)...">
                </div>

                <div class="col-12 col-sm-6 col-md-2">
                    <select name="device_type" class="form-select">
                        <option value="">كل الأجهزة</option>
                        <option value="mobile" {{ ($filters['device_type'] ?? '') === 'mobile' ? 'selected' : '' }}>هواتف (Mobile)</option>
                        <option value="desktop" {{ ($filters['device_type'] ?? '') === 'desktop' ? 'selected' : '' }}>كمبيوتر (Desktop)</option>
                        <option value="tablet" {{ ($filters['device_type'] ?? '') === 'tablet' ? 'selected' : '' }}>أجهزة لوحية (Tablet)</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">تطبيق</button>
                    @if(!empty(array_filter($filters)))
                        <a href="{{ route('admin.visitors.index') }}" class="btn btn-outline-secondary" title="إلغاء الفلاتر">
                            @include('admin.partials.icon', ['iconName' => 'x', 'size' => 16])
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Visitors Table -->
    <div class="ap-card border-0 shadow-sm bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3" style="width: 220px;">الزائر / المعرّف</th>
                        <th class="py-3" style="width: 240px;">المحافظة والمركز</th>
                        <th class="py-3" style="width: 200px;">الجهاز والمتصفح</th>
                        <th class="py-3">الصفحة والتصفح</th>
                        <th class="py-3" style="width: 140px;">عنوان الـ IP</th>
                        <th class="py-3" style="width: 160px;">آخر نشاط</th>
                        <th class="pe-3 text-end py-3" style="width: 100px;">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visitors as $visitor)
                        <tr>
                            <!-- Visitor ID & User -->
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted fw-bold" style="width: 38px; height: 38px; font-size: 13px;">
                                        {{ substr($visitor->visitor_id, 0, 2) }}
                                    </div>
                                    <div class="overflow-hidden">
                                        @if($visitor->user)
                                            <a href="{{ route('admin.users.edit', $visitor->user_id) }}" class="fw-bold text-dark d-block text-truncate text-decoration-none">
                                                {{ $visitor->user->name }}
                                            </a>
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 11px;">عضو مسجل</span>
                                        @else
                                            <span class="fw-semibold text-dark d-block text-truncate" title="{{ $visitor->visitor_id }}">
                                                زائر #{{ substr($visitor->visitor_id, 0, 8) }}
                                            </span>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size: 11px;">زائر الموقع</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Governorate & Markaz -->
                            <td>
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1" style="font-size: 13px;">
                                        @include('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 12])
                                        {{ $visitor->governorate ?: 'مصر' }}
                                    </span>
                                    @if($visitor->city_or_markaz)
                                        <div class="fw-semibold text-dark mt-1" style="font-size: 13px;">
                                            {{ $visitor->city_or_markaz }}
                                        </div>
                                    @endif
                                    @if($visitor->district)
                                        <div class="small text-muted" style="font-size: 11px;">
                                            {{ $visitor->district }}
                                        </div>
                                    @endif

                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        @if($visitor->location_source === 'gps')
                                            <span class="badge bg-success bg-opacity-10 text-success" style="font-size: 10px;" title="تم التحديد عبر إحداثيات GPS من المتصفح بدقة">
                                                دقة GPS عالية
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted border" style="font-size: 10px;" title="تم التحديد تقريبياً عبر عنوان IP الشبكة">
                                                تحديد IP
                                            </span>
                                        @endif

                                        @if($visitor->googleMapsUrl())
                                            <a href="{{ $visitor->googleMapsUrl() }}" target="_blank" class="small text-primary text-decoration-none" title="فتح الموقع على خرائط جوجل">
                                                الخريطة ↗
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Device & Browser -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary">
                                        @if($visitor->device_type === 'mobile')
                                            @include('admin.partials.icon', ['iconName' => 'smartphone', 'size' => 20])
                                        @elseif($visitor->device_type === 'tablet')
                                            @include('admin.partials.icon', ['iconName' => 'tablet', 'size' => 20])
                                        @else
                                            @include('admin.partials.icon', ['iconName' => 'monitor', 'size' => 20])
                                        @endif
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark" style="font-size: 13px;">
                                            {{ $visitor->browser ?: 'متصفح ويب' }}
                                        </div>
                                        <div class="small text-muted" style="font-size: 11px;">
                                            {{ $visitor->os ?: 'نظام غير محدد' }}
                                            @if($visitor->screen_resolution)
                                                ({{ $visitor->screen_resolution }})
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Page & Browsing -->
                            <td>
                                <div class="text-truncate" style="max-width: 280px;" title="{{ $visitor->last_page }}">
                                    <a href="{{ $visitor->last_page ?: '#' }}" target="_blank" class="text-dark text-decoration-none small">
                                        {{ $visitor->last_page ?: 'الصفحة الرئيسية' }}
                                    </a>
                                </div>
                                <div class="small text-muted mt-1 d-flex gap-2">
                                    <span>{{ $visitor->visits_count }} زيارة</span>
                                    <span>•</span>
                                    <span>{{ $visitor->page_views_count }} صفحة</span>
                                    @if($visitor->referrer)
                                        <span>•</span>
                                        <span class="text-truncate" style="max-width: 140px;" title="{{ $visitor->referrer }}">من: {{ parse_url($visitor->referrer, PHP_URL_HOST) ?: 'مباشر' }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- IP Address -->
                            <td>
                                <code class="small text-dark">{{ $visitor->ip_address }}</code>
                            </td>

                            <!-- Last Active -->
                            <td>
                                <div class="small fw-semibold text-dark">
                                    {{ $visitor->last_active_at ? $visitor->last_active_at->diffForHumans() : 'غير محدد' }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $visitor->last_active_at ? $visitor->last_active_at->format('Y-m-d H:i') : '' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="pe-3 text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <a href="{{ route('admin.visitors.show', $visitor) }}" class="btn btn-sm btn-light text-primary" title="عرض تفاصيل الزائر وسجل نشاطه">
                                        @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 15])
                                    </a>
                                    <form method="POST" action="{{ route('admin.visitors.destroy', $visitor) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger" title="حذف">
                                            @include('admin.partials.icon', ['iconName' => 'trash-2', 'size' => 15])
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-2">
                                    @include('admin.partials.icon', ['iconName' => 'map-pin', 'size' => 48])
                                </div>
                                <h5>لا توجد سجلات زوار تطابق معايير البحث</h5>
                                <p class="small text-muted">سيتم تسجيل زوار الموقع تلقائياً بمجرد دخولهم للمنصة.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visitors->hasPages())
            <div class="p-3 border-top">
                {{ $visitors->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

