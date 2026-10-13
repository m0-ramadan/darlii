<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $tr = $page?->translation($locale);
    $title = $tr?->title ?: ($isAr ? 'سياسة الخصوصية وحماية البيانات' : 'Privacy & Data Protection Policy');
    $content = $tr?->content ?: '';
    $updatedAt = $page?->updated_at ?: now();
?>

<?php $__env->startSection('content'); ?>
<style>
    /* ==========================================================================
       Darli Privacy Policy Page (سياسة الخصوصية)
       ========================================================================== */

    :root {
        --darli-pp-bg: #f8fafc;
        --darli-pp-card: #ffffff;
        --darli-pp-border: #e2e8f0;
        --darli-pp-text: #0f172a;
        --darli-pp-muted: #64748b;
        --darli-pp-primary: #db1d23;
        --darli-pp-primary-soft: rgba(219, 29, 35, 0.08);
        --darli-pp-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    }

    html[data-theme="dark"] {
        --darli-pp-bg: #0b1320;
        --darli-pp-card: #111e33;
        --darli-pp-border: #1e293b;
        --darli-pp-text: #f8fafc;
        --darli-pp-muted: #94a3b8;
        --darli-pp-primary-soft: rgba(219, 29, 35, 0.18);
        --darli-pp-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
    }

    /* Hero */
    .darli-pp-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        padding: 55px 0 65px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-theme="dark"] .darli-pp-hero {
        background: linear-gradient(180deg, #0c1726 0%, #111e33 100%);
        border-bottom-color: #1e293b;
    }

    .darli-pp-hero__content {
        max-width: 820px;
        margin: 0 auto;
        text-align: center;
    }

    .darli-pp-hero__tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--darli-pp-primary-soft);
        color: var(--darli-pp-primary);
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 16px;
        border: 1px solid rgba(219, 29, 35, 0.15);
    }

    .darli-pp-hero__title {
        font-size: 38px;
        font-weight: 800;
        color: var(--darli-pp-text);
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    @media (max-width: 768px) {
        .darli-pp-hero__title { font-size: 28px; }
    }

    .darli-pp-hero__meta {
        font-size: 14px;
        color: var(--darli-pp-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }

    /* Body Layout */
    .darli-pp-wrap {
        max-width: 960px;
        margin: -30px auto 60px auto;
        position: relative;
        z-index: 10;
    }

    .darli-pp-card {
        background: var(--darli-pp-card);
        border: 1px solid var(--darli-pp-border);
        border-radius: 20px;
        padding: 45px 50px;
        box-shadow: var(--darli-pp-shadow);
        color: var(--darli-pp-text);
        line-height: 1.85;
        font-size: 15px;
    }
    @media (max-width: 768px) {
        .darli-pp-card { padding: 25px 20px; }
    }

    .darli-pp-card h3 {
        font-size: 20px;
        font-weight: 800;
        color: var(--darli-pp-text);
        margin-top: 32px;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--darli-pp-border);
    }
    .darli-pp-card h3:first-child {
        margin-top: 0;
    }

    .darli-pp-card p {
        color: var(--darli-pp-muted);
        margin-bottom: 18px;
    }

    .darli-pp-card ul {
        color: var(--darli-pp-muted);
        margin-bottom: 22px;
        padding-right: 20px;
    }
    [dir="ltr"] .darli-pp-card ul {
        padding-right: 0;
        padding-left: 20px;
    }

    .darli-pp-card li {
        margin-bottom: 8px;
    }

    .darli-pp-card strong {
        color: var(--darli-pp-text);
    }

    .darli-pp-security-banner {
        background: var(--darli-pp-primary-soft);
        border: 1px solid rgba(219, 29, 35, 0.2);
        border-radius: 14px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 30px;
    }
    .darli-pp-security-banner__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--darli-pp-primary);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>

<div class="darli-pp-page" style="background: var(--darli-pp-bg); min-height: 80vh; padding-bottom: 80px;">
    
    <section class="darli-pp-hero">
        <div class="container">
            <div class="darli-pp-hero__content">
                <div class="darli-pp-hero__tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span><?php echo e($isAr ? 'الأمان والسرية التامة' : 'Security & Compliance'); ?></span>
                </div>

                <h1 class="darli-pp-hero__title"><?php echo e($title); ?></h1>

                <div class="darli-pp-hero__meta">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php echo e($isAr ? 'آخر تحديث: ' . $updatedAt->translatedFormat('j F Y') : 'Last updated: ' . $updatedAt->format('F j, Y')); ?>

                    </span>
                    <span>•</span>
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -2px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php echo e($isAr ? 'منصة دارلي المعتمدة' : 'Darli Platform Verified'); ?>

                    </span>
                </div>
            </div>
        </div>
    </section>

    
    <div class="container">
        <div class="darli-pp-wrap">
            <div class="darli-pp-card">
                <div class="darli-pp-security-banner">
                    <div class="darli-pp-security-banner__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 15px; color: var(--darli-pp-text);">
                            <?php echo e($isAr ? 'بياناتك مشفرة ومحمية بأعلى معايير الأمان الدولية' : 'Your data is encrypted and strictly protected'); ?>

                        </div>
                        <div class="text-muted small">
                            <?php echo e($isAr ? 'لا نقوم ببيع أو مشاركة بياناتك مع أي أطراف إعلانية خارجية دون موافقتك الصريحة.' : 'We never sell or share user data with third-party advertisers.'); ?>

                        </div>
                    </div>
                </div>

                <?php echo $content; ?>


                <div class="mt-5 pt-4 border-top text-center text-muted small">
                    <p class="mb-0">
                        <?php echo e($isAr
                            ? 'لأي استفسارات قانونية أو ممارسة حقوق الخصوصية، تواصل مع المستشار القانوني للمنصة عبر: privacy@darlii.eg'
                            : 'For any privacy-related inquiries, reach out to our compliance desk at: privacy@darlii.eg'); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/pages/privacy-policy.blade.php ENDPATH**/ ?>