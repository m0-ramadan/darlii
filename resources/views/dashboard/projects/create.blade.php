@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

@section('title', $isAr ? 'إضافة مشروع جديد' : 'Add New Project')

@section('content')
<div class="container page-section" style="padding-top: 35px; padding-bottom: 70px;">
    <div class="mx-auto" style="max-width: 800px;">
        <div class="d-flex align-items-center gap-2 mb-2">
            <a href="{{ route('dashboard.index', ['locale' => $locale]) }}" class="text-muted text-decoration-none" style="font-size: 13px;">لوحة التحكم</a>
            <span class="text-muted">/</span>
            <a href="{{ route('dashboard.projects.index', ['locale' => $locale]) }}" class="text-muted text-decoration-none" style="font-size: 13px;">المشاريع</a>
            <span class="text-muted">/</span>
            <span class="fw-bold" style="font-size: 13px;">مشروع جديد</span>
        </div>

        <h1 class="h3 fw-bold mb-3">إضافة مشروع عقاري جديد</h1>
        <p class="text-muted mb-4" style="font-size: 14px;">أدخل تفاصيل المشروع الجديد والوحدات السكنية والتجارية التابعة له</p>

        @if ($errors->any())
            <div class="alert alert-danger mb-4" style="border-radius: 12px;">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm border-0 p-4 p-md-5" style="border-radius: 16px;">
            <form method="POST" action="{{ route('dashboard.projects.store', ['locale' => $locale]) }}">
                @csrf

                <!-- Basic Info -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold mb-3" style="color: #111e33;">البيانات الأساسية للمشروع</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 14px;">اسم المشروع <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('name') }}" placeholder="مثال: كمبوند درة النيل أو برج الصفوة" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 14px;">الموقع / المدينة <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select" style="border-radius: 10px; padding: 10px 14px;" required>
                                <option value="">— اختر الموقع —</option>
                                @foreach ($locations as $loc)
                                    <option value="{{ $loc->id }}" @selected(old('location_id') == $loc->id)>
                                        {{ $loc->translation()?->name ?: $loc->slug }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 14px;">اسم المطور / المستثمر</label>
                            <input type="text" name="investor_name" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('investor_name', auth()->user()->agency?->name) }}" placeholder="اسم شركتكم أو الشريك المطور">
                        </div>
                    </div>
                </div>

                <!-- Counts & Dates -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold mb-3" style="color: #111e33;">تفاصيل البناء والتسليم</h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 14px;">عدد العمارات / المباني</label>
                            <input type="number" name="blocks_count" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('blocks_count') }}" placeholder="مثال: 5" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 14px;">إجمالي عدد الشقق / الوحدات</label>
                            <input type="number" name="flats_count" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('flats_count') }}" placeholder="مثال: 120" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold" style="font-size: 14px;">عدد الأدوار بالمبنى</label>
                            <input type="number" name="floors_count" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('floors_count') }}" placeholder="مثال: 11" min="0">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 14px;">تاريخ فتح البيع / الحجز</label>
                            <input type="date" name="open_sale_date" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('open_sale_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" style="font-size: 14px;">تاريخ الاستلام المتوقع</label>
                            <input type="date" name="completion_date" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('completion_date') }}">
                        </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div class="mb-4 pb-3 border-bottom">
                    <h5 class="fw-bold mb-3" style="color: #111e33;">الوصف والمميزات</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 14px;">نبذة موجزة عن المشروع</label>
                        <input type="text" name="short_description" class="form-control" style="border-radius: 10px; padding: 10px 14px;" value="{{ old('short_description') }}" placeholder="مثال: كمبوند سكني فاخر متكامل الخدمات على النيل مباشرة">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 14px;">الوصف التفصيلي</label>
                        <textarea name="description" class="form-control" rows="4" style="border-radius: 10px;" placeholder="اكتب مواصفات المشروع، أنظمة السداد، ومميزات الموقع...">{{ old('description') }}</textarea>
                    </div>

                    @if ($amenities->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 14px;">مرافق وخدمات المشروع</label>
                            <div class="row g-2">
                                @foreach ($amenities as $amenity)
                                    <div class="col-6 col-md-4">
                                        <label class="d-flex align-items-center gap-2 p-2 border rounded-2" style="font-size: 13px; cursor: pointer;">
                                            <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" @checked(in_array($amenity->id, old('amenities', []))) class="form-check-input mt-0">
                                            <span>{{ $amenity->translation()?->name ?: $amenity->slug }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <a href="{{ route('dashboard.projects.index', ['locale' => $locale]) }}" class="btn btn-outline-secondary" style="border-radius: 8px;">
                        إلغاء
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="background: #C8A45C; border-color: #C8A45C; border-radius: 8px;">
                        حفظ وإرسال المشروع للاعتماد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

