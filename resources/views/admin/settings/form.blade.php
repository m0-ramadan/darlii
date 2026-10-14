@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? '')

@section('content')
    @include('admin.settings._tabs', ['tabs' => $tabs, 'active' => $active])

    @if ($group === 'partners')
        <div class="ap-card" style="margin-bottom: 20px; background: rgba(201, 154, 69, 0.05); border: 1px dashed var(--ap-gold);">
            <div class="ap-card__body d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold" style="font-size: 15px; margin-bottom: 4px; color: var(--ap-navy);">
                        @include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18])
                        إدارة الشركات والوكلاء المعروضين
                    </h3>
                    <p class="text-muted mb-0" style="font-size: 12.5px;">
                        يمكنك إضافة شركات عقارية جديدة أو تعديل أسمائها ومسمياتها وأرقام هواتفها والمدن وتحديد من يظهر في الرئيسية من خلال دليل الوكلاء.
                    </p>
                </div>
                <a href="{{ admin_route('admin.agents.index') }}" class="ap-btn ap-btn--gold ap-btn--sm">
                    إدارة الوكلاء والشركات ←
                </a>
            </div>
        </div>
    @endif

    <form method="post" action="{{ admin_route('admin.settings.update', ['group' => $group]) }}" enctype="multipart/form-data">
        @csrf
        @method('put')

        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">
                    @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 18])
                    {{ $title }}
                </h2>
                <span class="ap-badge">{{ $fields->count() }} إعداد</span>
            </div>

            <div class="ap-card__body">
                @if ($fields->isEmpty())
                    <div class="ap-empty">
                        @include('admin.partials.icon', ['iconName' => 'alert', 'size' => 40])
                        <p>لا توجد إعدادات مخزنة لهذه المجموعة بعد.</p>
                    </div>
                @else
                    <div class="ap-grid ap-grid--2">
                        @foreach ($fields as $field)
                            @php
                                $value = old($field->key, $field->value);
                                $isWide = in_array($field->type, ['textarea', 'image'], true);
                                // PHP converts dots in form field names to underscores,
                                // and Laravel additionally treats "a.b" as a nested key.
                                // So the input name uses underscores and we map it back below.
                                $inputName = str_replace('.', '_', $field->key);
                            @endphp

                            <div class="ap-field" @if ($isWide) style="grid-column: 1 / -1;" @endif>
                                <label class="ap-label" for="field-{{ $inputName }}">
                                    {{ $field->label_ar ?: $field->key }}
                                </label>

                                @switch($field->type)
                                    @case('textarea')
                                        <textarea id="field-{{ $inputName }}" name="{{ $inputName }}" class="ap-textarea" rows="4">{{ $value }}</textarea>
                                        @break

                                    @case('boolean')
                                        <input type="hidden" name="{{ $inputName }}" value="0">
                                        <label class="ap-switch">
                                            <input type="checkbox" id="field-{{ $inputName }}" name="{{ $inputName }}"
                                                value="1" @checked((bool) $value)>
                                            <span class="ap-switch__track"></span>
                                            <span>{{ (bool) $value ? 'مُفعّل' : 'مُعطّل' }}</span>
                                        </label>
                                        @break

                                    @case('password')
                                    @case('secret')
                                        <input type="password" id="field-{{ $inputName }}" name="{{ $inputName }}"
                                            class="ap-input" value="" autocomplete="new-password"
                                            placeholder="{{ $value ? '•••••••• (محفوظ — اتركه فارغاً للإبقاء عليه)' : 'أدخل المفتاح' }}">
                                        @break

                                    @case('number')
                                        <input type="number" id="field-{{ $inputName }}" name="{{ $inputName }}"
                                            class="ap-input" value="{{ $value }}">
                                        @break

                                    @case('color')
                                        {{-- The swatch only drives the text field; it must not carry a name. --}}
                                        <div class="d-flex gap-2 align-items-center">
                                            <input type="color" id="swatch-{{ $inputName }}" value="{{ $value ?: '#0F3D2E' }}"
                                                oninput="document.getElementById('field-{{ $inputName }}').value = this.value"
                                                style="width: 54px; height: 40px; border: 1px solid var(--ap-border); border-radius: 8px; background: #fff; cursor: pointer;">
                                            <input type="text" id="field-{{ $inputName }}" name="{{ $inputName }}" class="ap-input"
                                                value="{{ $value }}" placeholder="#0F3D2E"
                                                oninput="document.getElementById('swatch-{{ $inputName }}').value = this.value">
                                        </div>
                                        @break

                                    @case('image')
                                    @case('file')
                                        <div class="d-flex gap-3 align-items-center flex-wrap">
                                            @if ($value)
                                                <img src="{{ str_starts_with((string) $value, 'http') ? $value : asset($value) }}"
                                                    alt="" style="height: 56px; background: #f4f6f8; border-radius: 8px; padding: 6px; border: 1px solid var(--ap-border);">
                                            @endif
                                            <input type="file" id="field-{{ $inputName }}" name="{{ $inputName }}"
                                                class="ap-input" accept="image/*">
                                        </div>
                                        <div class="ap-help">اتركه فارغاً للإبقاء على الملف الحالي: <code>{{ $value ?: '—' }}</code></div>
                                        @break

                                    @case('select')
                                        <select id="field-{{ $inputName }}" name="{{ $inputName }}" class="ap-select">
                                            @if ($field->key === 'properties.contact_display_mode')
                                                <option value="platform_only" @selected($value === 'platform_only')>أرقام إدارة دارلي المباشرة فقط (إخفاء أرقام الوسيط / الشركة تماماً)</option>
                                                <option value="platform_with_agent_name" @selected($value === 'platform_with_agent_name')>عرض اسم الوسيط / الشركة مع توجيه الاتصال والواتساب لأرقام دارلي</option>
                                                <option value="agent_direct" @selected($value === 'agent_direct')>عرض أرقام وبيانات الوسيط / الشركة مباشرة</option>
                                            @else
                                                <option value="{{ $value }}">{{ $value }}</option>
                                            @endif
                                        </select>
                                        @break

                                    @default
                                        <input type="text" id="field-{{ $inputName }}" name="{{ $inputName }}"
                                            class="ap-input" value="{{ $value }}">
                                @endswitch

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
                <div class="ap-card__body" style="border-top: 1px solid var(--ap-border);">
                    <button class="ap-btn ap-btn--gold">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16]) حفظ الإعدادات
                    </button>
                </div>
            @endif
        </div>
    </form>
@endsection
