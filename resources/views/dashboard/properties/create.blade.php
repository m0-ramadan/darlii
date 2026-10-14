@extends('layouts.public-shell')

@section('title', 'إضافة عقار جديد')

@section('content')
<style>
    :root {
        --dash-bg: #f8fafc;
        --dash-card: #ffffff;
        --dash-border: #e2e8f0;
        --dash-text: #0f172a;
        --dash-muted: #64748b;
        --dash-hover: #f1f5f9;
        --dash-hero: linear-gradient(180deg, #edf3f8 0%, #f8fafc 100%);
        --dash-primary: #db1d23;
        --dash-primary-hover: #b9151b;
        --dash-gold: #c8a45c;
    }

    html[data-theme="dark"] {
        --dash-bg: #0b1320;
        --dash-card: #111e33;
        --dash-border: #1e293b;
        --dash-text: #f8fafc;
        --dash-muted: #94a3b8;
        --dash-hover: #16243d;
        --dash-hero: linear-gradient(180deg, #0e192c 0%, #0b1320 100%);
    }

    .darli-dash-wrapper {
        background-color: var(--dash-bg);
        min-height: 80vh;
        padding-bottom: 80px;
    }

    .darli-dash-hero {
        background: var(--dash-hero);
        border-bottom: 1px solid var(--dash-border);
        padding: 40px 0 25px 0;
        margin-bottom: 30px;
    }

    .darli-dash-breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: var(--dash-muted);
        margin-bottom: 14px;
    }
    .darli-dash-breadcrumb a {
        color: var(--dash-muted);
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .darli-dash-breadcrumb a:hover {
        color: var(--dash-primary);
    }
    .darli-dash-breadcrumb .sep {
        font-size: 11px;
        opacity: 0.6;
    }

    .darli-dash-title {
        font-size: 28px;
        font-weight: 800;
        color: var(--dash-text);
        margin: 0 0 6px 0;
        line-height: 1.3;
    }

    .darli-dash-subtitle {
        color: var(--dash-muted);
        font-size: 14.5px;
        margin: 0;
    }

    .darli-form-card {
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        max-width: 860px;
        margin: 0 auto;
    }

    .darli-form-label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: var(--dash-text);
        margin-bottom: 8px;
    }

    .darli-form-control,
    .darli-form-select {
        width: 100%;
        background: var(--dash-card);
        border: 1px solid var(--dash-border);
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 14.5px;
        color: var(--dash-text);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .darli-form-control:focus,
    .darli-form-select:focus {
        outline: none;
        border-color: var(--dash-primary);
        box-shadow: 0 0 0 3px rgba(219, 29, 35, 0.12);
    }

    .darli-alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        border-radius: 14px;
        padding: 16px 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 14px;
    }

    .darli-btn-submit {
        background: var(--dash-primary);
        color: #ffffff;
        font-size: 16px;
        font-weight: 800;
        padding: 14px 28px;
        border-radius: 10px;
        border: none;
        width: 100%;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(219, 29, 35, 0.25);
        transition: all 0.2s ease;
    }
    .darli-btn-submit:hover {
        background: var(--dash-primary-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(219, 29, 35, 0.35);
    }
</style>

<div class="darli-dash-wrapper">
    {{-- Hero Banner --}}
    <div class="darli-dash-hero">
        <div class="container">
            <div class="darli-dash-breadcrumb">
                <a href="{{ route('home') }}">الرئيسية</a>
                <span class="sep">›</span>
                <a href="{{ route('dashboard.index') }}">لوحة التحكم</a>
                <span class="sep">›</span>
                <a href="{{ route('dashboard.properties.index') }}">عقاراتي</a>
                <span class="sep">›</span>
                <span style="color: var(--dash-text); font-weight: 700;">إضافة عقار جديد</span>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="darli-dash-title">إضافة عقار جديد</h1>
                    <p class="darli-dash-subtitle">
                        أدخل بيانات وتفاصيل العقار لإرساله لمراجعة وتأكيد الإدارة قبل النشر بالموقع
                    </p>
                </div>
                <div>
                    <a href="{{ route('dashboard.properties.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 13.5px;">
                        ← العودة إلى عقاراتي
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="container">
        <div class="darli-form-card">

            {{-- تنبيه المراجعة والاعتماد --}}
            <div class="darli-alert-warning">
                <span style="font-size: 24px; line-height: 1;">⏳</span>
                <div>
                    <strong style="color: #b45309;">تنويه هام:</strong>
                    عند إضافة العقار سيبقى في حسابك بحالة <strong>«قيد المراجعة»</strong> حتى يقوم فريق دارلي بمراجعة البيانات والتأكد منها، وفور الاعتماد سيتم نشره بالموقع فوراً ليظهر لآلاف الباحثين عن عقارات.
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mb-4" style="border-radius: 10px;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.properties.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf

                <div class="col-12">
                    <label class="darli-form-label">اسم / عنوان العقار <span class="text-danger">*</span></label>
                    <input class="darli-form-control" name="translations[ar][name]" value="{{ old('translations.ar.name') }}" placeholder="مثال: شقة تمليك 140م تشطيب سوبر لوكس بالحي الأول" required>
                </div>

                <div class="col-md-6">
                    <label class="darli-form-label">الغرض <span class="text-danger">*</span></label>
                    <select class="darli-form-select" name="purpose">
                        <option value="sale" {{ old('purpose') === 'sale' ? 'selected' : '' }}>للبيع</option>
                        <option value="rent" {{ old('purpose') === 'rent' ? 'selected' : '' }}>للإيجار</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="darli-form-label">السعر المطلوب (ج.م) <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="0" class="darli-form-control" name="price" value="{{ old('price') }}" placeholder="مثال: 1250000" required>
                    <input type="hidden" name="currency_id" value="{{ $currencies->first()?->id ?? 1 }}">
                </div>

                <div class="col-md-4">
                    <label class="darli-form-label">المساحة (م²)</label>
                    <input type="number" step="0.1" min="0" class="darli-form-control" name="area" value="{{ old('area') }}" placeholder="مثال: 140">
                </div>

                <div class="col-md-4">
                    <label class="darli-form-label">عدد الغرف</label>
                    <input class="darli-form-control" type="number" min="0" max="50" name="bedrooms" value="{{ old('bedrooms') }}" placeholder="3">
                </div>

                <div class="col-md-4">
                    <label class="darli-form-label">عدد الحمامات</label>
                    <input class="darli-form-control" type="number" min="0" max="20" name="bathrooms" value="{{ old('bathrooms') }}" placeholder="1">
                </div>

                <div class="col-12">
                    <label class="darli-form-label">تفاصيل ووصف العقار</label>
                    <textarea class="darli-form-control" rows="4" name="translations[ar][description]" placeholder="اكتب مواصفات العقار، الطابق، الخدمات المتوفرة، الموقع بالتحديد...">{{ old('translations.ar.description') }}</textarea>
                </div>

                <div class="col-12">
                    @include('admin.partials.map-picker', [
                        'latId' => 'prop-latitude-user',
                        'lngId' => 'prop-longitude-user',
                        'latName' => 'latitude',
                        'lngName' => 'longitude',
                        'latValue' => old('latitude'),
                        'lngValue' => old('longitude'),
                        'containerId' => 'prop-user-map-box',
                        'mapId' => 'prop-user-map-leaflet',
                        'defaultLat' => 29.0724,
                        'defaultLng' => 31.0962,
                    ])
                </div>

                {{-- قسم التخطيط الهندسي للشقة --}}
                <div class="col-12 mt-4 pt-3" style="border-top: 1px dashed var(--dash-border);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size: 20px;">📐</span>
                        <h6 class="fw-bold mb-0" style="color: var(--dash-text); font-size: 16px;">التخطيط الهندسي للشقة (المخطط المعماري)</h6>
                        <span class="badge" style="background: var(--dash-hover); color: var(--dash-muted); font-size: 11px;">اختياري</span>
                    </div>
                    <p style="font-size: 13px; color: var(--dash-muted); margin-bottom: 14px;">
                        أرفق رسم أو مخطط كروكي/هندسي للشقة يوضح تقسيم الغرف والمساحات والريسبشن لمساعدة المشترين على تصور الشقة بدقة.
                    </p>

                    <div style="background: var(--dash-hover); border: 1px solid var(--dash-border); border-radius: 12px; padding: 18px;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="darli-form-label" style="font-size: 13px;">عنوان أو اسم المخطط</label>
                                <input type="text" name="floor_plan_name" class="darli-form-control" value="{{ old('floor_plan_name') }}" placeholder="مثال: المخطط المعماري للشقة 140م">
                            </div>
                            <div class="col-md-6">
                                <label class="darli-form-label" style="font-size: 13px;">صورة التخطيط الهندسي</label>
                                <input type="file" name="floor_plan_image" class="darli-form-control" accept="image/*">
                                <small style="font-size: 11px; color: var(--dash-muted);">صيغ مدعومة: JPG, PNG, WEBP (بحد أقصى 10 ميجابايت)</small>
                            </div>
                            <div class="col-12">
                                <label class="darli-form-label" style="font-size: 13px;">ملاحظات معمارية أو وصف المخطط</label>
                                <textarea name="floor_plan_description" class="darli-form-control" rows="2" placeholder="مثال: مدخل خاص، ريسبشن 3 قطع، مطبخ أمريكان مفتوح، غرف نوم بحرية غير مجروحة...">{{ old('floor_plan_description') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 pt-3">
                    <button type="submit" class="darli-btn-submit">
                        إرسال العقار للمراجعة والاعتماد ✓
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
