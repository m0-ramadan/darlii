<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="blog-posts" class="flat-section-v3 flat-latest-new">
    <div class="container">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
            <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
        </div>
        <div class="row">
            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="box col-lg-4 col-md-6">
                    <div class="flat-blog-item hover-img">
                        <a class="img-style" href="<?php echo e($post['url']); ?>"><img width="400" height="260" src="<?php echo e($post['image']); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($post['title']); ?>"><span class="date-post"><?php echo e($post['date']); ?></span></a>
                        <div class="content-box">
                            <div class="post-author"><span class="fw-6"><?php echo e($post['author']); ?></span></div>
                            <h3 class="title h4"><a href="<?php echo e($post['url']); ?>" class="w-100 text-truncate"><?php echo e($post['title']); ?></a></h3>
                            <p class="description"><?php echo e($post['excerpt']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</section>
<?php /**PATH /var/www/html/resources/views/legacy/blocks/blog-posts.blade.php ENDPATH**/ ?>