@extends('layouts.admin')

@php
    $isEdit = $career->exists;
    $ar = $isEdit ? $career->translation('ar') : null;
    $en = $isEdit ? $career->translation('en') : null;
    $title = $isEdit ? 'تعديل الوظيفة: ' . $career->title : 'إضافة فرصة عمل جديدة';
@endphp

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $isEdit ? ($career->department ?: 'وظيفة بالمنصة') : 'أدخل تفاصيل الوظيفة والشروط والمزايا لعرضها في قسم التوظيف')

@section('page_actions')
    <a href="{{ admin_route('admin.careers.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
@endsection

@section('content')
    <form method="post" action="{{ $isEdit ? admin_route('admin.careers.update', $career) : admin_route('admin.careers.store') }}">
        @csrf
        @if ($isEdit)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            {{-- العمود الأيمن: تفاصيل الوظيفة والمسمى --}}
            <div>
                <div class="ap-card" style="margin-bottom: 20px;">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18])
                            المسمى الوظيفي والوصف
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">المسمى الوظيفي بالعربية <span>*</span></label>
                            <input type="text" name="title_ar" class="ap-input" required
                                value="{{ old('title_ar', $ar?->title) }}"
                                placeholder="مثال: مستشار مبيعات واستثمار عقاري">
                        </div>

                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">المسمى الوظيفي بالإنجليزية</label>
                            <input type="text" name="title_en" class="ap-input" dir="ltr"
                                value="{{ old('title_en', $en?->title) }}"
                                placeholder="e.g. Senior Real Estate Sales Advisor">
                        </div>

                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">ملخص الوظيفة (بالعربية)</label>
                            <textarea name="summary_ar" class="ap-textarea" rows="3" placeholder="ملخص سريع يظهر في كارت الوظيفة وقوائم البحث...">{{ old('summary_ar', $ar?->summary) }}</textarea>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">تفاصيل المهام والمسؤوليات والشروط (بالعربية)</label>
                            <textarea name="description_ar" class="ap-textarea" rows="8" placeholder="اكتب المسؤوليات والمتطلبات والمؤهلات المطلوبة للوظيفة...">{{ old('description_ar', $ar?->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- العمود الأيسر: الإدارة، الموقع، الراتب، الحالة --}}
            <div>
                <div class="ap-card" style="margin-bottom: 20px;">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 18])
                            خصائص الوظيفة
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--2" style="margin-bottom: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">القسم / الإدارة</label>
                                <input type="text" name="department" class="ap-input"
                                    value="{{ old('department', $career->department) }}"
                                    placeholder="مثال: المبيعات / التسويق / الشؤون القانونية">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">مقر العمل</label>
                                <input type="text" name="location" class="ap-input"
                                    value="{{ old('location', $career->location ?? 'مدينة بني سويف') }}"
                                    placeholder="مثال: مدينة بني سويف / هجين">
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--2" style="margin-bottom: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">نوع الدوام</label>
                                <select name="employment_type" class="ap-select">
                                    @foreach (['دوام كامل', 'دوام جزئي', 'عن بُعد / هجين', 'تدريب عملي'] as $type)
                                        <option value="{{ $type }}" @selected(old('employment_type', $career->employment_type) === $type)>{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">مستوى الخبرة</label>
                                <select name="experience_level" class="ap-select">
                                    @foreach (['مبتدئ (Junior)', 'متوسط (Mid-Level)', 'خبير (Senior)', 'مدير / قائد فريق'] as $exp)
                                        <option value="{{ $exp }}" @selected(old('experience_level', $career->experience_level) === $exp)>{{ $exp }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="ap-grid ap-grid--2" style="margin-bottom: 15px;">
                            <div class="ap-field">
                                <label class="ap-label">الحد الأدنى للراتب (ج.م)</label>
                                <input type="number" name="salary_min" class="ap-input" min="0" step="500"
                                    value="{{ old('salary_min', $career->salary_min) }}" placeholder="10000">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">الحد الأقصى للراتب (ج.م)</label>
                                <input type="number" name="salary_max" class="ap-input" min="0" step="500"
                                    value="{{ old('salary_max', $career->salary_max) }}" placeholder="20000">
                            </div>
                        </div>

                        <div class="ap-field" style="margin-bottom: 15px;">
                            <label class="ap-label">آخر موعد للتقديم (Deadline)</label>
                            <input type="date" name="deadline" class="ap-input"
                                value="{{ old('deadline', $career->deadline?->format('Y-m-d')) }}">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">حالة النشر <span>*</span></label>
                            <select name="status" class="ap-select" required>
                                <option value="published" @selected(old('status', $career->status) === 'published')>منشور (متاح بالموقع)</option>
                                <option value="draft" @selected(old('status', $career->status) === 'draft')>مسودة (غير ظاهر)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- أزرار الحفظ --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="ap-btn ap-btn--gold" style="flex: 1;">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                        {{ $isEdit ? 'حفظ التعديلات' : 'نشر الوظيفة' }}
                    </button>
                    <a href="{{ admin_route('admin.careers.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                </div>
            </div>
        </div>
    </form>
@endsection

