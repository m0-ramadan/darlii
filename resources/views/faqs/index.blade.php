@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));

    // Dynamic settings from database
    $heroBadge = setting('faq.hero_badge', $isAr ? 'مركز المساعدة ودليل المستخدمين' : 'Help Center & User Guide');
    $heroTitle = setting('faq.hero_title', $isAr ? 'الأسئلة الشائعة والأجوبة الوافية' : 'Frequently Asked Questions');
    $heroSubtitle = setting(
        'faq.hero_subtitle',
        $isAr
            ? 'إجابات شاملة على أهم الأسئلة حول شراء وبيع وتأجير العقارات، الأمور القانونية، والتعامل مع الوكلاء المعتمدين في بني سويف ومصر.'
            : 'Comprehensive answers to all your inquiries regarding buying, renting, financing, and verified brokers across Beni Suef and Egypt.',
    );
    $searchPlaceholder = setting(
        'faq.search_placeholder',
        $isAr
            ? 'ابحث في الأسئلة الشائعة، مثل: سند الملكية، الاستثمار في بني سويف، الإيجار ...'
            : 'Search FAQs... e.g. title deed, investment in Beni Suef, rent...',
    );

    $ctaBadge = setting('faq.cta_badge', $isAr ? 'ما زلت تبحث عن إجابة؟' : 'Still looking for answers?');
    $ctaTitle = setting('faq.cta_title', $isAr ? 'فريقنا مستعد لمساعدتك دائماً' : 'Our Team Is Always Ready to Help');
    $ctaDesc = setting(
        'faq.cta_desc',
        $isAr
            ? 'تواصل مع فريق الدعم لدينا للحصول على استشارة مجانية وإجابات دقيقة لجميع استفساراتك العقارية في بني سويف ومصر.'
            : 'Contact our dedicated support team for free consultations and precise answers regarding properties in Beni Suef and Egypt.',
    );
    $ctaBtnText = setting('faq.cta_btn_text', $isAr ? 'تواصل معنا الآن' : 'Contact Us Now');
    $ctaBtnUrl = setting('faq.cta_btn_url', route('contact-us.index', ['locale' => $locale]));
    $ctaWhatsappText = setting('faq.cta_whatsapp_text', $isAr ? 'محادثة واتساب سريعة' : 'Quick WhatsApp Chat');

    $rawWhatsapp = (string) setting('contact.whatsapp', '+20 10 34905812');
    $cleanWhatsapp = preg_replace('/[^\d+]/', '', $rawWhatsapp);
    $waDigits = ltrim($cleanWhatsapp, '+');

    // Extract categories
    $rawCategories = [];
    foreach ($faqs as $item) {
        $c = $isAr ? $item['category'] ?? '' : $item['category_en'] ?? ($item['category'] ?? '');
        if ($c && !in_array($c, $rawCategories)) {
            $rawCategories[] = $c;
        }
    }
@endphp

