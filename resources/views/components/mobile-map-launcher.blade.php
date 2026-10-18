@php
    $isAr = app()->getLocale() === 'ar';
    $isPropertiesPage = request()->routeIs('properties.index');
    $buttonUrl = $isPropertiesPage
        ? route('properties.index', array_merge(request()->query(), ['map' => 1]))
        : route('properties.index');
    $buttonLabel = $isPropertiesPage
        ? ($isAr ? 'عرض الخريطة' : 'View map')
        : ($isAr ? 'عرض جميع العقارات' : 'View all properties');
@endphp

<a href="{{ $buttonUrl }}" class="darli-mobile-map-launcher"
    aria-label="{{ $isPropertiesPage ? ($isAr ? 'عرض الخريطة بحجم الشاشة' : 'Open full-screen map') : $buttonLabel }}">
    <span>{{ $buttonLabel }}</span>
    @if ($isPropertiesPage)
        <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21 3 6"></polygon>
            <line x1="9" y1="3" x2="9" y2="18"></line>
            <line x1="15" y1="6" x2="15" y2="21"></line>
        </svg>
    @else
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 11.5 12 4l9 7.5"></path>
            <path d="M5.5 10.5V20h13v-9.5"></path>
            <path d="M9 20v-6h6v6"></path>
        </svg>
    @endif
</a>

<style>
    .darli-mobile-map-launcher {
        display: none;
    }

    @media (max-width: 991px) {
        .darli-mobile-map-launcher {
            position: fixed;
            left: 50%;
            bottom: calc(env(safe-area-inset-bottom, 0px) + 24px);
            z-index: 999998;
            min-height: 46px;
            padding: 0 17px;
            border: 1px solid rgba(201, 154, 69, 0.5);
            border-radius: 999px;
            background: linear-gradient(135deg, #0d2d50 0%, #071d36 100%);
            color: #ffffff !important;
            box-shadow: 0 8px 22px rgba(7, 29, 54, 0.38);
            transform: translateX(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Cairo', 'Almarai', sans-serif;
            font-size: 13px;
            font-weight: 800;
            line-height: 1;
            text-decoration: none !important;
            white-space: nowrap;
            -webkit-tap-highlight-color: transparent;
        }

        .darli-mobile-map-launcher svg {
            color: #d5a447;
            flex: 0 0 auto;
        }

        .darli-mobile-map-launcher:active {
            transform: translateX(-50%) scale(0.96);
        }

        body.darli-map-fullscreen .darli-mobile-map-launcher,
        body.darli-wizard-open .darli-mobile-map-launcher {
            display: none !important;
        }
    }

    @media (max-width: 370px) {
        .darli-mobile-map-launcher {
            padding-inline: 12px;
            font-size: 12px;
        }
    }
</style>
