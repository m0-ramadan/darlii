<?php if($testimonials->isNotEmpty()): ?>
<section class="darli-testimonials-section">
  <div class="darli-container">
    <span class="darli-section-tag">— <?php echo e($locale === 'ar' ? 'آراء عملائنا' : 'Client Testimonials'); ?> —</span>
    <h2 class="darli-section-title"><?php echo e($locale === 'ar' ? 'ماذا يقول عملاؤنا؟' : 'What Our Clients Say'); ?></h2>
    <p class="darli-section-subtitle"><?php echo e($locale === 'ar' ? 'تجارب حقيقية من عملائنا في رحلتهم العقارية' : 'Real experiences from our clients in their real estate journey'); ?></p>

    <div class="darli-testimonials-grid">
      <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $name = $t->translation($locale)?->name ?: ($t->name ?: ($locale === 'ar' ? 'عميل دارلي' : 'Darlii Client'));
          $role = $t->translation($locale)?->role ?: ($t->role ?: ($locale === 'ar' ? 'عميل موثق' : 'Verified Client'));
          $content = $t->translation($locale)?->content ?: $t->content;
          $stars = str_repeat('★', max(1, min(5, (int)($t->rating ?: 5))));
          $avatar = $t->image_path ? '/' . ltrim($t->image_path, '/') : '/storage/avatars/' . ($loop->iteration) . '.jpg';
        ?>
        <div class="darli-testimonial-card">
          <div>
            <div class="darli-stars-row" style="color: var(--darli-gold); letter-spacing: 2px;"><?php echo e($stars); ?></div>
            <p class="darli-testi-quote">"<?php echo e($content); ?>"</p>
          </div>
          <div class="darli-testi-watermark">”</div>
          <div class="darli-testi-user">
            <img src="<?php echo e($avatar); ?>" alt="<?php echo e($name); ?>" class="darli-testi-avatar"
              loading="lazy" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
            <div>
              <h5 class="darli-testi-name"><?php echo e($name); ?></h5>
              <span class="darli-testi-role"><?php echo e($role); ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
</section>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/legacy/homepage-testimonials.blade.php ENDPATH**/ ?>