@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? '')

@section('page_actions')
    <a href="{{ admin_route('admin.roles.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للمصفوفة</a>
@endsection

@section('content')
    <div class="ap-grid ap-grid--2" style="align-items: start;">
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18]) صلاحيات الدور</h2>
                <span class="ap-badge ap-badge--gold">مستوى {{ $role->level }}</span>
            </div>
            <div class="ap-card__body">
                @foreach ($permissionGroups as $group => $permissions)
                    <div style="margin-bottom: 16px;">
                        <h3 style="font-size: 13.5px; color: var(--ap-primary);">{{ $groupLabels[$group] ?? $group }}</h3>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($permissions as $permission)
                                @php $has = $role->permissions->contains('id', $permission->id); @endphp
                                <span class="ap-badge ap-badge--{{ $has ? 'success' : 'secondary' }}">
                                    {{ $has ? '✓' : '✕' }} {{ $permission->label_ar ?: $permission->slug }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18]) بيانات الدور</h2>
                </div>
                <div class="ap-card__body">
                    <table class="ap-table">
                        <tbody>
                            <tr><th style="width: 45%;">الاسم بالإنجليزية</th><td>{{ $role->name }}</td></tr>
                            <tr><th>الاسم بالعربية</th><td>{{ $role->label_ar }}</td></tr>
                            <tr><th>المُعرّف</th><td><code>{{ $role->slug }}</code></td></tr>
                            <tr><th>المستوى</th><td>{{ $role->level }}</td></tr>
                            <tr><th>دور نظام</th><td>{!! $role->is_system ? '<span class="ap-badge ap-badge--warning">نعم</span>' : 'لا' !!}</td></tr>
                            <tr><th>عدد الصلاحيات</th><td>{{ $role->permissions->count() }}</td></tr>
                            <tr><th>عدد المستخدمين</th><td>{{ $role->users->count() }}</td></tr>
                        </tbody>
                    </table>
                    @if ($role->description)
                        <p class="text-muted" style="margin-top: 10px; font-size: 12.5px;">{{ $role->description }}</p>
                    @endif
                </div>
            </div>

            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) المستخدمون بهذا الدور</h2>
                    <span class="ap-badge">{{ $role->users->count() }}</span>
                </div>
                <div class="ap-card__body ap-card__body--tight">
                    <div class="ap-table-wrap">
                        <table class="ap-table">
                            <thead><tr><th>المستخدم</th><th>البريد</th><th>الحالة</th><th></th></tr></thead>
                            <tbody>
                                @forelse ($role->users as $member)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $member->avatar_url }}" alt="" class="ap-avatar ap-avatar--sm">
                                                <span>{{ $member->name }}</span>
                                            </div>
                                        </td>
                                        <td dir="ltr">{{ $member->email }}</td>
                                        <td><span class="ap-badge ap-badge--{{ $member->status === 'active' ? 'success' : 'secondary' }}">{{ status_label($member->status) }}</span></td>
                                        <td>
                                            @can('users.update')
                                                <a href="{{ admin_route('admin.users.edit', $member) }}" class="ap-btn ap-btn--ghost ap-btn--sm">تعديل</a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">لا يوجد مستخدمون بهذا الدور.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
