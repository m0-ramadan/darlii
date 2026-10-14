<section class="darli-blog-section">
    <div class="darli-container">
        <span class="darli-section-tag">— {{ $locale === 'ar' ? 'نبض العقارات' : 'Real Estate Pulse' }} —</span>
        <h2 class="darli-section-title">{{ $locale === 'ar' ? 'أحدث المقالات' : 'Latest Articles' }}</h2>
        <p class="darli-section-subtitle">
            {{ $locale === 'ar' ? 'تابع آخر أخبار ونصائح السوق العقاري من خبرائنا' : 'Follow the latest real estate news and advice from our experts' }}
        </p>

        <div class="darli-blog-grid">
            @foreach ($posts as $post)
                <article class="darli-blog-card">
                    <a href="{{ $post['url'] }}" class="darli-blog-img-wrapper" style="display: block;" aria-label="{{ $post['title'] }}">
                        <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="darli-blog-img"
                            loading="lazy" onerror="this.onerror=null;this.src='/themes/homzen/images/darli-placeholder.png';">
                        @if ($post['date'])
                            <span class="darli-blog-date-badge">{{ $post['date'] }}</span>
                        @endif
                    </a>
                    <div class="darli-blog-body">
                        <a href="{{ $post['url'] }}" class="darli-blog-title">{{ $post['title'] }}</a>
                        <p class="darli-blog-excerpt">{{ $post['excerpt'] }}</p>
                        <a href="{{ $post['url'] }}" class="darli-blog-link">
                            <span>{{ $locale === 'ar' ? 'اقرأ المزيد' : 'Read more' }}</span>
                            <i data-lucide="{{ $locale === 'ar' ? 'arrow-left' : 'arrow-right' }}" style="width: 14px; height: 14px;"></i>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
