@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $tr = $agent->translation($locale);
    $name = $tr?->display_name ?: $agent->user?->name;
    $avatarUrl = $agent->user?->avatar_url;
    if (!$avatarUrl || str_contains($avatarUrl, 'placeholder')) {
        $avatarUrl = asset($agent->avatar_path ?: 'storage/avatars/1.jpg');
    }
@endphp

@section('title', $name)

@section('content')
    <style>
        .agent-detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 32px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: background-color 0.2s, border-color 0.2s;
        }
        .agent-detail-title {
            color: #0f172a !important;
            font-size: 26px !important;
            font-weight: 800 !important;
            margin-bottom: 6px !important;
        }
        .agent-detail-subtitle {
            color: #64748b !important;
            font-size: 15px !important;
            margin-bottom: 16px !important;
        }
        .agent-detail-facts {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .agent-detail-fact {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            min-width: 130px;
        }
        .agent-detail-fact-label {
            color: #64748b !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            margin-bottom: 2px;
        }
        .agent-detail-fact-value {
            color: #0f172a !important;
            font-size: 18px !important;
            font-weight: 800 !important;
        }
        .agent-detail-bio {
            color: #334155 !important;
            font-size: 15px !important;
            line-height: 1.85 !important;
        }
        html[data-theme="dark"] .agent-detail-card {
            background: #0e1e34 !important;
            border-color: #1a3152 !important;
        }
        html[data-theme="dark"] .agent-detail-title,
        html[data-theme="dark"] .agent-detail-fact-value {
            color: #ffffff !important;
        }
        html[data-theme="dark"] .agent-detail-subtitle,
        html[data-theme="dark"] .agent-detail-fact-label {
            color: #94a3b8 !important;
        }
        html[data-theme="dark"] .agent-detail-fact {
            background: #081528 !important;
            border-color: #1a3152 !important;
        }
        html[data-theme="dark"] .agent-detail-bio {
            color: #cbd5e1 !important;
        }
    </style>

    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-4">
        <div class="container">
            {{-- مسار التنقل --}}
            <div class="mb-3 darli-muted" style="font-size:14px;">
                <a href="{{ route('home', ['locale' => $locale]) }}">{{ $isAr ? 'الرئيسية' : 'Home' }}</a>
                <span> › </span>
                <a href="{{ route('agents.index', ['locale' => $locale]) }}">{{ $isAr ? 'الوكلاء والشركات' : 'Agents & Partners' }}</a>
                <span> › </span>
                <span>{{ $name }}</span>
            </div>

            {{-- بطاقة السمسار / الشركة --}}
            <div class="agent-detail-card">
                <div style="display:flex;gap:26px;align-items:flex-start;flex-wrap:wrap;">
                    <div style="width:120px;height:120px;border-radius:18px;overflow:hidden;flex-shrink:0;border:2px solid #e2e8f0;background:#f8fafc;display:flex;align-items:center;justify-content:center;">
                        <img src="{{ $avatarUrl }}" alt="{{ $name }}"
                            onerror="this.onerror=null;this.src='{{ asset('themes/homzen/images/darli-placeholder.png') }}';"
                            style="width:100%;height:100%;object-fit:cover;">
                    </div>

                    <div style="flex:1;min-width:260px;">
                        <div class="d-flex align-items-center gap-2 flex-wrap" style="margin-bottom: 4px;">
                            <h1 class="agent-detail-title">{{ $name }}</h1>
                            <span class="badge" style="background:#c39953;color:#ffffff;font-size:12px;padding:4px 10px;border-radius:6px;">
                                {{ $agent->city }}
                            </span>
                            @if ($agent->isVerified())
                                <span class="badge bg-success" style="font-size:12px;padding:4px 10px;border-radius:6px;">
                                    ✓ معتمد
                                </span>
                            @endif
                        </div>

                        <p class="agent-detail-subtitle">
                            {{ $tr?->job_title ?: ($agent->job_title ?: ($isAr ? 'شركة وساطة وتطوير عقاري' : 'Real estate company')) }}
                            @if ($agent->agency)
                                — {{ $agent->agency->name }}
                            @endif
                        </p>

                        <div class="agent-detail-facts">
                            @if ($agent->years_experience)
                                <div class="agent-detail-fact">
                                    <div class="agent-detail-fact-label">{{ $isAr ? 'سنوات الخبرة' : 'Years of experience' }}</div>
                                    <div class="agent-detail-fact-value">{{ $agent->years_experience }} سنة</div>
                                </div>
                            @endif
                            <div class="agent-detail-fact">
                                <div class="agent-detail-fact-label">{{ $isAr ? 'العقارات المعروضة' : 'Active listings' }}</div>
                                <div class="agent-detail-fact-value">{{ number_format($properties->count()) }}</div>
                            </div>
                            @if ($agent->license_number)
                                <div class="agent-detail-fact">
                                    <div class="agent-detail-fact-label">{{ $isAr ? 'رقم الترخيص' : 'Licence number' }}</div>
                                    <div class="agent-detail-fact-value" dir="ltr">{{ $agent->license_number }}</div>
                                </div>
                            @endif
                        </div>

                        <div style="display:flex;gap:12px;flex-wrap:wrap;">
                            @if ($agent->whatsapp || $agent->user?->phone)
                                <a class="darli-whatsapp" dir="ltr" target="_blank" rel="noopener"
                                    href="https://wa.me/{{ preg_replace('/\D/', '', $agent->whatsapp ?: $agent->user->phone) }}"
                                    style="padding:10px 20px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                    💬 {{ $isAr ? 'مراسلة واتساب' : 'WhatsApp' }}
                                </a>
                            @endif
                            @if ($agent->user?->phone)
                                <a class="tf-btn size-1" dir="ltr" href="tel:{{ $agent->user->phone }}"
                                    style="padding:10px 20px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                    📞 {{ $agent->user->phone }}
                                </a>
                            @endif
                            @if ($agent->user?->email)
                                <a class="tf-btn size-1" dir="ltr" href="mailto:{{ $agent->user->email }}"
                                    style="padding:10px 20px;border-radius:8px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                    ✉️ {{ $agent->user->email }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($tr?->bio || $agent->bio)
                    <hr style="border:0;border-top:1px solid #e9eef1;margin:24px 0;">
                    <h2 class="h6 fw-7" style="margin-bottom:10px;color:var(--ap-gold,#c39953);">{{ $isAr ? 'نبذة عن الشركة / الوكيل' : 'About the Agency' }}</h2>
                    <div class="agent-detail-bio">{!! nl2br(e($tr?->bio ?: $agent->bio)) !!}</div>
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
