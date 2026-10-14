@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'إدارة الحسابات: أدمن، مسؤول، سمسار — مع الربط الإجباري للمسؤول المتابع')

@section('page_actions')
    @can('users.create')
        <a href="{{ admin_route('admin.users.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) مستخدم جديد
        </a>
    @endcan
    <a href="{{ admin_route('admin.users.hierarchy') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'users', 'size' => 16]) شجرة الفريق
    </a>
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 200px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm" placeholder="الاسم، البريد أو الهاتف…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الدور</label>
                    <select name="role" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->slug }}" @selected(($filters['role'] ?? '') === $role->slug)>{{ $role->label_ar ?: $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>معلق (بانتظار الاعتماد)</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>نشط</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>غير نشط</option>
                        <option value="suspended" @selected(($filters['status'] ?? '') === 'suspended')>موقوف</option>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">المسؤول المتابع</label>
                    <select name="manager_id" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected((string) ($filters['manager_id'] ?? '') === (string) $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) المستخدمون <span class="ap-badge">{{ number_format($users->total()) }}</span></h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>الدور</th>
                            <th>المسؤول المتابع</th>
                            <th>التابعون</th>
                            <th>العقارات</th>
                            <th>الحالة</th>
                            <th>آخر دخول</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->avatar_url }}" alt="" class="ap-avatar ap-avatar--sm">
                                        <div>
                                            <a href="{{ admin_route('admin.users.show', $user) }}" class="fw-bold">{{ $user->name }}</a>
                                            <div class="text-muted" dir="ltr" style="font-size: 11px;">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $user->isAdmin() ? 'gold' : ($user->isManager() ? 'info' : 'secondary') }}">
                                        {{ user_role_label($user->primary_role_slug) }}
                                    </span>
                                    @if ($user->job_title)
                                        <div class="text-muted" style="font-size: 11px;">{{ $user->job_title }}</div>
                                    @endif
                                </td>
                                <td>
                                    @can('users.assign-manager')
                                        <form method="post" action="{{ admin_route('admin.users.manager', $user) }}" class="d-flex gap-1">
                                            @csrf
                                            <select name="manager_id" class="ap-select ap-select--sm" onchange="this.form.submit()">
                                                <option value="">— بدون —</option>
                                                @foreach ($managers as $manager)
                                                    @continue($manager->id === $user->id)
                                                    <option value="{{ $manager->id }}" @selected($user->manager_id === $manager->id)>{{ $manager->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        {{ $user->manager?->name ?? '—' }}
                                    @endcan
                                </td>
                                <td>{{ $user->subordinates_count }}</td>
                                <td>{{ $user->properties_count }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $user->status === 'active' ? 'success' : ($user->status === 'pending' ? 'warning' : ($user->status === 'suspended' ? 'danger' : 'secondary')) }}">
                                        {{ $user->status === 'pending' ? 'معلق (بانتظار الاعتماد)' : status_label($user->status) }}
                                    </span>
                                </td>
                                <td>{{ $user->last_login_at?->diffForHumans() ?? '—' }}</td>
                                <td>
                                    <div class="ap-table__actions">
                                        @if ($user->status === 'pending')
                                            <form method="post" action="{{ admin_route('admin.users.activate', $user) }}" class="d-inline">
                                                @csrf
                                                <button class="ap-btn ap-btn--gold ap-btn--sm" title="اعتماد وتفعيل الحساب فوراً" style="padding: 2px 8px; font-size: 11.5px;">
                                                    @include('admin.partials.icon', ['iconName' => 'check', 'size' => 14]) اعتماد
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ admin_route('admin.users.show', $user) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض">
                                            @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 15])
                                        </a>
                                        @can('users.update')
                                            <a href="{{ admin_route('admin.users.edit', $user) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                                @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                            </a>
                                            <form method="post" action="{{ admin_route('admin.users.status', $user) }}" class="d-inline">
                                                @csrf
                                                <button class="ap-btn ap-btn--ghost ap-btn--sm" title="{{ $user->status === 'active' ? 'إيقاف الحساب' : 'تفعيل الحساب' }}">
                                                    @include('admin.partials.icon', ['iconName' => 'toggle', 'size' => 15])
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'users', 'size' => 42])
                                        <p>لا يوجد مستخدمون مطابقون.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($users->hasPages())
            <div class="ap-pagination">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
