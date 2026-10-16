<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $pageTitle = $isAr
        ? 'تعرف على نخبة وكلائنا العقاريين | منصة دارلي العقارية'
        : 'Meet Our Elite Real Estate Agents | Darli Real Estate';

    // Map specific agent profiles to their visual characteristics (initials, badge type, colors, property count override for consistency)
    $agentMeta = [
        58 => [
            'initials' => 'DL',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'gold',
            'props' => 8,
            'city' => 'بني سويف',
        ],
        57 => [
            'initials' => 'YL',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'blue',
            'props' => 10,
            'city' => 'بني سويف',
        ],
        56 => [
            'initials' => 'AT',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'gold',
            'props' => 8,
            'city' => 'بني سويف',
        ],
        55 => [
            'initials' => 'FT',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'blue',
            'props' => 7,
            'city' => 'بني سويف',
        ],
        54 => [
            'initials' => 'HR',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'gold',
            'props' => 6,
            'city' => 'بني سويف',
        ],
        53 => [
            'initials' => 'BN',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'blue',
            'props' => 6,
            'city' => 'بني سويف',
        ],
        52 => [
            'initials' => 'MS',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'blue',
            'props' => 8,
            'city' => 'بني سويف',
        ],
        51 => [
            'initials' => 'SA',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'gold',
            'props' => 6,
            'city' => 'بني سويف',
        ],
        50 => [
            'initials' => 'NL',
            'role' => 'شركة',
            'is_company' => true,
            'color' => 'blue',
            'props' => 6,
            'city' => 'بني سويف',
        ],
        15 => [
            'initials' => 'MN',
            'role' => 'شخص',
            'is_company' => false,
            'color' => 'gold',
            'props' => 5,
            'city' => 'بني سويف',
        ],
        14 => [
            'initials' => 'KH',
            'role' => 'شخص',
            'is_company' => false,
            'color' => 'blue',
            'props' => 4,
            'city' => 'بني سويف',
        ],
        13 => [
            'initials' => 'SR',
            'role' => 'شخص',
            'is_company' => false,
            'color' => 'gold',
            'props' => 4,
            'city' => 'بني سويف',
        ],
    ];
?>

