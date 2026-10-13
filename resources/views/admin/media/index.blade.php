@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'رفع صور العقارات، استعراضها وحذفها من قرص التخزين')

@php
    $mediaUrl = function ($item): string {
        $path = (string) $item->path;

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (($item->disk ?? 'public') === 'legacy') {
            return asset('legacy/' . ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    };

    $humanSize = function ($bytes): string {
        $bytes = (int) $bytes;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' ميجابايت';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' كيلوبايت';
        }

        return $bytes . ' بايت';
    };
@endphp

@section('content')
    <div class="ap-grid ap-grid--3" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'photo', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي الصور</div>
                <div class="ap-stat__value">{{ number_format($totalCount) }}</div>
                <div class="ap-stat__hint">مطابقة للفلترة الحالية</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'chart-pie', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">الحجم الإجمالي</div>
                <div class="ap-stat__value" style="font-size: 19px;">{{ $humanSize($totalSize) }}</div>
                <div class="ap-stat__hint">مجموع أحجام الملفات</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">عقارات متاحة للرفع</div>
                <div class="ap-stat__value">{{ number_format($properties->count()) }}</div>
                <div class="ap-stat__hint">ضمن نطاق صلاحياتك</div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 18])
                رفع صور جديدة
            </h2>
            <p class="ap-card__subtitle">
                الصيغ المسموحة: jpg, jpeg, png, webp — بحد أقصى {{ (int) setting('storage.max_upload_mb', 8) }} ميجابايت للصورة
            </p>
        </div>
        <div class="ap-card__body">
            <form method="post" action="{{ admin_route('admin.media.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field">
                        <label class="ap-label" for="property_id">العقار <span>*</span></label>
                        <select id="property_id" name="property_id" class="ap-select" required>
                            <option value="">— اختر العقار —</option>
                            @foreach ($properties as $property)
                                <option value="{{ $property->id }}" @selected((string) old('property_id') === (string) $property->id)>
                                    {{ $property->reference_code }} — {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 50) }}
                                </option>
                            @endforeach
                        </select>
                        <div class="ap-help">سيتم تخزين الصور في مجلد العقار المحدد على قرص التخزين الافتراضي.</div>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label" for="images">الصور <span>*</span></label>
                        <input type="file" id="images" name="images[]" class="ap-input" accept="image/*" multiple required>
                        <div class="ap-help">يمكنك تحديد حتى 20 صورة في المرة الواحدة.</div>
                    </div>
                </div>
                <button type="submit" class="ap-btn ap-btn--gold">
                    @include('admin.partials.icon', ['iconName' => 'photo', 'size' => 16]) رفع الصور
                </button>
            </form>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 260px;">
                    <label class="ap-label">بحث باسم العقار أو كوده</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="مثال: DAR-2025 أو فيلا المعادي…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">النوع</label>
                    <select name="type" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    @include('admin.partials.icon', ['iconName' => 'search', 'size' => 16]) فلترة
                </button>
                <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'photo', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($media->total()) }}</span>
            </h2>
        </div>
        <div class="ap-card__body">
            @if ($media->isEmpty())
                <div class="ap-empty">
                    @include('admin.partials.icon', ['iconName' => 'photo', 'size' => 42])
                    <p>لا توجد صور مطابقة للفلترة الحالية.</p>
                </div>
            @else
                <div class="ap-thumb-grid">
                    @foreach ($media as $item)
                        @php
                            $parent = $propertiesById->get($item->property_id);
                            $parentLabel = $parent
                                ? $parent->reference_code . ' — ' . \Illuminate\Support\Str::limit($parent->displayName('ar'), 30)
                                : 'عقار #' . $item->property_id;
                        @endphp
                        <div class="ap-thumb">
                            <img src="{{ $mediaUrl($item) }}" alt="{{ $item->alt_text ?: $parentLabel }}" loading="lazy">
                            @if ($item->is_primary)
                                <span class="ap-thumb__badge ap-badge ap-badge--gold">رئيسية</span>
                            @endif
                            <div class="ap-thumb__meta">
                                <div class="fw-bold" title="{{ $parentLabel }}">{{ \Illuminate\Support\Str::limit($parentLabel, 32) }}</div>
                                <div class="text-muted">
                                    {{ $types[$item->type] ?? $item->type }} · {{ $humanSize($item->size) }}
                                </div>
                                <div class="text-muted">{{ $item->created_at?->format('Y/m/d H:i') }}</div>
                            </div>
                            <div class="ap-thumb__actions">
                                <form method="post" action="{{ admin_route('admin.media.destroy', $item) }}"
                                    data-confirm="حذف الصورة نهائياً؟" class="w-100">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm ap-btn--block">
                                        @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 15]) حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($media->hasPages())
            <div class="ap-pagination">{{ $media->links() }}</div>
        @endif
    </div>
@endsection
