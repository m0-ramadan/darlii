@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $isAr = $locale === 'ar';
@endphp

@section('title', $isAr ? 'العقارات المحفوظة | منصة دارلي' : 'Saved properties | Darlii')

@push('head')
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/darli-favorites.css') }}?v=1.0.0">
@endpush

@section('content')
    <section class="flat-section darli-fav-page">
        <div class="container">
            <header class="darli-fav-head">
                <div class="darli-fav-head__text">
                    <span class="darli-fav-eyebrow">{{ $isAr ? 'حسابي' : 'My account' }}</span>
                    <h1 class="darli-fav-title">{{ $isAr ? 'العقارات المحفوظة' : 'Saved properties' }}</h1>
                    <p class="darli-fav-subtitle">
                        {{ $isAr
                            ? 'كل العقارات التي حفظتها في مكان واحد — افتح التفاصيل أو أزل ما لم يعد يناسبك.'
                            : 'All the properties you saved in one place — open the details or remove what no longer fits.' }}
                    </p>
                </div>
                <span class="darli-fav-count">
                    <strong data-favorites-count>{{ $properties->count() }}</strong>
                    <span>{{ $isAr ? 'عقار' : 'properties' }}</span>
                </span>
            </header>

            <div id="darli-favorites-page" data-authenticated="{{ auth()->check() ? '1' : '0' }}">
                <div id="darli-favorites-grid" class="row g-4">
                    @foreach ($properties as $property)
                        @include('favorites._card', [
                            'property' => $property,
                            'locale' => $locale,
                            'isAr' => $isAr,
                        ])
                    @endforeach
                </div>

                <div id="darli-favorites-empty" class="darli-fav-empty" @if ($properties->isNotEmpty()) hidden @endif>
                    <div class="darli-fav-empty__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />
                        </svg>
                    </div>
                    <h2 class="darli-fav-empty__title">
                        {{ $isAr ? 'لا توجد عقارات محفوظة بعد' : 'No saved properties yet' }}
                    </h2>
                    <p class="darli-fav-empty__text">
                        {{ $isAr
                            ? 'اضغط على أيقونة القلب في أي عقار ليظهر هنا، ويمكنك الرجوع له في أي وقت.'
                            : 'Tap the heart icon on any property to save it here and come back to it anytime.' }}
                    </p>
                    <a href="{{ url('/properties') }}" class="darli-fav-empty__btn">
                        {{ $isAr ? 'تصفح العقارات' : 'Browse properties' }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
