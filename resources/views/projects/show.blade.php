@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $tr = $project->translation($locale);
    $projectLocation = $project->location?->translation($locale)?->name;

    /** 88.00 -> 88 ، 88.50 -> 88.5 ، null -> — */
    $num = fn ($value): string => $value === null || $value === ''
        ? '—'
        : rtrim(rtrim(number_format((float) $value, 2), '0'), '.');

    $date = fn ($value): string => $value ? $value->format('Y/m/d') : '—';

    /** Arabic status labels come from the shared helper; English from a tiny map. */
    $statusText = function ($status) use ($isAr): string {
        if ($isAr) {
            return status_label($status);
        }

        $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return match ($value) {
            'available' => 'Available',
            'reserved' => 'Reserved',
            'sold' => 'Sold',
            'rented' => 'Rented',
            'pending' => 'Pending',
            'draft' => 'Draft',
            'published' => 'Published',
            'inactive' => 'Inactive',
            '' => '—',
            default => ucfirst(str_replace('_', ' ', $value)),
        };
    };

    $placeholder = $isAr
        ? asset('themes/homzen/images/darli-placeholder-ar.png')
        : asset('themes/homzen/images/darli-placeholder-en.png');

    // المشروعات لا تملك جدول صور مستقل، فنستخدم أول صورة عقار داخل المشروع.
    $heroImage = $project->properties->first()?->primaryImageUrl($locale) ?? $placeholder;

    $phone = trim((string) setting('contact.phone', ''));
    $whatsapp = trim((string) setting('contact.whatsapp', ''));
    $whatsappDigits = preg_replace('/\D+/', '', $whatsapp) ?? '';
    $email = trim((string) setting('contact.email', ''));

    $hasCoords = $project->latitude !== null && $project->longitude !== null
        && $project->latitude !== '' && $project->longitude !== '';
    $coords = $hasCoords ? $project->latitude . ',' . $project->longitude : '';
    $showMap = (bool) setting('maps.google_enabled', false)
        && trim((string) setting('maps.google_api_key', '')) !== ''
        && $hasCoords;

    $facts = [
        ['icon' => 'icon-building', 'label' => $isAr ? 'عدد العمارات' : 'Blocks', 'value' => $num($project->blocks_count)],
        ['icon' => 'icon-home', 'label' => $isAr ? 'عدد الوحدات' : 'Total units', 'value' => $num($project->flats_count)],
        ['icon' => 'icon-stairs', 'label' => $isAr ? 'عدد الأدوار' : 'Floors', 'value' => $num($project->floors_count)],
        ['icon' => 'icon-calendar', 'label' => $isAr ? 'تاريخ فتح البيع' : 'Sales open', 'value' => $date($project->open_sale_date)],
        ['icon' => 'icon-calendar', 'label' => $isAr ? 'تاريخ التسليم' : 'Delivery date', 'value' => $date($project->completion_date)],
        ['icon' => 'icon-mapPin', 'label' => $isAr ? 'المطوّر' : 'Developer', 'value' => $project->investor_name ?: '—'],
    ];
@endphp

