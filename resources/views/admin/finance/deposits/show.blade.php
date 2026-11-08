@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-grid ap-grid--2">
        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">بيانات الوديعة #{{ $deposit->id }}</h2><a href="{{ admin_route('admin.finance.deposits.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
            <div class="ap-card__body">
                @if ($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
                @if (session('success'))<div class="ap-alert ap-alert--success" style="margin-bottom:14px">{{ session('success') }}</div>@endif
                <table class="ap-table">
                    <tr><th>المودع</th><td>
                        @if($deposit->user_id)
                            <a href="{{ admin_route('admin.finance.balances.show', $deposit->user_id) }}">{{ $deposit->user?->name }}</a><br><small>{{ $deposit->user?->email }}</small>
                        @else
                            <strong>{{ $deposit->depositorDisplayName() }}</strong><br><small>{{ $deposit->depositorDisplayPhone() }} · مودع غير مسجل</small>
                        @endif
                    </td></tr>
                    <tr><th>المبلغ</th><td><strong>{{ number_format((float) $deposit->amount, 2) }} {{ $deposit->currency }}</strong></td></tr>
                    <tr><th>الطريقة</th><td>{{ $deposit->methodLabel() }}</td></tr>
                    <tr><th>المرجع</th><td>{{ $deposit->reference ?: '—' }}</td></tr>
                    <tr><th>الحالة</th><td><span class="ap-badge ap-badge--{{ $deposit->status === 'approved' ? 'success' : ($deposit->status === 'rejected' ? 'danger' : 'warn') }}">{{ $deposit->statusLabel() }}</span></td></tr>
                    <tr><th>ملاحظات المستخدم</th><td>{{ $deposit->notes ?: '—' }}</td></tr>
                    <tr><th>ملاحظات الإدارة</th><td>{{ $deposit->admin_notes ?: '—' }}</td></tr>
                    <tr><th>أنشئت</th><td>{{ $deposit->created_at?->format('Y/m/d H:i') }} — {{ $deposit->creator?->name }}</td></tr>
                    @if($deposit->approved_at)<tr><th>راجعت</th><td>{{ $deposit->approved_at->format('Y/m/d H:i') }} — {{ $deposit->approver?->name }}</td></tr>@endif
                </table>
            </div>
        </div>

        <div class="ap-card">
            <div class="ap-card__head"><h2 class="ap-card__title">الإجراء</h2></div>
            <div class="ap-card__body">
                @if ($deposit->status === 'pending')
                    <form method="post" action="{{ admin_route('admin.finance.deposits.approve', $deposit) }}" style="margin-bottom:16px;">
                        @csrf
                        <div class="ap-field"><label class="ap-label">ملاحظات الاعتماد</label><textarea name="admin_notes" class="ap-input" rows="2"></textarea></div>
                        <button class="ap-btn ap-btn--primary" data-confirm="اعتماد الوديعة وإضافتها للرصيد؟">اعتماد الوديعة</button>
                    </form>
                    <form method="post" action="{{ admin_route('admin.finance.deposits.reject', $deposit) }}" style="margin-bottom:16px;">
                        @csrf
                        <div class="ap-field"><label class="ap-label">سبب الرفض</label><textarea name="admin_notes" class="ap-input" rows="2"></textarea></div>
                        <button class="ap-btn ap-btn--danger" data-confirm="رفض الوديعة؟">رفض الوديعة</button>
                    </form>
                    <form method="post" action="{{ admin_route('admin.finance.deposits.destroy', $deposit) }}" data-confirm="حذف الوديعة نهائيًا؟">
                        @csrf @method('delete')
                        <button class="ap-btn ap-btn--ghost">حذف الوديعة</button>
                    </form>
                @else
                    <div class="ap-alert ap-alert--info">تمت مراجعة هذه الوديعة ولا يمكن تغييرها.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
