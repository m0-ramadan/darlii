@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ admin_route('admin.notifications.settings') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 15])
            إعدادات Firebase
        </a>
        <a href="{{ admin_route('admin.notifications.create') }}" class="ap-btn ap-btn--primary ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'send', 'size' => 15])
            إرسال إشعار جديد
        </a>
    </div>
@endsection

@section('content')
    {{-- Tabs --}}
    <div class="ap-tabs mb-4">
        <a href="{{ admin_route('admin.notifications.index') }}" class="ap-tab is-active">
            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 16])
            سجل الإشعارات المرسلة ({{ $stats['total_sent'] }})
        </a>
        <a href="{{ admin_route('admin.notifications.devices') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 16])
            المشتركون ورموز الأجهزة ({{ $stats['total_devices'] }})
        </a>
        <a href="{{ admin_route('admin.notifications.create') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'send', 'size' => 16])
            إرسال إشعار جديد
        </a>
        <a href="{{ admin_route('admin.notifications.settings') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 16])
            إعدادات Firebase و Web Push
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary"
                    style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">إجمالي الإشعارات</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total_sent'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3 ap-clickable-device-card"
                style="cursor: pointer; transition: all 0.2s ease-in-out; border-right: 4px solid #06b6d4;"
                data-bs-toggle="modal" data-bs-target="#devicesModal" title="انقر هنا لعرض جميع الأجهزة المشتركة وتفاصيلها">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info"
                    style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'users', 'size' => 24])
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small fw-medium">المشتركون (رموز الأجهزة)</span>
                        <span class="badge bg-info bg-opacity-10 text-info px-2 py-1" style="font-size: 11px;">عرض الأجهزة
                            &larr;</span>
                    </div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total_devices'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $stats['is_firebase_enabled'] ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}"
                    style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'plug', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">خدمة Firebase</div>
                    <div class="fw-bold {{ $stats['is_firebase_enabled'] ? 'text-success' : 'text-danger' }}">
                        {{ $stats['is_firebase_enabled'] ? 'مفعلة ونشطة' : 'معطلة' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $stats['has_server_key'] ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}"
                    style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">مفتاح السيرفر (FCM)</div>
                    <div class="fw-bold {{ $stats['has_server_key'] ? 'text-success' : 'text-warning' }}">
                        {{ $stats['has_server_key'] ? 'متصل وجاهز' : 'غير مدخل بعد' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="ap-card mb-4">
        <div class="ap-card__body py-3">
            <form method="GET" action="{{ admin_route('admin.notifications.index') }}"
                class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control"
                            placeholder="ابحث في الإشعارات (العنوان أو النص)..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            @include('admin.partials.icon', ['iconName' => 'search', 'size' => 15])
                        </button>
                    </div>
                    @if ($search || $selectedTarget)
                        <a href="{{ admin_route('admin.notifications.index') }}"
                            class="btn btn-sm btn-link text-decoration-none">إلغاء التصفية</a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small fw-bold">الجمهور:</span>
                    <a href="{{ admin_route('admin.notifications.index', ['target_type' => '', 'search' => $search]) }}"
                        class="ap-btn {{ empty($selectedTarget) ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm"
                        style="border-radius: 9999px;">
                        الكل
                    </a>
                    <a href="{{ admin_route('admin.notifications.index', ['target_type' => 'all', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedTarget === 'all' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm"
                        style="border-radius: 9999px;">
                        عام لجميع المشتركين
                    </a>
                    <a href="{{ admin_route('admin.notifications.index', ['target_type' => 'user', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedTarget === 'user' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm"
                        style="border-radius: 9999px;">
                        خاص لمستخدمين
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Notifications List Card --}}
    <div class="ap-card">
        <div class="ap-card__header d-flex align-items-center justify-content-between">
            <h2 class="ap-card__title">سجل الإشعارات</h2>
            <span class="text-muted small">عرض {{ $notifications->count() }} من أصل {{ $notifications->total() }}</span>
        </div>

        @if ($notifications->isEmpty())
            <div class="ap-card__body text-center py-5">
                <div class="text-muted mb-3">
                    @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 48])
                </div>
                <h3 class="h5 fw-bold text-dark mb-2">لا توجد إشعارات مرسلة حتى الآن</h3>
                <p class="text-muted mb-4">يمكنك إرسال إشعار عام لجميع زوار ومشتركي الموقع، أو إرسال إشعار خاص لمستخدم معين
                    مع صورة ورابط توجيه.</p>
                <a href="{{ admin_route('admin.notifications.create') }}" class="ap-btn ap-btn--primary">
                    @include('admin.partials.icon', ['iconName' => 'send', 'size' => 16])
                    إرسال أول إشعار الآن
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="ap-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 70px;">الصورة</th>
                            <th>الإشعار</th>
                            <th style="width: 170px;">الجمهور المستهدف</th>
                            <th style="width: 150px;">حالة الإرسال</th>
                            <th style="width: 140px;">تاريخ الإرسال</th>
                            <th style="width: 110px;" class="text-end">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notifications as $item)
                            <tr>
                                <td>
                                    @if ($item->image_url)
                                        <a href="{{ $item->image_url }}" target="_blank" title="عرض الصورة بالحجم الكامل">
                                            <img src="{{ $item->image_url }}" alt="صورة الإشعار"
                                                class="rounded object-fit-cover shadow-sm border"
                                                style="width: 52px; height: 52px;">
                                        </a>
                                    @else
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted"
                                            style="width: 52px; height: 52px;">
                                            @include('admin.partials.icon', [
                                                'iconName' => 'bell',
                                                'size' => 20,
                                            ])
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">{{ $item->title }}</div>
                                    <div class="text-muted small mb-1" style="max-width: 480px; line-height: 1.4;">
                                        {{ Str::limit($item->body, 120) }}</div>
                                    @if ($item->action_url)
                                        <a href="{{ $item->action_url }}" target="_blank"
                                            class="text-primary small text-decoration-none d-inline-flex align-items-center gap-1">
                                            @include('admin.partials.icon', [
                                                'iconName' => 'external',
                                                'size' => 12,
                                            ])
                                            {{ Str::limit($item->action_url, 45) }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->target_type === 'all')
                                        <span
                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill">
                                            @include('admin.partials.icon', [
                                                'iconName' => 'users',
                                                'size' => 13,
                                            ])
                                            عام للجميع
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill">
                                            مستخدم محدد
                                        </span>
                                        @if ($item->user)
                                            <div class="fw-medium small text-dark mt-1">{{ $item->user->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $item->user->email }}
                                            </div>
                                        @else
                                            <div class="text-muted small mt-1">مستخدم محذوف (#{{ $item->user_id }})</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($item->sent_count > 0)
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            تم البث ({{ $item->sent_count }} جهاز)
                                        </span>
                                    @elseif ($item->fcm_status === 'ready_needs_server_key')
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25"
                                            title="تم الحفظ بالموقع ولكن يتطلب إدخال مفتاح السيرفر في الإعدادات للبث الخارجي">
                                            محفوظ (ينقصه مفتاح FCM)
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            محفوظ في الموقع
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium">{{ $item->created_at->format('Y-m-d') }}</div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ $item->created_at->format('H:i') }} ({{ $item->created_at->diffForHumans() }})
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST"
                                            action="{{ admin_route('admin.notifications.destroy', $item->id) }}"
                                            onsubmit="return confirm('هل أنت متأكد من حذف هذا الإشعار من السجل؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1"
                                                title="حذف">
                                                @include('admin.partials.icon', [
                                                    'iconName' => 'trash',
                                                    'size' => 15,
                                                ])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($notifications->hasPages())
                <div class="ap-card__footer p-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Modal عرض الأجهزة والمشتركين وتفاصيلها عند الضغط على الكارت --}}
    <div class="modal fade" id="devicesModal" tabindex="-1" aria-labelledby="devicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-light py-3 px-4 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info"
                            style="width: 38px; height: 38px;">
                            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="devicesModalLabel">المشتركون ورموز الأجهزة
                                المسجلة</h5>
                            <span class="text-muted small">تفاصيل الأجهزة والمتصفحات المشتركة في إشعارات الموقع وزوار
                                المنصة</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ admin_route('admin.notifications.devices') }}"
                            class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1">
                            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 14])
                            فتح في صفحة كاملة
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                </div>

                <div class="modal-body p-4">
                    {{-- شريط الإحصائيات السريع داخل النافذة --}}
                    <div class="row g-2 mb-3">
                        <div class="col-sm-4">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <span class="small text-muted">🔔 إشعارات الويب (Web Push):</span>
                                <span class="fw-bold text-success">{{ $stats['web_push_count'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <span class="small text-muted">🌐 زوار الموقع (Visitor Tokens):</span>
                                <span class="fw-bold text-info">{{ $stats['web_visitor_count'] ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <span class="small text-muted">👤 مستخدمون مسجلون:</span>
                                <span class="fw-bold text-dark">{{ $stats['registered_users_count'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- البحث والفلترة السريعة --}}
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
                        <div class="input-group input-group-sm flex-grow-1" style="max-width: 380px;">
                            <span class="input-group-text bg-white">
                                @include('admin.partials.icon', ['iconName' => 'search', 'size' => 14])
                            </span>
                            <input type="text" id="modalDeviceSearch" class="form-control"
                                placeholder="بحث فوري في التوكن، المتصفح، النظام، أو المستخدم...">
                        </div>

                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active modal-filter-btn"
                                data-filter="all">الكل ({{ $modalDevices->count() }})</button>
                            <button type="button" class="btn btn-outline-secondary modal-filter-btn"
                                data-filter="web_push">🔔 Web Push</button>
                            <button type="button" class="btn btn-outline-secondary modal-filter-btn"
                                data-filter="web_visitor">🌐 زوار</button>
                            <button type="button" class="btn btn-outline-secondary modal-filter-btn"
                                data-filter="registered">👤 مسجلون</button>
                        </div>
                    </div>

                    {{-- جدول الأجهزة --}}
                    <div class="table-responsive border rounded" style="max-height: 480px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0" id="modalDevicesTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>المستخدم</th>
                                    <th>نوع الاشتراك</th>
                                    <th>المتصفح والنظام</th>
                                    <th>رمز الجهاز (Token)</th>
                                    <th>آخر نشاط</th>
                                    <th class="text-end" style="width: 70px;">حذف</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($modalDevices as $device)
                                    <tr class="modal-device-row" data-type="{{ $device->device_type }}"
                                        data-registered="{{ $device->user_id ? 'registered' : 'guest' }}"
                                        data-search="{{ strtolower(($device->user?->name ?? 'زائر') . ' ' . ($device->user?->email ?? '') . ' ' . $device->token . ' ' . $device->browser_name . ' ' . $device->platform_name . ' ' . $device->device_type) }}">
                                        <td>
                                            <span class="text-muted small fw-mono">#{{ $device->id }}</span>
                                        </td>
                                        <td>
                                            @if ($device->user)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                                        style="width: 30px; height: 30px; font-size: 12px;">
                                                        {{ mb_substr($device->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark text-truncate"
                                                            style="max-width: 140px;">{{ $device->user->name }}</div>
                                                        <div class="text-muted small" style="font-size: 11px;">
                                                            {{ $device->user->email }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-light text-secondary border">
                                                    زائر غير مسجل
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($device->device_type === 'web_push')
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">
                                                    🔔 إشعار ويب فوري
                                                </span>
                                            @elseif($device->device_type === 'web_visitor')
                                                <span
                                                    class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 py-1 px-2">
                                                    🌐 رمز زائر
                                                </span>
                                            @else
                                                <span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2">
                                                    {{ $device->type_label }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $device->browser_name }}
                                            </span>
                                            <span class="text-muted small ms-1">{{ $device->platform_name }}</span>
                                            @if ($device->is_mobile)
                                                <span class="badge bg-warning bg-opacity-10 text-warning ms-1"
                                                    style="font-size: 10px;">هاتف</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <code class="text-dark bg-light px-2 py-1 rounded small border"
                                                    style="font-size: 11px; max-width: 150px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                                    title="{{ $device->token }}">
                                                    {{ $device->short_token }}
                                                </code>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary p-1 border-0 copy-modal-token-btn"
                                                    data-token="{{ $device->token }}" title="نسخ الرمز">
                                                    @include('admin.partials.icon', [
                                                        'iconName' => 'clipboard',
                                                        'size' => 14,
                                                    ])
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="small text-muted"
                                                title="{{ $device->last_used_at?->format('Y-m-d H:i:s') ?? $device->created_at->format('Y-m-d H:i:s') }}">
                                                {{ $device->last_used_at?->diffForHumans() ?? $device->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form
                                                action="{{ admin_route('admin.notifications.devices.destroy', $device->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('هل تريد بالتأكيد حذف هذا الجهاز؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger p-1 border-0"
                                                    title="حذف الجهاز">
                                                    @include('admin.partials.icon', [
                                                        'iconName' => 'trash',
                                                        'size' => 14,
                                                    ])
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            لا توجد أجهزة مسجلة حالياً
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-light d-flex align-items-center justify-content-between py-2 px-4">
                    <span class="text-muted small" id="modalDeviceCount">عرض {{ $modalDevices->count() }} جهاز من أصل
                        {{ $stats['total_devices'] }}</span>
                    <div class="d-flex gap-2">
                        <a href="{{ admin_route('admin.notifications.devices') }}"
                            class="ap-btn ap-btn--primary ap-btn--sm">
                            الانتقال لصفحة الأجهزة الكاملة &larr;
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Copy token in modal
            document.querySelectorAll('.copy-modal-token-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const token = this.getAttribute('data-token');
                    if (!token) return;
                    navigator.clipboard.writeText(token).then(() => {
                        const orig = this.innerHTML;
                        this.innerHTML =
                            '<span style="color:#22c55e; font-size:11px;">✓ تم</span>';
                        setTimeout(() => {
                            this.innerHTML = orig;
                        }, 1500);
                    });
                });
            });

            // Search and filter inside devices modal
            const searchInput = document.getElementById('modalDeviceSearch');
            const filterBtns = document.querySelectorAll('.modal-filter-btn');
            const rows = document.querySelectorAll('.modal-device-row');
            const countEl = document.getElementById('modalDeviceCount');

            let currentFilter = 'all';

            function filterRows() {
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                let visibleCount = 0;

                rows.forEach(function(row) {
                    const rowSearch = row.getAttribute('data-search') || '';
                    const rowType = row.getAttribute('data-type') || '';
                    const rowReg = row.getAttribute('data-registered') || '';

                    let matchesType = true;
                    if (currentFilter === 'web_push') matchesType = (rowType === 'web_push');
                    else if (currentFilter === 'web_visitor') matchesType = (rowType === 'web_visitor');
                    else if (currentFilter === 'registered') matchesType = (rowReg === 'registered');

                    const matchesQuery = !query || rowSearch.includes(query);

                    if (matchesType && matchesQuery) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (countEl) {
                    countEl.innerText = 'عرض ' + visibleCount + ' جهاز مطابق للبحث';
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterRows);
            }

            filterBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.getAttribute('data-filter') || 'all';
                    filterRows();
                });
            });
        });
    </script>
@endpush
