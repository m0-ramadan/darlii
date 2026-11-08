@php $isEdit = $customerRequest->exists; @endphp
<div class="cr-card cr-form">
    @if ($errors->any())<div class="cr-alert cr-alert--danger">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ $isEdit ? route('dashboard.customer-requests.update', $customerRequest) : route('dashboard.customer-requests.store') }}">
        @csrf @if($isEdit) @method('put') @endif
        <div class="cr-grid2">
            <div class="cr-field"><label>اسم العميل *</label><input name="customer_name" class="cr-input" value="{{ old('customer_name', $customerRequest->customer_name) }}" required maxlength="120"></div>
            <div class="cr-field"><label>رقم التليفون *</label><input name="phone" class="cr-input" value="{{ old('phone', $customerRequest->phone) }}" required maxlength="30" dir="ltr"></div>
            <div class="cr-field"><label>العنوان *</label>
                <select name="location_id" class="cr-select" required>
                    <option value="">اختر المدينة/المنطقة</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc['id'] }}" @selected((string) old('location_id', $customerRequest->location_id) === (string) $loc['id'])>{{ $loc['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="cr-field"><label>تفاصيل العنوان</label><input name="address_details" class="cr-input" value="{{ old('address_details', $customerRequest->address_details) }}" maxlength="255" placeholder="الشارع/علامة مميزة"></div>
            <div class="cr-field"><label>حالة الطلب</label>
                @if($isEdit)
                    <select name="status" class="cr-select">@foreach($statuses as $k=>$v)<option value="{{ $k }}" @selected(old('status',$customerRequest->status)===$k)>{{ $v }}</option>@endforeach</select>
                @else
                    <input class="cr-input" value="جديد" disabled>
                    <input type="hidden" name="status" value="new">
                @endif
            </div>
        </div>
        <div class="cr-field" style="margin-top:12px"><label>الاحتياج *</label><textarea name="need" class="cr-input" required maxlength="5000" placeholder="وصف احتياج العميل بالتفصيل...">{{ old('need', $customerRequest->need) }}</textarea></div>
        <div class="cr-field" style="margin-top:12px"><label>ملاحظات المتابعة</label><textarea name="notes" class="cr-input" maxlength="3000" style="min-height:80px">{{ old('notes', $customerRequest->notes) }}</textarea></div>
        <div class="cr-actions" style="margin-top:16px">
            <button class="cr-btn cr-btn--gold">{{ $isEdit ? 'حفظ التعديلات' : 'تسجيل الطلب' }}</button>
            <a href="{{ route('dashboard.customer-requests.index') }}" class="cr-btn cr-btn--ghost">إلغاء</a>
            @if($isEdit)<span class="cr-muted">أضيف بواسطة {{ $customerRequest->creator?->name ?? '—' }} في {{ $customerRequest->created_at?->format('Y/m/d H:i') }}</span>@endif
        </div>
    </form>
</div>
