@extends('layouts.public-shell')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isPending = $user->status === 'pending';
@endphp

@section('title', 'مشاريع الشركة العقارية')

@section('content')
<style>
    :root {
        --dash-bg: #f8fafc;
        --dash-card: #ffffff;
        --dash-border: #e2e8f0;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-hover: #f1f5f9;
        --dash-hero: linear-gradient(180deg, #edf3f8 0%, #f8fafc 100%);
        --dash-primary: #db1d23;
        --dash-primary-hover: #b9151b;
        --dash-gold: #c8a45c;
    }

    html[data-theme="dark"] {
        --dash-bg: #0b1320;
        --dash-card: #111e33;
        --dash-border: #1e293b;
        --dash-text: #f8fafc;
        --dash-muted: #94a3b8;
        --dash-hover: #16243d;
        --dash-hero: linear-gradient(180deg, #0e192c 0%, #0b1320 100%);
    }

    .darli-dash-wrapper {
        background-color: var(--dash-bg);
        min-height: 80vh;
        padding-bottom: 80px;
    }

    .darli-dash-hero {
        background: var(--dash-hero);
        border-bottom: 1px solid var(--dash-border);
        padding: 40px 0 25px 0;
        margin-bottom: 30px;
    }

    .darli-dash-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--dash-muted);
        margin-bottom: 14px;
    }
    .darli-dash-breadcrumb a {
        color: var(--dash-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .darli-dash-breadcrumb a:hover {
        color: var(--dash-primary);
    }
    .darli-dash-breadcrumb .sep {
        font-size: 11px;
        opacity: 0.6;
    }

    .darli-dash-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        background: rgba(200, 164, 92, 0.15);
        color: #b38933;
        border: 1px solid rgba(200, 164, 92, 0.3);
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .darli-dash-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--dash-text);
        margin: 0 0 6px 0;
        line-height: 1.3;
    }

    .darli-dash-subtitle {
        color: var(--dash-muted);
        font-size: 14.5px;
        margin: 0;
    }

    .darli-btn-gold {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #111e33;
        color: #f8e3a6 !important;
        font-weight: 700;
        font-size: 14px;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid rgba(200, 164, 92, 0.5);
        box-shadow: 0 4px 14px rgba(17, 30, 51, 0.2);
        transition: all 0.2s ease;
    }
    .darli-btn-gold:hover {
        background: #1e293b;
        transform: translateY(-1px);
    }

    .darli-dash-nav-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px;
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 12px;
        margin-bottom: 30px;
        overflow-x: auto;
    }

    .darli-dash-nav-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        color: var(--dash-muted);
        text-decoration: none;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .darli-dash-nav-item:hover {
        color: var(--dash-text);
        background: var(--dash-hover);
    }
    .darli-dash-nav-item.active {
        background: #111e33;
        color: #f8e3a6 !important;
    }
    html[data-theme="dark"] .darli-dash-nav-item.active {
        background: #1e293b;
    }

    .darli-table-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    }

    .darli-table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .darli-table {
        width: 100%;
        border-collapse: collapse;
        text-align: right;
    }
    .darli-table th {
        background: var(--dash-hover);
        color: var(--dash-muted);
        font-size: 13px;
        font-weight: 700;
        padding: 14px 20px;
        border-bottom: 1px solid var(--dash-border);
        white-space: nowrap;
    }
    .darli-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--dash-border);
        vertical-align: middle;
        font-size: 14px;
        color: var(--dash-text);
    }
    .darli-table tr:hover td {
        background: var(--dash-hover);
    }

    .darli-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .darli-badge-status.published {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .darli-badge-status.draft {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .darli-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid var(--dash-border);
        background: var(--dash-hover);
        color: var(--dash-text);
        transition: all 0.2s ease;
    }
    .darli-action-btn:hover {
        color: var(--dash-primary);
        border-color: var(--dash-primary);
    }

    .darli-alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 14px;
    }

    .darli-alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
</style>

