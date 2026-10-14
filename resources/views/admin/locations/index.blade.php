@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = (bool) $editing;
    $arEditing = $isEdit
        ? ($editing->translations->firstWhere('locale', 'ar') ?? new \App\Models\LocationTranslation(['locale' => 'ar']))
        : new \App\Models\LocationTranslation(['locale' => 'ar']);
    $enEditing = $isEdit
        ? ($editing->translations->firstWhere('locale', 'en') ?? new \App\Models\LocationTranslation(['locale' => 'en']))
        : new \App\Models\LocationTranslation(['locale' => 'en']);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
@endphp

@section('page_actions')
    @if ($isEdit)
        <a href="{{ admin_route('admin.locations.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إلغاء التعديل</a>
    @endif
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 260px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="اسم الموقع أو الرابط…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">النوع</label>
                    <select name="type" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($types as $typeKey => $typeLabel)
                            <option value="{{ $typeKey }}" @selected(($filters['type'] ?? '') === $typeKey)>{{ $typeLabel }}</option>
                        @endforeach
                    </select>
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

    {{-- ------------------------------------------- إضافة / تعديل موقع --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => $isEdit ? 'edit' : 'plus', 'size' => 18])
                {{ $isEdit ? 'تعديل الموقع: ' . ($editing->translation('ar')?->name ?? '#' . $editing->id) : 'إضافة موقع جديد' }}
            </h2>
            <span class="ap-badge ap-badge--{{ $isEdit ? 'info' : 'gold' }}">{{ $isEdit ? 'تعديل' : 'جديد' }}</span>
        </div>
        <div class="ap-card__body">
            <form method="post"
                action="{{ $isEdit ? admin_route('admin.locations.update', $editing) : admin_route('admin.locations.store') }}">
                @csrf
                @if ($isEdit)
                    @method('put')
                @endif

                <div class="ap-grid ap-grid--4">
                    <div class="ap-field">
                        <label class="ap-label">النوع <span>*</span></label>
                        <select name="type" class="ap-select" required>
                            @foreach ($types as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}" @selected($old('type', $editing?->type ?? 'governorate') === $typeKey)>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الموقع الأب</label>
                        <select name="parent_id" class="ap-select">
                            <option value="">— بلا أب —</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" @selected((string) $old('parent_id', $editing?->parent_id) === (string) $parent->id)>
                                    {{ $parent->translation('ar')?->name ?? '#' . $parent->id }}
                                    ({{ $types[$parent->type] ?? $parent->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">كود الدولة</label>
                        <input type="text" name="country_code" class="ap-input" maxlength="2"
                            value="{{ $old('country_code', $editing?->country_code) }}" placeholder="EG">
                    </div>
                    <div class="ap-field">
                        <label class="ap-check" style="margin-top: 26px;">
                            <input type="checkbox" name="status" value="1"
                                @checked((bool) $old('status', $editing?->status ?? true))>
                            <span>مفعّل ويظهر في الموقع</span>
                        </label>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">خط العرض</label>
                        <input type="number" step="0.0000001" name="latitude" class="ap-input"
                            value="{{ $old('latitude', $editing?->latitude) }}" placeholder="30.0444200">
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">خط الطول</label>
                        <input type="number" step="0.0000001" name="longitude" class="ap-input"
                            value="{{ $old('longitude', $editing?->longitude) }}" placeholder="31.2357120">
                    </div>
                </div>

                <div class="ap-grid ap-grid--2">
                    <div class="ap-field">
                        <label class="ap-label">الاسم بالعربية <span>*</span></label>
                        <input type="text" name="translations[ar][name]" class="ap-input" required
                            value="{{ $old('translations.ar.name', $arEditing->name) }}" placeholder="مثال: محافظة القاهرة">
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الرابط بالعربية (slug)</label>
                        <input type="text" name="translations[ar][slug]" class="ap-input"
                            value="{{ $old('translations.ar.slug', $arEditing->slug) }}" placeholder="cairo">
                    </div>
                    @if (config('locales.admin_english_enabled'))
                    <div class="ap-field">
                        <label class="ap-label">الاسم بالإنجليزية</label>
                        <input type="text" name="translations[en][name]" class="ap-input"
                            value="{{ $old('translations.en.name', $enEditing->name) }}" placeholder="Cairo">
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الرابط بالإنجليزية (slug)</label>
                        <input type="text" name="translations[en][slug]" class="ap-input"
                            value="{{ $old('translations.en.slug', $enEditing->slug) }}" placeholder="cairo">
                    </div>
                    @endif
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="ap-btn ap-btn--gold">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                        {{ $isEdit ? 'حفظ التعديلات' : 'إضافة الموقع' }}
                    </button>
                    @if ($isEdit)
                        <a href="{{ admin_route('admin.locations.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ------------------------------------------------------ الجدول --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'map', 'size' => 18])
                قائمة المواقع
                <span class="ap-badge">{{ number_format($locations->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الاسم (عربي)</th>
                            <th>الاسم (إنجليزي)</th>
                            <th>النوع</th>
                            <th>الموقع الأب</th>
                            <th>العقارات</th>
                            <th>المشروعات</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $location)
                            @php
                                $propertyCount = (int) ($propertyCounts[$location->id] ?? 0);
                                $projectCount = (int) ($projectCounts[$location->id] ?? 0);
                                $inUse = $propertyCount > 0 || $projectCount > 0;
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $location->translation('ar')?->name ?? '—' }}</span>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ $location->translation('ar')?->slug ?? '—' }}
                                    </div>
                                </td>
                                <td>{{ $location->translation('en')?->name ?? '—' }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--info">{{ $types[$location->type] ?? $location->type }}</span>
                                </td>
                                <td>{{ $location->parent?->translation('ar')?->name ?? '—' }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $propertyCount > 0 ? 'gold' : 'secondary' }}">
                                        {{ number_format($propertyCount) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $projectCount > 0 ? 'gold' : 'secondary' }}">
                                        {{ number_format($projectCount) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $location->status ? 'success' : 'secondary' }}">
                                        {{ $location->status ? 'مفعّل' : 'غير مفعّل' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.locations.index', ['edit' => $location->id]) }}"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                        </a>
                                        <form method="post" action="{{ admin_route('admin.locations.destroy', $location) }}"
                                            data-confirm="حذف هذا الموقع نهائياً؟">
                                            @csrf
                                            @method('delete')
                                            <button class="ap-btn ap-btn--danger ap-btn--sm"
                                                @disabled($inUse) title="{{ $inUse ? 'لا يمكن الحذف: الموقع مستخدم' : 'حذف' }}">
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
                                        @include('admin.partials.icon', ['iconName' => 'map', 'size' => 42])
                                        <p>لا توجد مواقع مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($locations->hasPages())
            <div class="ap-pagination">{{ $locations->links() }}</div>
        @endif
    </div>
@endsection
