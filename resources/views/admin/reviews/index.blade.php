@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'مراجعة آراء العملاء واعتمادها أو رفضها قبل نشرها')

@section('content')
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي النتائج</div>
                <div class="ap-stat__value">{{ number_format($reviews->total()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'alert', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">بانتظار المراجعة</div>
                <div class="ap-stat__value">{{ number_format($reviews->getCollection()->where('status', 'pending')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">معتمدة</div>
                <div class="ap-stat__value">{{ number_format($reviews->getCollection()->where('status', 'approved')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--danger">@include('admin.partials.icon', ['iconName' => 'x', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مرفوضة</div>
                <div class="ap-stat__value">{{ number_format($reviews->getCollection()->where('status', 'rejected')->count()) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">التقييم</label>
                    <select name="rating" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @for ($rating = 5; $rating >= 1; $rating--)
                            <option value="{{ $rating }}" @selected((string) ($filters['rating'] ?? '') === (string) $rating)>
                                {{ $rating }} نجوم
                            </option>
                        @endfor
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
                @include('admin.partials.icon', ['iconName' => 'star', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($reviews->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>العنصر المقيّم</th>
                            <th>صاحب التقييم</th>
                            <th>التقييم</th>
                            <th>التعليق</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviews as $review)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $targetLabels[$review->id] ?? '—' }}</span>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ class_basename($review->reviewable_type) }} #{{ $review->reviewable_id }}
                                    </div>
                                </td>
                                <td>{{ $userNames[$review->user_id] ?? '#' . $review->user_id }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span style="color: {{ $star <= (int) $review->rating ? 'var(--ap-accent)' : '#d5dbe0' }};">
                                                @include('admin.partials.icon', ['iconName' => 'star', 'size' => 15])
                                            </span>
                                        @endfor
                                        <span class="ap-badge ap-badge--gold">{{ (int) $review->rating }}/5</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($review->title)
                                        <div class="fw-bold">{{ \Illuminate\Support\Str::limit($review->title, 40) }}</div>
                                    @endif
                                    <span class="text-muted" style="font-size: 12px;">
                                        {{ \Illuminate\Support\Str::limit((string) $review->comment, 60) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($review->status) }}">
                                        {{ $statusLabels[$review->status] ?? status_label($review->status) }}
                                    </span>
                                    @if ($review->approved_at)
                                        <div class="text-muted" style="font-size: 11px;">{{ $review->approved_at->format('Y/m/d') }}</div>
                                    @endif
                                </td>
                                <td>{{ $review->created_at?->format('Y/m/d') ?? '—' }}</td>
                                <td>
                                    <div class="ap-table__actions">
                                        @if ($review->status !== 'approved')
                                            <form method="post" action="{{ admin_route('admin.reviews.approve', $review) }}"
                                                data-confirm="اعتماد هذا التقييم ونشره؟">
                                                @csrf
                                                <button type="submit" class="ap-btn ap-btn--success ap-btn--sm" title="اعتماد">
                                                    @include('admin.partials.icon', ['iconName' => 'check', 'size' => 15]) اعتماد
                                                </button>
                                            </form>
                                        @endif
                                        @if ($review->status !== 'rejected')
                                            <form method="post" action="{{ admin_route('admin.reviews.reject', $review) }}"
                                                data-confirm="رفض هذا التقييم؟">
                                                @csrf
                                                <button type="submit" class="ap-btn ap-btn--gold ap-btn--sm" title="رفض">
                                                    @include('admin.partials.icon', ['iconName' => 'x', 'size' => 15]) رفض
                                                </button>
                                            </form>
                                        @endif
                                        <form method="post" action="{{ admin_route('admin.reviews.destroy', $review) }}"
                                            data-confirm="حذف هذا التقييم نهائياً؟">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف">
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
                                        @include('admin.partials.icon', ['iconName' => 'star', 'size' => 42])
                                        <p>لا توجد تقييمات مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($reviews->hasPages())
            <div class="ap-pagination">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
