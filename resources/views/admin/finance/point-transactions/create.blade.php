@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $subtitle)

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">{{ $title }}</h2><a href="{{ admin_route('admin.finance.point-transactions.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
        <div class="ap-card__body">
            @if ($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
            @if (session('success'))<div class="ap-alert ap-alert--success" style="margin-bottom:14px">{{ session('success') }}</div>@endif
            <form method="post" action="{{ admin_route('admin.finance.point-transactions.store') }}">
                @csrf
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field"><label class="ap-label">المستخدم *</label><select name="user_id" class="ap-select" required><option value="">اختر</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((string) old('user_id', $selectedUser) === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">نوع النقاط *</label><select name="point_type_id" class="ap-select" required><option value="">اختر</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((string) old('point_type_id') === (string) $type->id)>{{ $type->name }} ({{ $type->unit_label }})</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">عدد النقاط *</label><input type="number" name="points" class="ap-input" value="{{ old('points') }}" required><div class="text-muted" style="font-size:11.5px;margin-top:4px">رقم موجب للإضافة، سالب للخصم.</div></div>
                    <div class="ap-field"><label class="ap-label">تاريخ الحركة</label><input type="datetime-local" name="occurred_at" class="ap-input" value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"></div>
                </div>
                <div class="ap-field"><label class="ap-label">سبب/وصف الحركة *</label><input name="description" class="ap-input" value="{{ old('description') }}" required maxlength="255"></div>
                <div class="d-flex gap-2"><button class="ap-btn ap-btn--primary">تسجيل الحركة</button><a href="{{ admin_route('admin.finance.point-transactions.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a></div>
            </form>
        </div>
    </div>
@endsection
