@extends('layouts.admin')

@php
    $isEdit = $agent->exists;
    $arTranslation = $isEdit ? $agent->translation('ar') : null;
    $enTranslation = $isEdit ? $agent->translation('en') : null;
    $title = $isEdit ? 'تعديل بيانات الوكيل: ' . $agent->display_name : 'إضافة وكيل / شركة عقارية جديدة';
@endphp

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $isEdit ? ($agent->agency?->name ?: 'وكيل مستقل') : 'أدخل بيانات الوكيل أو الشركة العقارية للاعتماد والظهور في دليل الوكلاء')

@section('page_actions')
    <a href="{{ admin_route('admin.agents.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $isEdit ? admin_route('admin.agents.update', $agent) : admin_route('admin.agents.store') }}">
        @csrf
        @if ($isEdit)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            {{-- العمود الأيمن: البيانات الأساسية والتعريفية --}}
            <div>
                <div class="ap-card" style="margin-bottom: 20px;">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18])
                            البيانات الأساسية
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">الاسم بالعربية <span>*</span></label>
                                <input type="text" name="display_name_ar" class="ap-input" required
                                    value="{{ old('display_name_ar', $arTranslation?->display_name ?? $agent->agency?->name ?? $agent->user?->name) }}"
                                    placeholder="مثال: شركة النيل للتطوير العقاري">
                            </div>

                            @if (config('locales.admin_english_enabled'))
                            <div class="ap-field">
                                <label class="ap-label">الاسم بالإنجليزية</label>
                                <input type="text" name="display_name_en" class="ap-input" dir="ltr"
                                    value="{{ old('display_name_en', $enTranslation?->display_name) }}"
                                    placeholder="e.g. Nile Real Estate Development">
                            </div>
                            @endif
                        </div>

                        <div class="ap-grid ap-grid--2" style="margin-top: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">الشركة أو الوكالة التابع لها</label>
                                <select name="agency_id" class="ap-select" id="agencySelect">
                                    <option value="">-- وكيل مستقل / غير منضم لشركة --</option>
                                    @foreach ($agencies as $agency)
                                        <option value="{{ $agency->id }}" @selected(old('agency_id', $agent->agency_id) == $agency->id)>
                                            {{ $agency->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">أو إنشاء شركة عقارية جديدة</label>
                                <input type="text" name="new_agency_name" class="ap-input"
                                    value="{{ old('new_agency_name') }}"
                                    placeholder="اسم الشركة الجديدة إذا لم تكن بالقائمة">
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--3" style="margin-top: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">نوع المسؤول / الكيان <span>*</span></label>
                                <select name="agent_type" class="ap-select" required>
                                    <option value="individual" @selected(old('agent_type', $agent->agent_type) === 'individual')>شخص / مسؤول فردي</option>
                                    <option value="office" @selected(old('agent_type', $agent->agent_type) === 'office')>مكتب عقارات / شركة</option>
                                    <option value="broker" @selected(old('agent_type', $agent->agent_type) === 'broker')>سمسار عادي</option>
                                </select>
                                <small class="text-muted" style="font-size: 11px;">حدد صفة المسؤول التابع له السماسرة والعقارات.</small>
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">المسمى والتخصص</label>
                                <input type="text" name="job_title" class="ap-input"
                                    value="{{ old('job_title', $agent->job_title) }}"
                                    placeholder="مثال: مسؤول مبيعات / مكتب وساطة">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">سنوات الخبرة</label>
                                <input type="number" name="years_experience" class="ap-input" min="0" max="60"
                                    value="{{ old('years_experience', $agent->years_experience ?? 5) }}">
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--2" style="margin-top: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">المدينة / المركز <span>*</span></label>
                                <input type="text" name="city" list="citiesList" class="ap-input"
                                    value="{{ old('city', $agent->city) }}"
                                    placeholder="مثال: بني سويف">
                                <datalist id="citiesList">
                                    <option value="بني سويف">
                                    <option value="بني سويف الجديدة">
                                    <option value="الواسطى">
                                    <option value="ناصر">
                                    <option value="إهناسيا">
                                    <option value="ببا">
                                    <option value="الفشن">
                                    <option value="سمسطا">
                                </datalist>
                                <small class="text-muted" style="font-size: 11px;">المدينة التي تظهر في أسفل بطاقة الشركة / الوكيل.</small>
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">الأيقونة المميزة (في سلايدر الشركاء)</label>
                                <select name="icon" class="ap-select">
                                    <option value="building" @selected(old('icon', $agent->icon) === 'building')>🏢 مبنى استثماري وتطوير (building)</option>
                                    <option value="landmark" @selected(old('icon', $agent->icon) === 'landmark')>🏛️ صرح وتعمير (landmark)</option>
                                    <option value="layers" @selected(old('icon', $agent->icon) === 'layers')>📑 طبقات واستثمار (layers)</option>
                                    <option value="compass" @selected(old('icon', $agent->icon) === 'compass')>🧭 بوصلة وتسويق (compass)</option>
                                    <option value="building-2" @selected(old('icon', $agent->icon) === 'building-2')>🏬 مكتب / شركة عقارية (building-2)</option>
                                    <option value="home" @selected(old('icon', $agent->icon) === 'home')>🏡 منازل وسكن (home)</option>
                                    <option value="briefcase" @selected(old('icon', $agent->icon) === 'briefcase')>💼 وساطة واستشارات (briefcase)</option>
                                </select>
                            </div>
                        </div>

                        <div class="ap-field" style="margin-top: 12px;">
                            <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                                <input type="checkbox" name="featured_on_homepage" value="1" @checked(old('featured_on_homepage', $agent->featured_on_homepage))>
                                <span class="fw-bold" style="font-size: 13.5px; color: var(--ap-primary);">عرض في قسم "شركاؤنا المميزون" في الصفحة الرئيسية</span>
                            </label>
                            <small class="text-muted" style="display: block; margin-top: 4px; font-size: 11.5px;">
                                عند التفعيل، ستظهر هذه الشركة / الوكيل في سلايدر "تعرف على وكلائنا" بالصفحة الرئيسية مع رابط لصفحة تفاصيلها.
                            </small>
                        </div>

                        {{-- بنود التعامل والنسب والأرقام (نص حر) --}}
                        <div class="ap-field" style="margin-top: 18px; padding-top: 15px; border-top: 1px dashed var(--ap-border);">
                            <label class="ap-label d-flex align-items-center justify-content-between">
                                <span class="fw-bold" style="color: var(--ap-primary);">
                                    @include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 15])
                                    بنود التعامل وخاصة في النسب والأرقام (نص حر)
                                </span>
                                <span class="ap-badge ap-badge--gold" style="font-size: 10.5px;">خاص بالاتفاقيات والعمولات</span>
                            </label>
                            <textarea name="contract_terms" class="ap-textarea" rows="4"
                                placeholder="اكتب هنا كل ما تريده بحرية: بنود الاتفاق، نسبة العمولة (مثال: 2.5% من إجمالي قيمة البيع، 50% من أول شهر إيجار)، طريقة وتوقيت صرف الأرباح، أرقام الحسابات، الحد الأدنى أو الأقصى للعمولات، وشروط التعامل الخاصة مع هذا المسؤول أو السمسار...">{{ old('contract_terms', $agent->contract_terms) }}</textarea>
                            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 11.5px;">
                                هذا الجزء نص حر غير مقيد بصيغة محددة، يمكنك كتابة أي نسب أو أرقام أو شروط تعاقدية تناسب هذا المسؤول أو المكتب أو السمسار.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- نبذة تعريفية --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 18])
                            النبذة التعريفية (Bio)
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">نبذة بالعربية</label>
                            <textarea name="bio_ar" class="ap-textarea" rows="4" placeholder="نبذة تعريفية عن الشركة أو الوكيل، مجالات التخصص، والخدمات المقدمة...">{{ old('bio_ar', $arTranslation?->bio ?? $agent->bio) }}</textarea>
                        </div>

                        @if (config('locales.admin_english_enabled'))
                        <div class="ap-field">
                            <label class="ap-label">نبذة بالإنجليزية (اختياري)</label>
                            <textarea name="bio_en" class="ap-textarea" rows="3" dir="ltr" placeholder="Short biography / summary in English...">{{ old('bio_en', $enTranslation?->bio) }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- العمود الأيسر: التواصل، الصورة، والحالة والتوثيق --}}
            <div>
                {{-- التواصل والترخيص --}}
                <div class="ap-card" style="margin-bottom: 20px;">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'phone', 'size' => 18])
                            بيانات الاتصال والترخيص
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">البريد الإلكتروني <span>*</span></label>
                            <input type="email" name="email" class="ap-input" required dir="ltr"
                                value="{{ old('email', $agent->user?->email) }}"
                                placeholder="name@darlii.eg">
                        </div>

                        <div class="ap-grid ap-grid--2" style="margin-bottom: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">الهاتف</label>
                                <input type="text" name="phone" class="ap-input" dir="ltr"
                                    value="{{ old('phone', $agent->user?->phone) }}"
                                    placeholder="010XXXXXXXX">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">واتساب</label>
                                <input type="text" name="whatsapp" class="ap-input" dir="ltr"
                                    value="{{ old('whatsapp', $agent->whatsapp) }}"
                                    placeholder="2010XXXXXXXX">
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">رقم الترخيص العقاري</label>
                                <input type="text" name="license_number" class="ap-input" dir="ltr"
                                    value="{{ old('license_number', $agent->license_number) }}"
                                    placeholder="BS-RE-2024-XXX">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">الموقع الإلكتروني</label>
                                <input type="url" name="website" class="ap-input" dir="ltr"
                                    value="{{ old('website', $agent->website) }}"
                                    placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- الصورة أو الشعار --}}
                <div class="ap-card" style="margin-bottom: 20px;">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'photo', 'size' => 18])
                            الصورة أو الشعار
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        @if ($agent->avatar_path)
                            <div class="d-flex align-items-center gap-3" style="margin-bottom: 15px;">
                                <img src="{{ asset($agent->avatar_path) }}" alt="" class="ap-avatar ap-avatar--lg" style="object-fit: cover; width: 64px; height: 64px; border-radius: 8px;">
                                <div>
                                    <div class="fw-bold" style="font-size: 13px;">الصورة الحالية</div>
                                    <div class="text-muted" style="font-size: 11px;">ارفع صورة جديدة لاستبدالها</div>
                                </div>
                            </div>
                        @endif

                        <div class="ap-field">
                            <label class="ap-label">رفع صورة جديدة</label>
                            <input type="file" name="avatar" class="ap-input" accept="image/*">
                            <small class="text-muted" style="font-size: 11px;">الحد الأقصى للحجم 4 ميجابايت (PNG, JPG, WEBP)</small>
                        </div>
                    </div>
                </div>

                {{-- الحالة والتوثيق --}}
                <div class="ap-card" style="margin-bottom: 20px;">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 18])
                            الحالة والاعتماد
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">حالة الحساب <span>*</span></label>
                            <select name="status" class="ap-select" required>
                                <option value="active" @selected(old('status', $agent->status) === 'active')>نشط (يظهر في الموقع)</option>
                                <option value="inactive" @selected(old('status', $agent->status) === 'inactive')>معطل (مخفي)</option>
                            </select>
                        </div>

                        <div class="ap-field">
                            <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                                <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $agent->isVerified()))>
                                <span class="fw-bold" style="font-size: 14px;">اعتماد وتوثيق الوكيل (Verified Badge)</span>
                            </label>
                            <small class="text-muted" style="display:block; margin-top: 4px; font-size: 11px;">
                                الوكيل المعتمد تظهر بجانب اسمه علامة التوثيق الزرقاء/الخضراء في الموقع لإعطاء ثقة للمشترين.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- أزرار الحفظ --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="ap-btn ap-btn--gold" style="flex: 1;">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                        {{ $isEdit ? 'حفظ التعديلات' : 'إضافة الوكيل / الشركة' }}
                    </button>
                    <a href="{{ admin_route('admin.agents.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                </div>
            </div>
        </div>
    </form>
@endsection

