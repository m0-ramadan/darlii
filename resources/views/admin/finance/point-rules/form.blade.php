@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', 'جدولة منح النقاط تلقائيًا')

@section('content')
    @include('admin.finance._tabs')
    <div class="ap-card">
        <div class="ap-card__head"><h2 class="ap-card__title">{{ $title }}</h2><a href="{{ admin_route('admin.finance.point-rules.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a></div>
        <div class="ap-card__body">
            @if ($errors->any())<div class="ap-alert ap-alert--danger" style="margin-bottom:14px">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ $rule->exists ? admin_route('admin.finance.point-rules.update', $rule) : admin_route('admin.finance.point-rules.store') }}">
                @csrf @if($rule->exists) @method('put') @endif
                <div class="ap-grid ap-grid--2">
                    <div class="ap-field"><label class="ap-label">اسم القاعدة *</label><input name="name" class="ap-input" value="{{ old('name', $rule->name) }}" required></div>
                    <div class="ap-field"><label class="ap-label">نوع النقاط *</label><select name="point_type_id" class="ap-select" required><option value="">اختر</option>@foreach($types as $type)<option value="{{ $type->id }}" @selected((string) old('point_type_id', $rule->point_type_id) === (string) $type->id)>{{ $type->name }} ({{ $type->unit_label }})</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">عدد النقاط *</label><input type="number" name="points" class="ap-input" min="1" value="{{ old('points', $rule->points ?: 50) }}" required></div>
                    <div class="ap-field"><label class="ap-label">التكرار *</label><select name="frequency" class="ap-select">@foreach(['daily'=>'يومي','weekly'=>'أسبوعي','monthly'=>'شهري','yearly'=>'سنوي'] as $k=>$v)<option value="{{ $k }}" @selected(old('frequency',$rule->frequency)===$k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">كل كم مرة؟ *</label><input type="number" name="interval_count" class="ap-input" min="1" max="12" value="{{ old('interval_count', $rule->interval_count ?: 1) }}" required></div>
                    <div class="ap-field"><label class="ap-label">الفئة المستهدفة *</label><select name="target_type" id="targetType" class="ap-select">@foreach(['all'=>'كل المستخدمين (عدا الأدمن)','role'=>'دور محدد','users'=>'مستخدمون محددون'] as $k=>$v)<option value="{{ $k }}" @selected(old('target_type',$rule->target_type)===$k)>{{ $v }}</option>@endforeach</select></div>
                    <div class="ap-field" id="roleField"><label class="ap-label">الدور</label><select name="target_role" class="ap-select"><option value="">اختر</option>@foreach($roles as $role)<option value="{{ $role->slug }}" @selected(old('target_role',$rule->target_role)===$role->slug)>{{ $role->label_ar ?: $role->name }}</option>@endforeach</select></div>
                    <div class="ap-field" id="usersField"><label class="ap-label">المستخدمون</label><select name="user_ids[]" class="ap-select" multiple size="7">@foreach($users as $user)<option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', $rule->users->pluck('id')->all())))>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></div>
                    <div class="ap-field"><label class="ap-label">يبدأ في</label><input type="datetime-local" name="starts_at" class="ap-input" value="{{ old('starts_at', $rule->starts_at?->format('Y-m-d\TH:i')) }}"></div>
                    <div class="ap-field"><label class="ap-label">ينتهي في</label><input type="datetime-local" name="ends_at" class="ap-input" value="{{ old('ends_at', $rule->ends_at?->format('Y-m-d\TH:i')) }}"></div>
                </div>
                <div class="ap-field"><label class="ap-label">الوصف</label><textarea name="description" class="ap-input" rows="2">{{ old('description', $rule->description) }}</textarea></div>
                <div class="ap-field"><input type="hidden" name="is_active" value="0"><label class="ap-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $rule->is_active))> مفعلة</label></div>
                <div class="d-flex gap-2"><button class="ap-btn ap-btn--primary">حفظ</button><a href="{{ admin_route('admin.finance.point-rules.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a></div>
            </form>
        </div>
    </div>
    <script>
        (function () {
            const type = document.getElementById('targetType');
            const role = document.getElementById('roleField');
            const users = document.getElementById('usersField');
            function sync() {
                role.style.display = type.value === 'role' ? '' : 'none';
                users.style.display = type.value === 'users' ? '' : 'none';
            }
            type.addEventListener('change', sync); sync();
        })();
    </script>
@endsection
