<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $title = $page?->translation($locale)?->title ?: ($isAr ? 'الأسئلة الشائعة والأجوبة الوافية' : 'Frequently Asked Questions');
?>

<?php $__env->startSection('content'); ?>
<style>
    /* ==========================================================================
       Darli FAQ Page (صفحة الأسئلة الشائعة)
       ========================================================================== */

    :root {
        --darli-faq-bg: #f8fafc;
        --darli-faq-card: #ffffff;
        --darli-faq-border: #e2e8f0;
        --darli-faq-text: #0f172a;
        --darli-faq-muted: #64748b;
        --darli-faq-primary: #db1d23;
        --darli-faq-primary-soft: rgba(219, 29, 35, 0.08);
        --darli-faq-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
    }

    html[data-theme="dark"] {
        --darli-faq-bg: #0b1320;
        --darli-faq-card: #111e33;
        --darli-faq-border: #1e293b;
        --darli-faq-text: #f8fafc;
        --darli-faq-muted: #94a3b8;
        --darli-faq-primary-soft: rgba(219, 29, 35, 0.18);
        --darli-faq-shadow: 0 4px 16px -2px rgba(0, 0, 0, 0.4);
    }

    /* Hero */
    .darli-faq-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        padding: 55px 0 75px 0;
        overflow: hidden;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-theme="dark"] .darli-faq-hero {
        background: linear-gradient(180deg, #0c1726 0%, #111e33 100%);
        border-bottom-color: #1e293b;
    }

    .darli-faq-hero__content {
        position: relative;
        z-index: 2;
        max-width: 780px;
        margin: 0 auto;
        text-align: center;
    }

    .darli-faq-hero__tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--darli-faq-primary-soft);
        color: var(--darli-faq-primary);
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 16px;
        border: 1px solid rgba(219, 29, 35, 0.15);
    }

    .darli-faq-hero__title {
        font-size: 38px;
        font-weight: 800;
        color: var(--darli-faq-text);
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    @media (max-width: 768px) {
        .darli-faq-hero__title { font-size: 28px; }
    }

    .darli-faq-hero__desc {
        font-size: 16px;
        line-height: 1.7;
        color: var(--darli-faq-muted);
        margin-bottom: 24px;
    }

    /* Live Search Box */
    .darli-faq-search-box {
        max-width: 560px;
        margin: 0 auto;
        position: relative;
    }
    .darli-faq-search-input {
        width: 100%;
        background: var(--darli-faq-card);
        border: 1px solid var(--darli-faq-border);
        color: var(--darli-faq-text);
        padding: 14px 20px 14px 48px;
        border-radius: 14px;
        font-size: 15px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        transition: all 0.2s ease;
    }
    [dir="rtl"] .darli-faq-search-input {
        padding: 14px 48px 14px 20px;
    }
    .darli-faq-search-input:focus {
        outline: none;
        border-color: var(--darli-faq-primary);
        box-shadow: 0 0 0 3px var(--darli-faq-primary-soft);
    }
    .darli-faq-search-icon {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        right: 18px;
        color: var(--darli-faq-muted);
        pointer-events: none;
    }
    [dir="ltr"] .darli-faq-search-icon {
        right: auto;
        left: 18px;
    }

    /* Category Filter Tabs */
    .darli-faq-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        margin: 35px 0 40px 0;
    }
    .darli-faq-cat-btn {
        background: var(--darli-faq-card);
        border: 1px solid var(--darli-faq-border);
        color: var(--darli-faq-muted);
        padding: 8px 18px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .darli-faq-cat-btn:hover,
    .darli-faq-cat-btn.is-active {
        background: var(--darli-faq-primary);
        color: #ffffff;
        border-color: var(--darli-faq-primary);
    }

    /* Accordion Container */
    .darli-faq-container {
        max-width: 860px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-bottom: 60px;
    }

    .darli-faq-item {
        background: var(--darli-faq-card);
        border: 1px solid var(--darli-faq-border);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.25s ease;
        box-shadow: var(--darli-faq-shadow);
    }
    .darli-faq-item.is-open {
        border-color: rgba(219, 29, 35, 0.3);
    }

    .darli-faq-item__header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        user-select: none;
        gap: 16px;
    }

    .darli-faq-item__title {
        font-size: 16px;
        font-weight: 700;
        color: var(--darli-faq-text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
        line-height: 1.5;
    }

    .darli-faq-item__badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 6px;
        background: var(--darli-faq-primary-soft);
        color: var(--darli-faq-primary);
        white-space: nowrap;
    }

    .darli-faq-item__toggle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--darli-faq-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--darli-faq-text);
        transition: transform 0.3s ease, background 0.2s ease;
    }
    .darli-faq-item.is-open .darli-faq-item__toggle {
        transform: rotate(180deg);
        background: var(--darli-faq-primary);
        color: #ffffff;
    }

    .darli-faq-item__body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s cubic-bezier(0, 1, 0, 1), padding 0.25s ease;
        padding: 0 24px;
        color: var(--darli-faq-muted);
        font-size: 14px;
        line-height: 1.8;
    }
    .darli-faq-item.is-open .darli-faq-item__body {
        max-height: 1000px;
        padding: 0 24px 22px 24px;
        transition: max-height 0.4s ease-in-out, padding 0.25s ease;
    }

    /* Bottom Contact CTA */
    .darli-faq-cta {
        background: var(--darli-faq-card);
        border: 1px solid var(--darli-faq-border);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        max-width: 860px;
        margin: 0 auto;
        box-shadow: var(--darli-faq-shadow);
    }
