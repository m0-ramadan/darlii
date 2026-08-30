<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="call-to-action" class="flat-section pt-0 flat-banner">
    <div class="container">
        <div class="wrap-banner bg-surface">
            <div class="box-left">
                <div class="box-title">
                    <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
                    <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
                </div>
                <a href="{{ $buttonUrl }}" class="tf-btn primary size-1">{{ $attributes['button_label'] ?? '' }}</a>
            </div>
            <div class="box-right"><img width="520" height="320" src="{{ $image ?: '/storage/general/placeholder.png' }}" data-bb-lazy="true" loading="lazy" alt="{{ $attributes['title'] ?? '' }}"></div>
        </div>
    </div>
</section>
