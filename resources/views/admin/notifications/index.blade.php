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
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">إجمالي الإشعارات</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total_sent'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'users', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">المشتركون (رموز الأجهزة)</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total_devices'] }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $stats['is_firebase_enabled'] ? 'bg-success bg-opacity-10 text-success' : 'bg-secondary bg-opacity-10 text-secondary' }}" style="width: 48px; height: 48px;">
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
                <div class="rounded-circle d-flex align-items-center justify-content-center {{ $stats['has_server_key'] ? 'bg-success bg-opacity-10 text-success' : 'bg-warning bg-opacity-10 text-warning' }}" style="width: 48px; height: 48px;">
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
            <form method="GET" action="{{ admin_route('admin.notifications.index') }}" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="ابحث في الإشعارات (العنوان أو النص)..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            @include('admin.partials.icon', ['iconName' => 'search', 'size' => 15])
                        </button>
                    </div>
                    @if($search || $selectedTarget)
                        <a href="{{ admin_route('admin.notifications.index') }}" class="btn btn-sm btn-link text-decoration-none">إلغاء التصفية</a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small fw-bold">الجمهور:</span>
                    <a href="{{ admin_route('admin.notifications.index', ['target_type' => '', 'search' => $search]) }}"
                        class="ap-btn {{ empty($selectedTarget) ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
                        الكل
                    </a>
                    <a href="{{ admin_route('admin.notifications.index', ['target_type' => 'all', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedTarget === 'all' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
                        عام لجميع المشتركين
                    </a>
                    <a href="{{ admin_route('admin.notifications.index', ['target_type' => 'user', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedTarget === 'user' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
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
                <p class="text-muted mb-4">يمكنك إرسال إشعار عام لجميع زوار ومشتركي الموقع، أو إرسال إشعار خاص لمستخدم معين مع صورة ورابط توجيه.</p>
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
                                            <img src="{{ $item->image_url }}" alt="صورة الإشعار" class="rounded object-fit-cover shadow-sm border" style="width: 52px; height: 52px;">
                                        </a>
                                    @else
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 52px; height: 52px;">
                                            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 20])
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">{{ $item->title }}</div>
                                    <div class="text-muted small mb-1" style="max-width: 480px; line-height: 1.4;">{{ Str::limit($item->body, 120) }}</div>
                                    @if ($item->action_url)
                                        <a href="{{ $item->action_url }}" target="_blank" class="text-primary small text-decoration-none d-inline-flex align-items-center gap-1">
                                            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 12])
                                            {{ Str::limit($item->action_url, 45) }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->target_type === 'all')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 rounded-pill">
                                            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 13])
                                            عام للجميع
                                        </span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill">
                                            مستخدم محدد
                                        </span>
                                        @if ($item->user)
                                            <div class="fw-medium small text-dark mt-1">{{ $item->user->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">{{ $item->user->email }}</div>
                                        @else
                                            <div class="text-muted small mt-1">مستخدم محذوف (#{{ $item->user_id }})</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if ($item->sent_count > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                            تم البث ({{ $item->sent_count }} جهاز)
                                        </span>
                                    @elseif ($item->fcm_status === 'ready_needs_server_key')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25" title="تم الحفظ بالموقع ولكن يتطلب إدخال مفتاح السيرفر في الإعدادات للبث الخارجي">
                                            محفوظ (ينقصه مفتاح FCM)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                            محفوظ في الموقع
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium">{{ $item->created_at->format('Y-m-d') }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $item->created_at->format('H:i') }} ({{ $item->created_at->diffForHumans() }})</div>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST" action="{{ admin_route('admin.notifications.destroy', $item->id) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإشعار من السجل؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="حذف">
                                                @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 15])
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
@endsection

