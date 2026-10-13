@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = (bool) $editing;
    $arEditing = $isEdit
        ? ($editing->translations->firstWhere('locale', 'ar') ?? new \App\Models\PropertyCategoryTranslation(['locale' => 'ar']))
        : new \App\Models\PropertyCategoryTranslation(['locale' => 'ar']);
    $enEditing = $isEdit
        ? ($editing->translations->firstWhere('locale', 'en') ?? new \App\Models\PropertyCategoryTranslation(['locale' => 'en']))
        : new \App\Models\PropertyCategoryTranslation(['locale' => 'en']);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
@endphp

@section('page_actions')
    @if ($isEdit)
        <a href="{{ admin_route('admin.categories.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إلغاء التعديل</a>
    @endif
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 260px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="اسم التصنيف أو المفتاح…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="1" @selected(($filters['status'] ?? '') === '1')>مفعّل</option>
                        <option value="0" @selected(($filters['status'] ?? '') === '0')>غير مفعّل</option>
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    @include('admin.partials.icon', ['iconName' => 'search', 'size' => 16]) فلترة
                </button>
                <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    {{-- ------------------------------------------ إضافة / تعديل تصنيف --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => $isEdit ? 'edit' : 'plus', 'size' => 18])
                {{ $isEdit ? 'تعديل التصنيف: ' . ($editing->translation('ar')?->name ?? '#' . $editing->id) : 'إضافة تصنيف جديد' }}
            </h2>
            <span class="ap-badge ap-badge--{{ $isEdit ? 'info' : 'gold' }}">{{ $isEdit ? 'تعديل' : 'جديد' }}</span>
        </div>
        <div class="ap-card__body">
            <form method="post"
                action="{{ $isEdit ? admin_route('admin.categories.update', $editing) : admin_route('admin.categories.store') }}">
                @csrf
                @if ($isEdit)
                    @method('put')
                @endif

                <div class="ap-grid ap-grid--4">
                    <div class="ap-field">
                        <label class="ap-label">المفتاح <span>*</span></label>
                        <input type="text" name="key" class="ap-input" required
                            value="{{ $old('key', $editing?->key) }}" placeholder="apartment">
                        <p class="ap-help">حروف إنجليزية صغيرة وأرقام وشرطات فقط.</p>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الأيقونة</label>
                        <input type="text" name="icon" class="ap-input" value="{{ $old('icon', $editing?->icon) }}"
                            placeholder="building">
                    </div>
                    <div class="ap-field">
                        <label class="ap-check" style="margin-top: 26px;">
                            <input type="checkbox" name="status" value="1"
                                @checked((bool) $old('status', $editing?->status ?? true))>
                            <span>مفعّل ويظهر في الفلاتر</span>
                        </label>
                    </div>
                </div>

                <div class="ap-grid ap-grid--2">
                    <div class="ap-field">
                        <label class="ap-label">الاسم بالعربية <span>*</span></label>
                        <input type="text" name="translations[ar][name]" class="ap-input" required
                            value="{{ $old('translations.ar.name', $arEditing->name) }}" placeholder="مثال: شقة">
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الرابط بالعربية (slug)</label>
                        <input type="text" name="translations[ar][slug]" class="ap-input"
                            value="{{ $old('translations.ar.slug', $arEditing->slug) }}" placeholder="apartment">
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الاسم بالإنجليزية</label>
                        <input type="text" name="translations[en][name]" class="ap-input"
                            value="{{ $old('translations.en.name', $enEditing->name) }}" placeholder="Apartment">
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الرابط بالإنجليزية (slug)</label>
                        <input type="text" name="translations[en][slug]" class="ap-input"
                            value="{{ $old('translations.en.slug', $enEditing->slug) }}" placeholder="apartment">
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="ap-btn ap-btn--gold">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                        {{ $isEdit ? 'حفظ التعديلات' : 'إضافة التصنيف' }}
                    </button>
                    @if ($isEdit)
                        <a href="{{ admin_route('admin.categories.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ------------------------------------------------------ الجدول --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'tag', 'size' => 18])
                قائمة التصنيفات
                <span class="ap-badge">{{ number_format($categories->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الاسم (عربي)</th>
                            <th>الاسم (إنجليزي)</th>
                            <th>المفتاح</th>
                            <th>الرابط</th>
                            <th>الأيقونة</th>
                            <th>عدد العقارات</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            @php $propertyCount = (int) ($propertyCounts[$category->id] ?? 0); @endphp
                            <tr>
                                <td><span class="fw-bold">{{ $category->translation('ar')?->name ?? '—' }}</span></td>
                                <td>{{ $category->translation('en')?->name ?? '—' }}</td>
                                <td><code>{{ $category->key }}</code></td>
                                <td class="text-muted" style="font-size: 12px;">
                                    {{ $category->translation('ar')?->slug ?? '—' }}
                                </td>
                                <td>
                                    @if ($category->icon)
                                        <span class="ap-badge ap-badge--secondary">
                                            @include('admin.partials.icon', ['iconName' => $category->icon, 'size' => 14])
                                            {{ $category->icon }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $propertyCount > 0 ? 'gold' : 'secondary' }}">
                                        {{ number_format($propertyCount) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $category->status ? 'success' : 'secondary' }}">
                                        {{ $category->status ? 'مفعّل' : 'غير مفعّل' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.categories.index', ['edit' => $category->id]) }}"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                        </a>
                                        <form method="post" action="{{ admin_route('admin.categories.destroy', $category) }}"
                                            data-confirm="حذف هذا التصنيف نهائياً؟">
                                            @csrf
                                            @method('delete')
                                            <button class="ap-btn ap-btn--danger ap-btn--sm"
                                                @disabled($propertyCount > 0)
                                                title="{{ $propertyCount > 0 ? 'لا يمكن الحذف: التصنيف مستخدم' : 'حذف' }}">
                                                @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 15])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'tag', 'size' => 42])
                                        <p>لا توجد تصنيفات مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($categories->hasPages())
            <div class="ap-pagination">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
