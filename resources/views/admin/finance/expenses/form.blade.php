@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'تسجيل مصروف إداري أو تشغيلي')

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">{{ $title }}</h2><a href="{{ admin_route('admin.finance.expenses.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
        <div class="ap-card__body">
            @if($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ $expense->exists ? admin_route('admin.finance.expenses.update', $expense) : admin_route('admin.finance.expenses.store') }}">
                @csrf @if($expense->exists) @method('put') @endif
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field"><label class="ap-label">اسم البند *</label><input name="title" class="ap-input" value="{{ old('title', $expense->title) }}" required maxlength="160"></div>
                    <div class="ap-field"><label class="ap-label">التصنيف *</label><select name="category" class="ap-select">@foreach($categories as $k=>$v)<option value="{{ $k }}" @selected(old('category',$expense->category)===$k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">المبلغ *</label><input type="number" step="0.01" min="0.01" name="amount" class="ap-input" value="{{ old('amount', $expense->amount) }}" required></div>
                    <div class="ap-field"><label class="ap-label">العملة</label><input class="ap-input" value="EGP — جنيه مصري" disabled><input type="hidden" name="currency" value="EGP"></div>
                    <div class="ap-field"><label class="ap-label">طريقة الدفع *</label><select name="method" class="ap-select">@foreach($methods as $k=>$v)<option value="{{ $k }}" @selected(old('method',$expense->method)===$k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">تاريخ المصروف *</label><input type="date" name="expense_date" class="ap-input" value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d') ?? now()->toDateString()) }}" required></div>
                    <div class="ap-field"><label class="ap-label">مرجع/فاتورة</label><input name="reference" class="ap-input" value="{{ old('reference', $expense->reference) }}" maxlength="100"></div>
                </div>
                <div class="ap-field"><label class="ap-label">ملاحظات</label><textarea name="notes" class="ap-input" rows="3">{{ old('notes', $expense->notes) }}</textarea></div>
                <div class="d-flex gap-2"><button class="ap-btn ap-btn--primary">حفظ</button><a href="{{ admin_route('admin.finance.expenses.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a></div>
            </form>
        </div>
    </div>
@endsection
