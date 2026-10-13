@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $placeholder = $isAr
        ? asset('themes/homzen/images/darli-placeholder-ar.png')
        : asset('themes/homzen/images/darli-placeholder-en.png');

    /** صورة المقال: روابط كاملة كما هي، وإلا من مجلد الميرور /legacy. */
    $articleImage = function (?string $path) use ($placeholder): string {
        if (blank($path)) {
            return $placeholder;
        }

        return str_starts_with($path, 'http') ? $path : asset('legacy/' . ltrim($path, '/'));
    };

    /** التاريخ: عربي بصيغة مقروءة، وإنجليزي بصيغة مقروءة. */
    $fmtDate = function ($value) use ($isAr): string {
        if (blank($value)) {
            return '—';
        }

        try {
            $date = $value instanceof \DateTimeInterface
                ? \Illuminate\Support\Carbon::instance($value)
                : \Illuminate\Support\Carbon::parse($value);
        } catch (\Throwable $e) {
            return (string) $value;
        }

        return $isAr
            ? $date->locale('ar')->translatedFormat('j F Y')
            : $date->format('j M Y');
    };

    /** تصنيف المقال بناءً على المعرف والمحتوى لتطابق التصميم الأصلي */
    $getCategory = function ($post, bool $ar): string {
        $id = $post->id;
        if ($id == 24 || in_array($id, [16, 13, 12, 10, 7])) {
            return $ar ? 'السوق العقاري' : 'Real Estate Market';
        }
        if ($id == 23 || in_array($id, [21, 17, 15, 14, 11, 9, 1])) {
            return $ar ? 'الاستثمار' : 'Investment';
        }
        return $ar ? 'نصائح الشراء' : 'Buying Advice';
    };
@endphp

@section('title', ($isAr ? 'المدونة' : 'Blog') . ($isAr ? ' | دارلي' : ' | Darlii'))

