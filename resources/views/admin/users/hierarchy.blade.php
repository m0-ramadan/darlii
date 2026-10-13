@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'الهيكل الإداري: أدمن ← مسؤول ← سمسار — كل مستخدم مرتبط بالمسؤول المتابع له')

@section('page_actions')
    @can('users.create')
        <a href="{{ admin_route('admin.users.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) مستخدم جديد
        </a>
    @endcan
@endsection

@section('content')
    @forelse ($admins as $adminUser)
        <div class="ap-card">
            <div class="ap-card__head">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $adminUser->avatar_url }}" alt="" class="ap-avatar">
                    <div>
                        <h2 class="ap-card__title" style="margin: 0;">
                            {{ $adminUser->name }}
                            <span class="ap-badge ap-badge--gold">{{ user_role_label($adminUser->primary_role_slug) }}</span>
                        </h2>
                        <p class="ap-card__subtitle">
                            {{ $adminUser->email }} ·
                            {{ $adminUser->properties_count }} عقار ·
                            {{ $adminUser->subordinates->count() }} تابع مباشر
                        </p>
                    </div>
                </div>
                <a href="{{ admin_route('admin.users.show', $adminUser) }}" class="ap-btn ap-btn--ghost ap-btn--sm">الملف الشخصي</a>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adminUser->subordinates as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $member->avatar_url }}" alt="" class="ap-avatar ap-avatar--sm">
                                            <span class="fw-bold">{{ $member->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ap-badge ap-badge--{{ $member->isManager() ? 'info' : 'secondary' }}">
                                            {{ user_role_label($member->primary_role_slug) }}
                                        </span>
                                    </td>
                                    <td>{{ $adminUser->name }}</td>
                                    <td>{{ $member->subordinates->count() }}</td>
                                    <td>{{ $member->properties()->count() }}</td>
                                    <td><span class="ap-badge ap-badge--{{ $member->status === 'active' ? 'success' : 'secondary' }}">{{ status_label($member->status) }}</span></td>
                                    <td>
                                        <a href="{{ admin_route('admin.users.show', $member) }}" class="ap-btn ap-btn--ghost ap-btn--sm">عرض</a>
                                    </td>
                                </tr>

                                {{-- سماسرة المسؤول --}}
                                @foreach ($member->subordinates as $agent)
                                    <tr style="background: #fafbfc;">
                                        <td>
                                            <div class="d-flex align-items-center gap-2" style="padding-inline-start: 22px;">
                                                <span class="text-muted">↳</span>
                                                <img src="{{ $agent->avatar_url }}" alt="" class="ap-avatar ap-avatar--sm">
                                                <span>{{ $agent->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="ap-badge ap-badge--{{ $agent->isAgent() ? 'secondary' : 'info' }}">
                                                {{ user_role_label($agent->primary_role_slug) }}
                                            </span>
                                        </td>
                                        <td>{{ $member->name }}</td>
                                        <td>{{ $agent->subordinates->count() }}</td>
                                        <td>{{ $agent->properties()->count() }}</td>
                                        <td><span class="ap-badge ap-badge--{{ $agent->status === 'active' ? 'success' : 'secondary' }}">{{ status_label($agent->status) }}</span></td>
                                        <td>
                                            <a href="{{ admin_route('admin.users.show', $agent) }}" class="ap-btn ap-btn--ghost ap-btn--sm">عرض</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">لا يوجد مستخدمون تابعون مباشرة لهذا الحساب.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="ap-card">
            <div class="ap-card__body">
                <div class="ap-empty">
                    @include('admin.partials.icon', ['iconName' => 'users', 'size' => 42])
                    <p>لا يوجد أدمن بعد.</p>
                </div>
            </div>
        </div>
    @endforelse

    @if ($unassigned->isNotEmpty())
        <div class="ap-card">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'alert', 'size' => 18]) مستخدمون بدون مسؤول متابع</h2>
                    <p class="ap-card__subtitle">اربطهم بمسؤول حتى تظهر عقاراتهم في لوحة المسؤول الصحيح</p>
                </div>
                <span class="ap-badge ap-badge--warning">{{ $unassigned->count() }}</span>
            </div>
            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead><tr><th>المستخدم</th><th>الدور</th><th>ربط بمسؤول</th></tr></thead>
                        <tbody>
                            @foreach ($unassigned as $member)
                                <tr>
                                    <td>{{ $member->name }} <span class="text-muted" dir="ltr">({{ $member->email }})</span></td>
                                    <td><span class="ap-badge ap-badge--secondary">{{ user_role_label($member->primary_role_slug) }}</span></td>
                                    <td>
                                        @can('users.assign-manager')
                                            <form method="post" action="{{ admin_route('admin.users.manager', $member) }}" class="d-flex gap-1">
                                                @csrf
                                                <select name="manager_id" class="ap-select ap-select--sm" required>
                                                    <option value="">— اختر —</option>
                                                    @foreach ($admins as $adminUser)
                                                        <option value="{{ $adminUser->id }}">{{ $adminUser->name }} ({{ user_role_label($adminUser->primary_role_slug) }})</option>
                                                    @endforeach
                                                </select>
                                                <button class="ap-btn ap-btn--sm">ربط</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
