@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $tr = $agent->translation($locale);
    $name = $tr?->display_name ?: $agent->user?->name;
@endphp

@section('title', $name)

@section('content')
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-4">
        <div class="container">
            {{-- مسار التنقل --}}
            <div class="mb-3 darli-muted" style="font-size:14px;">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                <span> › </span>
                <a href="{{ route('agents.index', ['locale' => $locale]) }}">{{ $isAr ? 'السماسرة' : 'Agents' }}</a>
                <span> › </span>
                <span>{{ $name }}</span>
            </div>

            {{-- بطاقة السمسار --}}
            <div class="darli-panel darli-panel--pad" style="margin-bottom:28px;">
                <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap;">
                    <img src="{{ $agent->user?->avatar_url }}" alt="{{ $name }}"
                        style="width:140px;height:140px;border-radius:50%;object-fit:cover;flex:0 0 auto;">

                    <div style="flex:1;min-width:260px;">
                        <h1 class="h4 fw-7" style="margin-bottom:6px;">{{ $name }}</h1>
                        <p class="darli-muted" style="margin-bottom:12px;">
                            {{ $tr?->job_title ?: ($agent->job_title ?: ($isAr ? 'سمسار عقارات' : 'Real estate agent')) }}
                            @if ($agent->agency)
                                — {{ $agent->agency->name }}
                            @endif
                        </p>

                        <div class="darli-facts" style="margin-bottom:16px;">
                            @if ($agent->years_experience)
                                <div class="darli-fact">
                                    <div class="darli-fact__label">{{ $isAr ? 'سنوات الخبرة' : 'Years of experience' }}</div>
                                    <div class="darli-fact__value">{{ $agent->years_experience }}</div>
                                </div>
                            @endif
                            <div class="darli-fact">
                                <div class="darli-fact__label">{{ $isAr ? 'العقارات المعروضة' : 'Active listings' }}</div>
                                <div class="darli-fact__value">{{ number_format($properties->count()) }}</div>
                            </div>
                            @if ($agent->license_number)
                                <div class="darli-fact">
                                    <div class="darli-fact__label">{{ $isAr ? 'رقم الترخيص' : 'Licence number' }}</div>
                                    <div class="darli-fact__value" dir="ltr">{{ $agent->license_number }}</div>
                                </div>
                            @endif
                        </div>

                        <div style="display:flex;gap:10px;flex-wrap:wrap;">
                            @if ($agent->whatsapp || $agent->user?->phone)
                                <a class="darli-whatsapp" dir="ltr"
                                    href="https://wa.me/{{ preg_replace('/\D/', '', $agent->whatsapp ?: $agent->user->phone) }}">
                                    {{ $isAr ? 'واتساب' : 'WhatsApp' }}
                                </a>
                            @endif
                            @if ($agent->user?->phone)
                                <a class="tf-btn size-1" dir="ltr" href="tel:{{ $agent->user->phone }}">{{ $agent->user->phone }}</a>
                            @endif
                            @if ($agent->user?->email)
                                <a class="tf-btn size-1" dir="ltr" href="mailto:{{ $agent->user->email }}">{{ $agent->user->email }}</a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($tr?->bio || $agent->bio)
                    <hr style="border:0;border-top:1px solid #e9eef1;margin:20px 0;">
                    <h2 class="h6 fw-7" style="margin-bottom:8px;">{{ $isAr ? 'نبذة' : 'About' }}</h2>
                    <div style="line-height:1.9;">{!! nl2br(e($tr?->bio ?: $agent->bio)) !!}</div>
                @endif
            </div>

            {{-- عقارات السمسار --}}
            <div class="box-title-listing">
                <div class="h5 fw-7">{{ $isAr ? 'عقارات هذا السمسار' : 'Listings by this agent' }}</div>
            </div>

            @if ($properties->isEmpty())
                <div class="darli-panel darli-empty">
                    <p class="mb-0">{{ $isAr ? 'لا توجد عقارات منشورة لهذا السمسار حالياً.' : 'This agent has no published listings yet.' }}</p>
                </div>
            @else
                <div class="darli-grid darli-grid--3">
                    @foreach ($properties as $property)
                        <div>
                            <x-legacy-property-card :property="$property" :locale="$locale" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