@section('content')
    <!-- ==================== HERO BANNER ==================== -->
    <section class="darli-blog-hero">
        <div class="darli-blog-hero__villas" aria-hidden="true">
            <img src="{{ asset('themes/homzen/images/darli-blog-villas.png') }}" alt="" class="darli-blog-hero__villas-img">
        </div>
        <div class="darli-blog-hero__skyscrapers" aria-hidden="true">
            <img src="{{ asset('themes/homzen/images/darli-blog-skyscrapers.png') }}" alt="" class="darli-blog-hero__skyscrapers-img">
        </div>
        <div class="container position-relative darli-blog-hero__container">
            <div class="darli-blog-hero__content">
                <h1 class="darli-blog-hero__title">{{ $isAr ? 'المدونة' : 'Blog' }}</h1>
                <div class="darli-blog-hero__accent-wrap">
                    <span class="darli-blog-hero__accent-line"></span>
                </div>
                <p class="darli-blog-hero__subtitle">{{ $isAr ? 'مقالات ونصائح عقارية تساعدك في اتخاذ القرار' : 'Real estate articles and tips to help you make decisions' }}</p>
            </div>
        </div>
    </section>

    <!-- ==================== FLOATING FILTER & SEARCH BAR ==================== -->
    <div class="container position-relative">
        <div class="darli-blog-filter-bar">
            <!-- Search input: On right in RTL -->
            <div class="darli-blog-search">
                <input type="text"
                       id="blogSearchInput"
                       class="darli-blog-search__input"
                       placeholder="{{ $isAr ? '... ابحث في المقالات' : 'Search articles...' }}"
                       autocomplete="off"
                       aria-label="{{ $isAr ? 'ابحث في المقالات' : 'Search articles' }}">
                <span class="darli-blog-search__icon" aria-hidden="true">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
            </div>

            <!-- Category pills: On left in RTL -->
            <div class="darli-blog-categories" role="tablist">
                <button type="button" class="darli-blog-cat-btn active" data-category="all">
                    {{ $isAr ? 'الكل' : 'All' }}
                </button>
                <button type="button" class="darli-blog-cat-btn" data-category="{{ $isAr ? 'نصائح الشراء' : 'Buying Advice' }}">
                    {{ $isAr ? 'نصائح الشراء' : 'Buying Advice' }}
                </button>
                <button type="button" class="darli-blog-cat-btn" data-category="{{ $isAr ? 'الاستثمار' : 'Investment' }}">
                    {{ $isAr ? 'الاستثمار' : 'Investment' }}
                </button>
                <button type="button" class="darli-blog-cat-btn" data-category="{{ $isAr ? 'السوق العقاري' : 'Real Estate Market' }}">
                    {{ $isAr ? 'السوق العقاري' : 'Real Estate Market' }}
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== BLOG POSTS SECTION ==================== -->
    <section class="darli-blog-section pb-5">
        <div class="container">
            @if ($posts->isEmpty())
                <div class="darli-panel darli-empty text-center py-5">
                    <h5 class="mb-2">{{ $isAr ? 'لا توجد مقالات منشورة حتى الآن' : 'No articles published yet' }}</h5>
                    <p class="text-1 mb-0">
                        {{ $isAr
                            ? 'نعمل على تجهيز محتوى جديد عن السوق العقاري. عُد إلينا قريباً.'
                            : 'We are preparing new real-estate content. Please check back soon.' }}
                    </p>
                </div>
            @else
                <div class="darli-blog-grid" id="blogGrid">
                    @foreach ($posts as $post)
                        @php
                            $tr = $post->translation($locale);
                            $url = $tr?->slug
                                ? route('blog.show', ['locale' => $locale, 'slug' => $tr->slug])
                                : '#';
                            $image = $articleImage($post->featured_image);
                            $rawExcerpt = trim(strip_tags((string) $tr?->excerpt));
                            $excerpt = \Illuminate\Support\Str::limit($rawExcerpt, 140);
                            $categoryName = $getCategory($post, $isAr);
                            $authorName = $post->author?->name ?: 'Recovered Catalog Owner';
                            $dateFormatted = $fmtDate($post->published_at);
                        @endphp

                        <div class="darli-blog-card"
                             data-category="{{ $categoryName }}"
                             data-title="{{ $tr?->title }}"
                             data-excerpt="{{ $rawExcerpt }}">
                            <!-- Image Wrap -->
                            <div class="darli-blog-card__image-wrap">
                                <a href="{{ $url }}" class="darli-blog-card__image-link" tabindex="-1">
                                    <img src="{{ $image }}" alt="{{ $tr?->title }}" class="darli-blog-card__img"
                                         onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                </a>

                                <!-- Golden Date Badge (Top-Right) -->
                                @if ($post->published_at)
                                    <div class="darli-blog-card__date-badge">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <span>{{ $dateFormatted }}</span>
                                    </div>
                                @endif

                                <!-- Category Badge (Bottom-Right) -->
                                <div class="darli-blog-card__cat-badge">
                                    {{ $categoryName }}
                                </div>
                            </div>

                            <!-- Content Body -->
                            <div class="darli-blog-card__body">
                                <h3 class="darli-blog-card__title">
                                    <a href="{{ $url }}">{{ $tr?->title }}</a>
                                </h3>

                                @if ($excerpt !== '')
                                    <p class="darli-blog-card__excerpt">{{ $excerpt }}</p>
                                @endif

                                <!-- Footer -->
                                <div class="darli-blog-card__footer">
                                    <!-- Meta: Author & Date (Right in RTL) -->
                                    <div class="darli-blog-card__meta">
                                        <div class="darli-blog-card__meta-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            <span>{{ $authorName }}</span>
                                        </div>
                                        <div class="darli-blog-card__meta-item">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                            <span>{{ $dateFormatted }}</span>
                                        </div>
                                    </div>

                                    <!-- Action Button: (Left in RTL) -->
                                    <a href="{{ $url }}" class="darli-blog-card__btn">
                                        <span>{{ $isAr ? 'اقرأ المقال' : 'Read Article' }}</span>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            @if ($isAr)
                                                <line x1="19" y1="12" x2="5" y2="12"></line>
                                                <polyline points="12 19 5 12 12 5"></polyline>
                                            @else
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            @endif
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Empty search state -->
                <div id="blogNoResults" class="darli-panel darli-empty text-center py-5" style="display: none;">
                    <h5 class="mb-2">{{ $isAr ? 'لم نتمكن من العثور على أي مقالات' : 'No articles match your criteria' }}</h5>
                    <p class="text-1 mb-0">
                        {{ $isAr ? 'جرب البحث بكلمات مختلفة أو اختر تصنيفاً آخر.' : 'Try different keywords or select another category.' }}
                    </p>
                </div>

                @if ($posts->hasPages())
                    <div class="darli-pagination mt-4" id="blogPagination">
                        {{ $posts->links('vendor.pagination.darli') }}
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- ==================== STYLES ==================== -->
    <style>
        /* Header navigation underline for active item */
        .main-header .main-menu .navigation li.current > a,
        .main-header .main-menu li.current > a {
            position: relative !important;
            color: #c9933b !important;
        }
        .main-header .main-menu .navigation li.current > a::after,
        .main-header .main-menu li.current > a::after {
            content: "" !important;
            position: absolute !important;
            bottom: -6px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            width: 36px !important;
            height: 3px !important;
            background-color: #c9933b !important;
            border-radius: 2px !important;
            display: block !important;
        }

        /* Hero Banner */
        .darli-blog-hero {
            position: relative;
            background: linear-gradient(180deg, #edf2f7 0%, #f7f9fa 100%);
            padding: 36px 0 52px;
            overflow: hidden;
            min-height: 155px;
        }
        html[data-theme="dark"] .darli-blog-hero {
            background: linear-gradient(180deg, #131d2e 0%, #1a2536 100%);
        }

        .darli-blog-hero__villas {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        .darli-blog-hero__villas-img {
            height: 100%;
            width: auto;
            max-width: 500px;
            object-fit: cover;
            object-position: left center;
        }

        .darli-blog-hero__skyscrapers {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        .darli-blog-hero__skyscrapers-img {
            height: 100%;
            width: auto;
            max-width: 420px;
            object-fit: cover;
            object-position: right center;
        }

        .darli-blog-hero__container {
            z-index: 2;
        }

        .darli-blog-hero__content {
            text-align: right;
            padding-right: 50px;
            max-width: 580px;
            margin-right: 0;
            margin-left: auto;
        }
        [dir="ltr"] .darli-blog-hero__content {
            text-align: left;
            margin-left: 0;
            margin-right: auto;
            padding-right: 0;
            padding-left: 50px;
        }

        .darli-blog-hero__title {
            font-size: 38px;
            font-weight: 800;
            color: #0e1b30;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -0.5px;
            text-align: right;
        }
        [dir="ltr"] .darli-blog-hero__title {
            text-align: left;
        }
        html[data-theme="dark"] .darli-blog-hero__title {
            color: #f8fafc;
        }

        .darli-blog-hero__accent-wrap {
            text-align: right;
            margin: 6px 0 8px;
        }
        [dir="ltr"] .darli-blog-hero__accent-wrap {
            text-align: left;
        }
        .darli-blog-hero__accent-line {
            display: inline-block;
            width: 48px;
            height: 3.5px;
            background-color: #c9933b;
            border-radius: 2px;
        }

        .darli-blog-hero__subtitle {
            font-size: 15.5px;
            color: #64748b;
            font-weight: 500;
            margin: 0;
            line-height: 1.5;
            text-align: right;
        }
        [dir="ltr"] .darli-blog-hero__subtitle {
            text-align: left;
        }
        html[data-theme="dark"] .darli-blog-hero__subtitle {
            color: #94a3b8;
        }

        /* Floating Filter & Search Bar */
        .darli-blog-filter-bar {
            position: relative;
            z-index: 20;
            margin-top: -30px;
            margin-bottom: 36px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1f5f9;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        html[data-theme="dark"] .darli-blog-filter-bar {
            background: #1b283f;
            border-color: #2c3b52;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .darli-blog-search {
            position: relative;
            width: 320px;
            max-width: 100%;
        }
        .darli-blog-search__input {
            width: 100%;
            height: 44px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding-right: 42px !important;
            padding-left: 16px !important;
            text-align: right;
            font-size: 14px;
            color: #1e293b;
            background-color: #ffffff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        [dir="ltr"] .darli-blog-search__input {
            text-align: left;
            padding-left: 42px !important;
            padding-right: 16px !important;
        }
        .darli-blog-search__input:focus {
            border-color: #c9933b;
            box-shadow: 0 0 0 3px rgba(201, 147, 59, 0.15);
        }
        html[data-theme="dark"] .darli-blog-search__input {
            background-color: #152238;
            border-color: #2d3e56;
            color: #f1f5f9;
        }

        .darli-blog-search__icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 14px;
            left: auto;
            color: #64748b;
            pointer-events: none;
            display: flex;
            align-items: center;
        }
        [dir="ltr"] .darli-blog-search__icon {
            left: 14px;
            right: auto;
        }

        .darli-blog-categories {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .darli-blog-cat-btn {
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            color: #475569;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.4;
        }
        .darli-blog-cat-btn:hover {
            border-color: #cbd5e1;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .darli-blog-cat-btn.active {
            background: #c9933b !important;
            border-color: #c9933b !important;
            color: #ffffff !important;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(201, 147, 59, 0.25);
        }
        html[data-theme="dark"] .darli-blog-cat-btn {
            background-color: #162235;
            border-color: #2b3a4f;
            color: #cbd5e1;
        }
        html[data-theme="dark"] .darli-blog-cat-btn:hover {
            background-color: #1e2f47;
            color: #ffffff;
        }
        html[data-theme="dark"] .darli-blog-cat-btn.active {
            background: #c9933b !important;
            border-color: #c9933b !important;
            color: #ffffff !important;
        }

        /* 3-Column Card Grid */
        .darli-blog-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 26px;
            margin-bottom: 40px;
        }
        @media (max-width: 991px) {
            .darli-blog-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            .darli-blog-grid {
                grid-template-columns: 1fr;
            }
        }

        .darli-blog-card {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .darli-blog-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }
        html[data-theme="dark"] .darli-blog-card {
            background: #1b283f;
            border-color: #2c3b52;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .darli-blog-card__image-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background-color: #e2e8f0;
        }
        .darli-blog-card__image-link {
            display: block;
            width: 100%;
            height: 100%;
        }
        .darli-blog-card__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .darli-blog-card:hover .darli-blog-card__img {
            transform: scale(1.04);
        }

        .darli-blog-card__date-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #c9933b;
            color: #ffffff;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            z-index: 2;
        }

        .darli-blog-card__cat-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: #ffffff;
            color: #0f1d38;
            border-radius: 6px;
            padding: 4px 13px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.12);
            z-index: 2;
        }

        .darli-blog-card__body {
            padding: 22px 22px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .darli-blog-card__title {
            font-size: 16.5px;
            font-weight: 700;
            line-height: 1.45;
            margin: 0 0 10px;
            color: #0f1d38;
            letter-spacing: -0.2px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        @media (max-width: 1199px) {
            .darli-blog-card__title {
                white-space: normal;
                -webkit-line-clamp: 2;
            }
        }
        .darli-blog-card__title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.2s;
        }
        .darli-blog-card__title a:hover {
            color: #c9933b;
        }
        html[data-theme="dark"] .darli-blog-card__title {
            color: #f8fafc;
        }

        .darli-blog-card__excerpt {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.65;
            margin: 0 0 18px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        html[data-theme="dark"] .darli-blog-card__excerpt {
            color: #94a3b8;
        }

        .darli-blog-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 14px;
            border-top: 1px solid #f1f5f9;
            gap: 12px;
        }
        html[data-theme="dark"] .darli-blog-card__footer {
            border-top-color: #28374d;
        }

        .darli-blog-card__meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .darli-blog-card__meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: #64748b;
        }
        html[data-theme="dark"] .darli-blog-card__meta-item {
            color: #94a3b8;
        }

        .darli-blog-card__btn {
            background: #c9933b;
            color: #ffffff !important;
            border-radius: 8px;
            padding: 7px 16px;
            font-size: 13.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: background-color 0.2s, transform 0.2s;
            white-space: nowrap;
        }
        .darli-blog-card__btn:hover {
            background: #b58230;
            transform: translateY(-1px);
        }
    </style>

    <!-- ==================== INTERACTIVE FILTERING SCRIPT ==================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('blogSearchInput');
            const catButtons = document.querySelectorAll('.darli-blog-cat-btn');
            const cards = document.querySelectorAll('.darli-blog-card');
            const noResults = document.getElementById('blogNoResults');
            const pagination = document.getElementById('blogPagination');

            let activeCategory = 'all';
            let searchQuery = '';

            function filterPosts() {
                let visibleCount = 0;
                const q = searchQuery.trim().toLowerCase();

                cards.forEach(card => {
                    const cardCat = (card.getAttribute('data-category') || '').trim();
                    const title = (card.getAttribute('data-title') || '').toLowerCase();
                    const excerpt = (card.getAttribute('data-excerpt') || '').toLowerCase();

                    const matchesCategory = (activeCategory === 'all' || cardCat === activeCategory);
                    const matchesSearch = (!q || title.includes(q) || excerpt.includes(q));

                    if (matchesCategory && matchesSearch) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (noResults) {
                    noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
                }

                if (pagination) {
                    if (activeCategory !== 'all' || q !== '') {
                        pagination.style.display = 'none';
                    } else {
                        pagination.style.display = '';
                    }
                }
            }

            catButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    catButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeCategory = this.getAttribute('data-category') || 'all';
                    filterPosts();
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    searchQuery = this.value;
                    filterPosts();
                });
            }
        });
    </script>
@endsection
