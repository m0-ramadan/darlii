<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="agents" class="flat-section flat-agents" style="background-color: <?php echo e($attributes['background_color'] ?? 'transparent'); ?>;">
    <div class="container">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
            <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $agent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="box col mb-4">
                    <div class="box-agent style-card hover-img wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
                        <div class="box-img img-style">
                            <img width="450" height="450" src="<?php echo e($agent['image']); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($agent['name']); ?>">
                            <?php if($agent['facebook'] || $agent['instagram'] || $agent['linkedin']): ?>
                                <ul class="agent-social">
                                    <?php if($agent['facebook']): ?><li><a href="<?php echo e($agent['facebook']); ?>" target="_blank" rel="noopener noreferrer"><i class="icon icon-facebook"></i></a></li><?php endif; ?>
                                    <?php if($agent['instagram']): ?><li><a href="<?php echo e($agent['instagram']); ?>" target="_blank" rel="noopener noreferrer"><i class="icon icon-instagram"></i></a></li><?php endif; ?>
                                    <?php if($agent['linkedin']): ?><li><a href="<?php echo e($agent['linkedin']); ?>" target="_blank" rel="noopener noreferrer"><i class="icon icon-linkedin"></i></a></li><?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="content">
                            <div class="agent-card-heading">
                                <h3 class="h6 agent-card-name"><a href="<?php echo e($agent['url']); ?>" class="link"><?php echo e($agent['name']); ?></a> <?php if($agent['verified']): ?><span class="verified-badge verified-badge-sm" title="Verified Agent">✓</span><?php endif; ?></h3>
                                <?php if($agent['email']): ?><a href="mailto:<?php echo e($agent['email']); ?>" class="agent-card-action" aria-label="Email <?php echo e($agent['name']); ?>"><i class="icon icon-mail"></i></a><?php endif; ?>
                            </div>
                            <ul class="list-info">
                                <?php if($agent['phone']): ?><li><a href="tel:<?php echo e($agent['phone']); ?>"><i class="icon icon-phone"></i><?php echo e($agent['phone']); ?></a></li><?php endif; ?>
                                <?php if($agent['email']): ?><li><a href="mailto:<?php echo e($agent['email']); ?>"><i class="icon icon-mail"></i><?php echo e($agent['email']); ?></a></li><?php endif; ?>
                                <?php if($agent['whatsapp']): ?><li><a href="https://wa.me/<?php echo e(preg_replace('/\D+/', '', $agent['whatsapp'])); ?>" target="_blank" rel="noopener noreferrer"><i class="icon icon-phone2"></i>WhatsApp</a></li><?php endif; ?>
                            </ul>
                        </div>
                        <div class="agent-card-footer">
                            <span class="agent-card-properties"><i class="icon icon-home"></i><?php echo e($agent['properties_count']); ?> <?php echo e($locale === 'ar' ? 'عقار' : 'Properties'); ?></span>
                            <a href="<?php echo e($agent['url']); ?>" class="tf-btn agent-card-view"><?php echo e($locale === 'ar' ? 'عرض' : 'View'); ?></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/resources/views/legacy/blocks/agents.blade.php ENDPATH**/ ?>