<?php if($style === '5'): ?>
<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="services" class="flat-benefit-v2">
    <div class="container">
        <div class="row wrap-benefit-v2">
            <div class="col-lg-4 wow fadeIn">
                <div class="box-left">
                    <div class="box-title">
                        <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
                        <h2 class="section-title mt-4 text-white"><?php echo e($attributes['title'] ?? ''); ?></h2>
                        <p class="description text-white body-2"><?php echo e($attributes['description'] ?? ''); ?></p>
                    </div>
                    <div class="box-navigation">
                        <div class="navigation swiper-nav-next nav-next-benefit"><i class="icon icon-left"></i></div>
                        <div class="navigation swiper-nav-prev nav-prev-benefit"><i class="icon icon-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 wow fadeIn">
                <div class="swiper tf-sw-benefit">
                    <div class="swiper-wrapper">
                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="swiper-slide">
                                <div class="box-right">
                                    <div class="box-benefit style-1">
                                        <div class="icon-box"><i class="icon <?php echo e($service['icon']); ?>"></i></div>
                                        <div class="content"><h3 class="title link"><?php echo e($service['title']); ?></h3><p class="description"><?php echo e($service['description']); ?></p></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="services" class="flat-section flat-service-v4" style="background-color: <?php echo e($attributes['background_color'] ?? 'transparent'); ?>;">
    <div class="container">
        <div class="wrap-service-v4 wow fadeInUpSmall" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="inner-service-left">
                <div class="img-service"><img width="551" height="654" src="<?php echo e($backgroundImage ?: '/storage/general/placeholder.png'); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($attributes['title'] ?? ''); ?>"></div>
            </div>
            <div class="inner-service-right">
                <div class="box-title">
                    <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
                    <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
                    <p class="desc"><?php echo e($attributes['description'] ?? ''); ?></p>
                </div>
                <ul class="list-service">
                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="box-service hover-btn-view style-4">
                            <div class="icon-box">
                                <?php if($service['image']): ?>
                                    <img width="80" height="80" src="<?php echo e($service['image']); ?>" data-bb-lazy="false" class="icon" loading="lazy" alt="<?php echo e($service['title']); ?>">
                                <?php else: ?>
                                    <i class="icon <?php echo e($service['icon']); ?>"></i>
                                <?php endif; ?>
                            </div>
                            <div class="content">
                                <h3 class="title"><?php echo e($service['title']); ?></h3>
                                <p class="description"><?php echo e($service['description']); ?></p>
                                <a href="<?php echo e($service['button_url']); ?>" class="btn-view style-1"><span class="text"><?php echo e($service['button_label']); ?></span><i class="icon icon-right"></i></a>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/legacy/blocks/services.blade.php ENDPATH**/ ?>