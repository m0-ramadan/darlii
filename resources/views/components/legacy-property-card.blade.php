@props(['property', 'locale' => null])

@php
    $locale = $locale ?: app()->getLocale();
    $tr = $property->translation($locale);
    $slug = $tr?->slug;
    $url = $slug ? route('properties.show', ['locale' => $locale, 'slug' => $slug]) : '#';
    $image = $property->primaryImageUrl($locale);
    $isContactPrice = $property->isPriceOnContact();
    $isNegotiablePrice = $property->isNegotiable();
    $price = $isContactPrice
        ? ($locale === 'ar' ? 'السعر عند التواصل' : 'Price on request')
        : money_eg($property->price);
    $isRent = $property->purpose === 'rent';
@endphp

{{--
    نفس بنية كارت العقار في تصميم هومزن الأصلي
    (.property-item.homeya-box) لكن ببيانات قاعدة البيانات.
--}}
<div class="property-item homeya-box" data-lat="{{ $property->latitude }}" data-lng="{{ $property->longitude }}">
    <div class="archive-top">
        <a href="{{ $url }}" class="images-group">
            <div class="images-style">
                <img src="{{ $image }}" alt="{{ $tr?->name }}"
                    onerror="this.onerror=null;this.src='{{ $property->placeholderUrl($locale) }}';">
            </div>
            <div class="top">
                <div class="d-flex gap-8">
                    @if ($property->isSold())
                        <span class="flag-tag success status-badge-sold" style="background-color: #10b981 !important; color: #fff !important; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 12px; box-shadow: 0 2px 6px rgba(16,185,129,0.3);">
                            {{ $locale === 'ar' ? 'تم البيع' : 'Sold' }}
                        </span>
                    @endif
                    @if ($property->is_featured)
                        <span class="flag-tag success">{{ $locale === 'ar' ? 'مميز' : 'Featured' }}</span>
                    @endif
                    <span class="flag-tag primary status-badge-renting">
                        {{ $isRent ? ($locale === 'ar' ? 'للإيجار' : 'For rent') : ($locale === 'ar' ? 'للبيع' : 'For sale') }}
                    </span>
                </div>
            </div>
            @if ($property->category)
                <div class="bottom">
                    <span class="flag-tag style-2">{{ $property->category->translation($locale)?->name }}</span>
                </div>
            @endif
        </a>

        <div class="content">
            <div class="text-capitalize h7 fw-7">
                <a href="{{ $url }}" class="link line-clamp-1" title="{{ $tr?->name }}">{{ $tr?->name }}</a>
            </div>
            <div class="desc">
                <i class="icon icon-mapPin"></i>
                <p class="line-clamp-1">{{ $tr?->address_text ?: ($property->location?->translation($locale)?->name ?? '') }}</p>
            </div>
            <ul class="meta-list">
                <li class="item"><i class="icon icon-bed"></i><span>{{ $property->bedrooms ?? '—' }}</span></li>
                <li class="item"><i class="icon icon-bathtub"></i><span>{{ $property->bathrooms ?? '—' }}</span></li>
                <li class="item"><i class="icon icon-ruler"></i><span>{{ rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.') }} m²</span></li>
            </ul>
        </div>
    </div>

    <div class="archive-bottom d-flex justify-content-between align-items-center">
        <div class="d-flex gap-8 align-items-center">
            <div class="avatar avt-40 round">
                <img src="{{ $property->owner?->avatar_url ?? asset('themes/homzen/images/logo/darli-logo-ar.png') }}"
                    alt="{{ $property->owner?->name }}"
                    onerror="this.onerror=null;this.src='{{ asset('themes/homzen/images/logo/darli-logo-ar.png') }}';">
            </div>
            <span>{{ $property->owner?->name ?? ($locale === 'ar' ? 'فريق دارلي' : 'Darlii team') }}</span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <h6>{{ $price }}{{ ($isRent && ! $isContactPrice) ? ($locale === 'ar' ? ' / شهرياً' : ' / month') : '' }}</h6>
            @if ($isNegotiablePrice)
                <span class="flag-tag success" style="font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                    {{ $locale === 'ar' ? 'قابل للتفاوض' : 'Negotiable' }}
                </span>
            @endif
        </div>
    </div>
</div>
