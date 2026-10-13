@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $tr = $post->translation($locale);

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

    /** التاريخ: عربي بصيغة مقروءة، وإنجليزي ISO. */
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
            : $date->format('Y-m-d');
    };

    /** زمن القراءة التقديري: عدد الكلمات ÷ 200 (دقيقة على الأقل). */
    $readingMinutes = function (?string $text): int {
        $plain = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

        if ($plain === '') {
            return 1;
        }

        $words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / 200));
    };

    $heroImage = blank($post->featured_image) ? null : $articleImage($post->featured_image);
    $minutes = $readingMinutes($tr?->content);
    $authorName = $post->author?->name ?: ($isAr ? 'فريق دارلي' : 'Darli team');
@endphp

@section('title', ($tr?->seo_title ?: $tr?->title ?: ($isAr ? 'مقال' : 'Article')) . ($isAr ? ' | دارلي' : ' | Darlii'))

@section('content')
    {{-- ===================== مسار التنقل ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-4">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home', ['locale' => $locale]) }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('blog.index', ['locale' => $locale]) }}">{{ $isAr ? 'المدونة' : 'Blog' }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $tr?->title }}</li>
                </ol>
            </nav>
        </div>
    </section>

    {{-- ===================== صورة المقال والعنوان ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="darli-panel darli-panel--pad">
                @if ($heroImage)
                    <div class="images-group">
                        <div class="images-style rounded-3">
                            <img src="{{ $heroImage }}" alt="{{ $tr?->title }}" class="w-100 d-block"
                                style="aspect-ratio:16/9;object-fit:cover;"
                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        </div>
                    </div>
                @endif

                <h1 class="h4 fw-7 {{ $heroImage ? 'mt-4' : '' }} mb-3">{{ $tr?->title }}</h1>

                <div class="d-flex flex-wrap gap-3 align-items-center text-1">
                    <span class="d-flex gap-8 align-items-center">
                        @if ($post->author?->avatar_url)
                            <img src="{{ $post->author->avatar_url }}" alt="{{ $authorName }}"
                                width="40" height="40"
                                style="border-radius:50%;object-fit:cover;width:40px;height:40px;"
                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        @else
                            <i class="icon icon-user"></i>
                        @endif
                        <span class="fw-6">{{ $authorName }}</span>
                    </span>

                    <span class="d-flex gap-8 align-items-center">
                        <i class="icon icon-calendar"></i>
                        <span>{{ $fmtDate($post->published_at) }}</span>
                    </span>

                    <span class="d-flex gap-8 align-items-center">
                        <i class="icon icon-calendar"></i>
                        <span>{{ $minutes }} {{ $isAr ? 'دقائق قراءة' : 'min read' }}</span>
                    </span>
                </div>

                @if (filled($tr?->excerpt))
                    <p class="text-1 mt-3 mb-0">{{ $tr->excerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== نص المقال ===================== --}}
    @if (filled($tr?->content))
        <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
            <div class="container">
                <article class="darli-panel darli-panel--pad">
                    <div class="text-1" style="line-height:2;">{!! nl2br(e($tr->content)) !!}</div>
                </article>
            </div>
        </section>
    @endif

    {{-- ===================== مقالات ذات صلة ===================== --}}
    @if ($related->isNotEmpty())
        <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
            <div class="container">
                <div class="box-title-listing">
                    <div class="h5 fw-7">{{ $isAr ? 'مقالات ذات صلة' : 'Related articles' }}</div>
                    <a class="tf-btn size-1" href="{{ route('blog.index', ['locale' => $locale]) }}">
                        {{ $isAr ? 'كل المقالات' : 'All articles' }}
                    </a>
                </div>

                <div class="darli-grid darli-grid--3">
                    @foreach ($related as $item)
                        @php
                            $itemTr = $item->translation($locale);
                            $itemUrl = $itemTr?->slug
                                ? route('blog.show', ['locale' => $locale, 'slug' => $itemTr->slug])
                                : '#';
                            $itemImage = $articleImage($item->featured_image);
                            $itemExcerpt = \Illuminate\Support\Str::limit(
                                trim(strip_tags((string) $itemTr?->excerpt)),
                                120
                            );
                        @endphp

                        <div>
                            <article class="property-item homeya-box h-100">
                                <div class="archive-top">
                                    <a href="{{ $itemUrl }}" class="images-group">
                                        <div class="images-style">
                                            <img src="{{ $itemImage }}" alt="{{ $itemTr?->title }}"
                                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                        </div>
                                        @if ($item->published_at)
                                            <div class="top">
                                                <div class="d-flex gap-8">
                                                    <span class="flag-tag primary">{{ $fmtDate($item->published_at) }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </a>

                                    <div class="content">
                                        <div class="text-capitalize h7 fw-7">
                                            <a href="{{ $itemUrl }}" class="link line-clamp-1">{{ $itemTr?->title }}</a>
                                        </div>

                                        @if ($itemExcerpt !== '')
                                            <p class="text-1 mt-2 mb-0">{{ $itemExcerpt }}</p>
                                        @endif

                                        <ul class="meta-list">
                                            <li class="item">
                                                <i class="icon icon-user"></i>
                                                <span>{{ $item->author?->name ?: ($isAr ? 'فريق دارلي' : 'Darli team') }}</span>
                                            </li>
                                            <li class="item">
                                                <i class="icon icon-calendar"></i>
                                                <span>{{ $fmtDate($item->published_at) }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="archive-bottom d-flex justify-content-between align-items-center">
                                    <span class="text-1">{{ $isAr ? 'مقال' : 'Article' }}</span>
                                    <a href="{{ $itemUrl }}" class="tf-btn primary size-2">
                                        {{ $isAr ? 'التفاصيل' : 'Details' }}
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
