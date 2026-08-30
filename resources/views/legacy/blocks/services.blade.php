@if ($style === '5')
<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="services" class="flat-benefit-v2">
    <div class="container">
        <div class="row wrap-benefit-v2">
            <div class="col-lg-4 wow fadeIn">
                <div class="box-left">
                    <div class="box-title">
                        <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
                        <h2 class="section-title mt-4 text-white">{{ $attributes['title'] ?? '' }}</h2>
                        <p class="description text-white body-2">{{ $attributes['description'] ?? '' }}</p>
                    </div>
                    <div class="box-navigation">
                        <div class="navigation swiper-nav-next nav-next-benefit"><i class="icon icon-left"></i></div>
                        <div class="navigation swiper-nav-prev nav-prev-benefit"><i class="icon icon-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 wow fadeIn">
                <div class="swiper tf-sw-benefit">
                    <div class="swiper-wrapper">
                        @foreach ($services as $service)
                            <div class="swiper-slide">
                                <div class="box-right">
                                    <div class="box-benefit style-1">
                                        <div class="icon-box"><i class="icon {{ $service['icon'] }}"></i></div>
                                        <div class="content"><h3 class="title link">{{ $service['title'] }}</h3><p class="description">{{ $service['description'] }}</p></div>
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
<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="services" class="flat-section flat-service-v4" style="background-color: {{ $attributes['background_color'] ?? 'transparent' }};">
    <div class="container">
        <div class="wrap-service-v4 wow fadeInUpSmall" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="inner-service-left">
                <div class="img-service"><img width="551" height="654" src="{{ $backgroundImage ?: '/storage/general/placeholder.png' }}" data-bb-lazy="true" loading="lazy" alt="{{ $attributes['title'] ?? '' }}"></div>
            </div>
            <div class="inner-service-right">
                <div class="box-title">
                    <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
                    <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
                    <p class="desc">{{ $attributes['description'] ?? '' }}</p>
                </div>
                <ul class="list-service">
                    @foreach ($services as $service)
                        <li class="box-service hover-btn-view style-4">
                            <div class="icon-box">
                                @if ($service['image'])
                                    <img width="80" height="80" src="{{ $service['image'] }}" data-bb-lazy="false" class="icon" loading="lazy" alt="{{ $service['title'] }}">
                                @else
                                    <i class="icon {{ $service['icon'] }}"></i>
                                @endif
                            </div>
                            <div class="content">
                                <h3 class="title">{{ $service['title'] }}</h3>
                                <p class="description">{{ $service['description'] }}</p>
                                <a href="{{ $service['button_url'] }}" class="btn-view style-1"><span class="text">{{ $service['button_label'] }}</span><i class="icon icon-right"></i></a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
@endif
