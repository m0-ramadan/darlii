@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $filters = request()->only(['q', 'k', 'purpose', 'category_id', 'location_id', 'sort']);
    $searchTerm = $filters['q'] ?? ($filters['k'] ?? '');
@endphp

@section('content')
    {{-- Leaflet CSS & Custom Properties Map CSS --}}
    <link rel="stylesheet" href="{{ asset('themes/homzen/plugins/leaflet/leaflet.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/homzen/css/darli-properties-map.css') }}?v=1.0.4">

    <div class="darli-properties-page">
        <div class="darli-properties-split-container">

            {{-- ================= 1. Right Content Pane (Listing & Filters) ================= --}}
            <div class="darli-split-content-pane">

                {{-- Header & City Badge --}}
                <div class="darli-prop-header">
                    <div class="darli-prop-title-box">
                        <div class="darli-prop-breadcrumb">
                            <a href="{{ url($locale) }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                            <span class="sep">›</span>
                            <span>{{ $isAr ? 'العقارات' : 'Properties' }}</span>
                        </div>
                        <h1 class="darli-prop-title">
                            {{ $isAr ? 'العقارات في ' . ($currentCity ?? 'بني سويف') : 'Properties in ' . ($currentCity ?? 'Beni Suef') }}
                        </h1>
                        <p class="darli-prop-subtitle">
                            {{ $isAr ? 'اكتشف أفضل العقارات المتاحة للبيع والإيجار في جميع مناطق بني سويف' : 'Discover the best properties available for sale and rent in all areas of Beni Suef' }}
                        </p>
                    </div>

                    <div class="darli-city-badge-card">
                        <div class="darli-city-badge-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-city-badge-info">
                            <span
                                class="darli-city-badge-name">{{ $currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef') }}</span>
                            <span class="darli-city-badge-count">{{ number_format($properties->total()) }}
                                {{ $isAr ? 'عقار متاح' : 'properties' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Horizontal Filter Bar --}}
                <div class="darli-prop-filter-card">
                    <form id="darli-filter-form" method="get"
                        action="{{ route('properties.index', ['locale' => $locale]) }}" class="darli-filter-form">
                        <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">

                        {{-- Search Input --}}
                        <div class="darli-filter-field search-field">
                            <label class="darli-filter-label">{{ $isAr ? 'البحث' : 'Search' }}</label>
                            <div class="darli-input-icon-wrap">
                                <input type="search" name="q" value="{{ $searchTerm }}" class="darli-filter-input"
                                    placeholder="{{ $isAr ? 'اسم العقار أو الكود...' : 'Property name or code...' }}">
                                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                        </div>

                        {{-- Purpose Select --}}
                        <div class="darli-filter-field">
                            <label class="darli-filter-label">{{ $isAr ? 'الغرض' : 'Purpose' }}</label>
                            <select name="purpose" class="darli-filter-select">
                                <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                                <option value="sale" @selected(($filters['purpose'] ?? '') === 'sale')>{{ $isAr ? 'للبيع' : 'For sale' }}
                                </option>
                                <option value="rent" @selected(($filters['purpose'] ?? '') === 'rent')>{{ $isAr ? 'للإيجار' : 'For rent' }}
                                </option>
                            </select>
                        </div>

                        {{-- Category Select --}}
                        <div class="darli-filter-field">
                            <label class="darli-filter-label">{{ $isAr ? 'النوع' : 'Type' }}</label>
                            <select name="category_id" class="darli-filter-select">
                                <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                        {{ $category->translation($locale)?->name ?? $category->key }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Location Select --}}
                        <div class="darli-filter-field">
                            <label class="darli-filter-label">{{ $isAr ? 'المنطقة' : 'Area' }}</label>
                            <select name="location_id" class="darli-filter-select">
                                <option value="">{{ $isAr ? 'كل المناطق' : 'All areas' }}</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" @selected((string) ($filters['location_id'] ?? '') === (string) $location->id)>
                                        {{ $location->translation($locale)?->name ?? $location->id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="darli-filter-actions">
                            <a href="{{ route('properties.index', ['locale' => $locale]) }}"
                                class="darli-filter-btn-reset">
                                {{ $isAr ? 'إعادة تعيين' : 'Reset' }}
                            </a>
                            <button type="submit" class="darli-filter-btn-submit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span>{{ $isAr ? 'بحث عن العقارات' : 'Search properties' }}</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Sub-bar: Count + View Switches + Sort --}}
                <div class="darli-prop-subbar">
                    <div class="darli-prop-count-badge">
                        {{ number_format($properties->total()) }} {{ $isAr ? 'عقار متاح' : 'properties available' }}
                    </div>

                    <div class="darli-prop-controls">
                        <div class="darli-view-toggles">
                            <button type="button" id="darli-view-grid" class="darli-view-btn active"
                                title="{{ $isAr ? 'عرض شبكي' : 'Grid view' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                                </svg>
                            </button>
                            <button type="button" id="darli-view-list" class="darli-view-btn"
                                title="{{ $isAr ? 'عرض طولي' : 'List view' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </button>
                        </div>

                        <select id="darli-sort-select" class="darli-sort-select">
                            <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                                {{ $isAr ? 'أحدث أولاً' : 'Newest first' }}</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>
                                {{ $isAr ? 'السعر: من الأقل للأعلى' : 'Price: Low to High' }}</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>
                                {{ $isAr ? 'السعر: من الأعلى للأقل' : 'Price: High to Low' }}</option>
                            <option value="popular" @selected(request('sort') === 'popular')>
                                {{ $isAr ? 'الأكثر شعبية' : 'Most popular' }}</option>
                        </select>
                    </div>
                </div>

                {{-- Property Cards Grid (3 Columns) --}}
                @if ($properties->isEmpty())
                    <div class="darli-panel darli-empty p-5 text-center"
                        style="background:#fff;border-radius:12px;border:1px solid #edf2f7;">
                        <h4 class="mb-2" style="font-weight:700;">
                            {{ $isAr ? 'لا توجد عقارات مطابقة للبحث' : 'No matching properties found' }}</h4>
                        <p class="text-muted mb-0">
                            {{ $isAr ? 'جرّب تغيير خيارات الفلترة أو إعادة تعيين البحث لعرض كافة العقارات.' : 'Try changing your filter criteria or reset to view all available properties.' }}
                        </p>
                    </div>
                @else
                    <div class="darli-props-grid">
                        @foreach ($properties as $property)
                            @php
                                $tr = $property->translation($locale);
                                $isRent = $property->purpose === 'rent';
                                $slug = $tr?->slug;
                                $url = $slug ? route('properties.show', ['locale' => $locale, 'slug' => $slug]) : '#';
                                $image = $property->primaryImageUrl($locale);
                                $purposeLabel = $isRent
                                    ? ($isAr
                                        ? 'للإيجار'
                                        : 'For rent')
                                    : ($isAr
                                        ? 'للبيع'
                                        : 'For sale');
                                $purposeClass = $isRent ? 'rent' : 'sale';
                                $categoryName = $property->category?->translation($locale)?->name;
                                $locationName =
                                    $tr?->address_text ?:
                                    ($property->location?->translation($locale)?->name ??
                                        ($isAr ? 'بني سويف' : 'Beni Suef'));
                                $isContactPrice = $property->isPriceOnContact();
                                $isNegotiablePrice = $property->isNegotiable();
                                $formattedPrice = $isContactPrice
                                    ? ($isAr ? 'السعر عند التواصل' : 'Price on request')
                                    : (number_format((float) $property->price, 0) . ' ' . ($isAr ? 'جنيه' : 'EGP'));
                                $areaFormatted = $property->area
                                    ? rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.')
                                    : null;
                            @endphp

                            <div class="darli-prop-card" data-id="{{ $property->id }}"
                                data-purpose="{{ $property->purpose }}" data-lat="{{ $property->latitude }}"
                                data-lng="{{ $property->longitude }}">
                                {{-- Media Container --}}
                                <div class="darli-prop-card-media">
                                    <a href="{{ $url }}">
                                        <img src="{{ $image }}" alt="{{ $tr?->name }}" loading="lazy"
                                            onerror="this.onerror=null;this.src='{{ $property->placeholderUrl($locale) }}';">
                                    </a>

                                    {{-- Top-Left: Badges (Sold + Purpose) --}}
                                    <div class="darli-badges-top-left">
                                        @if ($property->isSold())
                                            <span class="darli-badge-sold">{{ $isAr ? 'تم البيع' : 'Sold' }}</span>
                                        @endif
                                        <span class="darli-badge-purpose {{ $purposeClass }}">{{ $purposeLabel }}</span>
                                    </div>

                                    {{-- Top-Right: Wishlist Heart --}}
                                    <button type="button" class="darli-badge-wishlist" data-id="{{ $property->id }}"
                                        title="{{ $isAr ? 'إضافة إلى المفضلة' : 'Add to wishlist' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        </svg>
                                    </button>

                                    {{-- Bottom-Left: Category Badge --}}
                                    @if ($categoryName)
                                        <span class="darli-badge-category">{{ $categoryName }}</span>
                                    @endif
                                </div>

                                {{-- Card Body --}}
                                <div class="darli-prop-card-body">
                                    {{-- Title --}}
                                    <a href="{{ $url }}" class="darli-prop-card-title"
                                        title="{{ $tr?->name }}">
                                        {{ $tr?->name ?? $property->reference_code }}
                                    </a>

                                    {{-- Location --}}
                                    <div class="darli-prop-card-loc">
                                        <svg class="darli-prop-loc-pin" xmlns="http://www.w3.org/2000/svg" width="14"
                                            height="14" viewBox="0 0 24 24" fill="currentColor">
                                            <path
                                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                                        </svg>
                                        <span>{{ $locationName }}</span>
                                    </div>

                                    {{-- Price --}}
                                    <div class="darli-prop-card-price d-flex align-items-center gap-2 flex-wrap">
                                        <span>{{ $formattedPrice }}</span>
                                        @if ($isRent && ! $isContactPrice)
                                            <span class="rent-period">{{ $isAr ? ' / شهرياً' : ' / mo' }}</span>
                                        @endif
                                        @if ($isNegotiablePrice)
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.15); color: #059669; font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                                {{ $isAr ? 'قابل للتفاوض' : 'Negotiable' }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="darli-prop-card-divider"></div>

                                    {{-- Meta Specs Row --}}
                                    <div class="darli-prop-card-meta">
                                        {{-- Area --}}
                                        <div class="darli-prop-meta-item" title="{{ $isAr ? 'المساحة' : 'Area' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path
                                                    d="M21.3 8.7 8.7 21.3c-1 1-2.5 1-3.4 0l-2.6-2.6c-1-1-1-2.5 0-3.4L15.3 2.7c1-1 2.5-1 3.4 0l2.6 2.6c1 1 1 2.5 0 3.4Z" />
                                                <path d="m7.5 10.5 2 2" />
                                                <path d="m10.5 7.5 2 2" />
                                            </svg>
                                            <span>{{ $areaFormatted ? $areaFormatted . ($isAr ? ' م²' : ' m²') : '—' }}</span>
                                        </div>

                                        {{-- Bedrooms --}}
                                        <div class="darli-prop-meta-item" title="{{ $isAr ? 'الغرف' : 'Bedrooms' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M2 4v16" />
                                                <path d="M2 8h18a2 2 0 0 1 2 2v10" />
                                                <path d="M2 17h20" />
                                                <path d="M6 8v9" />
                                            </svg>
                                            <span>
                                                @if ($property->bedrooms > 0)
                                                    {{ $property->bedrooms }} {{ $isAr ? 'حجر' : 'rooms' }}
                                                @else
                                                    {{ $isAr ? 'طابق أرضي' : 'Ground floor' }}
                                                @endif
                                            </span>
                                        </div>

                                        {{-- Bathrooms --}}
                                        <div class="darli-prop-meta-item" title="{{ $isAr ? 'الحمامات' : 'Bathrooms' }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 6 6.5 3.5a1.5 1.5 0 0 0-1-1.5C4 2 2 3.5 2 5.5V11" />
                                                <path d="M2 11h20v2a7 7 0 0 1-7 7H9a7 7 0 0 1-7-7v-2Z" />
                                                <path d="M7 20v2" />
                                                <path d="M17 20v2" />
                                            </svg>
                                            <span>{{ $property->bathrooms ?? 1 }} {{ $isAr ? 'حمام' : 'bath' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($properties->hasPages())
                        <div class="darli-pagination d-flex justify-content-center mt-4">
                            {{ $properties->links('vendor.pagination.darli') }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- ================= 2. Left Map Pane (Sticky Interactive Map) ================= --}}
            <div class="darli-split-map-pane">

                {{-- Desktop Floating Purpose Switch Card (Hidden on Mobile) --}}
                <div class="darli-map-purpose-card darli-desktop-only">
                    <div class="darli-map-purpose-item rent" data-purpose="rent"
                        title="{{ $isAr ? 'تصفية عقارات للإيجار' : 'Filter rent' }}">
                        <div class="darli-map-purpose-icon rent">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-map-purpose-text">
                            <span class="darli-map-purpose-title">{{ $isAr ? 'للإيجار' : 'For rent' }}</span>
                            <span
                                class="darli-map-purpose-sub">{{ $isAr ? 'عقارات للإيجار' : 'Properties for rent' }}</span>
                        </div>
                    </div>

                    <div class="darli-map-purpose-divider"></div>

                    <div class="darli-map-purpose-item sale" data-purpose="sale"
                        title="{{ $isAr ? 'تصفية عقارات للبيع' : 'Filter sale' }}">
                        <div class="darli-map-purpose-icon sale">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                            </svg>
                        </div>
                        <div class="darli-map-purpose-text">
                            <span class="darli-map-purpose-title">{{ $isAr ? 'للبيع' : 'For sale' }}</span>
                            <span
                                class="darli-map-purpose-sub">{{ $isAr ? 'منازل ومسكنات للبيع' : 'Houses for sale' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Map Frame Card (Styled as rounded card with floating controls on mobile) --}}
                <div class="darli-map-frame-card" id="darli-map-frame-card">
                    {{-- The Leaflet Map Element --}}
                    <div id="darli-properties-map"></div>

                    {{-- Mobile Floating Layer Switcher (Top-Left) --}}
                    <button type="button" class="darli-mob-ctrl-btn darli-mob-btn-layer" id="darli-mob-layer-btn"
                        title="{{ $isAr ? 'نوع الخريطة' : 'Map type' }}" aria-label="نوع الخريطة">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e293b"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                    </button>
                    <div id="darli-mob-layer-popover" class="darli-mob-layer-popover">
                        <div class="darli-mob-layer-opt active" data-layer="satellite">
                            <span class="darli-mob-layer-dot"></span>
                            <span>{{ $isAr ? 'قمر صناعي (افتراضي)' : 'Satellite' }}</span>
                        </div>
                        <div class="darli-mob-layer-opt" data-layer="light">
                            <span class="darli-mob-layer-dot"></span>
                            <span>{{ $isAr ? 'خريطة شوارع' : 'Street map' }}</span>
                        </div>
                        <div class="darli-mob-layer-opt" data-layer="dark">
                            <span class="darli-mob-layer-dot"></span>
                            <span>{{ $isAr ? 'الوضع الليلي' : 'Dark mode' }}</span>
                        </div>
                    </div>

                    {{-- Mobile Floating Fullscreen Button (Top-Right) --}}
                    <button type="button" class="darli-mob-ctrl-btn darli-mob-btn-fullscreen" id="darli-mob-fullscreen-btn"
                        title="{{ $isAr ? 'ملء الشاشة' : 'Fullscreen' }}" aria-label="ملء الشاشة">
                        <svg class="icon-expand" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="#1e293b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3" />
                        </svg>
                        <svg class="icon-compress" style="display:none;" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="#1e293b" stroke-width="2.2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M4 14h6v6m10-10h-6V4m0 6 7-7M3 21l7-7" />
                        </svg>
                    </button>
                </div>

                {{-- Desktop Bottom Floating Layer Switcher (Hidden on Mobile) --}}
                <div class="darli-map-btn-type-wrap darli-desktop-only">
                    <button type="button" id="darli-map-btn-type" class="darli-map-btn-type">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                        <span>{{ $isAr ? 'نوع الخريطة' : 'Map type' }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div id="darli-map-layer-menu" class="darli-map-layer-menu">
                        <div class="darli-map-layer-item" data-layer="light">
                            {{ $isAr ? 'خريطة شوارع' : 'Street map' }}</div>
                        <div class="darli-map-layer-item active" data-layer="satellite">{{ $isAr ? 'قمر صناعي' : 'Satellite' }}
                        </div>
                        <div class="darli-map-layer-item" data-layer="dark">{{ $isAr ? 'الوضع الليلي' : 'Dark mode' }}
                        </div>
                    </div>
                </div>

                {{-- Desktop Bottom Floating Area Button (Hidden on Mobile) --}}
                <button type="button" id="darli-map-btn-area" class="darli-map-btn-area darli-desktop-only">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="currentColor" style="color: var(--darli-gold);">
                        <path
                            d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z" />
                    </svg>
                    <span>{{ $isAr ? 'عرض العقارات في هذه المنطقة' : 'Show properties in this area' }}</span>
                </button>

                {{-- ================= Mobile View Elements (Shown only on Mobile) ================= --}}
                <div class="darli-mob-elements-container">

                    {{-- 1. Mobile Purpose Segmented Tabs (للبيع / للإيجار) --}}
                    <div class="darli-mob-purpose-wrap">
                        <div class="darli-mob-purpose-pills">
                            <button type="button"
                                class="darli-mob-purpose-pill {{ (request('purpose', 'sale') === 'sale' || empty(request('purpose'))) ? 'active' : '' }}"
                                data-purpose="sale">
                                {{ $isAr ? 'للبيع' : 'For sale' }}
                            </button>
                            <button type="button"
                                class="darli-mob-purpose-pill {{ request('purpose') === 'rent' ? 'active' : '' }}"
                                data-purpose="rent">
                                {{ $isAr ? 'للإيجار' : 'For rent' }}
                            </button>
                        </div>
                    </div>

                    {{-- 2. Mobile Action Buttons (عرض الخريطة & فلترة متقدمة) --}}
                    <div class="darli-mob-actions-row">
                        <button type="button" class="darli-mob-action-btn" id="darli-mob-show-map-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e293b"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                                <line x1="8" y1="2" x2="8" y2="18"></line>
                                <line x1="16" y1="6" x2="16" y2="22"></line>
                            </svg>
                            <span>{{ $isAr ? 'عرض الخريطة' : 'View Map' }}</span>
                        </button>
                        <button type="button" class="darli-mob-action-btn" id="darli-mob-adv-filter-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1e293b"
                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="7" x2="21" y2="7"></line>
                                <circle cx="16" cy="7" r="2.5" fill="#ffffff" stroke="#1e293b" stroke-width="2"></circle>
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <circle cx="8" cy="12" r="2.5" fill="#ffffff" stroke="#1e293b" stroke-width="2"></circle>
                                <line x1="3" y1="17" x2="21" y2="17"></line>
                                <circle cx="15" cy="17" r="2.5" fill="#ffffff" stroke="#1e293b" stroke-width="2"></circle>
                            </svg>
                            <span>{{ $isAr ? 'فلترة متقدمة' : 'Advanced filter' }}</span>
                        </button>
                    </div>

                    {{-- 3. Mobile Centered Section Header --}}
                    <div class="darli-mob-hero-text">
                        <h1 class="darli-mob-title">
                            {{ $isAr ? 'العقارات في ' . ($currentCity ?? 'بني سويف') : 'Properties in ' . ($currentCity ?? 'Beni Suef') }}
                        </h1>
                        <p class="darli-mob-subtitle">
                            {{ $isAr ? 'اكتشف أفضل العقارات للبيع والإيجار في ' . ($currentCity ?? 'بني سويف') . ' ومحيطها' : 'Discover the best properties for sale and rent in ' . ($currentCity ?? 'Beni Suef') . ' and surroundings' }}
                        </p>
                    </div>

                    {{-- 4. Mobile Quick Search Card --}}
                    <div class="darli-mob-search-card">
                        <form id="darli-mob-search-form" method="get"
                            action="{{ route('properties.index', ['locale' => $locale]) }}">
                            <input type="hidden" name="purpose" id="darli-mob-input-purpose"
                                value="{{ request('purpose', 'sale') }}">
                            <input type="hidden" name="sort" value="{{ request('sort', 'newest') }}">

                            <div class="darli-mob-selects-grid">
                                {{-- Location Selector (Right in RTL) --}}
                                <div class="darli-mob-custom-select-box" id="darli-mob-location-box">
                                    <div class="select-icon">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                            stroke="#c28b2e" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </div>
                                    <div class="select-text">
                                        <span
                                            class="select-label">{{ $isAr ? 'المدينة / المنطقة' : 'City / Area' }}</span>
                                        <span class="select-value"
                                            id="darli-mob-location-display">{{ $currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef') }}</span>
                                    </div>
                                    <div class="select-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </div>
                                    <select name="location_id" id="darli-mob-location-select"
                                        class="darli-mob-overlay-select"
                                        aria-label="{{ $isAr ? 'المدينة / المنطقة' : 'City / Area' }}">
                                        <option value="">{{ $isAr ? 'كل المناطق' : 'All areas' }}</option>
                                        @foreach ($locations as $loc)
                                            <option value="{{ $loc->id }}" @selected((string) request('location_id') === (string) $loc->id)>
                                                {{ $loc->translation($locale)?->name ?? $loc->id }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Property Type Selector (Left in RTL) --}}
                                <div class="darli-mob-custom-select-box" id="darli-mob-category-box">
                                    <div class="select-icon">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                            stroke="#c28b2e" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                        </svg>
                                    </div>
                                    <div class="select-text">
                                        <span class="select-label">{{ $isAr ? 'نوع العقار' : 'Property type' }}</span>
                                        @php
                                            $activeCat = !empty(request('category_id'))
                                                ? $categories->firstWhere('id', (int) request('category_id'))
                                                : null;
                                            $activeCatName = $activeCat
                                                ? $activeCat->translation($locale)?->name ?? $activeCat->key
                                                : ($isAr
                                                    ? 'أي نوع'
                                                    : 'Any type');
                                        @endphp
                                        <span class="select-value"
                                            id="darli-mob-category-display">{{ $activeCatName }}</span>
                                    </div>
                                    <div class="select-arrow">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </div>
                                    <select name="category_id" id="darli-mob-category-select"
                                        class="darli-mob-overlay-select"
                                        aria-label="{{ $isAr ? 'نوع العقار' : 'Property type' }}">
                                        <option value="">{{ $isAr ? 'أي نوع' : 'Any type' }}</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                                                {{ $cat->translation($locale)?->name ?? $cat->key }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Submit Search Button --}}
                            <button type="submit" class="darli-mob-search-submit-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span>{{ $isAr ? 'بحث عن عقارات' : 'Search properties' }}</span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {{-- Advanced Filter Bottom Sheet Modal (Mobile) --}}
    <div class="darli-mob-sheet-backdrop" id="darli-mob-filter-backdrop"></div>
    <div class="darli-mob-filter-sheet" id="darli-mob-filter-sheet">
        <div class="sheet-drag-handle"></div>
        <div class="sheet-header">
            <h3 class="sheet-title">{{ $isAr ? 'فلترة متقدمة' : 'Advanced Filters' }}</h3>
            <button type="button" class="sheet-close-btn" id="darli-mob-filter-close" aria-label="إغلاق">&times;</button>
        </div>
        <div class="sheet-body">
            <form id="darli-mob-adv-filter-form" method="get"
                action="{{ route('properties.index', ['locale' => $locale]) }}">
                <input type="hidden" name="purpose" id="darli-adv-purpose"
                    value="{{ request('purpose', 'sale') }}">

                {{-- Keyword --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'البحث بالكلمة' : 'Keyword search' }}</label>
                    <input type="text" name="q" value="{{ request('q', request('k')) }}"
                        placeholder="{{ $isAr ? 'اسم العقار، الحي، الكود...' : 'Property name, area, code...' }}"
                        class="sheet-input">
                </div>

                {{-- Location --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'المنطقة / الحي' : 'Location / District' }}</label>
                    <select name="location_id" class="sheet-select">
                        <option value="">{{ $isAr ? 'كل المناطق' : 'All areas' }}</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" @selected((string) request('location_id') === (string) $loc->id)>
                                {{ $loc->translation($locale)?->name ?? $loc->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Category --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'نوع العقار' : 'Property type' }}</label>
                    <select name="category_id" class="sheet-select">
                        <option value="">{{ $isAr ? 'الكل' : 'All' }}</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>
                                {{ $cat->translation($locale)?->name ?? $cat->key }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Price Range --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'نطاق السعر (جنيه)' : 'Price range (EGP)' }}</label>
                    <div class="sheet-row-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}"
                            placeholder="{{ $isAr ? 'الحد الأدنى' : 'Min' }}" class="sheet-input">
                        <input type="number" name="max_price" value="{{ request('max_price') }}"
                            placeholder="{{ $isAr ? 'الحد الأقصى' : 'Max' }}" class="sheet-input">
                    </div>
                </div>

                {{-- Bedrooms --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'عدد الغرف' : 'Bedrooms' }}</label>
                    <div class="sheet-chips" data-target="bedrooms">
                        <button type="button" class="sheet-chip {{ empty(request('bedrooms')) ? 'active' : '' }}"
                            data-val="">{{ $isAr ? 'الكل' : 'Any' }}</button>
                        @foreach ([1, 2, 3, 4, 5] as $n)
                            <button type="button"
                                class="sheet-chip {{ (string) request('bedrooms') === (string) $n ? 'active' : '' }}"
                                data-val="{{ $n }}">{{ $n }}{{ $n === 5 ? '+' : '' }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="bedrooms" id="darli-adv-bedrooms"
                        value="{{ request('bedrooms') }}">
                </div>

                {{-- Bathrooms --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'عدد الحمامات' : 'Bathrooms' }}</label>
                    <div class="sheet-chips" data-target="bathrooms">
                        <button type="button" class="sheet-chip {{ empty(request('bathrooms')) ? 'active' : '' }}"
                            data-val="">{{ $isAr ? 'الكل' : 'Any' }}</button>
                        @foreach ([1, 2, 3, 4] as $n)
                            <button type="button"
                                class="sheet-chip {{ (string) request('bathrooms') === (string) $n ? 'active' : '' }}"
                                data-val="{{ $n }}">{{ $n }}{{ $n === 4 ? '+' : '' }}</button>
                        @endforeach
                    </div>
                    <input type="hidden" name="bathrooms" id="darli-adv-bathrooms"
                        value="{{ request('bathrooms') }}">
                </div>

                {{-- Sort --}}
                <div class="sheet-field">
                    <label class="sheet-label">{{ $isAr ? 'الترتيب حسب' : 'Sort by' }}</label>
                    <select name="sort" class="sheet-select">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>
                            {{ $isAr ? 'الأحدث أولاً' : 'Newest first' }}</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>
                            {{ $isAr ? 'السعر: من الأقل للأعلى' : 'Price: Low to High' }}</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>
                            {{ $isAr ? 'السعر: من الأعلى للأقل' : 'Price: High to Low' }}</option>
                        <option value="popular" @selected(request('sort') === 'popular')>
                            {{ $isAr ? 'الأكثر شعبية' : 'Most popular' }}</option>
                    </select>
                </div>

                <div class="sheet-actions">
                    <a href="{{ route('properties.index', ['locale' => $locale]) }}"
                        class="sheet-btn-reset">{{ $isAr ? 'إعادة تعيين' : 'Reset' }}</a>
                    <button type="submit" class="sheet-btn-apply">{{ $isAr ? 'تطبيق الفلترة' : 'Apply Filters' }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Map Data & Scripts --}}
    <script>
        window.__DARLI_MAP_PROPERTIES__ = @json($mapProperties);
        window.__DARLI_CURRENT_CITY__ = @json($currentCity ?? ($isAr ? 'بني سويف' : 'Beni Suef'));
    </script>
    <script src="{{ asset('themes/homzen/plugins/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('themes/homzen/js/darli-properties-map.js') }}?v=1.0.6"></script>
@endsection
