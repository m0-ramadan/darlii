@extends('layouts.admin')

@section('title', $title ?? 'العقارات')
@section('page_title', $title ?? 'العقارات')
@section('page_subtitle', $subtitle ?? 'إدارة كاملة لكل العقارات مع الفلترة والإسناد والاعتماد')

@section('page_actions')
    @can('property.create')
        <a href="{{ admin_route('admin.properties.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة عقار
        </a>
    @endcan
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 220px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="كود العقار أو الاسم…">
                </div>
                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                {{ status_label($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الغرض</label>
                    <select name="purpose" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="sale" @selected(($filters['purpose'] ?? '') === 'sale')>بيع</option>
                        <option value="rent" @selected(($filters['purpose'] ?? '') === 'rent')>إيجار</option>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">التصنيف</label>
                    <select name="category_id" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $category->id)>
                                {{ $category->translation('ar')?->name ?? $category->key }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الإسناد</label>
                    <select name="assignment" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="assigned" @selected(($filters['assignment'] ?? '') === 'assigned')>مُسند</option>
                        <option value="unassigned" @selected(($filters['assignment'] ?? '') === 'unassigned')>غير مُسند</option>
                    </select>
                </div>
                <div class="ap-field">
                    <label class="ap-label">الترتيب</label>
                    <select name="sort" class="ap-select ap-select--sm">
                        <option value="">الأحدث</option>
                        <option value="views" @selected(($filters['sort'] ?? '') === 'views')>الأكثر مشاهدة</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>الأعلى سعراً</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>الأقل سعراً</option>
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    @include('admin.partials.icon', ['iconName' => 'search', 'size' => 16]) فلترة
                </button>
                <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    @if (($filters['status'] ?? null) === null && !isset($subtitle))
        <div class="ap-grid ap-grid--4" style="margin-bottom: 18px;">
            <div class="ap-stat">
                <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
                <div>
                    <div class="ap-stat__label">إجمالي النتائج</div>
                    <div class="ap-stat__value">{{ number_format($properties->total()) }}</div>
                </div>
            </div>
        </div>
    @endif

    <form method="post" action="{{ admin_route('admin.properties.bulk') }}" id="bulkForm">
        @csrf
        <div class="ap-card">
            <div class="ap-card__head">
                <h2 class="ap-card__title">
                    @include('admin.partials.icon', ['iconName' => 'building', 'size' => 18])
                    {{ $title ?? 'العقارات' }}
                    <span class="ap-badge">{{ number_format($properties->total()) }}</span>
                </h2>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <select name="action" class="ap-select ap-select--sm" style="width: auto;">
                        <option value="">إجراء جماعي…</option>
                        @can('property.approve')
                            <option value="approve">اعتماد ونشر</option>
                            <option value="reject">رفض</option>
                        @endcan
                        @can('property.feature')
                            <option value="feature">تمييز</option>
                            <option value="unfeature">إلغاء التمييز</option>
                        @endcan
                        @can('property.assign')
                            <option value="assign">إسناد إلى…</option>
                        @endcan
                        @can('property.delete')
                            <option value="delete">حذف</option>
                        @endcan
                    </select>
                    <select name="value" class="ap-select ap-select--sm" style="width: auto;">
                        <option value="">— اختر المستخدم عند الإسناد —</option>
                        @foreach ($assignableUsers as $assignee)
                            <option value="{{ $assignee->id }}">{{ $assignee->name }} ({{ user_role_label($assignee->primary_role_slug) }})</option>
                        @endforeach
                    </select>
                    <button class="ap-btn ap-btn--sm ap-btn--ghost" data-confirm="تنفيذ الإجراء الجماعي على العناصر المحددة؟">تنفيذ</button>
                </div>
            </div>

            <div class="ap-card__body ap-card__body--tight">
                <div class="ap-table-wrap">
                    <table class="ap-table">
                        <thead>
                            <tr>
                                <th style="width: 34px;"><input type="checkbox" data-check-all></th>
                                <th>الصورة</th>
                                <th>الكود</th>
                                <th>العقار</th>
                                <th>النوع</th>
                                <th>السعر</th>
                                <th>الحالة</th>
                                <th>المسؤول / السمسار</th>
                                <th>المشاهدات</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($properties as $property)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $property->id }}" data-check-item></td>
                                    <td>
                                        <img src="{{ $property->primaryImageUrl('ar') }}" alt="" class="ap-table__thumb"
                                            loading="lazy">
                                    </td>
                                    <td><code>{{ $property->reference_code }}</code></td>
                                    <td>
                                        <a href="{{ admin_route('admin.properties.show', $property) }}" class="fw-bold">
                                            {{ \Illuminate\Support\Str::limit($property->displayName('ar'), 42) }}
                                        </a>
                                        <div class="text-muted" style="font-size: 11.5px;">
                                            {{ $property->location?->translation('ar')?->name ?? '—' }}
                                            @if ($property->is_featured)
                                                <span class="ap-badge ap-badge--gold">مميز</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ap-badge">{{ $property->purpose === 'rent' ? 'إيجار' : 'بيع' }}</span>
                                        <div class="text-muted" style="font-size: 11.5px;">{{ $property->category?->translation('ar')?->name ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ money_eg($property->price) }}</div>
                                        <div style="display: flex; align-items: center; gap: 4px; margin-top: 4px; flex-wrap: wrap;">
                                            @if ($property->price_type === 'negotiable')
                                                <span class="ap-badge ap-badge--info" style="font-size: 10px; padding: 1px 6px;">تفاوض</span>
                                            @elseif ($property->price_type === 'contact')
                                                <span class="ap-badge ap-badge--warning" style="font-size: 10px; padding: 1px 6px;">عند التواصل</span>
                                            @else
                                                <span class="ap-badge ap-badge--secondary" style="font-size: 10px; padding: 1px 6px;">محدد</span>
                                            @endif
                                            @if ($property->price_notes)
                                                <span title="ملاحظات تفاوض سرية: {{ Str::limit($property->price_notes, 80) }}" style="cursor: help; color: #d97706; font-size: 12px; display: inline-flex; align-items: center;" aria-label="ملاحظات تفاوض سرية">
                                                    🔒
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="ap-badge ap-badge--{{ status_color($property->status) }}">
                                            {{ status_label($property->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 12.5px;">{{ $property->manager?->name ?? $property->owner?->name ?? '— غير مُسند —' }}</div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            {{ $property->manager ? user_role_label($property->manager->primary_role_slug) : '' }}
                                        </div>
                                    </td>
                                    <td>{{ number_format($property->views_count) }}</td>
                                    <td>
                                        <div class="ap-table__actions">
                                            <a href="{{ admin_route('admin.properties.show', $property) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض">
                                                @include('admin.partials.icon', ['iconName' => 'eye', 'size' => 15])
                                            </a>
                                            @can('property.update-any')
                                                <a href="{{ admin_route('admin.properties.edit', $property) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                                    @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                                </a>
                                            @endcan
                                            @can('property.stats')
                                                <a href="{{ admin_route('admin.properties.stats', $property) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="إحصائيات">
                                                    @include('admin.partials.icon', ['iconName' => 'chart-bar', 'size' => 15])
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="ap-empty">
                                            @include('admin.partials.icon', ['iconName' => 'building', 'size' => 42])
                                            <p>لا توجد عقارات مطابقة للفلترة الحالية.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($properties->hasPages())
                <div class="ap-pagination">{{ $properties->links() }}</div>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function() {
            var all = document.querySelector('[data-check-all]');
            if (all) {
                all.addEventListener('change', function() {
                    document.querySelectorAll('[data-check-item]').forEach(function(box) {
                        box.checked = all.checked;
                    });
                });
            }
            var form = document.getElementById('bulkForm');
            if (form) {
                form.addEventListener('submit', function(event) {
                    var action = form.querySelector('[name="action"]').value;
                    if (!action) {
                        event.preventDefault();
                        alert('اختر إجراءً جماعياً أولاً.');
                        return;
                    }
                    if (!confirm('تنفيذ الإجراء "' + action + '" على العقارات المحددة؟')) {
                        event.preventDefault();
                    }
                });
            }
        })();
    </script>
@endpush
