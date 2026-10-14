@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $user = $user ?? auth()->user();
    $isPending = $user?->status === 'pending';
    $currentStatus = request('status');
    $currentQuery = request('q');
@endphp

@section('title', 'عقاراتي المسجلة والمتابعة')

@section('content')
<style>
    /* ==========================================================================
       Darli Unified Dashboard - Properties Management
       ========================================================================== */
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

    /* Page Hero Banner */
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
        margin-bottom: 12px;
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

    .darli-dash-header-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 20px;
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

    .darli-btn-add {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--dash-primary);
        color: #ffffff !important;
        font-weight: 700;
        font-size: 14px;
        padding: 12px 24px;
        border-radius: 10px;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(219, 29, 35, 0.25);
        transition: all 0.2s ease;
        border: none;
    }
    .darli-btn-add:hover {
        background: var(--dash-primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(219, 29, 35, 0.35);
    }

    /* Sub-Navigation Tabs */
    .darli-dash-nav-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px;
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 12px;
        margin-bottom: 25px;
        overflow-x: auto;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .darli-dash-nav-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--dash-muted);
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .darli-dash-nav-link:hover {
        background: var(--dash-hover);
        color: var(--dash-text);
    }
    .darli-dash-nav-link.active {
        background: #111e33;
        color: #ffffff;
    }
    html[data-theme="dark"] .darli-dash-nav-link.active {
        background: #1e293b;
        color: #ffffff;
    }
    .darli-dash-nav-link.highlight {
        background: rgba(219, 29, 35, 0.08);
        color: var(--dash-primary);
    }
    .darli-dash-nav-link.highlight:hover {
        background: rgba(219, 29, 35, 0.16);
    }

    /* KPI Summary Cards */
    .darli-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 25px;
    }
    .darli-stat-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .darli-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
    }
    .darli-stat-info .label {
        font-size: 13px;
        color: var(--dash-muted);
        margin-bottom: 4px;
        font-weight: 600;
    }
    .darli-stat-info .value {
        font-size: 24px;
        font-weight: 800;
        color: var(--dash-text);
        line-height: 1.2;
    }
    .darli-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .darli-stat-icon.all {
        background: rgba(100, 116, 139, 0.12);
        color: #64748b;
    }
    .darli-stat-icon.published {
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
    }
    .darli-stat-icon.sold {
        background: rgba(13, 148, 136, 0.12);
        color: #0d9488;
    }
    .darli-stat-icon.pending {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }
    .darli-stat-icon.views {
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
    }

    /* Filter & Search Bar */
    .darli-filter-box {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02);
    }
    .darli-status-pills {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .darli-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid var(--dash-border);
        background: var(--dash-hover);
        color: var(--dash-muted);
    }
    .darli-status-pill:hover {
        background: var(--dash-border);
        color: var(--dash-text);
    }
    .darli-status-pill.active {
        background: #111e33;
        color: #ffffff;
        border-color: #111e33;
    }
    html[data-theme="dark"] .darli-status-pill.active {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    .darli-status-pill.active-published {
        background: #059669;
        color: #ffffff;
        border-color: #059669;
    }
    .darli-status-pill.active-sold {
        background: #0d9488;
        color: #ffffff;
        border-color: #0d9488;
    }
    .darli-status-pill.active-pending {
        background: #d97706;
        color: #ffffff;
        border-color: #d97706;
    }

    .darli-search-form {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 280px;
    }
    .darli-search-input-wrap {
        position: relative;
        flex-grow: 1;
    }
    .darli-search-input-wrap input {
        width: 100%;
        padding: 8px 36px 8px 14px;
        border-radius: 10px;
        border: 1px solid var(--dash-border);
        font-size: 13.5px;
        background: var(--dash-hover);
        color: var(--dash-text);
        transition: all 0.2s ease;
    }
    .darli-search-input-wrap input:focus {
        background: var(--dash-card);
        border-color: var(--dash-primary);
        outline: none;
        box-shadow: 0 0 0 3px rgba(219, 29, 35, 0.1);
    }
    .darli-search-input-wrap .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--dash-muted);
        pointer-events: none;
    }

    /* Table Container */
    .darli-dash-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .darli-table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    .darli-table th {
        background: var(--dash-hover);
        color: var(--dash-muted);
        font-weight: 700;
        font-size: 13px;
        padding: 16px 20px;
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
    .darli-table tbody tr:hover td {
        background: var(--dash-hover);
    }
    .darli-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Property Thumbnail & Info */
    .darli-prop-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .darli-prop-thumb {
        width: 76px;
        height: 58px;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
        background: var(--dash-border);
        position: relative;
    }
    .darli-prop-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .darli-prop-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .darli-prop-title-link {
        font-weight: 700;
        color: var(--dash-text);
        text-decoration: none;
        line-height: 1.35;
        transition: color 0.2s ease;
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .darli-prop-title-link:hover {
        color: var(--dash-primary);
    }
    .darli-prop-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12px;
        color: var(--dash-muted);
    }
    .darli-prop-ref {
        display: inline-block;
        padding: 2px 7px;
        background: var(--dash-hover);
        border-radius: 6px;
        font-family: monospace;
        font-size: 11.5px;
        font-weight: 700;
        color: var(--dash-text);
        border: 1px solid var(--dash-border);
    }
    .darli-prop-loc {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Price Pill */
    .darli-price-val {
        font-weight: 800;
        font-size: 15px;
        color: var(--dash-text);
    }
    .darli-price-contact {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--dash-muted);
        background: var(--dash-hover);
        padding: 3px 8px;
        border-radius: 6px;
    }

    /* Views Badge */
    .darli-views-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-weight: 700;
        font-size: 13px;
        color: #2563eb;
        background: rgba(37, 99, 235, 0.1);
        padding: 4px 10px;
        border-radius: 8px;
    }

    /* Status Badges */
    .darli-badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .darli-badge-status.published {
        background: rgba(16, 185, 129, 0.15);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .darli-badge-status.sold {
        background: rgba(13, 148, 136, 0.15);
        color: #0d9488;
        border: 1px solid rgba(13, 148, 136, 0.3);
    }
    .darli-badge-status.pending {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }
    .darli-badge-status.rejected {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }
    .darli-badge-status.other {
        background: var(--dash-hover);
        color: var(--dash-muted);
        border: 1px solid var(--dash-border);
    }

    /* Actions */
    .darli-actions-cell {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
    }
    .darli-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: 1px solid transparent;
        cursor: pointer;
    }
    .darli-action-btn.view {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border-color: rgba(16, 185, 129, 0.3);
    }
    .darli-action-btn.view:hover {
        background: #10b981;
        color: #ffffff;
    }
    .darli-action-btn.sold-btn {
        background: rgba(13, 148, 136, 0.1);
        color: #0d9488;
        border-color: rgba(13, 148, 136, 0.3);
    }
    .darli-action-btn.sold-btn:hover {
        background: #0d9488;
        color: #ffffff;
    }
    .darli-action-btn.reactive {
        background: var(--dash-hover);
        color: var(--dash-text);
        border-color: var(--dash-border);
    }
    .darli-action-btn.reactive:hover {
        background: var(--dash-border);
    }
    .darli-action-btn.submit {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
        border-color: rgba(37, 99, 235, 0.3);
    }
    .darli-action-btn.submit:hover {
        background: #2563eb;
        color: #ffffff;
    }

    /* Empty State */
    .darli-empty-box {
        text-align: center;
        padding: 60px 20px;
    }
    .darli-empty-icon {
        width: 80px;
        height: 80px;
        background: var(--dash-hover);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--dash-muted);
        margin-bottom: 20px;
        border: 2px dashed var(--dash-border);
    }
    .darli-empty-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--dash-text);
        margin-bottom: 8px;
    }
    .darli-empty-desc {
        color: var(--dash-muted);
        font-size: 14px;
        max-width: 420px;
        margin: 0 auto 24px auto;
        line-height: 1.6;
    }

    @media (max-width: 768px) {
        .darli-dash-title {
            font-size: 22px;
        }
        .darli-dash-header-row {
            flex-direction: column;
            align-items: stretch;
        }
        .darli-btn-add {
            justify-content: center;
        }
        .darli-filter-box {
            flex-direction: column;
            align-items: stretch;
        }
        .darli-search-form {
            width: 100%;
        }
    }