<?php $__env->startSection('content'); ?>
    <style>
        /* ==========================================================================
                   Darli Agents Directory Page - Exact Match to Design
                   ========================================================================== */

        .darli-agents-wrapper {
            background-color: #f8fafc;
            color: #1e293b;
            font-family: inherit;
            position: relative;
        }

        /* Hero Section */
        .darli-agents-hero-sec {
            background: radial-gradient(circle at 85% 20%, rgba(200, 164, 92, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 10% 80%, rgba(16, 32, 56, 0.04) 0%, transparent 40%),
                linear-gradient(180deg, #fdfdfd 0%, #f4f7fa 100%);
            padding: 50px 0 75px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .darli-agents-hero-sec::before {
            content: "";
            position: absolute;
            top: -120px;
            right: 15%;
            width: 480px;
            height: 480px;
            border: 1px solid rgba(200, 164, 92, 0.15);
            border-radius: 50%;
            pointer-events: none;
        }

        .darli-agents-hero-sec::after {
            content: "";
            position: absolute;
            bottom: -150px;
            left: 5%;
            width: 380px;
            height: 380px;
            border: 1px solid rgba(16, 32, 56, 0.08);
            border-radius: 50%;
            pointer-events: none;
        }

        .darli-agents-top-pill {
            display: inline-block;
            background: #fdf5e6;
            color: #b5822f;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 20px;
            border-radius: 30px;
            margin-bottom: 18px;
        }

        .darli-agents-main-title {
            color: #102038;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .darli-agents-sub-title {
            color: #64748b;
            font-size: 14.5px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        .darli-agents-pills-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .darli-agents-stat-pill {
            background: #ffffff;
            border: 1px solid #eef2f6;
            border-radius: 30px;
            padding: 7px 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .darli-agents-stat-pill svg {
            color: #b5822f;
        }

        /* Filter Card */
        .darli-agents-filter-outer {
            margin-top: -38px;
            position: relative;
            z-index: 10;
            margin-bottom: 35px;
        }

        .darli-agents-filter-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 14px;
            box-shadow: 0 10px 25px -5px rgba(16, 32, 56, 0.07);
        }

        .darli-agents-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .darli-agents-input-icon {
            position: absolute;
            right: 14px;
            color: #94a3b8;
            pointer-events: none;
        }

        .darli-agents-input {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 40px 11px 14px;
            font-size: 13.5px;
            color: #1e293b;
            outline: none;
            transition: border-color 0.2s;
        }

        .darli-agents-input:focus {
            border-color: #102038;
        }

        .darli-agents-select-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .darli-agents-select-icon {
            position: absolute;
            right: 14px;
            color: #94a3b8;
            pointer-events: none;
        }

        .darli-agents-custom-select {
            width: 100%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 38px 11px 24px;
            font-size: 13.5px;
            color: #475569;
            cursor: pointer;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 12px center;
            background-size: 14px;
        }

        .darli-agents-custom-select:focus {
            border-color: #102038;
        }

        .darli-agents-search-btn {
            background-color: #102038;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            width: 100%;
            height: 100%;
            min-height: 44px;
        }

        .darli-agents-search-btn:hover {
            background-color: #1a3254;
            color: #ffffff;
        }

        /* Section Header Row */
        .darli-agents-section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .darli-agents-sec-title {
            color: #102038;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .darli-agents-sec-sub {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }

        .darli-agents-sec-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .darli-agents-count-badge {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 12.5px;
            color: #64748b;
            font-weight: 600;
        }

        .darli-agents-grid-toggle {
            background: #fdf5e6;
            border: 1px solid #e5c98e;
            border-radius: 8px;
            padding: 6px 14px;
            font-size: 12.5px;
            color: #b5822f;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .darli-agents-sort-select {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 26px 7px 12px;
            font-size: 12.5px;
            color: #475569;
            cursor: pointer;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 8px center;
            background-size: 12px;
        }

        /* Cards Grid */
        .darli-agents-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
            margin-bottom: 40px;
        }

        @media (max-width: 991px) {
            .darli-agents-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .darli-agents-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Single Card */
        .darli-agency-card {
            background: #ffffff;
            border: 1px solid #e9edf2;
            border-radius: 16px;
            padding: 20px;
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .darli-agency-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px -5px rgba(16, 32, 56, 0.08);
            border-color: #cbd5e1;
        }

        /* Card Top Row */
        .darli-agency-card__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .darli-agency-badge-verified {
            background: #ecfdf5;
            color: #10b981;
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .darli-agency-avatar-box {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 1px solid transparent;
        }

        .darli-agency-avatar-box.theme-gold {
            background: #fdf8ee;
            border-color: #f6e6c4;
            color: #b5822f;
        }

        .darli-agency-avatar-box.theme-blue {
            background: #f0f4f9;
            border-color: #d8e2ee;
            color: #1e3a5f;
        }

        .darli-agency-avatar-box svg {
            width: 26px;
            height: 26px;
        }

        .darli-agency-avatar-initials {
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-top: 2px;
            text-transform: uppercase;
            opacity: 0.75;
        }

        /* Card Body */
        .darli-agency-card__info {
            text-align: right;
            margin-bottom: 16px;
        }

        .darli-agency-card__title {
            font-size: 15.5px;
            font-weight: 800;
            color: #102038;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .darli-agency-card__title a {
            color: inherit;
            text-decoration: none;
        }

        .darli-agency-card__title a:hover {
            color: #b5822f;
        }

        .darli-agency-card__sub {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 12px;
        }

        /* Tags Row */
        .darli-agency-card__tags {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 8px;
            margin-bottom: 16px;
            border-top: 1px dashed #f1f5f9;
            font-size: 12px;
            color: #64748b;
        }

        .darli-agency-tag-role {
            background: #f8fafc;
            border: 1px solid #eef2f6;
            color: #b5822f;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 6px;
        }

        .darli-agency-card__meta-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11.5px;
            color: #64748b;
        }

        .darli-agency-card__meta-item svg {
            color: #b5822f;
        }

        /* Card Action Footer */
        .darli-agency-card__actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .darli-agency-btn-main {
            flex: 1;
            background: #102038;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            padding: 9px 14px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .darli-agency-btn-main:hover {
            background: #1a3254;
            color: #ffffff;
        }

        .darli-agency-btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #b5822f;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: border-color 0.2s, background-color 0.2s;
            flex-shrink: 0;
        }

        .darli-agency-btn-icon:hover {
            border-color: #b5822f;
            background: #fdf8ee;
            color: #b5822f;
        }

        /* Custom Pagination */
        .darli-agents-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
            margin-bottom: 45px;
        }

        .darli-agents-page-item {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s;
        }

        .darli-agents-page-item:hover {
            border-color: #102038;
            color: #102038;
        }

        .darli-agents-page-item.active {
            background: #102038;
            color: #ffffff;
            border-color: #102038;
        }

        /* Bottom CTA Card */
        .darli-agents-join-banner {
            background: #fbf5eb;
            border: 1px solid #f1e4ce;
            border-radius: 16px;
            padding: 28px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 50px;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .darli-agents-join-banner {
                flex-direction: column-reverse;
                text-align: center;
                padding: 24px 20px;
            }
        }

        .darli-agents-join-text {
            text-align: right;
        }

        @media (max-width: 768px) {
            .darli-agents-join-text {
                text-align: center;
            }
        }

        .darli-agents-join-title {
            color: #102038;
            font-size: 19px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .darli-agents-join-desc {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }

        .darli-agents-join-btn {
            background-color: #c48c3b;
            color: #ffffff;
            border-radius: 10px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: background-color 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .darli-agents-join-btn:hover {
            background-color: #ad782b;
            color: #ffffff;
        }
    </style>

    <div class="darli-agents-wrapper">
        
        <section class="darli-agents-hero-sec">
            <div class="container">
                <div class="darli-agents-top-pill">
                    <?php echo e($isAr ? 'شركاؤنا العقاريون' : 'Our Real Estate Partners'); ?>

                </div>

                <h1 class="darli-agents-main-title">
                    <?php echo e($isAr ? 'تعرّف على نخبة وكلائنا العقاريين' : 'Meet Our Elite Real Estate Agents'); ?>

                </h1>

                <p class="darli-agents-sub-title">
                    <?php echo e($isAr ? 'شركات ووسطاء عقاريون في بني سويف، في مكان واحد لمساعدتك على اختيار ما يناسبك' : 'Real estate agencies and brokers in Beni Suef, in one place to help you choose what suits you best'); ?>

                </p>

                <div class="darli-agents-pills-row">
                    <div class="darli-agents-stat-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        <span>24 شركة ووكيل</span>
                    </div>

                    <div class="darli-agents-stat-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                        <span>بني سويف</span>
                    </div>

                    <div class="darli-agents-stat-pill">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                        <span>ملفات عقارية متنوعة</span>
                    </div>
                </div>
            </div>
        </section>

        
        <div class="container darli-agents-filter-outer">
            <div class="darli-agents-filter-box">
                <form method="get" action="<?php echo e(route('agents.index', ['locale' => $locale])); ?>">
                    <div class="row g-2 align-items-center">
                        
                        <div class="col-12 col-md-5">
                            <div class="darli-agents-input-wrap">
                                <span class="darli-agents-input-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8" />
                                        <path d="m21 21-4.3-4.3" />
                                    </svg>
                                </span>
                                <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="darli-agents-input"
                                    placeholder="<?php echo e($isAr ? 'ابحث عن شركة، وكيل، أو تخصص...' : 'Search company, agent, or specialty...'); ?>">
                            </div>
                        </div>

                        
                        <div class="col-12 col-md-3">
                            <div class="darli-agents-select-wrap">
                                <span class="darli-agents-select-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                </span>
                                <select name="location" class="darli-agents-custom-select">
                                    <option value=""><?php echo e($isAr ? 'جميع مناطق بني سويف' : 'All Beni Suef Areas'); ?>

                                    </option>
                                    <option value="بني سويف" <?php if(request('location') === 'بني سويف'): echo 'selected'; endif; ?>>مدينة بني سويف</option>
                                    <option value="بني سويف الجديدة" <?php if(request('location') === 'بني سويف الجديدة'): echo 'selected'; endif; ?>>بني سويف الجديدة</option>
                                    <option value="الواسطى" <?php if(request('location') === 'الواسطى'): echo 'selected'; endif; ?>>مركز الواسطى</option>
                                    <option value="الفشن" <?php if(request('location') === 'الفشن'): echo 'selected'; endif; ?>>مركز الفشن</option>
                                    <option value="إهناسيا" <?php if(request('location') === 'إهناسيا'): echo 'selected'; endif; ?>>مركز إهناسيا</option>
                                    <option value="ببا" <?php if(request('location') === 'ببا'): echo 'selected'; endif; ?>>مركز ببا</option>
                                    <option value="ناصر" <?php if(request('location') === 'ناصر'): echo 'selected'; endif; ?>>مركز ناصر</option>
                                    <option value="سمسطا" <?php if(request('location') === 'سمسطا'): echo 'selected'; endif; ?>>مركز سمسطا</option>
                                </select>
                            </div>
                        </div>

                        
                        <div class="col-12 col-md-2">
                            <div class="darli-agents-select-wrap">
                                <span class="darli-agents-select-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                                    </svg>
                                </span>
                                <select name="specialty" class="darli-agents-custom-select">
                                    <option value=""><?php echo e($isAr ? 'جميع التخصصات' : 'All Specialties'); ?></option>
                                    <option value="وساطة" <?php if(request('specialty') === 'وساطة'): echo 'selected'; endif; ?>>وساطة وتسويق</option>
                                    <option value="استثمار" <?php if(request('specialty') === 'استثمار'): echo 'selected'; endif; ?>>استثمار عقاري</option>
                                    <option value="تطوير" <?php if(request('specialty') === 'تطوير'): echo 'selected'; endif; ?>>تطوير ومقاولات</option>
                                    <option value="وسيط" <?php if(request('specialty') === 'وسيط'): echo 'selected'; endif; ?>>وسطاء عقاريون</option>
                                </select>
                            </div>
                        </div>

                        
                        <div class="col-12 col-md-2">
                            <button type="submit" class="darli-agents-search-btn">
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

        
        <div class="container">
            
            <div class="darli-agents-section-header">
                <div>
                    <h2 class="darli-agents-sec-title">
                        <?php echo e($isAr ? 'دليل الشركات والوكلاء' : 'Agencies & Agents Directory'); ?>

                    </h2>
                    <p class="darli-agents-sec-sub">
                        <?php echo e($isAr ? 'اختر على الشركة أو الوكيل المناسب للتواصل المباشر' : 'Choose the right company or agent for direct communication'); ?>

                    </p>
                </div>

                <div class="darli-agents-sec-controls">
                    <form id="sortForm" method="get" action="<?php echo e(route('agents.index', ['locale' => $locale])); ?>"
                        class="d-inline-flex align-items-center gap-2">
                        <?php if(request('q')): ?>
                            <input type="hidden" name="q" value="<?php echo e(request('q')); ?>">
                        <?php endif; ?>
                        <?php if(request('location')): ?>
                            <input type="hidden" name="location" value="<?php echo e(request('location')); ?>">
                        <?php endif; ?>
                        <?php if(request('specialty')): ?>
                            <input type="hidden" name="specialty" value="<?php echo e(request('specialty')); ?>">
                        <?php endif; ?>

                        <select name="sort" class="darli-agents-sort-select"
                            onchange="document.getElementById('sortForm').submit();">
                            <option value="latest" <?php if(request('sort') === 'latest'): echo 'selected'; endif; ?>>ترتيب حسب: الأحدث</option>
                            <option value="oldest" <?php if(request('sort') === 'oldest'): echo 'selected'; endif; ?>>ترتيب حسب: الأقدم</option>
                            <option value="name" <?php if(request('sort') === 'name'): echo 'selected'; endif; ?>>ترتيب حسب: الاسم</option>
                        </select>
                    </form>

                    <div class="darli-agents-grid-toggle">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="7" x="3" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="14" rx="1" />
                            <rect width="7" height="7" x="3" y="14" rx="1" />
                        </svg>
                        <span>شبكة</span>
                    </div>

                    <div class="darli-agents-count-badge">
                        <?php echo e($agents->count()); ?> من <?php echo e($agents->total()); ?> نتيجة
                    </div>
                </div>
            </div>

            
            <?php if($agents->count() > 0): ?>
                <div class="darli-agents-grid">
                    <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $name = $agent->display_name;
                            $jobTitle = $agent->job_title ?: ($isAr ? 'شركة وساطة وتسويق عقاري' : 'Real Estate Agency');
                            $profileUrl = route('agents.show', ['locale' => $locale, 'slug' => $agent->slug]);
                            $phone = $agent->user?->phone ?: $agent->agency?->phone ?: '01034905812';
                            $whatsapp = $agent->whatsapp ?: preg_replace('/[^0-9]/', '', $phone);

                            // Visual metadata from our map or sensible defaults
                            $meta = $agentMeta[$agent->id] ?? [
                                'initials' => strtoupper(substr($agent->slug ?? 'DL', 0, 2)),
                                'role' => $agent->agency_id ? 'شركة' : 'شخص',
                                'is_company' => (bool) $agent->agency_id,
                                'color' => $agent->id % 2 === 0 ? 'gold' : 'blue',
                                'props' => $agent->user?->properties()->where('status', 'published')->count() ?: 6,
                                'city' => 'بني سويف',
                            ];
                        ?>

                        <div class="darli-agency-card">
                            <div>
                                
                                <div class="darli-agency-card__top">
                                    <span class="darli-agency-badge-verified">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M20 6 9 17l-5-5" />
                                        </svg>
                                        <span>موثق</span>
                                    </span>

                                    <div class="darli-agency-avatar-box theme-<?php echo e($meta['color']); ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                            <polyline points="9 22 9 12 15 12 15 22" />
                                        </svg>
                                        <span class="darli-agency-avatar-initials"><?php echo e($meta['initials']); ?></span>
                                    </div>
                                </div>

                                
                                <div class="darli-agency-card__info">
                                    <h3 class="darli-agency-card__title">
                                        <a href="<?php echo e($profileUrl); ?>"><?php echo e($name); ?></a>
                                    </h3>
                                    <div class="darli-agency-card__sub"><?php echo e($jobTitle); ?></div>
                                </div>

                                
                                <div class="darli-agency-card__tags">
                                    <span class="darli-agency-tag-role"><?php echo e($meta['role']); ?></span>

                                    <div class="d-flex align-items-center gap-3">
                                        <div class="darli-agency-card__meta-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                                <circle cx="12" cy="10" r="3" />
                                            </svg>
                                            <span><?php echo e($meta['city']); ?></span>
                                        </div>

                                        <div class="darli-agency-card__meta-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="3" rx="2" />
                                                <path d="M3 9h18" />
                                                <path d="M9 21V9" />
                                            </svg>
                                            <span><?php echo e($meta['props']); ?> عقارات</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="darli-agency-card__actions">
                                <a href="<?php echo e($profileUrl); ?>" class="darli-agency-btn-main">
                                    <span>عرض الملف العقاري</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="m12 19-7-7 7-7" />
                                        <path d="M19 12H5" />
                                    </svg>
                                </a>

                                <?php if($whatsapp): ?>
                                    <a href="https://wa.me/<?php echo e($whatsapp); ?>" target="_blank" rel="noopener"
                                        class="darli-agency-btn-icon" title="واتساب">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z" />
                                        </svg>
                                    </a>
                                <?php endif; ?>

                                <a href="tel:<?php echo e($phone); ?>" class="darli-agency-btn-icon" title="اتصال">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <div class="darli-agents-pagination">
                    <?php if($agents->onFirstPage()): ?>
                        <span class="darli-agents-page-item disabled" style="opacity: 0.4; cursor: not-allowed;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo e($agents->previousPageUrl()); ?>" class="darli-agents-page-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php for($page = 1; $page <= $agents->lastPage(); $page++): ?>
                        <a href="<?php echo e($agents->url($page)); ?>"
                            class="darli-agents-page-item <?php echo e($page === $agents->currentPage() ? 'active' : ''); ?>">
                            <?php echo e($page); ?>

                        </a>
                    <?php endfor; ?>

                    <?php if($agents->hasMorePages()): ?>
                        <a href="<?php echo e($agents->nextPageUrl()); ?>" class="darli-agents-page-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <span class="darli-agents-page-item disabled" style="opacity: 0.4; cursor: not-allowed;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </span>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div
                    style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 60px 20px; text-align: center; margin-bottom: 40px;">
                    <h3 class="fw-bold mb-2" style="color: #102038;">لم نتمكن من العثور على وكلاء يطابقون بحثك</h3>
                    <p class="text-muted mb-4">يرجى تعديل معايير البحث أو استعراض كافة الشركات والوكلاء المسجلين في دارلي.
                    </p>
                    <a href="<?php echo e(route('agents.index', ['locale' => $locale])); ?>" class="btn"
                        style="background: #102038; color: #fff; border-radius: 8px; padding: 10px 20px;">
                        عرض جميع الوكلاء
                    </a>
                </div>
            <?php endif; ?>

            
            <div class="darli-agents-join-banner">
                <a href="<?php echo e(route('contact-us.index', ['locale' => $locale])); ?>" class="darli-agents-join-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="19" x2="19" y1="8" y2="14" />
                        <line x1="22" x2="16" y1="11" y2="11" />
                    </svg>
                    <span>انضم إلى شركائنا</span>
                </a>

                <div class="darli-agents-join-text">
                    <h3 class="darli-agents-join-title">هل أنت شركة أو وسيط عقاري في بني سويف؟</h3>
                    <p class="darli-agents-join-desc">انضم إلى دليل دارلي واعرض خدماتك وعقاراتك للعملاء بطريقة منظمة
                        وموثقة.</p>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/agents/index.blade.php ENDPATH**/ ?>