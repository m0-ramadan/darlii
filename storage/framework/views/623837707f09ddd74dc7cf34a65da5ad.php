<?php if($style === '6'): ?>
<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="properties" class="flat-property-v3">
    <div class="container-full">
        <div class="wrap-property-v2">
            <div class="box-inner-left img-animation wow">
                <img width="960" height="680" src="<?php echo e($backgroundImage ?: '/storage/general/placeholder.png'); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($attributes['title'] ?? ''); ?>">
            </div>
            <div class="box-inner-right">
                <div class="swiper tf-sw-property">
                    <div class="swiper-wrapper">
                        <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="swiper-slide">
                                <div class="content-property">
                                    <div class="box-title wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
                                        <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
                                        <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
                                    </div>
                                    <ul class="box-tag">
                                        <?php if($property['is_sold'] ?? false): ?>
                                            <span class="flag-tag success status-badge-sold" style="background-color: #10b981 !important; color: #fff !important; font-weight: 700;"><?php echo e($locale === 'ar' ? 'تم البيع' : 'Sold'); ?></span>
                                        <?php endif; ?>
                                        <?php if($property['featured']): ?>
                                            <span class="flag-tag success"><?php echo e($locale === 'ar' ? 'مميز' : 'Featured'); ?></span>
                                        <?php endif; ?>
                                        <span class="flag-tag primary status-badge-<?php echo e($property['purpose_class']); ?>"><?php echo e($property['purpose']); ?></span>
                                    </ul>
                                    <div class="box-name">
                                        <h3 class="title"><a href="<?php echo e($property['url']); ?>" class="link"><?php echo e($property['name']); ?></a></h3>
                                        <p class="location"><span class="icon icon-mapPin"></span><?php echo e($property['address']); ?></p>
                                    </div>
                                    <ul class="list-info">
                                        <li class="item"><i class="icon icon-bed"></i><span><?php echo e($property['bedrooms']); ?></span></li>
                                        <li class="item"><i class="icon icon-bathtub"></i><span><?php echo e($property['bathrooms']); ?></span></li>
                                        <li class="item"><i class="icon icon-ruler"></i><span><?php echo e($property['area']); ?></span></li>
                                    </ul>
                                    <div class="box-avatar d-flex gap-12 align-items-center">
                                        <div class="avatar avt-60 round"><img width="60" height="60" src="<?php echo e($property['owner_avatar']); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($property['owner_name']); ?>"></div>
                                        <div class="info">
                                            <p class="body-2 text-variant-1"><?php echo e($locale === 'ar' ? 'الوكيل' : 'Agent'); ?></p>
                                            <div class="mt-4 h7 fw-7"><?php echo e($property['owner_name']); ?> <?php if($property['owner_verified']): ?><span class="verified-badge verified-badge-sm" title="Verified Agent">✓</span><?php endif; ?></div>
                                        </div>
                                    </div>
                                    <div class="pricing-property">
                                        <div class="d-flex align-items-center"><span class="h5"><?php echo e($property['price']); ?></span></div>
                                        <div class="d-flex gap-12">
                                            <button type="button" class="box-icon w-52<?php echo e($property['favorite'] ? ' active' : ''); ?>" data-type="property" data-bb-toggle="add-to-wishlist" data-id="<?php echo e($property['id']); ?>" aria-label="<?php echo e($property['favorite'] ? 'Remove from wishlist' : 'Add to wishlist'); ?>" aria-pressed="<?php echo e($property['favorite'] ? 'true' : 'false'); ?>">
                                                <i class="icon icon-heart"></i>
                                            </button>
                                        </div>
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
<section data-shortcode-id="<?php echo e($shortcodeId); ?>" data-shortcode-name="properties" class="flat-section-v5 bg-surface flat-recommended flat-recommended-v2">
    <div class="container">
        <div class="box-title text-center wow fadeIn style-2" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary"><?php echo e($attributes['subtitle'] ?? ''); ?></div>
            <h2 class="section-title mt-4"><?php echo e($attributes['title'] ?? ''); ?></h2>
        </div>
        <div class="row wow fadeInUpSmall" data-wow-delay=".2s" data-wow-duration="2000ms">
            <?php $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $property): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-xl-4 col-md-6">
                    <div class="property-item homeya-box" data-lat="<?php echo e($property['latitude']); ?>" data-lng="<?php echo e($property['longitude']); ?>">
                        <div class="archive-top">
                            <a href="<?php echo e($property['url']); ?>" class="images-group">
                                <div class="images-style"><img width="400" height="260" src="<?php echo e($property['image']); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($property['name']); ?>"></div>
                                <div class="top">
                                    <div class="d-flex gap-8">
                                        <?php if($property['is_sold'] ?? false): ?>
                                            <span class="flag-tag success status-badge-sold" style="background-color: #10b981 !important; color: #fff !important; font-weight: 700;"><?php echo e($locale === 'ar' ? 'تم البيع' : 'Sold'); ?></span>
                                        <?php endif; ?>
                                        <?php if($property['featured']): ?><span class="flag-tag success"><?php echo e($locale === 'ar' ? 'مميز' : 'Featured'); ?></span><?php endif; ?>
                                        <span class="flag-tag primary status-badge-<?php echo e($property['purpose_class']); ?>"><?php echo e($property['purpose']); ?></span>
                                    </div>
                                    <div class="d-flex gap-4">
                                        <button type="button" class="box-icon w-32<?php echo e($property['favorite'] ? ' active' : ''); ?>" data-type="property" data-bb-toggle="add-to-wishlist" data-id="<?php echo e($property['id']); ?>" aria-label="<?php echo e($property['favorite'] ? 'Remove from wishlist' : 'Add to wishlist'); ?>" aria-pressed="<?php echo e($property['favorite'] ? 'true' : 'false'); ?>">
                                            <i class="icon icon-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="bottom"><span class="flag-tag style-2"><?php echo e($property['category']); ?></span></div>
                            </a>
                            <div class="content">
                                <div class="text-capitalize h7 fw-7"><a href="<?php echo e($property['url']); ?>" class="link line-clamp-1" title="<?php echo e($property['name']); ?>"><?php echo e($property['name']); ?></a></div>
                                <div class="desc"><i class="icon icon-mapPin"></i><p class="line-clamp-1"><?php echo e($property['address']); ?></p></div>
                                <ul class="meta-list">
                                    <li class="item"><i class="icon icon-bed"></i><span><?php echo e($property['bedrooms']); ?></span></li>
                                    <li class="item"><i class="icon icon-bathtub"></i><span><?php echo e($property['bathrooms']); ?></span></li>
                                    <li class="item"><i class="icon icon-ruler"></i><span><?php echo e($property['area']); ?></span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="archive-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-8 align-items-center">
                                <div class="avatar avt-40 round"><img width="40" height="40" src="<?php echo e($property['owner_avatar']); ?>" data-bb-lazy="true" loading="lazy" alt="<?php echo e($property['owner_name']); ?>"></div>
                                <span><?php echo e($property['owner_name']); ?> <?php if($property['owner_verified']): ?><span class="verified-badge verified-badge-sm" title="Verified Agent">✓</span><?php endif; ?></span>
                            </div>
                            <div class="d-flex align-items-center"><span class="h6"><?php echo e($property['price']); ?></span></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if(! empty($attributes['button_label'])): ?>
            <div class="text-center mt-5"><a class="tf-btn primary size-1" href="/<?php echo e($locale); ?>/properties"><?php echo e($attributes['button_label']); ?></a></div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/legacy/blocks/properties.blade.php ENDPATH**/ ?>