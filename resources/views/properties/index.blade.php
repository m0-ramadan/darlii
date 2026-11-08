@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $filters = request()->only([
        'q',
        'k',
        'purpose',
        'category_id',
        'category_group',
        'student',
        'location_id',
        'sort',
        'min_price',
        'max_price',
        'bedrooms',
        'bathrooms',
        'furnished',
        'nearby',
        'nearby_location_id',
        'features',
    ]);
    $searchTerm = $filters['q'] ?? ($filters['k'] ?? '');
    $contactPhone = (string) setting('contact.phone', '+20 10 34905812');
    $contactPhoneDigits = preg_replace('/\D+/', '', $contactPhone);

    // Check if any filter is active or if user explicitly browsed all
    $hasActiveQuery =
        request()->hasAny([
            'purpose',
            'category_id',
            'category_group',
            'student',
            'location_id',
            'q',
            'k',
            'min_price',
            'max_price',
            'bedrooms',
            'bathrooms',
            'furnished',
            'nearby',
            'nearby_location_id',
            'features',
        ]) || request('browse') === 'all';
    // The discovery wizard is an entry step, not part of paginated browsing.
    // Never reopen it when the visitor moves to page 2+.
    $showWizard = !$hasActiveQuery && !request()->has('page');

    // Label for active filter badge on mobile listing
    $activeFilterLabel = null;
    $currPurpose = request('purpose');
    $currGroup = request('category_group');
    $currCat = request('category_id');
    $isStudent = request('student');

    if ($currPurpose === 'sale') {
        if ($currGroup === 'residential') {
            $activeFilterLabel = $isAr ? 'للبيع • تمليك سكني' : 'For Sale • Residential';
        } elseif ($currCat == 6) {
            $activeFilterLabel = $isAr ? 'للبيع • تمليك تجاري' : 'For Sale • Commercial';
        } elseif ($currCat == 5) {
            $activeFilterLabel = $isAr ? 'للبيع • أراضي واستثمار' : 'For Sale • Lands & Investment';
        } else {
            $activeFilterLabel = $isAr ? 'عقارات للبيع' : 'Properties for Sale';
        }
    } elseif ($currPurpose === 'rent') {
        if ($currGroup === 'residential') {
            $activeFilterLabel = $isAr ? 'للإيجار • الإيجار السكني' : 'For Rent • Residential';
        } elseif ($isStudent) {
            $activeFilterLabel = $isAr ? 'للإيجار • الإيجار الطلابي' : 'For Rent • Student Housing';
        } elseif ($currCat == 6) {
            $activeFilterLabel = $isAr ? 'للإيجار • الإيجار التجاري' : 'For Rent • Commercial';
        } else {
            $activeFilterLabel = $isAr ? 'عقارات للإيجار' : 'Properties for Rent';
        }
    }
@endphp

@push('head')
    {{-- Load the split-map styles in the <head> so the sidebar/list layout is
         correct on the first paint instead of jumping after the content loads. --}}
    @if (! str_contains($shell['head'] ?? '', 'plugins/leaflet/leaflet.css'))
        <link rel="stylesheet" href="{{ asset('themes/darlii/plugins/leaflet/leaflet.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('themes/darlii/css/darli-properties-map.min.css') }}?v=1.0.16">
@endpush

