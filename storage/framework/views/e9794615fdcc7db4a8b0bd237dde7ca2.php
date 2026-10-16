<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
?>

<?php $__env->startSection('content'); ?>
<style>
    /* ==========================================================================
       Projects Page (دارلي المشروعات العقارية)
       100% Pixel-Perfect Match with Reference Design
       ========================================================================== */

    /* Hero Section */
    .darli-projects-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        padding: 40px 0 65px 0;
        overflow: hidden;
    }
    html[data-theme="dark"] .darli-projects-hero {
        background: linear-gradient(180deg, #0c1726 0%, #111e33 100%);
    }

    .darli-projects-hero__villa {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        height: 100%;
        max-width: 520px;
        object-fit: cover;
        pointer-events: none;
        z-index: 1;
    }
    [dir="ltr"] .darli-projects-hero__villa {
        left: auto;
        right: 0;
        transform: scaleX(-1);
    }

    .darli-projects-hero__content {
        position: relative;
        z-index: 2;
        max-width: 650px;
        margin-right: 0;
        margin-left: auto;
        text-align: right;
    }
    [dir="ltr"] .darli-projects-hero__content {
        margin-left: 0;
        margin-right: auto;
        text-align: left;
    }

    .darli-projects-hero__tag {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #c9933b;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 6px;
    }
    .darli-projects-hero__tag-line {
        display: inline-block;
        width: 28px;
        height: 2.5px;
        background: #c9933b;
        border-radius: 1.5px;
    }

    .darli-projects-hero__title {
        font-size: 38px;
        font-weight: 800;
        color: #0d1e3d;
        margin: 0 0 10px 0;
        line-height: 1.25;
    }
    html[data-theme="dark"] .darli-projects-hero__title {
        color: #f1f5f9;
    }

    .darli-projects-hero__subtitle {
        font-size: 14.5px;
        line-height: 1.7;
        color: #5a6a85;
        margin: 0;
    }
    html[data-theme="dark"] .darli-projects-hero__subtitle {
        color: #94a3b8;
    }

    /* Floating Filter Card */
    .darli-projects-filter-card {
        position: relative;
        z-index: 10;
        margin-top: -38px;
        background: #ffffff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        padding: 20px 24px;
        border: 1px solid #eef2f6;
    }
    html[data-theme="dark"] .darli-projects-filter-card {
        background: #122035;
        border-color: #1e3557;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    }

    .darli-filter-grid {
        display: grid;
        grid-template-columns: 2fr 1.6fr auto;
        gap: 16px;
        align-items: flex-end;
    }
    @media (max-width: 991px) {
        .darli-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .darli-filter-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .darli-filter-label {
        font-size: 13px;
        font-weight: 700;
        color: #0d1e3d;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    html[data-theme="dark"] .darli-filter-label {
        color: #f1f5f9;
    }

    .darli-filter-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .darli-filter-input-wrap .darli-filter-input {
        width: 100%;
        height: 46px;
        background: #ffffff;
        border: 1px solid #dcdfe4;
        border-radius: 10px;
        padding: 0 16px 0 42px;
        font-size: 14px;
        color: #0d1e3d;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    [dir="ltr"] .darli-filter-input-wrap .darli-filter-input {
        padding: 0 42px 0 16px;
    }
    html[data-theme="dark"] .darli-filter-input-wrap .darli-filter-input {
        background: #0c1728;
        border-color: #223c63;
        color: #f1f5f9;
    }
    .darli-filter-input-wrap .darli-filter-input:focus {
        border-color: #c9933b;
        outline: none;
        box-shadow: 0 0 0 3px rgba(201, 147, 59, 0.15);
    }

    .darli-filter-input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: #94a3b8;
    }
    [dir="ltr"] .darli-filter-input-icon {
        left: auto;
        right: 14px;
    }

    .darli-filter-select {
        width: 100%;
        height: 46px;
        background: #ffffff;
        border: 1px solid #dcdfe4;
        border-radius: 10px;
        padding: 0 14px;
        font-size: 14px;
        color: #0d1e3d;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    html[data-theme="dark"] .darli-filter-select {
        background: #0c1728;
        border-color: #223c63;
        color: #f1f5f9;
    }
    .darli-filter-select:focus {
        border-color: #c9933b;
        outline: none;
        box-shadow: 0 0 0 3px rgba(201, 147, 59, 0.15);
    }

    .darli-filter-buttons {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .darli-btn-search {
        height: 46px;
        background: #c9933b;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 0 24px;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
    }
    .darli-btn-search:hover {
        background: #b5812f;
    }

    .darli-btn-reset {
        height: 46px;
        background: #ffffff;
        color: #0d1e3d;
        border: 1.5px solid #0d1e3d;
        border-radius: 10px;
        padding: 0 18px;
        font-size: 14px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .darli-btn-reset:hover {
        background: #f1f5f9;
        color: #0d1e3d;
    }
    html[data-theme="dark"] .darli-btn-reset {
        background: transparent;
        color: #f1f5f9;
        border-color: #475569;
    }
    html[data-theme="dark"] .darli-btn-reset:hover {
        background: #1e293b;
    }

    /* Section Header */
    .darli-projects-sec-header {
        margin-top: 38px;
        margin-bottom: 24px;
        text-align: right;
    }
    [dir="ltr"] .darli-projects-sec-header {
        text-align: left;
    }

    .darli-projects-sec-title {
        font-size: 22px;
        font-weight: 800;
        color: #0d1e3d;
        margin: 0;
        display: inline-block;
    }
    html[data-theme="dark"] .darli-projects-sec-title {
        color: #f1f5f9;
    }

    .darli-projects-sec-count {
        font-size: 20px;
        font-weight: 700;
        color: #0d1e3d;
    }
    html[data-theme="dark"] .darli-projects-sec-count {
        color: #f1f5f9;
    }

    .darli-golden-line {
        width: 48px;
        height: 3px;
        background: #c9933b;
        border-radius: 2px;
        margin-top: 6px;
    }

    /* Cards Grid */
    .darli-projects-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 40px;
    }
    @media (max-width: 991px) {
        .darli-projects-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 640px) {
        .darli-projects-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Card Item */
    .darli-card-item {
        background: #ffffff;
        border: 1px solid #edf0f5;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    html[data-theme="dark"] .darli-card-item {
        background: #122035;
        border-color: #1e3557;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25);
    }
    .darli-card-item:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.09);
    }

    /* Card Image */
    .darli-card-img-wrap {
        position: relative;
        width: 100%;
        height: 220px;
        overflow: hidden;
        background: #e2e8f0;
    }
    .darli-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    .darli-card-item:hover .darli-card-img-wrap img {
        transform: scale(1.04);
    }

    .darli-badge-new {
        position: absolute;
        top: 14px;
        right: 14px;
        background: #c9933b;
        color: #ffffff;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 14px;
        border-radius: 20px;
        z-index: 2;
    }
    [dir="ltr"] .darli-badge-new {
        right: auto;
        left: 14px;
    }

    .darli-fav-btn {
        position: absolute;
        top: 14px;
        left: 14px;
        width: 36px;
        height: 36px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        z-index: 2;
        color: #0d1e3d;
        transition: transform 0.2s, color 0.2s;
    }
    [dir="ltr"] .darli-fav-btn {
        left: auto;
        right: 14px;
    }
    .darli-fav-btn:hover {
        transform: scale(1.1);
        color: #e11d48;
    }

    /* Card Body */
    .darli-card-body {
        padding: 18px 20px 16px 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
        text-align: right;
    }
    [dir="ltr"] .darli-card-body {
        text-align: left;
    }

    .darli-card-title {
        font-size: 17px;
        font-weight: 800;
        color: #0d1e3d;
        margin: 0 0 6px 0;
        line-height: 1.35;
    }
    .darli-card-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    .darli-card-title a:hover {
        color: #c9933b;
    }
    html[data-theme="dark"] .darli-card-title {
        color: #f1f5f9;
    }

    .darli-card-loc {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #718096;
        margin-bottom: 14px;
    }
    html[data-theme="dark"] .darli-card-loc {
        color: #94a3b8;
    }

    /* Specs Bar */
    .darli-card-specs {
        background: #f8fafc;
        border: 1px solid #e7ecf2;
        border-radius: 12px;
        padding: 9px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        direction: rtl;
    }
    html[data-theme="dark"] .darli-card-specs {
        background: #0c1728;
        border-color: #1e3557;
    }

    .darli-spec-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 700;
        color: #0d1e3d;
    }
    html[data-theme="dark"] .darli-spec-item {
        color: #e2e8f0;
    }
    .darli-spec-item svg {
        color: #5a6a85;
        flex-shrink: 0;
    }

    .darli-spec-divider {
        width: 1px;
        height: 18px;
        background: #e2e8f0;
    }
    html[data-theme="dark"] .darli-spec-divider {
        background: #1e3557;
    }

    /* Card Footer */
    .darli-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: auto;
        padding-top: 10px;
    }

    .darli-dev-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .darli-dev-icon {
        width: 32px;
        height: 32px;
        background: #e8f0fe;
        color: #1a73e8;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    html[data-theme="dark"] .darli-dev-icon {
        background: #1e3355;
        color: #60a5fa;
    }

    .darli-dev-name {
        font-size: 12px;
        font-weight: 700;
        color: #0d1e3d;
        line-height: 1.2;
    }
    html[data-theme="dark"] .darli-dev-name {
        color: #f1f5f9;
    }

    .darli-btn-details {
        background: #c9933b;
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        padding: 7px 18px;
        border-radius: 10px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s, transform 0.1s;
    }
    .darli-btn-details:hover {
        background: #b5812f;
        color: #ffffff;
    }
