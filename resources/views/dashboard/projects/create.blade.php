@extends('layouts.public-shell')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

@section('title', 'إضافة مشروع جديد')

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
        border-color: var(--dash-gold);
        box-shadow: 0 0 0 3px rgba(200, 164, 92, 0.18);
    }

    .darli-section-title {
        font-size: 16px;
        font-weight: 800;
        color: var(--dash-text);
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .darli-btn-submit {
        background: #111e33;
        color: #f8e3a6;
        font-size: 15.5px;
        font-weight: 800;
        padding: 13px 30px;
        border-radius: 10px;
        border: 1px solid rgba(200, 164, 92, 0.5);
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(17, 30, 51, 0.2);
        transition: all 0.2s ease;
    }
    .darli-btn-submit:hover {
        background: #1e293b;
        transform: translateY(-1px);
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
                <a href="{{ route('dashboard.projects.index') }}">المشاريع</a>
                <span class="sep">›</span>
                <span style="color: var(--dash-text); font-weight: 700;">مشروع جديد</span>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="darli-dash-title">إضافة مشروع عقاري جديد</h1>
                    <p class="darli-dash-subtitle">
                        أدخل تفاصيل المشروع الجديد والوحدات السكنية والتجارية التابعة له
                    </p>
                </div>
                <div>
                    <a href="{{ route('dashboard.projects.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px; font-weight: 600; font-size: 13.5px;">
                        ← العودة للمشاريع
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="container">
        <div class="darli-form-card">

            @if ($errors->any())
                <div class="alert alert-danger mb-4" style="border-radius: 12px;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.projects.store') }}">
                @csrf

                <!-- Basic Info -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="darli-section-title">
                        <span>🏢</span> البيانات الأساسية للمشروع
                    </h5>

                    <div class="mb-3">
                        <label class="darli-form-label">اسم المشروع <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="darli-form-control" value="{{ old('name') }}" placeholder="مثال: كمبوند درة النيل أو برج الصفوة" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="darli-form-label">الموقع / المدينة <span class="text-danger">*</span></label>
                            <select name="location_id" class="darli-form-select" required>
                                <option value="">— اختر الموقع —</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" @selected(old('location_id') == $loc->id)>
                                        {{ $loc->translation()?->name ?: $loc->slug }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="darli-form-label">اسم المطور / المستثمر</label>
                            <input type="text" name="investor_name" class="darli-form-control" value="{{ old('investor_name', auth()->user()->agency?->name) }}" placeholder="اسم شركتكم أو الشريك المطور">
                        </div>
                    </div>
                </div>

                <!-- Counts & Dates -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="darli-section-title">
                        <span>📐</span> تفاصيل البناء والتسليم
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="darli-form-label">عدد العمارات / المباني</label>
                            <input type="number" name="blocks_count" class="darli-form-control" value="{{ old('blocks_count') }}" placeholder="مثال: 5" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="darli-form-label">إجمالي عدد الوحدات</label>
                            <input type="number" name="flats_count" class="darli-form-control" value="{{ old('flats_count') }}" placeholder="مثال: 120" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="darli-form-label">عدد الأدوار بالمبنى</label>
                            <input type="number" name="floors_count" class="darli-form-control" value="{{ old('floors_count') }}" placeholder="مثال: 11" min="0">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="darli-form-label">تاريخ فتح البيع / الحجز</label>
                            <input type="date" name="open_sale_date" class="darli-form-control" value="{{ old('open_sale_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="darli-form-label">تاريخ الاستلام المتوقع</label>
                            <input type="date" name="completion_date" class="darli-form-control" value="{{ old('completion_date') }}">
                        </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="darli-section-title">
                        <span>📝</span> الوصف والمميزات
                    </h5>

                    <div class="mb-3">
                        <label class="darli-form-label">نبذة موجزة عن المشروع</label>
                        <input type="text" name="short_description" class="darli-form-control" value="{{ old('short_description') }}" placeholder="مثال: كمبوند سكني فاخر متكامل الخدمات على النيل مباشرة">
                    </div>

                    <div class="mb-3">
                        <label class="darli-form-label">الوصف التفصيلي</label>
                        <textarea name="description" class="darli-form-control" rows="4" placeholder="اكتب مواصفات المشروع، أنظمة السداد، ومميزات الموقع...">{{ old('description') }}</textarea>
                    </div>

                    @if ($amenities->isNotEmpty())
                        <div class="mb-3">
                            <label class="darli-form-label">مرافق وخدمات المشروع</label>
                            <div class="row g-2">
                                @foreach ($amenities as $amenity)
                                    <div class="col-6 col-md-4">
                                        <label class="d-flex align-items-center gap-2 p-2 border rounded-2" style="background: var(--dash-hover); font-size: 13px; cursor: pointer;">
                                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, old('amenities', []))) class="form-check-input mt-0">
                                            <span style="color: var(--dash-text);">{{ $amenity->translation()?->name ?: $amenity->slug }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <a href="{{ route('dashboard.projects.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                        إلغاء
                    </a>
                    <button type="submit" class="darli-btn-submit">
                        حفظ وإرسال المشروع للاعتماد ✓
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
