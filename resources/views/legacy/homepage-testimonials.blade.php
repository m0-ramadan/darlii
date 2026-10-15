@if ($testimonials->isNotEmpty())
<section class="darli-testimonials-section">
  <div class="darli-container">
    <span class="darli-section-tag">— {{ $locale === 'ar' ? 'آراء عملائنا' : 'Client Testimonials' }} —</span>
    <h2 class="darli-section-title">{{ $locale === 'ar' ? 'ماذا يقول عملاؤنا؟' : 'What Our Clients Say' }}</h2>
    <p class="darli-section-subtitle">{{ $locale === 'ar' ? 'تجارب حقيقية من عملائنا في رحلتهم العقارية' : 'Real experiences from our clients in their real estate journey' }}</p>

    <div class="darli-testimonials-grid">
      @foreach ($testimonials as $t)
        @php
          $name = $t->translation($locale)?->name ?: ($t->name ?: ($locale === 'ar' ? 'عميل دارلي' : 'Darlii Client'));
          $role = $t->translation($locale)?->role ?: ($t->role ?: ($locale === 'ar' ? 'عميل موثق' : 'Verified Client'));
          $content = $t->translation($locale)?->content ?: $t->content;
          $stars = str_repeat('★', max(1, min(5, (int)($t->rating ?: 5))));
          $avatar = $t->image_path ? '/' . ltrim($t->image_path, '/') : '/storage/avatars/' . ($loop->iteration) . '.jpg';
        @endphp
        <div class="darli-testimonial-card">
          <div>
            <div class="darli-stars-row" style="color: var(--darli-gold); letter-spacing: 2px;">{{ $stars }}</div>
            <p class="darli-testi-quote">"{{ $content }}"</p>
          </div>
          <div class="darli-testi-watermark">”</div>
          <div class="darli-testi-user">
            <img src="{{ $avatar }}" alt="{{ $name }}" class="darli-testi-avatar"
              loading="lazy" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
            <div>
              <h5 class="darli-testi-name">{{ $name }}</h5>
              <span class="darli-testi-role">{{ $role }}</span>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

