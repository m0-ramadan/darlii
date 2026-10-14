@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
    $user = auth()->user();
    $isPending = $user?->status === 'pending';
@endphp

@section('title', 'عقاراتي المسجلة')

@push('head')
    <style>
        @media (max-width: 576px) {
            .dashboard-properties-title {
                font-size: 22px !important;
                line-height: 1.35;
            }

            .dashboard-properties-subtitle {
                display: none;
            }
        }
    </style>
@endpush

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
                ⏳ <strong>حسابك قيد المراجعة والاعتماد:</strong> مرحباً بك في دارلي! حسابك حالياً قيد المراجعة والاعتماد من قبل إدارة المنصة. بمجرد تفعيل الحساب ستتمكن من إضافة ونشر العقارات مباشرة.
            </div>
        @endif

        {{-- Header & Add button --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('dashboard.index', ['locale' => $locale]) }}" class="text-muted text-decoration-none" style="font-size: 13px;">لوحة التحكم</a>
                    <span class="text-muted">/</span>
                    <span class="fw-bold" style="font-size: 13px;">عقاراتي</span>
                </div>
                <h1 class="h3 fw-bold mb-1 dashboard-properties-title">عقاراتي المسجلة والمتابعة</h1>
                <p class="text-muted mb-0 dashboard-properties-subtitle" style="font-size: 14px;">متابعة تفاصيل العقارات والزيارات وحالات البيع لكل وحدة</p>
            </div>

            <div class="d-flex gap-2">
                @if ($isPending)
                    <button class="btn btn-secondary disabled" style="opacity: 0.65; border-radius: 8px; font-weight: 600;">
                        + إضافة عقار جديد (بانتظار التفعيل)
                    </button>
                @else
                    <a class="btn btn-primary" href="{{ route('dashboard.properties.create', ['locale' => $locale]) }}"
                        style="background: #db1d23; border-color: #db1d23; font-weight: 700; padding: 10px 22px; border-radius: 8px;">
                        + إضافة عقار جديد
                    </a>
                @endif
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <a href="{{ route('dashboard.properties.index', ['locale' => $locale]) }}"
               class="btn btn-sm @if(!request('status')) btn-dark @else btn-outline-secondary @endif" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                الكل ({{ $properties->total() }})
            </a>
            <a href="{{ route('dashboard.properties.index', ['locale' => $locale, 'status' => 'published']) }}"
               class="btn btn-sm @if(request('status') === 'published') btn-success @else btn-outline-success @endif" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                ✓ معروضة بالموقع
            </a>
            <a href="{{ route('dashboard.properties.index', ['locale' => $locale, 'status' => 'sold']) }}"
               class="btn btn-sm @if(request('status') === 'sold') btn-info text-white @else btn-outline-info @endif" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                🤝 تم البيع
            </a>
            <a href="{{ route('dashboard.properties.index', ['locale' => $locale, 'status' => 'pending_review']) }}"
               class="btn btn-sm @if(request('status') === 'pending_review') btn-warning @else btn-outline-warning text-dark @endif" style="border-radius: 20px; font-size: 13px; padding: 6px 16px;">
                ⏳ قيد المراجعة
            </a>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                    <thead style="background: #F8FAFC;">
                        <tr>
                            <th style="padding: 14px 18px;">كود العقار</th>
                            <th>اسم العقار والموقع</th>
                            <th>السعر</th>
                            <th>الزيارات (المشاهدات)</th>
                            <th>الحالة بالموقع</th>
                            <th class="text-end" style="padding-left: 18px;">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($properties as $p)
                            @php
                                $st = $p->status?->value ?? (string) $p->status;
                            @endphp
                            <tr>
                                <td style="padding: 14px 18px;">
                                    <code>{{ $p->reference_code }}</code>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $p->translation()?->name ?: 'عقار بدون اسم' }}</div>
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ $p->location?->translation()?->name ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if ($p->isPriceOnContact())
                                        <span class="badge text-bg-secondary">عند التواصل</span>
                                    @else
                                        <span class="fw-bold">{{ money_eg($p->price) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="d-inline-flex align-items-center gap-1 fw-bold text-primary" style="font-size: 13.5px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        {{ number_format($p->views_count) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($st === 'pending_review')
                                        <span class="badge"
                                            style="background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ⏳ تحت المراجعة
                                        </span>
                                    @elseif ($st === 'published')
                                        <span class="badge"
                                            style="background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ✓ معتمد ومنشور
                                        </span>
                                    @elseif ($st === 'sold')
                                        <span class="badge"
                                            style="background: #CCFBF1; color: #0F766E; border: 1px solid #99F6E4; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            🤝 تم البيع
                                        </span>
                                    @elseif ($st === 'rejected')
                                        <span class="badge"
                                            style="background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            ✕ مرفوض
                                        </span>
                                    @else
                                        <span class="badge"
                                            style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            {{ $st }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end" style="padding-left: 18px;">
                                    <div class="d-inline-flex gap-1">
                                        @if ($st === 'draft' || $st === 'rejected')
                                            <form method="post"
                                                action="{{ route('dashboard.properties.submit', ['locale' => $locale, 'property' => $p]) }}"
                                                style="display:inline;">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary"
                                                    style="border-radius: 6px; font-size: 12px;">إرسال للمراجعة</button>
                                            </form>
                                        @elseif ($st === 'pending_review')
                                            <span class="text-muted" style="font-size: 12px;">بانتظار موافقة الإدارة</span>
                                        @elseif ($st === 'published')
                                            <a href="{{ route('properties.show', ['locale' => $locale, 'slug' => $p->translation()?->slug ?? $p->id]) }}"
                                                class="btn btn-sm btn-outline-success" target="_blank"
                                                style="border-radius: 6px; font-size: 12px;">
                                                عرض بالموقع ↗
                                            </a>
                                            <form method="POST" action="{{ route('dashboard.properties.sold', ['locale' => $locale, 'property' => $p]) }}" style="display:inline;" onsubmit="return confirm('هل تريد تحديد هذا العقار كـ «تم البيع»؟');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-teal" style="border: 1px solid #0d9488; color: #0d9488; border-radius: 6px; font-size: 12px;">
                                                    تم البيع ✓
                                                </button>
                                            </form>
                                        @elseif ($st === 'sold')
                                            <form method="POST" action="{{ route('dashboard.properties.available', ['locale' => $locale, 'property' => $p]) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; font-size: 12px;">
                                                    إعادة العرض
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    لا توجد عقارات مسجلة لديك تطابق هذا الفلتر.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($properties->hasPages())
            <div class="mt-4">
                {{ $properties->links() }}
            </div>
        @endif
    </div>
@endsection
