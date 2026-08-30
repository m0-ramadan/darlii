<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="location" class="flat-section-v3 flat-location" style="background-color: {{ $attributes['background_color'] ?? '#f7f7f7' }};">
    <div class="container-full">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
            <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
        </div>
        <div class="wow fadeInUpSmall" data-wow-delay=".4s" data-wow-duration="2000ms">
            <div class="swiper tf-sw-location overlay" data-preview-lg="4.1" data-preview-md="3" data-preview-sm="2" data-space="30" data-centered="true" data-autoplay="true" data-autoplay-speed="5000" data-loop="true">
                <div class="swiper-wrapper">
                    @foreach ($locations as $location)
                        <div class="swiper-slide">
                            <a href="{{ $location['url'] }}" class="box-location" data-governorate="{{ $location['key'] }}">
                                <div class="image"><img src="{{ $location['image'] }}" data-bb-lazy="true" width="400" height="560" loading="lazy" alt="{{ $location['name'] }}"></div>
                                <div class="content"><h3 class="title">{{ $location['name'] }}</h3></div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="box-navigation">
                    <div class="navigation swiper-nav-next nav-next-location"><i class="icon icon-left"></i></div>
                    <div class="navigation swiper-nav-prev nav-prev-location"><i class="icon icon-right"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>
