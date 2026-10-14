<!-- Services Hero Section -->
<section class="services-hero-section">
    <img src="/themes/homzen/images/faq-hero-right.png" class="services-hero-decor services-hero-decor--left"
        alt="" aria-hidden="true">
    <img src="/themes/homzen/images/faq-hero-left.png" class="services-hero-decor services-hero-decor--right"
        alt="" aria-hidden="true">
    <div class="container position-relative" style="z-index: 2;">
        <ul class="breadcrumb services-breadcrumb justify-content-center">
            <li><a href="/<?php echo e($locale); ?>"><?php echo e($locale === 'en' ? 'Home' : 'الرئيسية'); ?></a></li>
            <span class="services-breadcrumb-sep">/</span>
            <li class="active"><?php echo e($heroTitle); ?></li>
        </ul>
        <h1 class="services-hero-title"><?php echo e($heroTitle); ?></h1>
        <p class="services-hero-subtitle"><?php echo nl2br(e(str_replace('\n', "\n", $heroSubtitle))); ?></p>
        <div class="services-hero-gold-bar"></div>
    </div>
</section>

<div class="ck-content single-detail services-page-wrapper">
    <!-- Section 1: Services / Why Choose Darli -->
    <section class="flat-section flat-services-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Right Services List Column: In RTL, appears on the RIGHT (first in DOM) -->
                <div class="col-lg-6 col-12 order-lg-1 order-1 mb-4 mb-lg-0">
                    <div class="services-content-wrap">
                        <!-- Header group with gold tag -->
                        <div class="services-header-group">
                            <div class="services-gold-tag">
                                <span class="services-tag-line"></span>
                                <span><?php echo e($whyTag); ?></span>
                            </div>
                            <h2 class="services-why-heading"><?php echo e($whyTitle); ?></h2>
                            <p class="services-why-desc"><?php echo nl2br(e(str_replace('\n', "\n", $whyDesc))); ?></p>
                        </div>

                        <!-- 3 Service Action Cards -->
                        <div class="services-cards-stack">
                            <!-- Card 1: Buy a Home -->
                            <div class="services-action-card">
                                <div class="services-card-icon-box">
                                    <svg class="services-card-svg" viewBox="0 0 64 64" width="46" height="46"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M32 10L14 26V52H50V26L32 10Z" stroke="#c39953" stroke-width="3"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M26 38C26 35 29 33 32 33C35 33 38 35 38 38V52H26V38Z" stroke="#c39953"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8 44C12 40 18 39 24 42L34 47C38 49 44 48 48 44L56 36" stroke="#c39953"
                                            stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div class="services-card-content">
                                    <h3 class="services-card-title"><?php echo e($s1Title); ?></h3>
                                    <p class="services-card-text"><?php echo e($s1Desc); ?></p>
                                </div>
                                <div class="services-card-action">
                                    <a href="/<?php echo e($locale); ?>/properties?type=sale" class="services-card-btn">
                                        <span class="services-btn-text"><?php echo e($s1Btn); ?></span>
                                        <span class="services-btn-circle">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M19 12H5M12 19l-7-7 7-7" />
                                            </svg>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- Card 2: Rent a Home -->
                            <div class="services-action-card">
                                <div class="services-card-icon-box">
                                    <svg class="services-card-svg" viewBox="0 0 64 64" width="46" height="46"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M32 10L14 26V52H50V26L32 10Z" stroke="#c39953" stroke-width="3"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="35" cy="35" r="9" stroke="#c39953" stroke-width="3" />
                                        <path d="M42 42L52 52" stroke="#c39953" stroke-width="3"
                                            stroke-linecap="round" />
                                    </svg>
                                </div>
                                <div class="services-card-content">
                                    <h3 class="services-card-title"><?php echo e($s2Title); ?></h3>
                                    <p class="services-card-text"><?php echo e($s2Desc); ?></p>
                                </div>
                                <div class="services-card-action">
                                    <a href="/<?php echo e($locale); ?>/properties?type=rent" class="services-card-btn">
                                        <span class="services-btn-text"><?php echo e($s2Btn); ?></span>
                                        <span class="services-btn-circle">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M19 12H5M12 19l-7-7 7-7" />
                                            </svg>
                                        </span>
                                    </a>
                                </div>
                            </div>

                            <!-- Card 3: Sell a Home -->
                            <div class="services-action-card">
                                <div class="services-card-icon-box">
                                    <svg class="services-card-svg" viewBox="0 0 64 64" width="46" height="46"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M32 10L14 26V52H50V26L32 10Z" stroke="#c39953" stroke-width="3"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                        <rect x="20" y="32" width="24" height="14" rx="2"
                                            stroke="#c39953" stroke-width="2.5" />
                                        <text x="32" y="42" font-size="8.5" font-weight="bold" fill="#c39953"
                                            text-anchor="middle" font-family="sans-serif">SALE</text>
                                    </svg>
                                </div>
                                <div class="services-card-content">
                                    <h3 class="services-card-title"><?php echo e($s3Title); ?></h3>
                                    <p class="services-card-text"><?php echo e($s3Desc); ?></p>
                                </div>
                                <div class="services-card-action">
                                    <a href="<?php echo e(auth()->check() ? '/' . $locale . '/dashboard/properties/create' : '#modalLogin'); ?>"
                                        <?php if(auth()->guard()->guest()): ?> data-bs-toggle="modal" data-bs-target="#modalLogin" <?php endif; ?>
                                        class="services-card-btn">
                                        <span class="services-btn-text"><?php echo e($s3Btn); ?></span>
                                        <span class="services-btn-circle">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M19 12H5M12 19l-7-7 7-7" />
                                            </svg>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Left Showcase Column: In RTL, appears on the LEFT (second in DOM) -->
                <div class="col-lg-6 col-12 order-lg-2 order-2">
                    <div class="services-showcase-container">
                        <img src="/themes/homzen/images/services-villa-showcase.png" alt="<?php echo e($whyTitle); ?>"
                            class="services-showcase-img" draggable="false">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Testimonials (ما يقوله الناس) -->
    <section class="flat-section flat-testimonials-section">
        <div class="container">
            <div class="testimonials-header text-center">
                <div class="testimonials-gold-tag"><?php echo e($testiTag); ?></div>
                <h2 class="testimonials-main-heading"><?php echo e($testiTitle); ?></h2>
                <p class="testimonials-subheading"><?php echo nl2br(e(str_replace('\n', "\n", $testiSubtitle))); ?></p>
            </div>

            <!-- Testimonials Grid with Drag-to-Scroll -->
            <div class="darli-testimonials-slider" id="darli-services-testimonials-slider">
                <div class="darli-services-testi-track row g-4 justify-content-center"
                    id="darli-services-testi-track">
                    <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-lg-4 col-md-6 col-12 darli-testi-col" data-slide="<?php echo e($idx); ?>">
                            <div class="darli-testi-card">
                                <div class="darli-testi-card-top">
                                    <div class="darli-testi-stars">
                                        <?php for($s = 0; $s < ($t['rating'] ?? 5); $s++): ?>
                                            <span class="star-icon">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="darli-testi-quote-mark">“</span>
                                </div>
                                <p class="darli-testi-content"><?php echo e($t['content']); ?></p>
                                <div class="darli-testi-author-row">
                                    <div class="darli-testi-avatar">
                                        <img src="<?php echo e($t['image']); ?>" alt="<?php echo e($t['name']); ?>" draggable="false">
                                    </div>
                                    <div class="darli-testi-info">
                                        <h4 class="darli-testi-name"><?php echo e($t['name']); ?></h4>
                                        <span class="darli-testi-role"><?php echo e($t['position']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- 3 Pagination Dots (Center active with gold ring) -->
                <div class="darli-testi-pagination" role="tablist">
                    <button type="button" class="darli-testi-dot" data-index="0" aria-label="Slide 1"
                        role="tab">
                        <span class="dot-inner"></span>
                    </button>
                    <button type="button" class="darli-testi-dot is-active" data-index="1" aria-label="Slide 2"
                        role="tab">
                        <span class="dot-inner"></span>
                    </button>
                    <button type="button" class="darli-testi-dot" data-index="2" aria-label="Slide 3"
                        role="tab">
                        <span class="dot-inner"></span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3: Call to Action Banner (كن جزءاً من مجتمع دارلي) -->
    <section class="flat-section pt-0 flat-cta-section">
        <div class="container">
            <div class="darli-services-cta-banner">
                <!-- Content Side: In RTL, appears on the RIGHT (first in DOM) -->
                <div class="darli-cta-content-side">
                    <div class="darli-cta-tag"><?php echo e($ctaTag); ?></div>
                    <h2 class="darli-cta-title"><?php echo e($ctaTitle); ?></h2>
                    <p class="darli-cta-subtitle"><?php echo nl2br(e(str_replace('\n', "\n", $ctaSubtitle))); ?></p>

                    <!-- Bottom Bar with action button and 3 features -->
                    <div class="darli-cta-bottom-bar" dir="rtl">
                        <a href="<?php echo e(auth()->check() ? '/' . $locale . '/dashboard/properties/create' : '#modalLogin'); ?>"
                            <?php if(auth()->guard()->guest()): ?> data-bs-toggle="modal" data-bs-target="#modalLogin" <?php endif; ?> class="darli-cta-btn">
                            <span><?php echo e($ctaBtnText); ?></span>
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none"
                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                        </a>

                        <div class="darli-cta-features-list">
                            <!-- Feature 1: Broad reach -->
                            <div class="darli-cta-feature-item">
                                <div class="darli-cta-feature-icon">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none"
                                        stroke="#c39953" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                </div>
                                <div class="darli-cta-feature-text"><?php echo nl2br(e(str_replace('\n', "\n", $ctaF1))); ?></div>
                            </div>

                            <!-- Feature 2: Simple steps -->
                            <div class="darli-cta-feature-item">
                                <div class="darli-cta-feature-icon">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none"
                                        stroke="#c39953" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                </div>
                                <div class="darli-cta-feature-text"><?php echo nl2br(e(str_replace('\n', "\n", $ctaF2))); ?></div>
                            </div>

                            <!-- Feature 3: Dedicated support -->
                            <div class="darli-cta-feature-item">
                                <div class="darli-cta-feature-icon">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none"
                                        stroke="#c39953" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M3 18v-6a9 9 0 0 1 18 0v6" />
                                        <path
                                            d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z" />
                                    </svg>
                                </div>
                                <div class="darli-cta-feature-text"><?php echo nl2br(e(str_replace('\n', "\n", $ctaF3))); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Side: In RTL, appears on the LEFT (second in DOM) -->
                <div class="darli-cta-image-side">
                    <img src="/themes/homzen/images/services-cta-house-clean.png" alt="<?php echo e($ctaTitle); ?>"
                        class="darli-cta-house-img" draggable="false">
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Interactive Carousel Script with Mouse Drag-to-Scroll -->
<script id="darli-services-script">
    (function() {
        function initServicesSlider() {
            var slider = document.getElementById('darli-services-testimonials-slider');
            var track = document.getElementById('darli-services-testi-track');
            if (!slider || !track) return;

            var dots = slider.querySelectorAll('.darli-testi-dot');
            if (!dots.length) return;

            var isDown = false;
            var startX = 0;
            var startScrollLeft = 0;
            var clickPrevented = false;

            track.style.cursor = 'grab';

            track.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return;
                isDown = true;
                clickPrevented = false;
                track.classList.add('is-dragging');
                track.style.cursor = 'grabbing';
                track.style.scrollBehavior = 'auto';
                startX = e.pageX;
                startScrollLeft = track.scrollLeft;
            });

            window.addEventListener('mousemove', function(e) {
                if (!isDown) return;
                var dx = e.pageX - startX;
                if (Math.abs(dx) > 4) {
                    clickPrevented = true;
                }
                track.scrollLeft = startScrollLeft - dx;
            });

            function finishDrag() {
                if (!isDown) return;
                isDown = false;
                track.classList.remove('is-dragging');
                track.style.cursor = 'grab';
                track.style.scrollBehavior = 'smooth';
                setTimeout(function() {
                    clickPrevented = false;
                }, 50);
            }

            window.addEventListener('mouseup', finishDrag);
            track.addEventListener('mouseleave', finishDrag);

            track.addEventListener('click', function(e) {
                if (clickPrevented) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            }, true);

            // Click on dots
            dots.forEach(function(dot, index) {
                dot.addEventListener('click', function(e) {
                    e.preventDefault();
                    dots.forEach(function(d, i) {
                        d.classList.toggle('is-active', i === index);
                    });
                    var maxScroll = track.scrollWidth - track.clientWidth;
                    if (maxScroll > 0) {
                        var targetScroll = (index / (dots.length - 1)) * maxScroll;
                        var finalLeft = document.dir === 'rtl' ? -targetScroll : targetScroll;
                        track.scrollTo({
                            left: finalLeft,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initServicesSlider);
        } else {
            initServicesSlider();
        }
    })();
</script>
<?php /**PATH /var/www/html/resources/views/legacy/our-services-content.blade.php ENDPATH**/ ?>