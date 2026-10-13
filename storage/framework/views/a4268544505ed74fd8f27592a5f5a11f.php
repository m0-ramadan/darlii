<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $pageTitle = $isAr ? 'تعرف على نخبة وكلائنا العقاريين | منصة دارلي العقارية' : 'Meet Our Elite Real Estate Agents | Darli Real Estate';
?>

<?php $__env->startSection('content'); ?>
<style>
    /* ==========================================================================
       Darli Agents Page (دليل الوكلاء والشركات العقارية)
       Pixel-Perfect & Fully Integrated with Homzen / Darli Shell & Dark Mode
       ========================================================================== */

    :root {
        --darli-agent-bg: #f8fafc;
        --darli-agent-card-bg: #ffffff;
        --darli-agent-border: #e2e8f0;
        --darli-agent-text: #0f172a;
        --darli-agent-muted: #64748b;
        --darli-agent-primary: #db1d23;
        --darli-agent-primary-hover: #b81419;
        --darli-agent-primary-soft: rgba(219, 29, 35, 0.08);
        --darli-agent-success: #10b981;
        --darli-agent-success-soft: rgba(16, 185, 129, 0.1);
        --darli-agent-gold: #c8a45c;
        --darli-agent-gold-soft: rgba(200, 164, 92, 0.12);
        --darli-agent-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
        --darli-agent-shadow-hover: 0 16px 32px -4px rgba(15, 23, 42, 0.12), 0 4px 12px -2px rgba(15, 23, 42, 0.06);
    }

    html[data-theme="dark"] {
        --darli-agent-bg: #0b1320;
        --darli-agent-card-bg: #111e33;
        --darli-agent-border: #1e293b;
        --darli-agent-text: #f8fafc;
        --darli-agent-muted: #94a3b8;
        --darli-agent-primary-soft: rgba(219, 29, 35, 0.18);
        --darli-agent-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
        --darli-agent-shadow-hover: 0 16px 32px -4px rgba(0, 0, 0, 0.6);
    }

    /* Hero Section */
    .darli-agents-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        padding: 55px 0 75px 0;
        overflow: hidden;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-theme="dark"] .darli-agents-hero {
        background: linear-gradient(180deg, #0c1726 0%, #111e33 100%);
        border-bottom-color: #1e293b;
    }

    .darli-agents-hero__pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: radial-gradient(rgba(219, 29, 35, 0.06) 1px, transparent 1px);
        background-size: 24px 24px;
        pointer-events: none;
        opacity: 0.8;
    }

    .darli-agents-hero__content {
        position: relative;
        z-index: 2;
        max-width: 820px;
        margin: 0 auto;
        text-align: center;
    }

    .darli-agents-hero__tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--darli-agent-primary-soft);
        color: var(--darli-agent-primary);
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 18px;
        letter-spacing: 0.3px;
        border: 1px solid rgba(219, 29, 35, 0.15);
    }

    .darli-agents-hero__title {
        font-size: 38px;
        font-weight: 800;
        color: var(--darli-agent-text);
        line-height: 1.35;
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    @media (max-width: 768px) {
        .darli-agents-hero__title {
            font-size: 28px;
        }
    }

    .darli-agents-hero__desc {
        font-size: 16px;
        line-height: 1.75;
        color: var(--darli-agent-muted);
        max-width: 720px;
        margin: 0 auto;
    }

    /* Filter Bar */
    .darli-agents-filter-wrap {
        position: relative;
        margin-top: -36px;
        z-index: 10;
        margin-bottom: 45px;
    }

    .darli-agents-filter-card {
        background: var(--darli-agent-card-bg);
        border: 1px solid var(--darli-agent-border);
        border-radius: 18px;
        padding: 16px 20px;
        box-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.08);
    }
    html[data-theme="dark"] .darli-agents-filter-card {
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
    }

    .darli-agents-search-input {
        background: #f8fafc;
        border: 1px solid var(--darli-agent-border);
        color: var(--darli-agent-text);
        border-radius: 12px;
        padding: 12px 18px;
        font-size: 14px;
        width: 100%;
        transition: all 0.2s ease;
    }
    html[data-theme="dark"] .darli-agents-search-input {
        background: #0b1320;
        color: #f8fafc;
    }
    .darli-agents-search-input:focus {
        outline: none;
        border-color: var(--darli-agent-primary);
        box-shadow: 0 0 0 3px var(--darli-agent-primary-soft);
    }

    .darli-agents-select {
        background: #f8fafc;
        border: 1px solid var(--darli-agent-border);
        color: var(--darli-agent-text);
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 14px;
        width: 100%;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    html[data-theme="dark"] .darli-agents-select {
        background: #0b1320;
        color: #f8fafc;
    }
    .darli-agents-select:focus {
        outline: none;
        border-color: var(--darli-agent-primary);
    }

    .darli-agents-btn-submit {
        background: var(--darli-agent-primary);
        color: #ffffff;
        font-weight: 700;
        font-size: 14px;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        height: 100%;
    }
    .darli-agents-btn-submit:hover {
        background: var(--darli-agent-primary-hover);
        color: #ffffff;
        transform: translateY(-1px);
    }

    /* Cards Grid */
    .darli-agents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 28px;
        margin-bottom: 50px;
    }
    @media (max-width: 640px) {
        .darli-agents-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    .darli-agent-card {
        background: var(--darli-agent-card-bg);
        border: 1px solid var(--darli-agent-border);
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: var(--darli-agent-shadow);
        position: relative;
    }
    .darli-agent-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--darli-agent-shadow-hover);
        border-color: rgba(219, 29, 35, 0.3);
    }

    /* Card Top Header / Cover */
    .darli-agent-card__cover {
        height: 90px;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    html[data-theme="dark"] .darli-agent-card__cover {
        background: linear-gradient(135deg, #132238 0%, #0a111c 100%);
    }
    .darli-agent-card__cover-accent {
        position: absolute;
        top: -30px;
        right: -30px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(219, 29, 35, 0.25) 0%, transparent 70%);
        border-radius: 50%;
    }

    .darli-agent-card__badges {
        position: absolute;
        top: 14px;
        left: 14px;
        right: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 2;
    }
    [dir="ltr"] .darli-agent-card__badges {
        left: 14px;
        right: 14px;
    }

    .darli-agent-badge-verified {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(16, 185, 129, 0.95);
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 9999px;
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .darli-agent-badge-props {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(15, 23, 42, 0.75);
        color: #ffffff;
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 9999px;
        backdrop-filter: blur(4px);
    }

    /* Card Body */
    .darli-agent-card__body {
        padding: 0 24px 22px 24px;
        display: flex;
        flex-direction: column;
        flex: 1;
        position: relative;
    }

    .darli-agent-card__avatar-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: -46px;
        margin-bottom: 16px;
        position: relative;
        z-index: 3;
    }

    .darli-agent-card__avatar-link {
        display: block;
        width: 84px;
        height: 84px;
        border-radius: 18px;
        background: var(--darli-agent-card-bg);
        padding: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: 2px solid var(--darli-agent-border);
        overflow: hidden;
        transition: transform 0.2s ease;
    }
    .darli-agent-card__avatar-link:hover {
        transform: scale(1.05);
    }

    .darli-agent-card__avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 14px;
        background: #f1f5f9;
    }

    .darli-agent-card__exp-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--darli-agent-gold-soft);
        color: #93712b;
        font-size: 11px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 8px;
        border: 1px solid rgba(200, 164, 92, 0.25);
    }
    html[data-theme="dark"] .darli-agent-card__exp-badge {
        color: #e5c37c;
    }

    .darli-agent-card__title {
        font-size: 19px;
        font-weight: 800;
        color: var(--darli-agent-text);
        margin-bottom: 6px;
        line-height: 1.4;
    }
    .darli-agent-card__title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .darli-agent-card__title a:hover {
        color: var(--darli-agent-primary);
    }

    .darli-agent-card__job {
        font-size: 13px;
        font-weight: 600;
        color: var(--darli-agent-primary);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .darli-agent-card__location {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: var(--darli-agent-muted);
        margin-bottom: 14px;
    }

    .darli-agent-card__bio {
        font-size: 13px;
        line-height: 1.65;
        color: var(--darli-agent-muted);
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 42px;
    }

    /* Card Footer / Buttons */
    .darli-agent-card__footer {
        padding-top: 16px;
        border-top: 1px solid var(--darli-agent-border);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: auto;
    }

    .darli-agent-card__btn-view {
        flex: 1;
        background: var(--darli-agent-primary);
        color: #ffffff;
        font-size: 13px;
        font-weight: 700;
        padding: 10px 16px;
        border-radius: 12px;
        text-align: center;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s ease;
    }
    .darli-agent-card__btn-view:hover {
        background: var(--darli-agent-primary-hover);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .darli-agent-card__btn-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 16px;
        transition: all 0.2s ease;
        border: 1px solid var(--darli-agent-border);
        background: #f8fafc;
        color: var(--darli-agent-text);
        flex-shrink: 0;
    }
    html[data-theme="dark"] .darli-agent-card__btn-icon {
        background: #0b1320;
    }
    .darli-agent-card__btn-icon:hover {
        border-color: var(--darli-agent-primary);
        color: var(--darli-agent-primary);
        transform: translateY(-1px);
    }
    .darli-agent-card__btn-icon--whatsapp:hover {
        border-color: #25d366;
        color: #25d366;
        background: rgba(37, 211, 102, 0.08);
    }

    /* Meta Results Summary */
    .darli-agents-results-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .darli-agents-results-count {
        font-size: 15px;
        font-weight: 700;
        color: var(--darli-agent-text);
    }
    .darli-agents-results-count span {
        color: var(--darli-agent-primary);
    }

    /* Empty State */
    .darli-agents-empty {
        background: var(--darli-agent-card-bg);
        border: 1px solid var(--darli-agent-border);
        border-radius: 20px;
        padding: 60px 20px;
        text-align: center;
        margin-bottom: 50px;
    }
    .darli-agents-empty__icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px auto;
        color: var(--darli-agent-muted);
    }
