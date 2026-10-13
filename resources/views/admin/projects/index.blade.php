@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle ?? 'إدارة المشروعات العقارية: الأكواد، المواقع، أعداد الوحدات وحالة النشر')

@section('page_actions')
    @if (auth()->user()?->hasPermission('project.manage'))
        <a href="{{ admin_route('admin.projects.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة مشروع
        </a>
    @endif
@endsection

@section('content')
    <div class="ap-card">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 240px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="ap-input ap-input--sm"
                        placeholder="كود المشروع أو الاسم أو المستثمر…">
                </div>
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
                    <label class="ap-label">التمييز</label>
                    <select name="is_featured" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="1" @selected(($filters['is_featured'] ?? '') === '1')>المشروعات المميزة فقط</option>
                        <option value="0" @selected(($filters['is_featured'] ?? '') === '0')>غير المميزة فقط</option>
                    </select>
                </div>
                <button class="ap-btn ap-btn--sm">
                    @include('admin.partials.icon', ['iconName' => 'search', 'size' => 16]) فلترة
                </button>
                <a href="{{ url()->current() }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    <div class="ap-grid ap-grid--3" style="margin-bottom: 18px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">إجمالي النتائج</div>
                <div class="ap-stat__value">{{ number_format($projects->total()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--success">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">منشورة في هذه الصفحة</div>
                <div class="ap-stat__value">{{ number_format($projects->getCollection()->where('status', 'published')->count()) }}</div>
            </div>
        </div>
        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'star', 'size' => 20])</div>
            <div>
                <div class="ap-stat__label">مميزة في هذه الصفحة</div>
                <div class="ap-stat__value">{{ number_format($projects->getCollection()->where('is_featured', true)->count()) }}</div>
            </div>
        </div>
    </div>

    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'building', 'size' => 18])
                {{ $title }}
                <span class="ap-badge">{{ number_format($projects->total()) }}</span>
            </h2>
        </div>

        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>الكود</th>
                            <th>المشروع</th>
                            <th>المستثمر</th>
                            <th>الموقع</th>
                            <th>الوحدات</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td><code>{{ $project->reference_code }}</code></td>
                                <td>
                                    <div class="fw-bold">
                                        {{ \Illuminate\Support\Str::limit($project->translation('ar')?->name ?? $project->reference_code, 44) }}
                                    </div>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ $project->translation('en')?->name ?? '— بدون اسم إنجليزي —' }}
                                        @if ($project->is_featured)
                                            <span class="ap-badge ap-badge--gold">مميز</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $project->investor_name ?: '—' }}</td>
                                <td>{{ $project->location?->translation('ar')?->name ?? '—' }}</td>
                                <td>
                                    <span class="ap-badge ap-badge--info">{{ number_format($project->units_count) }} وحدة</span>
                                    <div class="text-muted" style="font-size: 11.5px;">
                                        {{ number_format((int) $project->flats_count) }} شقة ·
                                        {{ number_format((int) $project->blocks_count) }} بلوك
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--{{ status_color($project->status) }}">
                                        {{ $statusLabels[$project->status] ?? status_label($project->status) }}
                                    </span>
                                </td>
                                <td>{{ $project->created_at?->format('Y/m/d') ?? '—' }}</td>
                                <td>
                                    <div class="ap-table__actions">
                                        @if (auth()->user()?->hasPermission('project.manage'))
                                            <a href="{{ admin_route('admin.projects.edit', $project) }}"
                                                class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                                @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 15])
                                            </a>
                                            <form method="post" action="{{ admin_route('admin.projects.destroy', $project) }}"
                                                data-confirm="حذف المشروع {{ $project->reference_code }} نهائياً؟">
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
                                        @include('admin.partials.icon', ['iconName' => 'building', 'size' => 42])
                                        <p>لا توجد مشروعات مطابقة للفلترة الحالية.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($projects->hasPages())
            <div class="ap-pagination">{{ $projects->links() }}</div>
        @endif
    </div>
@endsection
