@extends('layouts.public-shell', ['shellHead' => $shell['head'] ?? '', 'shellFooter' => $shell['footer'] ?? '', 'bodyClass' => 'body counter-scroll'])

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';

    $tr = $career->translation($locale);

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

    /** نطاق الراتب بالجنيه المصري (الرمز حسب اللغة). */
    $salaryRange = (function () use ($career, $isAr): ?string {
        $min = $career->salary_min;
        $max = $career->salary_max;

        if (blank($min) && blank($max)) {
            return null;
        }

        $symbol = $isAr ? 'ج.م' : 'EGP';
        $format = fn ($value): string => number_format((float) $value);

        if (! blank($min) && ! blank($max)) {
            return $format($min) . ' - ' . $format($max) . ' ' . $symbol;
        }

        return $format(blank($min) ? $max : $min) . ' ' . $symbol;
    })();

    $phone = trim((string) setting('contact.phone', ''));
    $email = trim((string) setting('contact.email', ''));
    $whatsapp = trim((string) setting('contact.whatsapp', ''));
    $whatsappDigits = preg_replace('/\D+/', '', $whatsapp) ?? '';

    $facts = array_values(array_filter([
        filled($career->location) ? [
            'icon' => 'icon-mapPin',
            'label' => $isAr ? 'الموقع' : 'Location',
            'value' => $career->location,
        ] : null,
        filled($career->employment_type) ? [
            'icon' => 'icon-briefcase',
            'label' => $isAr ? 'نوع الدوام' : 'Employment type',
            'value' => $career->employment_type,
        ] : null,
        filled($career->experience_level) ? [
            'icon' => 'icon-user',
            'label' => $isAr ? 'مستوى الخبرة' : 'Experience level',
            'value' => $career->experience_level,
        ] : null,
        $salaryRange ? [
            'icon' => 'icon-money',
            'label' => $isAr ? 'الراتب' : 'Salary range',
            'value' => $salaryRange,
        ] : null,
        $career->deadline ? [
            'icon' => 'icon-calendar',
            'label' => $isAr ? 'آخر موعد للتقديم' : 'Application deadline',
            'value' => $fmtDate($career->deadline),
        ] : null,
    ]));
@endphp

