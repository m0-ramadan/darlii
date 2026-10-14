@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = $post->exists;
    $ar = $post->translations->firstWhere('locale', 'ar') ?? new \App\Models\PostTranslation(['locale' => 'ar']);
    $en = $post->translations->firstWhere('locale', 'en') ?? new \App\Models\PostTranslation(['locale' => 'en']);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
    $currentImage = $post->featured_image
        ? (str_starts_with((string) $post->featured_image, 'http')
            ? $post->featured_image
            : (str_starts_with((string) $post->featured_image, 'storage/')
                ? asset($post->featured_image)
                : asset('storage/' . ltrim($post->featured_image, '/'))))
        : null;
@endphp

@section('page_actions')
    <a href="{{ admin_route('admin.posts.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع للقائمة</a>
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $isEdit ? admin_route('admin.posts.update', $post) : admin_route('admin.posts.store') }}">
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
                            <label class="ap-label">عنوان المقال <span>*</span></label>
                            <input type="text" name="translations[ar][title]" class="ap-input" required
                                value="{{ $old('translations.ar.title', $ar->title) }}" placeholder="مثال: أفضل مناطق الاستثمار العقاري">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الرابط (slug)</label>
                            <input type="text" name="translations[ar][slug]" class="ap-input"
                                value="{{ $old('translations.ar.slug', $ar->slug) }}" placeholder="best-investment-areas">
                            <p class="ap-help">اتركه فارغاً ليُولَّد تلقائياً من العنوان.</p>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المقتطف</label>
                            <textarea name="translations[ar][excerpt]" class="ap-textarea" rows="3"
                                placeholder="ملخص قصير يظهر في قائمة المقالات…">{{ $old('translations.ar.excerpt', $ar->excerpt) }}</textarea>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المحتوى</label>
                            <textarea name="translations[ar][content]" class="ap-textarea" rows="14"
                                placeholder="اكتب المقال بالعربية…">{{ $old('translations.ar.content', $ar->content) }}</textarea>
                        </div>
                    </div>
                </div>

                @if (config('locales.admin_english_enabled'))
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
                            <label class="ap-label">Title (EN)</label>
                            <input type="text" name="translations[en][title]" class="ap-input"
                                value="{{ $old('translations.en.title', $en->title) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Slug (EN)</label>
                            <input type="text" name="translations[en][slug]" class="ap-input"
                                value="{{ $old('translations.en.slug', $en->slug) }}">
                            <p class="ap-help">اتركه فارغاً ليُولَّد تلقائياً من العنوان الإنجليزي.</p>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Excerpt (EN)</label>
                            <textarea name="translations[en][excerpt]" class="ap-textarea" rows="3">{{ $old('translations.en.excerpt', $en->excerpt) }}</textarea>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">Content (EN)</label>
                            <textarea name="translations[en][content]" class="ap-textarea" rows="14">{{ $old('translations.en.content', $en->content) }}</textarea>
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
                            إعدادات النشر
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">حالة المقال <span>*</span></label>
                            <select name="status" class="ap-select" required>
                                @foreach ($statuses ?? ['draft', 'published'] as $status)
                                    <option value="{{ $status }}" @selected($old('status', $post->status ?: 'draft') === $status)>
                                        {{ status_label($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">الكاتب</label>
                            <input type="text" class="ap-input" disabled
                                value="{{ $post->author?->name ?? auth()->user()?->name ?? 'سيُسجَّل باسمك عند الإنشاء' }}">
                            @if ($isEdit)
                                <p class="ap-help">تاريخ النشر:
                                    {{ $post->published_at ? $post->published_at->format('Y-m-d H:i') : 'لم يُنشر بعد' }}
                                </p>
                            @endif
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">الصورة الرئيسية</label>
                            <input type="file" name="featured_image" class="ap-input" accept="image/*">
                            <p class="ap-help">
                                المقاسات المقترحة 1200×630، والحد الأقصى {{ (int) setting('storage.max_upload_mb', 8) }} ميجابايت.
                            </p>
                            @if ($currentImage)
                                <img src="{{ $currentImage }}" alt="" class="ap-table__thumb" style="width: 100%; height: 150px; margin-top: 8px;">
                                <label class="ap-check" style="margin-top: 8px;">
                                    <input type="checkbox" name="remove_featured_image" value="1">
                                    <span>حذف الصورة الحالية</span>
                                </label>
                            @endif
                        </div>
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
                        @if (config('locales.admin_english_enabled'))
                        <div class="ap-field">
                            <label class="ap-label">SEO title (EN)</label>
                            <input type="text" name="translations[en][seo_title]" class="ap-input"
                                value="{{ $old('translations.en.seo_title', $en->seo_title) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">SEO description (EN)</label>
                            <textarea name="translations[en][seo_description]" class="ap-textarea" rows="3">{{ $old('translations.en.seo_description', $en->seo_description) }}</textarea>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="ap-card border-primary border-opacity-25 bg-primary bg-opacity-10 mb-3">
                    <div class="ap-card__body py-3">
                        <label class="d-flex align-items-center gap-3 cursor-pointer m-0" style="cursor: pointer;">
                            <input type="checkbox" name="send_broadcast_notification" value="1" class="form-check-input mt-0 fs-5" id="postSendBroadcastNotif">
                            <div>
                                <strong class="d-block text-dark" style="font-size: 13.5px;">
                                    @include('admin.partials.icon', ['iconName' => 'bell', 'size' => 15])
                                    إرسال إشعار فوري لجميع المستخدمين
                                </strong>
                                <span class="text-muted small">بث إشعار فوري بهذا المقال إلى هواتف ومتصفحات المشتركين.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body d-flex gap-2 flex-wrap">
                        <button class="ap-btn ap-btn--gold">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء المقال' }}
                        </button>
                        <a href="{{ admin_route('admin.posts.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
