<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(in_array(app()->getLocale(), config('locales.rtl')) ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="<?php echo e(asset('themes/homzen/images/logo/favicon.png')); ?>">
    <title><?php echo $__env->yieldContent('title', 'Darlii | دارلي - ' . (app()->getLocale() === 'ar' ? 'مكانك يبدأ هنا' : 'Your Place Starts Here')); ?></title>
    <meta name="description" content="<?php echo $__env->yieldContent('meta_description', app()->getLocale() === 'ar' ? 'منصة دارلي - السوق العقاري الأبرز في مصر لشراء وبيع وتأجير العقارات والمشروعات' : 'Darlii - Egypt\'s premier real estate platform for buying, selling, and renting properties and developments.'); ?>">

    <!-- Preconnect Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script id="Darlii-theme-bootstrap">
        (function() {
            try {
                var theme = localStorage.getItem('darli-theme') || localStorage.getItem('homzen-theme');
                if (theme !== 'dark' && theme !== 'light') {
                    var cookie = document.cookie.match(/(?:^|;\s*)(?:Darlii|homzen)_theme=(dark|light)(?:;|$)/);
                    theme = cookie ? cookie[1] : null;
                }
                if (theme !== 'dark' && theme !== 'light') {
                    theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' :
                        'light';
                }
                document.documentElement.dataset.theme = theme;
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>

    <link rel="stylesheet"
        href="<?php echo e(asset(in_array(app()->getLocale(), config('locales.rtl')) ? 'themes/homzen/plugins/bootstrap/css/bootstrap.rtl.min.css' : 'themes/homzen/plugins/bootstrap/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/darli-design-system.css')); ?>?v=2.0.3">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/style.css')); ?>?v=2.0.3">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/dark-mode.css')); ?>?v=2.0.3">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/rtl-custom.css')); ?>?v=2.0.3">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/locale-switcher.css')); ?>?v=1.0.0">
    
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/darli-unified.css')); ?>?v=2.1.8">

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body class="darli-body">
    <?php echo app(\App\Services\LegacyFrontend\LegacyFrontendRenderer::class)->siteHeaderHtml(app()->getLocale()); ?>

    <button class="darli-theme-toggle" id="theme-toggle-btn" type="button" style="display:none;" aria-label="Toggle theme"></button>

    <?php if(session('success')): ?>
        <div class="container mt-3">
            <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2"
                role="alert">
                <i data-lucide="check-circle-2" style="width: 20px; height: 20px; color: var(--color-success);"></i>
                <div><?php echo e(session('success')); ?></div>
            </div>
        </div>
    <?php endif; ?>

    <main class="darli-main">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Darlii Unified Footer -->
    <footer class="darli-footer">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <?php
                        // $isRtl is set in the navbar block above, but this layout can also be
                        // rendered by views that set their own sections, so default it here.
                        $isRtl = $isRtl ?? in_array(app()->getLocale(), config('locales.rtl'), true);
                        $footerLogo = $isRtl
                            ? 'themes/homzen/images/logo/darli-logo-ar-dark.png'
                            : 'themes/homzen/images/logo/darli-logo-en-dark.png';
                    ?>
                    <a href="<?php echo e(route('home', ['locale' => app()->getLocale()])); ?>" class="d-inline-block mb-3">
                        <img src="<?php echo e(asset($footerLogo)); ?>" alt="Darlii دارلي" class="darli-logo-img"
                            style="height: 52px;">
                    </a>
                    <p class="text-secondary-muted body-sm mb-4" style="max-width: 340px;">
                        <?php echo e(app()->getLocale() === 'ar' ? 'منصة دارلي هي وجهتك الأولى للبحث عن أفضل العقارات والمشروعات السكنية والتجارية في جميع أنحاء جمهورية مصر العربية.' : 'Darlii is your premier destination for finding top residential and commercial properties and developments across Egypt.'); ?>

                    </p>
                    
                    <div class="d-flex align-items-center gap-3">
                        <?php if($fb = setting('contact.facebook', 'https://facebook.com')): ?>
                            <a href="<?php echo e($fb); ?>" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="Facebook">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        <?php if($tw = setting('contact.twitter', 'https://x.com')): ?>
                            <a href="<?php echo e($tw); ?>" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="X (Twitter)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                                    <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        <?php if($yt = setting('contact.youtube', 'https://youtube.com')): ?>
                            <a href="<?php echo e($yt); ?>" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="YouTube">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path
                                        d="M2 8a4 4 0 0 1 4 -4h12a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-12a4 4 0 0 1 -4 -4v-8z" />
                                    <path d="M10 9l5 3l-5 3z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        <?php if($li = setting('contact.linkedin', 'https://linkedin.com')): ?>
                            <a href="<?php echo e($li); ?>" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="LinkedIn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M8 11v5" />
                                    <path d="M8 8v.01" />
                                    <path d="M12 16v-5" />
                                    <path d="M16 16v-3a2 2 0 1 0 -4 0" />
                                    <path d="M3 7a4 4 0 0 1 4 -4h10a4 4 0 0 1 4 4v10a4 4 0 0 1 -4 4h-10a4 4 0 0 1 -4 -4z" />
                                </svg>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <h6 class="footer-title"><?php echo e(app()->getLocale() === 'ar' ? 'روابط سريعة' : 'Quick Links'); ?></h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 body-sm mb-0">
                        <li><a
                                href="<?php echo e(route('properties.index', ['locale' => app()->getLocale()])); ?>"><?php echo e(__('messages.properties')); ?></a>
                        </li>
                        <li><a
                                href="<?php echo e(route('projects.index', ['locale' => app()->getLocale()])); ?>"><?php echo e(__('messages.projects')); ?></a>
                        </li>
                        <li><a href="<?php echo e(url('/' . app()->getLocale() . '/agents')); ?>"><?php echo e(__('messages.agents')); ?></a>
                        </li>
                        <li><a
                                href="<?php echo e(route('blog.index', ['locale' => app()->getLocale()])); ?>"><?php echo e(__('messages.blog')); ?></a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-6">
                    <h6 class="footer-title"><?php echo e(app()->getLocale() === 'ar' ? 'أنواع العقارات' : 'Property Types'); ?>

                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 body-sm mb-0">
                        <li><a
                                href="<?php echo e(route('properties.index', ['locale' => app()->getLocale(), 'type' => 'apartment'])); ?>"><?php echo e(app()->getLocale() === 'ar' ? 'شقق للبيع' : 'Apartments for Sale'); ?></a>
                        </li>
                        <li><a
                                href="<?php echo e(route('properties.index', ['locale' => app()->getLocale(), 'type' => 'villa'])); ?>"><?php echo e(app()->getLocale() === 'ar' ? 'فيلات مستقلة' : 'Standalone Villas'); ?></a>
                        </li>
                        <li><a
                                href="<?php echo e(route('properties.index', ['locale' => app()->getLocale(), 'type' => 'duplex'])); ?>"><?php echo e(app()->getLocale() === 'ar' ? 'دوبلكس وتاون هاوس' : 'Duplex & Townhouse'); ?></a>
                        </li>
                        <li><a
                                href="<?php echo e(route('properties.index', ['locale' => app()->getLocale(), 'type' => 'commercial'])); ?>"><?php echo e(app()->getLocale() === 'ar' ? 'مكاتب ومحلات تجارية' : 'Commercial & Offices'); ?></a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-title"><?php echo e(app()->getLocale() === 'ar' ? 'تواصل معنا' : 'Contact Us'); ?></h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 body-sm mb-3">
                        <li class="d-flex align-items-center gap-2"><i data-lucide="map-pin"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <span><?php echo e(setting(app()->getLocale() === 'ar' ? 'contact.address_ar' : 'contact.address_en', app()->getLocale() === 'ar' ? 'شارع صلاح سالم (تقاطع كورنيش النيل)، مدينة بني سويف' : 'Salah Salem St, Beni Suef City, Egypt')); ?></span>
                        </li>
                        <li class="d-flex align-items-center gap-2"><i data-lucide="phone"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <a href="tel:<?php echo e(preg_replace('/[^\d+]/', '', setting('contact.phone', '+20 10 34905812'))); ?>" style="color:inherit;text-decoration:none;" dir="ltr"><?php echo e(setting('contact.phone', '+20 10 34905812')); ?></a>
                        </li>
                        <li class="d-flex align-items-center gap-2"><i data-lucide="mail"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <a href="mailto:<?php echo e(setting('contact.email', 'info@darlii.eg')); ?>" style="color:inherit;text-decoration:none;"><?php echo e(setting('contact.email', 'info@darlii.eg')); ?></a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="darli-footer-divider"></div>

            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 body-sm">
                <div>
                    &copy; <?php echo e(date('Y')); ?> <strong>Darlii دارلي</strong>.
                    <?php echo e(app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.'); ?>

                </div>
                <div class="d-flex align-items-center gap-3">
                    <?php $__currentLoopData = config('locales.names'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('locale.switch', ['locale' => $code, 'redirect' => request()->getRequestUri()])); ?>"
                            class="text-decoration-none <?php if($code === app()->getLocale()): ?> fw-bold text-warning <?php else: ?> opacity-75 <?php endif; ?>">
                            <?php echo e($name); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </footer>

    <?php echo $__env->make('components.floating-whatsapp', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script src="<?php echo e(asset('themes/homzen/js/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/homzen/js/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/homzen/plugins/bootstrap/js/bootstrap.min.js')); ?>"></script>
    <script defer src="<?php echo e(asset('themes/homzen/js/darli-mobile-menu.js')); ?>?v=1.1.2"></script>
    <script defer src="<?php echo e(asset('themes/homzen/js/theme-mode.js')); ?>?v=2.0.2"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/html/resources/views/layouts/app.blade.php ENDPATH**/ ?>