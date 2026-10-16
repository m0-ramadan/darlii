<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
?>

<?php $__env->startSection('title', 'لوحة التحكم الشريكة'); ?>

<?php $__env->startSection('content'); ?>
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

    .darli-dash-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .darli-user-profile-header {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .darli-user-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 14px rgba(0,0,0,0.1);
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

    .darli-btn-gold {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #111e33;
        color: #f8e3a6 !important;
        font-weight: 700;
        font-size: 14px;
        padding: 12px 22px;
        border-radius: 10px;
        text-decoration: none;
        border: 1px solid rgba(200, 164, 92, 0.5);
        transition: all 0.2s ease;
    }
    .darli-btn-gold:hover {
        background: #1e293b;
        transform: translateY(-1px);
    }

    .darli-btn-disabled {
        opacity: 0.65;
        cursor: not-allowed;
        background: #94a3b8;
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
        color: #ffffff !important;
    }
    html[data-theme="dark"] .darli-dash-nav-item.active {
        background: #db1d23;
    }

    /* KPI Cards */
    .darli-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 18px;
        margin-bottom: 30px;
    }

    .darli-stat-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        text-decoration: none;
        display: block;
        color: inherit;
    }
    .darli-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }

    .darli-stat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }
    .darli-stat-label {
        font-size: 13.5px;
        font-weight: 600;
        color: var(--dash-muted);
    }
    .darli-stat-icon-wrap {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .darli-stat-value {
        font-size: 30px;
        font-weight: 800;
        color: var(--dash-text);
        line-height: 1.1;
        margin-bottom: 6px;
    }
    .darli-stat-subtext {
        font-size: 12px;
        color: var(--dash-muted);
    }

    /* Recent Table Card */
    .darli-table-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        margin-bottom: 30px;
    }

    .darli-table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--dash-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .darli-table-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--dash-text);
        margin: 0;
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
    .darli-badge-status.sold {
        background: #ccfbf1;
        color: #0f766e;
        border: 1px solid #99f6e4;
    }
    .darli-badge-status.pending_review {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .darli-badge-status.rejected {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
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
        border: 1px solid transparent;
        transition: all 0.2s ease;
        background: transparent;
        cursor: pointer;
    }
    .darli-action-btn.view {
        background: var(--dash-hover);
        color: var(--dash-text);
        border-color: var(--dash-border);
    }
    .darli-action-btn.view:hover {
        color: var(--dash-primary);
        border-color: var(--dash-primary);
    }
    .darli-action-btn.sold-btn {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }
    .darli-action-btn.sold-btn:hover {
        background: #d1fae5;
    }
    .darli-action-btn.reactive {
        background: #f1f5f9;
        color: #475569;
        border-color: #cbd5e1;
    }
    .darli-action-btn.reactive:hover {
        background: #e2e8f0;
    }
    .darli-action-btn.submit {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .darli-action-btn.submit:hover {
        background: #dbeafe;
    }

    .darli-alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 14px;
        padding: 18px 22px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 16px;
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
    
    <div class="darli-dash-hero">
        <div class="container">
            <div class="darli-dash-breadcrumb">
                <a href="<?php echo e(route('home')); ?>">الرئيسية</a>
                <span class="sep">›</span>
                <span style="color: var(--dash-text); font-weight: 700;">لوحة التحكم</span>
                <span class="sep">›</span>
                <span>نظرة عامة</span>
            </div>

            <div class="darli-dash-header-row">
                <div class="darli-user-profile-header">
                    <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" class="darli-user-avatar">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <h1 class="darli-dash-title mb-0"><?php echo e($user->name); ?></h1>
                            <?php if($isCompany): ?>
                                <span class="badge" style="background: rgba(196, 154, 69, 0.2); color: #c8a45c; border: 1px solid rgba(200, 164, 92, 0.4); font-size: 12px; padding: 5px 12px; border-radius: 20px;">
                                    🏢 <?php echo e($user->agency?->name ?: 'شركة عقارية'); ?>

                                </span>
                            <?php elseif($isBroker): ?>
                                <span class="badge" style="background: rgba(14, 165, 233, 0.15); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.35); font-size: 12px; padding: 5px 12px; border-radius: 20px;">
                                    🤝 سمسار / وسيط عقاري
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background: rgba(100, 116, 139, 0.15); color: var(--dash-muted); font-size: 12px; padding: 5px 12px; border-radius: 20px;">
                                    👤 عميل دارلي
                                </span>
                            <?php endif; ?>

                            <?php if($isPending): ?>
                                <span class="badge" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 12px; padding: 5px 12px; border-radius: 20px;">
                                    ⏳ قيد المراجعة والاعتماد
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 12px; padding: 5px 12px; border-radius: 20px;">
                                    ✓ حساب مفعّل ونشط
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="darli-dash-subtitle">
                            متابعة أداء العقارات والزيارات وحالة المبيعات الخاصة بك على منصة دارلي
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if($isPending): ?>
                        <span class="darli-btn-add darli-btn-disabled" title="لا يمكن الإضافة حتى يتم تفعيل حسابك من قبل الإدارة">
                            + إضافة عقار (بانتظار التفعيل)
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e(route('dashboard.properties.create')); ?>" class="darli-btn-add">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            إضافة عقار جديد
                        </a>
                    <?php endif; ?>

                    <?php if($isCompany): ?>
                        <?php if($isPending): ?>
                            <span class="darli-btn-gold darli-btn-disabled">
                                + إضافة مشروع (معلق)
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e(route('dashboard.projects.create')); ?>" class="darli-btn-gold">
                                🏗️ إضافة مشروع جديد
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="container">

        
        <?php if(session('success')): ?>
            <div class="darli-alert-success">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <div><strong>تم بنجاح:</strong> <?php echo e(session('success')); ?></div>
            </div>
        <?php endif; ?>

        
        <?php if(session('status_warning') || $isPending): ?>
            <div class="darli-alert-warning">
                <div style="font-size: 28px; line-height: 1;">⏳</div>
                <div>
                    <h5 style="color: #b45309; font-size: 16px; font-weight: 800; margin: 0 0 6px 0;">
                        حسابك قيد المراجعة والاعتماد
                    </h5>
                    <p style="margin: 0; font-size: 14px; line-height: 1.6;">
                        <?php echo e(session('status_warning') ?? 'مرحباً بك في منصة دارلي! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة للتحقق من البيانات والمستندات. بمجرد تفعيل الحساب ستتمكن من إضافة ونشر العقارات والمشاريع الخاصة بك مباشرة.'); ?>

                    </p>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="darli-dash-nav-bar">
            <a href="<?php echo e(route('dashboard.index')); ?>" class="darli-dash-nav-item active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                نظرة عامة
            </a>
            <a href="<?php echo e(route('dashboard.properties.index')); ?>" class="darli-dash-nav-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                عقاراتي (<?php echo e($totalProperties); ?>)
            </a>
            <?php if($isCompany): ?>
                <a href="<?php echo e(route('dashboard.projects.index')); ?>" class="darli-dash-nav-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    مشاريع الشركة (<?php echo e($totalProjects); ?>)
                </a>
            <?php endif; ?>
            <?php if(! $isPending): ?>
                <a href="<?php echo e(route('dashboard.properties.create')); ?>" class="darli-dash-nav-item" style="color: var(--dash-primary); margin-inline-start: auto;">
                    + إضافة عقار جديد
                </a>
            <?php endif; ?>
        </div>

        
        <div class="darli-stat-grid">
            <!-- Total Properties -->
            <a href="<?php echo e(route('dashboard.properties.index')); ?>" class="darli-stat-card">
                <div class="darli-stat-header">
                    <span class="darli-stat-label">إجمالي العقارات</span>
                    <div class="darli-stat-icon-wrap" style="background: rgba(37, 99, 235, 0.1); color: #2563eb;">🏠</div>
                </div>
                <div class="darli-stat-value"><?php echo e(number_format($totalProperties)); ?></div>
                <div class="darli-stat-subtext">عرض كافة العقارات المسجلة ←</div>
            </a>

            <!-- Active / Published -->
            <a href="<?php echo e(route('dashboard.properties.index', ['status' => 'published'])); ?>" class="darli-stat-card">
                <div class="darli-stat-header">
                    <span class="darli-stat-label">معروضة بالموقع</span>
                    <div class="darli-stat-icon-wrap" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">🟢</div>
                </div>
                <div class="darli-stat-value" style="color: #10b981;"><?php echo e(number_format($activeProperties)); ?></div>
                <div class="darli-stat-subtext">العروض النشطة والمباشرة ←</div>
            </a>

            <!-- Sold -->
            <a href="<?php echo e(route('dashboard.properties.index', ['status' => 'sold'])); ?>" class="darli-stat-card">
                <div class="darli-stat-header">
                    <span class="darli-stat-label">عقارات تم بيعها</span>
                    <div class="darli-stat-icon-wrap" style="background: rgba(13, 148, 136, 0.1); color: #0d9488;">🤝</div>
                </div>
                <div class="darli-stat-value" style="color: #0d9488;"><?php echo e(number_format($soldProperties)); ?></div>
                <div class="darli-stat-subtext">المبيعات المنفذة عبر المنصة ←</div>
            </a>

            <!-- Total Views -->
            <div class="darli-stat-card">
                <div class="darli-stat-header">
                    <span class="darli-stat-label">إجمالي الزيارات</span>
                    <div class="darli-stat-icon-wrap" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">👁️</div>
                </div>
                <div class="darli-stat-value" style="color: #2563eb;"><?php echo e(number_format($totalViews)); ?></div>
                <div class="darli-stat-subtext">مشاهدات حقيقية على عقاراتك</div>
            </div>

            <!-- Company Projects (If Company) -->
            <?php if($isCompany): ?>
                <a href="<?php echo e(route('dashboard.projects.index')); ?>" class="darli-stat-card">
                    <div class="darli-stat-header">
                        <span class="darli-stat-label">مشاريع الشركة</span>
                        <div class="darli-stat-icon-wrap" style="background: rgba(200, 164, 92, 0.15); color: #c8a45c;">🏗️</div>
                    </div>
                    <div class="darli-stat-value" style="color: #c8a45c;"><?php echo e(number_format($totalProjects)); ?></div>
                    <div class="darli-stat-subtext">إدارة المشروعات والوحدات ←</div>
                </a>
            <?php endif; ?>
        </div>

        
        <div class="darli-table-card">
            <div class="darli-table-header">
                <div>
                    <h3 class="darli-table-title">آخر العقارات المضافة وتفاصيل الزيارات</h3>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--dash-muted);">
                        متابعة حركة المشاهدات والحالة لكل عقار مضاف
                    </p>
                </div>
                <a href="<?php echo e(route('dashboard.properties.index')); ?>" class="darli-action-btn view" style="padding: 8px 16px; font-size: 13px;">
                    عرض كل العقارات (<?php echo e($totalProperties); ?>) ←
                </a>
            </div>

            <div class="darli-table-responsive">
                <table class="darli-table">
                    <thead>
                        <tr>
                            <th>كود العقار</th>
                            <th>اسم العقار والموقع</th>
                            <th>السعر</th>
                            <th>المشاهدات</th>
                            <th>الحالة</th>
                            <th style="text-align: left;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentProperties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $st = $p->status?->value ?? (string) $p->status;
                            ?>
                            <tr>
                                <td>
                                    <span style="font-family: monospace; background: var(--dash-hover); padding: 4px 8px; border-radius: 6px; font-size: 12.5px; font-weight: 700; color: var(--dash-text);">
                                        <?php echo e($p->reference_code); ?>

                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dash-text); margin-bottom: 3px;">
                                        <?php if($st === 'published'): ?>
                                            <a href="<?php echo e(route('properties.show', ['slug' => $p->translation()?->slug ?? $p->id])); ?>" target="_blank" style="color: inherit; text-decoration: none;">
                                                <?php echo e($p->translation()?->name ?: 'عقار بدون اسم'); ?>

                                            </a>
                                        <?php else: ?>
                                            <?php echo e($p->translation()?->name ?: 'عقار بدون اسم'); ?>

                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 12.5px; color: var(--dash-muted);">
                                        📍 <?php echo e($p->location?->translation()?->name ?? 'موقع غير محدد'); ?>

                                        <?php if($p->category): ?>
                                            • <?php echo e($p->category->translation()?->name); ?>

                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($p->isPriceOnContact()): ?>
                                        <span class="badge" style="background: var(--dash-hover); color: var(--dash-muted); font-size: 12px;">عند التواصل</span>
                                    <?php else: ?>
                                        <span style="font-weight: 800; color: var(--dash-text);">
                                            <?php echo e(money_eg($p->price)); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 800; color: #2563eb; font-size: 13.5px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <?php echo e(number_format($p->views_count)); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($st === 'published'): ?>
                                        <span class="darli-badge-status published">✓ معتمد ومعروض</span>
                                    <?php elseif($st === 'sold'): ?>
                                        <span class="darli-badge-status sold">🤝 تم البيع</span>
                                    <?php elseif($st === 'pending_review'): ?>
                                        <span class="darli-badge-status pending_review">⏳ قيد المراجعة</span>
                                    <?php elseif($st === 'rejected'): ?>
                                        <span class="darli-badge-status rejected">✕ مرفوض</span>
                                    <?php else: ?>
                                        <span class="darli-badge-status" style="background: var(--dash-hover); color: var(--dash-muted);"><?php echo e($st); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: left;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <?php if($st === 'published'): ?>
                                            <a href="<?php echo e(route('properties.show', ['slug' => $p->translation()?->slug ?? $p->id])); ?>"
                                               class="darli-action-btn view" target="_blank" title="معاينة العقار بالواجهة">
                                                معاينة ↗
                                            </a>
                                            <form method="POST" action="<?php echo e(route('dashboard.properties.sold', ['property' => $p])); ?>" style="display:inline;" onsubmit="return confirm('هل تريد تحديد هذا العقار كـ «تم البيع»؟');">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="darli-action-btn sold-btn" title="تحديد كـ تم البيع">
                                                    تم البيع ✓
                                                </button>
                                            </form>
                                        <?php elseif($st === 'sold'): ?>
                                            <form method="POST" action="<?php echo e(route('dashboard.properties.available', ['property' => $p])); ?>" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="darli-action-btn reactive" title="إعادة العرض بالموقع">
                                                    إعادة العرض
                                                </button>
                                            </form>
                                        <?php elseif($st === 'draft' || $st === 'rejected'): ?>
                                            <form method="POST" action="<?php echo e(route('dashboard.properties.submit', ['property' => $p])); ?>" style="display:inline;">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="darli-action-btn submit" title="إرسال العقار لمراجعة وتأكيد الإدارة">
                                                    إرسال للمراجعة
                                                </button>
                                            </form>
                                        <?php elseif($st === 'pending_review'): ?>
                                            <span style="font-size: 12px; color: var(--dash-muted);">بانتظار الموافقة</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 45px 20px; color: var(--dash-muted);">
                                    <div style="font-size: 32px; margin-bottom: 8px;">🏠</div>
                                    <div style="font-weight: 700; margin-bottom: 4px;">لا توجد عقارات مضافة لديك حتى الآن</div>
                                    <div style="font-size: 13px; margin-bottom: 16px;">أضف أول عقار لك لتتم مراجعته وعرضه لآلاف الزوار على منصة دارلي.</div>
                                    <?php if(! $isPending): ?>
                                        <a href="<?php echo e(route('dashboard.properties.create')); ?>" class="darli-btn-add" style="display: inline-flex;">
                                            + إضافة أول عقار الآن
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/dashboard/index.blade.php ENDPATH**/ ?>