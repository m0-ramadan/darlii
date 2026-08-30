<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), config('locales.rtl')) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('themes/homzen/images/logo/favicon.png') }}">
    <title>@yield('title', 'Darlii | دارلي - ' . (app()->getLocale() === 'ar' ? 'مكانك يبدأ هنا' : 'Your Place Starts Here'))</title>
    <meta name="description" content="@yield('meta_description', app()->getLocale() === 'ar' ? 'منصة دارلي - السوق العقاري الأبرز في مصر لشراء وبيع وتأجير العقارات والمشروعات' : 'Darlii - Egypt\'s premier real estate platform for buying, selling, and renting properties and developments.')">

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
        href="{{ asset(in_array(app()->getLocale(), config('locales.rtl')) ? 'themes/homzen/plugins/bootstrap/css/bootstrap.rtl.min.css' : 'themes/homzen/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/darli-design-system.css') }}?v=1.1.0">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/style.css') }}?v=2.0.1">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/dark-mode.css') }}?v=2.0.1">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/rtl-custom.css') }}?v=2.0.1">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/locale-switcher.css') }}?v=1.0.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="darli-body">
    <!-- Darlii Main Header / Navbar -->
    <nav class="navbar navbar-expand-lg darli-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand me-4 ms-0" href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                @php
                    $isRtl = in_array(app()->getLocale(), config('locales.rtl'));
                    $defaultLogo = $isRtl
                        ? 'themes/homzen/images/logo/darli-logo-ar.png'
                        : 'themes/homzen/images/logo/darli-logo-en.png';
                @endphp
                <img src="{{ asset($defaultLogo) }}" alt="Darlii دارلي" class="darli-logo-img">
            </a>

            <div class="d-flex align-items-center gap-2 order-lg-3">
                <!-- Theme Toggle Button -->
                <button class="darli-theme-toggle me-1 ms-1" type="button" aria-label="Toggle theme"></button>

                <!-- Language Switcher -->
                <div class="dropdown">
                    <button
                        class="btn btn-darli-secondary btn-sm-custom dropdown-toggle d-inline-flex align-items-center gap-1"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i data-lucide="globe" style="width: 16px; height: 16px;"></i>
                        <span>{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        @foreach (config('locales.names') as $code => $name)
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between @if ($code === app()->getLocale()) active @endif"
                                    href="{{ route('locale.switch', ['locale' => $code, 'redirect' => request()->getRequestUri()]) }}"
                                    lang="{{ $code }}" hreflang="{{ $code }}">
                                    <span>{{ $name }}</span>
                                    @if ($code === app()->getLocale())
                                        <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                @auth
                    <a class="btn btn-darli-primary btn-sm-custom d-none d-sm-inline-flex"
                        href="{{ route('dashboard.properties.index', ['locale' => app()->getLocale()]) }}">
                        <i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i>
                        <span>{{ app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard' }}</span>
                    </a>
                    <form method="post" action="{{ route('logout', ['locale' => app()->getLocale()]) }}" class="d-inline">
                        @csrf
                        <button class="btn btn-darli-secondary btn-sm-custom ms-1">
                            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        </button>
                    </form>
                @else
                    <a class="btn btn-darli-secondary btn-sm-custom"
                        href="{{ route('login', ['locale' => app()->getLocale()]) }}">
                        <i data-lucide="user" style="width: 16px; height: 16px;"></i>
                        <span class="d-none d-sm-inline">{{ __('messages.login') }}</span>
                    </a>
                    <a class="btn btn-darli-primary btn-sm-custom d-none d-md-inline-flex"
                        href="{{ route('login', ['locale' => app()->getLocale()]) }}">
                        <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                        <span>{{ app()->getLocale() === 'ar' ? 'أضف عقارك' : 'Add Property' }}</span>
                    </a>
                @endauth

                <button class="navbar-toggler border-0 shadow-none p-1 ms-2" type="button" data-bs-toggle="collapse"
                    data-bs-target="#darliNav" aria-controls="darliNav" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <i data-lucide="menu" style="width: 24px; height: 24px; color: var(--text-primary);"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="darliNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link darli-nav-link @if (request()->routeIs('home')) active @endif"
                            href="{{ route('home', ['locale' => app()->getLocale()]) }}">
                            {{ app()->getLocale() === 'ar' ? 'الرئيسية' : 'Home' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link darli-nav-link @if (request()->routeIs('properties.*')) active @endif"
                            href="{{ route('properties.index', ['locale' => app()->getLocale()]) }}">
                            {{ __('messages.properties') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link darli-nav-link @if (request()->routeIs('projects.*')) active @endif"
                            href="{{ route('projects.index', ['locale' => app()->getLocale()]) }}">
                            {{ __('messages.projects') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link darli-nav-link" href="{{ url('/' . app()->getLocale() . '/agents') }}">
                            {{ __('messages.agents') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link darli-nav-link @if (request()->routeIs('blog.*')) active @endif"
                            href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}">
                            {{ __('messages.blog') }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center gap-2"
                role="alert">
                <i data-lucide="check-circle-2" style="width: 20px; height: 20px; color: var(--color-success);"></i>
                <div>{{ session('success') }}</div>
            </div>
        </div>
    @endif

    <main class="darli-main">
        @yield('content')
    </main>

    <!-- Darlii Unified Footer -->
    <footer class="darli-footer">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    @php
                        $footerLogo = $isRtl
                            ? 'themes/homzen/images/logo/darli-logo-ar-dark.png'
                            : 'themes/homzen/images/logo/darli-logo-en-dark.png';
                    @endphp
                    <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="d-inline-block mb-3">
                        <img src="{{ asset($footerLogo) }}" alt="Darlii دارلي" class="darli-logo-img"
                            style="height: 52px;">
                    </a>
                    <p class="text-secondary-muted body-sm mb-4" style="max-width: 340px;">
                        {{ app()->getLocale() === 'ar' ? 'منصة دارلي هي وجهتك الأولى للبحث عن أفضل العقارات والمشروعات السكنية والتجارية في جميع أنحاء جمهورية مصر العربية.' : 'Darlii is your premier destination for finding top residential and commercial properties and developments across Egypt.' }}
                    </p>
                    <div class="d-flex align-items-center gap-3">
                        <a href="#" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                            aria-label="Facebook"><i data-lucide="facebook"
                                style="width: 18px; height: 18px;"></i></a>
                        <a href="#" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                            aria-label="Instagram"><i data-lucide="instagram"
                                style="width: 18px; height: 18px;"></i></a>
                        <a href="#" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                            aria-label="Twitter"><i data-lucide="twitter" style="width: 18px; height: 18px;"></i></a>
                        <a href="#" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                            aria-label="LinkedIn"><i data-lucide="linkedin"
                                style="width: 18px; height: 18px;"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-6">
                    <h6 class="footer-title">{{ app()->getLocale() === 'ar' ? 'روابط سريعة' : 'Quick Links' }}</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 body-sm mb-0">
                        <li><a
                                href="{{ route('properties.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.properties') }}</a>
                        </li>
                        <li><a
                                href="{{ route('projects.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.projects') }}</a>
                        </li>
                        <li><a href="{{ url('/' . app()->getLocale() . '/agents') }}">{{ __('messages.agents') }}</a>
                        </li>
                        <li><a
                                href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}">{{ __('messages.blog') }}</a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-6">
                    <h6 class="footer-title">{{ app()->getLocale() === 'ar' ? 'أنواع العقارات' : 'Property Types' }}
                    </h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 body-sm mb-0">
                        <li><a
                                href="{{ route('properties.index', ['locale' => app()->getLocale(), 'type' => 'apartment']) }}">{{ app()->getLocale() === 'ar' ? 'شقق للبيع' : 'Apartments for Sale' }}</a>
                        </li>
                        <li><a
                                href="{{ route('properties.index', ['locale' => app()->getLocale(), 'type' => 'villa']) }}">{{ app()->getLocale() === 'ar' ? 'فيلات مستقلة' : 'Standalone Villas' }}</a>
                        </li>
                        <li><a
                                href="{{ route('properties.index', ['locale' => app()->getLocale(), 'type' => 'duplex']) }}">{{ app()->getLocale() === 'ar' ? 'دوبلكس وتاون هاوس' : 'Duplex & Townhouse' }}</a>
                        </li>
                        <li><a
                                href="{{ route('properties.index', ['locale' => app()->getLocale(), 'type' => 'commercial']) }}">{{ app()->getLocale() === 'ar' ? 'مكاتب ومحلات تجارية' : 'Commercial & Offices' }}</a>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-title">{{ app()->getLocale() === 'ar' ? 'تواصل معنا' : 'Contact Us' }}</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 body-sm mb-3">
                        <li class="d-flex align-items-center gap-2"><i data-lucide="map-pin"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <span>{{ app()->getLocale() === 'ar' ? 'القاهرة، جمهورية مصر العربية' : 'Cairo, Arab Republic of Egypt' }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-2"><i data-lucide="phone"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i> <span
                                dir="ltr">+20 100 000 0000</span></li>
                        <li class="d-flex align-items-center gap-2"><i data-lucide="mail"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <span>info@Darlii.eg</span></li>
                    </ul>
                </div>
            </div>

            <div class="darli-footer-divider"></div>

            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 body-sm">
                <div>
                    &copy; {{ date('Y') }} <strong>Darlii دارلي</strong>.
                    {{ app()->getLocale() === 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}
                </div>
                <div class="d-flex align-items-center gap-3">
                    @foreach (config('locales.names') as $code => $name)
                        <a href="{{ route('locale.switch', ['locale' => $code, 'redirect' => request()->getRequestUri()]) }}"
                            class="text-decoration-none @if ($code === app()->getLocale()) fw-bold text-warning @else opacity-75 @endif">
                            {{ $name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('themes/homzen/js/jquery.min.js') }}"></script>
    <script src="{{ asset('themes/homzen/js/popper.min.js') }}"></script>
    <script src="{{ asset('themes/homzen/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script defer src="{{ asset('themes/homzen/js/theme-mode.js') }}?v=2.0.1"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
