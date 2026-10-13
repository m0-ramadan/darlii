@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('page_actions')
    <form method="post" action="{{ admin_route('admin.settings.test') }}" class="d-inline">
        @csrf
        <input type="hidden" name="service" value="mail">
        <button class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plug', 'size' => 16]) اختبار SMTP
        </button>
    </form>
    <form method="post" action="{{ admin_route('admin.settings.test') }}" class="d-inline">
        @csrf
        <input type="hidden" name="service" value="s3">
        <button class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'download', 'size' => 16]) اختبار S3
        </button>
    </form>
    <form method="post" action="{{ admin_route('admin.settings.test') }}" class="d-inline">
        @csrf
        <input type="hidden" name="service" value="storage">
        <button class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'photo', 'size' => 16]) اختبار التخزين الحالي
        </button>
    </form>
@endsection

@section('content')
    @include('admin.settings._tabs', ['tabs' => $tabs, 'active' => $active])

    @php
        $groupLabels = [
            'analytics' => ['title' => 'التحليلات والتتبع', 'icon' => 'chart-bar', 'hint' => 'Google Analytics وTag Manager وFacebook Pixel وTikTok'],
            'maps' => ['title' => 'الخرائط والمواقع', 'icon' => 'map', 'hint' => 'مفاتيح Google Maps والإحداثيات الافتراضية'],
            'sms' => ['title' => 'الرسائل SMS وواتساب', 'icon' => 'message', 'hint' => 'إشعارات العملاء عبر واتساب أو مزود الرسائل'],
            'payments' => ['title' => 'بوابات الدفع', 'icon' => 'money', 'hint' => 'Paymob وStripe لتحصيل المدفوعات والاشتراكات'],
        ];
    @endphp

    <div class="ap-grid ap-grid--2" style="align-items: start;">
        @foreach ($groups as $group)
            @php $fields = $settingsByGroup[$group] ?? collect(); @endphp
            <div class="ap-card" @if ($group === 'payments') style="grid-column: 1 / -1;" @endif>
                <div class="ap-card__head">
                    <div>
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => $groupLabels[$group]['icon'] ?? 'plug', 'size' => 18])
                            {{ $groupLabels[$group]['title'] ?? $group }}
                        </h2>
                        <p class="ap-card__subtitle">{{ $groupLabels[$group]['hint'] ?? '' }}</p>
                    </div>
                    <span class="ap-badge">{{ $fields->count() }} إعداد</span>
                </div>

                <form method="post" action="{{ admin_route('admin.settings.update', ['group' => $group]) }}">
                    @csrf
                    @method('put')

                    <div class="ap-card__body">
                        @if ($fields->isEmpty())
                            <div class="ap-empty"><p>لا توجد حقول لهذه المجموعة.</p></div>
                        @else
                            <div class="ap-grid ap-grid--2">
                                @foreach ($fields as $field)
                                    @php
                                        $value = old($field->key, $field->value);
                                        $wide = in_array($field->type, ['textarea'], true);
                                        // Dots in form field names become underscores in PHP.
                                        $inputName = str_replace('.', '_', $field->key);
                                        $label = $field->label_ar ?: setting_label($field->key);
                                    @endphp
                                    <div class="ap-field" @if ($wide) style="grid-column: 1 / -1;" @endif>
                                        <label class="ap-label" for="f-{{ $inputName }}">{{ $label }}</label>

                                        @if ($field->type === 'boolean')
                                            <input type="hidden" name="{{ $inputName }}" value="0">
                                            <label class="ap-switch">
                                                <input type="checkbox" id="f-{{ $inputName }}" name="{{ $inputName }}" value="1" @checked((bool) $value)>
                                                <span class="ap-switch__track"></span>
                                                <span class="ap-help" style="margin: 0;">{{ (bool) $value ? 'مُفعّل' : 'مُعطّل' }}</span>
                                            </label>
                                        @elseif (in_array($field->type, ['password', 'secret'], true))
                                            <input type="password" id="f-{{ $inputName }}" name="{{ $inputName }}" class="ap-input"
                                                value="" autocomplete="new-password"
                                                placeholder="{{ $value ? '•••••••• (محفوظ — اتركه فارغاً للإبقاء عليه)' : 'أدخل المفتاح' }}">
                                        @elseif ($field->type === 'textarea')
                                            <textarea id="f-{{ $inputName }}" name="{{ $inputName }}" class="ap-textarea" rows="3">{{ $value }}</textarea>
                                        @elseif ($field->type === 'number')
                                            <input type="number" id="f-{{ $inputName }}" name="{{ $inputName }}" class="ap-input" value="{{ $value }}">
                                        @else
                                            <input type="text" id="f-{{ $inputName }}" name="{{ $inputName }}" class="ap-input" value="{{ $value }}">
                                        @endif

                                        @if ($field->help_ar)
                                            <div class="ap-help">{{ $field->help_ar }}</div>
                                        @endif
                                        <div class="ap-help" style="opacity: .6;">المفتاح: <code>{{ $field->key }}</code></div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($fields->isNotEmpty())
                        <div class="ap-card__body" style="border-top: 1px solid var(--ap-border); padding-top: 12px; padding-bottom: 12px;">
                            <button class="ap-btn ap-btn--gold ap-btn--sm">
                                @include('admin.partials.icon', ['iconName' => 'check', 'size' => 15]) حفظ {{ $groupLabels[$group]['title'] ?? $group }}
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        @endforeach
    </div>

    <div class="ap-alert ap-alert--info">
        @include('admin.partials.icon', ['iconName' => 'alert', 'size' => 18])
        <div>
            <strong>ملاحظة:</strong> القيم تُحفظ في قاعدة البيانات. مفاتيح التحليلات والخرائط والبكسل تُطبَّق مباشرة على صفحات الموقع.
            إعدادات البريد تُستخدم في إرسال الإشعارات. بعد أي تغيير على مفاتيح التخزين يمكنك استخدام زر "اختبار" بالأعلى للتأكد من الاتصال.
        </div>
    </div>
@endsection
