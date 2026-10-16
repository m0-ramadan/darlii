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

    {{-- Dark mode is temporarily disabled. Keep the site explicitly in light mode. --}}
    <script>document.documentElement.dataset.theme = 'light';</script>

    <link rel="stylesheet"
        href="{{ asset(in_array(app()->getLocale(), config('locales.rtl')) ? 'themes/homzen/plugins/bootstrap/css/bootstrap.rtl.min.css' : 'themes/homzen/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/darli-design-system.css') }}?v=2.0.3">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/style.css') }}?v=2.0.3">
    {{-- <link rel="stylesheet" href="{{ asset('themes/homzen/css/dark-mode.css') }}?v=2.0.3"> --}}
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/rtl-custom.css') }}?v=2.0.3">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/locale-switcher.css') }}?v=1.0.0">
    {{-- Brand unification layer: same header/footer colours as the legacy pages. --}}
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/darli-unified.css') }}?v=2.1.8">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="darli-body">
    {!! app(\App\Services\LegacyFrontend\LegacyFrontendRenderer::class)->siteHeaderHtml(app()->getLocale()) !!}
    {{-- <button class="darli-theme-toggle" id="theme-toggle-btn" type="button" style="display:none;" aria-label="Toggle theme"></button> --}}

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
                        // $isRtl is set in the navbar block above, but this layout can also be
                        // rendered by views that set their own sections, so default it here.
                        $isRtl = $isRtl ?? in_array(app()->getLocale(), config('locales.rtl'), true);
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
                    {{--
                        Brand icons were dropped from the lucide icon set, so
                        these are inlined (same Tabler paths the legacy footer
                        uses) to guarantee the footer renders identically on
                        every page.
                    --}}
                    <div class="d-flex align-items-center gap-3">
                        @if ($fb = setting('contact.facebook', 'https://facebook.com'))
                            <a href="{{ $fb }}" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="Facebook">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
                                </svg>
                            </a>
                        @endif
                        @if ($tw = setting('contact.twitter', 'https://x.com'))
                            <a href="{{ $tw }}" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="X (Twitter)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 4l11.733 16h4.267l-11.733 -16z" />
                                    <path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772" />
                                </svg>
                            </a>
                        @endif
                        @if ($yt = setting('contact.youtube', 'https://youtube.com'))
                            <a href="{{ $yt }}" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
                                aria-label="YouTube">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path
                                        d="M2 8a4 4 0 0 1 4 -4h12a4 4 0 0 1 4 4v8a4 4 0 0 1 -4 4h-12a4 4 0 0 1 -4 -4v-8z" />
                                    <path d="M10 9l5 3l-5 3z" />
                                </svg>
                            </a>
                        @endif
                        @if ($li = setting('contact.linkedin', 'https://linkedin.com'))
                            <a href="{{ $li }}" target="_blank" rel="noopener noreferrer" class="p-2 rounded-circle bg-body-tertiary d-inline-flex"
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
                        @endif
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
                            <span>{{ setting(app()->getLocale() === 'ar' ? 'contact.address_ar' : 'contact.address_en', app()->getLocale() === 'ar' ? 'شارع صلاح سالم (تقاطع كورنيش النيل)، مدينة بني سويف' : 'Salah Salem St, Beni Suef City, Egypt') }}</span>
                        </li>
                        <li class="d-flex align-items-center gap-2"><i data-lucide="phone"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', setting('contact.phone', '+20 10 34905812')) }}" style="color:inherit;text-decoration:none;" dir="ltr">{{ setting('contact.phone', '+20 10 34905812') }}</a>
                        </li>
                        <li class="d-flex align-items-center gap-2"><i data-lucide="mail"
                                style="width: 16px; height: 16px; color: var(--brand-gold);"></i>
                            <a href="mailto:{{ setting('contact.email', 'info@darlii.eg') }}" style="color:inherit;text-decoration:none;">{{ setting('contact.email', 'info@darlii.eg') }}</a>
                        </li>
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

    @include('components.floating-whatsapp')
    @include('components.floating-notifications')

    <script src="{{ asset('themes/homzen/js/jquery.min.js') }}"></script>
    <script src="{{ asset('themes/homzen/js/popper.min.js') }}"></script>
    <script src="{{ asset('themes/homzen/plugins/bootstrap/js/bootstrap.min.js') }}"></script>
    <script defer src="{{ asset('themes/homzen/js/darli-mobile-menu.js') }}?v=1.1.2"></script>
    {{-- <script defer src="{{ asset('themes/homzen/js/theme-mode.js') }}?v=2.0.2"></script> --}}

    @if(setting('firebase.enabled', '1') == '1')
        <script>
            window.DarliFirebaseConfig = {
                enabled: true,
                apiKey: "{{ setting('firebase.api_key', 'AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q') }}",
                authDomain: "{{ setting('firebase.auth_domain', 'khademate2.firebaseapp.com') }}",
                projectId: "{{ setting('firebase.project_id', 'khademate2') }}",
                storageBucket: "{{ setting('firebase.storage_bucket', 'khademate2.firebasestorage.app') }}",
                messagingSenderId: "{{ setting('firebase.messaging_sender_id', '504226887841') }}",
                appId: "{{ setting('firebase.app_id', '1:504226887841:web:cbc5abdc30304c6014d8e0') }}",
                measurementId: "{{ setting('firebase.measurement_id', 'G-01HJBBQZP7') }}",
                vapidKey: "{{ setting('firebase.vapid_public_key', '') }}",
                userId: {{ auth()->id() ?? 'null' }}
            };
        </script>
        <script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-messaging-compat.js"></script>
        <script defer src="{{ asset('themes/homzen/js/darli-notifications.js') }}?v=1.0.1"></script>
    @endif
    <script defer src="{{ asset('themes/homzen/js/darli-testimonials-drag.js') }}?v=1.0.0"></script>
    <script defer src="{{ asset('themes/homzen/js/darli-visitor-tracker.js') }}?v=1.0.0"></script>

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