@section('title', ($tr?->seo_title ?: $tr?->title ?: ($isAr ? 'وظيفة' : 'Career')) . ($isAr ? ' | دارلي' : ' | Darlii'))

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
                        <a href="{{ route('careers.index', ['locale' => $locale]) }}">{{ $isAr ? 'الوظائف' : 'Careers' }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $tr?->title }}</li>
                </ol>
            </nav>
        </div>
    </section>

    {{-- ===================== بيانات الوظيفة ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="darli-panel darli-panel--pad">
                @if (filled($career->department))
                    <div class="d-flex flex-wrap gap-8 mb-2">
                        <span class="flag-tag primary">{{ $career->department }}</span>
                    </div>
                @endif

                <h1 class="h4 fw-7 mb-3">{{ $tr?->title }}</h1>

                @if (filled($tr?->summary))
                    <p class="text-1 mb-4">{{ $tr->summary }}</p>
                @endif

                @if ($facts !== [])
                    <div class="darli-facts">
                        @foreach ($facts as $fact)
                            <div class="darli-fact">
                                <div class="darli-fact__label">
                                    <i class="icon {{ $fact['icon'] }}"></i>{{ $fact['label'] }}
                                </div>
                                <div class="darli-fact__value">{{ $fact['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ===================== الوصف الوظيفي ===================== --}}
    @if (filled($tr?->description))
        <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
            <div class="container">
                <div class="darli-panel darli-panel--pad">
                    <div class="h5 fw-7 mb-3">{{ $isAr ? 'الوصف الوظيفي' : 'Job description' }}</div>
                    <div class="text-1" style="line-height:2;">{!! nl2br(e($tr->description)) !!}</div>
                </div>
            </div>
        </section>
    @endif

    {{-- ===================== التقديم على الوظيفة + بيانات التواصل ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="darli-grid darli-grid--2">
                {{-- ------- نموذج التقديم ------- --}}
                <div class="darli-panel darli-panel--pad" id="apply">
                    <div class="h5 fw-7 mb-2">{{ $isAr ? 'تقدّم للوظيفة' : 'Apply for this job' }}</div>
                    <p class="text-1">
                        {{ $isAr
                            ? 'املأ البيانات التالية وسيتواصل معك فريق التوظيف. يُرجى كتابة مؤهلاتك وسنوات خبرتك ورابط سيرتك الذاتية أو أعمالك (Portfolio) داخل خانة الرسالة.'
                            : 'Fill in the form below and our recruitment team will get back to you. Please include your qualifications, years of experience and a link to your CV or portfolio in the message field.' }}
                    </p>

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
                        <input type="hidden" name="source" value="career">

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-name">
                                {{ $isAr ? 'الاسم' : 'Name' }} <span class="text-danger">*</span>
                            </label>
                            <input id="career-lead-name" type="text" name="name" class="darli-input" required
                                maxlength="120" value="{{ old('name') }}"
                                placeholder="{{ $isAr ? 'اسمك بالكامل' : 'Your full name' }}">
                        </div>

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-email">
                                {{ $isAr ? 'البريد الإلكتروني' : 'Email' }}
                            </label>
                            <input id="career-lead-email" type="email" name="email" class="darli-input" dir="ltr"
                                maxlength="190" value="{{ old('email') }}" placeholder="name@example.com">
                        </div>

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-phone">
                                {{ $isAr ? 'رقم الهاتف' : 'Phone' }}
                            </label>
                            <input id="career-lead-phone" type="text" name="phone" class="darli-input" dir="ltr"
                                maxlength="50" value="{{ old('phone') }}"
                                placeholder="{{ $isAr ? '01xxxxxxxxx' : '+20 1xx xxx xxxx' }}">
                        </div>

                        <div class="darli-field">
                            <label class="darli-label" for="career-lead-message">
                                {{ $isAr ? 'الرسالة (المؤهلات وسنوات الخبرة ورابط السيرة الذاتية)' : 'Message (qualifications, experience and CV link)' }}
                            </label>
                            <textarea id="career-lead-message" name="message" class="darli-textarea" rows="6" maxlength="5000"
                                placeholder="{{ $isAr
                                    ? 'أرغب في التقدم لوظيفة ' . ($tr?->title ?? '') . ' — مؤهلاتي: … سنوات الخبرة: … رابط السيرة الذاتية: …'
                                    : 'I would like to apply for ' . ($tr?->title ?? '') . ' — qualifications: … experience: … CV link: …' }}">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="tf-btn primary w-100">
                            {{ $isAr ? 'إرسال طلب التقديم' : 'Submit application' }}
                        </button>
                    </form>
                </div>

                {{-- ------- بيانات التواصل ------- --}}
                <aside class="darli-panel darli-panel--pad-sm h-100">
                    <div class="h7 fw-7 mb-3">{{ $isAr ? 'بيانات التواصل' : 'Contact details' }}</div>

                    @if ($phone === '' && $email === '' && $whatsappDigits === '')
                        <p class="text-1 mb-0">
                            {{ $isAr
                                ? 'يمكنك إرسال طلبك من خلال النموذج وسنتواصل معك.'
                                : 'Send your application through the form and we will contact you.' }}
                        </p>
                    @else
                        <ul class="darli-list-reset d-flex flex-column gap-2">
                            @if ($phone !== '')
                                <li>
                                    <a class="d-flex gap-8 align-items-center" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">
                                        <i class="icon icon-phone"></i>
                                        <span class="text-1" dir="ltr">{{ $phone }}</span>
                                    </a>
                                </li>
                            @endif

                            @if ($email !== '')
                                <li>
                                    <a class="d-flex gap-8 align-items-center" href="mailto:{{ $email }}">
                                        <i class="icon icon-mail"></i>
                                        <span class="text-1" dir="ltr">{{ $email }}</span>
                                    </a>
                                </li>
                            @endif

                            @if ($whatsappDigits !== '')
                                <li class="mt-2">
                                    <a class="darli-whatsapp" href="https://wa.me/{{ $whatsappDigits }}"
                                        target="_blank" rel="noopener noreferrer">
                                        {{ $isAr ? 'تواصل عبر واتساب' : 'Chat on WhatsApp' }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @endif

                    <div class="text-1 mt-4">
                        {{ $isAr ? 'الوظيفة المعلنة:' : 'Position:' }}
                        <span class="fw-6">{{ $tr?->title }}</span>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- ===================== رجوع لكل الوظائف ===================== --}}
    <section class="flat-section-v5 flat-recommended flat-recommended-v2 pt-0">
        <div class="container">
            <div class="box-title-listing mb-0">
                <div class="h5 fw-7">{{ $isAr ? 'ابحث عن وظيفة أخرى' : 'Looking for another role?' }}</div>
                <a class="tf-btn primary" href="{{ route('careers.index', ['locale' => $locale]) }}">
                    {{ $isAr ? 'كل الوظائف' : 'All careers' }}
                </a>
            </div>
        </div>
    </section>
@endsection
