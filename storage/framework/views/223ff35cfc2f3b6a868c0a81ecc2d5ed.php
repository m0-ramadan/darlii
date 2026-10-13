<?php
    $settings = app(\App\Services\Settings\SettingsRepository::class);
    $panelPrimary = $settings->get('panel.primary_color', '#0F3D2E');
    $panelAccent = $settings->get('panel.accent_color', '#C8A45C');
    $siteName = $settings->get('general.site_name', 'دارلي');
    $logo = $settings->get('branding.logo_ar', 'themes/homzen/images/logo/darli-logo-ar.png');
    $logoUrl = str_starts_with((string) $logo, 'http') ? $logo : asset($logo);
    $user = auth()->user();
    $current = request()->route()?->getName() ?? '';

    /** Sidebar definition: [ label, route name, icon, active-pattern, permission ]. */
    $groups = [
        [
            'title' => 'لوحة المعلومات',
            'items' => [
                ['لوحة التحكم', 'admin.dashboard', 'layout', 'admin.dashboard', null],
                ['الإحصائيات والتقارير', 'admin.stats.index', 'chart-bar', 'admin.stats.*', 'stats.view'],
            ],
        ],
        [
            'title' => 'العقارات',
            'items' => [
                ['كل العقارات', 'admin.properties.index', 'building', 'admin.properties.index', 'property.view'],
                ['إضافة عقار', 'admin.properties.create', 'plus', 'admin.properties.create', 'property.create'],
                ['بانتظار المراجعة', 'admin.properties.pending', 'clipboard', 'admin.properties.pending', 'property.approve'],
                ['العقارات المميزة', 'admin.properties.featured', 'star', 'admin.properties.featured', 'property.feature'],
                ['إسناد العقارات', 'admin.properties.assignments', 'users', 'admin.properties.assignments', 'property.assign'],
            ],
        ],
        [
            'title' => 'المشروعات',
            'items' => [
                ['كل المشروعات', 'admin.projects.index', 'building', 'admin.projects.*', 'project.view'],
            ],
        ],
        [
            'title' => 'المستخدمون والصلاحيات',
            'items' => [
                ['المستخدمون', 'admin.users.index', 'users', 'admin.users.*', 'users.view'],
                ['الشركات والوكلاء العقاريين', 'admin.agents.index', 'briefcase', 'admin.agents.*', 'users.view'],
                ['المسؤولون والسماسرة', 'admin.users.hierarchy', 'activity', 'admin.users.hierarchy', 'users.view'],
                ['الأدوار والصلاحيات', 'admin.roles.index', 'shield', 'admin.roles.*', 'roles.manage'],
            ],
        ],
        [
            'title' => 'المحتوى',
            'items' => [
                ['الصفحات', 'admin.pages.index', 'file-text', 'admin.pages.*', 'content.manage'],
                ['المقالات', 'admin.posts.index', 'edit', 'admin.posts.*', 'content.manage'],
                ['الوظائف وفرص العمل', 'admin.careers.index', 'briefcase', 'admin.careers.*', 'content.manage'],
                ['آراء العملاء', 'admin.testimonials.index', 'message', 'admin.testimonials.*', 'content.testimonials'],
                ['المواقع والمناطق', 'admin.locations.index', 'map', 'admin.locations.*', 'content.taxonomies'],
                ['التصنيفات', 'admin.categories.index', 'tag', 'admin.categories.*', 'content.taxonomies'],
                ['المرافق', 'admin.amenities.index', 'check', 'admin.amenities.*', 'content.taxonomies'],
                ['الوسائط', 'admin.media.index', 'photo', 'admin.media.*', 'media.manage'],
            ],
        ],
        [
            'title' => 'العملاء والمواعيد',
            'items' => [
                ['العملاء المحتملون', 'admin.leads.index', 'message', 'admin.leads.*', null],
                ['المواعيد', 'admin.appointments.index', 'clipboard', 'admin.appointments.*', 'appointment.manage'],
                ['التقييمات', 'admin.reviews.index', 'star', 'admin.reviews.*', 'review.moderate'],
            ],
        ],
        [
            'title' => 'الإعدادات',
            'items' => [
                ['الإعدادات العامة', 'admin.settings.general', 'settings', 'admin.settings.*', 'settings.manage'],
                ['التكاملات', 'admin.settings.integrations', 'plug', 'admin.settings.integrations', 'settings.integrations'],
                ['سجل النشاط', 'admin.audit.index', 'activity', 'admin.audit.*', 'audit.view'],
            ],
        ],
    ];
