@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $tr = $property->translation($locale);
    $isRent = $property->purpose === 'rent';

    /** Public URL for a media row */
    $mediaUrl = function ($media): string {
        $path = ltrim((string) $media->path, '/');

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return ($media->disk ?? 'public') === 'legacy'
            ? asset('legacy/' . $path)
            : asset('storage/' . $path);
    };

    /** 88.00 -> 88 ، 88.50 -> 88.5 ، null -> — */
    $num = fn ($value): string => $value === null || $value === ''
        ? '—'
        : rtrim(rtrim(number_format((float) $value, 2), '0'), '.');

    $areaUnit = match ((string) $property->area_unit) {
        'm2', 'm²', 'sqm', 'sq_m' => $isAr ? 'م²' : 'm²',
        'ft2', 'ft²' => $isAr ? 'قدم²' : 'ft²',
        default => $property->area_unit ?: ($isAr ? 'م²' : 'm²'),
    };

    $heroImage = $property->primaryImageUrl($locale);
    $placeholder = $property->placeholderUrl($locale);
    $gallery = $property->media;

    $address = $tr?->address_text ?: ($property->location?->translation($locale)?->name ?? ($isAr ? 'بني سويف' : 'Beni Suef'));
    $categoryName = $property->category?->translation($locale)?->name;
    $projectTr = $property->project?->translation($locale);

    $isContactPrice = $property->isPriceOnContact();
    $isNegotiablePrice = $property->isNegotiable();

    if ($isContactPrice) {
        $formattedPrice = $isAr ? 'السعر عند التواصل' : 'Price on request';
        $priceLabel = $formattedPrice;
    } else {
        $formattedPrice = number_format((float) $property->price, 0) . ' ' . ($isAr ? 'جنيه' : 'EGP');
        $priceLabel = $formattedPrice . ($isRent ? ($isAr ? ' / شهرياً' : ' / month') : '');
    }

    $phone = trim((string) setting('contact.phone', ''));
    $whatsapp = trim((string) setting('contact.whatsapp', ''));
    $whatsappDigits = preg_replace('/\D+/', '', $whatsapp) ?? '';
    $email = trim((string) setting('contact.email', ''));

    $hasCoords = $property->latitude !== null && $property->longitude !== null
        && $property->latitude !== '' && $property->longitude !== '';
    $coords = $hasCoords ? $property->latitude . ',' . $property->longitude : '';
@endphp

@section('title', ($tr?->seo_title ?: $tr?->name ?: $property->reference_code) . ($isAr ? ' | دارلي' : ' | Darlii'))

