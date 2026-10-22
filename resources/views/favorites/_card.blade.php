@php
    $tr = $property->translation($locale);
    $name = $tr?->name ?: ($property->reference_code ?: '#' . $property->id);
    $slug = $tr?->slug ?: $property->reference_code;
    $image = $property->primaryImageUrl($locale);
    $location = $property->location?->translation($locale)?->name;
    $address = $tr?->address_text ?: $location;
    $purpose = $property->purpose === 'rent'
        ? ($isAr ? 'للإيجار' : 'For rent')
        : ($isAr ? 'للبيع' : 'For sale');
    $symbol = $property->currency?->symbol ?: ($isAr ? 'ج.م' : 'EGP');
    $price = $property->price
        ? number_format((float) $property->price, 0) . ' ' . $symbol
        : ($isAr ? 'السعر عند التواصل' : 'Price on request');
    if ($property->price && $property->purpose === 'rent') {
        $price .= $isAr ? ' / شهرياً' : ' / mo';
    }
    $url = route('properties.show', ['locale' => $locale, 'slug' => $slug]);
    $area = $property->area ? rtrim(rtrim(number_format((float) $property->area, 2), '0'), '.') : null;
@endphp

<div class="col-md-6 col-lg-4" data-favorite-card="{{ $property->id }}">
    <article class="darli-fav-card">
        <div class="darli-fav-card__media">
            <a href="{{ $url }}" aria-label="{{ $name }}">
                <img src="{{ $image }}" alt="{{ $name }}" loading="lazy">
            </a>
            <span class="darli-fav-card__purpose">{{ $purpose }}</span>
            <button type="button" class="darli-fav-card__heart is-active"
                data-bb-toggle="add-to-wishlist" data-type="property" data-id="{{ $property->id }}"
                data-add-message="{{ $isAr ? 'تمت الإضافة إلى المفضلة' : 'Added to favorites' }}"
                data-remove-message="{{ $isAr ? 'تمت الإزالة من المفضلة' : 'Removed from favorites' }}"
                aria-pressed="true" aria-label="{{ $isAr ? 'إزالة من المفضلة' : 'Remove from favorites' }}"
                title="{{ $isAr ? 'إزالة من المفضلة' : 'Remove from favorites' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572" />
                </svg>
            </button>
        </div>

        <div class="darli-fav-card__body">
            <h3 class="darli-fav-card__title"><a href="{{ $url }}">{{ $name }}</a></h3>

            @if ($address)
                <p class="darli-fav-card__loc">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                        <path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" />
                    </svg>
                    <span>{{ $address }}</span>
                </p>
            @endif

            <ul class="darli-fav-card__meta">
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M7 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M22 17v-3h-20" />
                        <path d="M2 8v9" />
                        <path d="M12 14h10v-2a3 3 0 0 0 -3 -3h-7v5z" />
                    </svg>
                    <span>{{ $property->bedrooms ?? '—' }}</span>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 12h16a1 1 0 0 1 1 1v3a4 4 0 0 1 -4 4h-10a4 4 0 0 1 -4 -4v-3a1 1 0 0 1 1 -1z" />
                        <path d="M6 12v-7a2 2 0 0 1 2 -2h3v2.25" />
                    </svg>
                    <span>{{ $property->bathrooms ?? '—' }}</span>
                </li>
                <li>
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 4h14a1 1 0 0 1 1 1v5a1 1 0 0 1 -1 1h-7a1 1 0 0 0 -1 1v7a1 1 0 0 1 -1 1h-5a1 1 0 0 1 -1 -1v-14a1 1 0 0 1 1 -1" />
                    </svg>
                    <span>{{ $area ? $area . ' ' . ($isAr ? 'م²' : 'm²') : '—' }}</span>
                </li>
            </ul>

            <div class="darli-fav-card__footer">
                <span class="darli-fav-card__price">{{ $price }}</span>
                <a href="{{ $url }}" class="darli-fav-card__details">
                    {{ $isAr ? 'التفاصيل' : 'Details' }}
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12l14 0" /><path d="M13 18l6 -6" /><path d="M13 6l6 6" />
                    </svg>
                </a>
            </div>
        </div>
    </article>
</div>
