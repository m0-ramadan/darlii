<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $tr = $agent->translation($locale);
    $name = $tr?->display_name ?: $agent->user?->name;
    $avatarUrl = $agent->user?->avatar_url;
    if (!$avatarUrl || str_contains($avatarUrl, 'placeholder')) {
        $avatarUrl = asset($agent->avatar_path ?: 'storage/avatars/1.jpg');
    }
?>

<?php $__env->startSection('title', $name); ?>

<?php $__env->startSection('content'); ?>
    <style>
        .agent-detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 32px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: background-color 0.2s, border-color 0.2s;
        }
        .agent-detail-title {
            color: #0f172a !important;
            font-size: 26px !important;
            font-weight: 800 !important;
            margin-bottom: 6px !important;
        }
        .agent-detail-subtitle {
            color: #64748b !important;
            font-size: 15px !important;
            margin-bottom: 16px !important;
        }
        .agent-detail-facts {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .agent-detail-fact {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            min-width: 130px;
        }
        .agent-detail-fact-label {
            color: #64748b !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            margin-bottom: 2px;
        }
        .agent-detail-fact-value {
            color: #0f172a !important;
            font-size: 18px !important;
            font-weight: 800 !important;
        }
        .agent-detail-bio {
            color: #334155 !important;
            font-size: 15px !important;
            line-height: 1.85 !important;
        }
        html[data-theme="dark"] .agent-detail-card {
            background: #0e1e34 !important;
            border-color: #1a3152 !important;
        }
        html[data-theme="dark"] .agent-detail-title,
        html[data-theme="dark"] .agent-detail-fact-value {
            color: #ffffff !important;
        }
        html[data-theme="dark"] .agent-detail-subtitle,
        html[data-theme="dark"] .agent-detail-fact-label {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .agent-detail-fact {
            background: #081528 !important;
            border-color: #1a3152 !important;
        }
        html[data-theme="dark"] .agent-detail-bio {
            color: #cbd5e1 !important;
        }
    </style>

    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-4">
        <div class="container">
            
            <div class="mb-3 darli-muted" style="font-size:14px;">
                <a href="<?php echo e(route('home', ['locale' => $locale])); ?>"><?php echo e($isAr ? 'الرئيسية' : 'Home'); ?></a>
                <span> › </span>
                <a href="<?php echo e(route('agents.index', ['locale' => $locale])); ?>"><?php echo e($isAr ? 'الوكلاء والشركات' : 'Agents & Partners'); ?></a>
                <span> › </span>
                <span><?php echo e($name); ?></span>
            </div>

            
            <div class="agent-detail-card">
                <div style="display:flex;gap:26px;align-items:flex-start;flex-wrap:wrap;">
                    <div style="width:120px;height:120px;border-radius:18px;overflow:hidden;flex-shrink:0;border:2px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                        <img src="<?php echo e($avatarUrl); ?>" alt="<?php echo e($name); ?>"
                            onerror="this.onerror=null;this.src='<?php echo e(asset('themes/homzen/images/darli-placeholder.png')); ?>';"
                            style="width:100%;height:100%;object-fit:cover;">
                    </div>

                    <div style="flex:1;min-width:260px;">
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="margin-bottom: 4px;">
                            <h1 class="agent-detail-title"><?php echo e($name); ?></h1>
                            <span class="badge" style="background:#c39953;color:#ffffff;font-size:12px;padding:4px 10px;border-radius:6px;">
                                <?php echo e($agent->city); ?>

                            </span>
                            <?php if($agent->isVerified()): ?>
                                <span class="badge bg-success" style="font-size:12px;padding:4px 10px;border-radius:6px;">
                                    ✓ معتمد
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="agent-detail-subtitle">
                            <?php echo e($tr?->job_title ?: ($agent->job_title ?: ($isAr ? 'شركة وساطة وتطوير عقاري' : 'Real estate company'))); ?>

                            <?php if($agent->agency): ?>
                                — <?php echo e($agent->agency->name); ?>

                            <?php endif; ?>
                        </p>

                        <div class="agent-detail-facts">
                            <?php if($agent->years_experience): ?>
                                <div class="agent-detail-fact">
                                    <div class="agent-detail-fact-label"><?php echo e($isAr ? 'سنوات الخبرة' : 'Years of experience'); ?></div>
                                    <div class="agent-detail-fact-value"><?php echo e($agent->years_experience); ?> سنة</div>
                                </div>
                            <?php endif; ?>
                            <div class="agent-detail-fact">
                                <div class="agent-detail-fact-label"><?php echo e($isAr ? 'العقارات المعروضة' : 'Active listings'); ?></div>
                                <div class="agent-detail-fact-value"><?php echo e(number_format($properties->count())); ?></div>
                            </div>
                            <?php if($agent->license_number): ?>
                                <div class="agent-detail-fact">
                                    <div class="agent-detail-fact-label"><?php echo e($isAr ? 'رقم الترخيص' : 'Licence number'); ?></div>
                                    <div class="agent-detail-fact-value" dir="ltr"><?php echo e($agent->license_number); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            <?php if($agent->whatsapp || $agent->user?->phone): ?>
                                <a class="darli-whatsapp" dir="ltr" target="_blank" rel="noopener"
                                    href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $agent->whatsapp ?: $agent->user->phone)); ?>"
                                    style="padding:10px 20px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                    💬 <?php echo e($isAr ? 'مراسلة واتساب' : 'WhatsApp'); ?>

                                </a>
                            <?php endif; ?>
                            <?php if($agent->user?->phone): ?>
                                <a class="tf-btn size-1" dir="ltr" href="tel:<?php echo e($agent->user->phone); ?>"
                                    style="padding:10px 20px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                    📞 <?php echo e($agent->user->phone); ?>

                                </a>
                            <?php endif; ?>
                            <?php if($agent->user?->email): ?>
                                <a class="tf-btn size-1" dir="ltr" href="mailto:<?php echo e($agent->user->email); ?>"
                                    style="padding:10px 20px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                    ✉️ <?php echo e($agent->user->email); ?>

                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($tr?->bio || $agent->bio): ?>
                    <hr style="border:0;border-top:1px solid #e9eef1;margin:24px 0;">
                    <h2 class="h6 fw-7" style="margin-bottom:10px;color:var(--ap-gold,#c39953);"><?php echo e($isAr ? 'نبذة عن الشركة / الوكيل' : 'About the Agency'); ?></h2>
                    <div class="agent-detail-bio"><?php echo nl2br(e($tr?->bio ?: $agent->bio)); ?></div>
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