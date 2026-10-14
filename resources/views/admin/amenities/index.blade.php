@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $isEdit = (bool) $editing;
    $arEditing = $isEdit
        ? ($editing->translations->firstWhere('locale', 'ar') ?? new \App\Models\AmenityTranslation(['locale' => 'ar']))
        : new \App\Models\AmenityTranslation(['locale' => 'ar']);
    $enEditing = $isEdit
        ? ($editing->translations->firstWhere('locale', 'en') ?? new \App\Models\AmenityTranslation(['locale' => 'en']))
        : new \App\Models\AmenityTranslation(['locale' => 'en']);
    $old = fn (string $key, $fallback = null) => old($key, $fallback);
@endphp

@section('page_actions')
    @if ($isEdit)
        <a href="{{ admin_route('admin.amenities.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إلغاء التعديل</a>
    @endif
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 260px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="اسم المرفق أو المفتاح…">
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

    {{-- ------------------------------------------ إضافة / تعديل مرفق --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => $isEdit ? 'edit' : 'plus', 'size' => 18])
                {{ $isEdit ? 'تعديل المرفق: ' . ($editing->translation('ar')?->name ?? '#' . $editing->id) : 'إضافة مرفق جديد' }}
            </h2>
            <span class="ap-badge ap-badge--{{ $isEdit ? 'info' : 'gold' }}">{{ $isEdit ? 'تعديل' : 'جديد' }}</span>
        </div>
        <div class="ap-card__body">
            <form method="post"
                action="{{ $isEdit ? admin_route('admin.amenities.update', $editing) : admin_route('admin.amenities.store') }}">
                @csrf
                @if ($isEdit)
                    @method('put')
                @endif

                <div class="ap-grid ap-grid--4">
                    <div class="ap-field">
                        <label class="ap-label">المفتاح <span>*</span></label>
                        <input type="text" name="key" class="ap-input" required
                            value="{{ $old('key', $editing?->key) }}" placeholder="swimming-pool">
                        <p class="ap-help">حروف إنجليزية صغيرة وأرقام وشرطات فقط.</p>
                    </div>
                    <div class="ap-field">
                        <label class="ap-label">الأيقونة</label>
                        <input type="text" name="icon" class="ap-input" value="{{ $old('icon', $editing?->icon) }}"
                            placeholder="check">
                    </div>
                    <div class="ap-field">
                        <label class="ap-check" style="margin-top: 26px;">
                            <input type="checkbox" name="status" value="1"
                                @checked((bool) $old('status', $editing?->status ?? true))>
                            <span>مفعّل ويظهر في الموقع</span>
                        </label>
                    </div>
                </div>

                <div class="ap-grid ap-grid--2">
                    <div class="ap-field">
                        <label class="ap-label">الاسم بالعربية <span>*</span></label>
                        <input type="text" name="translations[ar][name]" class="ap-input" required
                            value="{{ $old('translations.ar.name', $arEditing->name) }}" placeholder="مثال: مسبح">
                    </div>
                    @if (config('locales.admin_english_enabled'))
                    <div class="ap-field">
                        <label class="ap-label">الاسم بالإنجليزية</label>
                        <input type="text" name="translations[en][name]" class="ap-input"
                            value="{{ $old('translations.en.name', $enEditing->name) }}" placeholder="Swimming pool">
                    </div>
                    @endif
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button class="ap-btn ap-btn--gold">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                        {{ $isEdit ? 'حفظ التعديلات' : 'إضافة المرفق' }}
                    </button>
                    @if ($isEdit)
                        <a href="{{ admin_route('admin.amenities.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ------------------------------------------------------ الجدول --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'check', 'size' => 18])
                قائمة المرافق
                <span class="ap-badge">{{ number_format($amenities->total()) }}</span>
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
                            <th>الأيقونة</th>
                            <th>يُستخدم في عقارات</th>
                            <th>يُستخدم في مشروعات</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($amenities as $amenity)
                            @php
                                $propertyCount = (int) ($propertyCounts[$amenity->id] ?? 0);
                                $projectCount = (int) ($projectCounts[$amenity->id] ?? 0);
                            @endphp
                            <tr>
                                <td><span class="fw-bold">{{ $amenity->translation('ar')?->name ?? '—' }}</span></td>
                                <td>{{ $amenity->translation('en')?->name ?? '—' }}</td>
                                <td><code>{{ $amenity->key }}</code></td>
                                <td>
                                    @if ($amenity->icon)
                                        <span class="ap-badge ap-badge--secondary">
                                            @include('admin.partials.icon', ['iconName' => $amenity->icon, 'size' => 14])
                                            {{ $amenity->icon }}
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
                                    <span class="ap-badge ap-badge--{{ $projectCount > 0 ? 'gold' : 'secondary' }}">
                                        {{ number_format($projectCount) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $amenity->status ? 'success' : 'secondary' }}">
                                        {{ $amenity->status ? 'مفعّل' : 'غير مفعّل' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.amenities.index', ['edit' => $amenity->id]) }}"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                        </a>
                                        <form method="post" action="{{ admin_route('admin.amenities.destroy', $amenity) }}"
                                            data-confirm="حذف هذا المرفق وفصله عن العقارات والمشروعات؟">
                                            @csrf
                                            @method('delete')
                                            <button class="ap-btn ap-btn--danger ap-btn--sm" title="حذف">
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
                                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 42])
                                        <p>لا توجد مرافق مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($amenities->hasPages())
            <div class="ap-pagination">{{ $amenities->links() }}</div>
        @endif
    </div>
@endsection
