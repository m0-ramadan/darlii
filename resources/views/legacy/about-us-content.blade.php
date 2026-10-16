<div class="ck-content single-detail about-page-wrapper">
    <!-- Section 1: Hero & Intro -->
    <section class="flat-section flat-banner-about">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-md-5">
                    <h2 class="section-title">
                        <span class="title-sub">مرحبا بكم في</span>
                        <span class="title-main">دارلي</span>
                    </h2>
                </div>
                <div class="col-md-7">
                    <p class="desc-text">
                        مرحبا بكم في دارلي، حيث نحول المنازل إلى بيوت والأحلام إلى واقع. في دارلي، نحن نفهم أن المنزل هو
                        أكثر من مجرد مساحة مادية؛ إنه المكان الذي يتم فيه إنشاء الذكريات، وتنمو العائلات، وتتكشف الحياة.
                    </p>
                </div>
            </div>
            <div class="about-video-card">
                <img src="/themes/homzen/images/darli-placeholder-ar.png" alt="دارلي" class="about-video-card-img">
                <a href="https://youtu.be/tRxGSHL8bI0" data-fancybox="gallery2" class="btn-play-badge"
                    aria-label="تشغيل الفيديو">
                    <i class="icon icon-play"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Section 2: Why Choose Us -->
    <section class="flat-section flat-service-about">
        <div class="container">
            <div class="row align-items-center">
                <!-- Right column in RTL: Features list -->
                <div class="col-lg-7 order-lg-1">
                    <div class="box-title">
                        <div class="text-subtitle">لماذا تختارنا</div>
                        <h2 class="section-title">اكتشف ما يميز خبرتنا العقارية</h2>
                        <p class="main-desc">
                            في Darli، يكمن التزامنا الثابت في صياغة رحلات عقارية لا مثيل لها. إن المتخصصين المتمرسين
                            لدينا، والمسلحين بمعرفة واسعة بالسوق، يسيرون بجانبك خلال كل مرحلة من مراحل مشروعك العقاري.
                            نحن نعطي الأولوية لفهم تطلعاتك الفريدة، وننصمم خبراتنا لتتناسب مع رؤيتك.
                        </p>
                    </div>
                    <div class="services-list">
                        <!-- Feature 1 -->
                        <div class="service-feature-row">
                            <div class="service-feature-icon">
                                <img src="/storage/pages/service-1.png" alt="شراء منزل جديد">
                            </div>
                            <div class="service-feature-content">
                                <h3>شراء منزل جديد</h3>
                                <p>اكتشف منزل أحلامك دون عناء. استكشف العقارات المتنوعة وإرشادات الخبراء للحصول على
                                    تجربة شراء سلسة.</p>
                                <a href="/properties?type=sale" class="btn-learn-more">يتعلم أكثر <i
                                        class="icon icon-arrow-right2"></i></a>
                            </div>
                        </div>
                        <!-- Feature 2 -->
                        <div class="service-feature-row">
                            <div class="service-feature-icon">
                                <img src="/storage/pages/service-2.png" alt="استئجار منزل">
                            </div>
                            <div class="service-feature-content">
                                <h3>استئجار منزل</h3>
                                <p>اكتشف الإيجار المثالي الخاص بك دون عناء. اكتشف مجموعة متنوعة من القوائم المصممة
                                    خصيصًا لتناسب احتياجات نمط حياتك الفريدة.</p>
                                <a href="/properties?type=rent" class="btn-learn-more">يتعلم أكثر <i
                                        class="icon icon-arrow-right2"></i></a>
                            </div>
                        </div>
                        <!-- Feature 3 -->
                        <div class="service-feature-row">
                            <div class="service-feature-icon">
                                <img src="/storage/pages/service-3.png" alt="بيع منزل">
                            </div>
                            <div class="service-feature-content">
                                <h3>بيع منزل</h3>
                                <p>قم بالبيع بثقة بفضل توجيهات الخبراء والاستراتيجيات الفعالة، مع عرض أفضل ميزات
                                    الممتلكات الخاصة بك لتحقيق عملية بيع ناجحة.</p>
                                <a href="/dashboard/properties/create" class="btn-learn-more">يتعلم أكثر <i
                                        class="icon icon-arrow-right2"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Left column in RTL: Arched illustration -->
                <div class="col-lg-5 order-lg-2">
                    <div class="about-arch-box">
                        <img src="/themes/homzen/images/darli-placeholder-ar.png" alt="دارلي"
                            class="about-arch-box-img">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: Testimonials -->
    <section class="flat-section flat-testimonials-about">
        <div class="container">
            <div class="box-title text-center">
                <div class="text-subtitle">آراء الجماهير</div>
                <h2 class="section-title">ما يقوله الناس</h2>
            </div>

            <div id="darli-about-testimonials-container">
                <!-- Slide 0 (Items 1 & 2) -->
                <div class="about-testi-slide" data-slide="0">
                    <div class="row g-4 w-100">
                        @if (isset($testimonials[0]))
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote">{{ $testimonials[0]['content'] }}</p>
                                    <div class="about-testi-user">
                                        <img src="{{ $testimonials[0]['image'] ?: '/storage/avatars/1.jpg' }}"
                                            alt="{{ $testimonials[0]['name'] }}">
                                        <div>
                                            <div class="user-name">{{ $testimonials[0]['name'] }}</div>
                                            <div class="user-role">
                                                {{ $testimonials[0]['position'] ?: 'باحث سعيد عن منزل' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (isset($testimonials[1]))
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote">{{ $testimonials[1]['content'] }}</p>
                                    <div class="about-testi-user">
                                        <img src="{{ $testimonials[1]['image'] ?: '/storage/avatars/10.jpg' }}"
                                            alt="{{ $testimonials[1]['name'] }}">
                                        <div>
                                            <div class="user-name">{{ $testimonials[1]['name'] }}</div>
                                            <div class="user-role">{{ $testimonials[1]['position'] ?: 'مستثمر عقاري' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Slide 1 (Items 3 & 4) - ACTIVE by default to match screenshot -->
                <div class="about-testi-slide active" data-slide="1">
                    <div class="row g-4 w-100">
                        @if (isset($testimonials[2]))
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote">{{ $testimonials[2]['content'] }}</p>
                                    <div class="about-testi-user">
                                        <img src="{{ $testimonials[2]['image'] ?: '/storage/avatars/9.jpg' }}"
                                            alt="{{ $testimonials[2]['name'] }}">
                                        <div>
                                            <div class="user-name">{{ $testimonials[2]['name'] }}</div>
                                            <div class="user-role">
                                                {{ $testimonials[2]['position'] ?: 'بائع منزل سعيد' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (isset($testimonials[3]))
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote">{{ $testimonials[3]['content'] }}</p>
                                    <div class="about-testi-user">
                                        <img src="{{ $testimonials[3]['image'] ?: '/storage/avatars/11.jpg' }}"
                                            alt="{{ $testimonials[3]['name'] }}">
                                        <div>
                                            <div class="user-name">{{ $testimonials[3]['name'] }}</div>
                                            <div class="user-role">
                                                {{ $testimonials[3]['position'] ?: 'مالك منزل جديد سعيد' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Testimonials Dots matching reference image (Active Dot 2) -->
            <div class="darli-about-dots" id="darli-about-testi-dots">
                <button type="button" class="darli-about-dot" data-index="0" aria-label="Page 1"></button>
                <button type="button" class="darli-about-dot active" data-index="1" aria-label="Page 2"></button>
                <button type="button" class="darli-about-dot" data-index="2" aria-label="Page 3"></button>
                <button type="button" class="darli-about-dot" data-index="3" aria-label="Page 4"></button>
            </div>
        </div>
    </section>

    <!-- Section 4: Contact Form Banner -->
    <section class="flat-contact-about">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <!-- Text on Right in RTL -->
                <div class="col-lg-6 order-lg-1 mb-4 mb-lg-0">
                    <div class="text-subtitle">اتصل بنا</div>
                    <h2 class="section-title">نحن دائماً حريصون على أن نسمع منك!</h2>
                    <p class="desc-text">
                        Aenean eget tortor nec elit sagittis molestie. في.. Sed ullamcorper nulla egestas Pellentesque
                        tempus Massa in.r nulla egestas at. Aenean eget tortor nec elit sagittis mole
                    </p>
                </div>
                <!-- Form on Left in RTL -->
                <div class="col-lg-5 order-lg-2">
                    <div class="about-contact-card">
                        <form id="darli-about-contact-form" action="/contact-us" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="required" for="contact-name">الاسم</label>
                                    <input type="text" id="contact-name" name="name" placeholder="اسمك"
                                        required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="required" for="contact-email">البريد الإلكتروني</label>
                                    <input type="email" id="contact-email" name="email"
                                        placeholder="البريد الإلكتروني الخاص بك" required>
                                </div>
                                <div class="col-sm-6">
                                    <label for="contact-address">العنوان</label>
                                    <input type="text" id="contact-address" name="address" placeholder="عنوانك">
                                </div>
                                <div class="col-sm-6">
                                    <label class="required" for="contact-phone">الهاتف</label>
                                    <div style="display: flex; gap: 6px; align-items: center;">
                                        <span class="phone-prefix-badge">
                                            🇪🇬 +20
                                        </span>
                                        <input type="tel" id="contact-phone" name="phone" placeholder="هاتفك"
                                            required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="contact-subject">الموضوع</label>
                                    <input type="text" id="contact-subject" name="subject" placeholder="موضوع">
                                </div>
                                <div class="col-12">
                                    <label for="contact-service">نوع العقار</label>
                                    <select id="contact-service" name="service_type">
                                        <option value="Residential">Residential (سكني)</option>
                                        <option value="Commercial">Commercial (تجاري)</option>
                                        <option value="Land">Land (أراضي)</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="required" for="contact-message">الرسالة</label>
                                    <textarea id="contact-message" name="message" rows="3" placeholder="رسالتك" required></textarea>
                                </div>
                                <div class="col-12 d-flex align-items-center">
                                    <input type="checkbox" id="contact-agree" name="agree"
                                        class="form-check-input" required checked>
                                    <label for="contact-agree" class="form-check-label mb-0 ms-2">
                                        أوافق على <a href="/{{ $locale ?? 'ar' }}/terms-and-conditions"
                                            target="_blank" onclick="event.stopPropagation();"
                                            style="color: #db1d23; text-decoration: underline; font-weight: 600;">الشروط
                                            والأحكام</a> و<a href="/{{ $locale ?? 'ar' }}/privacy-policy"
                                            target="_blank" onclick="event.stopPropagation();"
                                            style="color: #db1d23; text-decoration: underline; font-weight: 600;">سياسة
                                            الخصوصية</a> <span style="color: #ef4444;">*</span>
                                    </label>
                                </div>
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn-submit-contact" id="contact-submit-btn">إرسال
                                        رسالة</button>
                                </div>
                            </div>
                            <div id="contact-form-alert"
                                style="display: none; margin-top: 16px; padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 600;">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 5: Our Agents -->
    <section class="flat-section flat-agents-about">
        <div class="container">
            <div class="box-title text-center">
                <div class="text-subtitle">الوكلاء</div>
                <h2 class="section-title">تعرف على وكلائنا</h2>
            </div>
            <div class="row g-4" id="darli-about-agents-grid">
                @forelse ($agents as $agent)
                    <div class="col-lg-3 col-md-6">
                        <div class="about-agent-card">
                            <div class="about-agent-img-box">
                                <img src="{{ !empty($agent['image']) && $agent['image'] !== '/themes/homzen/images/darli-placeholder.png' ? $agent['image'] : '/themes/homzen/images/darli-placeholder-ar.png' }}"
                                    alt="{{ $agent['name'] }}">
                            </div>
                            <div class="about-agent-body">
                                <a href="{{ $agent['url'] }}" class="about-agent-name">
                                    {{ $agent['name'] }}
                                    <span class="about-agent-badge" title="موثق">✓</span>
                                </a>
                                <div class="about-agent-role">{{ $agent['job_title'] ?? 'وكيل عقاري معتمد' }}</div>
                                @if (!empty($agent['email']))
                                    <a href="mailto:{{ $agent['email'] }}" class="about-agent-email">
                                        <i class="icon icon-mail"></i>
                                        {{ $agent['email'] }}
                                    </a>
                                @endif
                            </div>
                            <div class="about-agent-footer">
                                <div class="about-agent-props">
                                    <i class="icon icon-home"></i>
                                    {{ $agent['properties_count'] ?? 0 }} عقار
                                </div>
                                <a href="{{ $agent['url'] }}" class="about-agent-btn-view">عرض</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted">لا يوجد وكلاء حالياً</div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Section 6: Call To Action -->
    <section class="flat-section flat-cta-about">
        <div class="container">
            <div class="row align-items-center justify-content-between">
                <div class="col-lg-7 order-lg-1">
                    <div class="text-subtitle">تصبح شركاء</div>
                    <h2 class="section-title">قم بإدراج عقاراتك على Darli، انضم إلينا الآن!</h2>
                    <a href="/dashboard/properties/create" class="btn-cta">تصبح استضافة</a>
                </div>
                <div class="col-lg-5 order-lg-2 text-center text-lg-start">
                    <img src="/storage/pages/call-to-action.png" alt="قم بإدراج عقاراتك على Darli، انضم إلينا الآن!">
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Interactive Scripts for About Us -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Contact Form AJAX Submission with Lead Creation
        var form = document.getElementById('darli-about-contact-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var submitBtn = document.getElementById('contact-submit-btn');
                var alertBox = document.getElementById('contact-form-alert');

                var originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'جاري الإرسال...';
                alertBox.style.display = 'none';

                var formData = new FormData(form);
                var token = document.querySelector('meta[name="csrf-token"]') ?
                    document.querySelector('meta[name="csrf-token"]').getAttribute('content') :
                    (formData.get('_token') || '');

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return {
                                status: res.status,
                                ok: res.ok,
                                data: data
                            };
                        });
                    })
                    .then(function(result) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        alertBox.style.display = 'block';

                        if (result.ok && result.data && result.data.success) {
                            alertBox.style.backgroundColor = '#ecfdf5';
                            alertBox.style.color = '#065f46';
                            alertBox.style.border = '1px solid #a7f3d0';
                            alertBox.textContent = result.data.message ||
                                'تم إرسال رسالتك بنجاح! سيتواصل معك فريق دارلي في أقرب وقت.';
                            form.reset();
                        } else {
                            alertBox.style.backgroundColor = '#fef2f2';
                            alertBox.style.color = '#991b1b';
                            alertBox.style.border = '1px solid #fecaca';
                            var errorMsg = (result.data && result.data.message) ? result.data
                                .message :
                                'حدث خطأ أثناء إرسال الرسالة، يرجى التحقق من صحة البيانات.';
                            if (result.data && result.data.errors) {
                                var firstErr = Object.values(result.data.errors)[0];
                                if (firstErr && firstErr[0]) errorMsg = firstErr[0];
                            }
                            alertBox.textContent = errorMsg;
                        }
                    })
                    .catch(function() {
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                        alertBox.style.display = 'block';
                        alertBox.style.backgroundColor = '#fef2f2';
                        alertBox.style.color = '#991b1b';
                        alertBox.style.border = '1px solid #fecaca';
                        alertBox.textContent =
                            'تعذر الاتصال بالخادم، يرجى المحاولة مرة أخرى لاحقاً.';
                    });
            });
        }

        // 2. Testimonials Slider Dots & Drag
        var dots = document.querySelectorAll('.darli-about-dot');
        var container = document.getElementById('darli-about-testimonials-container');
        if (dots.length && container) {
            var scrollRaf = null;

            function updateAboutDots() {
                if (scrollRaf) cancelAnimationFrame(scrollRaf);
                scrollRaf = requestAnimationFrame(function() {
                    var maxScroll = container.scrollWidth - container.clientWidth;
                    if (maxScroll <= 0) return;
                    var curScroll = Math.abs(container.scrollLeft);
                    var ratio = Math.min(1, Math.max(0, curScroll / maxScroll));
                    var activeIdx = Math.round(ratio * (dots.length - 1));
                    dots.forEach(function(d, i) {
                        d.classList.toggle('active', i === activeIdx);
                    });
                });
            }

            container.addEventListener('scroll', updateAboutDots);

            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function() {
                    var maxScroll = container.scrollWidth - container.clientWidth;
                    var targetScroll = (index / (dots.length - 1)) * maxScroll;
                    var finalLeft = document.dir === 'rtl' ? -targetScroll : targetScroll;
                    container.scrollTo({
                        left: finalLeft,
                        behavior: 'smooth'
                    });
                });
            });

            // Initial position at slide index 1 (active dot 2) to match reference
            requestAnimationFrame(function() {
                var maxScroll = container.scrollWidth - container.clientWidth;
                if (maxScroll > 0) {
                    var initialScroll = (1 / (dots.length - 1)) * maxScroll;
                    var finalLeft = document.dir === 'rtl' ? -initialScroll : initialScroll;
                    container.scrollTo({
                        left: finalLeft,
                        behavior: 'auto'
                    });
                }
            });
        }
    });
</script>