</style>

<div class="darli-dash-wrapper">

    {{-- 1. Hero Header Banner --}}
    <div class="darli-dash-hero">
        <div class="container">
            <div class="darli-dash-breadcrumb" dir="rtl">
                <a href="/">الرئيسية</a>
                <span class="sep">›</span>
                <a href="{{ route('dashboard.index') }}">لوحة التحكم</a>
                <span class="sep">›</span>
                <span>عقاراتي</span>
            </div>

            <div class="darli-dash-header-row" dir="rtl">
                <div>
                    <div class="darli-dash-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        @if ($user->isCompany()) لوحة تحكم الشركة @elseif($user->isBroker()) لوحة تحكم السمسار @else لوحة تحكم المعلن @endif
                    </div>
                    <h1 class="darli-dash-title">عقاراتي المسجلة والمتابعة</h1>
                    <p class="darli-dash-subtitle">متابعة تفاصيل العقارات، عدد الزيارات والمشاهدات، وتحديث حالات البيع والعرض لكل وحدة</p>
                </div>

                <div>
                    @if ($isPending)
                        <button class="btn btn-secondary disabled" style="opacity: 0.7; border-radius: 10px; font-weight: 600; padding: 12px 20px;">
                            ⏳ حسابك بانتظار الاعتماد للإضافة
                        </button>
                    @else
                        <a href="{{ route('dashboard.properties.create') }}" class="darli-btn-add">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            إضافة عقار جديد
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4 shadow-sm" role="alert"
                style="border-radius: 12px; background: #ecfdf5; border-color: #a7f3d0; color: #065f46; padding: 14px 18px;" dir="rtl">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-2" style="margin-left: 10px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <div><strong>تم بنجاح:</strong> {{ session('success') }}</div>
            </div>
        @endif

        @if (session('status_warning') || $isPending)
            <div class="alert alert-warning mb-4 shadow-sm" role="alert"
                style="border-radius: 12px; background: #FFFBEB; border-color: #FDE68A; color: #92400E; padding: 16px 20px;" dir="rtl">
                <div class="d-flex align-items-start gap-3">
                    <span style="font-size: 22px; line-height: 1;">⏳</span>
                    <div>
                        <strong>حسابك قيد المراجعة والاعتماد:</strong> مرحباً بك في دارلي! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة للتحقق من البيانات. بمجرد تفعيل الحساب ستتمكن من إضافة ونشر العقارات مباشرة.
                    </div>
                </div>
            </div>
        @endif

        {{-- 2. Sub-Navigation Tabs --}}
        <div class="darli-dash-nav-bar" dir="rtl">
            <a href="{{ route('dashboard.index') }}" class="darli-dash-nav-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                نظرة عامة
            </a>
            <a href="{{ route('dashboard.properties.index') }}" class="darli-dash-nav-link active">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                عقاراتي ({{ $counts['all'] }})
            </a>
            @if ($user->isCompany())
                <a href="{{ route('dashboard.projects.index') }}" class="darli-dash-nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    المشاريع
                </a>
            @endif
            @if (! $isPending)
                <a href="{{ route('dashboard.properties.create') }}" class="darli-dash-nav-link highlight">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    إضافة عقار جديد
                </a>
            @endif
        </div>

        {{-- 3. Summary Stats Cards --}}
        <div class="darli-stats-grid" dir="rtl">
            <div class="darli-stat-card">
                <div class="darli-stat-info">
                    <div class="label">إجمالي العقارات</div>
                    <div class="value">{{ number_format($counts['all']) }}</div>
                </div>
                <div class="darli-stat-icon all">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
                </div>
            </div>

            <div class="darli-stat-card">
                <div class="darli-stat-info">
                    <div class="label">معروضة بالموقع</div>
                    <div class="value" style="color: #10b981;">{{ number_format($counts['published']) }}</div>
                </div>
                <div class="darli-stat-icon published">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
            </div>

            <div class="darli-stat-card">
                <div class="darli-stat-info">
                    <div class="label">تم البيع</div>
                    <div class="value" style="color: #0d9488;">{{ number_format($counts['sold']) }}</div>
                </div>
                <div class="darli-stat-icon sold">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>
                </div>
            </div>

            <div class="darli-stat-card">
                <div class="darli-stat-info">
                    <div class="label">قيد المراجعة</div>
                    <div class="value" style="color: #f59e0b;">{{ number_format($counts['pending_review']) }}</div>
                </div>
                <div class="darli-stat-icon pending">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>

            <div class="darli-stat-card">
                <div class="darli-stat-info">
                    <div class="label">إجمالي المشاهدات</div>
                    <div class="value" style="color: #2563eb;">{{ number_format($counts['views']) }}</div>
                </div>
                <div class="darli-stat-icon views">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                </div>
            </div>
        </div>

        {{-- 4. Filters & Search Bar --}}
        <div class="darli-filter-box" dir="rtl">
            <div class="darli-status-pills">
                <a href="{{ route('dashboard.properties.index', array_filter(['q' => $currentQuery])) }}"
                   class="darli-status-pill {{ ! $currentStatus ? 'active' : '' }}">
                    الكل ({{ $counts['all'] }})
                </a>
                <a href="{{ route('dashboard.properties.index', array_filter(['status' => 'published', 'q' => $currentQuery])) }}"
                   class="darli-status-pill {{ $currentStatus === 'published' ? 'active-published' : '' }}">
                    ✓ معروضة بالموقع ({{ $counts['published'] }})
                </a>
                <a href="{{ route('dashboard.properties.index', array_filter(['status' => 'sold', 'q' => $currentQuery])) }}"
                   class="darli-status-pill {{ $currentStatus === 'sold' ? 'active-sold' : '' }}">
                    🤝 تم البيع ({{ $counts['sold'] }})
                </a>
                <a href="{{ route('dashboard.properties.index', array_filter(['status' => 'pending_review', 'q' => $currentQuery])) }}"
                   class="darli-status-pill {{ $currentStatus === 'pending_review' ? 'active-pending' : '' }}">
                    ⏳ قيد المراجعة ({{ $counts['pending_review'] }})
                </a>
            </div>

            <form method="GET" action="{{ route('dashboard.properties.index') }}" class="darli-search-form">
                @if ($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="darli-search-input-wrap">
                    <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="q" value="{{ $currentQuery }}" placeholder="بحث بالاسم أو كود العقار...">
                </div>
                @if ($currentQuery)
                    <a href="{{ route('dashboard.properties.index', array_filter(['status' => $currentStatus])) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px;">إلغاء</a>
                @endif
            </form>
        </div>

        {{-- 5. Properties Table Card --}}
        <div class="darli-dash-card" dir="rtl">
            <div class="table-responsive">
                <table class="darli-table">
                    <thead>
                        <tr>
                            <th style="width: 38%;">العقار</th>
                            <th style="width: 15%;">السعر</th>
                            <th style="width: 12%;">الزيارات والمشاهدات</th>
                            <th style="width: 15%;">حالة العرض</th>
                            <th style="width: 20%; text-align: left;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $p)
                            @php
                                $st = $p->status?->value ?? (string) $p->status;
                                $translation = $p->translation();
                                $slug = $translation?->slug ?? $p->id;
                                $thumb = $p->primaryImageUrl('ar') ?: '/themes/homzen/images/darli-placeholder.png';
                            @endphp
                            <tr>
                                {{-- Property Info --}}
                                <td>
                                    <div class="darli-prop-cell">
                                        <div class="darli-prop-thumb">
                                            <img src="{{ $thumb }}" alt="{{ $translation?->name }}" onerror="this.src='/themes/homzen/images/darli-placeholder.png'">
                                        </div>
                                        <div class="darli-prop-info">
                                            @if ($st === 'published')
                                                <a href="/properties/{{ $slug }}" target="_blank" class="darli-prop-title-link">
                                                    {{ $translation?->name ?: 'عقار بدون اسم' }}
                                                </a>
                                            @else
                                                <span class="darli-prop-title-link" style="color: #334155;">
                                                    {{ $translation?->name ?: 'عقار بدون اسم' }}
                                                </span>
                                            @endif
                                            <div class="darli-prop-meta">
                                                <span class="darli-prop-ref">{{ $p->reference_code }}</span>
                                                @if ($p->location?->translation()?->name)
                                                    <span class="darli-prop-loc">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                                        {{ $p->location->translation()->name }}
                                                    </span>
                                                @endif
                                                @if ($p->category?->name)
                                                    <span>• {{ $p->category->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Price --}}
                                <td>
                                    @if ($p->isPriceOnContact())
                                        <span class="darli-price-contact">عند التواصل</span>
                                    @else
                                        <span class="darli-price-val">{{ money_eg($p->price) }}</span>
                                    @endif
                                </td>

                                {{-- Views --}}
                                <td>
                                    <span class="darli-views-badge">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        {{ number_format($p->views_count) }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    @if ($st === 'published')
                                        <span class="darli-badge-status published">
                                            ✓ معروض ونشط
                                        </span>
                                    @elseif ($st === 'sold')
                                        <span class="darli-badge-status sold">
                                            🤝 تم البيع
                                        </span>
                                    @elseif ($st === 'pending_review')
                                        <span class="darli-badge-status pending">
                                            ⏳ قيد المراجعة
                                        </span>
                                    @elseif ($st === 'rejected')
                                        <span class="darli-badge-status rejected">
                                            ✕ مرفوض
                                        </span>
                                    @else
                                        <span class="darli-badge-status other">
                                            {{ $st }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="darli-actions-cell">
                                        @if ($st === 'published')
                                            <a href="/properties/{{ $slug }}" target="_blank" class="darli-action-btn view" title="معاينة العقار بالموقع">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                معاينة
                                            </a>
                                            <form method="POST" action="{{ route('dashboard.properties.sold', ['property' => $p]) }}" style="display:inline;" onsubmit="return confirm('هل تريد تحديد هذا العقار كـ «تم البيع»؟');">
                                                @csrf
                                                <button type="submit" class="darli-action-btn sold-btn" title="تحديد كـ تم البيع">
                                                    تم البيع ✓
                                                </button>
                                            </form>
                                        @elseif ($st === 'sold')
                                            <form method="POST" action="{{ route('dashboard.properties.available', ['property' => $p]) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="darli-action-btn reactive" title="إعادة تفعيل العرض بالموقع">
                                                    إعادة العرض
                                                </button>
                                            </form>
                                        @elseif ($st === 'draft' || $st === 'rejected')
                                            <form method="POST" action="{{ route('dashboard.properties.submit', ['property' => $p]) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="darli-action-btn submit" title="إرسال العقار لمراجعة وتأكيد الإدارة">
                                                    إرسال للمراجعة
                                                </button>
                                            </form>
                                        @elseif ($st === 'pending_review')
                                            <span style="font-size: 12px; color: var(--dash-muted);">بانتظار الموافقة</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="darli-empty-box">
                                        <div class="darli-empty-icon">
                                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                        </div>
                                        <h3 class="darli-empty-title">لا توجد عقارات مسجلة هنا بعد</h3>
                                        <p class="darli-empty-desc">
                                            @if ($currentQuery || $currentStatus)
                                                لم يتم العثور على أي عقار يطابق الفلتر أو البحث الحالي. جرّب مسح الفلتر أو البحث بكلمات أخرى.
                                            @else
                                                لم تقم بإضافة أي عقار حتى الآن. أضف عقاراتك الآن لتصل إلى آلاف الباحثين عن عقارات ومنازل للبيع والإيجار في مصر عبر منصة دارلي.
                                            @endif
                                        </p>
                                        @if (! $isPending)
                                            <a href="{{ route('dashboard.properties.create') }}" class="darli-btn-add" style="display: inline-flex;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                                إضافة أول عقار الآن
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($properties->hasPages())
                <div class="p-3 border-top d-flex justify-content-center" style="background: var(--dash-hover);">
                    {{ $properties->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