@section('content')
    <style>
        /* ==========================================================================
               Darli FAQ Page (100% Identical Visual Match with User Reference)
               Forced light aesthetic to match screenshot regardless of global dark theme
               ========================================================================== */

        .darli-faq-page {
            background-color: #faf9f6 !important;
            min-height: 85vh;
            padding-bottom: 90px;
            color: #1e293b !important;
            font-family: inherit;
        }

        /* ---------------- Hero Section ---------------- */
        .faq-hero {
            position: relative;
            background: linear-gradient(180deg, #fbf7ee 0%, #fefcf9 100%) !important;
            padding: 35px 0 50px 0;
            overflow: hidden;
            border-bottom: 1px solid #f1ece4;
            text-align: center;
        }

        /* Decorative side illustrations matching reference */
        .faq-hero__decor {
            position: absolute;
            bottom: 0;
            pointer-events: none;
            z-index: 1;
            user-select: none;
        }

        .faq-hero__decor--left {
            left: 0;
            max-height: 280px;
            width: auto;
            max-width: 320px;
            object-fit: contain;
            object-position: bottom left;
        }

        .faq-hero__decor--right {
            right: 0;
            max-height: 280px;
            width: auto;
            max-width: 300px;
            object-fit: contain;
            object-position: bottom right;
        }

        [dir="ltr"] .faq-hero__decor--left {
            left: auto;
            right: 0;
            object-position: bottom right;
        }

        [dir="ltr"] .faq-hero__decor--right {
            right: auto;
            left: 0;
            object-position: bottom left;
        }

        @media (max-width: 1200px) {
            .faq-hero__decor--left {
                max-width: 240px;
                max-height: 240px;
            }

            .faq-hero__decor--right {
                max-width: 230px;
                max-height: 240px;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .faq-hero__decor--left {
                max-width: 180px;
                max-height: 190px;
            }

            .faq-hero__decor--right {
                max-width: 170px;
                max-height: 190px;
            }
        }

        @media (max-width: 767px) {
            .faq-hero__decor {
                display: none !important;
            }

            .faq-hero {
                padding: 30px 0 40px 0;
            }
        }

        .faq-hero__inner {
            position: relative;
            z-index: 3;
            max-width: 820px;
            margin: 0 auto;
            padding: 0 16px;
        }

        /* Breadcrumbs */
        .faq-breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px !important;
            color: #64748b !important;
            margin-bottom: 20px;
        }

        .faq-breadcrumb a {
            color: #64748b !important;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }

        .faq-breadcrumb a:hover {
            color: #0f172a !important;
        }

        .faq-breadcrumb .faq-breadcrumb__sep {
            color: #94a3b8 !important;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
        }

        .faq-breadcrumb .faq-breadcrumb__current {
            color: #0f172a !important;
            font-weight: 600 !important;
        }

        /* Hero Badge */
        .faq-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #fef3c7 !important;
            border: 1px solid #fde68a !important;
            color: #b45309 !important;
            font-size: 13px !important;
            font-weight: 700 !important;
            padding: 6px 18px !important;
            border-radius: 9999px !important;
            margin-bottom: 16px;
            box-shadow: 0 1px 3px rgba(180, 83, 9, 0.05);
        }

        .faq-hero__badge svg {
            flex-shrink: 0;
            color: #b45309 !important;
        }

        /* Hero Title & Subtitle */
        .faq-hero__title {
            font-size: 38px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin: 0 0 14px 0 !important;
            letter-spacing: -0.5px;
            line-height: 1.3 !important;
        }

        @media (max-width: 768px) {
            .faq-hero__title {
                font-size: 26px !important;
            }
        }

        .faq-hero__subtitle {
            font-size: 15px !important;
            line-height: 1.75 !important;
            color: #64748b !important;
            max-width: 680px;
            margin: 0 auto 28px auto !important;
        }

        @media (max-width: 768px) {
            .faq-hero__subtitle {
                font-size: 14px !important;
                margin-bottom: 22px !important;
            }
        }

        /* Search Box */
        .faq-search-wrapper {
            max-width: 630px;
            margin: 0 auto;
            position: relative;
        }

        .darli-faq-page .faq-search-box-inner,
        html[data-theme="dark"] .darli-faq-page .faq-search-box-inner {
            position: relative;
            background: #ffffff !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 9999px !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
            display: flex;
            align-items: center;
            transition: all 0.25s ease;
        }

        .darli-faq-page .faq-search-box-inner:focus-within,
        html[data-theme="dark"] .darli-faq-page .faq-search-box-inner:focus-within {
            border-color: #d97706 !important;
            box-shadow: 0 10px 30px -5px rgba(217, 119, 6, 0.15), 0 0 0 3px rgba(253, 230, 138, 0.4) !important;
        }

        .darli-faq-page input.faq-search-input,
        html[data-theme="dark"] .darli-faq-page input.faq-search-input,
        .darli-faq-page input#faqSearchInput,
        html[data-theme="dark"] .darli-faq-page input#faqSearchInput {
            width: 100% !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            color: #0f172a !important;
            padding: 15px 56px 15px 24px !important;
            border-radius: 9999px !important;
            font-size: 14px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        [dir="ltr"] .darli-faq-page input.faq-search-input,
        [dir="ltr"] .darli-faq-page input#faqSearchInput {
            padding: 15px 24px 15px 56px !important;
        }

        .darli-faq-page input.faq-search-input::placeholder,
        .darli-faq-page input#faqSearchInput::placeholder {
            color: #94a3b8 !important;
        }

        .faq-search-icon-btn {
            position: absolute;
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: transparent;
            border: none;
            color: #0f172a !important;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        [dir="ltr"] .faq-search-icon-btn {
            right: auto;
            left: 18px;
        }

        /* ---------------- Category Tabs ---------------- */
        .faq-tabs-section {
            max-width: 1100px;
            margin: 36px auto 32px auto;
            padding: 0 16px;
        }

        .faq-tabs-list {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 767px) {
            .faq-tabs-list {
                justify-content: flex-start;
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .faq-tabs-list::-webkit-scrollbar {
                display: none;
            }
        }

        @media (min-width: 768px) and (max-width: 1024px) {
            .faq-tab-btn {
                padding: 7px 11px !important;
                font-size: 11.5px !important;
                gap: 5px !important;
            }
        }

        .faq-tab-btn {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            color: #1e293b !important;
            padding: 9px 18px !important;
            border-radius: 9999px !important;
            font-size: 13.5px !important;
            font-weight: 600 !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02) !important;
        }

        .faq-tab-btn svg {
            flex-shrink: 0;
            color: #1e293b !important;
            transition: color 0.2s;
        }

        .faq-tab-btn:hover {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
        }

        .faq-tab-btn.is-active {
            background: #0f172a !important;
            color: #ffffff !important;
            border-color: #0f172a !important;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15) !important;
        }

        .faq-tab-btn.is-active svg {
            color: #ffffff !important;
        }

        /* The 4-grid icon on active "All Questions" tab is warm gold in reference */
        .faq-tab-btn.is-active[data-cat="all"] svg {
            color: #f59e0b !important;
        }

        /* ---------------- FAQ Accordion Cards ---------------- */
        .faq-list-container {
            max-width: 960px;
            margin: 0 auto 50px auto;
            padding: 0 16px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .faq-card {
            background: #ffffff !important;
            border-radius: 16px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.02) !important;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
            overflow: hidden;
        }

        .faq-card:hover {
            border-color: #cbd5e1 !important;
        }

        .faq-card.is-open {
            border-color: #f59e0b !important;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.08) !important;
        }

        .faq-card__header {
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            cursor: pointer;
            user-select: none;
        }

        @media (max-width: 640px) {
            .faq-card__header {
                padding: 16px;
                gap: 12px;
            }
        }

        /* Right side in RTL: Category badge + question text */
        .faq-card__right-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
        }

        @media (max-width: 640px) {
            .faq-card__right-group {
                flex-wrap: wrap;
                gap: 8px;
            }
        }

        /* Category Pill Badge on Card */
        .faq-card__badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef3c7 !important;
            border: 1px solid #fde68a !important;
            color: #b45309 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            padding: 5px 12px !important;
            border-radius: 8px !important;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .faq-card__badge svg {
            flex-shrink: 0;
            color: #b45309 !important;
        }

        .faq-card__question {
            font-size: 15.5px !important;
            font-weight: 700 !important;
            color: #0f172a !important;
            line-height: 1.5 !important;
            margin: 0 !important;
        }

        @media (max-width: 640px) {
            .faq-card__question {
                font-size: 14px !important;
            }
        }

        /* Left side in RTL: Circular Chevron Toggle */
        .faq-card__toggle-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f8fafc !important;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #475569 !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.2s ease, color 0.2s ease;
        }

        .faq-card.is-open .faq-card__toggle-icon {
            transform: rotate(180deg);
            background: #fef3c7 !important;
            color: #b45309 !important;
        }

        .faq-card__body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0, 1, 0, 1), padding 0.25s ease;
            padding: 0 24px;
        }

        .faq-card.is-open .faq-card__body {
            max-height: 1200px;
            padding: 0 24px 22px 24px;
            transition: max-height 0.45s ease-in-out, padding 0.25s ease;
        }

        @media (max-width: 640px) {
            .faq-card__body {
                padding: 0 16px;
            }

            .faq-card.is-open .faq-card__body {
                padding: 0 16px 18px 16px;
            }
        }

        .faq-card__answer {
            color: #475569 !important;
            font-size: 14.5px !important;
            line-height: 1.85 !important;
            margin: 0 !important;
        }

        @media (max-width: 640px) {
            .faq-card__answer {
                font-size: 13.5px !important;
            }
        }

        /* ---------------- Bottom Support CTA Banner ---------------- */
        .faq-cta-card {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .faq-cta-card__inner {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 24px !important;
            padding: 36px 40px !important;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03) !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        @media (max-width: 767px) {
            .faq-cta-card__inner {
                flex-direction: column;
                text-align: center;
                padding: 32px 20px !important;
            }

            .faq-cta__bubbles {
                display: none !important;
            }

            .faq-cta__agent {
                max-height: 110px;
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .faq-cta-card__inner {
                padding: 24px 20px !important;
                gap: 12px;
            }

            .faq-cta__agent {
                max-height: 125px;
            }

            .faq-cta__bubbles {
                max-height: 100px;
            }
        }

        .faq-cta__side-img {
            position: relative;
            z-index: 2;
            flex-shrink: 0;
            user-select: none;
            pointer-events: none;
        }

        .faq-cta__agent {
            max-height: 155px;
            width: auto;
            object-fit: contain;
        }

        .faq-cta__bubbles {
            max-height: 125px;
            width: auto;
            object-fit: contain;
        }

        .faq-cta__content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 520px;
            margin: 0 auto;
        }

        .faq-cta__dash {
            width: 32px;
            height: 3px;
            background: #c58a38 !important;
            border-radius: 9999px;
            margin: 0 auto 10px auto;
        }

        .faq-cta__badge {
            font-size: 13px !important;
            font-weight: 700 !important;
            color: #b45309 !important;
            margin-bottom: 6px;
        }

        .faq-cta__title {
            font-size: 24px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            margin: 0 0 10px 0 !important;
            letter-spacing: -0.3px;
        }

        @media (max-width: 640px) {
            .faq-cta__title {
                font-size: 20px !important;
            }
        }

        .faq-cta__desc {
            font-size: 14px !important;
            line-height: 1.7 !important;
            color: #64748b !important;
            margin: 0 auto 20px auto !important;
        }

        .faq-cta__actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .faq-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px !important;
            border-radius: 10px !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .faq-btn--gold {
            background: #c58a38 !important;
            color: #ffffff !important;
            border: 1.5px solid #c58a38 !important;
            box-shadow: 0 4px 12px rgba(197, 138, 56, 0.25) !important;
        }

        .faq-btn--gold:hover {
            background: #af772c !important;
            border-color: #af772c !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        .faq-btn--whatsapp {
            background: #ffffff !important;
            color: #10b981 !important;
            border: 1.5px solid #10b981 !important;
        }

        .faq-btn--whatsapp:hover {
            background: #10b981 !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }

        /* Empty state */
        .faq-empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #64748b !important;
            background: #ffffff !important;
            border-radius: 16px !important;
            border: 1px dashed #cbd5e1 !important;
        }
    </style>

    <div class="darli-faq-page">
        {{-- ==================== Hero Section ==================== --}}
        <section class="faq-hero">
            {{-- Side decorations from user reference --}}
            <img src="{{ asset('themes/homzen/images/faq-hero-left.png') }}" class="faq-hero__decor faq-hero__decor--left"
                alt="Luxury Villas Decor">
            <img src="{{ asset('themes/homzen/images/faq-hero-right.png') }}" class="faq-hero__decor faq-hero__decor--right"
                alt="City Skyline Decor">

            <div class="container">
                <div class="faq-hero__inner">
                    {{-- Breadcrumbs matching screenshot order --}}
                    <nav class="faq-breadcrumb" aria-label="breadcrumb">
                        <a href="{{ route('home', ['locale' => $locale]) }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                <polyline points="9 22 9 12 15 12 15 22" />
                            </svg>
                            <span>{{ $isAr ? 'الرئيسية' : 'Home' }}</span>
                        </a>
                        <span class="faq-breadcrumb__sep">&#8249;</span>
                        <span>{{ $isAr ? 'مركز المساعدة' : 'Help Center' }}</span>
                        <span class="faq-breadcrumb__sep">&#8249;</span>
                        <span class="faq-breadcrumb__current">{{ $isAr ? 'الأسئلة الشائعة' : 'FAQs' }}</span>
                    </nav>

                    {{-- Badge --}}
                    <div>
                        <div class="faq-hero__badge">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                                <path d="M12 17h.01" />
                            </svg>
                            <span>{{ $heroBadge }}</span>
                        </div>
                    </div>

                    {{-- Title --}}
                    <h1 class="faq-hero__title">{{ $heroTitle }}</h1>

                    {{-- Subtitle --}}
                    <p class="faq-hero__subtitle">{{ $heroSubtitle }}</p>

                    {{-- Search Box matching screenshot --}}
                    <div class="faq-search-wrapper">
                        <div class="faq-search-box-inner">
                            <span class="faq-search-icon-btn" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8" />
                                    <path d="m21 21-4.3-4.3" />
                                </svg>
                            </span>
                            <input type="text" id="faqSearchInput" class="faq-search-input hero-overlay-input"
                                placeholder="{{ $searchPlaceholder }}" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== Category Filter Tabs ==================== --}}
        <section class="faq-tabs-section">
            <div class="faq-tabs-list" id="faqTabsList">
                {{-- All Questions Tab --}}
                <button type="button" class="faq-tab-btn is-active" data-cat="all">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="14" rx="1" />
                        <rect width="7" height="7" x="3" y="14" rx="1" />
                    </svg>
                    <span>{{ $isAr ? 'جميع الأسئلة' : 'All Questions' }}</span>
                </button>

                {{-- 1: Real Estate Buying & Investing --}}
                <button type="button" class="faq-tab-btn" data-cat="الشراء والاستثمار العقاري">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                        <polyline points="17 6 23 6 23 12" />
                    </svg>
                    <span>{{ $isAr ? 'الشراء والاستثمار العقاري' : 'Buying & Investment' }}</span>
                </button>

                {{-- 2: Renting & Housing Units --}}
                <button type="button" class="faq-tab-btn" data-cat="الإيجار والوحدات السكنية">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                        <polyline points="9 22 9 12 15 12 15 22" />
                    </svg>
                    <span>{{ $isAr ? 'الإيجار والوحدات السكنية' : 'Renting & Units' }}</span>
                </button>

                {{-- 3: Verified Brokers & Companies --}}
                <button type="button" class="faq-tab-btn" data-cat="الوكلاء والشركات المعتمدة">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <span>{{ $isAr ? 'الوكلاء والشركات المعتمدة' : 'Verified Agencies' }}</span>
                </button>

                {{-- 4: Financing & Installments --}}
                <button type="button" class="faq-tab-btn" data-cat="التمويل والأقساط">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                        <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3" />
                    </svg>
                    <span>{{ $isAr ? 'التمويل والأقساط' : 'Financing & Payments' }}</span>
                </button>

                {{-- 5: Listing Properties --}}
                <button type="button" class="faq-tab-btn" data-cat="نشر العقارات">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    <span>{{ $isAr ? 'نشر العقارات' : 'Listing Properties' }}</span>
                </button>

                {{-- Any custom user categories added from admin --}}
                @foreach ($rawCategories as $customCat)
                    @if (
                        !in_array($customCat, [
                            'الشراء والاستثمار العقاري',
                            'الإيجار والوحدات السكنية',
                            'الوكلاء والشركات المعتمدة',
                            'التمويل والأقساط',
                            'نشر العقارات',
                        ]))
                        <button type="button" class="faq-tab-btn" data-cat="{{ $customCat }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="m10 15 5-3-5-3v6z" />
                            </svg>
                            <span>{{ $customCat }}</span>
                        </button>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- ==================== FAQ Accordion Cards ==================== --}}
        <section class="faq-list-container" id="faqItemsContainer">
            @forelse ($faqs as $index => $item)
                @php
                    $cat = $isAr ? $item['category'] ?? 'عام' : $item['category_en'] ?? 'General';
                    $q = $isAr ? $item['question'] ?? '' : $item['question_en'] ?? ($item['question'] ?? '');
                    $a = $isAr ? $item['answer'] ?? '' : $item['answer_en'] ?? ($item['answer'] ?? '');
                    $isOpen = $index === 0;
                @endphp
                <div class="faq-card {{ $isOpen ? 'is-open' : '' }}" data-category="{{ $cat }}"
                    data-question="{{ mb_strtolower($q) }}" data-answer="{{ mb_strtolower($a) }}">
                    <div class="faq-card__header" onclick="toggleFaqCard(this)">
                        {{-- Right side (in RTL): Category badge THEN Question text --}}
                        <div class="faq-card__right-group">
                            <div class="faq-card__badge">
                                @if (str_contains($cat, 'استثمار') || str_contains($cat, 'شراء') || str_contains(strtolower($cat), 'buy'))
                                    @if ($index === 0)
                                        {{-- Scales of justice icon for first legal question --}}
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
                                            <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
                                            <path d="M7 21h10" />
                                            <path d="M12 3v18" />
                                            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2" />
                                        </svg>
                                    @else
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18" />
                                            <polyline points="17 6 23 6 23 12" />
                                        </svg>
                                    @endif
                                @elseif (str_contains($cat, 'إيجار') || str_contains($cat, 'سكنية') || str_contains(strtolower($cat), 'rent'))
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                                        <polyline points="9 22 9 12 15 12 15 22" />
                                    </svg>
                                @elseif (str_contains($cat, 'وكلاء') || str_contains($cat, 'شركات') || str_contains(strtolower($cat), 'agent'))
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                        <path d="m9 12 2 2 4-4" />
                                    </svg>
                                @elseif (str_contains($cat, 'تمويل') || str_contains($cat, 'أقساط') || str_contains(strtolower($cat), 'finance'))
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <ellipse cx="12" cy="5" rx="9" ry="3" />
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5" />
                                        <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3" />
                                    </svg>
                                @elseif (str_contains($cat, 'نشر') || str_contains(strtolower($cat), 'list'))
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <path d="M12 16v-4" />
                                        <path d="M12 8h.01" />
                                    </svg>
                                @endif
                                <span>{{ $cat }}</span>
                            </div>
                            <h3 class="faq-card__question">{{ $q }}</h3>
                        </div>

                        {{-- Left side (in RTL): Circular Chevron Toggle --}}
                        <div class="faq-card__toggle-icon" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </div>
                    </div>
                    <div class="faq-card__body">
                        <p class="faq-card__answer">{{ $a }}</p>
                    </div>
                </div>
            @empty
                <div class="faq-empty-state">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px;">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" />
                        <path d="M12 17h.01" />
                    </svg>
                    <h4 style="font-weight: 700; color: #334155; margin-bottom: 6px;">
                        {{ $isAr ? 'لا توجد أسئلة شائعة مسجلة حالياً' : 'No questions found' }}</h4>
                    <p style="margin: 0; font-size: 14px;">
                        {{ $isAr ? 'يمكنك إضافة أسئلة جديدة من خلال لوحة التحكم.' : 'You can add questions via admin panel.' }}
                    </p>
                </div>
            @endforelse

            {{-- Hidden no-results message for live search --}}
            <div id="faqNoResults" class="faq-empty-state" style="display: none;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px;">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <h4 style="font-weight: 700; color: #334155; margin-bottom: 6px;">
                    {{ $isAr ? 'لم يتم العثور على نتائج مطابقة' : 'No matching questions found' }}</h4>
                <p style="margin: 0; font-size: 14px;">
                    {{ $isAr ? 'جرب البحث بكلمات أخرى أو تصفح التصنيفات أعلاه.' : 'Try searching with different terms or browse categories above.' }}
                </p>
            </div>
        </section>

        {{-- ==================== Bottom Support CTA Banner ==================== --}}
        <section class="faq-cta-card">
            <div class="faq-cta-card__inner">
                {{-- Right side: Customer service agent illustration --}}
                <div class="faq-cta__side-img">
                    <img src="{{ asset('themes/homzen/images/faq-cta-agent.png') }}" class="faq-cta__agent"
                        alt="Customer Support Agent">
                </div>

                {{-- Center content --}}
                <div class="faq-cta__content">
                    <div class="faq-cta__dash"></div>
                    <div class="faq-cta__badge">{{ $ctaBadge }}</div>
                    <h2 class="faq-cta__title">{{ $ctaTitle }}</h2>
                    <p class="faq-cta__desc">{{ $ctaDesc }}</p>

                    <div class="faq-cta__actions">
                        {{-- Contact Us Button (Right in RTL) --}}
                        <a href="{{ $ctaBtnUrl }}" class="faq-btn faq-btn--gold">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
                                <path
                                    d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
                            </svg>
                            <span>{{ $ctaBtnText }}</span>
                        </a>

                        {{-- WhatsApp Button (Left in RTL) --}}
                        <a href="https://wa.me/{{ $waDigits }}" target="_blank" rel="noopener noreferrer"
                            class="faq-btn faq-btn--whatsapp">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                            </svg>
                            <span>{{ $ctaWhatsappText }}</span>
                        </a>
                    </div>
                </div>

                {{-- Left side: 3D chat bubbles illustration --}}
                <div class="faq-cta__side-img">
                    <img src="{{ asset('themes/homzen/images/faq-cta-bubbles.png') }}" class="faq-cta__bubbles"
                        alt="Chat Bubbles">
                </div>
            </div>
        </section>
    </div>

    <script>
        function toggleFaqCard(headerEl) {
            const card = headerEl.closest('.faq-card');
            card.classList.toggle('is-open');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('faqSearchInput');
            const cards = document.querySelectorAll('.faq-card');
            const tabBtns = document.querySelectorAll('.faq-tab-btn');
            const noResultsEl = document.getElementById('faqNoResults');
            let currentCat = 'all';

            function applyFaqFilters() {
                const query = (searchInput.value || '').trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(card => {
                    const qText = (card.getAttribute('data-question') || '').toLowerCase();
                    const aText = (card.getAttribute('data-answer') || '').toLowerCase();
                    const cat = (card.getAttribute('data-category') || '').trim();

                    const matchesQuery = !query || qText.includes(query) || aText.includes(query);
                    const matchesCat = (currentCat === 'all') || (cat === currentCat) || cat.includes(
                        currentCat) || currentCat.includes(cat);

                    if (matchesQuery && matchesCat) {
                        card.style.display = 'block';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (noResultsEl) {
                    noResultsEl.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyFaqFilters);
            }

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    tabBtns.forEach(b => b.classList.remove('is-active'));
                    this.classList.add('is-active');
                    currentCat = this.getAttribute('data-cat') || 'all';
                    applyFaqFilters();
                });
            });
        });
    </script>
@endsection