@section('content')
    <div class="darli-properties-page">

        {{-- ========================================================
             MOBILE PROPERTY DISCOVERY WIZARD (MATCHES USER MOCKUP)
             ======================================================== --}}
        <div id="darliMobileWizard" class="darli-mobile-wizard {{ $showWizard ? 'is-active' : '' }}">
            <style>
                @media (max-width: 991px) {

                    body:has(#darliMobileWizard.is-active) .site-notice,
                    body:has(#darliMobileWizard.is-active) .js-site-notice,
                    body:has(#darliMobileWizard.is-active) [class*="site-notice"],
                    body:has(#darliMobileWizard.is-active) #darli-floating-wa,
                    body:has(#darliMobileWizard.is-active) .darli-floating-wa,
                    body:has(#darliMobileWizard.is-active) #cookie-consent,
                    body:has(#darliMobileWizard.is-active) .cookie-consent,
                    body:has(#darliMobileWizard.is-active) .cookie-notice,
                    body:has(#darliMobileWizard.is-active) .whatsapp-button-wrapper,
                    body.darli-wizard-open .site-notice,
                    body.darli-wizard-open .js-site-notice,
                    body.darli-wizard-open [class*="site-notice"],
                    body.darli-wizard-open #darli-floating-wa,
                    body.darli-wizard-open .darli-floating-wa,
                    body.darli-wizard-open #cookie-consent,
                    body.darli-wizard-open .cookie-consent,
                    body.darli-wizard-open .cookie-notice,
                    body.darli-wizard-open .whatsapp-button-wrapper {
                        display: none !important;
                    }
                }
            </style>
            {{-- Background Skyline Silhouette --}}
            <div class="darli-wizard-bg-pattern" aria-hidden="true">
                <svg viewBox="0 0 800 600" preserveAspectRatio="xMidYBottom meet" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 450 C 150 430, 250 460, 400 440 C 550 420, 680 450, 800 430 L 800 600 L 0 600 Z"
                        fill="#e4edf7" opacity="0.6" />
                    <circle cx="45" cy="410" r="28" fill="#d9e6f3" opacity="0.7" />
                    <rect x="42" y="425" width="6" height="50" fill="#cbdbe9" />
                    <circle cx="75" cy="425" r="22" fill="#d9e6f3" opacity="0.8" />
                    <polygon points="110,340 180,300 250,340" fill="#d3e2f0" />
                    <rect x="125" y="340" width="110" height="150" fill="#dce8f5" />
                    <rect x="140" y="360" width="30" height="35" rx="3" fill="#ffffff" opacity="0.9" />
                    <rect x="185" y="360" width="30" height="35" rx="3" fill="#ffffff" opacity="0.9" />
                    <rect x="140" y="415" width="30" height="35" rx="3" fill="#ffffff" opacity="0.9" />
                    <rect x="185" y="415" width="30" height="35" rx="3" fill="#ffffff" opacity="0.9" />
                    <polygon points="215,290 230,290 230,325 215,315" fill="#cadbf0" />
                    <rect x="250" y="370" width="85" height="120" fill="#d3e2f0" />
                    <polygon points="250,370 292,345 335,370" fill="#c7d9ec" />
                    <rect x="265" y="390" width="22" height="26" rx="2" fill="#ffffff" opacity="0.85" />
                    <rect x="295" y="390" width="22" height="26" rx="2" fill="#ffffff" opacity="0.85" />
                    <rect x="280" y="440" width="24" height="50" rx="3" fill="#ffffff" opacity="0.75" />
                    <rect x="350" y="310" width="110" height="180" fill="#d7e5f3" />
                    <rect x="370" y="330" width="25" height="30" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="415" y="330" width="25" height="30" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="370" y="380" width="25" height="30" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="415" y="380" width="25" height="30" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="370" y="430" width="25" height="30" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="415" y="430" width="25" height="30" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="480" y="240" width="125" height="250" fill="#cee0f1" />
                    <polygon points="480,240 542,210 605,240" fill="#c3d8ec" />
                    <g fill="#ffffff" opacity="0.85">
                        <rect x="500" y="260" width="22" height="26" rx="2" />
                        <rect x="535" y="260" width="22" height="26" rx="2" />
                        <rect x="570" y="260" width="22" height="26" rx="2" />
                        <rect x="500" y="305" width="22" height="26" rx="2" />
                        <rect x="535" y="305" width="22" height="26" rx="2" />
                        <rect x="570" y="305" width="22" height="26" rx="2" />
                        <rect x="500" y="350" width="22" height="26" rx="2" />
                        <rect x="535" y="350" width="22" height="26" rx="2" />
                        <rect x="570" y="350" width="22" height="26" rx="2" />
                        <rect x="500" y="395" width="22" height="26" rx="2" />
                        <rect x="535" y="395" width="22" height="26" rx="2" />
                        <rect x="570" y="395" width="22" height="26" rx="2" />
                    </g>
                    <polygon points="620,350 690,305 760,350" fill="#d2e1f0" />
                    <rect x="635" y="350" width="110" height="140" fill="#d9e7f5" />
                    <rect x="650" y="370" width="28" height="32" rx="2" fill="#ffffff" opacity="0.9" />
                    <rect x="695" y="370" width="28" height="32" rx="2" fill="#ffffff" opacity="0.9" />
                    <circle cx="765" cy="410" r="25" fill="#d8e5f2" opacity="0.75" />
                </svg>
            </div>

            {{-- Top Bar: Hamburger + Logo --}}
            <header class="darli-wizard-topbar">
                <button type="button" class="darli-wizard-menu-btn" aria-label="القائمة">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="darli-wizard-header-actions">
                    <a href="{{ auth()->check() ? route('dashboard.properties.create') : route('login') }}"
                        class="darli-wizard-add-property">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        <span>{{ $isAr ? 'إضافة عقار' : 'Add property' }}</span>
                    </a>
                    <button type="button" class="darli-wizard-notifications-btn" id="wizardNotificationsBtn"
                        aria-label="{{ $isAr ? 'الإشعارات' : 'Notifications' }}"
                        title="{{ $isAr ? 'الإشعارات' : 'Notifications' }}">
                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M10 5a2 2 0 1 1 4 0 7 7 0 0 1 4 6v3a4 4 0 0 0 2 3H4a4 4 0 0 0 2-3v-3a7 7 0 0 1 4-6" />
                            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                        </svg>
                        <span class="darli-notif-badge" style="display: none;">0</span>
                    </button>
                </div>
                <a href="{{ url($locale) }}" class="darli-wizard-brand">
                    <img src="{{ asset('themes/darlii/images/logo/darli-logo-ar.png') }}" alt="Darlii دارلي"
                        class="darli-wizard-logo">
                </a>
            </header>

            {{-- Wizard Body --}}
            <div class="darli-wizard-body">

                {{-- ---------------- SCREEN 1: عند فتح صفحة العقارات ---------------- --}}
                <div class="darli-wizard-step step-main" id="wizardStepMain">
                    <div class="darli-wizard-heading">
                        <h2 class="darli-wizard-title">
                            {{ $isAr ? 'ماذا تبحث عنه اليوم ؟' : 'What are you looking for today?' }}</h2>
                        <p class="darli-wizard-subtitle">
                            {{ $isAr ? 'اختر نوع العملية العقارية التي تناسبك' : 'Choose the real estate transaction that suits you' }}
                        </p>
                    </div>

                    <div class="darli-wizard-circles-col">
                        {{-- للبيع (Dark Navy) --}}
                        <button type="button" class="darli-choice-circle circle-sale" id="wizardGoToSaleBtn">
                            <div class="circle-icon-wrap">
                                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 10.5L12 3l9 7.5" />
                                    <path d="M5 9.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5" />
                                    <path d="M9 21v-7a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v7" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'شراء' : 'Buy' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </button>

                        {{-- للإيجار (White with Gold Key) --}}
                        <button type="button" class="darli-choice-circle circle-rent" id="wizardGoToRentBtn">
                            <div class="circle-icon-wrap">
                                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="7.5" cy="15.5" r="4.5" />
                                    <path d="m10.7 12.3 8.3-8.3" />
                                    <path d="m16 7 2 2" />
                                    <path d="m18 5 2 2" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'إيجار' : 'Rent' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </button>
                    </div>

                    <div class="darli-wizard-skip-row">
                        <button type="button" class="darli-wizard-skip-btn" id="wizardSkipBtn">
                            {{ $isAr ? 'تخطي وعرض كافة العقارات' : 'Skip and view all properties' }}
                        </button>
                    </div>
                </div>

                {{-- ---------------- SCREEN 2: عند الضغط على (للبيع) ---------------- --}}
                <div class="darli-wizard-step step-sale" id="wizardStepSale" style="display: none;">
                    {{-- Segmented Pills: للبيع (Active) / للإيجار --}}
                    <div class="darli-segmented-pills">
                        <button type="button" class="darli-seg-pill active"
                            id="pillSaleActive">{{ $isAr ? 'للبيع' : 'For Sale' }}</button>
                        <button type="button" class="darli-seg-pill"
                            id="pillSwitchToRent">{{ $isAr ? 'للإيجار' : 'For Rent' }}</button>
                    </div>

                    <div class="darli-wizard-circles-col sub-col">
                        {{-- 1. تمليك سكني --}}
                        <a href="{{ route('properties.index', ['locale' => $locale, 'purpose' => 'sale', 'category_group' => 'residential']) }}"
                            class="darli-choice-circle circle-sub circle-dark">
                            <div class="circle-icon-wrap">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2" />
                                    <path d="M9 22v-4h6v4" />
                                    <path
                                        d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01M12 6h.01M12 10h.01M12 14h.01" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'تمليك سكني' : 'Residential Sale' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>

                        {{-- 2. تمليك تجاري --}}
                        <a href="{{ route('properties.index', ['locale' => $locale, 'purpose' => 'sale', 'category_id' => 6]) }}"
                            class="darli-choice-circle circle-sub circle-light">
                            <div class="circle-icon-wrap">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0b1f3a"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l2-5h14l2 5" />
                                    <path d="M21 9v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9" />
                                    <path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0" />
                                    <path d="M9 22V13h6v9" stroke="#c99a45" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'تمليك تجاري' : 'Commercial Sale' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>

                        {{-- 3. أراضي واستثمار --}}
                        <a href="{{ route('properties.index', ['locale' => $locale, 'purpose' => 'sale', 'category_id' => 5]) }}"
                            class="darli-choice-circle circle-sub circle-light">
                            <div class="circle-icon-wrap">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0b1f3a"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21" />
                                    <line x1="9" y1="3" x2="9" y2="18" />
                                    <line x1="15" y1="6" x2="15" y2="21" />
                                    <circle cx="12" cy="11" r="2" fill="#c99a45" stroke="#c99a45" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'أراضي واستثمار' : 'Lands & Investment' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>
                    </div>

                    <div class="darli-wizard-back-row">
                        <button type="button" class="darli-wizard-back-btn wizardBackToMain">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>{{ $isAr ? 'الرجوع للخيارات الرئيسية' : 'Back to main options' }}</span>
                        </button>
                    </div>
                </div>

                {{-- ---------------- SCREEN 3: عند الضغط على (للإيجار) ---------------- --}}
                <div class="darli-wizard-step step-rent" id="wizardStepRent" style="display: none;">
                    {{-- Segmented Pills: للبيع / للإيجار (Active) --}}
                    <div class="darli-segmented-pills">
                        <button type="button" class="darli-seg-pill"
                            id="pillSwitchToSale">{{ $isAr ? 'للبيع' : 'For Sale' }}</button>
                        <button type="button" class="darli-seg-pill active"
                            id="pillRentActive">{{ $isAr ? 'للإيجار' : 'For Rent' }}</button>
                    </div>

                    <div class="darli-wizard-circles-col sub-col">
                        {{-- 1. الإيجار السكني --}}
                        <a href="{{ route('properties.index', ['locale' => $locale, 'purpose' => 'rent', 'category_group' => 'residential']) }}"
                            class="darli-choice-circle circle-sub circle-dark">
                            <div class="circle-icon-wrap"
                                style="position: relative; display: inline-flex; align-items: center; justify-content: center;">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 10.5L12 3l9 7.5" />
                                    <path d="M5 9.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5" />
                                    <path d="M9 21v-7a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v7" />
                                </svg>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#c99a45"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    style="position: absolute; right: -5px; bottom: -2px;">
                                    <circle cx="7.5" cy="15.5" r="4.5" />
                                    <path d="m10.7 12.3 8.3-8.3" />
                                    <path d="m16 7 2 2" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'الإيجار السكني' : 'Residential Rent' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>

                        {{-- 2. الإيجار الطلابي --}}
                        <a href="{{ route('properties.index', ['locale' => $locale, 'purpose' => 'rent', 'student' => 1]) }}"
                            class="darli-choice-circle circle-sub circle-light">
                            <div class="circle-icon-wrap"
                                style="position: relative; display: inline-flex; align-items: center; justify-content: center;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0b1f3a"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                                    <path d="M6 12v5c3 3 9 3 12 0v-5" />
                                </svg>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#c99a45"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="position: absolute; right: -5px; bottom: -2px;">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'الإيجار الطلابي' : 'Student Housing' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>

                        {{-- 3. الإيجار التجاري --}}
                        <a href="{{ route('properties.index', ['locale' => $locale, 'purpose' => 'rent', 'category_id' => 6]) }}"
                            class="darli-choice-circle circle-sub circle-light">
                            <div class="circle-icon-wrap">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#0b1f3a"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l2-5h14l2 5" />
                                    <path d="M21 9v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9" />
                                    <path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0" />
                                    <path d="M9 22V13h6v9" stroke="#c99a45" />
                                </svg>
                            </div>
                            <span class="circle-title">{{ $isAr ? 'الإيجار التجاري' : 'Commercial Rent' }}</span>
                            <div class="circle-arrow-badge">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="3" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                        </a>
                    </div>

                    <div class="darli-wizard-back-row">
                        <button type="button" class="darli-wizard-back-btn wizardBackToMain">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                            <span>{{ $isAr ? 'الرجوع للخيارات الرئيسية' : 'Back to main options' }}</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="darli-properties-split-container">

            {{-- ================= 1. Right Content Pane (Listing & Filters) ================= --}}
            <div class="darli-split-content-pane">

                {{-- Mobile Active Filter Bar (When filtered on mobile) --}}
                @if ($activeFilterLabel)
                    <div class="darli-mob-active-filter-bar">
                        <div class="darli-mob-filter-info">
                            <span class="darli-mob-filter-dot"></span>
                            <span class="darli-mob-filter-text">{{ $activeFilterLabel }}</span>
                        </div>
                        <button type="button" class="darli-mob-change-flow-btn" id="openMobileWizardBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            <span>{{ $isAr ? 'تغيير التصنيف' : 'Change' }}</span>
                        </button>
                    </div>
                @endif

                {{-- Header & City Badge --}}
                <div class="darli-prop-header">
                    <div class="darli-prop-title-box">
                        <div class="darli-prop-breadcrumb">
                            <a href="{{ url($locale) }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                            <span class="sep">›</span>
                            <span>{{ $isAr ? 'العقارات' : 'Properties' }}</span>
                        </div>
                        <h1 class="darli-prop-title">
                            {{ $isAr ? 'العقارات في ' . ($currentCity ?? 'بني سويف') : 'Properties in ' . ($currentCity ?? 'Beni Suef') }}
                        </h1>
                        <p class="darli-prop-subtitle">
                            {{ $isAr ? 'اكتشف أفضل العقارات المتاحة للبيع والإيجار في جميع مناطق بني سويف' : 'Discover the best properties available for sale and rent in all areas of Beni Suef' }}
                        </p>
                    </div>

                    <div class="darli-city-badge-card">
                        <div class="darli-city-badge-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-city-badge-info">
                            <span
                                class="darli-city-badge-name">{{ $currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef') }}</span>
                            <span class="darli-city-badge-count">{{ number_format($properties->total()) }}
                                {{ $isAr ? 'عقار متاح' : 'properties' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Horizontal Filter Bar --}}
                <div class="darli-prop-filter-card">
                    <form id="darli-filter-form" method="get"
                        action="{{ route('properties.index', ['locale' => $locale]) }}" class="darli-filter-form">
                        <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">

                        {{-- Search Input --}}
                        <div class="darli-filter-field search-field">
                            <label class="darli-filter-label">{{ $isAr ? 'البحث' : 'Search' }}</label>
                            <div class="darli-input-icon-wrap">
                                <input type="search" name="q" value="{{ $searchTerm }}"
                                    class="darli-filter-input"
                                    placeholder="{{ $isAr ? 'اسم العقار أو الكود...' : 'Property name or code...' }}">
                                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15"
                                    height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                        </div>

                        {{-- Purpose Select --}}
                        <div class="darli-filter-field">
                            <label class="darli-filter-label">{{ $isAr ? 'الغرض' : 'Purpose' }}</label>
                            <select name="purpose" class="darli-filter-select" aria-label="الغرض">
                                <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                                <option value="sale" @selected(($filters['purpose'] ?? '') === 'sale')>{{ $isAr ? 'للبيع' : 'For sale' }}
                                </option>
                                <option value="rent" @selected(($filters['purpose'] ?? '') === 'rent')>{{ $isAr ? 'للإيجار' : 'For rent' }}
                                </option>
                            </select>
                        </div>

                        {{-- Category Select --}}
                        <div class="darli-filter-field">
                            <label class="darli-filter-label">{{ $isAr ? 'النوع' : 'Type' }}</label>
                            <select name="category_id" class="darli-filter-select" aria-label="التصنيف">
                                <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                        {{ $category->translation($locale)?->name ?? $category->key }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Location Select --}}
                        <div class="darli-filter-field">
                            <label class="darli-filter-label">{{ $isAr ? 'المنطقة' : 'Area' }}</label>
                            <select name="location_id" class="darli-filter-select" aria-label="الموقع">
                                <option value="">{{ $isAr ? 'كل المناطق' : 'All areas' }}</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected((string) ($filters['location_id'] ?? '') === (string) $location->id)>
                                        {{ $location->translation($locale)?->name ?? $location->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="darli-filter-actions">
                            <a href="{{ route('properties.index', ['locale' => $locale]) }}"
                                class="darli-filter-btn-reset">
                                {{ $isAr ? 'إعادة تعيين' : 'Reset' }}
                            </a>
                            <button type="submit" class="darli-filter-btn-submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span>{{ $isAr ? 'بحث عن العقارات' : 'Search properties' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sub-bar: Count + View Switches + Sort --}}
                <div class="darli-prop-subbar">
                    <div class="darli-prop-count-badge">
                        {{ number_format($properties->total()) }} {{ $isAr ? 'عقار متاح' : 'properties available' }}
                    </div>

                    <div class="darli-prop-controls">
                        <div class="darli-view-toggles">
                            <button type="button" id="darli-view-grid" class="darli-view-btn active"
                                title="{{ $isAr ? 'عرض شبكي' : 'Grid view' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                                </svg>
                            </button>
                            <button type="button" id="darli-view-list" class="darli-view-btn"
                                title="{{ $isAr ? 'عرض طولي' : 'List view' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </button>
                        </div>

                        <select id="darli-sort-select" class="darli-sort-select" aria-label="ترتيب النتائج">
                            <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                                {{ $isAr ? 'أحدث أولاً' : 'Newest first' }}</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>
                                {{ $isAr ? 'السعر: من الأقل للأعلى' : 'Price: Low to High' }}</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>
                                {{ $isAr ? 'السعر: من الأعلى للأقل' : 'Price: High to Low' }}</option>
                            <option value="popular" @selected(request('sort') === 'popular')>
                                {{ $isAr ? 'الأكثر شعبية' : 'Most popular' }}</option>
                        </select>
                    </div>
                </div>

                {{-- Property Cards Grid (3 Columns) --}}
                @if ($properties->isEmpty())
                    <div class="darli-panel darli-empty p-5 text-center"
                        style="background:#fff;border-radius:12px;border:1px solid #edf2f7;">
                        <h4 class="mb-2" style="font-weight:700;">
                            {{ $isAr ? 'لا توجد عقارات مطابقة للبحث' : 'No matching properties found' }}</h4>
                        <p class="text-muted mb-0">
                            {{ $isAr ? 'جرّب تغيير خيارات الفلترة أو إعادة تعيين البحث لعرض كافة العقارات.' : 'Try changing your filter criteria or reset to view all available properties.' }}
                        </p>
                    </div>
                @else
                    <div class="darli-props-grid">
                        @foreach ($properties as $property)
                            @php
                                $tr = $property->translation($locale);
                                $isRent = $property->purpose === 'rent';
                                $slug = $tr?->slug;
                                $url = $slug ? route('properties.show', ['locale' => $locale, 'slug' => $slug]) : '#';
                                $image = $property->primaryImageUrl($locale);
                                $cardImages = $property->media
                                    ->map(function ($media) {
                                        $path = ltrim((string) $media->path, '/');
                                        if (str_starts_with($path, 'http')) {
                                            return $path;
                                        }

                                        return ($media->disk ?? 'public') === 'legacy'
                                            ? asset('legacy/' . $path)
                                            : asset('storage/' . $path);
                                    })
                                    ->filter()
                                    ->values();
                                if ($cardImages->isEmpty()) {
                                    $cardImages = collect([$image]);
                                }
                                $purposeLabel = $isRent
                                    ? ($isAr
                                        ? 'للإيجار'
                                        : 'For rent')
                                    : ($isAr
                                        ? 'للبيع'
                                        : 'For sale');
                                $purposeClass = $isRent ? 'rent' : 'sale';
                                $categoryName = $property->category?->translation($locale)?->name;
                                $locationName =
                                    $tr?->address_text ?:
                                    $property->location?->translation($locale)?->name ??
                                        ($isAr ? 'بني سويف' : 'Beni Suef');
                                $isContactPrice = $property->isPriceOnContact();
                                $isNegotiablePrice = $property->isNegotiable();
                                $formattedPrice = $isContactPrice
                                    ? ($isAr
                                        ? 'السعر عند التواصل'
                                        : 'Price on request')
                                    : number_format((float) $property->price, 0) . ' ' . ($isAr ? 'جنيه' : 'EGP');
                                $areaFormatted = $property->area
                                    ? rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.')
                                    : null;
                            @endphp

                            <div class="darli-prop-card" data-id="{{ $property->id }}"
                                data-purpose="{{ $property->purpose }}" data-lat="{{ $property->latitude }}"
                                data-lng="{{ $property->longitude }}">
                                {{-- Media Container --}}
                                <div class="darli-prop-card-media" data-card-gallery>
                                    <div class="darli-card-gallery-track">
                                        @foreach ($cardImages as $cardImage)
                                            <a href="{{ $url }}"
                                                class="darli-card-gallery-slide {{ $loop->first ? 'is-active' : '' }}"
                                                aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                                                <img src="{{ $cardImage }}" alt="{{ $tr?->name }}" loading="lazy"
                                                    onerror="this.onerror=null;this.src='{{ $property->placeholderUrl($locale) }}';">
                                            </a>
                                        @endforeach
                                    </div>

                                    @if ($cardImages->count() > 1)
                                        <button type="button" class="darli-card-gallery-nav darli-card-gallery-prev"
                                            aria-label="{{ $isAr ? 'الصورة السابقة' : 'Previous image' }}"><span
                                                aria-hidden="true">‹</span></button>
                                        <button type="button" class="darli-card-gallery-nav darli-card-gallery-next"
                                            aria-label="{{ $isAr ? 'الصورة التالية' : 'Next image' }}"><span
                                                aria-hidden="true">›</span></button>
                                        <span class="darli-card-gallery-count" aria-live="polite">1 /
                                            {{ $cardImages->count() }}</span>
                                    @endif

                                    {{-- Top-Left: Badges (Sold + Purpose) --}}
                                    <div class="darli-badges-top-left">
                                        @if ($property->isSold())
                                            <span class="darli-badge-sold">{{ $isAr ? 'تم البيع' : 'Sold' }}</span>
                                        @endif
                                        <span class="darli-badge-purpose {{ $purposeClass }}">{{ $purposeLabel }}</span>
                                    </div>

                                    {{-- Top-Right: Wishlist Heart --}}
                                    <button type="button" class="darli-badge-wishlist"
                                        data-bb-toggle="add-to-wishlist" data-type="property"
                                        data-id="{{ $property->id }}"
                                        data-add-message="{{ $isAr ? 'تمت الإضافة إلى المفضلة' : 'Added to favorites' }}"
                                        data-remove-message="{{ $isAr ? 'تمت الإزالة من المفضلة' : 'Removed from favorites' }}"
                                        aria-pressed="false"
                                        aria-label="{{ $isAr ? 'إضافة إلى المفضلة' : 'Add to wishlist' }}"
                                        title="{{ $isAr ? 'إضافة إلى المفضلة' : 'Add to wishlist' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        </svg>
                                    </button>

                                    {{-- Bottom-Left: Category Badge --}}
                                    @if ($categoryName)
                                        <span class="darli-badge-category">{{ $categoryName }}</span>
                                    @endif
                                </div>

                                {{-- Card Body --}}
                                <div class="darli-prop-card-body">
                                    {{-- Title --}}
                                    <a href="{{ $url }}" class="darli-prop-card-title"
                                        title="{{ $tr?->name }}">
                                        {{ $tr?->name ?? $property->reference_code }}
                                    </a>

                                    {{-- Location --}}
                                    <div class="darli-prop-card-loc">
                                        <svg class="darli-prop-loc-pin" xmlns="http://www.w3.org/2000/svg" width="14"
                                            height="14" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                                        </svg>
                                        <span>{{ $locationName }}</span>
                                    </div>

                                    {{-- Price --}}
                                    <div class="darli-prop-card-price d-flex align-items-center gap-2 flex-wrap">
                                        <span>{{ $formattedPrice }}</span>
                                        @if ($isRent && !$isContactPrice)
                                            <span class="rent-period">{{ $isAr ? ' / شهرياً' : ' / mo' }}</span>
                                        @endif
                                        @if ($isNegotiablePrice)
                                            <span class="badge"
                                                style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                                {{ $isAr ? 'قابل للتفاوض' : 'Negotiable' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="darli-prop-card-divider"></div>

                                    {{-- Meta Specs Row --}}
                                    <div class="darli-prop-card-meta">
                                        {{-- Area --}}
                                        <div class="darli-prop-meta-item" title="{{ $isAr ? 'المساحة' : 'Area' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M21.3 8.7 8.7 21.3c-1 1-2.5 1-3.4 0l-2.6-2.6c-1-1-1-2.5 0-3.4L15.3 2.7c1-1 2.5-1 3.4 0l2.6 2.6c1 1 1 2.5 0 3.4Z" />
                                                <path d="m7.5 10.5 2 2" />
                                                <path d="m10.5 7.5 2 2" />
                                            </svg>
                                            <span>{{ $areaFormatted ? $areaFormatted . ($isAr ? ' م²' : ' m²') : '—' }}</span>
                                        </div>

                                        {{-- Bedrooms --}}
                                        <div class="darli-prop-meta-item" title="{{ $isAr ? 'الغرف' : 'Bedrooms' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 4v16" />
                                                <path d="M2 8h18a2 2 0 0 1 2 2v10" />
                                                <path d="M2 17h20" />
                                                <path d="M6 8v9" />
                                            </svg>
                                            <span>
                                                @if ($property->bedrooms > 0)
                                                    {{ $property->bedrooms }} {{ $isAr ? 'حجر' : 'rooms' }}
                                                @else
                                                    {{ $isAr ? 'طابق أرضي' : 'Ground floor' }}
                                                @endif
                                            </span>
                                        </div>

                                        {{-- Bathrooms --}}
                                        <div class="darli-prop-meta-item"
                                            title="{{ $isAr ? 'الحمامات' : 'Bathrooms' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-1.5C4 2 2 3.5 2 5.5V11" />
                                                <path d="M2 11h20v2a7 7 0 0 1-7 7H9a7 7 0 0 1-7-7v-2Z" />
                                                <path d="M7 20v2" />
                                                <path d="M17 20v2" />
                                            </svg>
                                            <span>{{ $property->bathrooms ?? 1 }} {{ $isAr ? 'حمام' : 'bath' }}</span>
                                        </div>
                                    </div>

                                    <div class="darli-prop-card-contact-actions">
                                        <a href="tel:{{ $contactPhoneDigits }}" class="darli-card-contact-call">{{ $isAr ? 'اتصال' : 'Call' }}</a>
                                        <a href="https://wa.me/{{ $contactPhoneDigits }}?text={{ rawurlencode(($isAr ? 'أرغب في الاستفسار عن العقار: ' : 'I would like to ask about: ') . ($tr?->name ?? $property->reference_code) . ' ' . $url) }}"
                                            class="darli-card-contact-whatsapp" target="_blank" rel="noopener">{{ $isAr ? 'واتساب' : 'WhatsApp' }}</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($properties->hasPages())
                        <div class="darli-pagination d-flex justify-content-center mt-4">
                            {{ $properties->links('vendor.pagination.darli') }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- ================= 2. Left Map Pane (Sticky Interactive Map) ================= --}}
            <div class="darli-split-map-pane">

                {{-- Desktop Floating Purpose Switch Card (Hidden on Mobile) --}}
                <div class="darli-map-purpose-card darli-desktop-only">
                    <div class="darli-map-purpose-item rent" data-purpose="rent"
                        title="{{ $isAr ? 'تصفية عقارات للإيجار' : 'Filter rent' }}">
                        <div class="darli-map-purpose-icon rent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-map-purpose-text">
                            <span class="darli-map-purpose-title">{{ $isAr ? 'للإيجار' : 'For rent' }}</span>
                            <span
                                class="darli-map-purpose-sub">{{ $isAr ? 'عقارات للإيجار' : 'Properties for rent' }}</span>
                        </div>
                    </div>

                    <div class="darli-map-purpose-divider"></div>

                    <div class="darli-map-purpose-item sale" data-purpose="sale"
                        title="{{ $isAr ? 'تصفية عقارات للبيع' : 'Filter sale' }}">
                        <div class="darli-map-purpose-icon sale">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-map-purpose-text">
                            <span class="darli-map-purpose-title">{{ $isAr ? 'للبيع' : 'For sale' }}</span>
                            <span
                                class="darli-map-purpose-sub">{{ $isAr ? 'منازل ومسكنات للبيع' : 'Houses for sale' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Map Frame Card (Styled as rounded card with floating controls on mobile) --}}
                <div class="darli-map-frame-card" id="darli-map-frame-card">
                    {{-- The Leaflet Map Element --}}
                    <div id="darli-properties-map"></div>

                    {{-- Visible only while the map fills the screen. --}}
                    <button type="button" class="darli-map-fullscreen-close" id="darli-map-fullscreen-close"
                        aria-label="{{ $isAr ? 'إغلاق الخريطة' : 'Close map' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                        <span>{{ $isAr ? 'إغلاق الخريطة' : 'Close map' }}</span>
                    </button>

                    {{-- In-map search filter (visible in full-screen map) --}}
                    <button type="button" class="darli-map-filter-fab" id="darli-map-filter-btn"
                        aria-expanded="false" aria-controls="darli-map-filter-panel">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 4h18l-7 8v6l-4 2v-8L3 4z" />
                        </svg>
                        <span>{{ $isAr ? 'فلترة الخريطة' : 'Filter map' }}</span>
                    </button>

                    <div class="darli-map-filter-panel" id="darli-map-filter-panel"
                        aria-label="{{ $isAr ? 'فلترة نتائج الخريطة' : 'Filter map results' }}">
                        <div class="darli-map-filter-head">
                            <strong>{{ $isAr ? 'فلترة الخريطة' : 'Filter map' }}</strong>
                            <button type="button" class="darli-map-filter-close" id="darli-map-filter-close"
                                aria-label="{{ $isAr ? 'إغلاق الفلتر' : 'Close filter' }}">&times;</button>
                        </div>

                        <form method="GET" action="{{ route('properties.index', ['locale' => $locale]) }}"
                            id="darli-map-filter-form" data-endpoint="{{ url('/ajax/properties/map-filter') }}">
                            <div class="darli-map-filter-grid">
                                <div class="darli-map-filter-field">
                                    <label for="darli-map-filter-purpose">{{ $isAr ? 'نوع الطلب' : 'Request type' }}</label>
                                    <select name="purpose" id="darli-map-filter-purpose" aria-label="الغرض">
                                        <option value="">{{ $isAr ? 'كل الطلبات' : 'All requests' }}</option>
                                        <option value="sale" @selected(request('purpose') === 'sale')>{{ $isAr ? 'شراء' : 'Buy' }}</option>
                                        <option value="rent" @selected(request('purpose') === 'rent')>{{ $isAr ? 'إيجار' : 'Rent' }}</option>
                                    </select>
                                </div>

                                <div class="darli-map-filter-field">
                                    <label for="darli-map-filter-location">{{ $isAr ? 'المدينة / المنطقة' : 'City / area' }}</label>
                                    <select name="location_id" id="darli-map-filter-location" aria-label="الموقع">
                                        <option value="">{{ $isAr ? 'كل المدن والمناطق' : 'All cities & areas' }}</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}" @selected((string) request('location_id') === (string) $loc->id)>
                                                {{ $loc->translation($locale)?->name ?? $loc->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="darli-map-filter-field">
                                    <label for="darli-map-filter-category">{{ $isAr ? 'التصنيف' : 'Category' }}</label>
                                    <select name="category_id" id="darli-map-filter-category" aria-label="التصنيف">
                                        <option value="">{{ $isAr ? 'جميع التصنيفات' : 'All categories' }}</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                                                {{ $cat->translation($locale)?->name ?? $cat->key }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="darli-map-filter-field">
                                    <span>{{ $isAr ? 'نطاق السعر (جنيه)' : 'Price range (EGP)' }}</span>
                                    <div class="darli-map-filter-price">
                                        <input type="number" name="min_price" min="0" value="{{ request('min_price') }}"
                                            placeholder="{{ $isAr ? 'من' : 'Min' }}">
                                        <input type="number" name="max_price" min="0" value="{{ request('max_price') }}"
                                            placeholder="{{ $isAr ? 'إلى' : 'Max' }}">
                                    </div>
                                </div>
                            </div>

                            @foreach ((array) request('features', []) as $feature)
                                <input type="hidden" name="features[]" value="{{ $feature }}">
                            @endforeach
                            @if (request()->filled('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif
                            @if (request()->filled('furnished'))
                                <input type="hidden" name="furnished" value="{{ request('furnished') }}">
                            @endif
                            @if (request()->filled('nearby_location_id'))
                                <input type="hidden" name="nearby_location_id" value="{{ request('nearby_location_id') }}">
                            @endif
                            @if (request()->filled('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            <input type="hidden" name="map" value="1">

                            <div class="darli-map-filter-actions">
                                <a href="{{ route('properties.index', ['locale' => $locale]) }}"
                                    id="darli-map-filter-reset">
                                    {{ $isAr ? 'مسح الفلاتر' : 'Clear filters' }}
                                </a>
                                <button type="submit">{{ $isAr ? 'تطبيق الفلترة' : 'Apply filters' }}</button>
                            </div>
                        </form>
                    </div>

                    <div class="darli-map-filter-loading" id="darli-map-filter-loading" role="status" aria-live="polite">
                        <span class="darli-map-filter-spinner" aria-hidden="true"></span>
                        <span>{{ $isAr ? 'جارٍ تحديث الخريطة...' : 'Updating map...' }}</span>
                    </div>

                    <div class="darli-map-filter-toast" id="darli-map-filter-toast" role="status" aria-live="polite"></div>

                    {{-- Mobile Floating Layer Switcher (Top-Left) --}}
                    <button type="button" class="darli-mob-ctrl-btn darli-mob-btn-layer" id="darli-mob-layer-btn"
                        title="{{ $isAr ? 'نوع الخريطة' : 'Map type' }}" aria-label="نوع الخريطة">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e293b"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </button>
                    <div id="darli-mob-layer-popover" class="darli-mob-layer-popover">
                        <div class="darli-mob-layer-opt active" data-layer="satellite">
                            <span class="darli-mob-layer-dot"></span>
                            <span>{{ $isAr ? 'قمر صناعي (افتراضي)' : 'Satellite' }}</span>
                        </div>
                        <div class="darli-mob-layer-opt" data-layer="light">
                            <span class="darli-mob-layer-dot"></span>
                            <span>{{ $isAr ? 'خريطة شوارع' : 'Street map' }}</span>
                        </div>
                        <div class="darli-mob-layer-opt" data-layer="dark">
                            <span class="darli-mob-layer-dot"></span>
                            <span>{{ $isAr ? 'الوضع الليلي' : 'Dark mode' }}</span>
                        </div>
                    </div>

                    {{-- Mobile Floating Fullscreen Button (Top-Right) --}}
                    <button type="button" class="darli-mob-ctrl-btn darli-mob-btn-fullscreen"
                        id="darli-mob-fullscreen-btn" title="{{ $isAr ? 'ملء الشاشة' : 'Fullscreen' }}"
                        aria-label="ملء الشاشة">
                        <svg class="icon-expand" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" />
                        </svg>
                        <svg class="icon-compress" style="display:none;" width="20" height="20"
                            viewBox="0 0 24 24" fill="none" stroke="#1e293b" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 14h6v6m10-10h-6V4m0 6 7-7M3 21l7-7" />
                        </svg>
                    </button>
                </div>

                {{-- Desktop Bottom Floating Layer Switcher (Hidden on Mobile) --}}
                <div class="darli-map-btn-type-wrap darli-desktop-only">
                    <button type="button" id="darli-map-btn-type" class="darli-map-btn-type">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                        <span>{{ $isAr ? 'نوع الخريطة' : 'Map type' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div id="darli-map-layer-menu" class="darli-map-layer-menu">
                        <div class="darli-map-layer-item" data-layer="light">
                            {{ $isAr ? 'خريطة شوارع' : 'Street map' }}</div>
                        <div class="darli-map-layer-item active" data-layer="satellite">
                            {{ $isAr ? 'قمر صناعي' : 'Satellite' }}
                        </div>
                        <div class="darli-map-layer-item" data-layer="dark">{{ $isAr ? 'الوضع الليلي' : 'Dark mode' }}
                        </div>
                    </div>
                </div>

                {{-- Desktop Bottom Floating Area Button (Hidden on Mobile) --}}
                <button type="button" id="darli-map-btn-area" class="darli-map-btn-area darli-desktop-only">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="currentColor" style="color: var(--darli-gold);">
                        <path
                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                    </svg>
                    <span>{{ $isAr ? 'عرض العقارات في هذه المنطقة' : 'Show properties in this area' }}</span>
                </button>

                {{-- ================= Mobile View Elements (Shown only on Mobile) ================= --}}
                <div class="darli-mob-elements-container">

                    {{-- 1. Mobile Purpose Segmented Tabs (للبيع / للإيجار) --}}
                    <div class="darli-mob-purpose-wrap">
                        <div class="darli-mob-purpose-pills">
                            <button type="button"
                                class="darli-mob-purpose-pill {{ request('purpose', 'sale') === 'sale' || empty(request('purpose')) ? 'active' : '' }}"
                                data-purpose="sale">
                                {{ $isAr ? 'للبيع' : 'For sale' }}
                            </button>
                            <button type="button"
                                class="darli-mob-purpose-pill {{ request('purpose') === 'rent' ? 'active' : '' }}"
                                data-purpose="rent">
                                {{ $isAr ? 'للإيجار' : 'For rent' }}
                            </button>
                        </div>
                    </div>

                    {{-- Mobile compact filters --}}
                    <div class="darli-mob-search-card darli-mob-compact-filter">
                        <form id="darli-mob-search-form" method="get"
                            action="{{ route('properties.index', ['locale' => $locale]) }}">
                            <input type="hidden" name="purpose" id="darli-mob-input-purpose"
                                value="{{ request('purpose', 'sale') }}">
                            <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">

                            <div class="darli-mob-selects-grid">
                                {{-- Location Selector (Right in RTL) --}}
                                <div class="darli-mob-custom-select-box" id="darli-mob-location-box">
                                    <div class="select-icon">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                            stroke="#c28b2e" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div class="select-text">
                                        <span
                                            class="select-label">{{ $isAr ? 'المدينة' : 'City' }}</span>
                                        <span class="select-value"
                                            id="darli-mob-location-display">{{ $currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef') }}</span>
                                    </div>
                                    <div class="select-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </div>
                                    <select name="location_id" id="darli-mob-location-select"
                                        class="darli-mob-overlay-select darli-mob-auto-submit"
                                        aria-label="{{ $isAr ? 'المدينة' : 'City' }}">
                                        <option value="">{{ $isAr ? 'كل المدن' : 'All cities' }}</option>
                                        @foreach ($cities as $loc)
                                            <option value="{{ $loc->id }}" @selected((string) request('location_id') === (string) $loc->id)>
                                                {{ $loc->translation($locale)?->name ?? $loc->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Property Type Selector (Left in RTL) --}}
                                <div class="darli-mob-custom-select-box" id="darli-mob-category-box">
                                    <div class="select-icon">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                            stroke="#c28b2e" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                        </svg>
                                    </div>
                                    <div class="select-text">
                                        <span class="select-label">{{ $isAr ? 'نوع العقار' : 'Property type' }}</span>
                                        @php
                                            $activeCat = !empty(request('category_id'))
                                                ? $categories->firstWhere('id', (int) request('category_id'))
                                                : null;
                                            $activeCatName = $activeCat
                                                ? $activeCat->translation($locale)?->name ?? $activeCat->key
                                                : ($isAr
                                                    ? 'أي نوع'
                                                    : 'Any type');
                                        @endphp
                                        <span class="select-value"
                                            id="darli-mob-category-display">{{ $activeCatName }}</span>
                                    </div>
                                    <div class="select-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </div>
                                    <select name="category_id" id="darli-mob-category-select"
                                        class="darli-mob-overlay-select darli-mob-auto-submit"
                                        aria-label="{{ $isAr ? 'نوع العقار' : 'Property type' }}">
                                        <option value="">{{ $isAr ? 'أي نوع' : 'Any type' }}</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                                                {{ $cat->translation($locale)?->name ?? $cat->key }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <button type="button" class="darli-mob-custom-select-box darli-mob-price-trigger"
                                    id="darli-mob-price-trigger">
                                    <span class="select-icon">⌄</span>
                                    <span class="select-text">
                                        <span class="select-label">{{ $isAr ? 'السعر' : 'Price' }}</span>
                                        <span class="select-value">
                                            @if(request('min_price') || request('max_price'))
                                                {{ number_format((float) request('min_price', 0)) }} - {{ number_format((float) request('max_price', 0)) }} {{ $isAr ? 'ج.م' : 'EGP' }}
                                            @else
                                                {{ $isAr ? 'أي نطاق' : 'Any range' }}
                                            @endif
                                        </span>
                                    </span>
                                </button>

                                <div class="darli-mob-custom-select-box">
                                    <span class="select-icon">⌄</span>
                                    <span class="select-text"><span class="select-label">{{ $isAr ? 'التجهيز' : 'Furnishing' }}</span><span class="select-value">{{ request('furnished') === '1' ? ($isAr ? 'مفروش' : 'Furnished') : (request('furnished') === '0' ? ($isAr ? 'غير مفروش' : 'Unfurnished') : ($isAr ? 'الكل' : 'Any')) }}</span></span>
                                    <select name="furnished" class="darli-mob-overlay-select darli-mob-auto-submit" aria-label="مفروش">
                                        <option value="">{{ $isAr ? 'الكل' : 'Any' }}</option>
                                        <option value="1" @selected(request('furnished') === '1')>{{ $isAr ? 'مفروش' : 'Furnished' }}</option>
                                        <option value="0" @selected(request('furnished') === '0')>{{ $isAr ? 'غير مفروش' : 'Unfurnished' }}</option>
                                    </select>
                                </div>

                            </div>
                            <input type="hidden" name="bedrooms" value="{{ request('bedrooms') }}">
                            <input type="hidden" name="nearby_location_id" value="{{ request('nearby_location_id') }}">
                            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                        </form>
                    </div>

                    <div class="darli-mob-actions-row">
                        <button type="button" class="darli-mob-action-btn darli-mob-filter-button" id="darli-mob-adv-filter-btn"><span>☷</span><span>{{ $isAr ? 'فلترة متقدمة' : 'Advanced filter' }}</span></button>
                        <button type="button" class="darli-mob-action-btn darli-mob-map-button" id="darli-mob-show-map-btn" aria-expanded="false"><span>🗺</span><span class="darli-map-toggle-label">{{ $isAr ? 'عرض الخريطة' : 'Show map' }}</span></button>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Price Range Sheet (Mobile) --}}
    <div class="darli-price-sheet-backdrop" id="darli-price-sheet-backdrop"></div>
    <div class="darli-price-sheet" id="darli-price-sheet" role="dialog" aria-modal="true"
        aria-labelledby="darli-price-sheet-title">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <h3 class="sheet-title" id="darli-price-sheet-title">{{ $isAr ? 'حدد نطاق السعر' : 'Select price range' }}</h3>
            <button type="button" class="sheet-close-btn" id="darli-price-sheet-close" aria-label="{{ $isAr ? 'إغلاق' : 'Close' }}">&times;</button>
        </div>
        <form method="get" action="{{ route('properties.index', ['locale' => $locale]) }}" class="darli-price-sheet-form">
            @foreach (['purpose', 'category_id', 'category_group', 'student', 'location_id', 'bedrooms', 'bathrooms', 'furnished', 'nearby_location_id', 'sort', 'q'] as $preservedFilter)
                @if(request()->filled($preservedFilter))
                    <input type="hidden" name="{{ $preservedFilter }}" value="{{ request($preservedFilter) }}">
                @endif
            @endforeach
            @foreach ((array) request('features', []) as $feature)
                <input type="hidden" name="features[]" value="{{ $feature }}">
            @endforeach
            <div class="darli-price-range-fields">
                <label>
                    <span>{{ $isAr ? 'من' : 'From' }}</span>
                    <input type="number" name="min_price" min="0" step="1000" value="{{ request('min_price') }}"
                        placeholder="{{ $isAr ? 'الحد الأدنى' : 'Minimum' }}">
                </label>
                <label>
                    <span>{{ $isAr ? 'إلى' : 'To' }}</span>
                    <input type="number" name="max_price" min="0" step="1000" value="{{ request('max_price') }}"
                        placeholder="{{ $isAr ? 'الحد الأقصى' : 'Maximum' }}">
                </label>
            </div>
            <div class="darli-price-sheet-actions">
                <button type="button" class="darli-price-clear-btn" id="darli-price-clear-btn">{{ $isAr ? 'مسح' : 'Clear' }}</button>
                <button type="submit" class="darli-price-apply-btn">{{ $isAr ? 'تطبيق النطاق' : 'Apply range' }}</button>
            </div>
        </form>
    </div>

    {{-- Advanced Filter Bottom Sheet Modal (Mobile) --}}
    <div class="darli-mob-sheet-backdrop" id="darli-mob-filter-backdrop"></div>
    <div class="darli-mob-filter-sheet" id="darli-mob-filter-sheet">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <h3 class="sheet-title">{{ $isAr ? 'فلترة متقدمة' : 'Advanced Filters' }}</h3>
            <button type="button" class="sheet-close-btn" id="darli-mob-filter-close"
                aria-label="إغلاق">&times;</button>
        </div>
        <div class="sheet-body">
            <form id="darli-mob-adv-filter-form" method="get"
                action="{{ route('properties.index', ['locale' => $locale]) }}">
                <input type="hidden" name="purpose" id="darli-adv-purpose"
                    value="{{ request('purpose', 'sale') }}">
                <input type="hidden" name="furnished" value="{{ request('furnished') }}">

                {{-- Keyword --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'البحث بالكلمة' : 'Keyword search' }}</label>
                    <input type="text" name="q" value="{{ request('q', request('k')) }}"
                        placeholder="{{ $isAr ? 'اسم العقار، الحي، الكود...' : 'Property name, area, code...' }}"
                        class="sheet-input">
                </div>

                {{-- Location --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'المنطقة / الحي' : 'Location / District' }}</label>
                    <select name="location_id" class="sheet-select" aria-label="الموقع">
                        <option value="">{{ $isAr ? 'كل المناطق' : 'All areas' }}</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" @selected((string) request('location_id') === (string) $loc->id)>
                                {{ $loc->translation($locale)?->name ?? $loc->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nearby area / landmark --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'قريب من' : 'Near' }}</label>
                    <select name="nearby_location_id" class="sheet-select" @disabled(!request('location_id')) aria-label="اختر">
                        <option value="">
                            {{ request('location_id') ? ($isAr ? 'أي منطقة أو معلم' : 'Any area or landmark') : ($isAr ? 'اختر المدينة أولاً' : 'Choose city first') }}
                        </option>
                        @foreach ($nearbyLocations->where('parent_id', (int) request('location_id')) as $nearbyLocation)
                            <option value="{{ $nearbyLocation->id }}" @selected((string) request('nearby_location_id') === (string) $nearbyLocation->id)>
                                {{ $nearbyLocation->type === 'landmark' ? '★ ' : '' }}{{ $nearbyLocation->translation($locale)?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'نوع العقار' : 'Property type' }}</label>
                    <select name="category_id" class="sheet-select" aria-label="التصنيف">
                        <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                                {{ $cat->translation($locale)?->name ?? $cat->key }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'نطاق السعر (جنيه)' : 'Price range (EGP)' }}</label>
                    <div class="sheet-row-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}"
                            placeholder="{{ $isAr ? 'الحد الأدنى' : 'Min' }}" class="sheet-input">
                        <input type="number" name="max_price" value="{{ request('max_price') }}"
                            placeholder="{{ $isAr ? 'الحد الأقصى' : 'Max' }}" class="sheet-input">
                    </div>
                </div>

                {{-- Bedrooms --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'عدد الغرف' : 'Bedrooms' }}</label>
                    <div class="sheet-chips" data-target="bedrooms">
                        <button type="button" class="sheet-chip {{ empty(request('bedrooms')) ? 'active' : '' }}"
                            data-val="">{{ $isAr ? 'الكل' : 'Any' }}</button>
                        @foreach ([1, 2, 3, 4, 5] as $n)
                            <button type="button"
                                class="sheet-chip {{ (string) request('bedrooms') === (string) $n ? 'active' : '' }}"
                                data-val="{{ $n }}">{{ $n }}{{ $n === 5 ? '+' : '' }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="bedrooms" id="darli-adv-bedrooms"
                        value="{{ request('bedrooms') }}">
                </div>

                {{-- Bathrooms --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'عدد الحمامات' : 'Bathrooms' }}</label>
                    <div class="sheet-chips" data-target="bathrooms">
                        <button type="button" class="sheet-chip {{ empty(request('bathrooms')) ? 'active' : '' }}"
                            data-val="">{{ $isAr ? 'الكل' : 'Any' }}</button>
                        @foreach ([1, 2, 3, 4] as $n)
                            <button type="button"
                                class="sheet-chip {{ (string) request('bathrooms') === (string) $n ? 'active' : '' }}"
                                data-val="{{ $n }}">{{ $n }}{{ $n === 4 ? '+' : '' }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="bathrooms" id="darli-adv-bathrooms"
                        value="{{ request('bathrooms') }}">
                </div>

                {{-- Sort --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'الترتيب حسب' : 'Sort by' }}</label>
                    <select name="sort" class="sheet-select" aria-label="ترتيب النتائج">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                            {{ $isAr ? 'الأحدث أولاً' : 'Newest first' }}</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>
                            {{ $isAr ? 'السعر: من الأقل للأعلى' : 'Price: Low to High' }}</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>
                            {{ $isAr ? 'السعر: من الأعلى للأقل' : 'Price: High to Low' }}</option>
                        <option value="popular" @selected(request('sort') === 'popular')>
                            {{ $isAr ? 'الأكثر شعبية' : 'Most popular' }}</option>
                    </select>
                </div>

                <div class="sheet-actions">
                    <a href="{{ route('properties.index', ['locale' => $locale]) }}"
                        class="sheet-btn-reset">{{ $isAr ? 'إعادة تعيين' : 'Reset' }}</a>
                    <button type="submit"
                        class="sheet-btn-apply">{{ $isAr ? 'تطبيق الفلترة' : 'Apply Filters' }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Map Data & Scripts --}}
    <script>
        window.__DARLI_MAP_PROPERTIES__ = @json($mapProperties);
        window.__DARLI_CURRENT_CITY__ = @json($currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef'));
    </script>
    <script src="{{ asset('themes/darlii/plugins/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('themes/darlii/js/darli-properties-map.min.js') }}?v=1.0.16"></script>

    {{-- Mobile Wizard Interactive Script --}}
    <script>
        (function() {
            function initDarliWizard() {
                var wizard = document.getElementById('darliMobileWizard');
                if (!wizard) return;

                var stepMain = document.getElementById('wizardStepMain');
                var stepSale = document.getElementById('wizardStepSale');
                var stepRent = document.getElementById('wizardStepRent');

                function showStep(step) {
                    if (stepMain) stepMain.style.display = step === 'main' ? 'flex' : 'none';
                    if (stepSale) stepSale.style.display = step === 'sale' ? 'flex' : 'none';
                    if (stepRent) stepRent.style.display = step === 'rent' ? 'flex' : 'none';
                }

                function selectMainChoice(button, step) {
                    if (!button) return;

                    button.classList.add('is-selected');
                    window.setTimeout(function() {
                        button.classList.remove('is-selected');
                        showStep(step);
                    }, 160);
                }

                if (wizard.classList.contains('is-active')) {
                    document.body.classList.add('darli-wizard-open');
                }

                var wizardMenuBtn = wizard.querySelector('.darli-wizard-menu-btn');
                if (wizardMenuBtn) {
                    wizardMenuBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var siteMenuBtn = document.querySelector('.mobile-nav-toggler');
                        closeWizard();

                        if (siteMenuBtn) {
                            window.requestAnimationFrame(function() {
                                siteMenuBtn.click();
                            });
                        } else {
                            document.body.classList.add('mobile-menu-visible');
                        }
                    });
                }

                function openWizard() {
                    wizard.classList.add('is-active');
                    document.body.classList.add('darli-wizard-open');
                }

                function closeWizard() {
                    wizard.classList.remove('is-active');
                    document.body.classList.remove('darli-wizard-open');
                }

                // Step 1: Click "للبيع" -> Show Step 2
                var btnSale = document.getElementById('wizardGoToSaleBtn');
                if (btnSale) {
                    btnSale.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectMainChoice(btnSale, 'sale');
                    });
                }

                // Step 1: Click "للإيجار" -> Show Step 3
                var btnRent = document.getElementById('wizardGoToRentBtn');
                if (btnRent) {
                    btnRent.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectMainChoice(btnRent, 'rent');
                    });
                }

                // Segmented pills switching
                var pillSwitchToRent = document.getElementById('pillSwitchToRent');
                if (pillSwitchToRent) {
                    pillSwitchToRent.addEventListener('click', function() {
                        showStep('rent');
                    });
                }

                var pillSwitchToSale = document.getElementById('pillSwitchToSale');
                if (pillSwitchToSale) {
                    pillSwitchToSale.addEventListener('click', function() {
                        showStep('sale');
                    });
                }

                // Back buttons -> Return to Step 1
                var backBtns = wizard.querySelectorAll('.wizardBackToMain');
                backBtns.forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        showStep('main');
                    });
                });

                // Skip button -> Close wizard
                var skipBtn = document.getElementById('wizardSkipBtn');
                if (skipBtn) {
                    skipBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        closeWizard();
                    });
                }

                var listingsBtn = document.getElementById('wizardNavListingsBtn');
                if (listingsBtn) {
                    listingsBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        closeWizard();
                    });
                }

                var wizardNotificationsBtn = document.getElementById('wizardNotificationsBtn');
                if (wizardNotificationsBtn) {
                    wizardNotificationsBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        var notificationsPanel = document.getElementById('darli-notif-panel');
                        if (notificationsPanel) {
                            var isOpen = notificationsPanel.style.display !== 'none';
                            notificationsPanel.style.display = isOpen ? 'none' : 'block';
                        }
                    });
                }

                // Open wizard from listings page
                var openBtn = document.getElementById('openMobileWizardBtn');
                if (openBtn) {
                    openBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        showStep('main');
                        openWizard();
                    });
                }

                // Top menu button & bottom "more" button -> Open mobile menu drawer
                var menuBtn = wizard.querySelector('.darli-wizard-menu-btn');
                var moreBtn = wizard.querySelector('.wizardNavMoreBtn');
                var triggerMenu = function(e) {
                    e.preventDefault();
                    var toggler = document.querySelector('.mobile-nav-toggler');
                    if (toggler) {
                        toggler.click();
                    }
                };
                if (menuBtn) menuBtn.addEventListener('click', triggerMenu);
                if (moreBtn) moreBtn.addEventListener('click', triggerMenu);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDarliWizard);
            } else {
                initDarliWizard();
            }
        })();
    </script>
@endsection
