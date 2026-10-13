<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $tr = $career->translation($locale);

    /** التاريخ: عربي بصيغة مقروءة، وإنجليزي ISO. */
    $fmtDate = function ($value) use ($isAr): string {
        if (blank($value)) {
            return '—';
        }

        try {
            $date = $value instanceof \DateTimeInterface
                ? \Illuminate\Support\Carbon::instance($value)
                : \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable $e) {
            return (string) $value;
        }

        return $isAr
            ? $date->locale('ar')->translatedFormat('j F Y')
            : $date->format('Y-m-d');
    };

    /** نطاق الراتب بالجنيه المصري (الرمز حسب اللغة). */
    $salaryRange = (function () use ($career, $isAr): ?string {
        $min = $career->salary_min;
        $max = $career->salary_max;

        if (blank($min) && blank($max)) {
            return null;
        }

        $symbol = $isAr ? 'ج.م' : 'EGP';
        $format = fn ($value): string => number_format((float) $value);

        if (! blank($min) && ! blank($max)) {
            return $format($min) . ' - ' . $format($max) . ' ' . $symbol;
        }

        return $format(blank($min) ? $max : $min) . ' ' . $symbol;
    })();

    $phone = trim((string) setting('contact.phone', ''));
    $email = trim((string) setting('contact.email', ''));
    $whatsapp = trim((string) setting('contact.whatsapp', ''));
    $whatsappDigits = preg_replace('/\D+/', '', $whatsapp) ?? '';

    $facts = array_values(array_filter([
        filled($career->location) ? [
            'icon' => 'icon-mapPin',
            'label' => $isAr ? 'الموقع' : 'Location',
            'value' => $career->location,
        ] : null,
        filled($career->employment_type) ? [
            'icon' => 'icon-briefcase',
            'label' => $isAr ? 'نوع الدوام' : 'Employment type',
            'value' => $career->employment_type,
        ] : null,
        filled($career->experience_level) ? [
            'icon' => 'icon-user',
            'label' => $isAr ? 'مستوى الخبرة' : 'Experience level',
            'value' => $career->experience_level,
        ] : null,
        $salaryRange ? [
            'icon' => 'icon-money',
            'label' => $isAr ? 'الراتب' : 'Salary range',
            'value' => $salaryRange,
        ] : null,
        $career->deadline ? [
            'icon' => 'icon-calendar',
            'label' => $isAr ? 'آخر موعد للتقديم' : 'Application deadline',
            'value' => $fmtDate($career->deadline),
        ] : null,
    ]));
?>

<?php $__env->startSection('title', ($tr?->seo_title ?: $tr?->title ?: ($isAr ? 'وظيفة' : 'Career')) . ($isAr ? ' | دارلي' : ' | Darlii')); ?>