?>
<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'لوحة التحكم'); ?> | <?php echo e($siteName); ?></title>
    <link rel="icon" type="image/png"
        href="<?php echo e(str_starts_with((string) $settings->get('branding.favicon', ''), 'http') ? $settings->get('branding.favicon') : asset($settings->get('branding.favicon', 'themes/homzen/images/logo/favicon.png'))); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/plugins/bootstrap/css/bootstrap.rtl.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/admin-panel.css')); ?>?v=1.0.0">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/dark-mode.css')); ?>?v=2.0.3">
    <style>
        :root {
            --ap-primary: <?php echo e($panelPrimary); ?>;
            --ap-accent: <?php echo e($panelAccent); ?>;
        }
    </style>
    <?php echo $__env->yieldPushContent('head'); ?>
</head>

<body class="ap-body">
    <div class="ap-shell">
        
        <aside class="ap-sidebar" id="apSidebar">
            <div class="ap-sidebar__brand">
                <a href="<?php echo e(admin_route('admin.dashboard')); ?>" class="ap-brand">
                    <img src="<?php echo e($logoUrl); ?>" alt="<?php echo e($siteName); ?>" class="ap-brand__logo">
                </a>
                <button type="button" class="ap-sidebar__close" data-ap-sidebar-close aria-label="إغلاق">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'x', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </button>
            </div>

            <div class="ap-sidebar__user">
                <img src="<?php echo e($user?->avatar_url); ?>" alt="<?php echo e($user?->name); ?>" class="ap-avatar">
                <div class="ap-sidebar__user-meta">
                    <span class="ap-sidebar__user-name"><?php echo e($user?->name); ?></span>
                    <span class="ap-sidebar__user-role"><?php echo e(user_role_label($user?->primary_role_slug)); ?></span>
                </div>
            </div>

            <nav class="ap-nav">
                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $visibleItems = collect($group['items'])->filter(
                            fn ($item) => $item[4] === null || ($user && $user->hasPermission($item[4])),
                        );
                    ?>
                    <?php if($visibleItems->isNotEmpty()): ?>
                        <div class="ap-nav__group">
                            <span class="ap-nav__title"><?php echo e($group['title']); ?></span>
                            <?php $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $isActive = str_ends_with($item[3], '*') ? str_starts_with($current, rtrim($item[3], '*')) : $current === $item[3]; ?>
                                <a href="<?php echo e(admin_route($item[1])); ?>"
                                    class="ap-nav__link <?php if($isActive): ?> is-active <?php endif; ?>">
                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => $item[2], 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <span><?php echo e($item[0]); ?></span>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <div class="ap-sidebar__footer">
                <a href="<?php echo e(route('home', ['locale' => 'ar'])); ?>" target="_blank" class="ap-nav__link">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'external', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <span>عرض الموقع</span>
                </a>
            </div>
        </aside>

        
        <div class="ap-main">
            <header class="ap-topbar">
                <button type="button" class="ap-topbar__burger" data-ap-sidebar-open aria-label="القائمة">
                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'menu', 'size' => 22], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </button>

                <div class="ap-topbar__titles">
                    <h1 class="ap-topbar__title"><?php echo $__env->yieldContent('page_title', 'لوحة التحكم'); ?></h1>
                    <p class="ap-topbar__subtitle"><?php echo $__env->yieldContent('page_subtitle', 'إدارة كاملة للمنصة العقارية'); ?></p>
                </div>

                <div class="ap-topbar__actions">
                    <?php echo $__env->yieldContent('page_actions'); ?>
                    <button class="darli-theme-toggle btn btn-sm btn-outline-secondary me-2 ms-2" type="button" aria-label="Toggle theme"></button>
                    <div class="ap-dropdown">
                        <button type="button" class="ap-topbar__user" data-ap-dropdown>
                            <img src="<?php echo e($user?->avatar_url); ?>" alt="<?php echo e($user?->name); ?>" class="ap-avatar ap-avatar--sm">
                            <span class="d-none d-md-inline"><?php echo e($user?->name); ?></span>
                            <?php echo $__env->make('admin.partials.icon', ['iconName' => 'chevron-down', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </button>
                        <div class="ap-dropdown__menu">
                            <a href="<?php echo e(admin_route('admin.profile.edit')); ?>" class="ap-dropdown__item">
                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'users', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> الملف الشخصي
                            </a>
                            <a href="<?php echo e(admin_route('admin.settings.general')); ?>" class="ap-dropdown__item">
                                <?php echo $__env->make('admin.partials.icon', ['iconName' => 'settings', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> الإعدادات
                            </a>
                            <form method="post" action="<?php echo e(route('admin.logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="ap-dropdown__item ap-dropdown__item--danger">
                                    <?php echo $__env->make('admin.partials.icon', ['iconName' => 'logout', 'size' => 16], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="ap-content">
                <?php if(session('success')): ?>
                    <div class="ap-alert ap-alert--success">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'check', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span><?php echo e(session('success')); ?></span>
                        <button type="button" class="ap-alert__close" data-ap-dismiss>&times;</button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="ap-alert ap-alert--danger">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'alert', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <span><?php echo e(session('error')); ?></span>
                        <button type="button" class="ap-alert__close" data-ap-dismiss>&times;</button>
                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="ap-alert ap-alert--danger">
                        <?php echo $__env->make('admin.partials.icon', ['iconName' => 'alert', 'size' => 18], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div>
                            <strong>يوجد <?php echo e($errors->count()); ?> خطأ في البيانات:</strong>
                            <ul class="mb-0 mt-1">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                        <button type="button" class="ap-alert__close" data-ap-dismiss>&times;</button>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <footer class="ap-footer">
                <span>&copy; <?php echo e(date('Y')); ?> <?php echo e($siteName); ?> — كل الحقوق محفوظة.</span>
                <span class="ap-footer__meta">لوحة تحكم دارلي · الإصدار 1.0</span>
            </footer>
        </div>
    </div>

    <div class="ap-backdrop" data-ap-sidebar-close></div>

    <script src="<?php echo e(asset('themes/homzen/js/jquery.min.js')); ?>"></script>
    <script src="<?php echo e(asset('themes/homzen/plugins/bootstrap/js/bootstrap.min.js')); ?>"></script>
    <script>
        (function() {
            document.addEventListener('click', function(event) {
                if (event.target.closest('[data-ap-sidebar-open]')) {
                    document.body.classList.add('ap-sidebar-open');
                }
                if (event.target.closest('[data-ap-sidebar-close]')) {
                    document.body.classList.remove('ap-sidebar-open');
                }
                if (event.target.closest('[data-ap-dismiss]')) {
                    var alert = event.target.closest('.ap-alert');
                    if (alert) alert.remove();
                }
                var toggle = event.target.closest('[data-ap-dropdown]');
                document.querySelectorAll('.ap-dropdown.is-open').forEach(function(node) {
                    if (!toggle || node !== toggle.closest('.ap-dropdown')) node.classList.remove('is-open');
                });
                if (toggle) toggle.closest('.ap-dropdown').classList.toggle('is-open');
            });

            document.addEventListener('submit', function(event) {
                var form = event.target;
                if (form.matches('[data-confirm]') && !window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        })();
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH /var/www/html/resources/views/layouts/admin.blade.php ENDPATH**/ ?>