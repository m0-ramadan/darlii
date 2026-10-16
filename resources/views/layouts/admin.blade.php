@php
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
                [
                    'بانتظار المراجعة',
                    'admin.properties.pending',
                    'clipboard',
                    'admin.properties.pending',
                    'property.approve',
                ],
                [
                    'العقارات المميزة',
                    'admin.properties.featured',
                    'star',
                    'admin.properties.featured',
                    'property.feature',
                ],
                [
                    'إسناد العقارات',
                    'admin.properties.assignments',
                    'users',
                    'admin.properties.assignments',
                    'property.assign',
                ],
            ],
        ],
        [
            'title' => 'المشروعات',
            'items' => [['كل المشروعات', 'admin.projects.index', 'building', 'admin.projects.*', 'project.view']],
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
                ['الأسئلة الشائعة', 'admin.faqs.index', 'help-circle', 'admin.faqs.*', 'content.manage'],
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
            'title' => 'الإشعارات والتواصل',
            'items' => [
                ['الإشعارات', 'admin.notifications.index', 'bell', 'admin.notifications.*', 'settings.manage'],
                ['إرسال إشعار', 'admin.notifications.create', 'send', 'admin.notifications.create', 'settings.manage'],
                [
                    'إعدادات Firebase',
                    'admin.notifications.settings',
                    'plug',
                    'admin.notifications.settings',
                    'settings.manage',
                ],
                ['العملاء المحتملون', 'admin.leads.index', 'message', 'admin.leads.*', null],
                ['المواعيد', 'admin.appointments.index', 'clipboard', 'admin.appointments.*', 'appointment.manage'],
                ['التقييمات', 'admin.reviews.index', 'star', 'admin.reviews.*', 'review.moderate'],
            ],
        ],
        [
            'title' => 'الإعدادات',
            'items' => [
                ['الإعدادات العامة', 'admin.settings.general', 'settings', 'admin.settings.*', 'settings.manage'],
                [
                    'التكاملات',
                    'admin.settings.integrations',
                    'plug',
                    'admin.settings.integrations',
                    'settings.integrations',
                ],
                ['سجل النشاط', 'admin.audit.index', 'activity', 'admin.audit.*', 'audit.view'],
            ],
        ],
    ];
@endphp
<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') | {{ $siteName }}</title>
    <link rel="icon" type="image/png"
        href="{{ str_starts_with((string) $settings->get('branding.favicon', ''), 'http') ? $settings->get('branding.favicon') : asset($settings->get('branding.favicon', 'themes/homzen/images/logo/favicon.png')) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('themes/homzen/plugins/bootstrap/css/bootstrap.rtl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/admin-panel.css') }}?v=1.0.2">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/dark-mode.css') }}?v=2.0.3">
    <style>
        :root {
            --ap-primary: {{ $panelPrimary }};
            --ap-accent: {{ $panelAccent }};
        }
    </style>
    @stack('head')
</head>

