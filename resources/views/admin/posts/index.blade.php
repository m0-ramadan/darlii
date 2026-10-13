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
    @can('content.manage')
        <a href="{{ admin_route('admin.posts.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة مقال
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
                        placeholder="عنوان المقال أو الرابط أو اسم الكاتب…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ status_label($status) }}
                            </option>
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
                @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($posts->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>العنوان (عربي)</th>
                            <th>الرابط</th>
                            <th>الكاتب</th>
                            <th>الحالة</th>
                            <th>تاريخ النشر</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($posts as $post)
                            @php
                                $arTranslation = $post->translations->firstWhere('locale', 'ar');
                                $enTranslation = $post->translations->firstWhere('locale', 'en');
                            @endphp
                            <tr>
                                <td>
                                    @if ($imageUrl($post->featured_image))
                                        <img src="{{ $imageUrl($post->featured_image) }}" alt="" class="ap-table__thumb"
                                            loading="lazy">
                                    @else
                                        <span class="ap-badge ap-badge--secondary">بلا صورة</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">{{ $arTranslation?->title ?? '— بلا عنوان —' }}</span>
                                    @if ($enTranslation?->title)
                                        <div class="text-muted" style="font-size: 11.5px;">{{ $enTranslation->title }}</div>
                                    @endif
                                </td>
                                <td class="text-muted" style="font-size: 12px;">{{ $arTranslation?->slug ?: '—' }}</td>
                                <td>{{ $post->author?->name ?? '—' }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($post->status) }}">
                                        {{ status_label($post->status) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size: 12px;">
                                    {{ $post->published_at ? $post->published_at->format('Y-m-d') : '—' }}
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.posts.edit', $post) }}"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                        </a>
                                        <form method="post" action="{{ admin_route('admin.posts.destroy', $post) }}"
                                            data-confirm="حذف هذا المقال؟">
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
                                <td colspan="7">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 42])
                                        <p>لا توجد مقالات مطابقة للفلترة الحالية.</p>
                                        <a href="{{ admin_route('admin.posts.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
                                            إضافة أول مقال
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($posts->hasPages())
            <div class="ap-pagination">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
