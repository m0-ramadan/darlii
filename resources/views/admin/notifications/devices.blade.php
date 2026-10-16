@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('page_actions')
    <div class="d-flex gap-2">
        <a href="{{ admin_route('admin.notifications.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 15])
            سجل الإشعارات
        </a>
        <a href="{{ admin_route('admin.notifications.create') }}" class="ap-btn ap-btn--primary ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'send', 'size' => 15])
            إرسال إشعار
        </a>
    </div>
@endsection

@section('content')
    {{-- Tabs --}}
    <div class="ap-tabs mb-4">
        <a href="{{ admin_route('admin.notifications.index') }}" class="ap-tab">
            @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 16])
            سجل الإشعارات
        </a>
        <a href="{{ admin_route('admin.notifications.devices') }}" class="ap-tab is-active">
            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 16])
            المشتركون ورموز الأجهزة ({{ $stats['total'] }})
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
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'users', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">إجمالي الأجهزة المسجلة</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">إشعارات الويب (Web Push)</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($stats['web_push']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">رموز الزوار (Visitor Tokens)</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['web_visitor']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="ap-card p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning" style="width: 48px; height: 48px;">
                    @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 24])
                </div>
                <div>
                    <div class="text-muted small fw-medium">مستخدمون مسجلون</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['registered']) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="ap-card mb-4">
        <div class="ap-card__body py-3">
            <form method="GET" action="{{ admin_route('admin.notifications.devices') }}" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="ابحث في رمز الجهاز، المتصفح، النظام، أو المستخدم..." value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            @include('admin.partials.icon', ['iconName' => 'search', 'size' => 15])
                        </button>
                    </div>
                    @if($search || $selectedType)
                        <a href="{{ admin_route('admin.notifications.devices') }}" class="btn btn-sm btn-link text-decoration-none">إلغاء التصفية</a>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-bold">النوع:</span>
                    <a href="{{ admin_route('admin.notifications.devices', ['type' => '', 'search' => $search]) }}"
                        class="ap-btn {{ empty($selectedType) ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
                        الكل ({{ $stats['total'] }})
                    </a>
                    <a href="{{ admin_route('admin.notifications.devices', ['type' => 'web_push', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedType === 'web_push' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
                        🔔 إشعارات الويب ({{ $stats['web_push'] }})
                    </a>
                    <a href="{{ admin_route('admin.notifications.devices', ['type' => 'web_visitor', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedType === 'web_visitor' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
                        🌐 زوار ({{ $stats['web_visitor'] }})
                    </a>
                    <a href="{{ admin_route('admin.notifications.devices', ['type' => 'registered', 'search' => $search]) }}"
                        class="ap-btn {{ $selectedType === 'registered' ? 'ap-btn--primary' : 'ap-btn--ghost' }} ap-btn--sm" style="border-radius: 9999px;">
                        👤 مسجلون ({{ $stats['registered'] }})
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Devices Table Card --}}
    <div class="ap-card">
        <div class="ap-card__header d-flex align-items-center justify-content-between">
            <h2 class="ap-card__title">سجل الأجهزة والمشتركين</h2>
            <span class="text-muted small">عرض {{ $devices->count() }} من أصل {{ $devices->total() }} جهاز</span>
        </div>

        @if ($devices->isEmpty())
            <div class="ap-card__body text-center py-5">
                <div class="text-muted mb-3">
                    @include('admin.partials.icon', ['iconName' => 'users', 'size' => 48])
                </div>
                <h3 class="h5 fw-bold text-dark mb-2">لا توجد أجهزة مطابقة للبحث</h3>
                <p class="text-muted mb-4">جرّب تغيير كلمات البحث أو إزالة التصفية لعرض جميع الأجهزة المسجلة.</p>
                <a href="{{ admin_route('admin.notifications.devices') }}" class="ap-btn ap-btn--primary">عرض جميع الأجهزة</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="ap-table w-100">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>المستخدم</th>
                            <th>نوع الاشتراك</th>
                            <th>المتصفح ونظام التشغيل</th>
                            <th>رمز الجهاز (Token)</th>
                            <th>آخر نشاط</th>
                            <th>تاريخ التسجيل</th>
                            <th class="text-end" style="width: 80px;">إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($devices as $device)
                            <tr>
                                <td>
                                    <span class="text-muted small fw-mono">#{{ $device->id }}</span>
                                </td>

                                <td>
                                    @if ($device->user)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                                {{ mb_substr($device->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 160px;">{{ $device->user->name }}</div>
                                                <div class="text-muted small" style="font-size: 11px;">{{ $device->user->email }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-light text-secondary border">
                                            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 12])
                                            زائر للموقع
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($device->device_type === 'web_push')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 py-1 px-2">
                                            🔔 إشعار فوري (Web Push)
                                        </span>
                                    @elseif($device->device_type === 'web_visitor')
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 py-1 px-2">
                                            🌐 رمز زائر (Visitor)
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border py-1 px-2">
                                            {{ $device->type_label }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark border">
                                            {{ $device->browser_name }}
                                        </span>
                                        <span class="text-muted small">
                                            {{ $device->platform_name }}
                                            @if($device->is_mobile)
                                                <span class="badge bg-warning bg-opacity-10 text-warning ms-1" style="font-size: 10px;">هاتف</span>
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <code class="text-dark bg-light px-2 py-1 rounded small border" style="font-size: 11px; max-width: 170px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $device->token }}">
                                            {{ $device->short_token }}
                                        </code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary p-1 border-0 copy-token-btn" data-token="{{ $device->token }}" title="نسخ الرمز">
                                            @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 14])
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info p-1 border-0 view-token-btn" data-token="{{ $device->token }}" data-id="{{ $device->id }}" data-browser="{{ $device->browser_name }}" data-os="{{ $device->platform_name }}" data-user="{{ $device->user?->name ?? 'زائر' }}" title="عرض التفاصيل">
                                            @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 14])
                                        </button>
                                    </div>
                                </td>

                                <td>
                                    @if ($device->last_used_at)
                                        <div class="small text-dark" title="{{ $device->last_used_at->format('Y-m-d H:i:s') }}">
                                            {{ $device->last_used_at->diffForHumans() }}
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="small text-muted" title="{{ $device->created_at->format('Y-m-d H:i:s') }}">
                                        {{ $device->created_at->format('Y-m-d') }}
                                        <div style="font-size: 10px;">{{ $device->created_at->format('H:i') }}</div>
                                    </div>
                                </td>

                                <td class="text-end">
                                    <form action="{{ admin_route('admin.notifications.devices.destroy', $device->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف رمز هذا الجهاز؟ لن يتمكن من استلام إشعارات بعد ذلك.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm p-1" title="حذف الجهاز">
                                            @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 14])
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($devices->hasPages())
                <div class="ap-card__footer d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        عرض الصفحة {{ $devices->currentPage() }} من {{ $devices->lastPage() }}
                    </div>
                    <div>
                        {{ $devices->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Modal View Token Details --}}
    <div class="modal fade" id="tokenDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18])
                        تفاصيل رمز الجهاز <span id="detailDeviceId" class="text-muted small fw-mono"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small text-muted">المستخدم المرتبط:</label>
                        <div id="detailDeviceUser" class="fw-bold text-dark"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">المتصفح ونظام التشغيل:</label>
                        <div id="detailDeviceEnv" class="badge bg-light text-dark border p-2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted">رمز الجهاز الكامل (FCM Token):</label>
                        <textarea id="detailDeviceToken" class="form-control font-monospace small" rows="5" readonly style="font-size: 11px; direction: ltr; text-align: left;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary d-flex align-items-center gap-1" id="btnCopyDetailToken">
                        @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 15])
                        نسخ الرمز كاملاً
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Copy token to clipboard
    document.querySelectorAll('.copy-token-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const token = this.getAttribute('data-token');
            if (!token) return;
            navigator.clipboard.writeText(token).then(() => {
                const orig = this.innerHTML;
                this.innerHTML = '<span style="color:#22c55e; font-size:12px;">✓ تم النسخ</span>';
                setTimeout(() => { this.innerHTML = orig; }, 1500);
            });
        });
    });

    // View Token Modal
    const tokenModalEl = document.getElementById('tokenDetailModal');
    if (tokenModalEl) {
        const tokenModal = new bootstrap.Modal(tokenModalEl);
        document.querySelectorAll('.view-token-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('detailDeviceId').innerText = '#' + this.getAttribute('data-id');
                document.getElementById('detailDeviceUser').innerText = this.getAttribute('data-user');
                document.getElementById('detailDeviceEnv').innerText = this.getAttribute('data-browser') + ' - ' + this.getAttribute('data-os');
                document.getElementById('detailDeviceToken').value = this.getAttribute('data-token');
                tokenModal.show();
            });
        });

        document.getElementById('btnCopyDetailToken')?.addEventListener('click', function() {
            const token = document.getElementById('detailDeviceToken').value;
            navigator.clipboard.writeText(token).then(() => {
                this.innerText = '✓ تم النسخ بنجاح!';
                setTimeout(() => {
                    this.innerHTML = `@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 15]) نسخ الرمز كاملاً`;
                }, 1500);
            });
        });
    }
});
</script>
@endpush

