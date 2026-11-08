@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">{{ $title }}</h2><a href="{{ admin_route('admin.finance.deposits.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
        <div class="ap-card__body">
            @if ($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ admin_route('admin.finance.deposits.store') }}">
                @csrf
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field"><label class="ap-label">ربط بمستخدم (اختياري)</label><select name="user_id" id="depositUser" class="ap-select"><option value="">— مودع غير مسجل —</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) old('user_id', $selectedUser) === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select><div class="text-muted" style="font-size:11.5px;margin-top:4px">لو المودع مش مسجل، اكتب اسمه وتليفونه يدويًا.</div></div>
                    <div class="ap-field"><label class="ap-label">اسم المودع (لو غير مسجل) *</label><input name="depositor_name" class="ap-input" value="{{ old('depositor_name') }}" maxlength="120"></div>
                    <div class="ap-field"><label class="ap-label">تليفون المودع</label><input name="depositor_phone" class="ap-input" value="{{ old('depositor_phone') }}" maxlength="30" dir="ltr"></div>
                    <div class="ap-field"><label class="ap-label">المبلغ *</label><input type="number" step="0.01" min="0.01" name="amount" class="ap-input" value="{{ old('amount') }}" required></div>
                    <div class="ap-field"><label class="ap-label">العملة *</label><select name="currency" class="ap-select"><option value="EGP" selected>EGP — جنيه مصري</option></select></div>
                    <div class="ap-field"><label class="ap-label">الطريقة *</label><select name="method" class="ap-select">@foreach($methods as $k=>$v)<option value="{{ $k }}" @selected(old('method','cash')===$k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">مرجع العملية</label><input name="reference" class="ap-input" value="{{ old('reference') }}" placeholder="رقم التحويل/الشيك"></div>
                </div>
                <div class="ap-field"><label class="ap-label">ملاحظات</label><textarea name="notes" class="ap-input" rows="3">{{ old('notes') }}</textarea></div>
                <div class="d-flex gap-2"><button class="ap-btn ap-btn--primary">تسجيل الوديعة</button><a href="{{ admin_route('admin.finance.deposits.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a></div>
            </form>
        </div>
    </div>
@endsection
