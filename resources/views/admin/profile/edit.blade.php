@extends('layouts.admin')

@section('title', $title ?? 'الملف الشخصي')
@section('page_title', $title ?? 'الملف الشخصي')
@section('page_subtitle', 'تعديل بياناتك الشخصية وكلمة المرور')

@section('page_actions')
    <a href="{{ admin_route('admin.dashboard') }}" class="ap-btn ap-btn--ghost ap-btn--sm">
        @include('admin.partials.icon', ['iconName' => 'home', 'size' => 16]) لوحة التحكم
    </a>
@endsection

@section('content')
    <div class="ap-grid ap-grid--2" style="align-items: start;">
        {{-- ------------------------------------------------ البيانات الشخصية --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'users', 'size' => 18]) البيانات الشخصية</h2>
                    <p class="ap-card__subtitle">الاسم والبريد وبيانات التواصل والصورة</p>
                </div>
            </div>
            <div class="ap-card__body">
                <form method="post" action="{{ admin_route('admin.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('put')

                    <div class="d-flex align-items-center gap-3" style="margin-bottom: 14px;">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="ap-avatar"
                            style="width: 72px; height: 72px;">
                        <div>
                            <div class="fw-bold">{{ $user->name }}</div>
                            <div class="ap-help">{{ $user->roleLabels() }}</div>
                        </div>
                    </div>

                    <div class="ap-field">
                        <label class="ap-label" for="profile-name">الاسم <span>*</span></label>
                        <input type="text" id="profile-name" name="name" value="{{ old('name', $user->name) }}"
                            class="ap-input" required maxlength="150">
                    </div>

                    <div class="ap-field">
                        <label class="ap-label" for="profile-email">البريد الإلكتروني <span>*</span></label>
                        <input type="email" id="profile-email" name="email" value="{{ old('email', $user->email) }}"
                            class="ap-input" required maxlength="190" dir="ltr">
                    </div>

                    <div class="ap-grid ap-grid--2">
                        <div class="ap-field">
                            <label class="ap-label" for="profile-phone">الهاتف</label>
                            <input type="text" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                class="ap-input" maxlength="50" dir="ltr">
                        </div>
                        <div class="ap-field">
                            <label class="ap-label" for="profile-job">المسمى الوظيفي</label>
                            <input type="text" id="profile-job" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                                class="ap-input" maxlength="150">
                        </div>
                    </div>

                    <div class="ap-field">
                        <label class="ap-label" for="profile-locale">اللغة المفضلة</label>
                        <select id="profile-locale" name="preferred_locale" class="ap-select">
                            <option value="ar" @selected(old('preferred_locale', $user->preferred_locale) === 'ar')>العربية</option>
                            <option value="en" @selected(old('preferred_locale', $user->preferred_locale) === 'en')>English</option>
                        </select>
                    </div>

                    <div class="ap-field">
                        <label class="ap-label" for="profile-avatar">الصورة الشخصية</label>
                        <input type="file" id="profile-avatar" name="avatar" class="ap-input" accept="image/jpeg,image/png,image/webp">
                        <div class="ap-help">المقاسات المدعومة: jpg, jpeg, png, webp — بحد أقصى 4 ميجابايت.</div>
                    </div>

                    <button type="submit" class="ap-btn ap-btn--block">
                        @include('admin.partials.icon', ['iconName' => 'check', 'size' => 16]) حفظ البيانات
                    </button>
                </form>
            </div>
        </div>

        {{-- ---------------------------------------------------- كلمة المرور --}}
        <div class="ap-card">
            <div class="ap-card__head">
                <div>
                    <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'shield', 'size' => 18]) كلمة المرور</h2>
                    <p class="ap-card__subtitle">غيّر كلمة المرور الخاصة بحسابك</p>
                </div>
            </div>
            <div class="ap-card__body">
                <form method="post" action="{{ admin_route('admin.profile.password') }}">
                    @csrf
                    @method('put')

                    <div class="ap-field">
                        <label class="ap-label" for="current-password">كلمة المرور الحالية <span>*</span></label>
                        <input type="password" id="current-password" name="current_password" class="ap-input"
                            required autocomplete="current-password">
                    </div>

                    <div class="ap-field">
                        <label class="ap-label" for="new-password">كلمة المرور الجديدة <span>*</span></label>
                        <input type="password" id="new-password" name="password" class="ap-input"
                            required minlength="8" autocomplete="new-password">
                        <div class="ap-help">8 أحرف على الأقل.</div>
                    </div>

                    <div class="ap-field">
                        <label class="ap-label" for="confirm-password">تأكيد كلمة المرور <span>*</span></label>
                        <input type="password" id="confirm-password" name="password_confirmation" class="ap-input"
                            required minlength="8" autocomplete="new-password">
                    </div>

                    <button type="submit" class="ap-btn ap-btn--gold ap-btn--block">
                        @include('admin.partials.icon', ['iconName' => 'shield', 'size' => 16]) تغيير كلمة المرور
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- -------------------------------------------------------- بطاقة القراءة --}}
    <div class="ap-card">
        <div class="ap-card__head">
            <h2 class="ap-card__title">@include('admin.partials.icon', ['iconName' => 'clipboard', 'size' => 18]) معلومات الحساب</h2>
        </div>
        <div class="ap-card__body">
            <div class="ap-grid ap-grid--2">
                <div class="ap-field">
                    <span class="ap-label">الأدوار</span>
                    <div class="d-flex gap-2 flex-wrap">
                        @forelse ($user->roles as $role)
                            <span class="ap-badge ap-badge--gold">{{ user_role_label($role->slug) }}</span>
                        @empty
                            <span class="ap-badge ap-badge--secondary">بدون دور</span>
                        @endforelse
                    </div>
                    <div class="ap-help">{{ $user->roleLabels() }}</div>
                </div>
                <div class="ap-field">
                    <span class="ap-label">تاريخ الانضمام</span>
                    <div class="fw-bold">{{ $user->created_at?->translatedFormat('j F Y') ?? '—' }}</div>
                    <div class="ap-help">
                        آخر دخول:
                        {{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
