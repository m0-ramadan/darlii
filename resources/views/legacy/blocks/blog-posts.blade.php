<section data-shortcode-id="{{ $shortcodeId }}" data-shortcode-name="blog-posts" class="flat-section-v3 flat-latest-new">
    <div class="container">
        <div class="box-title text-center wow fadeIn" data-wow-delay=".2s" data-wow-duration="2000ms">
            <div class="text-subtitle text-primary">{{ $attributes['subtitle'] ?? '' }}</div>
            <h2 class="section-title mt-4">{{ $attributes['title'] ?? '' }}</h2>
        </div>
        <div class="row">
            @foreach ($posts as $post)
                <div class="box col-lg-4 col-md-6">
                    <div class="flat-blog-item hover-img">
                        <a class="img-style" href="{{ $post['url'] }}"><img width="400" height="260" src="{{ $post['image'] }}" data-bb-lazy="true" loading="lazy" alt="{{ $post['title'] }}"><span class="date-post">{{ $post['date'] }}</span></a>
                        <div class="content-box">
                            <div class="post-author"><span class="fw-6">{{ $post['author'] }}</span></div>
                            <h3 class="title h4"><a href="{{ $post['url'] }}" class="w-100 text-truncate">{{ $post['title'] }}</a></h3>
                            <p class="description">{{ $post['excerpt'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
