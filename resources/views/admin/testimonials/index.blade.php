@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@php
    $imageUrl = static function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return str_starts_with($path, 'storage/') ? asset($path) : asset('storage/' . ltrim($path, '/'));
    };
@endphp

@section('page_actions')
    @can('content.testimonials')
        <a href="{{ admin_route('admin.testimonials.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة رأي
        </a>
    @endcan
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 260px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="اسم العميل أو نص الرأي…">
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

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'message', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($testimonials->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>الاسم (عربي)</th>
                            <th>المسمى الوظيفي</th>
                            <th>الرأي</th>
                            <th>التقييم</th>
                            <th>الترتيب</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($testimonials as $testimonial)
                            @php
                                $arTranslation = $testimonial->translations->firstWhere('locale', 'ar');
                                $enTranslation = $testimonial->translations->firstWhere('locale', 'en');
                            @endphp
                            <tr>
                                <td>
                                    @if ($imageUrl($testimonial->image_path))
                                        <img src="{{ $imageUrl($testimonial->image_path) }}" alt="" class="ap-table__thumb"
                                            loading="lazy">
                                    @else
                                        <span class="ap-badge ap-badge--secondary">بلا صورة</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $arTranslation?->name ?? '—' }}</span>
                                    @if ($enTranslation?->name)
                                        <div class="text-muted" style="font-size: 11.5px;">{{ $enTranslation->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $arTranslation?->position ?? '—' }}</td>
                                <td class="text-muted" style="font-size: 12px; max-width: 320px;">
                                    {{ \Illuminate\Support\Str::limit((string) $arTranslation?->content, 90) }}
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--gold">
                                        @include('admin.partials.icon', ['iconName' => 'star', 'size' => 13])
                                        {{ (int) $testimonial->rating }} / 5
                                    </span>
                                </td>
                                <td>{{ (int) $testimonial->sort_order }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $testimonial->status ? 'success' : 'secondary' }}">
                                        {{ $testimonial->status ? 'مفعّل' : 'غير مفعّل' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.testimonials.edit', $testimonial) }}"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                        </a>
                                        <form method="post"
                                            action="{{ admin_route('admin.testimonials.destroy', $testimonial) }}"
                                            data-confirm="حذف رأي هذا العميل نهائياً؟">
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
                                        @include('admin.partials.icon', ['iconName' => 'message', 'size' => 42])
                                        <p>لا توجد آراء مطابقة للفلترة الحالية.</p>
                                        <a href="{{ admin_route('admin.testimonials.create') }}"
                                            class="ap-btn ap-btn--gold ap-btn--sm">إضافة أول رأي</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($testimonials->hasPages())
            <div class="ap-pagination">{{ $testimonials->links() }}</div>
        @endif
    </div>
@endsection
