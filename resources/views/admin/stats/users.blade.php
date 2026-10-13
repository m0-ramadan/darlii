@extends('layouts.admin')

@section('title', 'إحصائيات المستخدمين')
@section('page_title', 'إحصائيات المستخدمين')
@section('page_subtitle', 'توزيع الفريق والأدوار والنشاط اليومي للمستخدمين')

@section('page_actions')
    <a href="{{ admin_route('admin.stats.export') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'download', 'size' => 16]) تصدير CSV
    </a>
@endsection

@section('content')
    @php
        $trendMax = max(1, max($trend['users']));
    @endphp

    {{-- ------------------------------------------------------------ تابات --}}
    <nav class="ap-tabs">
        <a href="{{ admin_route('admin.stats.index') }}" class="ap-tab">النظرة العامة</a>
        <a href="{{ admin_route('admin.stats.properties') }}" class="ap-tab">إحصائيات العقارات</a>
        <a href="{{ admin_route('admin.stats.users') }}" class="ap-tab is-active">إحصائيات المستخدمين</a>
    </nav>

    {{-- ------------------------------------------------------- مؤشرات KPI --}}
    <div class="ap-grid ap-grid--6" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي المستخدمين</div>
                <div class="ap-stat__value">{{ number_format($overview['users_total']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">نشط</div>
                <div class="ap-stat__value">{{ number_format($overview['users_active']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المسؤولون</div>
                <div class="ap-stat__value">{{ number_format($overview['users_admins']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'layout', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المدراء</div>
                <div class="ap-stat__value">{{ number_format($overview['users_managers']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">السماسرة</div>
                <div class="ap-stat__value">{{ number_format($overview['users_agents']) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'plus', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">جدد هذا الشهر</div>
                <div class="ap-stat__value">{{ number_format($userComparison['this_month']) }}</div>
                <div class="ap-stat__hint">
                    الشهر الماضي {{ number_format($userComparison['last_month']) }}
                    @if (($userComparison['change'] ?? 0) != 0)
                        <span class="ap-stat__trend ap-stat__trend--{{ $userComparison['change'] > 0 ? 'up' : 'down' }}">
                            ({{ $userComparison['change'] > 0 ? '+' : '' }}{{ $userComparison['change'] }}%)
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------- المستخدمون يومياً --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <div>
                <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 18]) المستخدمون الجدد يومياً</h2>
                <p class="ap-card__subtitle">عدد الحسابات المسجّلة خلال آخر 30 يوماً</p>
            </div>
            <span class="ap-badge ap-badge--secondary">مستخدمون</span>
        </div>
        <div class="ap-card__body">
            <div class="ap-bars">
                @foreach ($trend['labels'] as $index => $label)
                    <div class="ap-bars__item" title="{{ $label }} — {{ $trend['users'][$index] }} مستخدم">
                        <span class="ap-bars__value">{{ $trend['users'][$index] ?: '' }}</span>
                        <div class="ap-bars__bar" style="height: {{ max(3, (int) round(($trend['users'][$index] / $trendMax) * 100)) }}%;"></div>
                        <span class="ap-bars__label">{{ $index % 5 === 0 ? $label : '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------------ توزيع الفريق --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) توزيع الفريق
                <span class="ap-badge">{{ number_format($breakdown->count()) }}</span>
            </h2>
            <a href="{{ admin_route('admin.users.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إدارة المستخدمين</a>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>المستخدم</th>
                            <th>الأدوار</th>
                            <th>المسؤول المباشر</th>
                            <th>عدد المرؤوسين</th>
                            <th>عدد العقارات</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($breakdown as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $member->avatar_url }}" alt="{{ $member->name }}" class="ap-avatar ap-avatar--sm">
                                        <div>
                                            <span class="fw-bold">{{ $member->name }}</span>
                                            <div class="text-muted" dir="ltr" style="font-size: 11px;">{{ $member->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $member->roleLabels() }}</td>
                                <td>{{ $member->manager?->name ?? '—' }}</td>
                                <td>{{ number_format($member->subordinates_count) }}</td>
                                <td class="fw-bold">{{ number_format($member->properties_count) }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($member->status) }}">
                                        {{ status_label($member->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'users', 'size' => 42])
                                        <p>لا يوجد مستخدمون بعد.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------- أفضل السماسرة --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 18]) أفضل السماسرة</h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>السمسار</th>
                            <th>البريد الإلكتروني</th>
                            <th>عدد العقارات</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topAgents as $index => $agent)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $agent->avatar_url }}" alt="{{ $agent->name }}" class="ap-avatar ap-avatar--sm">
                                        <span class="fw-bold">{{ $agent->name }}</span>
                                    </div>
                                </td>
                                <td dir="ltr">{{ $agent->email }}</td>
                                <td class="fw-bold">{{ number_format($agent->properties_count) }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($agent->status) }}">
                                        {{ status_label($agent->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'star', 'size' => 42])
                                        <p>لا يوجد سماسرة بعد.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
