@extends('layouts.public-shell')

@section('title', 'طلبات العملاء')

@push('head')
    <link rel="stylesheet" href="{{ asset('themes/darlii/css/dashboard-requests.css') }}?v=1.0.0">
@endpush

@section('content')
    <div class="darli-dash-wrapper cr-page">
        <div class="darli-dash-hero">
            <div class="container">
                <div class="darli-dash-breadcrumb">
                    <a href="{{ route('home') }}">الرئيسية</a><span class="sep">›</span>
                    <a href="{{ route('dashboard.index') }}">لوحة التحكم</a><span class="sep">›</span>
                    <span>طلبات العملاء</span>
                </div>
                <div class="cr-head">
                    <div>
                        <h1 class="cr-title">طلبات العملاء</h1>
                        <p class="cr-sub">سجل احتياجات العملاء ومتابعة حالتها — متاح لكل حسابات الداشبورد.</p>
                    </div>
                    <a href="{{ route('dashboard.customer-requests.create') }}" class="cr-btn cr-btn--gold">+ إضافة طلب عميل</a>
                </div>
            </div>
        </div>

        <div class="container">
            @if (session('success'))<div class="cr-alert cr-alert--success">{{ session('success') }}</div>@endif
            @if ($errors->any())<div class="cr-alert cr-alert--danger">{{ $errors->first() }}</div>@endif

            <div class="cr-shell">
                @include('dashboard.customer-requests._sidebar')

                <div class="cr-main">
                    <div class="cr-stats">
                        <div class="cr-stat"><div class="cr-stat__label">إجمالي الطلبات</div><div class="cr-stat__value">{{ number_format(array_sum($counts)) }}</div></div>
                        <div class="cr-stat"><div class="cr-stat__label">جديد</div><div class="cr-stat__value">{{ number_format($counts['new'] ?? 0) }}</div></div>
                        <div class="cr-stat"><div class="cr-stat__label">قيد التنفيذ</div><div class="cr-stat__value">{{ number_format($counts['in_progress'] ?? 0) }}</div></div>
                        <div class="cr-stat"><div class="cr-stat__label">تم</div><div class="cr-stat__value">{{ number_format($counts['completed'] ?? 0) }}</div></div>
                    </div>

                    <div class="cr-card cr-filter">
                        <form method="get">
                            <div class="cr-field"><label>بحث</label><input name="q" value="{{ $filters['q'] ?? '' }}" class="cr-input" placeholder="اسم العميل / التليفون / الاحتياج"></div>
                            <div class="cr-field"><label>الحالة</label><select name="status" class="cr-select"><option value="">كل الحالات</option>@foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected(($filters['status'] ?? '')===$k)>{{ $v }}</option>@endforeach</select></div>
                            <div class="cr-field"><label>العنوان</label><select name="location_id" class="cr-select"><option value="">كل المواقع</option>@foreach($locations as $loc)<option value="{{ $loc['id'] }}" @selected((string)($filters['location_id'] ?? '')===(string)$loc['id'])>{{ $loc['label'] }}</option>@endforeach</select></div>
                            <div class="cr-field"><label>من</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="cr-input"></div>
                            <div class="cr-field"><label>إلى</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="cr-input"></div>
                            <button class="cr-btn cr-btn--sm">فلترة</button>
                            <a href="{{ route('dashboard.customer-requests.index') }}" class="cr-btn cr-btn--ghost cr-btn--sm">إعادة تعيين</a>
                        </form>
                    </div>

                    <div class="cr-card">
                        <div class="cr-table-wrap">
                            <table class="cr-table">
                                <thead><tr><th>العميل</th><th>الاحتياج</th><th>العنوان</th><th>الحالة</th><th>أضيف بواسطة</th><th>التاريخ</th><th>إجراء</th></tr></thead>
                                <tbody>
                                @forelse ($requests as $req)
                                    <tr>
                                        <td>
                                            <div class="cr-name">{{ $req->customer_name }}</div>
                                            <span class="cr-phone">{{ $req->phone }}</span>
                                        </td>
                                        <td>
                                            <div class="cr-need">{{ \Illuminate\Support\Str::limit($req->need, 130) }}</div>
                                            @if($req->notes)<div class="cr-muted">ملاحظات: {{ \Illuminate\Support\Str::limit($req->notes, 70) }}</div>@endif
                                        </td>
                                        <td>
                                            <div>{{ $req->locationName() }}</div>
                                            @if($req->address_details)<div class="cr-muted">{{ $req->address_details }}</div>@endif
                                        </td>
                                        <td>
                                            <form method="post" action="{{ route('dashboard.customer-requests.status', $req) }}">
                                                @csrf @method('patch')
                                                <select name="status" class="cr-select" style="min-width:120px;padding:6px 8px;font-size:12px" onchange="this.form.submit()">
                                                    @foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected($req->status===$k)>{{ $v }}</option>@endforeach
                                                </select>
                                            </form>
                                        </td>
                                        <td>{{ $req->creator?->name ?? '—' }}<div class="cr-muted">{{ $req->created_at?->format('Y/m/d H:i') }}</div></td>
                                        <td>{{ $req->updated_at?->format('Y/m/d') }}</td>
                                        <td>
                                            <div class="cr-actions">
                                                <a href="{{ route('dashboard.customer-requests.edit', $req) }}" class="cr-btn cr-btn--ghost cr-btn--sm">تعديل</a>
                                                @if(auth()->id() === $req->created_by || auth()->user()?->isAdmin())
                                                    <form method="post" action="{{ route('dashboard.customer-requests.destroy', $req) }}" onsubmit="return confirm('حذف هذا الطلب؟')">
                                                        @csrf @method('delete')
                                                        <button class="cr-btn cr-btn--danger cr-btn--sm">حذف</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7"><div class="cr-empty">لا توجد طلبات مطابقة. أضف أول طلب عميل.</div></td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($requests->hasPages())<div class="cr-pagination">{{ $requests->links() }}</div>@endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
