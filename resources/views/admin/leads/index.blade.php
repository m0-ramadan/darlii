@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'كل الاستفسارات الواردة مع الفلترة والإسناد والمتابعة')

@section('content')
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي النتائج</div>
                <div class="ap-stat__value">{{ number_format($leads->total()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--warn">@include('admin.partials.icon', ['iconName' => 'alert', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">جديدة في هذه الصفحة</div>
                <div class="ap-stat__value">{{ number_format($leads->getCollection()->where('status', 'new')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مُسندة في هذه الصفحة</div>
                <div class="ap-stat__value">{{ number_format($leads->getCollection()->whereNotNull('assigned_to')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">تم البيع في هذه الصفحة</div>
                <div class="ap-stat__value">{{ number_format($leads->getCollection()->where('status', 'won')->count()) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 220px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="الاسم أو البريد أو الهاتف أو الكود…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                {{ $statusLabels[$status->value] ?? $status->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">المسؤول</label>
                    <select name="assigned_to" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}" @selected((string) ($filters['assigned_to'] ?? '') === (string) $assignee->id)>
                                {{ $assignee->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">المصدر</label>
                    <select name="source" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($sources as $source)
                            <option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ $source }}</option>
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

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'message', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($leads->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>العميل</th>
                            <th>العقار / المشروع</th>
                            <th>المصدر</th>
                            <th>الحالة</th>
                            <th>المسؤول</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td><code>{{ $lead->reference_code }}</code></td>
                                <td>
                                    <a href="{{ admin_route('admin.leads.show', $lead) }}" class="fw-bold">{{ $lead->name }}</a>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ $lead->phone ?: '—' }} · {{ $lead->email ?: '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if ($lead->property)
                                        {{ \Illuminate\Support\Str::limit($lead->property->displayName('ar'), 34) }}
                                    @elseif ($lead->project)
                                        {{ \Illuminate\Support\Str::limit($lead->project->translation('ar')?->name ?? $lead->project->reference_code, 34) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="ap-badge ap-badge--secondary">{{ $lead->source ?: '—' }}</span></td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($lead->status) }}">
                                        {{ $statusLabels[$lead->status] ?? status_label($lead->status) }}
                                    </span>
                                </td>
                                <td>{{ $userNames[$lead->assigned_to] ?? '— غير مُسند —' }}</td>
                                <td>{{ $lead->created_at?->format('Y/m/d H:i') ?? '—' }}</td>
                                <td>
                                    <div class="ap-table__actions">
                                        <a href="{{ admin_route('admin.leads.show', $lead) }}" class="ap-btn ap-btn--ghost ap-btn--sm"
                                            title="عرض">
                                            @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 15])
                                        </a>
                                        @if (auth()->user()?->hasPermission('lead.manage'))
                                            <form method="post" action="{{ admin_route('admin.leads.destroy', $lead) }}"
                                                data-confirm="حذف العميل {{ $lead->name }} نهائياً؟">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف">
                                                    @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 15])
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="ap-empty">
                                        @include('admin.partials.icon', ['iconName' => 'message', 'size' => 42])
                                        <p>لا يوجد عملاء مطابقون للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($leads->hasPages())
            <div class="ap-pagination">{{ $leads->links() }}</div>
        @endif
    </div>
@endsection