<?php $__env->startSection('content'); ?>
    
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('home', ['locale' => $locale])); ?>"><?php echo e($isAr ? 'الرئيسية' : 'Home'); ?></a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="<?php echo e(route('careers.index', ['locale' => $locale])); ?>"><?php echo e($isAr ? 'الوظائف' : 'Careers'); ?></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo e($tr?->title); ?></li>
                </ol>
            </nav>
        </div>
    </section>

    
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="darli-panel darli-panel--pad">
                <?php if(filled($career->department)): ?>
                    <div class="d-flex flex-wrap gap-8 mb-2">
                        <span class="flag-tag primary"><?php echo e($career->department); ?></span>
                    </div>
                <?php endif; ?>

                <h1 class="h4 fw-7 mb-3"><?php echo e($tr?->title); ?></h1>

                <?php if(filled($tr?->summary)): ?>
                    <p class="text-1 mb-4"><?php echo e($tr->summary); ?></p>
                <?php endif; ?>

                <?php if($facts !== []): ?>
                    <div class="darli-facts">
                        <?php $__currentLoopData = $facts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="darli-fact">
                                <div class="darli-fact__label">
                                    <i class="icon <?php echo e($fact['icon']); ?>"></i><?php echo e($fact['label']); ?>

                                </div>
                                <div class="darli-fact__value"><?php echo e($fact['value']); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <?php if(filled($tr?->description)): ?>
        <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
            <div class="container">
                <div class="darli-panel darli-panel--pad">
                    <div class="h5 fw-7 mb-3"><?php echo e($isAr ? 'الوصف الوظيفي' : 'Job description'); ?></div>
                    <div class="text-1" style="line-height:2;"><?php echo nl2br(e($tr->description)); ?></div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="darli-grid darli-grid--2">
                
                <div class="darli-panel darli-panel--pad" id="apply">
                    <div class="h5 fw-7 mb-2"><?php echo e($isAr ? 'تقدّم للوظيفة' : 'Apply for this job'); ?></div>
                    <p class="text-1">
                        <?php echo e($isAr
                            ? 'املأ البيانات التالية وسيتواصل معك فريق التوظيف. يُرجى كتابة مؤهلاتك وسنوات خبرتك ورابط سيرتك الذاتية أو أعمالك (Portfolio) داخل خانة الرسالة.'
                            : 'Fill in the form below and our recruitment team will get back to you. Please include your qualifications, years of experience and a link to your CV or portfolio in the message field.'); ?>

                    </p>

                    <?php if(session('success')): ?>
                        <div class="alert alert-success border-0 rounded-3"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>

                    <?php if($errors->any()): ?>
                        <div class="alert alert-danger border-0 rounded-3">
                            <ul class="mb-0 ps-3">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo e(route('leads.store', ['locale' => $locale])); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="source" value="career">

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-name">
                                <?php echo e($isAr ? 'الاسم' : 'Name'); ?> <span class="text-danger">*</span>
                            </label>
                            <input id="career-lead-name" type="text" name="name" class="darli-input" required
                                maxlength="120" value="<?php echo e(old('name')); ?>"
                                placeholder="<?php echo e($isAr ? 'اسمك بالكامل' : 'Your full name'); ?>">
                        </div>

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-email">
                                <?php echo e($isAr ? 'البريد الإلكتروني' : 'Email'); ?>

                            </label>
                            <input id="career-lead-email" type="email" name="email" class="darli-input" dir="ltr"
                                maxlength="190" value="<?php echo e(old('email')); ?>" placeholder="name@example.com">
                        </div>

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-phone">
                                <?php echo e($isAr ? 'رقم الهاتف' : 'Phone'); ?>

                            </label>
                            <input id="career-lead-phone" type="text" name="phone" class="darli-input" dir="ltr"
                                maxlength="50" value="<?php echo e(old('phone')); ?>"
                                placeholder="<?php echo e($isAr ? '01xxxxxxxxx' : '+20 1xx xxx xxxx'); ?>">
                        </div>

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-message">
                                <?php echo e($isAr ? 'الرسالة (المؤهلات وسنوات الخبرة ورابط السيرة الذاتية)' : 'Message (qualifications, experience and CV link)'); ?>

                            </label>
                            <textarea id="career-lead-message" name="message" class="darli-textarea" rows="6" maxlength="5000"
                                placeholder="<?php echo e($isAr
                                    ? 'أرغب في التقدم لوظيفة ' . ($tr?->title ?? '') . ' — مؤهلاتي: … سنوات الخبرة: … رابط السيرة الذاتية: …'
                                    : 'I would like to apply for ' . ($tr?->title ?? '') . ' — qualifications: … experience: … CV link: …'); ?>"><?php echo e(old('message')); ?></textarea>
                        </div>

                        <button type="submit" class="tf-btn primary w-100">
                            <?php echo e($isAr ? 'إرسال طلب التقديم' : 'Submit application'); ?>

                        </button>
                    </form>
                </div>

                
                <aside class="darli-panel darli-panel--pad-sm h-100">
                    <div class="h7 fw-7 mb-3"><?php echo e($isAr ? 'بيانات التواصل' : 'Contact details'); ?></div>

                    <?php if($phone === '' && $email === '' && $whatsappDigits === ''): ?>
                        <p class="text-1 mb-0">
                            <?php echo e($isAr
                                ? 'يمكنك إرسال طلبك من خلال النموذج وسنتواصل معك.'
                                : 'Send your application through the form and we will contact you.'); ?>

                        </p>
                    <?php else: ?>
                        <ul class="darli-list-reset d-flex flex-column gap-2">
                            <?php if($phone !== ''): ?>
                                <li>
                                    <a class="d-flex gap-8 align-items-center" href="tel:<?php echo e(preg_replace('/\s+/', '', $phone)); ?>">
                                        <i class="icon icon-phone"></i>
                                        <span class="text-1" dir="ltr"><?php echo e($phone); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if($email !== ''): ?>
                                <li>
                                    <a class="d-flex gap-8 align-items-center" href="mailto:<?php echo e($email); ?>">
                                        <i class="icon icon-mail"></i>
                                        <span class="text-1" dir="ltr"><?php echo e($email); ?></span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if($whatsappDigits !== ''): ?>
                                <li class="mt-2">
                                    <a class="darli-whatsapp" href="https://wa.me/<?php echo e($whatsappDigits); ?>"
                                        target="_blank" rel="noopener noreferrer">
                                        <?php echo e($isAr ? 'تواصل عبر واتساب' : 'Chat on WhatsApp'); ?>

                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    <?php endif; ?>

                    <div class="text-1 mt-4">
                        <?php echo e($isAr ? 'الوظيفة المعلنة:' : 'Position:'); ?>

                        <span class="fw-6"><?php echo e($tr?->title); ?></span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="box-title-listing mb-0">
                <div class="h5 fw-7"><?php echo e($isAr ? 'ابحث عن وظيفة أخرى' : 'Looking for another role?'); ?></div>
                <a class="tf-btn primary" href="<?php echo e(route('careers.index', ['locale' => $locale])); ?>">
                    <?php echo e($isAr ? 'كل الوظائف' : 'All careers'); ?>

                </a>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/careers/show.blade.php ENDPATH**/ ?>