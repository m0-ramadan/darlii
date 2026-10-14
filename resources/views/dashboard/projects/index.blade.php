@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $isPending = $user->status === 'pending';
@endphp

@section('title', $isAr ? 'مشاريع الشركة' : 'Company Projects')

@section('content')
<div class="container page-section" style="padding-top: 35px; padding-bottom: 60px;">
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-4" role="alert"
            style="border-radius: 12px; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
            <div><strong>✓ تم بنجاح:</strong> {{ session('success') }}</div>
        </div>
    @endif

    @if (session('status_warning') || $isPending)
        <div class="alert alert-warning mb-4 shadow-sm" role="alert"
            style="border-radius: 12px; background: #FFFBEB; border-color: #FDE68A; color: #92400E; padding: 16px 20px;">
            ⏳ <strong>حساب الشركة قيد المراجعة:</strong> حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك نشر مشاريع جديدة حتى يتم تفعيل الحساب.
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('dashboard.index', ['locale' => $locale]) }}" class="text-muted text-decoration-none" style="font-size: 13px;">لوحة التحكم</a>
                <span class="text-muted">/</span>
                <span class="fw-bold" style="font-size: 13px;">المشاريع</span>
            </div>
            <h1 class="h3 fw-bold mb-1">مشاريع الشركة العقارية</h1>
            <p class="text-muted mb-0" style="font-size: 14px;">إدارة مشروعات التطوير العقاري الخاصة بشركتك والوحدات التابعة لها</p>
        </div>

        <div>
            @if ($isPending)
                <button class="btn btn-secondary disabled" style="opacity: 0.65; border-radius: 8px; font-weight: 600;">
                    + إضافة مشروع جديد (بانتظار التفعيل)
                </button>
            @else
                <a class="btn btn-primary" href="{{ route('dashboard.projects.create', ['locale' => $locale]) }}"
                    style="background: #C8A45C; border-color: #C8A45C; font-weight: 700; padding: 10px 22px; border-radius: 8px;">
                    + إضافة مشروع جديد
                </a>
            @endif
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3" style="font-size: 14px;">
        <li class="nav-item">
            <a class="nav-link text-dark fw-semibold" href="{{ route('dashboard.index', ['locale' => $locale]) }}">
                لوحة التحكم
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-dark fw-semibold" href="{{ route('dashboard.properties.index', ['locale' => $locale]) }}">
                عقارات الشركة
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="{{ route('dashboard.projects.index', ['locale' => $locale]) }}" style="background: #C8A45C; border-radius: 8px;">
                المشاريع ({{ $projects->total() }})
            </a>
        </li>
    </ul>

    {{-- Projects Table --}}
    <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                <thead style="background: #F8FAFC;">
                    <tr>
                        <th style="padding: 14px 18px;">كود المشروع</th>
                        <th>اسم المشروع</th>
                        <th>الموقع</th>
                        <th>العمارات / الوحدات</th>
                        <th>تاريخ التسليم</th>
                        <th>الحالة</th>
                        <th class="text-end" style="padding-left: 18px;">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $proj)
                        @php
                            $tr = $proj->translation();
                        @endphp
                        <tr>
                            <td style="padding: 14px 18px;">
                                <code>{{ $proj->reference_code }}</code>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $tr?->name ?: 'مشروع بدون اسم' }}</div>
                                @if ($proj->investor_name)
                                    <div class="text-muted" style="font-size: 12px;">المطور: {{ $proj->investor_name }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $proj->location?->translation()?->name ?? '—' }}
                            </td>
                            <td>
                                <div>{{ $proj->blocks_count ? $proj->blocks_count . ' عمارة' : '—' }}</div>
                                <div class="text-muted" style="font-size: 12px;">{{ $proj->flats_count ? $proj->flats_count . ' شقة/وحدة' : '' }}</div>
                            </td>
                            <td>
                                {{ $proj->completion_date?->format('Y/m') ?? '—' }}
                            </td>
                            <td>
                                @if ($proj->status === 'published')
                                    <span class="badge" style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        ✓ معتمد ومنشور
                                    </span>
                                @elseif ($proj->status === 'draft')
                                    <span class="badge" style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        مسودة / قيد المراجعة
                                    </span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 12px; padding: 5px 10px; border-radius: 20px;">
                                        {{ $proj->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end" style="padding-left: 18px;">
                                @if ($proj->status === 'published')
                                    <a href="{{ route('projects.show', ['locale' => $locale, 'slug' => $tr?->slug ?? $proj->id]) }}"
                                        class="btn btn-sm btn-outline-success" target="_blank"
                                        style="border-radius: 6px; font-size: 12px;">
                                        عرض بالموقع ↗
                                    </a>
                                @else
                                    <span class="text-muted" style="font-size: 12px;">بانتظار تفعيل الإدارة</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                لا توجد مشاريع مسجلة لشركتك حتى الآن. اضغط على «+ إضافة مشروع جديد» للبدء.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($projects->hasPages())
        <div class="mt-4">
            {{ $projects->links() }}
        </div>
    @endif
</div>
@endsection

