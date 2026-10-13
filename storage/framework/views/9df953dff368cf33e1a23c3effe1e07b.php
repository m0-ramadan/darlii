<div class="ck-content single-detail about-page-wrapper">
    <!-- Section 1: Hero & Intro -->
    <section class="flat-section flat-banner-about">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-md-5">
                    <h2 class="section-title">
                        <span class="title-sub">مرحبا بكم في</span>
                        <span class="title-main">حمصين</span>
                    </h2>
                </div>
                <div class="col-md-7">
                    <p class="desc-text">
                        مرحبا بكم في هومزين، حيث نحول المنازل إلى بيوت والأحلام إلى واقع. في Darli، نحن نفهم أن المنزل هو أكثر من مجرد مساحة مادية؛ إنه المكان الذي يتم فيه إنشاء الذكريات، وتنمو العائلات، وتتكشف الحياة.
                    </p>
                </div>
            </div>
            <div class="about-video-card">
                <img src="/themes/homzen/images/darli-placeholder-ar.png" alt="دارلي" class="about-video-card-img">
                <a href="https://youtu.be/tRxGSHL8bI0" data-fancybox="gallery2" class="btn-play-badge" aria-label="تشغيل الفيديو">
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
                            في Darli، يكمن التزامنا الثابت في صياغة رحلات عقارية لا مثيل لها. إن المتخصصين المتمرسين لدينا، والمسلحين بمعرفة واسعة بالسوق، يسيرون بجانبك خلال كل مرحلة من مراحل مشروعك العقاري. نحن نعطي الأولوية لفهم تطلعاتك الفريدة، وننصمم خبراتنا لتتناسب مع رؤيتك.
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
                                <p>اكتشف منزل أحلامك دون عناء. استكشف العقارات المتنوعة وإرشادات الخبراء للحصول على تجربة شراء سلسة.</p>
                                <a href="/ar/properties?type=sale" class="btn-learn-more">يتعلم أكثر <i class="icon icon-arrow-right2"></i></a>
                            </div>
                        </div>
                        <!-- Feature 2 -->
                        <div class="service-feature-row">
                            <div class="service-feature-icon">
                                <img src="/storage/pages/service-2.png" alt="استئجار منزل">
                            </div>
                            <div class="service-feature-content">
                                <h3>استئجار منزل</h3>
                                <p>اكتشف الإيجار المثالي الخاص بك دون عناء. اكتشف مجموعة متنوعة من القوائم المصممة خصيصًا لتناسب احتياجات نمط حياتك الفريدة.</p>
                                <a href="/ar/properties?type=rent" class="btn-learn-more">يتعلم أكثر <i class="icon icon-arrow-right2"></i></a>
                            </div>
                        </div>
                        <!-- Feature 3 -->
                        <div class="service-feature-row">
                            <div class="service-feature-icon">
                                <img src="/storage/pages/service-3.png" alt="بيع منزل">
                            </div>
                            <div class="service-feature-content">
                                <h3>بيع منزل</h3>
                                <p>قم بالبيع بثقة بفضل توجيهات الخبراء والاستراتيجيات الفعالة، مع عرض أفضل ميزات الممتلكات الخاصة بك لتحقيق عملية بيع ناجحة.</p>
                                <a href="/ar/dashboard/properties/create" class="btn-learn-more">يتعلم أكثر <i class="icon icon-arrow-right2"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Left column in RTL: Arched illustration -->
                <div class="col-lg-5 order-lg-2">
                    <div class="about-arch-box">
                        <img src="/themes/homzen/images/darli-placeholder-ar.png" alt="دارلي" class="about-arch-box-img">
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
                <div class="about-testi-slide" data-slide="0" style="display: none;">
                    <div class="row g-4 w-100">
                        <?php if(isset($testimonials[0])): ?>
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote"><?php echo e($testimonials[0]['content']); ?></p>
                                    <div class="about-testi-user">
                                        <img src="<?php echo e($testimonials[0]['image'] ?: '/storage/avatars/1.jpg'); ?>" alt="<?php echo e($testimonials[0]['name']); ?>">
                                        <div>
                                            <div class="user-name"><?php echo e($testimonials[0]['name']); ?></div>
                                            <div class="user-role"><?php echo e($testimonials[0]['position'] ?: 'باحث سعيد عن منزل'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($testimonials[1])): ?>
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote"><?php echo e($testimonials[1]['content']); ?></p>
                                    <div class="about-testi-user">
                                        <img src="<?php echo e($testimonials[1]['image'] ?: '/storage/avatars/10.jpg'); ?>" alt="<?php echo e($testimonials[1]['name']); ?>">
                                        <div>
                                            <div class="user-name"><?php echo e($testimonials[1]['name']); ?></div>
                                            <div class="user-role"><?php echo e($testimonials[1]['position'] ?: 'مستثمر عقاري'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Slide 1 (Items 3 & 4) - ACTIVE by default to match screenshot -->
                <div class="about-testi-slide active" data-slide="1">
                    <div class="row g-4 w-100">
                        <?php if(isset($testimonials[2])): ?>
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote"><?php echo e($testimonials[2]['content']); ?></p>
                                    <div class="about-testi-user">
                                        <img src="<?php echo e($testimonials[2]['image'] ?: '/storage/avatars/9.jpg'); ?>" alt="<?php echo e($testimonials[2]['name']); ?>">
                                        <div>
                                            <div class="user-name"><?php echo e($testimonials[2]['name']); ?></div>
                                            <div class="user-role"><?php echo e($testimonials[2]['position'] ?: 'بائع منزل سعيد'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if(isset($testimonials[3])): ?>
                            <div class="col-md-6">
                                <div class="about-testi-card">
                                    <div class="about-testi-stars">★★★★★</div>
                                    <p class="about-testi-quote"><?php echo e($testimonials[3]['content']); ?></p>
                                    <div class="about-testi-user">
                                        <img src="<?php echo e($testimonials[3]['image'] ?: '/storage/avatars/11.jpg'); ?>" alt="<?php echo e($testimonials[3]['name']); ?>">
                                        <div>
                                            <div class="user-name"><?php echo e($testimonials[3]['name']); ?></div>
                                            <div class="user-role"><?php echo e($testimonials[3]['position'] ?: 'مالك منزل جديد سعيد'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
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
                        Aenean eget tortor nec elit sagittis molestie. في.. Sed ullamcorper nulla egestas Pellentesque tempus Massa in.r nulla egestas at. Aenean eget tortor nec elit sagittis mole
                    </p>
                </div>
                <!-- Form on Left in RTL -->
                <div class="col-lg-5 order-lg-2">
                    <div class="about-contact-card">
                        <form id="darli-about-contact-form" action="/ar/contact-us" method="POST">
                            <input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="required" for="contact-name">الاسم</label>
                                    <input type="text" id="contact-name" name="name" placeholder="اسمك" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="required" for="contact-email">البريد الإلكتروني</label>
                                    <input type="email" id="contact-email" name="email" placeholder="البريد الإلكتروني الخاص بك" required>
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
                                        <input type="tel" id="contact-phone" name="phone" placeholder="هاتفك" required>
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
                                    <input type="checkbox" id="contact-agree" name="agree" class="form-check-input" required checked>
                                    <label for="contact-agree" class="form-check-label mb-0 ms-2">
                                        أوافق على الشروط وسياسة الخصوصية <span style="color: #ef4444;">*</span>
                                    </label>
                                </div>
                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn-submit-contact" id="contact-submit-btn">إرسال رسالة</button>
                                </div>
                            </div>
                            <div id="contact-form-alert" style="display: none; margin-top: 16px; padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 600;"></div>
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
                <?php $__empty_1 = true; $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="about-agent-card">
                            <div class="about-agent-img-box">
                                <img src="<?php echo e((!empty($agent['image']) && $agent['image'] !== '/themes/homzen/images/darli-placeholder.png') ? $agent['image'] : '/themes/homzen/images/darli-placeholder-ar.png'); ?>" alt="<?php echo e($agent['name']); ?>">
                            </div>
                            <div class="about-agent-body">
                                <a href="<?php echo e($agent['url']); ?>" class="about-agent-name">
                                    <?php echo e($agent['name']); ?>

                                    <span class="about-agent-badge" title="موثق">✓</span>
                                </a>
                                <div class="about-agent-role"><?php echo e($agent['job_title'] ?? 'وكيل عقاري معتمد'); ?></div>
                                <?php if(!empty($agent['email'])): ?>
                                    <a href="mailto:<?php echo e($agent['email']); ?>" class="about-agent-email">
                                        <i class="icon icon-mail"></i>
                                        <?php echo e($agent['email']); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="about-agent-footer">
                                <div class="about-agent-props">
                                    <i class="icon icon-home"></i>
                                    <?php echo e($agent['properties_count'] ?? 0); ?> عقار
                                </div>
                                <a href="<?php echo e($agent['url']); ?>" class="about-agent-btn-view">عرض</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-12 text-center text-muted">لا يوجد وكلاء حالياً</div>
                <?php endif; ?>
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
                    <a href="/ar/dashboard/properties/create" class="btn-cta">تصبح استضافة</a>
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
            var token = document.querySelector('meta[name="csrf-token"]') 
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                : (formData.get('_token') || '');
                
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
                    return { status: res.status, ok: res.ok, data: data };
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
                    alertBox.textContent = result.data.message || 'تم إرسال رسالتك بنجاح! سيتواصل معك فريق دارلي في أقرب وقت.';
                    form.reset();
                } else {
                    alertBox.style.backgroundColor = '#fef2f2';
                    alertBox.style.color = '#991b1b';
                    alertBox.style.border = '1px solid #fecaca';
                    var errorMsg = (result.data && result.data.message) ? result.data.message : 'حدث خطأ أثناء إرسال الرسالة، يرجى التحقق من صحة البيانات.';
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
                alertBox.textContent = 'تعذر الاتصال بالخادم، يرجى المحاولة مرة أخرى لاحقاً.';
            });
        });
    }

    // 2. Testimonials Slider Dots
    var dots = document.querySelectorAll('.darli-about-dot');
    var slides = document.querySelectorAll('.about-testi-slide');
    if (dots.length && slides.length) {
        dots.forEach(function(dot) {
            dot.addEventListener('click', function() {
                var idx = parseInt(this.getAttribute('data-index'), 10) || 0;
                dots.forEach(function(d) { d.classList.remove('active'); });
                this.classList.add('active');
                
                var targetSlide = idx % slides.length;
                slides.forEach(function(s, i) {
                    s.style.display = (i === targetSlide) ? 'flex' : 'none';
                    if (i === targetSlide) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }
});
</script>
<?php /**PATH /var/www/html/resources/views/legacy/about-us-content.blade.php ENDPATH**/ ?>