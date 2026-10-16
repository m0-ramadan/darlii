@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $trans = $page?->translation($locale);
    $pageTitle = !empty($contactInfo['hero_title'])
        ? $contactInfo['hero_title']
        : ($trans?->title ?:
        ($isAr
            ? 'تواصل معنا - منصة دارلي العقارية'
            : 'Contact Us - Darli Real Estate Platform'));
    $pageContent = !empty($contactInfo['hero_desc'])
        ? $contactInfo['hero_desc']
        : ($trans?->content ?:
        ($isAr
            ? 'نحن هنا دائماً لاستقبال استفساراتكم ومساعدتكم في اختيار عقاركم المثالي في محافظة بني سويف بكل سهولة واحترافية'
            : 'We are always glad to assist you in discovering the ideal property across Beni Suef and Egypt with complete ease.'));
@endphp

@section('title', $pageTitle)

@section('content')
    <style>
        /* ==========================================================================
           Darli Contact Us - Exact Reference Design Match
           ========================================================================== */

        :root {
            --darli-cnt-bg: #f8fafc;
            --darli-cnt-card: #ffffff;
            --darli-cnt-border: #edf2f7;
            --darli-cnt-navy: #102038;
            --darli-cnt-gold: #c58d34;
            --darli-cnt-gold-hover: #af7b28;
            --darli-cnt-gold-soft: #fdf6eb;
            --darli-cnt-gold-border: #f7e8ce;
            --darli-cnt-gold-text: #b8862d;
            --darli-cnt-muted: #64748b;
            --darli-cnt-shadow: 0 4px 18px -2px rgba(16, 32, 56, 0.04);
            --darli-cnt-shadow-hover: 0 10px 25px -4px rgba(16, 32, 56, 0.08);
            --darli-cnt-input-bg: #ffffff;
            --darli-cnt-input-border: #e2e8f0;
            --darli-cnt-green: #22c55e;
            --darli-cnt-green-hover: #16a34a;
        }

        html[data-theme="dark"] {
            --darli-cnt-bg: #0b1320;
            --darli-cnt-card: #111e33;
            --darli-cnt-border: #1e293b;
            --darli-cnt-navy: #f8fafc;
            --darli-cnt-muted: #94a3b8;
            --darli-cnt-gold-soft: #1d1c19;
            --darli-cnt-gold-border: #383020;
            --darli-cnt-gold-text: #d4a555;
            --darli-cnt-input-bg: #0d1627;
            --darli-cnt-input-border: #24344d;
            --darli-cnt-shadow: 0 4px 18px -2px rgba(0, 0, 0, 0.4);
            --darli-cnt-shadow-hover: 0 10px 25px -4px rgba(0, 0, 0, 0.6);
        }

        .darli-cnt-page {
            background-color: var(--darli-cnt-bg);
            min-height: 100vh;
            padding-bottom: 70px;
        }

        /* Hero Section */
        .darli-cnt-hero {
            position: relative;
            background: linear-gradient(180deg, #edf3f8 0%, #f6f8fb 100%);
            padding: 42px 0 68px 0;
            text-align: center;
            overflow: hidden;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        html[data-theme="dark"] .darli-cnt-hero {
            background: linear-gradient(180deg, #0e192b 0%, #0b1320 100%);
            border-bottom-color: #1e293b;
        }

        .darli-cnt-hero__villa {
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            width: 400px;
            background: url('/themes/homzen/images/contact-hero-villa.png') no-repeat right top;
            background-size: cover;
            opacity: 0.9;
            pointer-events: none;
            z-index: 1;
            mask-image: linear-gradient(to left, rgba(0, 0, 0, 0.95) 45%, rgba(0, 0, 0, 0) 100%);
            -webkit-mask-image: linear-gradient(to left, rgba(0, 0, 0, 0.95) 45%, rgba(0, 0, 0, 0) 100%);
        }

        .darli-cnt-hero__decor-left {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 320px;
            background: radial-gradient(circle at 10% 30%, rgba(200, 150, 62, 0.06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 1;
        }

        .darli-cnt-hero__pin {
            position: absolute;
            top: 48%;
            right: calc((100vw - min(100vw - 32px, 1200px)) / 2 + 180px);
            z-index: 3;
            width: 30px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            filter: drop-shadow(0 4px 10px rgba(184, 134, 45, 0.25));
            animation: darliPinFloat 3s ease-in-out infinite;
        }

        @keyframes darliPinFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @media (max-width: 991px) {
            .darli-cnt-hero__pin {
                display: none;
            }
        }

        .darli-cnt-hero__content {
            position: relative;
            z-index: 4;
            max-width: 680px;
            margin: 0 auto;
            padding: 0 16px;
        }

        .darli-cnt-hero__pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold-text);
            font-size: 12.5px;
            font-weight: 700;
            padding: 5px 16px;
            border-radius: 30px;
            margin-bottom: 14px;
        }

        .darli-cnt-hero__title {
            font-size: 30px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin-bottom: 12px;
            line-height: 1.35;
        }

        .darli-cnt-hero__desc {
            font-size: 14.5px;
            color: var(--darli-cnt-muted);
            line-height: 1.65;
            margin: 0;
        }

        /* Container & Cards */
        .darli-cnt-container {
            max-width: 1200px;
            margin: -34px auto 0;
            padding: 0 20px;
            position: relative;
            z-index: 5;
        }

        /* Top 3 Quick Info Cards */
        .darli-cnt-top-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 991px) {
            .darli-cnt-top-cards {
                grid-template-columns: 1fr;
            }
            .darli-cnt-hero__villa {
                display: none;
            }
            .darli-cnt-hero__pin {
                display: none;
            }
        }

        .darli-cnt-info-card {
            background: var(--darli-cnt-card);
            border: 1px solid var(--darli-cnt-border);
            border-radius: 16px;
            padding: 24px 22px 20px;
            box-shadow: var(--darli-cnt-shadow);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .darli-cnt-info-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--darli-cnt-shadow-hover);
        }

        .darli-cnt-icon-circle {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .darli-cnt-card-heading {
            font-size: 17px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin-bottom: 8px;
        }

        .darli-cnt-card-text {
            font-size: 12.5px;
            color: var(--darli-cnt-muted);
            line-height: 1.6;
            margin-bottom: 14px;
            min-height: 40px;
        }

        .darli-cnt-card-details {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
            flex-grow: 1;
            justify-content: center;
        }

        .darli-cnt-detail-row {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13.5px;
            font-weight: 700;
            color: var(--darli-cnt-navy);
            text-decoration: none;
        }

        .darli-cnt-soft-btn {
            width: 100%;
            height: 40px;
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold-text);
            font-size: 13px;
            font-weight: 700;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s, color 0.2s;
        }

        .darli-cnt-soft-btn:hover {
            background: #faeedb;
            color: #9d6d1d;
        }

        html[data-theme="dark"] .darli-cnt-soft-btn:hover {
            background: #2a2418;
            color: #e5b667;
        }

        /* Two Columns Main Section: Form (Right in RTL) & Sidebar (Left in RTL) */
        .darli-cnt-main-row {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 20px;
            margin-bottom: 24px;
            align-items: start;
        }

        @media (max-width: 991px) {
            .darli-cnt-main-row {
                grid-template-columns: 1fr;
            }
        }

        /* Form Card (In RTL: Right side) */
        .darli-cnt-form-card {
            background: var(--darli-cnt-card);
            border: 1px solid var(--darli-cnt-border);
            border-radius: 18px;
            padding: 30px;
            box-shadow: var(--darli-cnt-shadow);
        }

        .darli-cnt-form-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 24px;
        }

        .darli-cnt-form-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .darli-cnt-form-title-group h2 {
            font-size: 19px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin: 0 0 4px 0;
        }

        .darli-cnt-form-title-group p {
            font-size: 12.5px;
            color: var(--darli-cnt-muted);
            margin: 0;
            line-height: 1.5;
        }

        .darli-cnt-input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        @media (max-width: 640px) {
            .darli-cnt-input-row {
                grid-template-columns: 1fr;
            }
        }

        .darli-cnt-field {
            margin-bottom: 14px;
        }

        .darli-cnt-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--darli-cnt-navy);
            margin-bottom: 6px;
        }

        .darli-cnt-label .req {
            color: #ef4444;
            margin-right: 2px;
        }

        .darli-cnt-label .opt {
            color: var(--darli-cnt-muted);
            font-weight: 500;
            font-size: 11.5px;
        }

        .darli-cnt-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .darli-cnt-input,
        .darli-cnt-select,
        .darli-cnt-textarea {
            width: 100%;
            background: var(--darli-cnt-input-bg);
            border: 1px solid var(--darli-cnt-input-border);
            border-radius: 10px;
            padding: 10px 14px 10px 38px;
            font-size: 13.5px;
            color: var(--darli-cnt-navy);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .darli-cnt-input:focus,
        .darli-cnt-select:focus,
        .darli-cnt-textarea:focus {
            border-color: var(--darli-cnt-gold);
            box-shadow: 0 0 0 3px rgba(197, 141, 52, 0.14);
        }

        .darli-cnt-select {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left 12px center;
            background-size: 14px;
            cursor: pointer;
            padding-left: 32px;
        }

        .darli-cnt-input-icon {
            position: absolute;
            left: 12px;
            right: auto;
            color: #94a3b8;
            pointer-events: none;
        }

        .darli-cnt-textarea {
            resize: vertical;
            min-height: 120px;
            padding: 12px 14px 12px 38px;
            line-height: 1.6;
        }

        .darli-cnt-textarea-icon {
            position: absolute;
            left: 12px;
            right: auto;
            top: 14px;
            color: #94a3b8;
            pointer-events: none;
        }

        .darli-cnt-submit-btn {
            width: 100%;
            height: 48px;
            background: var(--darli-cnt-gold);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 14.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.15s ease;
            box-shadow: 0 4px 14px rgba(197, 141, 52, 0.25);
        }

        .darli-cnt-submit-btn:hover {
            background: var(--darli-cnt-gold-hover);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .darli-cnt-security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 14px;
            font-size: 12px;
            color: var(--darli-cnt-muted);
        }

        /* Sidebar Cards (In RTL: Left side) */
        .darli-cnt-sidebar {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .darli-cnt-side-card {
            background: var(--darli-cnt-card);
            border: 1px solid var(--darli-cnt-border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--darli-cnt-shadow);
        }

        /* Card 1: WhatsApp Instant */
        .darli-cnt-side-wa-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .darli-cnt-side-wa-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin: 0;
        }

        .darli-cnt-wa-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e8fbf0;
            color: var(--darli-cnt-green);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .darli-cnt-side-desc {
            font-size: 12px;
            color: var(--darli-cnt-muted);
            line-height: 1.55;
            margin-bottom: 14px;
        }

        .darli-cnt-wa-btn {
            width: 100%;
            height: 40px;
            background: var(--darli-cnt-green);
            color: #ffffff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            transition: background 0.2s;
            margin-bottom: 8px;
        }

        .darli-cnt-wa-btn:hover {
            background: var(--darli-cnt-green-hover);
            color: #ffffff;
        }

        .darli-cnt-call-btn-soft {
            width: 100%;
            height: 38px;
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold-text);
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            direction: ltr;
        }

        .darli-cnt-call-btn-soft:hover {
            background: #faeedb;
            color: #9d6d1d;
        }

        /* Card 2: Features */
        .darli-cnt-features-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .darli-cnt-features-head h3 {
            font-size: 15px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin: 0;
        }

        .darli-cnt-feat-item {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        html[data-theme="dark"] .darli-cnt-feat-item {
            background: #142033;
            border-color: #1e293b;
        }

        .darli-cnt-feat-item:last-child {
            margin-bottom: 0;
        }

        .darli-cnt-feat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            color: var(--darli-cnt-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .darli-cnt-feat-info strong {
            display: block;
            font-size: 12.5px;
            color: var(--darli-cnt-navy);
            margin-bottom: 2px;
        }

        .darli-cnt-feat-info span {
            display: block;
            font-size: 11px;
            color: var(--darli-cnt-muted);
            line-height: 1.4;
        }

        /* Card 3: FAQs */
        .darli-cnt-faq-head {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .darli-cnt-faq-head h3 {
            font-size: 15px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin: 0;
        }

        /* Map Box */
        .darli-cnt-map-card {
            background: var(--darli-cnt-card);
            border: 1px solid var(--darli-cnt-border);
            border-radius: 18px;
            padding: 22px 24px 20px;
            box-shadow: var(--darli-cnt-shadow);
        }

        .darli-cnt-map-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 16px;
        }

        @media (max-width: 640px) {
            .darli-cnt-map-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        .darli-cnt-map-title-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .darli-cnt-map-icon-box {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .darli-cnt-map-title-group h3 {
            font-size: 16.5px;
            font-weight: 800;
            color: var(--darli-cnt-navy);
            margin: 0 0 3px 0;
        }

        .darli-cnt-map-title-group p {
            font-size: 12px;
            color: var(--darli-cnt-muted);
            margin: 0;
        }

        .darli-cnt-google-btn {
            background: var(--darli-cnt-gold-soft);
            border: 1px solid var(--darli-cnt-gold-border);
            color: var(--darli-cnt-gold-text);
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            text-decoration: none;
            transition: background 0.2s;
            flex-shrink: 0;
        }

        .darli-cnt-google-btn:hover {
            background: #faeedb;
            color: #9d6d1d;
        }

        .darli-cnt-map-frame-box {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--darli-cnt-border);
            height: 280px;
        }

        .darli-cnt-map-frame {
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>

    <div class="darli-cnt-page">
        {{-- Hero Header --}}
        <header class="darli-cnt-hero">
            <div class="darli-cnt-hero__villa"></div>
            <div class="darli-cnt-hero__decor-left"></div>

            {{-- Floating Location Pin over villa --}}
            <div class="darli-cnt-hero__pin" title="دارلي - بني سويف">
                <svg width="28" height="36" viewBox="0 0 24 30" fill="none">
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 9 12 18 12 18s12-9 12-18c0-6.627-5.373-12-12-12z" fill="#f8eed9" stroke="#b8862d" stroke-width="1"/>
                    <circle cx="12" cy="11" r="4" fill="#94a3b8" />
                </svg>
            </div>

            <div class="darli-cnt-hero__content">
                <div class="darli-cnt-hero__pill">
                    <span>{{ !empty($contactInfo['hero_badge']) ? $contactInfo['hero_badge'] : ($isAr ? 'فريق خدمة العملاء والاستشارات' : 'Client Care & Advisory Team') }}</span>
                </div>

                <h1 class="darli-cnt-hero__title">{{ $pageTitle }}</h1>
                <p class="darli-cnt-hero__desc">{{ $pageContent }}</p>
            </div>
        </header>

        <div class="darli-cnt-container">
            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4 rounded-3 border" style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;" role="alert">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-4 rounded-3 border" style="background: #fef2f2; border-color: #fecaca; color: #991b1b;" role="alert">
                    <strong>{{ $isAr ? 'يرجى مراجعة وتصحيح البيانات التالية:' : 'Please check and fix the following fields:' }}</strong>
                    <ul class="mb-0 mt-1 pe-3">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 1. Top 3 Quick Info Cards --}}
            <div class="darli-cnt-top-cards">
                {{-- Card 1 (Right in RTL): Phone & Hotline --}}
                <div class="darli-cnt-info-card">
                    <div class="darli-cnt-icon-circle">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <h3 class="darli-cnt-card-heading">{{ $isAr ? 'الهاتف والخط الساخن' : 'Phone & Hotline' }}</h3>
                    <p class="darli-cnt-card-text">
                        {{ $isAr ? 'فريقنا متاح للرد على استفساراتكم وحجز المعاينات طوال أيام الأسبوع.' : 'Our team is available all week for inquiries and on-site viewings.' }}
                    </p>
                    <div class="darli-cnt-card-details">
                        <a href="tel:{{ $contactInfo['phone'] }}" class="darli-cnt-detail-row" dir="ltr">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#c58d34" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>{{ $contactInfo['phone'] }}</span>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo['whatsapp']) }}" target="_blank" rel="noopener" class="darli-cnt-detail-row" dir="ltr">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                            <span>{{ $contactInfo['whatsapp'] }} (واتساب)</span>
                        </a>
                    </div>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo['whatsapp']) }}?text={{ rawurlencode($isAr ? 'مرحباً، أود استشارة عقارية عبر منصة دارلي' : 'Hello, I would like to inquire about properties') }}" target="_blank" rel="noopener" class="darli-cnt-soft-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        <span>{{ $isAr ? 'محادثة فورية واتساب' : 'Chat via WhatsApp' }}</span>
                    </a>
                </div>

                {{-- Card 2 (Center in RTL): Headquarters --}}
                <div class="darli-cnt-info-card">
                    <div class="darli-cnt-icon-circle">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3 class="darli-cnt-card-heading">{{ $isAr ? 'المقر الرئيسي للمنصة' : 'Headquarters' }}</h3>
                    <p class="darli-cnt-card-text">
                        {{ $contactInfo['address'] }}
                    </p>
                    <div class="darli-cnt-card-details">
                        <div class="darli-cnt-detail-row" style="font-size: 12.5px; font-weight: 500; color: var(--darli-cnt-muted);">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#c58d34" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <span>{{ !empty($contactInfo['working_hours']) ? $contactInfo['working_hours'] : ($isAr ? 'يومياً من 9:00 ص حتى 9:00 م (الجمعة من 4 م إلى 9 م)' : 'Daily: 9:00 AM - 9:00 PM (Fri 4 PM - 9 PM)') }}</span>
                        </div>
                    </div>
                    <a href="#darliMapSection" class="darli-cnt-soft-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                            <line x1="8" y1="2" x2="8" y2="18"/>
                            <line x1="16" y1="6" x2="16" y2="22"/>
                        </svg>
                        <span>{{ $isAr ? 'الاطلاع على الخريطة' : 'View on Map' }}</span>
                    </a>
                </div>

                {{-- Card 3 (Left in RTL): Email Support --}}
                <div class="darli-cnt-info-card">
                    <div class="darli-cnt-icon-circle">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2"/>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </div>
                    <h3 class="darli-cnt-card-heading">{{ $isAr ? 'البريد الإلكتروني' : 'Email Support' }}</h3>
                    <p class="darli-cnt-card-text">
                        {{ $isAr ? 'لإرسال استفساراتكم وعروض الشراكات الاستثمارية أو أي استفسارات أخرى' : 'For inquiries, partnerships, investments, or general assistance' }}
                    </p>
                    <div class="darli-cnt-card-details">
                        <a href="mailto:{{ $contactInfo['email'] }}" class="darli-cnt-detail-row" dir="ltr">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#c58d34" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            <span>{{ $contactInfo['email'] }}</span>
                        </a>
                    </div>
                    <a href="mailto:{{ $contactInfo['email'] }}" class="darli-cnt-soft-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"/>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                        </svg>
                        <span>{{ $isAr ? 'إرسال بريد إلكتروني' : 'Send an Email' }}</span>
                    </a>
                </div>
            </div>

            {{-- 2. Main Middle Section: Form (Right in RTL) & Sidebar (Left in RTL) --}}
            <div class="darli-cnt-main-row">
                {{-- Right Side: The Inquiry Form --}}
                <div class="darli-cnt-form-card">
                    <div class="darli-cnt-form-header">
                        <div class="darli-cnt-form-icon-box">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                        </div>
                        <div class="darli-cnt-form-title-group">
                            <h2>{{ $isAr ? 'أرسل لنا استفسارك مباشرة' : 'Send Us an Inquiry' }}</h2>
                            <p>{{ $isAr ? 'املأ النموذج التالي وسيتواصل معك مستشار عقاري متخصص خلال أقل من ساعتين عمل.' : 'Fill out the form below and an advisor will contact you within two business hours.' }}</p>
                        </div>
                    </div>

                    <form action="{{ route('contact-us.submit', ['locale' => $locale]) }}" method="POST">
                        @csrf

                        {{-- Row 1: Name & Phone --}}
                        <div class="darli-cnt-input-row">
                            <div class="darli-cnt-field">
                                <label for="name" class="darli-cnt-label">
                                    {{ $isAr ? 'الإسم بالكامل' : 'Full Name' }} <span class="req">*</span>
                                </label>
                                <div class="darli-cnt-input-wrapper">
                                    <input type="text" id="name" name="name" class="darli-cnt-input"
                                        value="{{ old('name') }}"
                                        placeholder="{{ $isAr ? 'مثال: أحمد محمد علي' : 'e.g. John Doe' }}" required>
                                    <svg class="darli-cnt-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="darli-cnt-field">
                                <label for="phone" class="darli-cnt-label">
                                    {{ $isAr ? 'رقم الهاتف / الواتساب' : 'Phone / WhatsApp' }} <span class="req">*</span>
                                </label>
                                <div class="darli-cnt-input-wrapper">
                                    <input type="tel" id="phone" name="phone" class="darli-cnt-input"
                                        value="{{ old('phone') }}"
                                        placeholder="01001234567" required dir="ltr" style="text-align: right;">
                                    <svg class="darli-cnt-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Row 2: Email & Service Type --}}
                        <div class="darli-cnt-input-row">
                            <div class="darli-cnt-field">
                                <label for="email" class="darli-cnt-label">
                                    {{ $isAr ? 'البريد الإلكتروني' : 'Email Address' }} <span class="opt">({{ $isAr ? 'اختياري' : 'Optional' }})</span>
                                </label>
                                <div class="darli-cnt-input-wrapper">
                                    <input type="email" id="email" name="email" class="darli-cnt-input"
                                        value="{{ old('email') }}"
                                        placeholder="name@example.com" dir="ltr" style="text-align: right;">
                                    <svg class="darli-cnt-input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                    </svg>
                                </div>
                            </div>

                            <div class="darli-cnt-field">
                                <label for="subject" class="darli-cnt-label">
                                    {{ $isAr ? 'نوع الإستفسار أو الخدمة' : 'Inquiry / Service' }}
                                </label>
                                <div class="darli-cnt-input-wrapper">
                                    <select id="subject" name="subject" class="darli-cnt-select">
                                        <option value="{{ $isAr ? 'استفسار عن شراء عقار (سكني / تجاري)' : 'Property Purchase Inquiry' }}">
                                            {{ $isAr ? 'استفسار عن شراء عقار (سكني / تجاري)' : 'Property Purchase Inquiry' }}
                                        </option>
                                        <option value="{{ $isAr ? 'استفسار عن استئجار عقار' : 'Property Rental Inquiry' }}">
                                            {{ $isAr ? 'استفسار عن استئجار عقار' : 'Property Rental Inquiry' }}
                                        </option>
                                        <option value="{{ $isAr ? 'طلب حجز معاينة ميدانية' : 'Request On-Site Viewing' }}">
                                            {{ $isAr ? 'طلب حجز معاينة ميدانية' : 'Request On-Site Viewing' }}
                                        </option>
                                        <option value="{{ $isAr ? 'أرغب في عرض عقاري على المنصة' : 'List Property on Platform' }}">
                                            {{ $isAr ? 'أرغب في عرض عقاري على المنصة' : 'List Property on Platform' }}
                                        </option>
                                        <option value="{{ $isAr ? 'استشارة استثمارية وفرص تمويل' : 'Investment & Financing' }}">
                                            {{ $isAr ? 'استشارة استثمارية وفرص تمويل' : 'Investment & Financing' }}
                                        </option>
                                        <option value="{{ $isAr ? 'طلب انضمام كوكيل أو شركة عقارية' : 'Join as Agent / Brokerage' }}">
                                            {{ $isAr ? 'طلب انضمام كوكيل أو شركة عقارية' : 'Join as Agent / Brokerage' }}
                                        </option>
                                        <option value="{{ $isAr ? 'أخرى / ملاحظات عامة' : 'Other Inquiries' }}">
                                            {{ $isAr ? 'أخرى / ملاحظات عامة' : 'Other Inquiries' }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Row 3: Message Textarea --}}
                        <div class="darli-cnt-field">
                            <label for="message" class="darli-cnt-label">
                                {{ $isAr ? 'تفاصيل الرسالة أو الطلب' : 'Message / Details' }} <span class="req">*</span>
                            </label>
                            <div class="darli-cnt-input-wrapper">
                                <textarea id="message" name="message" rows="4" class="darli-cnt-textarea"
                                    placeholder="{{ $isAr ? 'اكتب تفاصيل العقار المطلوب، موقعه المفضل في بني سويف، ميزانيتك، أو أي أسئلة أخرى...' : 'Describe your property requirements, budget, preferred area, or questions...' }}"
                                    required>{{ old('message') }}</textarea>
                                <svg class="darli-cnt-textarea-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                </svg>
                            </div>
                        </div>

                        <button type="submit" class="darli-cnt-submit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                            <span>{{ $isAr ? 'إرسال الرسالة الآن' : 'Send Message Now' }}</span>
                        </button>

                        <div class="darli-cnt-security-note">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>{{ $isAr ? 'جميع بياناتك محفوظة وسرية ولا يتم مشاركتها مع أي جهة خارجية.' : 'Your data is strictly confidential and never shared with third parties.' }}</span>
                        </div>
                    </form>
                </div>

                {{-- Left Side: Fast Support Cards (In RTL, this is on the left) --}}
                <div class="darli-cnt-sidebar">
                    {{-- Side Card 1: WhatsApp Fast Channel --}}
                    <div class="darli-cnt-side-card">
                        <div class="darli-cnt-side-wa-head">
                            <h3 class="darli-cnt-side-wa-title">{{ $isAr ? 'تواصل سريع عبر واتساب' : 'Instant WhatsApp' }}</h3>
                            <div class="darli-cnt-wa-badge">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="darli-cnt-side-desc">
                            {{ $isAr ? 'للحصول على استجابة فورية من فريق المبيعات يمكنك التواصل معنا مباشرة عبر واتساب الآن.' : 'For instant assistance from our sales advisory team, connect via WhatsApp.' }}
                        </p>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo['whatsapp']) }}?text={{ rawurlencode($isAr ? 'مرحباً، أود استشارة عقارية عبر منصة دارلي' : 'Hello, I want an inquiry') }}" target="_blank" rel="noopener" class="darli-cnt-wa-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                            <span>{{ $isAr ? 'محادثة واتساب مباشرة' : 'Direct WhatsApp Chat' }}</span>
                        </a>
                        <a href="tel:{{ $contactInfo['phone'] }}" class="darli-cnt-call-btn-soft">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <span>{{ $isAr ? 'أو اتصل بخط الاستشارات: ' . $contactInfo['phone'] : 'Or Call Advisor: ' . $contactInfo['phone'] }}</span>
                        </a>
                    </div>

                    {{-- Side Card 2: Platform Features --}}
                    <div class="darli-cnt-side-card">
                        <div class="darli-cnt-features-head">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c58d34" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="6 3 18 3 22 9 12 22 2 9 6 3"></polygon>
                            </svg>
                            <h3>{{ $isAr ? 'مميزات منصة دارلي' : 'Darli Platform Features' }}</h3>
                        </div>

                        {{-- Item 1 --}}
                        <div class="darli-cnt-feat-item">
                            <div class="darli-cnt-feat-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <div class="darli-cnt-feat-info">
                                <strong>{{ $isAr ? 'مصداقية وأمان تام' : 'Full Authenticity & Safety' }}</strong>
                                <span>{{ $isAr ? 'معلومات العقارات موثقة ومفحوصة بالكامل.' : 'All listings are verified and inspected thoroughly.' }}</span>
                            </div>
                        </div>

                        {{-- Item 2 --}}
                        <div class="darli-cnt-feat-item">
                            <div class="darli-cnt-feat-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                </svg>
                            </div>
                            <div class="darli-cnt-feat-info">
                                <strong>{{ $isAr ? 'فريق متخصص ومحترف' : 'Specialized Advisors' }}</strong>
                                <span>{{ $isAr ? 'دعم واستشارات مجانية لمساعدتك في قرارك.' : 'Free expert guidance to help with every step.' }}</span>
                            </div>
                        </div>

                        {{-- Item 3 --}}
                        <div class="darli-cnt-feat-item">
                            <div class="darli-cnt-feat-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                                    <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
                                </svg>
                            </div>
                            <div class="darli-cnt-feat-info">
                                <strong>{{ $isAr ? 'متابعة بعد البيع' : 'Post-Purchase Support' }}</strong>
                                <span>{{ $isAr ? 'نحن معك حتى بعد إتمام العملية بخطوات مضمونة.' : 'We stay by your side until complete finalization.' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Side Card 3: FAQs Link --}}
                    <div class="darli-cnt-side-card">
                        <div class="darli-cnt-faq-head">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c58d34" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <h3>{{ $isAr ? 'هل لديك أسئلة متكررة؟' : 'Frequently Asked Questions?' }}</h3>
                        </div>
                        <p class="darli-cnt-side-desc">
                            {{ $isAr ? 'يمكنك زيارة صفحة الأسئلة الشائعة للحصول على إجابات سريعة لأكثر استفساراتكم.' : 'Visit our FAQ section for instant answers regarding contracts, viewings, and real estate laws.' }}
                        </p>
                        <a href="{{ route('faqs.index', ['locale' => $locale]) }}" class="darli-cnt-soft-btn">
                            <span>{{ $isAr ? 'تصفح صفحة الأسئلة الشائعة' : 'Browse FAQs' }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 19-7-7 7-7"/>
                                <path d="M19 12H5"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- 3. Bottom Card: Google Maps Section --}}
            <div class="darli-cnt-map-card" id="darliMapSection">
                <div class="darli-cnt-map-header">
                    <div class="darli-cnt-map-title-group">
                        <div class="darli-cnt-map-icon-box">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div>
                            <h3>{{ $isAr ? 'موقعنا على خريطة بني سويف' : 'Our Location on Beni Suef Map' }}</h3>
                            <p>{{ $contactInfo['address'] }}</p>
                        </div>
                    </div>

                    <a href="https://maps.google.com/?q={{ urlencode($contactInfo['address']) }}" target="_blank" rel="noopener" class="darli-cnt-google-btn">
                        <svg width="15" height="15" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.98 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                        </svg>
                        <span>{{ $isAr ? 'فتح في خرائط Google' : 'Open in Google Maps' }}</span>
                    </a>
                </div>

                <div class="darli-cnt-map-frame-box">
                    <iframe class="darli-cnt-map-frame"
                        src="{{ $contactInfo['map_embed_url'] ?? 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d111244.75704179374!2d31.0261352!3d29.0660727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x145a27845bc8bbcb%3A0xb36ef554a938c35b!2z2KjZhtmKINiz2YjZitmB2Iwg2YXYsdmD2LIg2KjZhtmKINiz2YjZitmB2Iwg2KjZhtmKINiz2YjZitmB2IwgNjc3NDAwMQ!5e0!3m2!1sar!2seg!4v1710000000000!5m2!1sar!2seg' }}"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
@endsection