@section('title', ($tr?->seo_title ?: $tr?->name ?: $project->reference_code) . ($isAr ? ' | دارلي' : ' | Darlii'))

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
                        <a href="{{ route('projects.index', ['locale' => $locale]) }}">{{ $isAr ? 'المشروعات' : 'Projects' }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $tr?->name }}</li>
                </ol>
            </nav>
        </div>
    </section>

    {{-- ===================== صورة المشروع والعنوان ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="p-16 p-4 bg-white rounded-3 box-shadow-1">
                <div class="images-group">
                    <div class="images-style rounded-3">
                        <img src="{{ $heroImage }}" alt="{{ $tr?->name }}" class="w-100 d-block"
                            style="aspect-ratio:16/9;object-fit:cover;"
                            onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                    </div>
                    <div class="position-absolute top-0 start-0 p-3 d-flex gap-8 flex-wrap">
                        @if ($project->is_featured)
                            <span class="flag-tag success">{{ $isAr ? 'مميز' : 'Featured' }}</span>
                        @endif
                        <span class="flag-tag primary">{{ $isAr ? 'مشروع جديد' : 'New project' }}</span>
                        @if ($project->reference_code)
                            <span class="flag-tag style-2 bg-white">{{ $project->reference_code }}</span>
                        @endif
                    </div>
                </div>

                <h1 class="h4 fw-7 mt-4 mb-2">{{ $tr?->name ?: '#' . $project->reference_code }}</h1>

                <div class="d-flex flex-wrap gap-3 align-items-center text-1">
                    @if ($projectLocation)
                        <span class="d-flex gap-8 align-items-center">
                            <i class="icon icon-mapPin"></i>{{ $projectLocation }}
                        </span>
                    @endif
                    @if ($project->investor_name)
                        <span class="d-flex gap-8 align-items-center">
                            <i class="icon icon-building"></i>{{ $project->investor_name }}
                        </span>
                    @endif
                    @if ($project->published_at)
                        <span>{{ $isAr ? 'تاريخ النشر' : 'Published' }}:
                            <span class="fw-6" dir="ltr">{{ $project->published_at->format('Y/m/d') }}</span>
                        </span>
                    @endif
                </div>

                @if (filled($tr?->short_description))
                    <p class="text-1 mt-3 mb-0">{{ $tr->short_description }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== بطاقات الحقائق ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="row g-3">
                @foreach ($facts as $fact)
                    <div class="col-6 col-md-4 col-lg-2">
                        <div class="p-16 p-3 bg-white rounded-3 box-shadow-1 h-100 text-center">
                            <i class="icon {{ $fact['icon'] }}"></i>
                            <div class="text-1 mt-2" style="font-size:12px;line-height:18px;">{{ $fact['label'] }}</div>
                            <div class="fw-7">{{ $fact['value'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    {{-- ------- الوصف ------- --}}
                    @if (filled($tr?->description))
                        <div class="p-16 p-4 bg-white rounded-3 box-shadow-1">
                            <div class="h5 fw-7 mb-3">{{ $isAr ? 'الوصف' : 'Description' }}</div>
                            <div class="text-1" style="line-height:1.9;">{!! nl2br(e($tr->description)) !!}</div>
                        </div>
                    @endif

                    {{-- ------- المرافق ------- --}}
                    @if ($project->amenities->isNotEmpty())
                        <div class="p-16 p-4 bg-white rounded-3 box-shadow-1 mt-4">
                            <div class="h5 fw-7 mb-3">{{ $isAr ? 'المرافق' : 'Amenities' }}</div>
                            <ul class="meta-list mt-0" style="gap:12px 24px;">
                                @foreach ($project->amenities as $amenity)
                                    <li class="item d-flex align-items-center gap-2">
                                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12l5 5l9 -9" />
                                        </svg>
                                        <span>{{ $amenity->translation($locale)?->name ?? '—' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ------- جدول الوحدات ------- --}}
                    <div class="p-16 p-4 bg-white rounded-3 box-shadow-1 mt-4">
                        <div class="h5 fw-7 mb-3">{{ $isAr ? 'الوحدات المتاحة' : 'Units inventory' }}</div>

                        @if ($project->units->isEmpty())
                            <div class="text-center p-16 p-4 rounded-3" style="background-color:#f7f7f7;">
                                <div class="h7 fw-7 mb-1">
                                    {{ $isAr ? 'لا توجد وحدات مسجّلة حتى الآن' : 'No units have been listed yet' }}
                                </div>
                                <p class="text-1 mb-0">
                                    {{ $isAr
                                        ? 'سيتم إضافة جدول الوحدات والأسعار فور اعتماده من المطوّر. يمكنك التواصل معنا لمعرفة المتاح.'
                                        : 'The unit inventory and price list will appear here once the developer confirms it. Contact us for availability.' }}
                                </p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>{{ $isAr ? 'كود الوحدة' : 'Unit code' }}</th>
                                            <th>{{ $isAr ? 'الدور' : 'Floor' }}</th>
                                            <th>{{ $isAr ? 'غرف النوم' : 'Bedrooms' }}</th>
                                            <th>{{ $isAr ? 'الحمامات' : 'Bathrooms' }}</th>
                                            <th>{{ $isAr ? 'المساحة' : 'Area' }}</th>
                                            <th>{{ $isAr ? 'السعر' : 'Price' }}</th>
                                            <th>{{ $isAr ? 'الحالة' : 'Status' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project->units as $unit)
                                            <tr>
                                                <td class="fw-6" dir="ltr">{{ $unit->unit_code ?: '—' }}</td>
                                                <td dir="ltr">{{ $num($unit->floor_number) }}</td>
                                                <td dir="ltr">{{ $num($unit->bedrooms) }}</td>
                                                <td dir="ltr">{{ $num($unit->bathrooms) }}</td>
                                                <td dir="ltr">{{ $num($unit->area) }} {{ $isAr ? 'م²' : 'm²' }}</td>
                                                <td class="fw-6" dir="ltr">{{ money_eg($unit->price) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ status_color($unit->availability_status) }}">
                                                        {{ $statusText($unit->availability_status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- ------- الوحدات المعروضة داخل المشروع ------- --}}
                    @if ($project->properties->isNotEmpty())
                        <div class="p-16 p-4 bg-white rounded-3 box-shadow-1 mt-4">
                            <div class="h5 fw-7 mb-3">{{ $isAr ? 'وحدات معروضة للبيع' : 'Listed units' }}</div>
                            <div class="row g-4">
                                @foreach ($project->properties as $item)
                                    <div class="col-lg-6 col-xl-4">
                                        <x-legacy-property-card :property="$item" :locale="$locale" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- ------- الموقع على الخريطة ------- --}}
                    <div class="p-16 p-4 bg-white rounded-3 box-shadow-1 mt-4">
                        <div class="h5 fw-7 mb-3">{{ $isAr ? 'الموقع على الخريطة' : 'Location' }}</div>

                        @if ($hasCoords)
                            <p class="text-1 mb-3">
                                <span class="fw-6">{{ $isAr ? 'الإحداثيات' : 'Coordinates' }}:</span>
                                <span dir="ltr">{{ $coords }}</span>
                                @if ($projectLocation)
                                    &nbsp;—&nbsp;{{ $projectLocation }}
                                @endif
                            </p>

                            @if ($showMap)
                                <div class="rounded-3 overflow-hidden mb-3">
                                    <iframe
                                        src="https://www.google.com/maps?q={{ $project->latitude }},{{ $project->longitude }}&amp;z=15&amp;output=embed"
                                        title="{{ $isAr ? 'خريطة موقع المشروع' : 'Project location map' }}"
                                        width="100%" height="360" style="border:0;display:block;"
                                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                </div>
                            @endif

                            <a class="tf-btn style-border"
                                href="https://www.google.com/maps/dir/?api=1&amp;destination={{ $project->latitude }},{{ $project->longitude }}"
                                target="_blank" rel="noopener noreferrer">
                                {{ $isAr ? 'الاتجاهات على خرائط جوجل' : 'Get directions on Google Maps' }}
                            </a>
                        @else
                            <p class="text-1 mb-0">
                                {{ $isAr ? 'لم يتم تحديد إحداثيات هذا المشروع بعد.' : 'Coordinates for this project have not been set yet.' }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- ===================== التواصل + النموذج ===================== --}}
                <aside class="col-lg-4">
                    <div class="p-16 p-4 bg-white rounded-3 box-shadow-1 mb-4">
                        <div class="h5 fw-7 mb-3">{{ $isAr ? 'تواصل معنا' : 'Contact us' }}</div>

                        <ul class="meta-list mt-0" style="gap:10px 20px;">
                            <li class="item d-flex align-items-center gap-2"><i class="icon icon-building"></i><span>{{ $project->investor_name ?: '—' }}</span></li>
                            <li class="item d-flex align-items-center gap-2"><i class="icon icon-home"></i><span>{{ $num($project->flats_count) }} {{ $isAr ? 'وحدة' : 'units' }}</span></li>
                            <li class="item d-flex align-items-center gap-2"><i class="icon icon-calendar"></i><span dir="ltr">{{ $date($project->completion_date) }}</span></li>
                        </ul>

                        <div class="d-flex flex-column gap-2 mt-3">
                            @if ($phone)
                                <a class="tf-btn primary w-100" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">
                                    <span>{{ $isAr ? 'اتصل الآن' : 'Call now' }}</span>
                                    <span dir="ltr">{{ $phone }}</span>
                                </a>
                            @endif

                            @if ($whatsappDigits !== '')
                                <a class="tf-btn style-border w-100" href="https://wa.me/{{ $whatsappDigits }}"
                                    target="_blank" rel="noopener noreferrer">
                                    {{ $isAr ? 'واتساب' : 'WhatsApp' }}
                                </a>
                            @endif

                            @if ($email)
                                <a class="tf-btn style-border w-100" href="mailto:{{ $email }}" dir="ltr">{{ $email }}</a>
                            @endif
                        </div>
                    </div>

                    {{-- ------- نموذج طلب المعاينة ------- --}}
                    <div class="p-16 p-4 bg-white rounded-3 box-shadow-1" id="project-inquiry">
                        <div class="h5 fw-7 mb-3">{{ $isAr ? 'اطلب معاينة' : 'Request a viewing' }}</div>

                        @if (session('success'))
                            <div class="alert alert-success border-0 rounded-3">{{ session('success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger border-0 rounded-3">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="post" action="{{ route('leads.store', ['locale' => $locale]) }}">
                            @csrf
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="source" value="project_inquiry">

                            <div class="mb-2">
                                <label class="text-1 fw-6 mb-1" for="project-lead-name">{{ $isAr ? 'الاسم' : 'Name' }}</label>
                                <input id="project-lead-name" type="text" name="name" class="form-control" required
                                    maxlength="120" value="{{ old('name') }}"
                                    placeholder="{{ $isAr ? 'اسمك بالكامل' : 'Your full name' }}">
                            </div>

                            <div class="mb-2">
                                <label class="text-1 fw-6 mb-1" for="project-lead-phone">{{ $isAr ? 'رقم الهاتف' : 'Phone' }}</label>
                                <input id="project-lead-phone" type="text" name="phone" class="form-control" dir="ltr"
                                    maxlength="50" value="{{ old('phone') }}"
                                    placeholder="{{ $isAr ? '01xxxxxxxxx' : '+20 1xx xxx xxxx' }}">
                            </div>

                            <div class="mb-2">
                                <label class="text-1 fw-6 mb-1" for="project-lead-email">{{ $isAr ? 'البريد الإلكتروني' : 'Email' }}</label>
                                <input id="project-lead-email" type="email" name="email" class="form-control" dir="ltr"
                                    maxlength="190" value="{{ old('email') }}" placeholder="name@example.com">
                            </div>

                            <div class="mb-3">
                                <label class="text-1 fw-6 mb-1" for="project-lead-message">{{ $isAr ? 'الرسالة' : 'Message' }}</label>
                                <textarea id="project-lead-message" name="message" class="form-control" rows="4" maxlength="5000"
                                    placeholder="{{ $isAr ? 'أرغب في معرفة المتاح داخل المشروع…' : 'I would like to know what is available in this project…' }}">{{ old('message') }}</textarea>
                            </div>

                            <button type="submit" class="tf-btn primary w-100">
                                {{ $isAr ? 'إرسال الطلب' : 'Send request' }}
                            </button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- ===================== مشروعات قريبة ===================== --}}
    @if ($nearbyProjects->isNotEmpty())
        <section class="flat-section-v5 flat-recommended flat-recommended-v2">
            <div class="container">
                <div class="box-title-listing">
                    <div class="h5 fw-7">{{ $isAr ? 'مشروعات مشابهة' : 'Nearby projects' }}</div>
                    <a class="tf-btn style-border" href="{{ route('projects.index', ['locale' => $locale]) }}">
                        {{ $isAr ? 'كل المشروعات' : 'All projects' }}
                    </a>
                </div>

                <div class="row g-4">
                    @foreach ($nearbyProjects as $nearby)
                        @php
                            $nearbyTr = $nearby->translation($locale);
                            $nearbyUrl = $nearbyTr?->slug
                                ? route('projects.show', ['locale' => $locale, 'slug' => $nearbyTr->slug])
                                : '#';
                            $nearbyImage = $nearby->properties->first()?->primaryImageUrl($locale) ?? $placeholder;
                        @endphp
                        <div class="col-lg-4 col-md-6">
                            <div class="property-item homeya-box h-100">
                                <div class="archive-top">
                                    <a href="{{ $nearbyUrl }}" class="images-group">
                                        <div class="images-style">
                                            <img src="{{ $nearbyImage }}" alt="{{ $nearbyTr?->name }}"
                                                onerror="this.onerror=null;this.src='{{ $placeholder }}';">
                                        </div>
                                        <div class="top">
                                            <div class="d-flex gap-8">
                                                @if ($nearby->is_featured)
                                                    <span class="flag-tag success">{{ $isAr ? 'مميز' : 'Featured' }}</span>
                                                @endif
                                                <span class="flag-tag primary">{{ $isAr ? 'مشروع جديد' : 'New project' }}</span>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="content">
                                        <div class="text-capitalize h7 fw-7">
                                            <a href="{{ $nearbyUrl }}" class="link line-clamp-1">{{ $nearbyTr?->name }}</a>
                                        </div>
                                        <div class="desc">
                                            <i class="icon icon-mapPin"></i>
                                            <p class="line-clamp-1">{{ $nearby->location?->translation($locale)?->name ?? '' }}</p>
                                        </div>
                                        <ul class="meta-list">
                                            <li class="item"><i class="icon icon-building"></i><span>{{ $nearby->blocks_count ?? '—' }} {{ $isAr ? 'عمارة' : 'bldgs' }}</span></li>
                                            <li class="item"><i class="icon icon-home"></i><span>{{ $nearby->flats_count ?? '—' }} {{ $isAr ? 'وحدة' : 'units' }}</span></li>
                                            <li class="item"><i class="icon icon-stairs"></i><span>{{ $nearby->floors_count ?? '—' }} {{ $isAr ? 'أدوار' : 'floors' }}</span></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="archive-bottom d-flex justify-content-between align-items-center">
                                    <span class="text-1">{{ $nearby->investor_name }}</span>
                                    <a href="{{ $nearbyUrl }}" class="tf-btn primary">
                                        {{ $isAr ? 'التفاصيل' : 'Details' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
