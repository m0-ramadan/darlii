@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = $testimonial->exists;
    $ar = $testimonial->translations->firstWhere('locale', 'ar') ?? new \App\Models\TestimonialTranslation(['locale' => 'ar']);
    $en = $testimonial->translations->firstWhere('locale', 'en') ?? new \App\Models\TestimonialTranslation(['locale' => 'en']);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
    $currentImage = $testimonial->image_path
        ? (str_starts_with((string) $testimonial->image_path, 'http')
            ? $testimonial->image_path
            : (str_starts_with((string) $testimonial->image_path, 'storage/')
                ? asset($testimonial->image_path)
                : asset('storage/' . ltrim($testimonial->image_path, '/'))))
        : null;
@endphp

@section('page_actions')
    <a href="{{ admin_route('admin.testimonials.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $isEdit ? admin_route('admin.testimonials.update', $testimonial) : admin_route('admin.testimonials.store') }}">
        @csrf
        @if ($isEdit)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            {{-- ------------------------------------------------ المحتوى --}}
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'message', 'size' => 18])
                            الرأي بالعربية
                        </h2>
                        <span class="ap-badge ap-badge--success">إلزامي</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">اسم العميل <span>*</span></label>
                            <input type="text" name="translations[ar][name]" class="ap-input" required
                                value="{{ $old('translations.ar.name', $ar->name) }}" placeholder="مثال: أحمد محمود">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المسمى الوظيفي</label>
                            <input type="text" name="translations[ar][position]" class="ap-input"
                                value="{{ $old('translations.ar.position', $ar->position) }}" placeholder="مثال: مستثمر عقاري">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">نص الرأي <span>*</span></label>
                            <textarea name="translations[ar][content]" class="ap-textarea" rows="7" required
                                placeholder="اكتب رأي العميل بالعربية…">{{ $old('translations.ar.content', $ar->content) }}</textarea>
                        </div>
                    </div>
                </div>

                @if (config('locales.admin_english_enabled'))
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 18])
                            الرأي بالإنجليزية
                        </h2>
                        <span class="ap-badge ap-badge--secondary">اختياري</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">Client name (EN)</label>
                            <input type="text" name="translations[en][name]" class="ap-input"
                                value="{{ $old('translations.en.name', $en->name) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Position (EN)</label>
                            <input type="text" name="translations[en][position]" class="ap-input"
                                value="{{ $old('translations.en.position', $en->position) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Testimonial (EN)</label>
                            <textarea name="translations[en][content]" class="ap-textarea" rows="7">{{ $old('translations.en.content', $en->content) }}</textarea>
                            <p class="ap-help">إذا أدخلت الاسم الإنجليزي فيجب إدخال نص الرأي بالإنجليزية أيضاً.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- ------------------------------------------------ الإعدادات --}}
            <div style="position: sticky; top: 90px;">
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 18])
                            إعدادات العرض
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">التقييم <span>*</span></label>
                            <select name="rating" class="ap-select" required>
                                @for ($star = 5; $star >= 1; $star--)
                                    <option value="{{ $star }}" @selected((string) $old('rating', $testimonial->rating ?: 5) === (string) $star)>
                                        {{ $star }} {{ $star === 1 ? 'نجمة' : 'نجوم' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">ترتيب الظهور</label>
                            <input type="number" name="sort_order" class="ap-input" min="0" max="65535"
                                value="{{ $old('sort_order', $testimonial->sort_order ?? 0) }}">
                            <p class="ap-help">الأرقام الأصغر تظهر أولاً.</p>
                        </div>
                        <div class="ap-field">
                            <label class="ap-check">
                                <input type="checkbox" name="status" value="1"
                                    @checked((bool) $old('status', $testimonial->status ?? true))>
                                <span>مفعّل ويظهر في الموقع</span>
                            </label>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">صورة العميل</label>
                            <input type="file" name="image" class="ap-input" accept="image/*">
                            <p class="ap-help">الحد الأقصى {{ (int) setting('storage.max_upload_mb', 8) }} ميجابايت.</p>
                            @if ($currentImage)
                                <img src="{{ $currentImage }}" alt="" class="ap-table__thumb" style="width: 100%; height: 150px; margin-top: 8px;">
                                <label class="ap-check" style="margin-top: 8px;">
                                    <input type="checkbox" name="remove_image" value="1">
                                    <span>حذف الصورة الحالية</span>
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body d-flex gap-2 flex-wrap">
                        <button class="ap-btn ap-btn--gold">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                            {{ $isEdit ? 'حفظ التعديلات' : 'إضافة الرأي' }}
                        </button>
                        <a href="{{ admin_route('admin.testimonials.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