</style>


<section class="darli-projects-hero">
    
    <img src="<?php echo e(asset('themes/homzen/images/darli-projects-villa.png')); ?>" alt="Darli Projects" class="darli-projects-hero__villa">

    <div class="container">
        <div class="darli-projects-hero__content">
            <div class="darli-projects-hero__tag">
                <span class="darli-projects-hero__tag-line"></span>
                <span><?php echo e($isAr ? 'أكتشف أفضل الفرص الإستثمارية' : 'Discover the Best Investment Opportunities'); ?></span>
            </div>
            <h1 class="darli-projects-hero__title">
                <?php echo e($isAr ? 'المشروعات العقارية' : 'Real Estate Developments'); ?>

            </h1>
            <p class="darli-projects-hero__subtitle">
                <?php echo e($isAr ? 'تصفح مجموعة مميزة من المشروعات السكنية والتجارية في أشهر مدننا،' : 'Browse a curated collection of residential and commercial projects in our top cities,'); ?><br>
                <?php echo e($isAr ? 'والمناطق المجاورة، واكتشف فرص استثمارية واعدة تناسب احتياجاتك.' : 'and neighbouring areas, and discover promising investment opportunities to suit your needs.'); ?>

            </p>
        </div>
    </div>
</section>

<section class="flat-section-v5 pt-0">
    <div class="container">
        
        <div class="darli-projects-filter-card">
            <form method="get" action="<?php echo e(route('projects.index', ['locale' => $locale])); ?>">
                <div class="darli-filter-grid">
                    
                    <div class="darli-filter-field">
                        <label class="darli-filter-label">
                            <?php echo e($isAr ? 'ابحث عن مشروع' : 'Search projects'); ?>

                        </label>
                        <div class="darli-filter-input-wrap">
                            <input type="text" name="q" value="<?php echo e(request('q')); ?>"
                                class="darli-filter-input"
                                placeholder="<?php echo e($isAr ? '... اسم المشروع' : 'Project name...'); ?>">
                            <svg class="darli-filter-input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                    </div>

                    
                    <div class="darli-filter-field">
                        <label class="darli-filter-label">
                            <?php echo e($isAr ? 'المنطقة' : 'Area'); ?>

                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </label>
                        <select name="location_id" class="darli-filter-select">
                            <option value=""><?php echo e($isAr ? 'كل المناطق' : 'All areas'); ?></option>
                            <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $locName = $location->translation($locale)?->name ?? $location->id;
                                ?>
                                <option value="<?php echo e($location->id); ?>" <?php if((string) request('location_id') === (string) $location->id): echo 'selected'; endif; ?>>
                                    <?php echo e($locName); ?>

                                </option>
                                <?php $__currentLoopData = $location->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($child->id); ?>" <?php if((string) request('location_id') === (string) $child->id): echo 'selected'; endif; ?>>
                                        &nbsp;&nbsp;&nbsp;— <?php echo e($child->translation($locale)?->name ?? $child->id); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div class="darli-filter-buttons">
                        <button type="submit" class="darli-btn-search">
                            <?php echo e($isAr ? 'بحث' : 'Search'); ?>

                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                        <a href="<?php echo e(route('projects.index', ['locale' => $locale])); ?>" class="darli-btn-reset">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                            <?php echo e($isAr ? 'إعادة تعيين' : 'Reset'); ?>

                        </a>
                    </div>
                </div>
            </form>
        </div>

        
        <div class="darli-projects-sec-header">
            <h2 class="darli-projects-sec-title">
                <?php echo e($isAr ? 'المشروعات العقارية' : 'Real estate developments'); ?>

                <span class="darli-projects-sec-count">(<?php echo e(number_format($projects->total())); ?>)</span>
            </h2>
            <div class="darli-golden-line"></div>
        </div>

        
        <?php if($projects->isEmpty()): ?>
            <div class="darli-panel darli-empty" style="text-align: center; padding: 48px 24px; background: #fff; border-radius: 16px; border: 1px solid #edf0f5; margin-bottom: 40px;">
                <h5 class="mb-2" style="font-weight: 800; color: #0d1e3d;"><?php echo e($isAr ? 'لا توجد مشروعات مطابقة' : 'No matching projects'); ?></h5>
                <p class="text-1 mb-0" style="color: #64748b;"><?php echo e($isAr ? 'جرّب تغيير كلمة البحث أو اختيار منطقة أخرى.' : 'Try a different keyword or area.'); ?></p>
            </div>
        <?php else: ?>
            <div class="darli-projects-grid">
                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $tr = $project->translation($locale);
                        $url = $tr?->slug ? route('projects.show', ['locale' => $locale, 'slug' => $tr->slug]) : '#';
                        $image = $project->properties->first()?->primaryImageUrl($locale)
                            ?? asset('themes/homzen/images/darli-placeholder-ar.png');
                        $locName = $project->location?->translation($locale)?->name ?? ($isAr ? 'بني سويف' : 'Beni Suef');
                    ?>
                    <div class="darli-card-item">
                        
                        <div class="darli-card-img-wrap">
                            <a href="<?php echo e($url); ?>">
                                <img src="<?php echo e($image); ?>" alt="<?php echo e($tr?->name); ?>" loading="lazy"
                                    onerror="this.onerror=null;this.src='<?php echo e(asset('themes/homzen/images/darli-placeholder-ar.png')); ?>';">
                            </a>
                            <span class="darli-badge-new"><?php echo e($isAr ? 'مشروع جديد' : 'New project'); ?></span>
                            <button type="button" class="darli-fav-btn" aria-label="Favorite">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </button>
                        </div>

                        
                        <div class="darli-card-body">
                            <h3 class="darli-card-title">
                                <a href="<?php echo e($url); ?>"><?php echo e($tr?->name); ?></a>
                            </h3>
                            <div class="darli-card-loc">
                                <span><?php echo e($locName); ?></span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>

                            
                            <div class="darli-card-specs">
                                <div class="darli-spec-item">
                                    <span><?php echo e($project->blocks_count ?? '12'); ?> <?php echo e($isAr ? 'عمارة' : 'bldgs'); ?></span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5a6a85" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M20 21V11a2 2 0 0 0-2-2h-2M2 21h20M8 7h2M8 11h2M8 15h2M8 19h2M12 7h2M12 11h2M12 15h2M12 19h2"></path>
                                    </svg>
                                </div>
                                <div class="darli-spec-divider"></div>
                                <div class="darli-spec-item">
                                    <span><?php echo e($project->flats_count ?? '648'); ?> <?php echo e($isAr ? 'وحدة' : 'units'); ?></span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#5a6a85">
                                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path>
                                    </svg>
                                </div>
                                <div class="darli-spec-divider"></div>
                                <div class="darli-spec-item">
                                    <span><?php echo e($isAr ? 'أدوار' : 'floors'); ?></span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#5a6a85" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                                        <polyline points="2 17 12 22 22 17"></polyline>
                                        <polyline points="2 12 12 17 22 12"></polyline>
                                    </svg>
                                </div>
                            </div>

                            
                            <div class="darli-card-footer">
                                <div class="darli-dev-info">
                                    <div class="darli-dev-icon">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M20 21V11a2 2 0 0 0-2-2h-2M2 21h20M8 7h2M8 11h2M8 15h2M8 19h2M12 7h2M12 11h2M12 15h2M12 19h2"></path>
                                        </svg>
                                    </div>
                                    <span class="darli-dev-name">
                                        <?php echo e($project->investor_name ?: ($isAr ? 'شركة المستقبل العقارية' : 'Al Mustaqbal Real Estate')); ?>

                                    </span>
                                </div>
                                <a href="<?php echo e($url); ?>" class="darli-btn-details">
                                    <span><?php echo e($isAr ? 'التفاصيل' : 'Details'); ?></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="19" y1="12" x2="5" y2="12"></line>
                                        <polyline points="12 19 5 12 12 5"></polyline>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if($projects->hasPages()): ?>
                <div class="darli-pagination" style="display: flex; justify-content: center; margin-bottom: 50px;">
                    <?php echo e($projects->links('vendor.pagination.darli')); ?>

                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.darli-fav-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const isFilled = btn.classList.toggle('active');
                const path = btn.querySelector('svg path');
                if (isFilled) {
                    btn.style.color = '#ef4444';
                    if (path) path.setAttribute('fill', '#ef4444');
                } else {
                    btn.style.color = '';
                    if (path) path.setAttribute('fill', 'none');
                }
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/projects/index.blade.php ENDPATH**/ ?>