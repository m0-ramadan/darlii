<!-- =========================================================================
   5. OUR PARTNERS / AGENTS (تعرف على وكلائنا)
   ========================================================================= -->
<section class="darli-partners-section">
  <div class="darli-container" style="text-align: center;">
    <span class="darli-section-tag"><?php echo e($sectionTag ?? '— شركاؤنا المميزون —'); ?></span>
    <h2 class="darli-section-title"><?php echo e($sectionTitle ?? 'تعرف على وكلائنا'); ?></h2>
    <p class="darli-section-subtitle"><?php echo e($sectionSubtitle ?? 'تعاون مع نخبة من شركات الوساطة العقارية الموثوقة في مختلف مناطق المملكة'); ?></p>

    <div class="darli-partners-slider-wrapper">
      <button class="darli-partners-nav-arrow darli-partners-prev" aria-label="السابق"><i
          data-lucide="chevron-right" style="width: 20px; height: 20px;"></i></button>
      <button class="darli-partners-nav-arrow darli-partners-next" aria-label="التالي"><i data-lucide="chevron-left"
          style="width: 20px; height: 20px;"></i></button>

      <div class="darli-partners-grid">
        <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="darli-partner-card"
             onclick="if (!event.target.closest('.darli-partner-contacts')) window.location.href='<?php echo e($partner['url']); ?>'"
             style="cursor: pointer;"
             title="عرض تفاصيل <?php echo e($partner['name']); ?>">
          <div class="darli-partner-logo">
            <?php if(!empty($partner['custom_logo'])): ?>
              <img src="<?php echo e($partner['custom_logo']); ?>" alt="<?php echo e($partner['name']); ?>" style="max-width: 46px; max-height: 46px; object-fit: contain;">
            <?php else: ?>
              <i data-lucide="<?php echo e($partner['icon']); ?>" style="width: 42px; height: 42px; color: <?php echo e($partner['icon_color']); ?>;"></i>
            <?php endif; ?>
          </div>
          <h4 class="darli-partner-name">
            <a href="<?php echo e($partner['url']); ?>" style="color: inherit; text-decoration: none;">
              <?php echo e($partner['name']); ?>

            </a>
          </h4>
          <span class="darli-partner-type"><?php echo e($partner['job_title']); ?></span>
          <div class="darli-partner-contacts" onclick="event.stopPropagation();">
            <?php if(!empty($partner['phone'])): ?>
              <a href="tel:<?php echo e($partner['phone']); ?>" class="darli-partner-icon-btn" aria-label="اتصال" title="اتصال: <?php echo e($partner['phone']); ?>">
                <i data-lucide="phone" style="width: 14px; height: 14px;"></i>
              </a>
            <?php endif; ?>
            <?php if(!empty($partner['whatsapp'])): ?>
              <a href="https://wa.me/<?php echo e(preg_replace('/\D/', '', $partner['whatsapp'])); ?>" class="darli-partner-icon-btn" target="_blank" rel="noopener" aria-label="مراسلة واتساب" title="واتساب">
                <i data-lucide="message-circle" style="width: 14px; height: 14px;"></i>
              </a>
            <?php endif; ?>
            <?php if(!empty($partner['email'])): ?>
              <a href="mailto:<?php echo e($partner['email']); ?>" class="darli-partner-icon-btn" aria-label="بريد إلكتروني" title="بريد إلكتروني: <?php echo e($partner['email']); ?>">
                <i data-lucide="mail" style="width: 14px; height: 14px;"></i>
              </a>
            <?php endif; ?>
          </div>
          <div class="darli-partner-city">
            <i data-lucide="map-pin" style="width: 13px; height: 13px; color: var(--darli-gold);"></i>
            <span><?php echo e($partner['city']); ?></span>
          </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      <div class="darli-slider-dots">
        <span class="darli-dot active"></span>
        <span class="darli-dot"></span>
        <span class="darli-dot"></span>
        <span class="darli-dot"></span>
      </div>
    </div>
  </div>
</section>

<?php /**PATH /var/www/html/resources/views/legacy/homepage-partners.blade.php ENDPATH**/ ?>