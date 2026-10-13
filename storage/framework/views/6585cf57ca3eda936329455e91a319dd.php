<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
?>

<?php $__env->startSection('title', ($isAr ? 'الوظائف وفرص العمل | دارلي' : 'Careers & Open Positions | Darlii')); ?>

<?php $__env->startSection('content'); ?>
<style>
    /* ==========================================================================
       Darli Careers Page (دليل الوظائف والفرص المتاحة)
       ========================================================================== */

    :root {
        --darli-career-bg: #f8fafc;
        --darli-career-card: #ffffff;
        --darli-career-border: #e2e8f0;
        --darli-career-text: #0f172a;
        --darli-career-muted: #64748b;
        --darli-career-primary: #db1d23;
        --darli-career-primary-soft: rgba(219, 29, 35, 0.08);
        --darli-career-gold: #c8a45c;
        --darli-career-gold-soft: rgba(200, 164, 92, 0.12);
        --darli-career-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
        --darli-career-shadow-hover: 0 12px 28px -4px rgba(15, 23, 42, 0.1);
    }

    html[data-theme="dark"] {
        --darli-career-bg: #0b1320;
        --darli-career-card: #111e33;
        --darli-career-border: #1e293b;
        --darli-career-text: #f8fafc;
        --darli-career-muted: #94a3b8;
        --darli-career-primary-soft: rgba(219, 29, 35, 0.18);
        --darli-career-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.4);
        --darli-career-shadow-hover: 0 12px 28px -4px rgba(0, 0, 0, 0.6);
    }

    /* Hero */
    .darli-career-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        padding: 55px 0 75px 0;
        overflow: hidden;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-theme="dark"] .darli-career-hero {
        background: linear-gradient(180deg, #0c1726 0%, #111e33 100%);
        border-bottom-color: #1e293b;
    }

    .darli-career-hero__content {
        max-width: 820px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .darli-career-hero__tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--darli-career-primary-soft);
        color: var(--darli-career-primary);
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 16px;
        border: 1px solid rgba(219, 29, 35, 0.15);
    }

    .darli-career-hero__title {
        font-size: 38px;
        font-weight: 800;
        color: var(--darli-career-text);
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    @media (max-width: 768px) {
        .darli-career-hero__title { font-size: 28px; }
    }

    .darli-career-hero__desc {
        font-size: 16px;
        line-height: 1.7;
        color: var(--darli-career-muted);
    }

    /* Filter Bar */
    .darli-career-filter-wrap {
        margin-top: -36px;
        position: relative;
        z-index: 10;
        margin-bottom: 40px;
    }

    .darli-career-filter-card {
        background: var(--darli-career-card);
        border: 1px solid var(--darli-career-border);
        border-radius: 18px;
        padding: 16px 20px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
    }

    .darli-career-input,
    .darli-career-select {
        background: #f8fafc;
        border: 1px solid var(--darli-career-border);
        color: var(--darli-career-text);
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        width: 100%;
    }
    html[data-theme="dark"] .darli-career-input,
    html[data-theme="dark"] .darli-career-select {
        background: #0b1320;
        color: #f8fafc;
    }

    /* Career Card */
    .darli-career-card {
        background: var(--darli-career-card);
        border: 1px solid var(--darli-career-border);
        border-radius: 18px;
        padding: 26px 28px;
        margin-bottom: 20px;
        box-shadow: var(--darli-career-shadow);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .darli-career-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--darli-career-shadow-hover);
        border-color: rgba(219, 29, 35, 0.3);
    }

    .darli-career-card__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
    }

    .darli-career-card__title {
        font-size: 20px;
        font-weight: 800;
        color: var(--darli-career-text);
        margin-bottom: 6px;
    }
    .darli-career-card__title a {
        color: inherit;
        text-decoration: none;
    }
    .darli-career-card__title a:hover {
        color: var(--darli-career-primary);
    }

    .darli-career-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .darli-career-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 8px;
        background: var(--darli-career-bg);
        color: var(--darli-career-muted);
        border: 1px solid var(--darli-career-border);
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .darli-career-badge--dept {
        background: var(--darli-career-primary-soft);
        color: var(--darli-career-primary);
        border-color: rgba(219, 29, 35, 0.15);
    }
    .darli-career-badge--salary {
        background: var(--darli-career-gold-soft);
        color: #93712b;
        border-color: rgba(200, 164, 92, 0.25);
    }
    html[data-theme="dark"] .darli-career-badge--salary {
        color: #e5c37c;
    }

    .darli-career-card__desc {
        color: var(--darli-career-muted);
        font-size: 14px;
        line-height: 1.7;
        margin: 0;
    }

    .darli-career-card__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid var(--darli-career-border);
        flex-wrap: wrap;
        gap: 12px;
    }
</style>

