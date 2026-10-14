@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('page_actions')
    @can('content.manage')
        <a href="{{ admin_route('admin.pages.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة صفحة
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
                        placeholder="عنوان الصفحة أو المفتاح أو الرابط…">
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
                @include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($pages->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>العنوان (عربي)</th>
                            <th>المفتاح</th>
                            <th>الرابط</th>
                            <th>العنوان (إنجليزي)</th>
                            <th>الحالة</th>
                            <th>تاريخ النشر</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pages as $page)
                            @php
                                $arTranslation = $page->translations->firstWhere('locale', 'ar');
                                $enTranslation = $page->translations->firstWhere('locale', 'en');
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $arTranslation?->title ?? '— بلا عنوان —' }}</span>
                                </td>
                                <td><code>{{ $page->key ?: '—' }}</code></td>
                                <td class="text-muted" style="font-size: 12px;">{{ $arTranslation?->slug ?: '—' }}</td>
                                <td>{{ $enTranslation?->title ?? '—' }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($page->status) }}">
                                        {{ status_label($page->status) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size: 12px;">
                                    {{ $page->published_at ? \Illuminate\Support\Carbon::parse($page->published_at)->format('Y-m-d') : '—' }}
                                </td>
                                <td>
                                    <div class="ap-table__actions">
                                        @php
                                            $publicUrl = match($page->key) {
                                                'privacy-policy' => route('privacy-policy.index', ['locale' => 'ar']),
                                                'terms-and-conditions' => route('terms.index', ['locale' => 'ar']),
                                                'faqs' => route('faqs.index', ['locale' => 'ar']),
                                                'contact-us' => route('contact-us.index', ['locale' => 'ar']),
                                                default => url('/' . ($arTranslation?->slug ?: $page->key)),
                                            };
                                        @endphp
                                        <a href="{{ $publicUrl }}" target="_blank"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض في الموقع">
                                            @include('admin.partials.icon', ['iconName' => 'external-link', 'size' => 15])
                                        </a>
                                        <a href="{{ admin_route('admin.pages.edit', $page) }}"
                                            class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                        </a>
                                        <form method="post" action="{{ admin_route('admin.pages.destroy', $page) }}"
                                            data-confirm="حذف هذه الصفحة؟ يمكن استرجاعها من قاعدة البيانات فقط.">
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
                                        @include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 42])
                                        <p>لا توجد صفحات مطابقة للفلترة الحالية.</p>
                                        <a href="{{ admin_route('admin.pages.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
                                            إضافة أول صفحة
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($pages->hasPages())
            <div class="ap-pagination">{{ $pages->links() }}</div>
        @endif
    </div>
@endsection
