@props(['property'])
@php
    $tr = $property->translation();
    $placeholderUrl = $property->placeholderUrl();
    $img = $property->media->first()?->path;
@endphp
<div class="card-property">
    <div class="position-relative"><img
            src="{{ $img ? asset('legacy/' . $img) : $placeholderUrl }}"
            onerror="this.onerror=null;this.src='{{ $placeholderUrl }}';"
            alt="{{ $tr?->name }}">
        <div class="position-absolute top-0 start-0 m-2 d-flex gap-1">
            @if ($property->isSold())
                <span class="badge bg-success" style="background-color: #10b981 !important;">{{ app()->getLocale() === 'ar' ? 'تم البيع' : 'Sold' }}</span>
            @endif
            <span class="badge bg-primary badge-purpose">{{ strtoupper($property->purpose) }}</span>
        </div>
    </div>
    <div class="p-3">
        <div class="small text-muted">{{ $property->reference_code }}</div>
        <h5 class="mt-1"><a class="text-decoration-none text-dark"
                href="{{ route('properties.show', ['locale' => app()->getLocale(), 'slug' => $tr?->slug]) }}">{{ $tr?->name }}</a>
        </h5>
        <div class="text-muted small mb-2">{{ $tr?->address_text }}</div>
        <div class="d-flex gap-3 small"><span>🛏 {{ $property->bedrooms ?? '—' }}</span><span>🛁
                {{ $property->bathrooms ?? '—' }}</span><span>📐 {{ $property->area ?? '—' }} m²</span></div>
        <div class="fw-bold fs-5 mt-3 d-flex align-items-center gap-2 flex-wrap">
            @if ($property->isPriceOnContact())
                <span>{{ app()->getLocale() === 'ar' ? 'السعر عند التواصل' : 'Price on request' }}</span>
            @else
                <span>
                    @if(($property->currency?->code ?? 'EGP') === 'EGP')
                        {{ number_format((float) $property->price, 0) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}
                    @elseif(($property->currency?->symbol_position ?? 'before') === 'after')
                        {{ number_format((float) $property->price, 0) }} {{ $property->currency?->symbol }}
                    @else
                        {{ $property->currency?->symbol }}{{ number_format((float) $property->price, 0) }}
                    @endif
                </span>
                @if ($property->isNegotiable())
                    <span class="badge bg-success-subtle text-success fs-6" style="font-size: 11px !important; font-weight: 600;">
                        {{ app()->getLocale() === 'ar' ? 'قابل للتفاوض' : 'Negotiable' }}
                    </span>
                @endif
            @endif
        </div>
    </div>
</div>
