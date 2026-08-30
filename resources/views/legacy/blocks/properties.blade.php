@if ($style === '6')
<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="properties" class="flat-property-v3">
    <div class="container-full">
        <div class="wrap-property-v2">
            <div class="box-inner-left img-animation wow">
                <img width="960" height="680" src="{{ $backgroundImage ?: '/storage/general/placeholder.png' }}" data-bb-lazy="true" loading="lazy" alt="{{ $attributes['title'] ?? '' }}">
            </div>
            <div class="box-inner-right">
                <div class="swiper tf-sw-property">
                    <div class="swiper-wrapper">
                        @foreach ($properties as $property)
                            <div class="swiper-slide">
                                <div class="content-property">
                                    <div class="box-title wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
                                        <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
                                        <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
                                    </div>
                                    <ul class="box-tag">
                                        @if ($property['featured'])
                                            <span class="flag-tag success">{{ $locale === 'ar' ? 'مميز' : 'Featured' }}</span>
                                        @endif
                                        <span class="flag-tag primary status-badge-{{ $property['purpose_class'] }}">{{ $property['purpose'] }}</span>
                                    </ul>
                                    <div class="box-name">
                                        <h3 class="title"><a href="{{ $property['url'] }}" class="link">{{ $property['name'] }}</a></h3>
                                        <p class="location"><span class="icon icon-mapPin"></span>{{ $property['address'] }}</p>
                                    </div>
                                    <ul class="list-info">
                                        <li class="item"><i class="icon icon-bed"></i><span>{{ $property['bedrooms'] }}</span></li>
                                        <li class="item"><i class="icon icon-bathtub"></i><span>{{ $property['bathrooms'] }}</span></li>
                                        <li class="item"><i class="icon icon-ruler"></i><span>{{ $property['area'] }}</span></li>
                                    </ul>
                                    <div class="box-avatar d-flex gap-12 align-items-center">
                                        <div class="avatar avt-60 round"><img width="60" height="60" src="{{ $property['owner_avatar'] }}" data-bb-lazy="true" loading="lazy" alt="{{ $property['owner_name'] }}"></div>
                                        <div class="info">
                                            <p class="body-2 text-variant-1">{{ $locale === 'ar' ? 'الوكيل' : 'Agent' }}</p>
                                            <div class="mt-4 h7 fw-7">{{ $property['owner_name'] }} @if ($property['owner_verified'])<span class="verified-badge verified-badge-sm" title="Verified Agent">✓</span>@endif</div>
                                        </div>
                                    </div>
                                    <div class="pricing-property">
                                        <div class="d-flex align-items-center"><span class="h5">{{ $property['price'] }}</span></div>
                                        <div class="d-flex gap-12">
                                            <button type="button" class="box-icon w-52{{ $property['favorite'] ? ' active' : '' }}" data-type="property" data-bb-toggle="add-to-wishlist" data-id="{{ $property['id'] }}" aria-label="{{ $property['favorite'] ? 'Remove from wishlist' : 'Add to wishlist' }}" aria-pressed="{{ $property['favorite'] ? 'true' : 'false' }}">
                                                <i class="icon icon-heart"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@else
<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="properties" class="flat-section-v5 bg-surface flat-recommended flat-recommended-v2">
    <div class="container">
        <div class="box-title text-center wow fadeIn style-2" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
            <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
        </div>
        <div class="row wow fadeInUpSmall" data-wow-delay=".2s" data-wow-duration="2000ms">
            @foreach ($properties as $property)
                <div class="col-xl-4 col-md-6">
                    <div class="property-item homeya-box" data-lat="{{ $property['latitude'] }}" data-lng="{{ $property['longitude'] }}">
                        <div class="archive-top">
                            <a href="{{ $property['url'] }}" class="images-group">
                                <div class="images-style"><img width="400" height="260" src="{{ $property['image'] }}" data-bb-lazy="true" loading="lazy" alt="{{ $property['name'] }}"></div>
                                <div class="top">
                                    <div class="d-flex gap-8">
                                        @if ($property['featured'])<span class="flag-tag success">{{ $locale === 'ar' ? 'مميز' : 'Featured' }}</span>@endif
                                        <span class="flag-tag primary status-badge-{{ $property['purpose_class'] }}">{{ $property['purpose'] }}</span>
                                    </div>
                                    <div class="d-flex gap-4">
                                        <button type="button" class="box-icon w-32{{ $property['favorite'] ? ' active' : '' }}" data-type="property" data-bb-toggle="add-to-wishlist" data-id="{{ $property['id'] }}" aria-label="{{ $property['favorite'] ? 'Remove from wishlist' : 'Add to wishlist' }}" aria-pressed="{{ $property['favorite'] ? 'true' : 'false' }}">
                                            <i class="icon icon-heart"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="bottom"><span class="flag-tag style-2">{{ $property['category'] }}</span></div>
                            </a>
                            <div class="content">
                                <div class="text-capitalize h7 fw-7"><a href="{{ $property['url'] }}" class="link line-clamp-1" title="{{ $property['name'] }}">{{ $property['name'] }}</a></div>
                                <div class="desc"><i class="icon icon-mapPin"></i><p class="line-clamp-1">{{ $property['address'] }}</p></div>
                                <ul class="meta-list">
                                    <li class="item"><i class="icon icon-bed"></i><span>{{ $property['bedrooms'] }}</span></li>
                                    <li class="item"><i class="icon icon-bathtub"></i><span>{{ $property['bathrooms'] }}</span></li>
                                    <li class="item"><i class="icon icon-ruler"></i><span>{{ $property['area'] }}</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="archive-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-8 align-items-center">
                                <div class="avatar avt-40 round"><img width="40" height="40" src="{{ $property['owner_avatar'] }}" data-bb-lazy="true" loading="lazy" alt="{{ $property['owner_name'] }}"></div>
                                <span>{{ $property['owner_name'] }} @if ($property['owner_verified'])<span class="verified-badge verified-badge-sm" title="Verified Agent">✓</span>@endif</span>
                            </div>
                            <div class="d-flex align-items-center"><span class="h6">{{ $property['price'] }}</span></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if (! empty($attributes['button_label']))
            <div class="text-center mt-5"><a class="tf-btn primary size-1" href="/{{ $locale }}/properties">{{ $attributes['button_label'] }}</a></div>
        @endif
    </div>
</section>
@endif