@section('content')

    {{-- ===================== 1. معرض الصور المتنقل الأصلي لهومزن ===================== --}}
    <section class="flat-location flat-slider-detail-v1">
        <div class="swiper tf-sw-location" data-preview-lg="2.03" data-preview-md="2" data-preview-sm="2" data-space="20" data-centered="true" data-loop="true">
            <div class="swiper-wrapper">
                @if ($gallery->isNotEmpty())
                    @foreach ($gallery as $item)
                        @php
                            $imgUrl = $mediaUrl($item);
                        @endphp
                        <div class="swiper-slide">
                            <a href="{{ $imgUrl }}" class="box-imgage-detail d-block" data-fancybox="gallery">
                                <img src="{{ $imgUrl }}" alt="{{ $tr?->name }}" width="100%" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="swiper-slide">
                        <a href="{{ $heroImage }}" class="box-imgage-detail d-block" data-fancybox="gallery">
                            <img src="{{ $heroImage }}" alt="{{ $tr?->name }}" width="100%" loading="lazy"
                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                        </a>
                    </div>
                @endif
            </div>
            <div class="box-navigation">
                <div class="navigation swiper-nav-next nav-next-location">
                    <svg class="icon svg-icon-ti-ti-chevron-left" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 6l-6 6l6 6"></path>
                    </svg>
                </div>
                <div class="navigation swiper-nav-prev nav-prev-location">
                    <svg class="icon svg-icon-ti-ti-chevron-right" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 6l6 6l-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== 2. قسم تفاصيل العقار بهيكل هومزن الأصلي ===================== --}}
    <section class="flat-section pt-0 flat-property-detail">
        <div class="container">
            {{-- بطاقة الترويسة الرئيسية العائمة فوق السلايدر --}}
            <div class="header-property-detail">
                <div class="content-top d-flex justify-content-between align-items-center">
                    <div class="box-name">
                        @if ($property->isSold())
                            <span class="flag-tag success status-badge-sold" style="background-color: #10b981 !important; color: #fff !important; font-weight: 800; padding: 6px 14px; border-radius: 6px; font-size: 13px; box-shadow: 0 2px 8px rgba(16,185,129,0.35);">
                                {{ $isAr ? 'تم البيع' : 'Sold' }}
                            </span>
                        @endif
                        @if ($property->is_featured)
                            <span class="flag-tag success">{{ $isAr ? 'مميز' : 'Featured' }}</span>
                        @endif
                        <span class="flag-tag primary {{ $isRent ? 'status-badge-renting' : 'status-badge-selling' }}">
                            {{ $isRent ? ($isAr ? 'للإيجار' : 'For rent') : ($isAr ? 'للبيع' : 'For sale') }}
                        </span>
                        @if ($categoryName)
                            <span class="flag-tag style-2">{{ $categoryName }}</span>
                        @endif
                        <h1 class="h4 title">{{ $tr?->name ?: '#' . $property->reference_code }}</h1>
                    </div>
                    <div class="box-price d-flex align-items-center gap-2 flex-wrap">
                        <h4>{{ $priceLabel }}</h4>
                        @if ($isNegotiablePrice)
                            <span class="flag-tag success" style="font-size: 13px; font-weight: 600; padding: 4px 10px; border-radius: 6px; display: inline-flex; align-items: center;">
                                {{ $isAr ? 'قابل للتفاوض' : 'Negotiable' }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="content-bottom">
                    <div class="info-box">
                        <ul class="meta">
                            <li class="meta-item rating-star-wrapper">
                                <div class="rating-star">
                                    <span></span>
                                </div>
                                (5.0)
                            </li>
                            <li class="meta-item">
                                <svg class="icon svg-icon-ti-ti-eye" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                                </svg>
                                {{ number_format($property->views ?: 120) }} {{ $isAr ? 'مشاهدة' : 'views' }}
                            </li>
                            @if ($property->published_at)
                                <li class="meta-item">
                                    <svg class="icon svg-icon-ti-ti-calendar" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                        <path d="M16 3v4"></path>
                                        <path d="M8 3v4"></path>
                                        <path d="M4 11h16"></path>
                                    </svg>
                                    {{ $property->published_at->translatedFormat('F d, Y') }}
                                </li>
                            @endif
                            @if ($address)
                                <li class="meta-item">
                                    <svg class="icon svg-icon-ti-ti-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path>
                                    </svg>
                                    {{ $address }}
                                </li>
                            @endif
                        </ul>
                    </div>
                    <ul class="icon-box">
                        <li>
                            <button class="item" data-bb-toggle="add-to-wishlist" data-id="{{ $property->id }}" data-type="property" type="button" aria-label="Favorite">
                                <svg class="icon svg-icon-ti-ti-heart" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"></path>
                                </svg>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- التخطيط ذو العمودين: المحتوى والشريط الجانبي --}}
            <div class="row">
                <div class="col-lg-8">
                    {{-- 1. الوصف --}}
                    @if (filled($tr?->description))
                        <div class="single-property-desc single-property-element">
                            <div class="h7 title fw-7">{{ $isAr ? 'الوصف' : 'Description' }}</div>
                            <div class="body-2 text-variant-1">
                                <div class="ck-content single-detail">
                                    {!! nl2br(e($tr?->description)) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 2. نظرة عامة --}}
                    <div class="single-property-overview single-property-element">
                        <div class="h7 title fw-7">{{ $isAr ? 'نظرة عامة' : 'Overview' }}</div>
                        <div class="row row-cols-sm-2 row-cols-lg-3 g-3 g-lg-4 info-box">
                            <div class="col item">
                                <div class="box-icon w-52">
                                    <svg class="icon svg-icon-ti-ti-home" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12l-2 0l9 -9l9 9l-2 0"></path>
                                        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path>
                                        <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"></path>
                                    </svg>
                                </div>
                                <div class="content">
                                    <span class="label">{{ $isAr ? 'معرّف العقار:' : 'Property ID:' }}</span>
                                    <span>{{ $property->reference_code ?: '#' . $property->id }}</span>
                                </div>
                            </div>

                            @if ($categoryName)
                                <div class="col item">
                                    <div class="box-icon w-52">
                                        <svg class="icon svg-icon-ti-ti-category" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4h6v6h-6z"></path>
                                            <path d="M14 4h6v6h-6z"></path>
                                            <path d="M4 14h6v6h-6z"></path>
                                            <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"></path>
                                        </svg>
                                    </div>
                                    <div class="content">
                                        <span class="label">{{ $isAr ? 'النوع:' : 'Type:' }}</span>
                                        <span>{{ $categoryName }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="col item">
                                <div class="box-icon w-52">
                                    <svg class="icon svg-icon-ti-ti-bed" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M22 17v-3h-20"></path>
                                        <path d="M2 8v9"></path>
                                        <path d="M12 14h10v-2a3 3 0 0 0 -3 -3h-7v5z"></path>
                                    </svg>
                                </div>
                                <div class="content">
                                    <span class="label">{{ $isAr ? 'غرف النوم:' : 'Bedrooms:' }}</span>
                                    <span>{{ $num($property->bedrooms) }}</span>
                                </div>
                            </div>

                            <div class="col item">
                                <div class="box-icon w-52">
                                    <svg class="icon svg-icon-ti-ti-bath" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 12h16a1 1 0 0 1 1 1v3a4 4 0 0 1 -4 4h-10a4 4 0 0 1 -4 -4v-3a1 1 0 0 1 1 -1z"></path>
                                        <path d="M6 12v-7a2 2 0 0 1 2 -2h3v2.25"></path>
                                        <path d="M4 21l1 -1.5"></path>
                                        <path d="M20 21l-1 -1.5"></path>
                                    </svg>
                                </div>
                                <div class="content">
                                    <span class="label">{{ $isAr ? 'الحمامات:' : 'Bathrooms:' }}</span>
                                    <span>{{ $num($property->bathrooms) }}</span>
                                </div>
                            </div>

                            <div class="col item">
                                <div class="box-icon w-52">
                                    <svg class="icon svg-icon-ti-ti-ruler-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3l4 4l-14 14l-4 -4z"></path>
                                        <path d="M16 7l-1.5 -1.5"></path>
                                        <path d="M13 10l-1.5 -1.5"></path>
                                        <path d="M10 13l-1.5 -1.5"></path>
                                        <path d="M7 16l-1.5 -1.5"></path>
                                    </svg>
                                </div>
                                <div class="content">
                                    <span class="label">{{ $isAr ? 'المساحة:' : 'Size:' }}</span>
                                    <span>{{ $num($property->area) }} {{ $areaUnit }}</span>
                                </div>
                            </div>

                            @if ($property->parking_spaces)
                                <div class="col item">
                                    <div class="box-icon w-52">
                                        <svg class="icon svg-icon-ti-ti-car" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                            <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                                        </svg>
                                    </div>
                                    <div class="content">
                                        <span class="label">{{ $isAr ? 'موقف سيارات:' : 'Parking:' }}</span>
                                        <span>{{ $property->parking_spaces }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($property->floor_number)
                                <div class="col item">
                                    <div class="box-icon w-52">
                                        <svg class="icon svg-icon-ti-ti-stairs" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 5h-5v5h-5v5h-5v5h-5"></path>
                                        </svg>
                                    </div>
                                    <div class="content">
                                        <span class="label">{{ $isAr ? 'الدور:' : 'Floor:' }}</span>
                                        <span>{{ $property->floor_number }}</span>
                                    </div>
                                </div>
                            @endif

                            @if ($property->year_built)
                                <div class="col item">
                                    <div class="box-icon w-52">
                                        <svg class="icon svg-icon-ti-ti-calendar" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z"></path>
                                            <path d="M16 3v4"></path>
                                            <path d="M8 3v4"></path>
                                            <path d="M4 11h16"></path>
                                        </svg>
                                    </div>
                                    <div class="content">
                                        <span class="label">{{ $isAr ? 'سنة البناء:' : 'Year Built:' }}</span>
                                        <span>{{ $property->year_built }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 3. المرافق والمميزات --}}
                    @if ($property->amenities->isNotEmpty())
                        <div class="single-property-feature single-property-element">
                            <div class="h7 title fw-7">{{ $isAr ? 'المرافق والميزات' : 'Amenities & Features' }}</div>
                            <div class="box-feature">
                                <ul>
                                    @foreach ($property->amenities as $amenity)
                                        <li class="feature-item">
                                            <svg class="icon svg-icon-ti-ti-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="2.5" stroke="#c9933b" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M5 12l5 5l10 -10"></path>
                                            </svg>
                                            {{ $amenity->translation($locale)?->name ?? $amenity->name }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- 4. معلومات المشروع (إذا كان العقار تابعاً لمشروع) --}}
                    @if ($property->project && $projectTr)
                        <div class="single-property-project single-property-element">
                            <div class="h7 title fw-7">{{ $isAr ? 'معلومات المشروع' : 'Project Information' }}</div>
                            <div class="box-project mt-3">
                                <div class="project-thumb">
                                    <a href="{{ route('projects.show', ['locale' => $locale, 'slug' => $projectTr->slug ?? $property->project->id]) }}">
                                        <img src="{{ $property->project->properties->first()?->primaryImageUrl($locale) ?? $placeholder }}" alt="{{ $projectTr->name }}" class="darli-logo-img"
                                            onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                    </a>
                                </div>
                                <div class="project-info">
                                    <h5 class="title">
                                        <a href="{{ route('projects.show', ['locale' => $locale, 'slug' => $projectTr->slug ?? $property->project->id]) }}">
                                            {{ $projectTr->name }}
                                        </a>
                                    </h5>
                                    @if ($projectTr->description)
                                        <div class="text-variant-1">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($projectTr->description), 140) }}
                                        </div>
                                    @endif
                                    @if ($property->project->location?->translation($locale)?->name)
                                        <ul class="meta">
                                            <li class="meta-item">
                                                <svg class="icon svg-icon-ti-ti-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                                    <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path>
                                                </svg>
                                                {{ $property->project->location->translation($locale)->name }}
                                            </li>
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 5. الموقع على الخريطة --}}
                    <div class="single-property-map single-property-element">
                        <div class="h7 title fw-7">{{ $isAr ? 'الموقع' : 'Location' }}</div>
                        <div id="homzen-property-map" style="min-height: 400px; border-radius: 8px; width: 100%;"></div>
                        @if ($address)
                            <ul class="info-map">
                                <li>
                                    <div class="fw-7">{{ $isAr ? 'العنوان' : 'Address' }}</div>
                                    <a class="mt-4 text-variant-1" href="{{ $hasCoords ? 'https://maps.google.com/?q=' . $coords : '#' }}" target="_blank">
                                        {{ $address }}
                                    </a>
                                </li>
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- الشريط الجانبي (تواصل مع الوكالة / السمسار) --}}
                <div class="col-lg-4">
                    <div class="widget-sidebar wrapper-sidebar-right">
                        <div class="widget-box single-property-contact bg-surface">
                            <div class="h7 title fw-7">{{ $isAr ? 'تواصل مع الوكالة' : 'Contact Agency' }}</div>
                            <div class="box-avatar">
                                <div class="avatar avt-100 round">
                                    <a class="d-block" href="#">
                                        <img src="{{ $property->owner?->avatar_url ? asset('storage/' . $property->owner->avatar_url) : asset('themes/homzen/images/logo/darli-logo-ar.png') }}"
                                            alt="{{ $property->owner?->name ?? 'Darli' }}" class="darli-logo-img"
                                            onerror="this.onerror=null;this.src='{{ asset('themes/homzen/images/logo/darli-logo-ar.png') }}';">
                                    </a>
                                </div>
                                <div class="info line-clamp-1">
                                    <div class="text-1 name">
                                        <a href="#">{{ $property->owner?->name ?? ($isAr ? 'سارة السمسار' : 'Darlii Agent') }}</a>
                                    </div>
                                    @if ($phone)
                                        <a class="info-item" href="tel:{{ $phone }}" dir="ltr">{{ $phone }}</a>
                                    @endif
                                    @if ($email)
                                        <a class="info-item" href="mailto:{{ $email }}">{{ $email }}</a>
                                    @endif
                                </div>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success mt-3 py-2 px-3 small">{{ session('success') }}</div>
                            @endif

                            <form method="POST" action="{{ route('leads.store', ['locale' => $locale]) }}" class="js-base-form consult-form contact-form mt-3">
                                @csrf
                                <input type="hidden" name="property_id" value="{{ $property->id }}">
                                <input type="hidden" name="source" value="property_inquiry">

                                <div class="ip-group">
                                    <label class="form-label required" for="inquiry-name">{{ $isAr ? 'الاسم' : 'Name' }}</label>
                                    <input type="text" class="form-control" id="inquiry-name" name="name"
                                        value="{{ old('name') }}" placeholder="{{ $isAr ? 'أحمد محمد' : 'Your name' }}" required>
                                </div>
                                <div class="ip-group">
                                    <label class="form-label required" for="inquiry-phone">{{ $isAr ? 'الهاتف' : 'Phone' }}</label>
                                    <input type="tel" class="form-control" id="inquiry-phone" name="phone"
                                        value="{{ old('phone') }}" placeholder="{{ $isAr ? 'مثال 0123456789' : 'Phone number' }}" required>
                                </div>
                                <div class="ip-group">
                                    <label class="form-label" for="inquiry-email">{{ $isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
                                    <input type="email" class="form-control" id="inquiry-email" name="email"
                                        value="{{ old('email') }}" placeholder="email@example.com">
                                </div>
                                <div class="ip-group">
                                    <input type="text" class="form-control" disabled value="{{ $tr?->name ?? $property->reference_code }}">
                                </div>
                                <div class="ip-group">
                                    <label class="form-label required" for="inquiry-message">{{ $isAr ? 'الرسالة' : 'Message' }}</label>
                                    <textarea class="form-control" id="inquiry-message" name="message" rows="3"
                                        placeholder="{{ $isAr ? 'أدخل رسالتك...' : 'Enter your message...' }}" required>{{ old('message') }}</textarea>
                                </div>

                                <button type="submit" class="tf-btn primary w-100 mt-3">
                                    {{ $isAr ? 'إرسال الرسالة' : 'Send Message' }}
                                </button>
                            </form>

                            @if ($whatsappDigits !== '')
                                <div class="whatsapp-contact-section mt-4 pt-4">
                                    <div class="text-center mb-3">
                                        <p class="mb-2">{{ $isAr ? 'أو احصل على رد فوري عبر واتساب' : 'Or get instant reply via WhatsApp' }}</p>
                                    </div>
                                    <div class="text-center">
                                        <a class="contact-whatsapp-btn justify-content-center"
                                            href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode(($isAr ? 'مرحباً، أود الاستفسار عن عقار: ' : 'Hi, I want to inquire about: ') . ($tr?->name ?? $property->reference_code) . ' - ' . url()->current()) }}"
                                            target="_blank" rel="noopener">
                                            <svg class="icon svg-icon-ti-ti-brand-whatsapp" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9"></path>
                                                <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1"></path>
                                            </svg>
                                            <span>{{ $isAr ? 'الدردشة عبر واتساب' : 'Chat via WhatsApp' }}</span>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== 3. عقارات مشابهة بتصميم بطاقات هومزن الأصلية ===================== --}}
    @if ($similar->isNotEmpty())
        <section class="flat-section pt-0 flat-latest-property">
            <div class="container">
                <div class="box-title">
                    <div class="text-subtitle text-primary">{{ $isAr ? 'عقارات مشابهة' : 'Similar Properties' }}</div>
                    <h2 class="section-title mt-4">{{ $isAr ? 'عقارات قد تهمك' : 'Properties you might like' }}</h2>
                </div>
                <div class="row g-4">
                    @foreach ($similar as $item)
                        @php
                            $sTr = $item->translation($locale);
                            $sIsRent = $item->purpose === 'rent';
                            $sUrl = route('properties.show', ['locale' => $locale, 'slug' => $sTr?->slug ?? $item->id]);
                            $sImage = $item->primaryImageUrl($locale);
                            $sPurposeLabel = $sIsRent ? ($isAr ? 'للإيجار' : 'For rent') : ($isAr ? 'للبيع' : 'For sale');
                            $sPurposeClass = $sIsRent ? 'status-badge-renting' : 'status-badge-selling';
                            $sCategoryName = $item->category?->translation($locale)?->name;
                            $sLocationName = $sTr?->address_text ?: ($item->location?->translation($locale)?->name ?? ($isAr ? 'بني سويف' : 'Beni Suef'));
                            $sIsContact = $item->isPriceOnContact();
                            $sIsNegotiable = $item->isNegotiable();
                            $sPrice = $sIsContact
                                ? ($isAr ? 'السعر عند التواصل' : 'Price on request')
                                : (number_format((float) $item->price, 0) . ' ' . ($isAr ? 'جنيه' : 'EGP'));
                            $sArea = $item->area ? rtrim(rtrim(number_format((float) $item->area, 2), '0'), '.') : null;
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="property-item homeya-box style-2">
                                <div class="archive-top">
                                    <a class="images-group" href="{{ $sUrl }}">
                                        <div class="images-style">
                                            <img src="{{ $sImage }}" alt="{{ $sTr?->name }}" loading="lazy"
                                                onerror="this.onerror=null;this.src='{{ $item->placeholderUrl($locale) }}';">
                                        </div>
                                        <div class="top">
                                            <div class="d-flex gap-8">
                                                @if ($item->isSold())
                                                    <span class="flag-tag success status-badge-sold" style="background-color: #10b981 !important; color: #fff !important; font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: 12px; box-shadow: 0 2px 6px rgba(16,185,129,0.3);">
                                                        {{ $isAr ? 'تم البيع' : 'Sold' }}
                                                    </span>
                                                @endif
                                                @if ($item->is_featured)
                                                    <span class="flag-tag success">{{ $isAr ? 'مميز' : 'Featured' }}</span>
                                                @endif
                                                <span class="flag-tag primary {{ $sPurposeClass }}">{{ $sPurposeLabel }}</span>
                                            </div>
                                        </div>
                                        <div class="bottom-sub">
                                            @if ($sCategoryName)
                                                <span class="flag-tag style-2">{{ $sCategoryName }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </div>
                                <div class="content">
                                    <div class="archive-bottom">
                                        <h6 class="title">
                                            <a href="{{ $sUrl }}">{{ $sTr?->name ?? $item->reference_code }}</a>
                                        </h6>
                                        <div class="desc">
                                            <svg class="icon svg-icon-ti-ti-map-pin" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                                <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                                                <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"></path>
                                            </svg>
                                            <p>{{ $sLocationName }}</p>
                                        </div>
                                        <div class="price d-flex align-items-center gap-2 flex-wrap">
                                            <span>{{ $sPrice }}{{ ($sIsRent && ! $sIsContact) ? ($isAr ? ' / شهرياً' : ' / mo') : '' }}</span>
                                            @if ($sIsNegotiable)
                                                <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                                    {{ $isAr ? 'قابل للتفاوض' : 'Negotiable' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="meta-list">
                                        <div class="item">
                                            <i class="icon icon-bed"></i>
                                            <span>{{ $item->bedrooms ?: 1 }} {{ $isAr ? 'غرف' : 'Beds' }}</span>
                                        </div>
                                        <div class="item">
                                            <i class="icon icon-bathtub"></i>
                                            <span>{{ $item->bathrooms ?: 1 }} {{ $isAr ? 'حمام' : 'Baths' }}</span>
                                        </div>
                                        <div class="item">
                                            <i class="icon icon-ruler"></i>
                                            <span>{{ $sArea ?: '—' }} {{ $isAr ? 'م²' : 'm²' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- تفعيل خريطة الموقع التفاعلية --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var mapEl = document.getElementById('homzen-property-map');
            if (mapEl && typeof L !== 'undefined') {
                var lat = {{ $hasCoords ? (float) $property->latitude : 29.0661 }};
                var lng = {{ $hasCoords ? (float) $property->longitude : 31.0994 }};
                var isDark = document.documentElement.dataset.theme === 'dark';

                var tileUrl = isDark
                    ? 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}'
                    : 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}';

                var map = L.map('homzen-property-map', {
                    center: [lat, lng],
                    zoom: 15,
                    scrollWheelZoom: false,
                    attributionControl: false
                });

                L.tileLayer(tileUrl, { maxZoom: 18 }).addTo(map);

                var iconHtml = '<div style="background:#c9933b;width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 4px 14px rgba(201,147,59,0.5);border:3px solid #fff;font-size:18px;">📍</div>';
                var customIcon = L.divIcon({
                    className: 'darli-map-pin',
                    html: iconHtml,
                    iconSize: [38, 38],
                    iconAnchor: [19, 19]
                });

                L.marker([lat, lng], { icon: customIcon }).addTo(map);
            }
        });
    </script>
@endsection
