<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="testimonials" class="flat-section flat-testimonial-v4">
    <div class="container">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
            <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
        </div>
        <div class="swiper tf-sw-testimonial" data-preview-lg="2" data-preview-md="2" data-preview-sm="1" data-space="30" data-autoplay="true" data-autoplay-speed="5000" data-loop="true">
            <div class="swiper-wrapper">
                @foreach ($testimonials as $testimonial)
                    <div class="swiper-slide">
                        <div class="box-tes-item style-2">
                            <ul class="list-star">@for ($star = 0; $star < $testimonial['rating']; $star++)<li class="icon icon-star"></li>@endfor</ul>
                            <p class="note body-1">{{ $testimonial['content'] }}</p>
                            <div class="box-avt d-flex align-items-center gap-12">
                                <div class="avatar avt-60 round"><img width="60" height="60" src="{{ $testimonial['image'] }}" data-bb-lazy="true" loading="lazy" alt="{{ $testimonial['name'] }}"></div>
                                <div class="info"><div class="h7 fw-7">{{ $testimonial['name'] }}</div><p class="text-variant-1 mt-4">{{ $testimonial['position'] }}</p></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="sw-pagination sw-pagination-testimonial"></div>
        </div>
    </div>
</section>
