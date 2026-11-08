@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'بيانات العميل والاحتياج والعنوان')

@section('content')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">{{ $title }}</h2><a href="{{ admin_route('admin.customer-requests.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
        <div class="ap-card__body">
            @if($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ $customerRequest->exists ? admin_route('admin.customer-requests.update', $customerRequest) : admin_route('admin.customer-requests.store') }}">
                @csrf @if($customerRequest->exists) @method('put') @endif
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field"><label class="ap-label">اسم العميل *</label><input name="customer_name" class="ap-input" value="{{ old('customer_name', $customerRequest->customer_name) }}" required maxlength="120"></div>
                    <div class="ap-field"><label class="ap-label">رقم التليفون *</label><input name="phone" class="ap-input" value="{{ old('phone', $customerRequest->phone) }}" required maxlength="30" dir="ltr"></div>
                    <div class="ap-field"><label class="ap-label">العنوان *</label><select name="location_id" class="ap-select" required><option value="">اختر المدينة/المنطقة</option>@foreach($locations as $loc)<option value="{{ $loc['id'] }}" @selected((string) old('location_id',$customerRequest->location_id)===(string)$loc['id'])>{{ $loc['label'] }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">تفاصيل العنوان</label><input name="address_details" class="ap-input" value="{{ old('address_details', $customerRequest->address_details) }}" maxlength="255"></div>
                    <div class="ap-field"><label class="ap-label">الحالة</label>@if($customerRequest->exists)<select name="status" class="ap-select">@foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected(old('status',$customerRequest->status)===$k)>{{ $v }}</option>@endforeach</select>@else<input class="ap-input" value="جديد" disabled><input type="hidden" name="status" value="new">@endif</div>
                </div>
                <div class="ap-field"><label class="ap-label">الاحتياج *</label><textarea name="need" class="ap-input" rows="5" required maxlength="5000">{{ old('need', $customerRequest->need) }}</textarea></div>
                <div class="ap-field"><label class="ap-label">ملاحظات المتابعة</label><textarea name="notes" class="ap-input" rows="3" maxlength="3000">{{ old('notes', $customerRequest->notes) }}</textarea></div>
                <div class="d-flex gap-2"><button class="ap-btn ap-btn--primary">حفظ</button><a href="{{ admin_route('admin.customer-requests.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a></div>
            </form>
        </div>
    </div>
@endsection
