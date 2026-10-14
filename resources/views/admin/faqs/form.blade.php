@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = !empty($item);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
@endphp

@section('page_actions')
    <a href="{{ admin_route('admin.faqs.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'arrow-right', 'size' => 16])
        رجوع للأسئلة الشائعة
    </a>
@endsection

@section('content')
    <form method="post"
        action="{{ $isEdit ? admin_route('admin.faqs.update', $item['id']) : admin_route('admin.faqs.store') }}">
        @csrf
        @if ($isEdit)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            {{-- المحتوى بالعربية --}}
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 18])
                            تفاصيل السؤال (بالعربية)
                        </h2>
                        <span class="ap-badge ap-badge--success">إلزامي</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">التصنيف <span>*</span></label>
                            <input type="text" name="category" list="faq_categories_list" class="ap-input" required
                                value="{{ $old('category', $item['category'] ?? '') }}"
                                placeholder="اختر من القائمة أو اكتب تصنيفاً جديداً...">
                            <datalist id="faq_categories_list">
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}"></option>
                                @endforeach
                            </datalist>
                            <div class="ap-help" style="font-size: 12px; color: #64748b; margin-top: 4px;">
                                يمكنك اختيار تصنيف موجود من القائمة المنسدلة أو كتابة تصنيف جديد مباشرة.
                            </div>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">السؤال <span>*</span></label>
                            <input type="text" name="question" class="ap-input" required
                                value="{{ $old('question', $item['question'] ?? '') }}"
                                placeholder="مثال: كيف أبدأ الاستثمار العقاري من خلال المنصة؟">
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">الإجابة <span>*</span></label>
                            <textarea name="answer" class="ap-textarea" rows="7" required
                                placeholder="اكتب الإجابة المفصلة للسؤال هنا...">{{ $old('answer', $item['answer'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 16px; display: flex; gap: 12px; align-items: center;">
                    <button type="submit" class="ap-btn ap-btn--primary">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 18])
                        {{ $isEdit ? 'تحديث السؤال الشائع' : 'إضافة السؤال الشائع' }}
                    </button>
                    <a href="{{ admin_route('admin.faqs.index') }}" class="ap-btn ap-btn--ghost">
                        إلغاء
                    </a>
                </div>
            </div>

            {{-- المحتوى بالإنجليزية (اختياري) --}}
            <div>
                @if (config('locales.admin_english_enabled'))
                    <div class="ap-card">
                        <div class="ap-card__head">
                            <h2 class="ap-card__title">
                                @include('admin.partials.icon', ['iconName' => 'external', 'size' => 18])
                                English Translation
                            </h2>
                            <span class="ap-badge ap-badge--secondary">Optional</span>
                        </div>
                        <div class="ap-card__body">
                            <div class="ap-field">
                                <label class="ap-label">Category (EN)</label>
                                <input type="text" name="category_en" class="ap-input"
                                    value="{{ $old('category_en', $item['category_en'] ?? '') }}"
                                    placeholder="e.g. Real Estate Buying & Investing">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">Question (EN)</label>
                                <input type="text" name="question_en" class="ap-input"
                                    value="{{ $old('question_en', $item['question_en'] ?? '') }}"
                                    placeholder="e.g. How do I start real estate investment?">
                            </div>

                            <div class="ap-field">
                                <label class="ap-label">Answer (EN)</label>
                                <textarea name="answer_en" class="ap-textarea" rows="7"
                                    placeholder="Write English answer here...">{{ $old('answer_en', $item['answer_en'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="ap-card" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                        <div class="ap-card__body" style="padding: 24px; text-align: center; color: #64748b;">
                            @include('admin.partials.icon', ['iconName' => 'help-circle', 'size' => 32])
                            <h4 style="margin: 12px 0 6px; font-weight: 700; color: #334155;">نصيحة لصياغة الأسئلة</h4>
                            <p style="font-size: 13px; line-height: 1.6; margin: 0;">
                                اجعل السؤال واضحاً ومباشراً كما يطرحه المستخدم تماماً، واحرص على أن تكون الإجابة شاملة ومختصرة توفر الحل بأسرع وسيلة.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection

