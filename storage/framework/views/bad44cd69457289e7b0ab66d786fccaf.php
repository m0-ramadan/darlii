<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="testimonials" class="flat-section flat-testimonial-v4">
    <div class="container">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
            <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
        </div>
        <div class="swiper tf-sw-testimonial" data-preview-lg="2" data-preview-md="2" data-preview-sm="1" data-space="30" data-autoplay="true" data-autoplay-speed="5000" data-loop="true">
            <div class="swiper-wrapper">
                <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="swiper-slide">
                        <div class="box-tes-item style-2">
                            <ul class="list-star"><?php for($star = 0; $star < $testimonial['rating']; $star++): ?><li class="icon icon-star"></li><?php endfor; ?></ul>
                            <p class="note body-1"><?php echo e($testimonial['content']); ?></p>
                            <div class="box-avt d-flex align-items-center gap-12">
                                <div class="avatar avt-60 round"><img width="60" height="60" src="<?php echo e($testimonial['image']); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($testimonial['name']); ?>"></div>
                                <div class="info"><div class="h7 fw-7"><?php echo e($testimonial['name']); ?></div><p class="text-variant-1 mt-4"><?php echo e($testimonial['position']); ?></p></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="sw-pagination sw-pagination-testimonial"></div>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/resources/views/legacy/blocks/testimonials.blade.php ENDPATH**/ ?>