@extends('layouts.admin')

@section('title', $title ?? 'سجل النشاط')
@section('page_title', $title ?? 'سجل النشاط')
@section('page_subtitle', 'كل الأحداث والعمليات المسجّلة في لوحة التحكم')

@section('page_actions')
    <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 16]) تحديث السجل
    </a>
@endsection

@section('content')
    {{-- ------------------------------------------------------ شريط الفلترة --}}
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" action="{{ url()->current() }}" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 220px;">
                    <label class="ap-label">الحدث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="مثال: property.updated أو login…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">المستخدم</label>
                    <select name="user_id" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($users as $actor)
                            <option value="{{ $actor->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $actor->id)>
                                {{ $actor->name }}
                            </option>
                        @endforeach
                    </select>
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

    {{-- ------------------------------------------------------------- الجدول --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18]) سجل النشاط
                <span class="ap-badge">{{ number_format($logs->total()) }}</span>
            </h2>
            <span class="ap-card__subtitle">مرتّب من الأحدث إلى الأقدم</span>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>المستخدم</th>
                            <th>الحدث</th>
                            <th>العنصر</th>
                            <th>IP</th>
                            <th>القيم الجديدة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $eventColor = match (true) {
                                    str_contains((string) $log->event, 'delete') => 'danger',
                                    str_contains((string) $log->event, 'create') => 'success',
                                    str_contains((string) $log->event, 'update') => 'info',
                                    str_contains((string) $log->event, 'login') => 'gold',
                                    default => 'secondary',
                                };
                                $preview = \Illuminate\Support\Str::limit(
                                    (string) json_encode($log->new_values, JSON_UNESCAPED_UNICODE),
                                    120,
                                );
                            @endphp
                            <tr>
                                <td style="white-space: nowrap;">
                                    {{ $log->created_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td>
                                    {{ ($userNames ?? [])[$log->user_id] ?? $log->user?->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ $eventColor }}" dir="ltr">{{ $log->event }}</span>
                                </td>
                                <td>
                                    @if ($log->auditable_type)
                                        <span class="fw-bold">{{ class_basename($log->auditable_type) }}</span>
                                        <span class="text-muted">#{{ $log->auditable_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td dir="ltr">{{ $log->ip ?? '—' }}</td>
                                <td style="max-width: 340px;">
                                    @if (! empty($log->new_values))
                                        <div class="text-muted" dir="ltr" style="font-size: 11.5px; word-break: break-all;">
                                            {{ $preview }}
                                        </div>
                                        <details>
                                            <summary class="text-muted" style="font-size: 11.5px; cursor: pointer;">عرض التفاصيل</summary>
                                            <pre dir="ltr" style="font-size: 11px; white-space: pre-wrap; word-break: break-all; margin: 6px 0 0;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 42])
                                        <p>لا توجد أحداث مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($logs->hasPages())
            <div class="ap-pagination">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
