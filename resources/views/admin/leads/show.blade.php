@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'بيانات العميل، سجل الأنشطة، الإسناد وتحديث الحالة')

@section('page_actions')
    <a href="{{ admin_route('admin.leads.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'x', 'size' => 16]) رجوع للقائمة
    </a>
@endsection

@section('content')
    <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'message', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">الحالة الحالية</div>
                <div class="ap-stat__value" style="font-size: 17px;">
                    {{ $statusLabels[$lead->status] ?? status_label($lead->status) }}
                </div>
                <div class="ap-stat__hint">{{ $lead->reference_code }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">المسؤول عن المتابعة</div>
                <div class="ap-stat__value" style="font-size: 17px;">
                    {{ $lead->assigned_to ? ($userNames[$lead->assigned_to] ?? '#' . $lead->assigned_to) : 'غير مُسند' }}
                </div>
                <div class="ap-stat__hint">{{ $lead->source ?: 'مصدر غير محدد' }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'activity', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">عدد الأنشطة</div>
                <div class="ap-stat__value">{{ number_format($activities->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">تاريخ الاستفسار</div>
                <div class="ap-stat__value" style="font-size: 16px;">{{ $lead->created_at?->format('Y/m/d') ?? '—' }}</div>
                <div class="ap-stat__hint">{{ $lead->created_at?->format('H:i') }}</div>
            </div>
        </div>
    </div>

    <div class="ap-grid ap-grid--3">
        <div style="grid-column: span 2;">
            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">
                        @include('admin.partials.icon', ['iconName' => 'users', 'size' => 18])
                        بيانات العميل
                    </h2>
                    <span class="ap-badge ap-badge--{{ status_color($lead->status) }}">
                        {{ $statusLabels[$lead->status] ?? status_label($lead->status) }}
                    </span>
                </div>
                <div class="ap-card__body">
                    <div class="ap-grid ap-grid--2">
                        <div class="ap-field">
                            <label class="ap-label">الاسم الكامل</label>
                            <div class="fw-bold">{{ $lead->name }}</div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">كود العميل</label>
                            <div><code>{{ $lead->reference_code }}</code></div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">البريد الإلكتروني</label>
                            <div>
                                @if ($lead->email)
                                    <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">رقم الهاتف</label>
                            <div>
                                @if ($lead->phone)
                                    <a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a>
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">المصدر</label>
                            <div><span class="ap-badge ap-badge--secondary">{{ $lead->source ?: '—' }}</span></div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">العقار محل الاهتمام</label>
                            <div>
                                @if ($lead->property)
                                    <a href="{{ admin_route('admin.properties.show', $lead->property) }}" class="fw-bold">
                                        {{ $lead->property->displayName('ar') }}
                                    </a>
                                    <div class="text-muted" style="font-size: 11.5px;">{{ $lead->property->reference_code }}</div>
                                @elseif ($lead->project)
                                    {{ $lead->project->translation('ar')?->name ?? $lead->project->reference_code }}
                                @else
                                    —
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="ap-field" style="margin-bottom: 0;">
                        <label class="ap-label">نص الرسالة / الملاحظات</label>
                        <div class="ap-alert ap-alert--info" style="margin-bottom: 0;">
                            <span>{{ $lead->message ?: 'لا توجد رسالة مسجّلة.' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ap-card">
                <div class="ap-card__head">
                    <h2 class="ap-card__title">
                        @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 18])
                        سجل الأنشطة
                    </h2>
                    <span class="ap-badge">{{ number_format($activities->count()) }}</span>
                </div>
                <div class="ap-card__body ap-card__body--tight">
                    @forelse ($activities as $activity)
                        <div class="d-flex gap-2" style="padding: 14px 18px; border-bottom: 1px solid #f1f3f6;">
                            <div class="ap-stat__icon" style="width: 34px; height: 34px; border-radius: 10px;">
                                @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 16])
                            </div>
                            <div style="flex: 1;">
                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <span class="ap-badge ap-badge--info">{{ $activityTypes[$activity->type] ?? $activity->type }}</span>
                                    <span class="fw-bold">{{ $userNames[$activity->user_id] ?? 'النظام' }}</span>
                                    <span class="text-muted" style="font-size: 11.5px;">
                                        {{ $activity->created_at?->format('Y/m/d H:i') ?? '' }}
                                    </span>
                                </div>
                                <p class="mb-0 mt-1">{{ $activity->description ?: '—' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="ap-empty">
                            @include('admin.partials.icon', ['iconName' => 'activity', 'size' => 42])
                            <p>لا توجد أنشطة مسجّلة على هذا العميل بعد.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div>
            @if (auth()->user()?->hasPermission('lead.assign'))
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'users', 'size' => 18])
                            إسناد العميل
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.leads.assign', $lead) }}">
                            @csrf
                            <div class="ap-field">
                                <label class="ap-label" for="assign_assigned_to">المسؤول <span>*</span></label>
                                <select id="assign_assigned_to" name="assigned_to" class="ap-select" required>
                                    <option value="">— اختر المسؤول —</option>
                                    @foreach ($assignableUsers as $assignee)
                                        <option value="{{ $assignee->id }}" @selected((int) $lead->assigned_to === (int) $assignee->id)>
                                            {{ $assignee->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="ap-help">الإسناد متاح فقط للمستخدمين ضمن نطاقك.</div>
                            </div>
                            <button type="submit" class="ap-btn ap-btn--block">
                                @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16]) إسناد
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if (auth()->user()?->hasPermission('lead.manage'))
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 18])
                            تحديث البيانات
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.leads.update', $lead) }}">
                            @csrf
                            @method('put')
                            <div class="ap-field">
                                <label class="ap-label" for="update_status">الحالة <span>*</span></label>
                                <select id="update_status" name="status" class="ap-select" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected(old('status', $lead->status) === $status->value)>
                                            {{ $statusLabels[$status->value] ?? $status->value }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (auth()->user()?->hasPermission('lead.assign'))
                                <div class="ap-field">
                                    <label class="ap-label" for="update_assigned_to">المسؤول</label>
                                    <select id="update_assigned_to" name="assigned_to" class="ap-select">
                                        <option value="">— بدون تغيير —</option>
                                        @foreach ($assignableUsers as $assignee)
                                            <option value="{{ $assignee->id }}" @selected((int) old('assigned_to', $lead->assigned_to) === (int) $assignee->id)>
                                                {{ $assignee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="ap-field">
                                <label class="ap-label" for="update_message">ملاحظات</label>
                                <textarea id="update_message" name="message" class="ap-textarea">{{ old('message', $lead->message) }}</textarea>
                            </div>
                            <button type="submit" class="ap-btn ap-btn--gold ap-btn--block">
                                @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16]) حفظ التعديلات
                            </button>
                        </form>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">
                            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 18])
                            تسجيل نشاط
                        </h2>
                    </div>
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.leads.activity', $lead) }}">
                            @csrf
                            <div class="ap-field">
                                <label class="ap-label" for="activity_type">نوع النشاط</label>
                                <select id="activity_type" name="type" class="ap-select">
                                    @foreach ($activityTypes as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', 'note') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label" for="activity_description">الوصف <span>*</span></label>
                                <textarea id="activity_description" name="description" class="ap-textarea" required
                                    placeholder="مثال: تم التواصل هاتفياً وتحديد موعد معاينة…">{{ old('description') }}</textarea>
                            </div>
                            <button type="submit" class="ap-btn ap-btn--block">
                                @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة النشاط
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if (auth()->user()?->hasPermission('lead.manage'))
                <div class="ap-card">
                    <div class="ap-card__body">
                        <form method="post" action="{{ admin_route('admin.leads.destroy', $lead) }}"
                            data-confirm="حذف العميل {{ $lead->name }} وكل أنشطته نهائياً؟">
                            @csrf
                            @method('delete')
                            <button type="submit" class="ap-btn ap-btn--danger ap-btn--block">
                                @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 16]) حذف العميل
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
