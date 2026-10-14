@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? '')

@section('page_actions')
    @if ($user->status === 'pending')
        <form method="post" action="{{ admin_route('admin.users.activate', $user) }}" class="d-inline">
            @csrf
            <button class="ap-btn ap-btn--gold ap-btn--sm">
                @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16]) اعتماد وتفعيل الحساب
            </button>
        </form>
    @endif
    @can('users.update')
        <a href="{{ admin_route('admin.users.edit', $user) }}" class="ap-btn ap-btn--ghost ap-btn--sm">تعديل</a>
    @endcan
    <a href="{{ admin_route('admin.users.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a>
@endsection

@section('content')
    @if ($user->status === 'pending')
        <div class="ap-alert ap-alert--warning" style="margin-bottom: 18px; border-radius: 12px; padding: 16px 20px;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>⏳ تنبيه: هذا الحساب معلق وبانتظار اعتماد الإدارة.</strong>
                    <div style="font-size: 13px; margin-top: 4px;">لا يمكن للمستخدم نشر عقارات أو مشاريع حتى تقوم باعتماد وتفعيل حسابه.</div>
                </div>
                <form method="post" action="{{ admin_route('admin.users.activate', $user) }}">
                    @csrf
                    <button class="ap-btn ap-btn--gold ap-btn--sm">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 15]) تفعيل الحساب الآن
                    </button>
                </form>
            </div>
        </div>
    @endif
    <div class="ap-grid ap-grid--3" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">عقاراته</div>
                <div class="ap-stat__value">{{ number_format($propertiesCount) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">التابعون له</div>
                <div class="ap-stat__value">{{ $user->subordinates->count() }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">العملاء المحتملون</div>
                <div class="ap-stat__value">{{ number_format($leadsCount) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div>
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) البيانات</h2>
                </div>
                <div class="ap-card__body">
                    <div class="d-flex align-items-center gap-3" style="margin-bottom: 16px;">
                        <img src="{{ $user->avatar_url }}" alt="" class="ap-avatar" style="width: 66px; height: 66px;">
                        <div>
                            <h3 style="margin: 0;">{{ $user->name }}</h3>
                            <div class="text-muted" dir="ltr" style="font-size: 12.5px;">{{ $user->email }}</div>
                            <div class="d-flex gap-1" style="margin-top: 6px;">
                                @foreach ($user->roles as $role)
                                    <span class="ap-badge ap-badge--{{ $role->level >= 90 ? 'gold' : ($role->level >= 55 ? 'info' : 'secondary') }}">{{ $role->label_ar ?: $role->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <table class="ap-table">
                        <tbody>
                            <tr><th style="width: 45%;">الهاتف</th><td dir="ltr">{{ $user->phone ?? '—' }}</td></tr>
                            <tr><th>المسمى الوظيفي</th><td>{{ $user->job_title ?? '—' }}</td></tr>
                            <tr><th>الحالة</th><td><span class="ap-badge ap-badge--{{ $user->status === 'active' ? 'success' : 'secondary' }}">{{ status_label($user->status) }}</span></td></tr>
                            <tr><th>المسؤول المتابع</th><td>{{ $user->manager?->name ?? '—' }}</td></tr>
                            <tr><th>آخر دخول</th><td>{{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}</td></tr>
                            <tr><th>آخر IP</th><td dir="ltr">{{ $user->last_login_ip ?? '—' }}</td></tr>
                            <tr><th>مسجّل منذ</th><td>{{ $user->created_at?->format('Y-m-d') }}</td></tr>
                        </tbody>
                    </table>

                    @if ($user->notes)
                        <div class="ap-alert ap-alert--warning" style="margin-top: 12px; margin-bottom: 0;">
                            <div><strong>ملاحظات:</strong> {{ $user->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($user->agentProfile)
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 18]) ملف السمسار والتوثيق</h2>
                        @if ($user->agentProfile->status === 'pending')
                            <span class="ap-badge ap-badge--warning">بانتظار الاعتماد</span>
                        @endif
                    </div>
                    <div class="ap-card__body">
                        <table class="ap-table">
                            <tbody>
                                <tr><th style="width: 45%;">المسمى</th><td>{{ $user->agentProfile->job_title ?? '—' }}</td></tr>
                                <tr><th>واتساب</th><td dir="ltr">{{ $user->agentProfile->whatsapp ?? '—' }}</td></tr>
                                <tr><th>سنوات الخبرة</th><td>{{ $user->agentProfile->years_experience ?? '—' }}</td></tr>
                                <tr><th>الرابط العام</th><td><code>{{ $user->agentProfile->slug }}</code></td></tr>
                                @if ($user->agentProfile->national_id_url)
                                    <tr>
                                        <th>بطاقة الرقم القومي</th>
                                        <td>
                                            <a href="{{ $user->agentProfile->national_id_url }}" target="_blank" class="ap-btn ap-btn--gold ap-btn--sm" style="display: inline-flex; align-items: center; gap: 6px;">
                                                @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 14]) عرض صورة البطاقة
                                            </a>
                                            <div class="mt-2">
                                                <a href="{{ $user->agentProfile->national_id_url }}" target="_blank">
                                                    <img src="{{ $user->agentProfile->national_id_url }}" alt="بطاقة الرقم القومي" class="rounded shadow-sm border" style="max-width: 100%; max-height: 180px; object-fit: contain;">
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        @if ($user->agentProfile->bio)
                            <p style="margin-top: 10px; font-size: 13px;">{{ $user->agentProfile->bio }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($user->agency)
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 18]) بيانات الشركة / الوكالة العقارية</h2>
                        @if ($user->agency->status === 'pending')
                            <span class="ap-badge ap-badge--warning">معلقة</span>
                        @else
                            <span class="ap-badge ap-badge--success">نشطة</span>
                        @endif
                    </div>
                    <div class="ap-card__body">
                        <table class="ap-table">
                            <tbody>
                                <tr><th style="width: 45%;">اسم الشركة</th><td class="fw-bold">{{ $user->agency->name }}</td></tr>
                                <tr><th>البريد الإلكتروني للشركة</th><td dir="ltr">{{ $user->agency->email ?? '—' }}</td></tr>
                                <tr><th>هاتف الشركة</th><td dir="ltr">{{ $user->agency->phone ?? '—' }}</td></tr>
                                <tr><th>الرابط التعريفي</th><td><code>{{ $user->agency->slug }}</code></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div>
            @can('users.update')
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18]) إعادة تعيين كلمة المرور</h2>
                    </div>
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.users.password', $user) }}">
                            @csrf
                            <div class="ap-field">
                                <label class="ap-label">كلمة مرور جديدة</label>
                                <input type="text" name="password" class="ap-input" dir="ltr" required autocomplete="new-password">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">تأكيد كلمة المرور</label>
                                <input type="text" name="password_confirmation" class="ap-input" dir="ltr" required autocomplete="new-password">
                            </div>
                            <button class="ap-btn ap-btn--sm ap-btn--gold">تغيير كلمة المرور</button>
                        </form>
                    </div>
                </div>
            @endcan

            @if ($user->subordinates->isNotEmpty())
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) الفريق التابع له</h2>
                        <span class="ap-badge">{{ $user->subordinates->count() }}</span>
                    </div>
                    <div class="ap-card__body ap-card__body--tight">
                        <div class="ap-table-wrap">
                            <table class="ap-table">
                                <thead><tr><th>الاسم</th><th>الدور</th><th>الحالة</th></tr></thead>
                                <tbody>
                                    @foreach ($user->subordinates as $subordinate)
                                        <tr>
                                            <td><a href="{{ admin_route('admin.users.show', $subordinate) }}">{{ $subordinate->name }}</a></td>
                                            <td>{{ user_role_label($subordinate->primary_role_slug) }}</td>
                                            <td><span class="ap-badge ap-badge--{{ $subordinate->status === 'active' ? 'success' : 'secondary' }}">{{ status_label($subordinate->status) }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 18]) أحدث عقاراته</h2>
                </div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap">
                        <table class="ap-table">
                            <thead><tr><th>العقار</th><th>السعر</th><th>الحالة</th></tr></thead>
                            <tbody>
                                @forelse ($properties as $property)
                                    <tr>
                                        <td>
                                            <a href="{{ admin_route('admin.properties.show', $property) }}">
                                                {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 34) }}
                                            </a>
                                        </td>
                                        <td>{{ money_eg($property->price) }}</td>
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

            @can('users.delete')
                <div class="ap-card">
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.users.destroy', $user) }}"
                            data-confirm="حذف المستخدم {{ $user->name }} نهائياً؟">
                            @csrf
                            @method('delete')
                            <button class="ap-btn ap-btn--danger ap-btn--block">حذف المستخدم</button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
@endsection