</style>

<div class="darli-agents-page" style="background: var(--darli-agent-bg); min-height: 80vh; padding-bottom: 80px;">
    
    <section class="darli-agents-hero">
        <div class="darli-agents-hero__pattern"></div>
        <div class="container">
            <div class="darli-agents-hero__content">
                <div class="darli-agents-hero__tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span><?php echo e($isAr ? 'وكلاء وشركات معتمدة وموثوقة' : 'Verified & Trusted Agencies'); ?></span>
                </div>

                <h1 class="darli-agents-hero__title">
                    <?php echo e($isAr ? 'تعرف على نخبة وكلائنا العقاريين' : 'Meet Our Elite Real Estate Agents'); ?>

                </h1>

                <p class="darli-agents-hero__desc">
                    <?php echo e($isAr 
                        ? 'تصفح كبرى شركات التطوير والاستثمار العقاري والوسطاء المعتمدين في محافظة بني سويف ومراكزها. تواصل معهم مباشرة للحصول على أفضل الوحدات والاستشارات العقارية الموثوقة.'
                        : 'Explore premier real estate developers, brokerage agencies, and verified property consultants across Beni Suef governorate. Connect directly for guaranteed listings and professional advisory.'); ?>

                </p>
            </div>
        </div>
    </section>

    
    <div class="container">
        <div class="darli-agents-filter-wrap">
            <div class="darli-agents-filter-card">
                <form method="get" action="<?php echo e(route('agents.index', ['locale' => $locale])); ?>">
                    <div class="row g-3 align-items-center">
                        
                        <div class="col-12 col-md-5">
                            <input type="text" name="q" value="<?php echo e(request('q')); ?>" class="darli-agents-search-input"
                                placeholder="<?php echo e($isAr ? 'ابحث باسم الشركة، الوكيل، أو المسمى...' : 'Search company or agent name...'); ?>">
                        </div>

                        
                        <div class="col-12 col-md-4">
                            <select name="location" class="darli-agents-select">
                                <option value=""><?php echo e($isAr ? 'جميع مراكز ومدن بني سويف' : 'All Beni Suef Locations'); ?></option>
                                <option value="مدينة بني سويف" <?php if(request('location') === 'مدينة بني سويف'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مدينة بني سويف (العاصمة)' : 'Beni Suef City'); ?></option>
                                <option value="بني سويف الجديدة" <?php if(request('location') === 'بني سويف الجديدة'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مدينة بني سويف الجديدة' : 'New Beni Suef City'); ?></option>
                                <option value="الواسطى" <?php if(request('location') === 'الواسطى'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مركز الواسطى' : 'El Wasta Center'); ?></option>
                                <option value="الفشن" <?php if(request('location') === 'الفشن'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مركز الفشن' : 'El Fashn Center'); ?></option>
                                <option value="إهناسيا" <?php if(request('location') === 'إهناسيا'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مركز إهناسيا' : 'Ihnasya Center'); ?></option>
                                <option value="ببا" <?php if(request('location') === 'ببا'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مركز ببا' : 'Biba Center'); ?></option>
                                <option value="ناصر" <?php if(request('location') === 'ناصر'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مركز ناصر' : 'Nasser Center'); ?></option>
                                <option value="سمسطا" <?php if(request('location') === 'سمسطا'): echo 'selected'; endif; ?>><?php echo e($isAr ? 'مركز سمسطا' : 'Sumusta Center'); ?></option>
                            </select>
                        </div>

                        
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="darli-agents-btn-submit flex-fill">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                <span><?php echo e($isAr ? 'بحث' : 'Search'); ?></span>
                            </button>
                            <?php if(request()->filled('q') || request()->filled('location')): ?>
                                <a href="<?php echo e(route('agents.index', ['locale' => $locale])); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="border-radius: 12px; padding: 12px 14px;" title="<?php echo e($isAr ? 'إعادة تعيين' : 'Reset'); ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="darli-agents-results-meta">
            <div class="darli-agents-results-count">
                <?php if($isAr): ?>
                    عرض <span><?php echo e($agents->total()); ?></span> شركة ووكيل عقاري معتمد
                <?php else: ?>
                    Showing <span><?php echo e($agents->total()); ?></span> certified companies & agents
                <?php endif; ?>
            </div>

            <div class="text-muted small d-flex align-items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--darli-agent-primary);"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><?php echo e($isAr ? 'محافظة بني سويف ومراكزها' : 'Beni Suef Governorate'); ?></span>
            </div>
        </div>

        
        <?php if($agents->count() > 0): ?>
            <div class="darli-agents-grid">
                <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $name = $agent->display_name;
                        $avatarNum = (($agent->id - 1) % 11) + 1;
                        $avatarUrl = $agent->avatar_path ? asset($agent->avatar_path) : asset("storage/avatars/{$avatarNum}.jpg");
                        $profileUrl = route('agents.show', ['locale' => $locale, 'slug' => $agent->slug]);
                        $propsCount = $agent->user?->properties()->where('status', 'published')->count() ?? 0;
                        $phone = $agent->user?->phone ?: $agent->agency?->phone;
                        $whatsapp = $agent->whatsapp ?: ($phone ? preg_replace('/[^0-9]/', '', $phone) : null);
                        $locationLabel = $agent->agency?->city ?: ($isAr ? 'محافظة بني سويف' : 'Beni Suef, Egypt');
                    ?>

                    <div class="darli-agent-card">
                        
                        <div class="darli-agent-card__cover">
                            <div class="darli-agent-card__cover-accent"></div>
                            <div class="darli-agent-card__badges">
                                <?php if($agent->isVerified()): ?>
                                    <span class="darli-agent-badge-verified">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                        <?php echo e($isAr ? 'معتمد' : 'Verified'); ?>

                                    </span>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>

                                <?php if($propsCount > 0): ?>
                                    <span class="darli-agent-badge-props">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        <?php echo e($propsCount); ?> <?php echo e($isAr ? 'عقارات' : 'Properties'); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="darli-agent-card__body">
                            
                            <div class="darli-agent-card__avatar-row">
                                <a href="<?php echo e($profileUrl); ?>" class="darli-agent-card__avatar-link">
                                    <img src="<?php echo e($avatarUrl); ?>" alt="<?php echo e($name); ?>" class="darli-agent-card__avatar-img"
                                        loading="lazy" onerror="this.onerror=null;this.src='<?php echo e(asset('themes/homzen/images/darli-placeholder.png')); ?>';">
                                </a>

                                <?php if($agent->years_experience > 0): ?>
                                    <span class="darli-agent-card__exp-badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                        <?php echo e($agent->years_experience); ?> <?php echo e($isAr ? 'سنة خبرة' : 'yrs exp'); ?>

                                    </span>
                                <?php endif; ?>
                            </div>

                            
                            <h3 class="darli-agent-card__title">
                                <a href="<?php echo e($profileUrl); ?>"><?php echo e($name); ?></a>
                            </h3>

                            
                            <div class="darli-agent-card__job">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <span><?php echo e($agent->job_title ?: ($isAr ? 'شركة وساطة واستثمار معتمدة' : 'Certified Real Estate Agency')); ?></span>
                            </div>

                            
                            <div class="darli-agent-card__location">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--darli-agent-primary);"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span><?php echo e($locationLabel); ?></span>
                            </div>

                            
                            <p class="darli-agent-card__bio">
                                <?php echo e($agent->translation($locale)?->bio ?: $agent->bio ?: ($isAr ? 'نخبة من خبراء ومستشاري العقارات في محافظة بني سويف لتقديم أفضل الفرص الاستثمارية.' : 'Expert real estate agency providing premier housing and investment opportunities in Beni Suef.')); ?>

                            </p>

                            
                            <div class="darli-agent-card__footer">
                                <a href="<?php echo e($profileUrl); ?>" class="darli-agent-card__btn-view">
                                    <span><?php echo e($isAr ? 'عرض الملف العقاري' : 'View Profile'); ?></span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="transform: <?php echo e($isRtl ? 'scaleX(-1)' : 'none'); ?>;"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </a>

                                <?php if($whatsapp): ?>
                                    <a href="https://wa.me/<?php echo e($whatsapp); ?>" target="_blank" rel="noopener" class="darli-agent-card__btn-icon darli-agent-card__btn-icon--whatsapp" title="<?php echo e($isAr ? 'محادثة واتساب' : 'WhatsApp Chat'); ?>">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8z"/></svg>
                                    </a>
                                <?php endif; ?>

                                <?php if($phone): ?>
                                    <a href="tel:<?php echo e($phone); ?>" class="darli-agent-card__btn-icon" title="<?php echo e($isAr ? 'اتصال هاتفي' : 'Call'); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <?php if($agents->hasPages()): ?>
                <div class="d-flex justify-content-center">
                    <?php echo e($agents->links()); ?>

                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="darli-agents-empty">
                <svg class="darli-agents-empty__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <h3 class="fw-bold mb-2" style="color: var(--darli-agent-text);">
                    <?php echo e($isAr ? 'لم نتمكن من العثور على وكلاء يطابقون بحثك' : 'No agents found matching your query'); ?>

                </h3>
                <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                    <?php echo e($isAr ? 'يرجى تجربة البحث باسم مركز آخر أو الضغط على الزر أدناه لعرض كافة الوكلاء والشركات المعتمدة في بني سويف.' : 'Try adjusting your search criteria or view all verified agencies in Beni Suef.'); ?>

                </p>
                <a href="<?php echo e(route('agents.index', ['locale' => $locale])); ?>" class="btn btn-danger px-4 py-2" style="border-radius: 12px; background: var(--darli-agent-primary); border-color: var(--darli-agent-primary);">
                    <?php echo e($isAr ? 'عرض جميع الوكلاء' : 'View All Agents'); ?>

                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/agents/index.blade.php ENDPATH**/ ?>