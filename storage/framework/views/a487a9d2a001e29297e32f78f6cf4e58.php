<section class="darli-blog-section">
    <div class="darli-container">
        <span class="darli-section-tag">— <?php echo e($locale === 'ar' ? 'نبض العقارات' : 'Real Estate Pulse'); ?> —</span>
        <h2 class="darli-section-title"><?php echo e($locale === 'ar' ? 'أحدث المقالات' : 'Latest Articles'); ?></h2>
        <p class="darli-section-subtitle">
            <?php echo e($locale === 'ar' ? 'تابع آخر أخبار ونصائح السوق العقاري من خبرائنا' : 'Follow the latest real estate news and advice from our experts'); ?>

        </p>

        <div class="darli-blog-grid">
            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="darli-blog-card">
                    <a href="<?php echo e($post['url']); ?>" class="darli-blog-img-wrapper" style="display: block;" aria-label="<?php echo e($post['title']); ?>">
                        <img src="<?php echo e($post['image']); ?>" alt="<?php echo e($post['title']); ?>" class="darli-blog-img"
                            loading="lazy" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
                        <?php if($post['date']): ?>
                            <span class="darli-blog-date-badge"><?php echo e($post['date']); ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="darli-blog-body">
                        <a href="<?php echo e($post['url']); ?>" class="darli-blog-title"><?php echo e($post['title']); ?></a>
                        <p class="darli-blog-excerpt"><?php echo e($post['excerpt']); ?></p>
                        <a href="<?php echo e($post['url']); ?>" class="darli-blog-link">
                            <span><?php echo e($locale === 'ar' ? 'اقرأ المزيد' : 'Read more'); ?></span>
                            <i data-lucide="<?php echo e($locale === 'ar' ? 'arrow-left' : 'arrow-right'); ?>" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/resources/views/legacy/homepage-posts.blade.php ENDPATH**/ ?>