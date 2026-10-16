<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
?>

<?php $__env->startSection('title', $isAr ? 'الوظائف الشاغرة وفرص العمل | دارلي' : 'Careers & Job Opportunities | Darlii'); ?>

<?php $__env->startSection('content'); ?>
    <style>
        /* ==========================================================================
               Darli Careers Page - Exact Design Match
               ========================================================================== */

        :root {
            --darli-career-bg: #f8fafc;
            --darli-career-card: #ffffff;
            --darli-career-border: #edf2f7;
            --darli-career-navy: #102038;
            --darli-career-gold: #c8963e;
            --darli-career-gold-hover: #b08230;
            --darli-career-tag-gold-bg: #fbf5ea;
            --darli-career-tag-gold-border: #f3e6cf;
            --darli-career-tag-gold-text: #b8862d;
            --darli-career-tag-blue-bg: #f3f7fd;
            --darli-career-tag-blue-border: #e2ecf9;
            --darli-career-tag-blue-text: #597193;
            --darli-career-muted: #64748b;
            --darli-career-shadow: 0 4px 18px -2px rgba(16, 32, 56, 0.04);
            --darli-career-shadow-hover: 0 10px 25px -4px rgba(16, 32, 56, 0.08);
        }

        html[data-theme="dark"] {
            --darli-career-bg: #0b1320;
            --darli-career-card: #111e33;
            --darli-career-border: #1e293b;
            --darli-career-navy: #f8fafc;
            --darli-career-muted: #94a3b8;
            --darli-career-tag-blue-bg: #15243d;
            --darli-career-tag-blue-border: #223759;
            --darli-career-tag-blue-text: #93b2dc;
            --darli-career-tag-gold-bg: #221c12;
            --darli-career-tag-gold-border: #3d321d;
            --darli-career-tag-gold-text: #d9aa55;
            --darli-career-shadow: 0 4px 18px -2px rgba(0, 0, 0, 0.4);
            --darli-career-shadow-hover: 0 10px 25px -4px rgba(0, 0, 0, 0.6);
        }

        .darli-career-page {
            background-color: var(--darli-career-bg);
            min-height: 100vh;
            padding-bottom: 90px;
        }

        /* Hero Section */
        .darli-career-hero {
            position: relative;
            background: linear-gradient(180deg, #edf3f8 0%, #f6f9fc 100%);
            padding: 48px 0 76px 0;
            overflow: hidden;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        html[data-theme="dark"] .darli-career-hero {
            background: linear-gradient(180deg, #0e192b 0%, #0b1320 100%);
            border-bottom-color: #1e293b;
        }

        .darli-career-hero__bg-left {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 320px;
            background: url('/themes/homzen/images/careers-hero-left.png') no-repeat left center;
            background-size: cover;
            opacity: 0.95;
            pointer-events: none;
            z-index: 1;
            mask-image: linear-gradient(to right, rgba(0, 0, 0, 1) 50%, rgba(0, 0, 0, 0) 100%);
            -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 1) 50%, rgba(0, 0, 0, 0) 100%);
        }

        .darli-career-hero__bg-right {
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 280px;
            background: url('/themes/homzen/images/careers-hero-right.png') no-repeat right center;
            background-size: cover;
            opacity: 0.9;
            pointer-events: none;
            z-index: 1;
            mask-image: linear-gradient(to left, rgba(0, 0, 0, 1) 40%, rgba(0, 0, 0, 0) 100%);
            -webkit-mask-image: linear-gradient(to left, rgba(0, 0, 0, 1) 40%, rgba(0, 0, 0, 0) 100%);
        }

        @media (max-width: 991px) {

            .darli-career-hero__bg-left,
            .darli-career-hero__bg-right {
                display: none;
            }
        }

        .darli-career-hero__content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 3;
        }

        .darli-career-hero__pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fbf5ea;
            color: #b8862d;
            font-size: 12.5px;
            font-weight: 700;
            padding: 5px 16px;
            border-radius: 9999px;
            border: 1px solid #f3e6cf;
            margin-bottom: 14px;
        }

        html[data-theme="dark"] .darli-career-hero__pill {
            background: #221c12;
            border-color: #3d321d;
            color: #d9aa55;
        }

        .darli-career-hero__title {
            font-size: 34px;
            font-weight: 850;
            color: var(--darli-career-navy);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        @media (max-width: 768px) {
            .darli-career-hero__title {
                font-size: 26px;
            }
        }

        .darli-career-hero__desc {
            font-size: 14.5px;
            line-height: 1.8;
            color: var(--darli-career-muted);
            max-width: 650px;
            margin: 0 auto;
        }

        /* Filter Card */
        .darli-career-filter-wrapper {
            margin-top: -38px;
            position: relative;
            z-index: 10;
            margin-bottom: 35px;
        }

        .darli-career-filter-box {
            background: var(--darli-career-card);
            border: 1px solid var(--darli-career-border);
            border-radius: 16px;
            padding: 14px 18px;
            box-shadow: 0 10px 28px -4px rgba(16, 32, 56, 0.08);
        }

        .darli-filter-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .darli-filter-input-wrap .darli-input-icon {
            position: absolute;
            right: 14px;
            color: #94a3b8;
            pointer-events: none;
        }

        .darli-filter-input {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 40px 10px 14px;
            font-size: 13.5px;
            width: 100%;
            color: var(--darli-career-navy);
            outline: none;
            transition: border-color 0.2s;
        }

        .darli-filter-input:focus {
            border-color: var(--darli-career-gold);
        }

        html[data-theme="dark"] .darli-filter-input {
            background: #0d1627;
            border-color: #24344d;
            color: #f8fafc;
        }

        .darli-filter-select-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .darli-filter-select-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--darli-career-navy);
            margin-bottom: 2px;
            padding-right: 4px;
        }

        .darli-filter-select-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .darli-filter-select-wrap .darli-select-icon {
            position: absolute;
            right: 12px;
            color: #94a3b8;
            pointer-events: none;
        }

        .darli-filter-select {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 34px 8px 12px;
            font-size: 13px;
            width: 100%;
            color: #475569;
            font-weight: 500;
            outline: none;
            cursor: pointer;
        }

        html[data-theme="dark"] .darli-filter-select {
            background: #0d1627;
            border-color: #24344d;
            color: #cbd5e1;
        }

        .darli-filter-btn {
            background: var(--darli-career-gold);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .darli-filter-btn:hover {
            background: var(--darli-career-gold-hover);
        }

        /* Section Header */
        .darli-jobs-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .darli-jobs-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .darli-jobs-title-wrap .title-icon {
            color: var(--darli-career-gold);
            font-size: 20px;
            display: flex;
            align-items: center;
        }

        .darli-jobs-title {
            font-size: 19px;
            font-weight: 850;
            color: var(--darli-career-navy);
            margin: 0;
        }

        .darli-jobs-count-tag {
            background: #fbf5ea;
            color: #b8862d;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            border: 1px solid #f3e6cf;
        }

        .darli-jobs-quote {
            color: var(--darli-career-muted);
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .darli-jobs-quote::before {
            content: '';
            display: inline-block;
            width: 3px;
            height: 16px;
            background: var(--darli-career-gold);
            border-radius: 2px;
        }

        /* Job Card Exact */
        .darli-job-card {
            background: var(--darli-career-card);
            border: 1px solid var(--darli-career-border);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 14px;
            box-shadow: var(--darli-career-shadow);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .darli-job-card:hover {
            box-shadow: var(--darli-career-shadow-hover);
            border-color: rgba(200, 150, 62, 0.4);
            transform: translateY(-2px);
        }

        .darli-job-card__right {
            display: flex;
            align-items: center;
            gap: 18px;
            flex: 1;
            min-width: 0;
        }

        .darli-job-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: #f4f8fd;
            border: 1px solid #e5effa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #244b7a;
            flex-shrink: 0;
        }

        html[data-theme="dark"] .darli-job-icon-box {
            background: #15243d;
            border-color: #223759;
            color: #93b2dc;
        }

        .darli-job-details {
            flex: 1;
            min-width: 0;
        }

        .darli-job-title {
            font-size: 16.5px;
            font-weight: 850;
            color: var(--darli-career-navy);
            margin: 0 0 8px 0;
        }

        .darli-job-title a {
            color: inherit;
            text-decoration: none;
        }

        .darli-job-title a:hover {
            color: var(--darli-career-gold);
        }

        .darli-job-tags-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            margin-bottom: 8px;
        }

        .darli-job-tag {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .darli-job-tag--gold {
            background: var(--darli-career-tag-gold-bg);
            color: var(--darli-career-tag-gold-text);
            border: 1px solid var(--darli-career-tag-gold-border);
        }

        .darli-job-tag--blue {
            background: var(--darli-career-tag-blue-bg);
            color: var(--darli-career-tag-blue-text);
            border: 1px solid var(--darli-career-tag-blue-border);
        }

        .darli-job-tag-icon {
            font-size: 11px;
            color: currentColor;
        }

        .darli-job-summary {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 6px 0;
            line-height: 1.6;
        }

        .darli-job-deadline {
            font-size: 11.5px;
            color: #8fa0b5;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
        }

        /* Left Action Side */
        .darli-job-card__left {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            padding-left: 6px;
            border-left: 1px solid #f1f5f9;
            min-width: 140px;
        }

        html[data-theme="dark"] .darli-job-card__left {
            border-left-color: #1e293b;
        }

        .darli-job-apply-btn {
            background: var(--darli-career-gold);
            color: #ffffff;
            font-weight: 700;
            font-size: 13.5px;
            border: none;
            border-radius: 10px;
            padding: 9px 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            width: 100%;
            transition: background-color 0.2s;
        }

        .darli-job-apply-btn:hover {
            background: var(--darli-career-gold-hover);
            color: #ffffff;
        }

        .darli-job-detail-link {
            font-size: 12px;
            font-weight: 700;
            color: #1e293b;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }

        .darli-job-detail-link:hover {
            color: var(--darli-career-gold);
        }

        html[data-theme="dark"] .darli-job-detail-link {
            color: #94a3b8;
        }

        @media (max-width: 768px) {
            .darli-job-card {
                flex-direction: column;
                align-items: stretch;
                gap: 16px;
            }

            .darli-job-card__left {
                border-left: none;
                border-top: 1px solid #f1f5f9;
                padding-top: 14px;
                padding-left: 0;
                width: 100%;
            }
        }
    </style>

    <div class="darli-career-page">
        
        <section class="darli-career-hero">
            <div class="darli-career-hero__bg-left"></div>
            <div class="darli-career-hero__bg-right"></div>

            <div class="container">
                <div class="darli-career-hero__content">
                    <div class="darli-career-hero__pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span><?php echo e($isAr ? 'انضم إلى فريق دارلي' : 'Join Darli Real Estate Team'); ?></span>
                    </div>

                    <h1 class="darli-career-hero__title">
                        <?php echo e($isAr ? 'الوظائف الشاغرة وفرص العمل' : 'Current Job Openings'); ?>

                    </h1>

                    <p class="darli-career-hero__desc">
                        <?php echo e($isAr
                            ? 'كن جزءاً من رحلة دارلي في بني سويف وساهم معنا في بناء مستقبل أكثر تطوراً للقطاع العقاري، من خلال فريق شغوف يصنع الفرق.'
                            : 'Be part of Darli journey and contribute to shaping a smarter real estate future through a passionate team making an impact.'); ?>

                    </p>
                </div>
            </div>
        </section>

        
        <div class="container">
            <div class="darli-career-filter-wrapper">
                <div class="darli-career-filter-box">
                    <form method="get" action="<?php echo e(route('careers.index', ['locale' => $locale])); ?>">
                        <div class="row g-2 align-items-center">
                            
                            <div class="col-12 col-lg-5">
                                <div class="darli-filter-input-wrap">
                                    <svg class="darli-input-icon" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.3-4.3" />
                                    </svg>
                                    <input type="text" name="q" value="<?php echo e(request('q')); ?>"
                                        class="darli-filter-input"
                                        placeholder="<?php echo e($isAr ? 'ابحث عن وظيفة، مهارة، أو كلمة مفتاحية...' : 'Search by job title, skill, or keyword...'); ?>">
                                </div>
                            </div>

                            
                            <div class="col-12 col-sm-6 col-lg-2">
                                <div class="darli-filter-select-group">
                                    <span class="darli-filter-select-label"><?php echo e($isAr ? 'القسم' : 'Department'); ?></span>
                                    <div class="darli-filter-select-wrap">
                                        <svg class="darli-select-icon" width="13" height="13" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                        </svg>
                                        <select name="department" class="darli-filter-select">
                                            <option value=""><?php echo e($isAr ? 'جميع الأقسام' : 'All Departments'); ?></option>
                                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($dept); ?>" <?php if(request('department') === $dept): echo 'selected'; endif; ?>>
                                                    <?php echo e($dept); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="col-12 col-sm-6 col-lg-2">
                                <div class="darli-filter-select-group">
                                    <span class="darli-filter-select-label"><?php echo e($isAr ? 'نوع الدوام' : 'Job Type'); ?></span>
                                    <div class="darli-filter-select-wrap">
                                        <svg class="darli-select-icon" width="13" height="13" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <select name="type" class="darli-filter-select">
                                            <option value=""><?php echo e($isAr ? 'جميع أنواع الدوام' : 'All Types'); ?></option>
                                            <option value="دوام كامل" <?php if(request('type') === 'دوام كامل'): echo 'selected'; endif; ?>>
                                                <?php echo e($isAr ? 'دوام كامل' : 'Full Time'); ?></option>
                                            <option value="دوام جزئي" <?php if(request('type') === 'دوام جزئي'): echo 'selected'; endif; ?>>
                                                <?php echo e($isAr ? 'دوام جزئي' : 'Part Time'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="col-12 col-sm-6 col-lg-2">
                                <div class="darli-filter-select-group">
                                    <span class="darli-filter-select-label"><?php echo e($isAr ? 'الموقع' : 'Location'); ?></span>
                                    <div class="darli-filter-select-wrap">
                                        <svg class="darli-select-icon" width="13" height="13" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                        <select name="location" class="darli-filter-select">
                                            <option value=""><?php echo e($isAr ? 'بني سويف' : 'Beni Suef'); ?></option>
                                            <?php $__currentLoopData = $locations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($loc); ?>" <?php if(request('location') === $loc): echo 'selected'; endif; ?>>
                                                    <?php echo e($loc); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="col-12 col-sm-6 col-lg-1">
                                <button type="submit" class="darli-filter-btn" style="margin-top: 18px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.3-4.3" />
                                    </svg>
                                    <span><?php echo e($isAr ? 'بحث' : 'Search'); ?></span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            
            <div class="darli-jobs-header-row">
                <div class="darli-jobs-title-wrap">
                    <span class="title-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                        </svg>
                    </span>
                    <h2 class="darli-jobs-title"><?php echo e($isAr ? 'الوظائف المتاحة حالياً' : 'Available Positions'); ?></h2>
                    <span class="darli-jobs-count-tag"><?php echo e($isAr ? 'وظائف' : 'Jobs'); ?></span>
                </div>

                <div class="darli-jobs-quote">
                    <span><?php echo e($isAr ? 'انضم إلى دارلي .. وكن جزءاً من قصة نجاح حقيقية' : 'Join Darli and become part of a real success story'); ?></span>
                </div>
            </div>

            
            <div class="darli-jobs-container">
                <?php $__empty_1 = true; $__currentLoopData = $careers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $career): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $tr = $career->translation($locale);
                        $title = $tr?->title ?: $career->title;
                        $slug = $tr?->slug ?: $career->translation('en')?->slug ?: $career->id;
                        $detailUrl = route('careers.show', ['locale' => $locale, 'slug' => $slug]);

                        // Map specific icons according to title keywords
                        $iconSvg = match (true) {
                            str_contains($title, 'مستشار مبيعات') ||
                                (str_contains($title, 'مبيعات') &&
                                    !str_contains($title, 'مندوب') &&
                                    !str_contains($title, 'مدير'))
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
                            str_contains($title, 'خدمة عملاء')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>',
                            str_contains($title, 'تقييم عقاري')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>',
                            str_contains($title, 'تسويق رقمي')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>',
                            str_contains($title, 'محاسب')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>',
                            str_contains($title, 'مدير تسويق')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                            str_contains($title, 'مكتب فني') || str_contains($title, 'هندسة')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
                            str_contains($title, 'توثيق قانوني') || str_contains($title, 'قانوني')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
                            str_contains($title, 'مندوب مبيعات')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
                            str_contains($title, 'استثمار عقاري')
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m11 17 2 2a1 1 0 0 0 1.4 0l6.6-6.6a1 1 0 0 0 0-1.4l-2-2a1 1 0 0 0-1.4 0L11 15.6"/><path d="m13 7.1 2.3 2.3"/><path d="M15 11l2 2"/><path d="m3 11 2 2a1 1 0 0 0 1.4 0l6.6-6.6a1 1 0 0 0 0-1.4l-2-2a1 1 0 0 0-1.4 0L3 9.6a1 1 0 0 0 0 1.4z"/></svg>',
                            default
                                => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>',
                        };
                    ?>

                    <div class="darli-job-card">
                        
                        <div class="darli-job-card__right">
                            <div class="darli-job-icon-box">
                                <?php echo $iconSvg; ?>

                            </div>

                            <div class="darli-job-details">
                                <h3 class="darli-job-title">
                                    <a href="<?php echo e($detailUrl); ?>"><?php echo e($title); ?></a>
                                </h3>

                                <div class="darli-job-tags-row">
                                    <?php if($career->department): ?>
                                        <span class="darli-job-tag darli-job-tag--gold">
                                            <?php echo e($career->department); ?>

                                        </span>
                                    <?php endif; ?>

                                    <?php if($career->location): ?>
                                        <span class="darli-job-tag darli-job-tag--blue">
                                            <svg class="darli-job-tag-icon" width="12" height="12"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <?php echo e($career->location); ?>

                                        </span>
                                    <?php endif; ?>

                                    <?php if($career->employment_type): ?>
                                        <span class="darli-job-tag darli-job-tag--blue">
                                            <svg class="darli-job-tag-icon" width="12" height="12"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <polyline points="12 6 12 12 16 14" />
                                            </svg>
                                            <?php echo e($career->employment_type); ?>

                                        </span>
                                    <?php endif; ?>

                                    <?php if($career->salary_min || $career->salary_max): ?>
                                        <span class="darli-job-tag darli-job-tag--gold">
                                            <svg class="darli-job-tag-icon" width="12" height="12"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="12" y1="8" x2="12" y2="16" />
                                                <line x1="8" y1="12" x2="16" y2="12" />
                                            </svg>
                                            <?php echo e(number_format($career->salary_min)); ?>-<?php echo e(number_format($career->salary_max)); ?>

                                            <?php echo e($isAr ? 'ج.م' : 'EGP'); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="darli-job-summary">
                                    <?php echo e($tr?->summary ?: Str::limit(strip_tags($tr?->description ?? ''), 160)); ?>

                                </p>

                                <p class="darli-job-deadline">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                        <line x1="16" x2="16" y1="2" y2="6" />
                                        <line x1="8" x2="8" y1="2" y2="6" />
                                        <line x1="3" x2="21" y1="10" y2="10" />
                                    </svg>
                                    <span><?php echo e($isAr ? 'آخر موعد للتقديم: ' . ($career->deadline?->translatedFormat('j F Y') ?: 'متاح حتى اكتمال العدد') : 'Deadline: ' . ($career->deadline?->format('j F Y') ?: 'Open until filled')); ?></span>
                                </p>
                            </div>
                        </div>

                        
                        <div class="darli-job-card__left">
                            <a href="<?php echo e($detailUrl); ?>" class="darli-job-apply-btn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="22" y1="2" x2="11" y2="13" />
                                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                </svg>
                                <span><?php echo e($isAr ? 'التقديم الآن' : 'Apply Now'); ?></span>
                            </a>

                            <a href="<?php echo e($detailUrl); ?>" class="darli-job-detail-link">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6" />
                                </svg>
                                <span><?php echo e($isAr ? 'عرض التفاصيل والشروط' : 'View Requirements'); ?></span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-5 bg-white rounded-4 border p-4">
                        <p class="text-muted mb-3">
                            <?php echo e($isAr ? 'لا توجد وظائف شاغرة تطابق خيارات البحث الحالية.' : 'No open positions matching your search.'); ?>

                        </p>
                        <a href="<?php echo e(route('careers.index', ['locale' => $locale])); ?>"
                            class="btn btn-outline-danger rounded-pill px-4">
                            <?php echo e($isAr ? 'عرض جميع الوظائف' : 'View All Jobs'); ?>

                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if($careers->hasPages()): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($careers->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/careers/index.blade.php ENDPATH**/ ?>