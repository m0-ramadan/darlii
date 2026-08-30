<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="call-to-action" class="flat-section pt-0 flat-banner">
    <div class="container">
        <div class="wrap-banner bg-surface">
            <div class="box-left">
                <div class="box-title">
                    <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
                    <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
                </div>
                <a href="<?php echo e($buttonUrl); ?>" class="tf-btn primary size-1"><?php echo e($attributes['button_label'] ?? ''); ?></a>
            </div>
            <div class="box-right"><img width="520" height="320" src="<?php echo e($image ?: '/storage/general/placeholder.png'); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($attributes['title'] ?? ''); ?>"></div>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/resources/views/legacy/blocks/call-to-action.blade.php ENDPATH**/ ?>