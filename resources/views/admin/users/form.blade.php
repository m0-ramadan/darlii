@extends('layouts.admin')

@php
    $isEdit = $user->exists;
    $selectedRoles = old('roles', $isEdit ? $user->roles->pluck('id')->all() : []);
@endphp

@section('title', $title)
@section('page_title', $title)
@section('page_subtitle', $isEdit ? $user->email : 'أضف أدمن أو مسؤول أو سمسار وحدّد المسؤول المتابع له')

@section('page_actions')
    <a href="{{ admin_route('admin.users.index') }}" class="ap-btn ap-btn--ghost ap-btn--sm">رجوع</a>
@endsection

@section('content')
    <form method="post" enctype="multipart/form-data"
        action="{{ $isEdit ? admin_route('admin.users.update', $user) : admin_route('admin.users.store') }}">
        @csrf
        @if ($isEdit)
            @method('put')
        @endif

        <div class="ap-grid ap-grid--2" style="align-items: start;">
            <div>
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) البيانات الأساسية</h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">الاسم <span>*</span></label>
                                <input type="text" name="name" class="ap-input" required value="{{ old('name', $user->name) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">البريد الإلكتروني <span>*</span></label>
                                <input type="email" name="email" class="ap-input" required dir="ltr" value="{{ old('email', $user->email) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الهاتف</label>
                                <input type="text" name="phone" class="ap-input" dir="ltr" value="{{ old('phone', $user->phone) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">المسمى الوظيفي</label>
                                <input type="text" name="job_title" class="ap-input" value="{{ old('job_title', $user->job_title) }}" placeholder="مثال: مسؤول مبيعات">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">الحالة <span>*</span></label>
                                <select name="status" class="ap-select" required>
                                    @foreach (['active' => 'نشط', 'inactive' => 'غير نشط', 'suspended' => 'موقوف'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $user->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">اللغة المفضلة</label>
                                <select name="preferred_locale" class="ap-select">
                                    <option value="ar" @selected(old('preferred_locale', $user->preferred_locale ?? 'ar') === 'ar')>العربية</option>
                                    <option value="en" @selected(old('preferred_locale', $user->preferred_locale) === 'en')>English</option>
                                </select>
                            </div>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">الصورة الشخصية</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $user->avatar_url }}" alt="" class="ap-avatar" style="width: 54px; height: 54px;">
                                <input type="file" name="avatar" class="ap-input" accept="image/*">
                            </div>
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">ملاحظات داخلية</label>
                            <textarea name="notes" class="ap-textarea" rows="2">{{ old('notes', $user->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- ---------------------------------------------- الصلاحيات --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18]) الدور والصلاحيات</h2>
                    </div>
                    <div class="ap-card__body">
                        <div class="ap-field">
                            <label class="ap-label">الأدوار <span>*</span></label>
                            @foreach ($roles as $role)
                                <label class="ap-check" style="display: flex; margin-bottom: 8px;">
                                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                        @checked(in_array($role->id, $selectedRoles, true))>
                                    <span>
                                        <strong>{{ $role->label_ar ?: $role->name }}</strong>
                                        <span class="ap-badge ap-badge--secondary">مستوى {{ $role->level }}</span>
                                        <div class="ap-help">{{ $role->description }}</div>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="ap-field">
                            <label class="ap-label">المسؤول المتابع (manager)</label>
                            <select name="manager_id" class="ap-select">
                                <option value="">— بدون (مستقل) —</option>
                                @foreach ($managers as $manager)
                                    @continue(isset($user->id) && $manager->id === $user->id)
                                    <option value="{{ $manager->id }}" @selected((string) old('manager_id', $user->manager_id) === (string) $manager->id)>
                                        {{ $manager->name }} — {{ user_role_label($manager->primary_role_slug) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="ap-help">
                                الأدمن يمكن أن يكون تحته مسؤول أو سمسار مباشرة، والمسؤول يكون تحته سماسرة فقط.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                {{-- ------------------------------------------------ كلمة المرور --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18]) كلمة المرور</h2>
                    </div>
                    <div class="ap-card__body">
                        @if (!$isEdit)
                            <div class="ap-alert ap-alert--info" style="margin-bottom: 14px;">
                                <div>كلمة المرور مطلوبة عند الإنشاء. شاركها مع المستخدم واطلب منه تغييرها بعد أول دخول.</div>
                            </div>
                        @endif
                        <div class="ap-field">
                            <label class="ap-label">كلمة المرور {{ $isEdit ? '' : '*' }}</label>
                            <input type="text" name="password" class="ap-input" dir="ltr" autocomplete="new-password"
                                @if (!$isEdit) required @endif
                                placeholder="{{ $isEdit ? 'اتركها فارغة للإبقاء على كلمة المرور الحالية' : 'مثال: Agent@12345' }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">تأكيد كلمة المرور</label>
                            <input type="text" name="password_confirmation" class="ap-input" dir="ltr" autocomplete="new-password">
                        </div>
                    </div>
                </div>

                {{-- --------------------------------------------- ملف السمسار --}}
                <div class="ap-card">
                    <div class="ap-card__head">
                        <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'building', 'size' => 18]) ملف السمسار (اختياري)</h2>
                    </div>
                    <div class="ap-card__body">
                        <p class="text-muted" style="font-size: 12.5px;">يُستخدم هذا الملف في صفحة السماسرة على الموقع العام عند اختيار دور "سمسار".</p>
                        @php $profile = $user->agentProfile; @endphp
                        <div class="ap-field">
                            <label class="ap-label">المسمى الوظيفي العام</label>
                            <input type="text" name="agent_profile[job_title]" class="ap-input"
                                value="{{ old('agent_profile.job_title', $profile?->job_title) }}">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">نبذة تعريفية</label>
                            <textarea name="agent_profile[bio]" class="ap-textarea" rows="3">{{ old('agent_profile.bio', $profile?->bio) }}</textarea>
                        </div>
                        <div class="ap-grid ap-grid--2">
                            <div class="ap-field">
                                <label class="ap-label">واتساب</label>
                                <input type="text" name="agent_profile[whatsapp]" class="ap-input" dir="ltr"
                                    value="{{ old('agent_profile.whatsapp', $profile?->whatsapp) }}">
                            </div>
                            <div class="ap-field">
                                <label class="ap-label">سنوات الخبرة</label>
                                <input type="number" min="0" max="70" name="agent_profile[years_experience]" class="ap-input"
                                    value="{{ old('agent_profile.years_experience', $profile?->years_experience) }}">
                            </div>
                        </div>
                        <div class="ap-field">
                            <label class="ap-label">الموقع الإلكتروني</label>
                            <input type="url" name="agent_profile[website]" class="ap-input" dir="ltr"
                                value="{{ old('agent_profile.website', $profile?->website) }}">
                        </div>
                    </div>
                </div>

                <div class="ap-card">
                    <div class="ap-card__body d-flex gap-2 flex-wrap">
                        <button class="ap-btn ap-btn--gold">
                            @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16])
                            {{ $isEdit ? 'حفظ التعديلات' : 'إنشاء المستخدم' }}
                        </button>
                        <a href="{{ admin_route('admin.users.index') }}" class="ap-btn ap-btn--ghost">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
