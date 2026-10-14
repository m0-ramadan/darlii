<!-- =========================================================================
   5. OUR PARTNERS / AGENTS (تعرف على وكلائنا)
   ========================================================================= -->
<section class="darli-partners-section">
  <div class="darli-container" style="text-align: center;">
    <span class="darli-section-tag">{{ $sectionTag ?? '— شركاؤنا المميزون —' }}</span>
    <h2 class="darli-section-title">{{ $sectionTitle ?? 'تعرف على وكلائنا' }}</h2>
    <p class="darli-section-subtitle">{{ $sectionSubtitle ?? 'تعاون مع نخبة من شركات الوساطة العقارية الموثوقة في مختلف مناطق المملكة' }}</p>

    <div class="darli-partners-slider-wrapper">
      <button class="darli-partners-nav-arrow darli-partners-prev" aria-label="السابق"><i
          data-lucide="chevron-right" style="width: 20px; height: 20px;"></i></button>
      <button class="darli-partners-nav-arrow darli-partners-next" aria-label="التالي"><i data-lucide="chevron-left"
          style="width: 20px; height: 20px;"></i></button>

      <div class="darli-partners-grid">
        @foreach ($partners as $partner)
        <div class="darli-partner-card"
             onclick="if (!event.target.closest('.darli-partner-contacts')) window.location.href='{{ $partner['url'] }}'"
             style="cursor: pointer;"
             title="عرض تفاصيل {{ $partner['name'] }}">
          <div class="darli-partner-logo">
            @if (!empty($partner['custom_logo']))
              <img src="{{ $partner['custom_logo'] }}" alt="{{ $partner['name'] }}" style="max-width: 46px; max-height: 46px; object-fit: contain;">
            @else
              <i data-lucide="{{ $partner['icon'] }}" style="width: 42px; height: 42px; color: {{ $partner['icon_color'] }};"></i>
            @endif
          </div>
          <h4 class="darli-partner-name">
            <a href="{{ $partner['url'] }}" style="color: inherit; text-decoration: none;">
              {{ $partner['name'] }}
            </a>
          </h4>
          <span class="darli-partner-type">{{ $partner['job_title'] }}</span>
          <div class="darli-partner-contacts" onclick="event.stopPropagation();">
            @if (!empty($partner['phone']))
              <a href="tel:{{ $partner['phone'] }}" class="darli-partner-icon-btn" aria-label="اتصال" title="اتصال: {{ $partner['phone'] }}">
                <i data-lucide="phone" style="width: 14px; height: 14px;"></i>
              </a>
            @endif
            @if (!empty($partner['whatsapp']))
              <a href="https://wa.me/{{ preg_replace('/\D/', '', $partner['whatsapp']) }}" class="darli-partner-icon-btn" target="_blank" rel="noopener" aria-label="مراسلة واتساب" title="واتساب">
                <i data-lucide="message-circle" style="width: 14px; height: 14px;"></i>
              </a>
            @endif
            @if (!empty($partner['email']))
              <a href="mailto:{{ $partner['email'] }}" class="darli-partner-icon-btn" aria-label="بريد إلكتروني" title="بريد إلكتروني: {{ $partner['email'] }}">
                <i data-lucide="mail" style="width: 14px; height: 14px;"></i>
              </a>
            @endif
          </div>
          <div class="darli-partner-city">
            <i data-lucide="map-pin" style="width: 13px; height: 13px; color: var(--darli-gold);"></i>
            <span>{{ $partner['city'] }}</span>
          </div>
        </div>
        @endforeach
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

