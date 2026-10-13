@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = $page->exists;
    $ar = $page->translations->firstWhere('locale', 'ar') ?? new \App\Models\PageTranslation(['locale' => 'ar']);
    $en = $page->translations->firstWhere('locale', 'en') ?? new \App\Models\PageTranslation(['locale' => 'en']);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
@endphp

@section('page_actions')
    <a href="{{ admin_route('admin.pages.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
@endsection

@section('content')
    <form method="post"
        action="{{ $isEdit ? admin_route('admin.pages.update', $page) : admin_route('admin.pages.store') }}">
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
                            @include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 18])
                            المحتوى العربي
                        </h2>
                        <span class="ap-badge ap-badge--success">إلزامي</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">عنوان الصفحة <span>*</span></label>
                            <input type="text" name="translations[ar][title]" class="ap-input" required
                                value="{{ $old('translations.ar.title', $ar->title) }}" placeholder="مثال: من نحن">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الرابط (slug)</label>
                            <input type="text" name="translations[ar][slug]" class="ap-input"
                                value="{{ $old('translations.ar.slug', $ar->slug) }}" placeholder="about-us">
                            <p class="ap-help">اتركه فارغاً ليُولَّد تلقائياً من العنوان.</p>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المحتوى</label>
                            <textarea name="translations[ar][content]" class="ap-textarea" rows="12"
                                placeholder="اكتب محتوى الصفحة بالعربية…">{{ $old('translations.ar.content', $ar->content) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'external', 'size' => 18])
                            المحتوى الإنجليزي
                        </h2>
                        <span class="ap-badge ap-badge--secondary">اختياري</span>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">Page title (EN)</label>
                            <input type="text" name="translations[en][title]" class="ap-input"
                                value="{{ $old('translations.en.title', $en->title) }}" placeholder="About us">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Slug (EN)</label>
                            <input type="text" name="translations[en][slug]" class="ap-input"
                                value="{{ $old('translations.en.slug', $en->slug) }}" placeholder="about-us">
                            <p class="ap-help">اتركه فارغاً ليُولَّد تلقائياً من العنوان الإنجليزي.</p>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Content (EN)</label>
                            <textarea name="translations[en][content]" class="ap-textarea" rows="12"
                                placeholder="Page content in English…">{{ $old('translations.en.content', $en->content) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ------------------------------------------------ الإعدادات --}}
            <div style="position: sticky; top: 90px;">
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'settings', 'size' => 18])
                            إعدادات الصفحة
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">المفتاح الداخلي</label>
                            <input type="text" name="key" class="ap-input" value="{{ $old('key', $page->key) }}"
                                placeholder="about-us">
                            <p class="ap-help">معرّف ثابت للصفحة داخل الكود (اختياري) ويجب ألا يتكرر.</p>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">حالة النشر <span>*</span></label>
                            <select name="status" class="ap-select" required>
                                @foreach ($statuses ?? ['draft', 'published'] as $status)
                                    <option value="{{ $status }}" @selected($old('status', $page->status ?: 'draft') === $status)>
                                        {{ status_label($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @if ($isEdit)
                            <p class="ap-help">
                                تاريخ النشر الحالي:
                                {{ $page->published_at ? \Illuminate\Support\Carbon::parse($page->published_at)->format('Y-m-d H:i') : 'لم تُنشر بعد' }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'search', 'size' => 18])
                            تحسين محركات البحث
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">SEO title (AR)</label>
                            <input type="text" name="translations[ar][seo_title]" class="ap-input"
                                value="{{ $old('translations.ar.seo_title', $ar->seo_title) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">SEO description (AR)</label>
                            <textarea name="translations[ar][seo_description]" class="ap-textarea" rows="3">{{ $old('translations.ar.seo_description', $ar->seo_description) }}</textarea>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">SEO title (EN)</label>
                            <input type="text" name="translations[en][seo_title]" class="ap-input"
                                value="{{ $old('translations.en.seo_title', $en->seo_title) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">SEO description (EN)</label>
                            <textarea name="translations[en][seo_description]" class="ap-textarea" rows="3">{{ $old('translations.en.seo_description', $en->seo_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body d-flex gap-2 flex-wrap">
                        <button class="ap-btn ap-btn--gold">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء الصفحة' }}
                        </button>
                        <a href="{{ admin_route('admin.pages.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
