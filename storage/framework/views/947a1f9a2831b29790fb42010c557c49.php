<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $tr = $agent->translation($locale);
    $name = $tr?->display_name ?: $agent->user?->name;
?>

<?php $__env->startSection('title', $name); ?>

<?php $__env->startSection('content'); ?>
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-4">
        <div class="container">
            
            <div class="mb-3 darli-muted" style="font-size:14px;">
                <a href="<?php echo e(route('home', ['locale' => $locale])); ?>"><?php echo e($isAr ? 'الرئيسية' : 'Home'); ?></a>
                <span> › </span>
                <a href="<?php echo e(route('agents.index', ['locale' => $locale])); ?>"><?php echo e($isAr ? 'السماسرة' : 'Agents'); ?></a>
                <span> › </span>
                <span><?php echo e($name); ?></span>
            </div>

            
            <div class="darli-panel darli-panel--pad" style="margin-bottom:28px;">
                <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
                    <img src="<?php echo e($agent->user?->avatar_url); ?>" alt="<?php echo e($name); ?>"
                        style="width:140px;height:140px;border-radius:50%;object-fit:cover;flex:0 0 auto;">

                    <div style="flex:1;min-width:260px;">
                        <h1 class="h4 fw-7" style="margin-bottom:6px;"><?php echo e($name); ?></h1>
                        <p class="darli-muted" style="margin-bottom:12px;">
                            <?php echo e($tr?->job_title ?: ($agent->job_title ?: ($isAr ? 'سمسار عقارات' : 'Real estate agent'))); ?>

                            <?php if($agent->agency): ?>
                                — <?php echo e($agent->agency->name); ?>

                            <?php endif; ?>
                        </p>

                        <div class="darli-facts" style="margin-bottom:16px;">
                            <?php if($agent->years_experience): ?>
                                <div class="darli-fact">
                                    <div class="darli-fact__label"><?php echo e($isAr ? 'سنوات الخبرة' : 'Years of experience'); ?></div>
                                    <div class="darli-fact__value"><?php echo e($agent->years_experience); ?></div>
                                </div>
                            <?php endif; ?>
                            <div class="darli-fact">
                                <div class="darli-fact__label"><?php echo e($isAr ? 'العقارات المعروضة' : 'Active listings'); ?></div>
                                <div class="darli-fact__value"><?php echo e(number_format($properties->count())); ?></div>
                            </div>
                            <?php if($agent->license_number): ?>
                                <div class="darli-fact">
                                    <div class="darli-fact__label"><?php echo e($isAr ? 'رقم الترخيص' : 'Licence number'); ?></div>
                                    <div class="darli-fact__value" dir="ltr"><?php echo e($agent->license_number); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            <?php if($agent->whatsapp || $agent->user?->phone): ?>
                                <a class="darli-whatsapp" dir="ltr"
                                    href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $agent->whatsapp ?: $agent->user->phone)); ?>">
                                    <?php echo e($isAr ? 'واتساب' : 'WhatsApp'); ?>

                                </a>
                            <?php endif; ?>
                            <?php if($agent->user?->phone): ?>
                                <a class="tf-btn size-1" dir="ltr" href="tel:<?php echo e($agent->user->phone); ?>"><?php echo e($agent->user->phone); ?></a>
                            <?php endif; ?>
                            <?php if($agent->user?->email): ?>
                                <a class="tf-btn size-1" dir="ltr" href="mailto:<?php echo e($agent->user->email); ?>"><?php echo e($agent->user->email); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($tr?->bio || $agent->bio): ?>
                    <hr style="border:0;border-top:1px solid #e9eef1;margin:20px 0;">
                    <h2 class="h6 fw-7" style="margin-bottom:8px;"><?php echo e($isAr ? 'نبذة' : 'About'); ?></h2>
                    <div style="line-height:1.9;"><?php echo nl2br(e($tr?->bio ?: $agent->bio)); ?></div>
                <?php endif; ?>
            </div>

            
            <div class="box-title-listing">
                <div class="h5 fw-7"><?php echo e($isAr ? 'عقارات هذا السمسار' : 'Listings by this agent'); ?></div>
            </div>

            <?php if($properties->isEmpty()): ?>
                <div class="darli-panel darli-empty">
                    <p class="mb-0"><?php echo e($isAr ? 'لا توجد عقارات منشورة لهذا السمسار حالياً.' : 'This agent has no published listings yet.'); ?></p>
                </div>
            <?php else: ?>
                <div class="darli-grid darli-grid--3">
                    <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div>
                            <?php if (isset($component)) { $__componentOriginal921a5e9c4a8716397bc69069f0f003a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal921a5e9c4a8716397bc69069f0f003a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.legacy-property-card','data' => ['property' => $property,'locale' => $locale]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('legacy-property-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['property' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($property),'locale' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($locale)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal921a5e9c4a8716397bc69069f0f003a5)): ?>
<?php $attributes = $__attributesOriginal921a5e9c4a8716397bc69069f0f003a5; ?>
<?php unset($__attributesOriginal921a5e9c4a8716397bc69069f0f003a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal921a5e9c4a8716397bc69069f0f003a5)): ?>
<?php $component = $__componentOriginal921a5e9c4a8716397bc69069f0f003a5; ?>
<?php unset($__componentOriginal921a5e9c4a8716397bc69069f0f003a5); ?>
<?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/agents/show.blade.php ENDPATH**/ ?>