@extends('layouts.admin')

@section('title', 'الوظائف وفرص العمل')
@section('page_title', 'الوظائف وفرص العمل')
@section('page_subtitle', 'إدارة الوظائف المتاحة واستقبال طلبات التوظيف بالمنصة')

@section('page_actions')
    @can('content.manage')
        <a href="{{ admin_route('admin.careers.create') }}" class="ap-btn ap-btn--gold ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'plus', 'size' => 16]) إضافة وظيفة جديدة
        </a>
        <a href="{{ admin_route('admin.careers.applications') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
            @include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 16]) طلبات التقديم ({{ $stats['applications'] }})
        </a>
    @endcan
    <a href="{{ route('careers.index', ['locale' => 'ar']) }}" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'external', 'size' => 16]) صفحة الوظائف بالموقع
    </a>
@endsection

@section('content')
    {{-- مؤشرات سريعة --}}
    <div class="ap-grid ap-grid--4" style="margin-bottom: 20px;">
        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">إجمالي الوظائف</div>
                <div class="ap-stat__value">{{ number_format($stats['total']) }}</div>
                <div class="ap-stat__hint">جميع الفرص المسجلة</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--info">@include('admin.partials.icon', ['iconName' => 'check', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">الوظائف المنشورة</div>
                <div class="ap-stat__value">{{ number_format($stats['published']) }}</div>
                <div class="ap-stat__hint">تظهر للباحثين عن عمل بالموقع</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon">@include('admin.partials.icon', ['iconName' => 'file-text', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">المسودات</div>
                <div class="ap-stat__value">{{ number_format($stats['draft']) }}</div>
                <div class="ap-stat__hint">وظائف قيد الإعداد أو مغلقة</div>
            </div>
        </div>

        <div class="ap-stat">
            <div class="ap-stat__icon ap-stat__icon--gold">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 20])</div>
            <div style="flex: 1;">
                <div class="ap-stat__label">طلبات التقديم</div>
                <div class="ap-stat__value">{{ number_format($stats['applications']) }}</div>
                <div class="ap-stat__hint">سير ذاتية واستمارات تقديم</div>
            </div>
        </div>
    </div>

    {{-- الفلترة والبحث --}}
    <div class="ap-card" style="margin-bottom: 20px;">
        <div class="ap-card__body">
            <form method="get" class="ap-filterbar">
                <div class="ap-field" style="flex: 1 1 240px;">
                    <label class="ap-label">بحث</label>
                    <input type="search" name="q" value="{{ request('q') }}" class="ap-input ap-input--sm" placeholder="المسمى الوظيفي، الكلمات المفتاحية…">
                </div>

                <div class="ap-field">
                    <label class="ap-label">القسم / الإدارة</label>
                    <select name="department" class="ap-select ap-select--sm">
                        <option value="">كل الأقسام</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept }}" @selected(request('department') === $dept)>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ap-field">
                    <label class="ap-label">الحالة</label>
                    <select name="status" class="ap-select ap-select--sm">
                        <option value="">الكل</option>
                        <option value="published" @selected(request('status') === 'published')>منشور فقط</option>
                        <option value="draft" @selected(request('status') === 'draft')>مسودة</option>
                    </select>
                </div>

                <button class="ap-btn ap-btn--sm">فلترة</button>
                <a href="{{ admin_route('admin.careers.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">إعادة تعيين</a>
            </form>
        </div>
    </div>

    {{-- جدول الوظائف --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">
                @include('admin.partials.icon', ['iconName' => 'briefcase', 'size' => 18])
                قائمة الوظائف المتاحة
                <span class="ap-badge">{{ number_format($careers->total()) }}</span>
            </h2>
        </div>
        <div class="ap-card__body ap-card__body--tight">
            <div class="ap-table-wrap">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>المسمى الوظيفي</th>
                            <th>القسم والإدارة</th>
                            <th>الموقع ونوع الدوام</th>
                            <th>نطاق الراتب</th>
                            <th>المستوى</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($careers as $career)
                            @php
                                $title = $career->title;
                                $slug = $career->translation('ar')?->slug ?: $career->translation('en')?->slug;
                            @endphp
                            <tr>
                                <td>
                                    <div>
                                        <a href="{{ admin_route('admin.careers.edit', $career) }}" class="fw-bold">{{ $title }}</a>
                                        <div class="text-muted" style="font-size: 11px;">{{ Str::limit($career->translation()?->summary, 60) }}</div>
                                    </div>
                                </td>
                                <td>
                                    <span class="ap-badge ap-badge--info">{{ $career->department ?: 'عام' }}</span>
                                </td>
                                <td>
                                    <div style="font-size: 13px;">{{ $career->location ?: 'بني سويف' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $career->employment_type ?: 'دوام كامل' }}</div>
                                </td>
                                <td>
                                    @if($career->salary_min || $career->salary_max)
                                        <span class="ap-badge ap-badge--gold" style="font-size: 11px;">
                                            {{ number_format($career->salary_min) }} - {{ number_format($career->salary_max) }} ج.م
                                        </span>
                                    @else
                                        <span class="text-muted" style="font-size: 12px;">يحدد في المقابلة</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 12px;">{{ $career->experience_level ?: 'متوسط' }}</span>
                                </td>
                                <td>
                                    <form method="post" action="{{ admin_route('admin.careers.status', $career) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="ap-btn ap-btn--sm {{ $career->status === 'published' ? 'ap-btn--ghost' : 'ap-btn--danger' }}" style="padding: 2px 8px; font-size: 11px;">
                                            <span class="ap-badge ap-badge--{{ $career->status === 'published' ? 'success' : 'secondary' }}" style="padding:0; background:transparent;">
                                                {{ $career->status === 'published' ? 'منشور' : 'مسودة' }}
                                            </span>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="{{ admin_route('admin.careers.edit', $career) }}" class="ap-btn ap-btn--ghost ap-btn--sm" title="تعديل">
                                            @include('admin.partials.icon', ['iconName' => 'edit', 'size' => 14])
                                        </a>
                                        @if($slug)
                                            <a href="{{ route('careers.show', ['locale' => 'ar', 'slug' => $slug]) }}" target="_blank" class="ap-btn ap-btn--ghost ap-btn--sm" title="عرض في الموقع">
                                                @include('admin.partials.icon', ['iconName' => 'external', 'size' => 14])
                                            </a>
                                        @endif
                                        <form method="post" action="{{ admin_route('admin.careers.destroy', $career) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذه الوظيفة؟');" style="display:inline;">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="ap-btn ap-btn--danger ap-btn--sm" title="حذف" style="padding: 4px 6px;">
                                                @include('admin.partials.icon', ['iconName' => 'trash', 'size' => 14])
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    لا توجد وظائف مسجلة حالياً.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($careers->hasPages())
                <div class="p-3 border-top">
                    {{ $careers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

