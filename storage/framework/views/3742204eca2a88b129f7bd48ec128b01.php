<section class="darli-featured-section">
  <div class="darli-container">
    <?php if($properties->isNotEmpty()): ?>
      <div class="darli-section-header-flex">
        <div>
          <span class="darli-section-tag">— <?php echo e($locale === 'ar' ? 'العقارات المميزة' : 'Featured Properties'); ?> —</span>
          <h2 class="darli-section-title"><?php echo e($locale === 'ar' ? 'اكتشف أفضل العقارات في دارلي' : 'Discover the Best Properties in Darlii'); ?><br><?php echo e($locale === 'ar' ? 'لمنازل أحلامك' : 'For Your Dream Homes'); ?></h2>
        </div>
        <div>
          <a href="<?php echo e(route('properties.index')); ?>" class="darli-view-all-link">
            <span><?php echo e($locale === 'ar' ? 'عرض جميع العقارات' : 'View All Properties'); ?></span>
            <i data-lucide="<?php echo e($locale === 'ar' ? 'arrow-left' : 'arrow-right'); ?>" style="width: 16px; height: 16px;"></i>
          </a>
        </div>
      </div>
    <?php endif; ?>

    <div class="darli-properties-grid">
      <?php $__empty_1 = true; $__currentLoopData = $properties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prop): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
          $tr = $prop->translation($locale);
          $slug = $tr?->slug ?: $prop->id;
          $url = route('properties.show', ['slug' => $slug]);
          $image = $prop->primaryImageUrl($locale);
          $isRent = $prop->purpose === 'rent';
          $isSold = $prop->isSold();
          $isContact = $prop->isPriceOnContact() || (float)$prop->price <= 0;
          $priceDisplay = $isContact
              ? ($locale === 'ar' ? 'السعر عند التواصل' : 'Price on request')
              : money_eg($prop->price);
          $locationText = $tr?->address_text ?: ($prop->location?->translation($locale)?->name ?? ($locale === 'ar' ? 'بني سويف' : 'Beni Suef'));
        ?>
        <div class="darli-property-card" onclick="if (!event.target.closest('button') && !event.target.closest('a')) window.location.href='<?php echo e($url); ?>'" style="cursor: pointer;">
          <div class="darli-card-image-box">
            <a href="<?php echo e($url); ?>" style="display: block; width: 100%; height: 100%;">
              <img src="<?php echo e($image); ?>" alt="<?php echo e($tr?->name); ?>" class="darli-card-image"
                loading="lazy" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
            </a>
            <div class="d-flex gap-2" style="position: absolute; top: 12px; right: 12px; z-index: 2;">
              <?php if($isSold): ?>
                <span class="darli-card-badge" style="position: static; background: #10b981; color: #fff;">
                  <?php echo e($locale === 'ar' ? 'تم البيع' : 'Sold'); ?>

                </span>
              <?php endif; ?>
              <span class="darli-card-badge <?php echo e($prop->is_featured ? 'badge-gold' : ''); ?>" style="position: static;">
                <?php echo e($isRent ? ($locale === 'ar' ? 'للإيجار' : 'For Rent') : ($locale === 'ar' ? 'للبيع' : 'For Sale')); ?>

              </span>
            </div>
            <button class="darli-card-heart" data-property-id="<?php echo e($prop->id); ?>" aria-label="<?php echo e($locale === 'ar' ? 'أضف للمفضلة' : 'Add to favorites'); ?>" onclick="event.stopPropagation();">
              <i data-lucide="heart" style="width: 18px; height: 18px;"></i>
            </button>
          </div>
          <div class="darli-card-body">
            <a href="<?php echo e($url); ?>" class="darli-card-title line-clamp-1" title="<?php echo e($tr?->name); ?>">
              <?php echo e($tr?->name); ?>

            </a>
            <div class="darli-card-location">
              <i data-lucide="map-pin" style="width: 14px; height: 14px; color: var(--darli-gold); flex-shrink: 0;"></i>
              <span class="line-clamp-1"><?php echo e($locationText); ?></span>
            </div>
            <div class="darli-card-features">
              <div class="darli-feature-item">
                <i data-lucide="bed" style="width: 16px; height: 16px; color: var(--darli-gold);"></i>
                <span><?php echo e($prop->bedrooms ? $prop->bedrooms . ' ' . ($locale === 'ar' ? 'غرف' : 'Beds') : ($locale === 'ar' ? 'مساحة خاصة' : 'Private')); ?></span>
              </div>
              <div class="darli-feature-item">
                <i data-lucide="bath" style="width: 16px; height: 16px; color: var(--darli-gold);"></i>
                <span><?php echo e($prop->bathrooms ? $prop->bathrooms . ' ' . ($locale === 'ar' ? 'حمام' : 'Baths') : ($locale === 'ar' ? 'خدمات' : 'Amenities')); ?></span>
              </div>
              <div class="darli-feature-item">
                <i data-lucide="maximize" style="width: 16px; height: 16px; color: var(--darli-gold);"></i>
                <span><?php echo e($prop->area ? (int)$prop->area . ' م²' : ($locale === 'ar' ? 'متعدد' : 'Multi')); ?></span>
              </div>
            </div>
            <div class="darli-card-footer">
              <div class="darli-card-price"><?php echo e($priceDisplay); ?></div>
              <a href="<?php echo e($url); ?>" class="darli-btn-details">
                <span><?php echo e($locale === 'ar' ? 'عرض التفاصيل' : 'Details'); ?></span>
                <i data-lucide="<?php echo e($locale === 'ar' ? 'arrow-left' : 'arrow-right'); ?>" style="width: 14px; height: 14px;"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="grid-column: 1 / -1; width: 100%; max-width: 1024px; margin: 0 auto;">
          <img
            src="<?php echo e(asset($locale === 'ar' ? 'themes/homzen/images/darli-placeholder-ar.png' : 'themes/homzen/images/darli-placeholder-en.png')); ?>"
            alt="<?php echo e($locale === 'ar' ? 'لا توجد بيانات متاحة حاليًا' : 'No data is currently available'); ?>"
            style="display: block; width: 100%; height: auto; border-radius: 28px;"
          >
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php /**PATH /var/www/html/resources/views/legacy/homepage-featured-properties.blade.php ENDPATH**/ ?>