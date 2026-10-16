<?php if($property): ?>
<?php
  $tr = $property->translation($locale);
  $slug = $tr?->slug ?: $property->id;
  $url = route('properties.show', ['slug' => $slug]);
  $isRent = $property->purpose === 'rent';
  $isContact = $property->isPriceOnContact();
  $priceDisplay = $isContact
      ? ($locale === 'ar' ? 'السعر عند التواصل' : 'Price on request')
      : money_eg($property->price);
  $locationText = $tr?->address_text ?: ($property->location?->translation($locale)?->name ?? ($locale === 'ar' ? 'بني سويف' : 'Beni Suef'));
  $images = $property->media->pluck('path')->map(fn($p) => '/' . ltrim($p, '/'))->filter()->values()->all();
  if (empty($images)) {
      $images = ['/themes/homzen/images/darli-placeholder.png'];
  }
?>
<section class="darli-recommended-section">
  <div class="darli-container">
    <div style="text-align: center; margin-bottom: 30px;">
      <span class="darli-section-tag">— <?php echo e($locale === 'ar' ? 'عقارات مميزة' : 'Featured Properties'); ?> —</span>
      <h2 class="darli-section-title"><?php echo e($locale === 'ar' ? 'موصى به لك' : 'Recommended For You'); ?></h2>
      <p class="darli-section-subtitle"><?php echo e($locale === 'ar' ? 'اكتشف نخبة من العقارات التي تناسب احتياجاتك' : 'Discover curated properties matching your needs'); ?></p>
    </div>

    <div class="darli-recommended-card">
      <!-- Info Content -->
      <div class="darli-rec-content">
        <div class="darli-rec-badges">
          <span class="darli-card-badge" style="position: static;"><?php echo e($isRent ? ($locale === 'ar' ? 'للإيجار' : 'For Rent') : ($locale === 'ar' ? 'للبيع' : 'For Sale')); ?></span>
          <?php if($property->is_featured): ?>
            <span class="darli-card-badge badge-gold" style="position: static;"><?php echo e($locale === 'ar' ? 'مميز' : 'Featured'); ?></span>
          <?php endif; ?>
        </div>

        <h3 class="darli-rec-title">
          <a href="<?php echo e($url); ?>" style="color: inherit; text-decoration: none;">
            <?php echo e($tr?->name); ?>

          </a>
        </h3>
        <div class="darli-rec-location">
          <i data-lucide="map-pin" style="width: 16px; height: 16px; color: var(--darli-gold); flex-shrink: 0;"></i>
          <span><?php echo e($locationText); ?></span>
        </div>

        <div class="darli-rec-features">
          <?php if($property->bedrooms): ?>
            <div class="darli-feature-item">
              <i data-lucide="bed" style="width: 18px; height: 18px; color: var(--darli-gold);"></i>
              <span><?php echo e($property->bedrooms); ?> <?php echo e($locale === 'ar' ? 'غرف' : 'Beds'); ?></span>
            </div>
          <?php endif; ?>
          <?php if($property->bathrooms): ?>
            <div class="darli-feature-item">
              <i data-lucide="bath" style="width: 18px; height: 18px; color: var(--darli-gold);"></i>
              <span><?php echo e($property->bathrooms); ?> <?php echo e($locale === 'ar' ? 'حمام' : 'Baths'); ?></span>
            </div>
          <?php endif; ?>
          <?php if($property->area): ?>
            <div class="darli-feature-item">
              <i data-lucide="maximize" style="width: 18px; height: 18px; color: var(--darli-gold);"></i>
              <span><?php echo e((int)$property->area); ?> م²</span>
            </div>
          <?php endif; ?>
        </div>

        <div class="darli-rec-price"><?php echo e($priceDisplay); ?></div>

        <div class="darli-rec-actions">
          <a href="<?php echo e($url); ?>" class="darli-btn-details"
            style="padding: 10px 24px; font-size: 0.95rem; border-color: var(--darli-navy);">
            <span><?php echo e($locale === 'ar' ? 'عرض التفاصيل' : 'Details'); ?></span>
            <i data-lucide="<?php echo e($locale === 'ar' ? 'arrow-left' : 'arrow-right'); ?>" style="width: 16px; height: 16px;"></i>
          </a>
          <button class="darli-card-heart" style="position: static; border: 1px solid var(--darli-border);"
            data-property-id="<?php echo e($property->id); ?>" aria-label="<?php echo e($locale === 'ar' ? 'أضف للمفضلة' : 'Add to favorites'); ?>">
            <i data-lucide="heart" style="width: 18px; height: 18px;"></i>
          </button>
        </div>
      </div>

      <!-- Media Slider -->
      <div class="darli-rec-media">
        <a href="<?php echo e($url); ?>" style="display: block; width: 100%; height: 100%;">
          <img id="rec-slider-img" src="<?php echo e($images[0]); ?>" alt="<?php echo e($tr?->name); ?>"
            class="darli-rec-img" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
        </a>
        <?php if(count($images) > 1): ?>
          <button id="rec-prev-btn" class="darli-media-nav-arrow darli-media-prev" aria-label="السابق">
            <i data-lucide="chevron-right" style="width: 20px; height: 20px;"></i>
          </button>
          <button id="rec-next-btn" class="darli-media-nav-arrow darli-media-next" aria-label="التالي">
            <i data-lucide="chevron-left" style="width: 20px; height: 20px;"></i>
          </button>
          <script>
            (function() {
              var images = <?php echo json_encode($images, 15, 512) ?>;
              var currentIndex = 0;
              var imgEl = document.getElementById('rec-slider-img');
              var prevBtn = document.getElementById('rec-prev-btn');
              var nextBtn = document.getElementById('rec-next-btn');
              if (prevBtn && nextBtn && imgEl) {
                prevBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  currentIndex = (currentIndex - 1 + images.length) % images.length;
                  imgEl.src = images[currentIndex];
                });
                nextBtn.addEventListener('click', function(e) {
                  e.preventDefault();
                  e.stopPropagation();
                  currentIndex = (currentIndex + 1) % images.length;
                  imgEl.src = images[currentIndex];
                });
              }
            })();
          </script>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php /**PATH /var/www/html/resources/views/legacy/homepage-recommended-property.blade.php ENDPATH**/ ?>