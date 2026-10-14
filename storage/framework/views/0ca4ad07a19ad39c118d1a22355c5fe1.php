<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isRtl = in_array($locale, config('locales.rtl', ['ar']));
    $tr = $page?->translation($locale);
    $title = $tr?->title ?: ($isAr ? 'الشروط والأحكام' : 'Terms & Conditions');
    $content = $tr?->content ?: '';
    $updatedAt = $page?->updated_at ?: now();
?>

<?php $__env->startSection('content'); ?>
<style>
    /* ==========================================================================
       Darli Terms and Conditions Page (الشروط والأحكام)
       ========================================================================== */

    :root {
        --darli-tc-bg: #f8fafc;
        --darli-tc-card: #ffffff;
        --darli-tc-border: #e2e8f0;
        --darli-tc-text: #0f172a;
        --darli-tc-muted: #64748b;
        --darli-tc-primary: #db1d23;
        --darli-tc-primary-soft: rgba(219, 29, 35, 0.08);
        --darli-tc-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
    }

    html[data-theme="dark"] {
        --darli-tc-bg: #0b1320;
        --darli-tc-card: #111e33;
        --darli-tc-border: #1e293b;
        --darli-tc-text: #f8fafc;
        --darli-tc-muted: #94a3b8;
        --darli-tc-primary-soft: rgba(219, 29, 35, 0.18);
        --darli-tc-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
    }

    /* Hero */
    .darli-tc-hero {
        position: relative;
        background: linear-gradient(180deg, #edf3f8 0%, #f7f9fa 100%);
        padding: 55px 0 65px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-theme="dark"] .darli-tc-hero {
        background: linear-gradient(180deg, #0c1726 0%, #111e33 100%);
        border-bottom-color: #1e293b;
    }

    .darli-tc-hero__content {
        max-width: 820px;
        margin: 0 auto;
        text-align: center;
    }

    .darli-tc-hero__tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--darli-tc-primary-soft);
        color: var(--darli-tc-primary);
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 9999px;
        margin-bottom: 16px;
        border: 1px solid rgba(219, 29, 35, 0.15);
    }

    .darli-tc-hero__title {
        font-size: 38px;
        font-weight: 800;
        color: var(--darli-tc-text);
        margin-bottom: 14px;
        letter-spacing: -0.5px;
    }
    @media (max-width: 768px) {
        .darli-tc-hero__title { font-size: 28px; }
    }

    .darli-tc-hero__meta {
        font-size: 14px;
        color: var(--darli-tc-muted);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }

    /* Body Layout */
    .darli-tc-wrap {
        max-width: 960px;
        margin: -30px auto 60px auto;
        position: relative;
        z-index: 10;
    }

    .darli-tc-card {
        background: var(--darli-tc-card);
        border: 1px solid var(--darli-tc-border);
        border-radius: 20px;
        padding: 45px 50px;
        box-shadow: var(--darli-tc-shadow);
        color: var(--darli-tc-text);
        line-height: 1.85;
        font-size: 15px;
    }
    @media (max-width: 768px) {
        .darli-tc-card { padding: 25px 20px; }
    }

    .darli-tc-card h3 {
        font-size: 20px;
        font-weight: 800;
        color: var(--darli-tc-text);
        margin-top: 32px;
        margin-bottom: 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--darli-tc-border);
    }
    .darli-tc-card h3:first-child {
        margin-top: 0;
    }

    .darli-tc-card p {
        color: var(--darli-tc-muted);
        margin-bottom: 18px;
    }

    .darli-tc-card ul {
        color: var(--darli-tc-muted);
        margin-bottom: 22px;
        padding-right: 20px;
    }
    [dir="ltr"] .darli-tc-card ul {
        padding-right: 0;
        padding-left: 20px;
    }

    .darli-tc-card li {
        margin-bottom: 8px;
    }

    .darli-tc-card strong {
        color: var(--darli-tc-text);
    }

    .darli-tc-security-banner {
        background: var(--darli-tc-primary-soft);
        border: 1px solid rgba(219, 29, 35, 0.2);
        border-radius: 14px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 30px;
    }
    .darli-tc-security-banner__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--darli-tc-primary);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>

