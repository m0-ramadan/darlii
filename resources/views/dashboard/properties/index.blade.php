@extends('layouts.app')

@section('title', 'عقاراتي')

@section('content')
    <div class="container page-section" style="padding-top: 40px; padding-bottom: 60px;">
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center mb-4" role="alert"
                style="border-radius: 12px; background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                <div>
                    <strong>✓ تم بنجاح:</strong> {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1">عقاراتي المسجلة</h1>
                <p class="text-muted mb-0" style="font-size: 14px;">متابعة حالة العقارات الخاصة بك والمضافة للمنصة</p>
            </div>
            <a class="btn btn-primary" href="{{ route('dashboard.properties.create', ['locale' => app()->getLocale()]) }}"
                style="background: #C49A45; border-color: #C49A45; font-weight: 600; padding: 10px 22px; border-radius: 8px;">
                + إضافة عقار جديد
            </a>
        </div>

        {{-- تنبيه المراجعة والاعتماد --}}
        <div class="p-3 mb-4"
            style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; font-size: 13.5px; color: #92400E;">
            ℹ️ <strong>ملاحظة للمستخدمين:</strong> عند إضافة أي عقار جديد، يتم حفظه فوراً بحالة <strong>«تحت
                المراجعة»</strong> لمطابقة بياناته من قِبل إدارة المنصة، ولا يظهر في نتائج البحث والصفحات العامة للموقع إلا
            بعد اعتماد الإدارة وتأكيده لضمان جودة العروض.
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 14px;">
                    <thead style="background: #F8FAFC;">
                        <tr>
                            <th style="padding: 14px 18px;">كود العقار</th>
                            <th>اسم العقار</th>
                            <th>السعر</th>
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
                                        {{ $p->location?->translation()?->name ?? '—' }}</div>
                                </td>
                                <td>
                                    @if ($p->isPriceOnContact())
                                        <span class="badge text-bg-secondary">عند التواصل</span>
                                    @else
                                        <span class="fw-bold">{{ money_eg($p->price) }}</span>
                                    @endif
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
                                            ✓ معتمد ومنشور بالموقع
                                        </span>
                                    @elseif ($st === 'sold')
                                        <span class="badge"
                                            style="background: #DCFCE7; color: #15803D; border: 1px solid #86EFAC; font-size: 12px; padding: 6px 12px; border-radius: 20px;">
                                            تم البيع
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
                                    @if ($st === 'draft' || $st === 'rejected')
                                        <form method="post"
                                            action="{{ route('dashboard.properties.submit', ['locale' => app()->getLocale(), 'property' => $p]) }}"
                                            style="display:inline;">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary"
                                                style="border-radius: 6px; font-size: 12px;">إرسال للمراجعة</button>
                                        </form>
                                    @elseif ($st === 'pending_review')
                                        <span class="text-muted" style="font-size: 12px;">بانتظار موافقة الإدارة</span>
                                    @elseif ($st === 'published')
                                        <a href="{{ route('properties.show', ['locale' => app()->getLocale(), 'slug' => $p->translation()?->slug ?? $p->id]) }}"
                                            class="btn btn-sm btn-outline-success" target="_blank"
                                            style="border-radius: 6px; font-size: 12px;">
                                            عرض بالموقع ↗
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    لا توجد عقارات مسجلة لديك حتى الآن. اضغط على «+ إضافة عقار جديد» للبدء.
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
