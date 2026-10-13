@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $trans = $page?->translation($locale);
    $pageTitle = $trans?->title ?: ($isAr ? 'تواصل معنا - منصة دارلي العقارية' : 'Contact Us - Darli Real Estate Platform');
    $pageContent = $trans?->content ?: ($isAr ? 'يسعدنا دائماً استقبال استفساراتكم ومساعدتكم في اختيار عقاركم المثالي بمحافظة بني سويف.' : 'We are always glad to assist you in discovering the ideal property across Beni Suef and Egypt.');
@endphp

@section('title', $pageTitle)

@section('content')
<style>
    /* ==========================================================================
       Darli Contact Us Page (صفحة اتصل بنا)
       ========================================================================== */

    :root {
        --darli-cnt-bg: #f8fafc;
        --darli-cnt-card: #ffffff;
        --darli-cnt-border: #e2e8f0;
        --darli-cnt-text: #0f172a;
        --darli-cnt-muted: #64748b;
        --darli-cnt-primary: #db1d23;
        --darli-cnt-primary-hover: #b9151b;
        --darli-cnt-primary-soft: rgba(219, 29, 35, 0.08);
        --darli-cnt-gold: #c8a45c;
        --darli-cnt-gold-soft: rgba(200, 164, 92, 0.12);
        --darli-cnt-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
        --darli-cnt-shadow-hover: 0 12px 28px -4px rgba(15, 23, 42, 0.09);
        --darli-cnt-input-bg: #ffffff;
        --darli-cnt-input-border: #cbd5e1;
    }

    html[data-theme="dark"] {
        --darli-cnt-bg: #0b1320;
        --darli-cnt-card: #111e33;
        --darli-cnt-border: #1e293b;
        --darli-cnt-text: #f8fafc;
        --darli-cnt-muted: #94a3b8;
        --darli-cnt-primary-soft: rgba(219, 29, 35, 0.18);
        --darli-cnt-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.4);
        --darli-cnt-shadow-hover: 0 12px 28px -4px rgba(0, 0, 0, 0.6);
        --darli-cnt-input-bg: #0d1627;
        --darli-cnt-input-border: #24344d;
    }

    /* Page Wrapper */
    .darli-cnt-wrapper {
        background-color: var(--darli-cnt-bg);
        min-height: 100vh;
        color: var(--darli-cnt-text);
        padding-bottom: 5rem;
    }

    /* Hero Section */
    .darli-cnt-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        border-bottom: 1px solid var(--darli-cnt-border);
        padding: 3.5rem 1.5rem 3rem;
        text-align: center;
    }

    html[data-theme="dark"] .darli-cnt-hero {
        background: linear-gradient(180deg, #0f1c30 0%, #0b1320 100%);
    }

    .darli-cnt-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--darli-cnt-primary-soft);
        color: var(--darli-cnt-primary);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.35rem 1rem;
        border-radius: 9999px;
        margin-bottom: 1rem;
        border: 1px solid rgba(219, 29, 35, 0.2);
    }

    .darli-cnt-title {
        font-size: 2.25rem;
        font-weight: 800;
        margin-bottom: 0.85rem;
        color: var(--darli-cnt-text);
        letter-spacing: -0.02em;
    }

    .darli-cnt-desc {
        max-width: 680px;
        margin: 0 auto;
        color: var(--darli-cnt-muted);
        font-size: 1.05rem;
        line-height: 1.7;
    }

    /* Cards Grid */
    .darli-cnt-container {
        max-width: 1240px;
        margin: -2rem auto 0;
        padding: 0 1.5rem;
        position: relative;
        z-index: 2;
    }

    .darli-cnt-cards-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    @media (max-width: 992px) {
        .darli-cnt-cards-grid {
            grid-template-columns: 1fr;
            margin-top: 2rem;
        }
    }

    .darli-cnt-card {
        background: var(--darli-cnt-card);
        border: 1px solid var(--darli-cnt-border);
        border-radius: 1.25rem;
        padding: 1.75rem;
        box-shadow: var(--darli-cnt-shadow);
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        display: flex;
        flex-direction: column;
    }

    .darli-cnt-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--darli-cnt-shadow-hover);
        border-color: rgba(219, 29, 35, 0.3);
    }

    .darli-cnt-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 1rem;
        background: var(--darli-cnt-primary-soft);
        color: var(--darli-cnt-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 1.25rem;
    }

    .darli-cnt-card-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--darli-cnt-text);
    }

    .darli-cnt-card-desc {
        color: var(--darli-cnt-muted);
        font-size: 0.92rem;
        margin-bottom: 1.25rem;
        line-height: 1.6;
        flex-grow: 1;
    }

    .darli-cnt-card-links {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .darli-cnt-card-link {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--darli-cnt-text);
        text-decoration: none;
        transition: color 0.2s ease;
        word-break: break-all;
    }

    .darli-cnt-card-link:hover {
        color: var(--darli-cnt-primary);
    }

    .darli-cnt-card-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: var(--darli-cnt-primary-soft);
        color: var(--darli-cnt-primary);
        padding: 0.6rem 1rem;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 0.9rem;
        text-decoration: none;
        transition: background-color 0.2s, color 0.2s;
        margin-top: 0.75rem;
    }

    .darli-cnt-card-btn:hover {
        background: var(--darli-cnt-primary);
        color: #ffffff;
    }

    /* Main Section Layout: Form + Info Sidebar */
    .darli-cnt-main-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 2rem;
        align-items: start;
        margin-bottom: 3.5rem;
    }

    @media (max-width: 992px) {
        .darli-cnt-main-grid {
            grid-template-columns: 1fr;
        }
    }

    .darli-cnt-form-box {
        background: var(--darli-cnt-card);
        border: 1px solid var(--darli-cnt-border);
        border-radius: 1.25rem;
        padding: 2.25rem;
        box-shadow: var(--darli-cnt-shadow);
    }

    .darli-cnt-form-header {
        margin-bottom: 1.75rem;
        border-bottom: 1px solid var(--darli-cnt-border);
        padding-bottom: 1.25rem;
    }

    .darli-cnt-form-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--darli-cnt-text);
        margin-bottom: 0.35rem;
    }

    .darli-cnt-form-sub {
        color: var(--darli-cnt-muted);
        font-size: 0.92rem;
    }

    .darli-form-group {
        margin-bottom: 1.25rem;
    }

    .darli-form-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--darli-cnt-text);
        margin-bottom: 0.45rem;
    }

    .darli-form-label .required {
        color: var(--darli-cnt-primary);
    }

    .darli-form-control {
        width: 100%;
        padding: 0.8rem 1rem;
        background: var(--darli-cnt-input-bg);
        border: 1px solid var(--darli-cnt-input-border);
        border-radius: 0.75rem;
        color: var(--darli-cnt-text);
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .darli-form-control:focus {
        border-color: var(--darli-cnt-primary);
        box-shadow: 0 0 0 3px rgba(219, 29, 35, 0.15);
    }

    .darli-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    @media (max-width: 640px) {
        .darli-form-row {
            grid-template-columns: 1fr;
        }
    }

    .darli-submit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        width: 100%;
        padding: 0.95rem 1.5rem;
        background: var(--darli-cnt-primary);
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        border: none;
        border-radius: 0.85rem;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.2s;
        box-shadow: 0 4px 14px rgba(219, 29, 35, 0.35);
    }

    .darli-submit-btn:hover {
        background: var(--darli-cnt-primary-hover);
        transform: translateY(-2px);
    }

    /* Sidebar Quick Channels */
    .darli-cnt-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .darli-cnt-side-card {
        background: var(--darli-cnt-card);
        border: 1px solid var(--darli-cnt-border);
        border-radius: 1.25rem;
        padding: 1.75rem;
        box-shadow: var(--darli-cnt-shadow);
    }

    .darli-cnt-side-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--darli-cnt-text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .darli-whatsapp-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        background: #25D366;
        color: #ffffff;
        font-weight: 700;
        font-size: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        text-decoration: none;
        transition: opacity 0.2s, transform 0.2s;
        box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3);
        margin-bottom: 0.75rem;
    }

    .darli-whatsapp-btn:hover {
        opacity: 0.95;
        transform: translateY(-2px);
        color: #ffffff;
    }

    .darli-call-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        background: var(--darli-cnt-primary-soft);
        color: var(--darli-cnt-primary);
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0.85rem 1rem;
        border-radius: 0.85rem;
        text-decoration: none;
        transition: background-color 0.2s, color 0.2s;
        border: 1px solid rgba(219, 29, 35, 0.2);
    }

    .darli-call-btn:hover {
        background: var(--darli-cnt-primary);
        color: #ffffff;
    }

    .darli-trust-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .darli-trust-item:last-child {
        margin-bottom: 0;
    }

    .darli-trust-icon {
        color: #10b981;
        font-size: 1.15rem;
        margin-top: 0.15rem;
        flex-shrink: 0;
    }

    .darli-trust-text {
        font-size: 0.88rem;
        color: var(--darli-cnt-muted);
        line-height: 1.55;
    }

    .darli-trust-text strong {
        color: var(--darli-cnt-text);
        display: block;
        margin-bottom: 0.15rem;
    }

    /* Map Box */
    .darli-cnt-map-box {
        background: var(--darli-cnt-card);
        border: 1px solid var(--darli-cnt-border);
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: var(--darli-cnt-shadow);
    }

    .darli-cnt-map-header {
        padding: 1.25rem 1.75rem;
        border-bottom: 1px solid var(--darli-cnt-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .darli-cnt-map-header-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--darli-cnt-text);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .darli-cnt-map-header-sub {
        font-size: 0.88rem;
        color: var(--darli-cnt-muted);
    }

    .darli-cnt-map-frame {
        width: 100%;
        height: 380px;
        border: 0;
        display: block;
    }

    /* Alerts */
    .darli-alert {
        padding: 1rem 1.25rem;
        border-radius: 0.85rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .darli-alert-success {
        background: rgba(16, 185, 129, 0.12);
        color: #065f46;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    html[data-theme="dark"] .darli-alert-success {
        background: rgba(16, 185, 129, 0.18);
        color: #34d399;
        border-color: rgba(16, 185, 129, 0.4);
    }

    .darli-alert-danger {
        background: rgba(239, 68, 68, 0.12);
        color: #991b1b;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    html[data-theme="dark"] .darli-alert-danger {
        background: rgba(239, 68, 68, 0.18);
        color: #f87171;
        border-color: rgba(239, 68, 68, 0.4);
    }
</style>

<div class="darli-cnt-wrapper">
    <!-- Hero Section -->
    <header class="darli-cnt-hero">
        <span class="darli-cnt-badge">
            <i class="icon icon-headphones"></i>
            {{ $isAr ? 'فريق خدمة العملاء والاستشارات' : 'Client Care & Advisory Team' }}
        </span>
        <h1 class="darli-cnt-title">{{ $pageTitle }}</h1>
        <p class="darli-cnt-desc">{{ $pageContent }}</p>
    </header>

    <div class="darli-cnt-container">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="darli-alert darli-alert-success" role="alert">
                <i class="icon icon-check-circle" style="font-size: 1.4rem;"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="darli-alert darli-alert-danger" role="alert">
                <i class="icon icon-alert-circle" style="font-size: 1.4rem;"></i>
                <div>
                    {{ $isAr ? 'يرجى مراجعة البيانات المدخلة وتصحيح الأخطاء التالية:' : 'Please review the entered data and fix the following errors:' }}
                    <ul style="margin: 0.35rem 0 0 1.25rem; font-weight: 500;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Quick Contact Info Cards -->
        <div class="darli-cnt-cards-grid">
            <!-- Card 1: Phone & WhatsApp -->
            <div class="darli-cnt-card">
                <div class="darli-cnt-card-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </div>
                <h3 class="darli-cnt-card-title">{{ $isAr ? 'الهاتف والخط الساخن' : 'Phone & Direct Line' }}</h3>
                <p class="darli-cnt-card-desc">
                    {{ $isAr ? 'فريقنا متاح للرد على استفساراتكم وحجز المعاينات طوال أيام الأسبوع.' : 'Our team is ready to assist with consultations and viewings all week.' }}
                </p>
                <div class="darli-cnt-card-links">
                    <a href="tel:{{ $contactInfo['phone'] }}" class="darli-cnt-card-link" dir="ltr">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--darli-cnt-primary);"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ $contactInfo['phone'] }}</span>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo['whatsapp']) }}" target="_blank" rel="noopener" class="darli-cnt-card-link" dir="ltr">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #25D366;"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                        <span>{{ $contactInfo['whatsapp'] }} (واتساب)</span>
                    </a>
                </div>
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo['whatsapp']) }}?text={{ urlencode($isAr ? 'مرحباً، أود الاستفسار عن عقارات منصة دارلي' : 'Hello, I would like to inquire about properties on Darli') }}" target="_blank" rel="noopener" class="darli-cnt-card-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>{{ $isAr ? 'محادثة فورية واتساب' : 'Chat via WhatsApp' }}</span>
                </a>
            </div>

            <!-- Card 2: HQ Location -->
            <div class="darli-cnt-card">
                <div class="darli-cnt-card-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <h3 class="darli-cnt-card-title">{{ $isAr ? 'المقر الرئيسي للمنصة' : 'Headquarters & Branch' }}</h3>
                <p class="darli-cnt-card-desc">
                    {{ $contactInfo['address'] }}
                </p>
                <div class="darli-cnt-card-links">
                    <span class="darli-cnt-card-link" style="color: var(--darli-cnt-muted); font-size: 0.9rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--darli-cnt-gold);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ $isAr ? 'يومياً: 9:00 ص حتى 9:00 م (الجمعة 1 م - 9 م)' : 'Daily: 9:00 AM - 9:00 PM (Fri 1 PM - 9 PM)' }}</span>
                    </span>
                </div>
                <a href="https://maps.google.com/?q={{ urlencode($contactInfo['address']) }}" target="_blank" rel="noopener" class="darli-cnt-card-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                    <span>{{ $isAr ? 'الاتجاهات على الخريطة' : 'Get Directions' }}</span>
                </a>
            </div>

            <!-- Card 3: Email & Support -->
            <div class="darli-cnt-card">
                <div class="darli-cnt-card-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </div>
                <h3 class="darli-cnt-card-title">{{ $isAr ? 'البريد والمراسلات الرسمية' : 'Email & Support' }}</h3>
                <p class="darli-cnt-card-desc">
                    {{ $isAr ? 'راسلنا بخصوص عروض الشراكة، الاستثمار المؤسسي، أو الاستفسارات العقارية العامة.' : 'Email us for partnerships, institutional investments, or general property inquiries.' }}
                </p>
                <div class="darli-cnt-card-links">
                    <a href="mailto:{{ $contactInfo['email'] }}" class="darli-cnt-card-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--darli-cnt-primary);"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <span>{{ $contactInfo['email'] }}</span>
                    </a>
                </div>
                <a href="mailto:{{ $contactInfo['email'] }}" class="darli-cnt-card-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    <span>{{ $isAr ? 'إرسال بريد إلكتروني' : 'Send an Email' }}</span>
                </a>
            </div>
        </div>

        <!-- Main Layout: Form + Side Channels -->
        <div class="darli-cnt-main-grid">
            <!-- Left / Main: The Form -->
            <div class="darli-cnt-form-box">
                <div class="darli-cnt-form-header">
                    <h2 class="darli-cnt-form-title">{{ $isAr ? 'أرسل لنا استفسارك مباشرة' : 'Send Us an Inquiry' }}</h2>
                    <p class="darli-cnt-form-sub">{{ $isAr ? 'املأ الحقول التالية وسيتواصل معك مستشار عقاري متخصص خلال أقل من ساعتين عمل.' : 'Fill in the fields below and an advisor will contact you within two business hours.' }}</p>
                </div>

                <form action="{{ route('contact-us.submit', ['locale' => $locale]) }}" method="POST">
                    @csrf

                    <div class="darli-form-row">
                        <!-- Name -->
                        <div class="darli-form-group">
                            <label for="name" class="darli-form-label">
                                {{ $isAr ? 'الاسم بالكامل' : 'Full Name' }} <span class="required">*</span>
                            </label>
                            <input type="text" id="name" name="name" class="darli-form-control" value="{{ old('name') }}" placeholder="{{ $isAr ? 'مثال: أحمد محمد علي' : 'e.g. John Doe' }}" required>
                        </div>

                        <!-- Phone -->
                        <div class="darli-form-group">
                            <label for="phone" class="darli-form-label">
                                {{ $isAr ? 'رقم الهاتف / الواتساب' : 'Phone / WhatsApp Number' }} <span class="required">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" class="darli-form-control" value="{{ old('phone') }}" placeholder="{{ $isAr ? 'مثال: 01001234567' : 'e.g. 01001234567' }}" required>
                        </div>
                    </div>

                    <div class="darli-form-row">
                        <!-- Email -->
                        <div class="darli-form-group">
                            <label for="email" class="darli-form-label">
                                {{ $isAr ? 'البريد الإلكتروني' : 'Email Address' }} <span style="font-weight: 400; color: var(--darli-cnt-muted);">({{ $isAr ? 'اختياري' : 'Optional' }})</span>
                            </label>
                            <input type="email" id="email" name="email" class="darli-form-control" value="{{ old('email') }}" placeholder="{{ $isAr ? 'name@example.com' : 'name@example.com' }}">
                        </div>

                        <!-- Subject -->
                        <div class="darli-form-group">
                            <label for="subject" class="darli-form-label">
                                {{ $isAr ? 'نوع الاستفسار أو الخدمة' : 'Inquiry / Subject' }}
                            </label>
                            <select id="subject" name="subject" class="darli-form-control">
                                <option value="{{ $isAr ? 'استفسار عن شراء عقار' : 'Property Purchase Inquiry' }}">{{ $isAr ? 'استفسار عن شراء عقار (سكني / تجاري)' : 'Property Purchase Inquiry' }}</option>
                                <option value="{{ $isAr ? 'استفسار عن إيجار' : 'Property Rental Inquiry' }}">{{ $isAr ? 'استفسار عن استئجار عقار' : 'Property Rental Inquiry' }}</option>
                                <option value="{{ $isAr ? 'طلب معاينة ميدانية' : 'Request On-Site Viewing' }}">{{ $isAr ? 'طلب حجز معاينة ميدانية' : 'Request On-Site Viewing' }}</option>
                                <option value="{{ $isAr ? 'عرض عقار للبيع أو الإيجار' : 'List Property for Sale/Rent' }}">{{ $isAr ? 'أرغب في عرض عقاري على المنصة' : 'List Property on Platform' }}</option>
                                <option value="{{ $isAr ? 'استشارة استثمارية وتمويل' : 'Investment & Financing Consultation' }}">{{ $isAr ? 'استشارة استثمارية وفرص تمويل' : 'Investment & Financing' }}</option>
                                <option value="{{ $isAr ? 'انضمام كوكيل أو شركة عقارية' : 'Join as Agent / Agency' }}">{{ $isAr ? 'طلب انضمام كوكيل أو شركة عقارية' : 'Join as Agent / Brokerage' }}</option>
                                <option value="{{ $isAr ? 'شكاوى وملاحظات' : 'Feedback / Inquiry' }}">{{ $isAr ? 'أخرى / ملاحظات عامة' : 'Other Inquiries' }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="darli-form-group">
                        <label for="message" class="darli-form-label">
                            {{ $isAr ? 'تفاصيل الرسالة أو الطلب' : 'Message / Details' }} <span class="required">*</span>
                        </label>
                        <textarea id="message" name="message" rows="5" class="darli-form-control" placeholder="{{ $isAr ? 'اكتب تفاصيل العقار المطلوب، موقعه المفضل في بني سويف، ميزانيتك، أو أي أسئلة أخرى...' : 'Describe your property requirements, budget, preferred area, or questions...' }}" required>{{ old('message') }}</textarea>
                    </div>

                    <button type="submit" class="darli-submit-btn">
                        <i class="icon icon-send"></i>
                        <span>{{ $isAr ? 'إرسال الرسالة الآن' : 'Send Message Now' }}</span>
                    </button>
                </form>
            </div>

            <!-- Right: Fast Channels & Guarantees -->
            <div class="darli-cnt-sidebar">
                <!-- Instant Chat Box -->
                <div class="darli-cnt-side-card">
                    <h3 class="darli-cnt-side-title">
                        <i class="icon icon-zap" style="color: #eab308;"></i>
                        {{ $isAr ? 'تواصل فوري وسريع' : 'Instant Channels' }}
                    </h3>
                    <p style="color: var(--darli-cnt-muted); font-size: 0.9rem; margin-bottom: 1.25rem; line-height: 1.6;">
                        {{ $isAr ? 'إذا كنت بحاجة إلى استجابة فورية بخصوص معاينة عاجلة أو استفسار طارئ:' : 'If you need instant assistance regarding an urgent viewing or property lookup:' }}
                    </p>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactInfo['whatsapp']) }}?text={{ urlencode($isAr ? 'مرحباً، أود استشارة عقارية عاجلة عبر دارلي' : 'Hello, I need an urgent consultation on Darli') }}" target="_blank" rel="noopener" class="darli-whatsapp-btn">
                        <i class="icon icon-whatsapp" style="font-size: 1.2rem;"></i>
                        <span>{{ $isAr ? 'محادثة واتساب مباشرة' : 'Direct WhatsApp Chat' }}</span>
                    </a>
                    <a href="tel:{{ $contactInfo['phone'] }}" class="darli-call-btn">
                        <i class="icon icon-phone-call"></i>
                        <span>{{ $isAr ? 'اتصل بخط الاستشارات: ' . $contactInfo['phone'] : 'Call Advisor: ' . $contactInfo['phone'] }}</span>
                    </a>
                </div>

                <!-- Trust and Transparency -->
                <div class="darli-cnt-side-card">
                    <h3 class="darli-cnt-side-title">
                        <i class="icon icon-shield-check" style="color: #10b981;"></i>
                        {{ $isAr ? 'ضمانات منصة دارلي' : 'Darli Platform Guarantees' }}
                    </h3>

                    <div class="darli-trust-item">
                        <i class="icon icon-check-circle darli-trust-icon"></i>
                        <div class="darli-trust-text">
                            <strong>{{ $isAr ? 'خصوصية وأمان تام' : 'Privacy & Data Protection' }}</strong>
                            {{ $isAr ? 'معلومات الاتصال الخاصة بك مشفرة ولا يتم مشاركتها أبداً مع جهات إعلانية مجهولة.' : 'Your contact information is strictly confidential and protected.' }}
                        </div>
                    </div>

                    <div class="darli-trust-item">
                        <i class="icon icon-check-circle darli-trust-icon"></i>
                        <div class="darli-trust-text">
                            <strong>{{ $isAr ? 'وكلاء ومطورون معتمدون' : 'Verified Brokers & Agents' }}</strong>
                            {{ $isAr ? 'جميع المعاينات يتم تنسيقها مباشرة مع الملاك أو الوكلاء الموثقين لدينا ببني سويف.' : 'All viewings are scheduled directly with verified sellers and agents.' }}
                        </div>
                    </div>

                    <div class="darli-trust-item">
                        <i class="icon icon-check-circle darli-trust-icon"></i>
                        <div class="darli-trust-text">
                            <strong>{{ $isAr ? 'متابعة شفافة برقم مرجعي' : 'Trackable Inquiries' }}</strong>
                            {{ $isAr ? 'تحصل على كود مرجعي فوري لمتابعة حالة طلبك ومعاينتك خطوة بخطوة.' : 'Get a distinct reference code to monitor your inquiry progress.' }}
                        </div>
                    </div>
                </div>

                <!-- Need FAQs? -->
                <div class="darli-cnt-side-card" style="background: linear-gradient(135deg, rgba(219, 29, 35, 0.04) 0%, rgba(200, 164, 92, 0.04) 100%);">
                    <h4 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--darli-cnt-text);">
                        {{ $isAr ? 'هل لديك أسئلة متكررة؟' : 'Frequently Asked Questions?' }}
                    </h4>
                    <p style="color: var(--darli-cnt-muted); font-size: 0.88rem; margin-bottom: 1rem; line-height: 1.55;">
                        {{ $isAr ? 'يمكنك قراءة إجابات وافية بخصوص إجراءات التسجيل العقاري والشراء والتمويل في بني سويف.' : 'Find instant answers about registration, contracts, and verified listings in our FAQs.' }}
                    </p>
                    <a href="{{ route('faqs.index', ['locale' => $locale]) }}" style="font-weight: 700; color: var(--darli-cnt-primary); font-size: 0.92rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
                        <span>{{ $isAr ? 'تصفح صفحة الأسئلة الشائعة' : 'Visit FAQ Page' }}</span>
                        <i class="icon {{ $isRtl ? 'icon-arrow-left' : 'icon-arrow-right' }}"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Google Maps Embed Section -->
        <div class="darli-cnt-map-box">
            <div class="darli-cnt-map-header">
                <div>
                    <h3 class="darli-cnt-map-header-title">
                        <i class="icon icon-map" style="color: var(--darli-cnt-primary);"></i>
                        {{ $isAr ? 'موقعنا على خريطة بني سويف' : 'Our Location on Beni Suef Map' }}
                    </h3>
                    <div class="darli-cnt-map-header-sub">
                        {{ $contactInfo['address'] }}
                    </div>
                </div>
                <a href="https://maps.google.com/?q={{ urlencode($contactInfo['address']) }}" target="_blank" rel="noopener" class="darli-cnt-card-btn" style="margin-top: 0;">
                    <i class="icon icon-external-link"></i>
                    {{ $isAr ? 'فتح في خرائط Google' : 'Open in Google Maps' }}
                </a>
            </div>
            <iframe 
                class="darli-cnt-map-frame"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d111244.75704179374!2d31.0261352!3d29.0660727!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x145a27845bc8bbcb%3A0xb36ef554a938c35b!2z2KjZhtmKINiz2YjZitmB2Iwg2YXYsdmD2LIg2KjZhtmKINiz2YjZitmB2Iwg2KjZhtmKINiz2YjZitmB2IwgNjc3NDAwMQ!5e0!3m2!1sar!2seg!4v1710000000000!5m2!1sar!2seg"
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</div>
@endsection