<div class="darli-tc-page" style="background: var(--darli-tc-bg); min-height: 80vh; padding-bottom: 80px;">
    
    <section class="darli-tc-hero">
        <div class="container">
            <div class="darli-tc-hero__content">
                <div class="darli-tc-hero__tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    <span><?php echo e($isAr ? 'وثيقة الاستخدام والضوابط القانونية' : 'Legal Terms of Service'); ?></span>
                </div>

                <h1 class="darli-tc-hero__title"><?php echo e($title); ?></h1>

                <div class="darli-tc-hero__meta">
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
        <div class="darli-tc-wrap">
            <div class="darli-tc-card">
                <div class="darli-tc-security-banner">
                    <div class="darli-tc-security-banner__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size: 15px; color: var(--darli-tc-text);">
                            <?php echo e($isAr ? 'اتفاقية استخدام شفافة تضمن حقوق كافة الأطراف' : 'Transparent service agreement protecting all parties'); ?>

                        </div>
                        <div class="text-muted small">
                            <?php echo e($isAr ? 'تنظم هذه الشروط العلاقة بين المستخدمين والوسطاء والشركات العقارية وإدارة منصة دارلي وفقاً للقانون المصري.' : 'These terms govern the relationship between users, brokers, companies, and Darli platform under Egyptian law.'); ?>

                        </div>
                    </div>
                </div>

                <?php if(!empty($content)): ?>
                    <?php echo $content; ?>

                <?php else: ?>
                    <?php if($isAr): ?>
                        <h3>1. تمهيد وقبول الاتفاقية</h3>
                        <p>مرحباً بكم في منصة <strong>دارلي العقارية (Darli Real Estate)</strong>. يُعد استخدامك لمنصتنا الإلكترونية أو تسجيل حساب جديد إقراراً صريحاً بموافقتك الكاملة وغير المشروطة على الالتزام بجميع بنود هذه الشروط والأحكام وسياسة الخصوصية المعتمدة.</p>

                        <h3>2. أهلية الاستخدام وتسجيل الحسابات</h3>
                        <ul>
                            <li>يجب أن يبلغ المستخدم السن القانونية (21 عاماً أو الأهلية القانونية الكاملة للتعاقد وفقاً لأحكام القانون المدني المصري).</li>
                            <li>يلتزم المستخدم بتقديم بيانات شخصية صحيحة ودقيقة (الاسم، رقم الهاتف الفعلي، والعنوان)، وتحديثها دورياً.</li>
                            <li>يتحمل المستخدم المسؤولية الكاملة عن سرية بيانات حسابه وكلمة المرور، وكافة الأنشطة التي تتم من خلاله.</li>
                        </ul>

                        <h3>3. حسابات الوسطاء (السماسرة) والشركات العقارية</h3>
                        <ul>
                            <li>تخضع حسابات الوسطاء المستقلين والشركات العقارية لنظام التدقيق والمراجعة الإدارية (الحساب المعلق) قبل تفعيل صلاحيات نشر العقارات والمشروعات.</li>
                            <li>يلتزم الوسيط العقاري برفع صورة بطاقة الرقم القومي سارية والبيانات المهنية المطلوبة.</li>
                            <li>تلتزم الشركات العقارية بتقديم بيانات السجل التجاري والبطاقة الضريبية والتراخيص الرسمية اللازمة لمزاولة النشاط.</li>
                            <li>تحتفظ إدارة المنصة بالحق الكامل في رفض أو تعليق أي حساب يخالف معايير المصداقية أو يقدم وثائق غير مطابقة.</li>
                        </ul>

                        <h3>4. ضوابط وسياسة نشر الإعلانات العقارية</h3>
                        <ul>
                            <li>يجب أن تكون جميع العقارات المعروضة حقيقية ومتاحة فعلياً للبيع أو الإيجار، وبأسعار ومواصفات واقعية وموثقة.</li>
                            <li>يُحظر منعاً باتاً نشر إعلانات مكررة، أو مضللة، أو استخدام صور تخضع لحقوق ملكية فكرية لأطراف أخرى دون ترخيص.</li>
                            <li>يحق لمنصة دارلي تعديل أو إيقاف أو حذف أي إعلان يثبت عدم دقته أو مخالفته للقوانين واللوائح السارية دون أي تعويض.</li>
                        </ul>

                        <h3>5. العمولات والوساطة وإخلاء المسؤولية</h3>
                        <ul>
                            <li>تعمل منصة دارلي كبيئة رقمية موثوقة لربط البائعين والمشترين والمستأجرين مع نخبة من الوكلاء والشركات المعتمدة في بني سويف وكافة المحافظات.</li>
                            <li>تخضع العمولات العقارية للاتفاقات المباشرة والعقود المبرمة بين أطراف المعاملة ووفقاً للعرف التجاري والقوانين المنظمة للوساطة العقارية في مصر.</li>
                            <li>لا تتحمل منصة دارلي أي مسؤولية مالية أو قانونية عن أي اتفاقات أو تعاملات تتم خارج نطاق الإشراف المباشر للمنصة.</li>
                        </ul>

                        <h3>6. حقوق الملكية الفكرية</h3>
                        <p>جميع حقوق الملكية الفكرية، بما في ذلك العلامات التجارية "دارلي" (Darli)، التصاميم، النصوص، البرمجيات، وقواعد البيانات، هي ملك حصري للمنصة ومحمية بموجب القانون رقم 82 لسنة 2002 بشأن حماية حقوق الملكية الفكرية في جمهورية مصر العربية.</p>

                        <h3>7. التعديلات والقانون الحاكم والاختصاص القضائي</h3>
                        <p>تحتفظ المنصة بالحق في تحديث أو تعديل هذه الشروط في أي وقت، ويسري التعديل من تاريخ نشره على هذه الصفحة. تخضع هذه الاتفاقية لأحكام القوانين المصرية، وتختص محاكم بني سويف ومحاكم القاهرة الاقتصادية بنظر أي نزاع ينشأ عن تطبيقها.</p>
                    <?php else: ?>
                        <h3>1. Agreement & Acceptance</h3>
                        <p>Welcome to <strong>Darli Real Estate Platform</strong>. By accessing our platform or creating an account, you unconditionally agree to comply with and be bound by these Terms and Conditions and our Privacy Policy.</p>

                        <h3>2. Account Registration & Eligibility</h3>
                        <ul>
                            <li>Users must possess full legal capacity under Egyptian law to enter into binding agreements.</li>
                            <li>You must provide accurate, current, and verifiable contact and identification information.</li>
                            <li>You are solely responsible for safeguarding your credentials and all activity under your account.</li>
                        </ul>

                        <h3>3. Broker & Agency Guidelines</h3>
                        <ul>
                            <li>Broker and company accounts are subject to administrative vetting and approval before publishing listings.</li>
                            <li>Brokers must provide valid national identification; companies must furnish official trade licenses and tax cards.</li>
                            <li>Darli reserves the right to suspend or terminate accounts that breach integrity guidelines.</li>
                        </ul>

                        <h3>4. Property Listing Standards</h3>
                        <ul>
                            <li>All property listings must be genuine, available, and priced accurately.</li>
                            <li>Duplicate, misleading, or unauthorized third-party media is strictly prohibited.</li>
                        </ul>

                        <h3>5. Intellectual Property & Governing Law</h3>
                        <p>All platform software, branding, and content are protected by Egyptian Intellectual Property Law No. 82 of 2002. Any dispute shall be governed by the laws of the Arab Republic of Egypt.</p>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="mt-5 pt-4 border-top text-center text-muted small">
                    <p class="mb-0">
                        <?php echo e($isAr
                            ? 'لأي استفسارات قانونية أو مراجعة شروط الاستخدام، تواصل مع فريق الشؤون القانونية عبر: legal@darlii.eg'
                            : 'For legal inquiries or terms review, contact our legal desk at: legal@darlii.eg'); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/pages/terms-and-conditions.blade.php ENDPATH**/ ?>