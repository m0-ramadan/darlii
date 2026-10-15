<section class="darli-featured-section">
  <div class="darli-container">
    <div class="darli-section-header-flex">
      <div>
        <span class="darli-section-tag">— {{ $locale === 'ar' ? 'العقارات المميزة' : 'Featured Properties' }} —</span>
        <h2 class="darli-section-title">{{ $locale === 'ar' ? 'اكتشف أفضل العقارات في دارلي' : 'Discover the Best Properties in Darlii' }}<br>{{ $locale === 'ar' ? 'لمنازل أحلامك' : 'For Your Dream Homes' }}</h2>
      </div>
      <div>
        <a href="{{ route('properties.index') }}" class="darli-view-all-link">
          <span>{{ $locale === 'ar' ? 'عرض جميع العقارات' : 'View All Properties' }}</span>
          <i data-lucide="{{ $locale === 'ar' ? 'arrow-left' : 'arrow-right' }}" style="width: 16px; height: 16px;"></i>
        </a>
      </div>
    </div>

    <div class="darli-properties-grid">
      @forelse ($properties as $prop)
        @php
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
        @endphp
        <div class="darli-property-card" onclick="if (!event.target.closest('button') && !event.target.closest('a')) window.location.href='{{ $url }}'" style="cursor: pointer;">
          <div class="darli-card-image-box">
            <a href="{{ $url }}" style="display: block; width: 100%; height: 100%;">
              <img src="{{ $image }}" alt="{{ $tr?->name }}" class="darli-card-image"
                loading="lazy" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
            </a>
            <div class="d-flex gap-2" style="position: absolute; top: 12px; right: 12px; z-index: 2;">
              @if ($isSold)
                <span class="darli-card-badge" style="position: static; background: #10b981; color: #fff;">
                  {{ $locale === 'ar' ? 'تم البيع' : 'Sold' }}
                </span>
              @endif
              <span class="darli-card-badge {{ $prop->is_featured ? 'badge-gold' : '' }}" style="position: static;">
                {{ $isRent ? ($locale === 'ar' ? 'للإيجار' : 'For Rent') : ($locale === 'ar' ? 'للبيع' : 'For Sale') }}
              </span>
            </div>
            <button class="darli-card-heart" data-property-id="{{ $prop->id }}" aria-label="{{ $locale === 'ar' ? 'أضف للمفضلة' : 'Add to favorites' }}" onclick="event.stopPropagation();">
              <i data-lucide="heart" style="width: 18px; height: 18px;"></i>
            </button>
          </div>
          <div class="darli-card-body">
            <a href="{{ $url }}" class="darli-card-title line-clamp-1" title="{{ $tr?->name }}">
              {{ $tr?->name }}
            </a>
            <div class="darli-card-location">
              <i data-lucide="map-pin" style="width: 14px; height: 14px; color: var(--darli-gold); flex-shrink: 0;"></i>
              <span class="line-clamp-1">{{ $locationText }}</span>
            </div>
            <div class="darli-card-features">
              <div class="darli-feature-item">
                <i data-lucide="bed" style="width: 16px; height: 16px; color: var(--darli-gold);"></i>
                <span>{{ $prop->bedrooms ? $prop->bedrooms . ' ' . ($locale === 'ar' ? 'غرف' : 'Beds') : ($locale === 'ar' ? 'مساحة خاصة' : 'Private') }}</span>
              </div>
              <div class="darli-feature-item">
                <i data-lucide="bath" style="width: 16px; height: 16px; color: var(--darli-gold);"></i>
                <span>{{ $prop->bathrooms ? $prop->bathrooms . ' ' . ($locale === 'ar' ? 'حمام' : 'Baths') : ($locale === 'ar' ? 'خدمات' : 'Amenities') }}</span>
              </div>
              <div class="darli-feature-item">
                <i data-lucide="maximize" style="width: 16px; height: 16px; color: var(--darli-gold);"></i>
                <span>{{ $prop->area ? (int)$prop->area . ' م²' : ($locale === 'ar' ? 'متعدد' : 'Multi') }}</span>
              </div>
            </div>
            <div class="darli-card-footer">
              <div class="darli-card-price">{{ $priceDisplay }}</div>
              <a href="{{ $url }}" class="darli-btn-details">
                <span>{{ $locale === 'ar' ? 'عرض التفاصيل' : 'Details' }}</span>
                <i data-lucide="{{ $locale === 'ar' ? 'arrow-left' : 'arrow-right' }}" style="width: 14px; height: 14px;"></i>
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center py-5">
          <p style="color: var(--darli-muted);">لا توجد عقارات منشورة حالياً</p>
        </div>
      @endforelse
    </div>
  </div>
</section>