<div class="darli-dash-wrapper">
    {{-- Hero Banner --}}
    <div class="darli-dash-hero">
        <div class="container">
            <div class="darli-dash-breadcrumb">
                <a href="{{ route('home') }}">الرئيسية</a>
                <span class="sep">›</span>
                <a href="{{ route('dashboard.index') }}">لوحة التحكم</a>
                <span class="sep">›</span>
                <span style="color: var(--dash-text); font-weight: 700;">مشاريع الشركة</span>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="darli-dash-badge">
                        🏢 {{ $user->agency?->name ?: 'إدارة مشاريع التطوير العقاري' }}
                    </div>
                    <h1 class="darli-dash-title">مشاريع الشركة العقارية</h1>
                    <p class="darli-dash-subtitle">
                        إدارة مشروعات التطوير العقاري الخاصة بشركتك والوحدات السكنية والتجارية التابعة لها
                    </p>
                </div>

                <div>
                    @if ($isPending)
                        <button class="btn btn-secondary disabled" style="opacity: 0.65; border-radius: 10px; font-weight: 700; padding: 12px 20px;">
                            + إضافة مشروع جديد (بانتظار التفعيل)
                        </button>
                    @else
                        <a href="{{ route('dashboard.projects.create') }}" class="darli-btn-gold">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            إضافة مشروع جديد
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="container">

        @if (session('success'))
            <div class="darli-alert-success">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <div><strong>تم بنجاح:</strong> {{ session('success') }}</div>
            </div>
        @endif

        @if (session('status_warning') || $isPending)
            <div class="darli-alert-warning">
                <span style="font-size: 24px; line-height: 1;">⏳</span>
                <div>
                    <strong>حساب الشركة قيد المراجعة:</strong> حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك نشر مشاريع جديدة حتى يتم تفعيل الحساب.
                </div>
            </div>
        @endif

        {{-- Sub-Navigation Tabs --}}
        <div class="darli-dash-nav-bar">
            <a href="{{ route('dashboard.index') }}" class="darli-dash-nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                نظرة عامة
            </a>
            <a href="{{ route('dashboard.properties.index') }}" class="darli-dash-nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                عقارات الشركة
            </a>
            <a href="{{ route('dashboard.projects.index') }}" class="darli-dash-nav-item active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                مشاريع الشركة ({{ $projects->total() }})
            </a>
            @if (! $isPending)
                <a href="{{ route('dashboard.projects.create') }}" class="darli-dash-nav-item" style="color: var(--dash-gold); margin-inline-start: auto;">
                    + إضافة مشروع جديد
                </a>
            @endif
        </div>

        {{-- Projects Table --}}
        <div class="darli-table-card">
            <div class="darli-table-responsive">
                <table class="darli-table">
                    <thead>
                        <tr>
                            <th>كود المشروع</th>
                            <th>اسم المشروع</th>
                            <th>الموقع</th>
                            <th>العمارات / الوحدات</th>
                            <th>تاريخ التسليم</th>
                            <th>الحالة</th>
                            <th style="text-align: left;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $proj)
                            @php
                                $tr = $proj->translation();
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-family: monospace; background: var(--dash-hover); padding: 4px 8px; border-radius: 6px; font-size: 12.5px; font-weight: 700; color: var(--dash-text);">
                                        {{ $proj->reference_code }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dash-text); margin-bottom: 2px;">
                                        {{ $tr?->name ?: 'مشروع بدون اسم' }}
                                    </div>
                                    @if ($proj->investor_name)
                                        <div style="font-size: 12px; color: var(--dash-muted);">المطور: {{ $proj->investor_name }}</div>
                                    @endif
                                </td>
                                <td>
                                    📍 {{ $proj->location?->translation()?->name ?? '—' }}
                                </td>
                                <td>
                                    <div style="font-weight: 600;">{{ $proj->blocks_count ? $proj->blocks_count . ' عمارة' : '—' }}</div>
                                    <div style="font-size: 12px; color: var(--dash-muted);">{{ $proj->flats_count ? $proj->flats_count . ' شقة/وحدة' : '' }}</div>
                                </td>
                                <td>
                                    {{ $proj->completion_date?->format('Y/m') ?? '—' }}
                                </td>
                                <td>
                                    @if ($proj->status === 'published')
                                        <span class="darli-badge-status published">✓ معتمد ومنشور</span>
                                    @elseif ($proj->status === 'draft')
                                        <span class="darli-badge-status draft">⏳ مسودة / قيد المراجعة</span>
                                    @else
                                        <span class="darli-badge-status" style="background: var(--dash-hover); color: var(--dash-muted);">{{ $proj->status }}</span>
                                    @endif
                                </td>
                                <td style="text-align: left;">
                                    @if ($proj->status === 'published')
                                        <a href="{{ route('projects.show', ['slug' => $tr?->slug ?? $proj->id]) }}"
                                            class="darli-action-btn" target="_blank">
                                            معاينة بالموقع ↗
                                        </a>
                                    @else
                                        <span style="font-size: 12px; color: var(--dash-muted);">بانتظار تفعيل الإدارة</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 45px 20px; color: var(--dash-muted);">
                                    <div style="font-size: 32px; margin-bottom: 8px;">🏗️</div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">لا توجد مشاريع مسجلة لشركتك حتى الآن</div>
                                    <div style="font-size: 13px; margin-bottom: 16px;">أضف أول مشروع تطوير عقاري لشركتك لعرضه بالموقع.</div>
                                    @if (! $isPending)
                                        <a href="{{ route('dashboard.projects.create') }}" class="darli-btn-gold" style="display: inline-flex;">
                                            + إضافة أول مشروع الآن
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($projects->hasPages())
                <div class="p-3 border-top d-flex justify-content-center" style="background: var(--dash-hover);">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
