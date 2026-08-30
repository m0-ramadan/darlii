<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="agents" class="flat-section flat-agents" style="background-color: {{ $attributes['background_color'] ?? 'transparent' }};">
    <div class="container">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
            <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
        </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3">
            @foreach ($agents as $agent)
                <div class="box col mb-4">
                    <div class="box-agent style-card hover-img wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
                        <div class="box-img img-style">
                            <img width="450" height="450" src="{{ $agent['image'] }}" data-bb-lazy="true" loading="lazy" alt="{{ $agent['name'] }}">
                            @if ($agent['facebook'] || $agent['instagram'] || $agent['linkedin'])
                                <ul class="agent-social">
                                    @if ($agent['facebook'])<li><a href="{{ $agent['facebook'] }}" target="_blank" rel="noopener noreferrer"><i class="icon icon-facebook"></i></a></li>@endif
                                    @if ($agent['instagram'])<li><a href="{{ $agent['instagram'] }}" target="_blank" rel="noopener noreferrer"><i class="icon icon-instagram"></i></a></li>@endif
                                    @if ($agent['linkedin'])<li><a href="{{ $agent['linkedin'] }}" target="_blank" rel="noopener noreferrer"><i class="icon icon-linkedin"></i></a></li>@endif
                                </ul>
                            @endif
                        </div>
                        <div class="content">
                            <div class="agent-card-heading">
                                <h3 class="h6 agent-card-name"><a href="{{ $agent['url'] }}" class="link">{{ $agent['name'] }}</a> @if ($agent['verified'])<span class="verified-badge verified-badge-sm" title="Verified Agent">✓</span>@endif</h3>
                                @if ($agent['email'])<a href="mailto:{{ $agent['email'] }}" class="agent-card-action" aria-label="Email {{ $agent['name'] }}"><i class="icon icon-mail"></i></a>@endif
                            </div>
                            <ul class="list-info">
                                @if ($agent['phone'])<li><a href="tel:{{ $agent['phone'] }}"><i class="icon icon-phone"></i>{{ $agent['phone'] }}</a></li>@endif
                                @if ($agent['email'])<li><a href="mailto:{{ $agent['email'] }}"><i class="icon icon-mail"></i>{{ $agent['email'] }}</a></li>@endif
                                @if ($agent['whatsapp'])<li><a href="https://wa.me/{{ preg_replace('/\D+/', '', $agent['whatsapp']) }}" target="_blank" rel="noopener noreferrer"><i class="icon icon-phone2"></i>WhatsApp</a></li>@endif
                            </ul>
                        </div>
                        <div class="agent-card-footer">
                            <span class="agent-card-properties"><i class="icon icon-home"></i>{{ $agent['properties_count'] }} {{ $locale === 'ar' ? 'عقار' : 'Properties' }}</span>
                            <a href="{{ $agent['url'] }}" class="tf-btn agent-card-view">{{ $locale === 'ar' ? 'عرض' : 'View' }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