<body class="ap-body">
    <div class="ap-shell">
        {{-- ------------------------------------------------------- السايدبار --}}
        <aside class="ap-sidebar" id="apSidebar">
            <div class="ap-sidebar__brand">
                <a href="{{ admin_route('admin.dashboard') }}" class="ap-brand">
                    <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="ap-brand__logo">
                </a>
                <button type="button" class="ap-sidebar__close" data-ap-sidebar-close aria-label="إغلاق">
                    @include('admin.partials.icon', ['iconName' => 'x', 'size' => 18])
                </button>
            </div>

            <div class="ap-sidebar__user">
                <img src="{{ $user?->avatar_url }}" alt="{{ $user?->name }}" class="ap-avatar">
                <div class="ap-sidebar__user-meta">
                    <span class="ap-sidebar__user-name">{{ $user?->name }}</span>
                    <span class="ap-sidebar__user-role">{{ user_role_label($user?->primary_role_slug) }}</span>
                </div>
            </div>

            <nav class="ap-nav">
                @foreach ($groups as $group)
                    @php
                        $visibleItems = collect($group['items'])->filter(
                            fn($item) => $item[4] === null || ($user && $user->hasPermission($item[4])),
                        );
                    @endphp
                    @if ($visibleItems->isNotEmpty())
                        <div class="ap-nav__group">
                            <span class="ap-nav__title">{{ $group['title'] }}</span>
                            @foreach ($visibleItems as $item)
                                @php $isActive = str_ends_with($item[3], '*') ? str_starts_with($current, rtrim($item[3], '*')) : $current === $item[3]; @endphp
                                <a href="{{ admin_route($item[1]) }}"
                                    class="ap-nav__link @if ($isActive) is-active @endif">
                                    @include('admin.partials.icon', ['iconName' => $item[2], 'size' => 18])
                                    <span>{{ $item[0] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </nav>

            <div class="ap-sidebar__footer">
                <a href="{{ route('home', ['locale' => 'ar']) }}" target="_blank" class="ap-nav__link">
                    @include('admin.partials.icon', ['iconName' => 'external', 'size' => 18])
                    <span>عرض الموقع</span>
                </a>
            </div>
        </aside>

        {{-- -------------------------------------------------------- المحتوى --}}
        <div class="ap-main">
            <header class="ap-topbar">
                <button type="button" class="ap-topbar__burger" data-ap-sidebar-open aria-label="القائمة">
                    @include('admin.partials.icon', ['iconName' => 'menu', 'size' => 22])
                </button>

                <div class="ap-topbar__titles">
                    <h1 class="ap-topbar__title">@yield('page_title', 'لوحة التحكم')</h1>
                    <p class="ap-topbar__subtitle">@yield('page_subtitle', 'إدارة كاملة للمنصة العقارية')</p>
                </div>

                <div class="ap-topbar__actions">
                    @yield('page_actions')
                    <a href="{{ admin_route('admin.notifications.index') }}"
                        class="btn btn-sm btn-outline-secondary position-relative me-1 ms-1 d-inline-flex align-items-center justify-content-center"
                        style="width: 36px; height: 36px; padding: 0;" title="الإشعارات">
                        @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 17])
                        @php
                            $adminUnreadNotifs = \Illuminate\Support\Facades\Schema::hasTable('site_notifications')
                                ? \App\Models\SiteNotification::unread()->count()
                                : 0;
                        @endphp
                        @if ($adminUnreadNotifs > 0)
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="font-size: 10px; padding: 2px 5px;">
                                {{ $adminUnreadNotifs > 99 ? '99+' : $adminUnreadNotifs }}
                            </span>
                        @endif
                    </a>
                    <button class="darli-theme-toggle btn btn-sm btn-outline-secondary me-2 ms-2" type="button"
                        aria-label="Toggle theme"></button>
                    <div class="ap-dropdown">
                        <button type="button" class="ap-topbar__user" data-ap-dropdown>
                            <img src="{{ $user?->avatar_url }}" alt="{{ $user?->name }}"
                                class="ap-avatar ap-avatar--sm">
                            <span class="d-none d-md-inline">{{ $user?->name }}</span>
                            @include('admin.partials.icon', ['iconName' => 'chevron-down', 'size' => 16])
                        </button>
                        <div class="ap-dropdown__menu">
                            <a href="{{ admin_route('admin.profile.edit') }}" class="ap-dropdown__item">
                                @include('admin.partials.icon', ['iconName' => 'users', 'size' => 16]) الملف الشخصي
                            </a>
                            @if ($user?->hasPermission('settings.manage'))
                                <a href="{{ admin_route('admin.settings.general') }}" class="ap-dropdown__item">
                                    @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 16]) الإعدادات
                                </a>
                            @endif
                            <form method="post" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="ap-dropdown__item ap-dropdown__item--danger">
                                    @include('admin.partials.icon', ['iconName' => 'logout', 'size' => 16]) تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="ap-content">
                @if (session('success'))
                    <div class="ap-alert ap-alert--success">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 18])
                        <span>{{ session('success') }}</span>
                        <button type="button" class="ap-alert__close" data-ap-dismiss>&times;</button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="ap-alert ap-alert--danger">
                        @include('admin.partials.icon', ['iconName' => 'alert', 'size' => 18])
                        <span>{{ session('error') }}</span>
                        <button type="button" class="ap-alert__close" data-ap-dismiss>&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="ap-alert ap-alert--danger">
                        @include('admin.partials.icon', ['iconName' => 'alert', 'size' => 18])
                        <div>
                            <strong>يوجد {{ $errors->count() }} خطأ في البيانات:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="ap-alert__close" data-ap-dismiss>&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="ap-footer">
                <span>&copy; {{ date('Y') }} {{ $siteName }} — كل الحقوق محفوظة.</span>
                <span class="ap-footer__meta">لوحة تحكم دارلي · الإصدار 1.0</span>
            </footer>
        </div>
    </div>

    <div class="ap-backdrop" data-ap-sidebar-close></div>

    <script src="{{ asset('themes/homzen/js/jquery.min.js') }}"></script>
    <script src="{{ asset('themes/homzen/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script>
        (function() {
            document.addEventListener('click', function(event) {
                if (event.target.closest('[data-ap-sidebar-open]')) {
                    document.body.classList.add('ap-sidebar-open');
                }
                if (event.target.closest('[data-ap-sidebar-close]')) {
                    document.body.classList.remove('ap-sidebar-open');
                }
                if (window.innerWidth <= 992 && event.target.closest('.ap-sidebar .ap-nav__link')) {
                    document.body.classList.remove('ap-sidebar-open');
                }
                if (event.target.closest('[data-ap-dismiss]')) {
                    var alert = event.target.closest('.ap-alert');
                    if (alert) alert.remove();
                }
                var toggle = event.target.closest('[data-ap-dropdown]');
                document.querySelectorAll('.ap-dropdown.is-open').forEach(function(node) {
                    if (!toggle || node !== toggle.closest('.ap-dropdown')) node.classList.remove(
                        'is-open');
                });
                if (toggle) toggle.closest('.ap-dropdown').classList.toggle('is-open');
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    document.body.classList.remove('ap-sidebar-open');
                }
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    document.body.classList.remove('ap-sidebar-open');
                }
            });

            document.addEventListener('submit', function(event) {
                var form = event.target;
                if (form.matches('[data-confirm]') && !window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>

</html>