</style>

<div class="darli-faq-page" style="background: var(--darli-faq-bg); min-height: 80vh; padding-bottom: 80px;">
    
    <section class="darli-faq-hero">
        <div class="container">
            <div class="darli-faq-hero__content">
                <div class="darli-faq-hero__tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    <span><?php echo e($isAr ? 'مركز المساعدة ودليل الاستفسارات' : 'Help & FAQ Center'); ?></span>
                </div>

                <h1 class="darli-faq-hero__title"><?php echo e($title); ?></h1>

                <p class="darli-faq-hero__desc">
                    <?php echo e($isAr
                        ? 'إجابات شاملة على كل ما يتعلق بشراء وتأجير العقارات، الاستثمار، التوثيق القانوني، والتعامل مع الوكلاء المعتمدين في بني سويف ومصر.'
                        : 'Comprehensive answers to all your inquiries regarding buying, renting, financing, and verified brokers across Beni Suef and Egypt.'); ?>

                </p>

                
                <div class="darli-faq-search-box">
                    <input type="text" id="faqSearchInput" class="darli-faq-search-input"
                        placeholder="<?php echo e($isAr ? 'ابحث في الأسئلة (مثال: الشراء، التوثيق، بني سويف، التقسيط)...' : 'Search questions (e.g. buying, mortgage, Beni Suef)...'); ?>">
                    <svg class="darli-faq-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        
        <div class="darli-faq-categories" id="faqCategoryTabs">
            <button class="darli-faq-cat-btn is-active" data-cat="all"><?php echo e($isAr ? 'جميع الأسئلة' : 'All Questions'); ?></button>
            <button class="darli-faq-cat-btn" data-cat="الشراء والاستثمار"><?php echo e($isAr ? 'الشراء والاستثمار' : 'Buying & Investment'); ?></button>
            <button class="darli-faq-cat-btn" data-cat="الإيجار والوحدات"><?php echo e($isAr ? 'الإيجار والوحدات' : 'Renting'); ?></button>
            <button class="darli-faq-cat-btn" data-cat="الوكلاء والشركات"><?php echo e($isAr ? 'الوكلاء المعتمدون' : 'Verified Agencies'); ?></button>
            <button class="darli-faq-cat-btn" data-cat="التمويل والدفع"><?php echo e($isAr ? 'التمويل والأقساط' : 'Financing & Payments'); ?></button>
            <button class="darli-faq-cat-btn" data-cat="الإعلان وإضافة"><?php echo e($isAr ? 'نشر العقارات' : 'Listing Properties'); ?></button>
        </div>

        
        <div class="darli-faq-container" id="faqListContainer">
            <?php $__empty_1 = true; $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $cat = $isAr ? ($item['category'] ?? 'عام') : ($item['category_en'] ?? 'General');
                    $q = $isAr ? ($item['question'] ?? '') : ($item['question_en'] ?? ($item['question'] ?? ''));
                    $a = $isAr ? ($item['answer'] ?? '') : ($item['answer_en'] ?? ($item['answer'] ?? ''));
                ?>
                <div class="darli-faq-item <?php echo e($index === 0 ? 'is-open' : ''); ?>" data-category="<?php echo e($cat); ?>">
                    <div class="darli-faq-item__header" onclick="toggleFaq(this)">
                        <div class="darli-faq-item__title">
                            <span class="darli-faq-item__badge"><?php echo e($cat); ?></span>
                            <span><?php echo e($q); ?></span>
                        </div>
                        <div class="darli-faq-item__toggle">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                        </div>
                    </div>
                    <div class="darli-faq-item__body">
                        <p class="mb-0"><?php echo e($a); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-5 text-muted">
                    <p><?php echo e($isAr ? 'لا توجد أسئلة شائعة مسجلة حالياً.' : 'No FAQs recorded at the moment.'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="darli-faq-cta">
            <h3 class="fw-bold mb-2" style="color: var(--darli-faq-text); font-size: 22px;">
                <?php echo e($isAr ? 'هل لديك استفسار آخر لم تجد إجابته هنا؟' : 'Have a question not answered here?'); ?>

            </h3>
            <p class="text-muted mb-4" style="max-width: 580px; margin: 0 auto; font-size: 15px;">
                <?php echo e($isAr
                    ? 'فريق مستشارينا في بني سويف مستعد للإجابة على جميع استفساراتك وتقديم استشارة عقارية مجانية فورية.'
                    : 'Our certified real estate advisory team in Beni Suef is ready to help you with free property consultation.'); ?>

            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="<?php echo e(route('contact-us.index', ['locale' => $locale])); ?>" class="btn btn-danger px-4 py-2" style="border-radius: 12px; background: var(--darli-faq-primary); border-color: var(--darli-faq-primary); font-weight: 700;">
                    <?php echo e($isAr ? 'تواصل معنا الآن' : 'Contact Us Now'); ?>

                </a>
                <a href="https://wa.me/201001234561" target="_blank" rel="noopener" class="btn btn-outline-success px-4 py-2" style="border-radius: 12px; font-weight: 700;">
                    <?php echo e($isAr ? 'محادثة واتساب سريعة' : 'WhatsApp Chat'); ?>

                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleFaq(header) {
        const item = header.closest('.darli-faq-item');
        item.classList.toggle('is-open');
    }

    // Live search & category tabs filtering
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('faqSearchInput');
        const items = document.querySelectorAll('.darli-faq-item');
        const catTabs = document.querySelectorAll('.darli-faq-cat-btn');
        let activeCat = 'all';

        function filterFaqs() {
            const query = (searchInput.value || '').trim().toLowerCase();

            items.forEach(item => {
                const text = item.innerText.toLowerCase();
                const itemCat = (item.getAttribute('data-category') || '').toLowerCase();

                const matchesQuery = !query || text.includes(query);
                const matchesCat = activeCat === 'all' || itemCat.includes(activeCat.toLowerCase());

                if (matchesQuery && matchesCat) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterFaqs);
        }

        catTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                catTabs.forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
                activeCat = this.getAttribute('data-cat');
                filterFaqs();
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/faqs/index.blade.php ENDPATH**/ ?>