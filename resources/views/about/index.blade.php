@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $pageTitle = $isAr ? 'معلومات عنا - دارلي للاستثمار العقاري' : 'About Us - Darli Real Estate';
@endphp

@section('title', $pageTitle)

@section('content')
<link rel="stylesheet" href="/themes/homzen/css/darli-about-us.css?v={{ time() }}">
<main class="darli-about-page" dir="rtl">

    <!-- ======================================================================
         1. Hero Header
         ====================================================================== -->
    <section class="darli-about-hero">
        <div class="container">
            <span class="darli-about-eyebrow">— من نحن —</span>
            <h1 class="darli-about-hero-title">معلومات عنا</h1>
            <p class="darli-about-hero-sub">
                في دارلي نؤمن أن العقار أكثر من مجرد مكان،<br>
                بل هو بداية حياة أفضل
            </p>
        </div>
    </section>

    <!-- ======================================================================
         2. About Darli Intro Section
         ====================================================================== -->
    <section class="darli-about-intro-section">
        <div class="container">
            <div class="darli-about-intro-grid">
                <!-- Text Column (Right in RTL) -->
                <div class="darli-about-intro-content">
                    <span class="darli-about-eyebrow">— عن دارلي —</span>
                    <h2 class="darli-about-heading">شركة دارلي للاستثمار العقاري والمقاولات</h2>
                    <p class="darli-about-desc">
                        نحن شركة مصرية متخصصة في الاستثمار العقاري والتطوير والمقاولات، نقدم حلولاً متكاملة تلبي احتياجات عملائنا من السكن والاستثمار في محافظة بني سويف والمناطق المجاورة، مع الالتزام بأعلى معايير الجودة والمصداقية.
                    </p>
                    <a href="/projects" class="darli-btn-gold">
                        <span>تعرف على مشاريعنا</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                    </a>
                </div>

                <!-- Image Column (Left in RTL) -->
                <div class="darli-about-image-wrap">
                    <img src="/themes/homzen/images/about/darli-about-villa.png" alt="شركة دارلي للاستثمار العقاري">
                    <div class="darli-about-badge">
                        <div class="darli-about-badge-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                        <div class="darli-about-badge-text">
                            <span class="badge-title">خبرة محلية</span>
                            <span class="badge-subtitle">برؤية مستقبلية</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==============================================================
                 3. Stats Bar
                 ============================================================== -->
            <div class="darli-about-stats-wrap">
                <div class="darli-about-stats-grid">
                    <!-- Stat 1 -->
                    <div class="darli-stat-item">
                        <div class="darli-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div class="darli-stat-number">+500</div>
                        <div class="darli-stat-label">عميل سعيد</div>
                    </div>

                    <!-- Stat 2 -->
                    <div class="darli-stat-item">
                        <div class="darli-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                <line x1="9" y1="22" x2="9" y2="22.01"></line>
                                <line x1="15" y1="22" x2="15" y2="22.01"></line>
                                <line x1="9" y1="18" x2="9" y2="18.01"></line>
                                <line x1="15" y1="18" x2="15" y2="18.01"></line>
                                <line x1="9" y1="14" x2="9" y2="14.01"></line>
                                <line x1="15" y1="14" x2="15" y2="14.01"></line>
                                <line x1="9" y1="10" x2="9" y2="10.01"></line>
                                <line x1="15" y1="10" x2="15" y2="10.01"></line>
                                <line x1="9" y1="6" x2="9" y2="6.01"></line>
                                <line x1="15" y1="6" x2="15" y2="6.01"></line>
                            </svg>
                        </div>
                        <div class="darli-stat-number">+120</div>
                        <div class="darli-stat-label">مشروع ومطور</div>
                    </div>

                    <!-- Stat 3 -->
                    <div class="darli-stat-item">
                        <div class="darli-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="8" r="7"></circle>
                                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                            </svg>
                        </div>
                        <div class="darli-stat-number">+10</div>
                        <div class="darli-stat-label">سنوات خبرة</div>
                    </div>

                    <!-- Stat 4 -->
                    <div class="darli-stat-item">
                        <div class="darli-stat-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </div>
                        <div class="darli-stat-number">+5</div>
                        <div class="darli-stat-label">مناطق مميزة</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         4. Why Darli Section
         ====================================================================== -->
    <section class="darli-about-why-section">
        <div class="container">
            <div class="darli-about-section-header">
                <span class="darli-about-eyebrow">لماذا دارلي؟</span>
                <h2 class="darli-about-heading">نقدم لك تجربة عقارية متكاملة</h2>
            </div>

            <div class="darli-about-why-grid">
                <!-- Why 1 -->
                <div class="darli-why-card">
                    <div class="darli-why-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 3h12l4 6-10 13L2 9z"></path>
                            <path d="M11 3L8 9l4 13 4-13-3-6"></path>
                            <path d="M2 9h20"></path>
                        </svg>
                    </div>
                    <h3 class="darli-why-card-title">جودة في التنفيذ</h3>
                    <p class="darli-why-card-desc">نحرص على تقديم أعلى معايير الجودة</p>
                </div>

                <!-- Why 2 -->
                <div class="darli-why-card">
                    <div class="darli-why-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="10" height="17" rx="1"></rect>
                            <rect x="13" y="8" width="8" height="13" rx="1"></rect>
                            <path d="M7 8h2"></path>
                            <path d="M7 12h2"></path>
                            <path d="M7 16h2"></path>
                            <path d="M16 12h2"></path>
                            <path d="M16 16h2"></path>
                        </svg>
                    </div>
                    <h3 class="darli-why-card-title">مشاريع مميزة</h3>
                    <p class="darli-why-card-desc">مجموعة متنوعة من العقارات في أفضل المواقع</p>
                </div>

                <!-- Why 3 -->
                <div class="darli-why-card">
                    <div class="darli-why-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>
                    </div>
                    <h3 class="darli-why-card-title">مصداقية وشفافية</h3>
                    <p class="darli-why-card-desc">نلتزم بالوضوح في جميع تعاملاتنا</p>
                </div>

                <!-- Why 4 -->
                <div class="darli-why-card">
                    <div class="darli-why-icon-wrap">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
                            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
                        </svg>
                    </div>
                    <h3 class="darli-why-card-title">دعم مستمر</h3>
                    <p class="darli-why-card-desc">فريق متخصص لمساعدتك في كل خطوة</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         5. Vision & Mission Section
         ====================================================================== -->
    <section class="darli-about-vision-section">
        <div class="container">
            <div class="darli-about-vision-grid">
                <!-- Text and Cards Column (Right in RTL) -->
                <div class="darli-vision-content">
                    <span class="darli-about-eyebrow">— رؤيتنا ورسالتنا —</span>
                    <h2 class="darli-about-heading">نبني اليوم لمستقبل أفضل</h2>

                    <div class="darli-vision-cards-grid">
                        <!-- Vision Card -->
                        <div class="darli-vision-card">
                            <div class="darli-vision-card-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="3"></circle>
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                </svg>
                            </div>
                            <h3 class="darli-vision-card-title">رؤيتنا</h3>
                            <p class="darli-vision-card-text">
                                أن نكون الخيار الأول في مجال الاستثمار العقاري في بني سويف والمناطق المجاورة.
                            </p>
                        </div>

                        <!-- Mission Card -->
                        <div class="darli-vision-card">
                            <div class="darli-vision-card-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                                </svg>
                            </div>
                            <h3 class="darli-vision-card-title">رسالتنا</h3>
                            <p class="darli-vision-card-text">
                                تقديم حلول عقارية مبتكرة تجمع بين الجودة والموقع المتميز لتلبية احتياجات عملائنا وتحقيق مستقبل أفضل لهم.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Image Column (Left in RTL) -->
                <div class="darli-vision-image-wrap">
                    <img src="/themes/homzen/images/about/darli-about-balcony.png" alt="رؤيتنا ورسالتنا - دارلي">
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         6. Consultation & Contact Section
         ====================================================================== -->
    <section class="darli-about-consult-section">
        <div class="container">
            <div class="darli-about-consult-grid">
                <!-- Text and Features (Right in RTL) -->
                <div class="darli-consult-content">
                    <span class="darli-about-eyebrow">نحن دائماً في خدمتك</span>
                    <h2 class="darli-about-heading">تحدث معنا الآن</h2>
                    <p class="darli-about-desc">
                        فريق دارلي جاهز لمساعدتك في إيجاد العقار المناسب لك
                    </p>

                    <div class="darli-consult-features-row">
                        <!-- Feature 1 -->
                        <div class="darli-consult-feature-item">
                            <div class="darli-consult-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                    <polyline points="9 12 11 14 15 10"></polyline>
                                </svg>
                            </div>
                            <span class="darli-consult-feature-label">استجابة سريعة</span>
                        </div>

                        <!-- Feature 2 -->
                        <div class="darli-consult-feature-item">
                            <div class="darli-consult-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </div>
                            <span class="darli-consult-feature-label">استشارات مجانية</span>
                        </div>

                        <!-- Feature 3 -->
                        <div class="darli-consult-feature-item">
                            <div class="darli-consult-feature-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <span class="darli-consult-feature-label">دعم كامل</span>
                        </div>
                    </div>
                </div>

                <!-- Form Card (Left in RTL) -->
                <div class="darli-consult-form-card">
                    <h3 class="darli-consult-form-title">تواصل معنا</h3>
                    <p class="darli-consult-form-sub">نحن هنا للإجابة على استفساراتك</p>

                    <div id="darli-consult-alert" style="display: none; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; font-weight: 600;"></div>

                    <form id="darli-consult-form" action="{{ route('contact-us.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="subject" value="استشارة من صفحة معلومات عنا">

                        <div class="darli-consult-form-group">
                            <span class="form-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <input type="text" name="name" class="darli-consult-input" placeholder="الاسم" required>
                        </div>

                        <div class="darli-consult-form-group">
                            <span class="form-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                            </span>
                            <input type="tel" name="phone" class="darli-consult-input" placeholder="رقم الهاتف" required>
                        </div>

                        <div class="darli-consult-form-group">
                            <span class="form-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </span>
                            <textarea name="message" class="darli-consult-input darli-consult-textarea" placeholder="الرسالة" required></textarea>
                        </div>

                        <button type="submit" class="darli-btn-gold darli-btn-submit-consult" id="darli-consult-submit-btn">
                            <span>إرسال الرسالة</span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                <polyline points="12 19 5 12 12 5"></polyline>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         7. Process Steps Section
         ====================================================================== -->
    <section class="darli-about-steps-section">
        <div class="container">
            <div class="darli-about-section-header">
                <h2 class="darli-about-heading">فريقنا يرافقك في كل خطوة</h2>
                <p class="darli-about-desc">من الاستشارة الأولى حتى استلام مفتاح وحدتك</p>
            </div>

            <div class="darli-about-steps-grid">
                <!-- Step 1 -->
                <div class="darli-step-card">
                    <div class="darli-step-card-image-wrap">
                        <img src="/themes/homzen/images/about/step-1.png" alt="استشارة مخصصة">
                        <span class="darli-step-number-badge">01</span>
                    </div>
                    <div class="darli-step-card-body">
                        <h3 class="darli-step-card-title">استشارة مخصصة</h3>
                        <p class="darli-step-card-desc">نستمع لاحتياجاتك ونرشح لك أفضل الخيارات</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="darli-step-card">
                    <div class="darli-step-card-image-wrap">
                        <img src="/themes/homzen/images/about/step-2.png" alt="إجراءات سهلة">
                        <span class="darli-step-number-badge">02</span>
                    </div>
                    <div class="darli-step-card-body">
                        <h3 class="darli-step-card-title">إجراءات سهلة</h3>
                        <p class="darli-step-card-desc">نساعدك في جميع الإجراءات بكل سهولة وشفافية</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="darli-step-card">
                    <div class="darli-step-card-image-wrap">
                        <img src="/themes/homzen/images/about/step-3.png" alt="متابعة ما بعد البيع">
                        <span class="darli-step-number-badge">03</span>
                    </div>
                    <div class="darli-step-card-body">
                        <h3 class="darli-step-card-title">متابعة ما بعد البيع</h3>
                        <p class="darli-step-card-desc">نبقى معك دائماً حتى بعد إتمام الصفقة</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         8. Bottom CTA Banner
         ====================================================================== -->
    <section class="darli-about-cta-section">
        <div class="container">
            <div class="darli-about-cta-box">
                <div class="darli-about-cta-content">
                    <span class="darli-about-eyebrow">— ابدأ رحلتك العقارية الآن —</span>
                    <h2 class="darli-about-cta-title">اكتشف أحدث مشاريع دارلي</h2>
                    <a href="/projects" class="darli-btn-gold">
                        <span>عرض المشاريع</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('darli-consult-form');
    const alertBox = document.getElementById('darli-consult-alert');
    const submitBtn = document.getElementById('darli-consult-submit-btn');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>جاري الإرسال...</span>';

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alertBox.style.display = 'block';
                if (data.success) {
                    alertBox.style.backgroundColor = '#dcfce7';
                    alertBox.style.color = '#166534';
                    alertBox.style.border = '1px solid #bbf7d0';
                    alertBox.innerText = data.message || 'تم إرسال رسالتك بنجاح! سيتواصل معك فريق دارلي قريباً.';
                    form.reset();
                } else {
                    alertBox.style.backgroundColor = '#fee2e2';
                    alertBox.style.color = '#991b1b';
                    alertBox.style.border = '1px solid #fecaca';
                    alertBox.innerText = data.message || 'حدث خطأ أثناء الإرسال، يرجى المحاولة مرة أخرى.';
                }
            })
            .catch(err => {
                alertBox.style.display = 'block';
                alertBox.style.backgroundColor = '#fee2e2';
                alertBox.style.color = '#991b1b';
                alertBox.style.border = '1px solid #fecaca';
                alertBox.innerText = 'حدث خطأ في الاتصال، يرجى المحاولة لاحقاً.';
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
            });
        });
    }
});
</script>
@endsection
