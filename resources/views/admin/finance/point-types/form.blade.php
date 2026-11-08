@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'تعريف نوع نقاط وقيمته الافتراضية')

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">{{ $title }}</h2><a href="{{ admin_route('admin.finance.point-types.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
        <div class="ap-card__body">
            @if ($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ $type->exists ? admin_route('admin.finance.point-types.update', $type) : admin_route('admin.finance.point-types.store') }}">
                @csrf @if($type->exists) @method('put') @endif
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field"><label class="ap-label">الاسم *</label><input name="name" class="ap-input" value="{{ old('name', $type->name) }}" required></div>
                    <div class="ap-field"><label class="ap-label">الكود (اختياري)</label><input name="slug" class="ap-input" dir="ltr" value="{{ old('slug', $type->slug) }}" placeholder="registration"></div>
                    <div class="ap-field"><label class="ap-label">التصنيف</label><input name="category" class="ap-input" value="{{ old('category', $type->category) }}" placeholder="مكافآت / ولاء"></div>
                    <div class="ap-field"><label class="ap-label">وحدة القياس</label><input name="unit_label" class="ap-input" value="{{ old('unit_label', $type->unit_label ?: 'نقطة') }}" required></div>
                    <div class="ap-field"><label class="ap-label">النقاط الافتراضية</label><input type="number" name="default_points" class="ap-input" min="0" value="{{ old('default_points', $type->default_points) }}"></div>
                    <div class="ap-field"><label class="ap-label">ترتيب العرض</label><input type="number" name="sort_order" class="ap-input" min="0" value="{{ old('sort_order', $type->sort_order) }}"></div>
                </div>
                <div class="ap-field"><label class="ap-label">الوصف</label><textarea name="description" class="ap-input" rows="3">{{ old('description', $type->description) }}</textarea></div>
                <div class="ap-field">
                    <input type="hidden" name="is_active" value="0">
                    <label class="ap-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active))> مفعل</label>
                </div>
                <div class="d-flex gap-2"><button class="ap-btn ap-btn--primary">حفظ</button><a href="{{ admin_route('admin.finance.point-types.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a></div>
            </form>
        </div>
    </div>
@endsection