<div class="darli-career-page" style="background: var(--darli-career-bg); min-height: 80vh; padding-bottom: 80px;">
    
    <section class="darli-career-hero">
        <div class="container">
            <div class="darli-career-hero__content">
                <div class="darli-career-hero__tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <span><?php echo e($isAr ? 'انضم إلى فريق دارلي' : 'Join Darli Real Estate Team'); ?></span>
                </div>

                <h1 class="darli-career-hero__title">
                    <?php echo e($isAr ? 'الوظائف الشاغرة وفرص العمل' : 'Current Job Openings'); ?>

                </h1>

                <p class="darli-career-hero__desc">
                    <?php echo e($isAr
                        ? 'نبني معاً مستقبل الاستثمار والتطوير العقاري في بني سويف ومصر. اكتشف الفرص المتاحة وكن جزءاً من فريقنا المتميز.'
                        : 'Shape the future of real estate with Darli. Explore open positions and build your career in a dynamic, high-growth environment.'); ?>

                </p>
            </div>
        </div>
    </section>

    
    <div class="container">
        <div class="darli-career-filter-wrap">
            <div class="darli-career-filter-card">
                <form method="get" action="<?php echo e(route('careers.index', ['locale' => $locale])); ?>">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-5">
                            <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="darli-career-input"
                                placeholder="<?php echo e($isAr ? 'ابحث بالمسمى الوظيفي أو الكلمات المفتاحية...' : 'Search by job title or keyword...'); ?>">
                        </div>

                        <div class="col-12 col-md-3">
                            <select name="department" class="darli-career-select">
                                <option value=""><?php echo e($isAr ? 'جميع الأقسام' : 'All Departments'); ?></option>
                                <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dept); ?>" <?php if(request('department') === $dept): echo 'selected'; endif; ?>><?php echo e($dept); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <select name="type" class="darli-career-select">
                                <option value=""><?php echo e($isAr ? 'نوع الدوام' : 'All Types'); ?></option>
                                <option value="دوام كامل" <?php if(request('type') === 'دوام كامل'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'دوام كامل' : 'Full Time'); ?></option>
                                <option value="دوام جزئي" <?php if(request('type') === 'دوام جزئي'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'دوام جزئي' : 'Part Time'); ?></option>
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <button type="submit" class="btn btn-danger w-100 fw-bold py-2" style="border-radius: 12px; background: var(--darli-career-primary); border-color: var(--darli-career-primary);">
                                <?php echo e($isAr ? 'بحث' : 'Search'); ?>

                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: var(--darli-career-text);">
                <?php echo e($isAr ? 'الوظائف المتاحة حالياً' : 'Available Openings'); ?>

                <span class="badge bg-secondary ms-1" style="font-size: 12px;"><?php echo e($careers->total()); ?></span>
            </h5>
            <a href="<?php echo e(route('contact-us.index', ['locale' => $locale])); ?>" class="text-danger text-decoration-none small fw-bold">
                <?php echo e($isAr ? 'تواصل مع إدارة التوظيف ←' : 'Contact HR Desk →'); ?>

            </a>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $careers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $career): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $tr = $career->translation($locale);
                $title = $tr?->title ?: $career->title;
                $slug = $tr?->slug ?: $career->translation('en')?->slug ?: $career->id;
                $detailUrl = route('careers.show', ['locale' => $locale, 'slug' => $slug]);
            ?>
            <div class="darli-career-card">
                <div class="darli-career-card__header">
                    <div>
                        <h3 class="darli-career-card__title">
                            <a href="<?php echo e($detailUrl); ?>"><?php echo e($title); ?></a>
                        </h3>
                        <div class="darli-career-badges">
                            <?php if($career->department): ?>
                                <span class="darli-career-badge darli-career-badge--dept">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                    <?php echo e($career->department); ?>

                                </span>
                            <?php endif; ?>

                            <?php if($career->location): ?>
                                <span class="darli-career-badge">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?php echo e($career->location); ?>

                                </span>
                            <?php endif; ?>

                            <?php if($career->employment_type): ?>
                                <span class="darli-career-badge">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <?php echo e($career->employment_type); ?>

                                </span>
                            <?php endif; ?>

                            <?php if($career->salary_min || $career->salary_max): ?>
                                <span class="darli-career-badge darli-career-badge--salary">
                                    <?php echo e(number_format($career->salary_min)); ?> - <?php echo e(number_format($career->salary_max)); ?> <?php echo e($isAr ? 'ج.م' : 'EGP'); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <a href="<?php echo e($detailUrl); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                        <?php echo e($isAr ? 'التقديم الآن' : 'Apply Now'); ?>

                    </a>
                </div>

                <p class="darli-career-card__desc">
                    <?php echo e($tr?->summary ?: Str::limit(strip_tags($tr?->description ?? ''), 180)); ?>

                </p>

                <div class="darli-career-card__footer">
                    <span class="text-muted small">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                        <?php echo e($isAr ? 'آخر موعد للتقديم: ' . ($career->deadline?->translatedFormat('j F Y') ?: 'متاح حتى اكتمال العدد') : 'Deadline: ' . ($career->deadline?->format('Y-m-d') ?: 'Open until filled')); ?>

                    </span>

                    <a href="<?php echo e($detailUrl); ?>" class="text-danger text-decoration-none fw-bold small d-inline-flex align-items-center gap-1">
                        <span><?php echo e($isAr ? 'عرض متطلبات الوظيفة والشروط' : 'View Requirements & Apply'); ?></span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transform: <?php echo e($isRtl ? 'scaleX(-1)' : 'none'); ?>;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-5 bg-white rounded-4 border p-4">
                <p class="text-muted mb-3"><?php echo e($isAr ? 'لا توجد وظائف شاغرة تطابق خيارات البحث الحالية.' : 'No open positions matching your search.'); ?></p>
                <a href="<?php echo e(route('careers.index', ['locale' => $locale])); ?>" class="btn btn-outline-danger rounded-pill px-4">
                    <?php echo e($isAr ? 'عرض جميع الوظائف' : 'View All Jobs'); ?>

                </a>
            </div>
        <?php endif; ?>

        <?php if($careers->hasPages()): ?>
            <div class="d-flex justify-content-center mt-4">
                <?php echo e($careers->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/careers/index.blade.php ENDPATH**/ ?>