@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'متابعة مواعيد المعاينة والمكالمات وتحديث حالتها وموعدها')

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ $statusLabels[$status] ?? $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">يوم محدد</label>
                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="ap-input ap-input--sm">
                </div>
                <div class="ap-field">
                    <label class="ap-label">من تاريخ</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="ap-input ap-input--sm">
                </div>
                <div class="ap-field">
                    <label class="ap-label">إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="ap-input ap-input--sm">
                </div>
                <button class="ap-btn ap-btn--sm">
                    @include('admin.partials.icon', ['iconName' => 'search', 'size' => 16]) فلترة
                </button>
                <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي النتائج</div>
                <div class="ap-stat__value">{{ number_format($appointments->total()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'alert', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مطلوبة</div>
                <div class="ap-stat__value">{{ number_format($appointments->getCollection()->where('status', 'requested')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مؤكدة</div>
                <div class="ap-stat__value">{{ number_format($appointments->getCollection()->where('status', 'confirmed')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مكتملة</div>
                <div class="ap-stat__value">{{ number_format($appointments->getCollection()->where('status', 'completed')->count()) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($appointments->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>العقار</th>
                            <th>الوكيل</th>
                            <th>الموعد</th>
                            <th>الحالة</th>
                            <th>ملاحظات</th>
                            <th>تحديث / حذف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>
                                    @if ($appointment->lead_id)
                                        <span class="fw-bold">{{ $leadNames[$appointment->lead_id] ?? '#' . $appointment->lead_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    {{ $appointment->property_id ? ($propertyNames[$appointment->property_id] ?? '#' . $appointment->property_id) : '—' }}
                                </td>
                                <td>{{ $appointment->agent_id ? ($agentNames[$appointment->agent_id] ?? '#' . $appointment->agent_id) : '— غير مُسند —' }}</td>
                                <td>
                                    <div class="fw-bold">{{ $appointment->scheduled_at?->format('Y/m/d') ?? '—' }}</div>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ $appointment->scheduled_at?->format('H:i') ?? '' }}
                                        {{ $appointment->timezone ? '· ' . $appointment->timezone : '' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($appointment->status) }}">
                                        {{ $statusLabels[$appointment->status] ?? status_label($appointment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted" style="font-size: 12px;">
                                        {{ $appointment->notes ? \Illuminate\Support\Str::limit($appointment->notes, 40) : '—' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="post" action="{{ admin_route('admin.appointments.update', $appointment) }}"
                                        class="d-flex gap-2 flex-wrap align-items-center">
                                        @csrf
                                        @method('put')
                                        <select name="status" class="ap-select ap-select--sm" style="width: auto;" required>
                                            @foreach ($statuses as $status)
                                                <option value="{{ $status }}" @selected($appointment->status === $status)>
                                                    {{ $statusLabels[$status] ?? $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="datetime-local" name="scheduled_at" class="ap-input ap-input--sm"
                                            style="width: auto;"
                                            value="{{ $appointment->scheduled_at?->format('Y-m-d\TH:i') }}">
                                        <button type="submit" class="ap-btn ap-btn--sm" title="حفظ">
                                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 15]) حفظ
                                        </button>
                                    </form>
                                    <form method="post" action="{{ admin_route('admin.appointments.destroy', $appointment) }}"
                                        data-confirm="حذف هذا الموعد نهائياً؟" style="margin-top: 6px;">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm">
                                            @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 15]) حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 42])
                                        <p>لا توجد مواعيد مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($appointments->hasPages())
            <div class="ap-pagination">{{ $appointments->links() }}</div>
        @endif
    </div>
@endsection
