@php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
@endphp

<!-- =========================================================================
     DARLI AUTH MODALS (LOGIN & REGISTER POPUPS)
     ========================================================================= -->
<style>
    .darli-auth-modal.modal {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        z-index: 9999999 !important;
        display: none;
        width: 100% !important;
        height: 100% !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        outline: 0 !important;
        background-color: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
    }
    .darli-auth-modal.modal.show {
        display: block !important;
    }
    .darli-auth-modal .modal-dialog {
        position: relative !important;
        width: auto !important;
        margin: 1.75rem auto !important;
        pointer-events: none !important;
        max-width: 500px !important;
        padding: 0 1rem !important;
    }
    .darli-auth-modal .modal-dialog-centered {
        display: flex !important;
        align-items: center !important;
        min-height: calc(100% - 3.5rem) !important;
    }
    .darli-auth-modal .modal-content {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        width: 100% !important;
        pointer-events: auto !important;
        border-radius: 1.25rem;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 20px 50px -10px rgba(15, 23, 42, 0.35);
        overflow: hidden;
        background-color: #ffffff;
        color: #0f172a;
    }
    .darli-modal-backdrop,
    .modal-backdrop.darli-modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999998 !important;
        background-color: rgba(15, 23, 42, 0.65) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
    }
    html[data-theme="dark"] .darli-auth-modal .modal-content {
        background-color: #111e33;
        color: #f8fafc;
        border-color: #1e293b;
        box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.7);
    }
    .darli-auth-header {
        position: relative;
        padding: 2rem 2rem 1.25rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    html[data-theme="dark"] .darli-auth-header {
        border-bottom-color: #1e293b;
    }
    .darli-auth-header .btn-close {
        position: absolute;
        top: 1.5rem;
        {{ $isAr ? 'left: 1.5rem;' : 'right: 1.5rem;' }}
        filter: none;
        opacity: 0.65;
        transition: opacity 0.2s;
    }
    html[data-theme="dark"] .darli-auth-header .btn-close {
        filter: invert(1);
    }
    .darli-auth-icon-badge {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(219, 29, 35, 0.08);
        color: #db1d23;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    html[data-theme="dark"] .darli-auth-icon-badge {
        background: rgba(219, 29, 35, 0.18);
    }
    .darli-auth-title {
        font-size: 1.45rem;
        font-weight: 800;
        margin-bottom: 0.35rem;
        color: inherit;
    }
    .darli-auth-sub {
        font-size: 0.9rem;
        color: #64748b;
        margin-bottom: 0;
    }
    html[data-theme="dark"] .darli-auth-sub {
        color: #94a3b8;
    }
    .darli-auth-body {
        padding: 1.75rem 2rem 2.25rem;
    }
    .darli-auth-group {
        margin-bottom: 1.2rem;
        position: relative;
    }
    .darli-auth-label {
        display: block;
        font-size: 0.88rem;
        font-weight: 600;
        margin-bottom: 0.45rem;
        color: inherit;
    }
    .darli-auth-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #cbd5e1;
        border-radius: 0.75rem;
        background: #ffffff;
        color: #0f172a;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    html[data-theme="dark"] .darli-auth-input {
        background: #0d1627;
        color: #f8fafc;
        border-color: #24344d;
    }
    .darli-auth-input:focus {
        border-color: #db1d23;
        box-shadow: 0 0 0 3px rgba(219, 29, 35, 0.15);
    }
    .darli-auth-btn-submit {
        width: 100%;
        padding: 0.85rem 1.25rem;
        background: #db1d23;
        color: #ffffff;
        border: none;
        border-radius: 0.75rem;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 14px rgba(219, 29, 35, 0.35);
        transition: background-color 0.2s, transform 0.2s;
    }
    .darli-auth-btn-submit:hover {
        background: #b9151b;
        transform: translateY(-1px);
        color: #ffffff;
    }
    .darli-auth-btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    .darli-pwd-toggle {
        position: absolute;
        top: 36px;
        {{ $isAr ? 'left: 12px;' : 'right: 12px;' }}
        cursor: pointer;
        color: #94a3b8;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }
    .darli-pwd-toggle:hover {
        color: #0f172a;
    }
    html[data-theme="dark"] .darli-pwd-toggle:hover {
        color: #f8fafc;
    }
    .darli-auth-divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
        color: #94a3b8;
        font-size: 0.85rem;
    }
    .darli-auth-divider::before, .darli-auth-divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }
    html[data-theme="dark"] .darli-auth-divider::before, html[data-theme="dark"] .darli-auth-divider::after {
        border-bottom-color: #1e293b;
    }
    .darli-auth-divider span {
        padding: 0 1rem;
    }
    .darli-social-btns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .darli-social-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        padding: 0.65rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #ffffff;
        color: #0f172a;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.2s, border-color 0.2s;
    }
    html[data-theme="dark"] .darli-social-btn {
        background: #0d1627;
        color: #f8fafc;
        border-color: #1e293b;
    }
    .darli-social-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }
    html[data-theme="dark"] .darli-social-btn:hover {
        background: #152238;
        border-color: #24344d;
        color: #f8fafc;
    }
    .darli-auth-switch {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 0.9rem;
        color: #64748b;
    }
    html[data-theme="dark"] .darli-auth-switch {
        color: #94a3b8;
    }
    .darli-auth-switch a {
        color: #db1d23;
        font-weight: 700;
        text-decoration: none;
        margin-right: 0.35rem;
    }
    .darli-auth-switch a:hover {
        text-decoration: underline;
    }
</style>

<!-- 1. LOGIN MODAL -->
<div class="modal fade darli-auth-modal" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="modal-content">
            <div class="darli-auth-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="darli-auth-icon-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                </div>
                <h3 class="darli-auth-title" id="modalLoginLabel">{{ $isAr ? 'تسجيل الدخول إلى حسابك' : 'Sign in to your account' }}</h3>
                <p class="darli-auth-sub">{{ $isAr ? 'أهلاً بك مجدداً في منصة دارلي العقارية' : 'Welcome back to Darli Real Estate Platform' }}</p>
            </div>

            <div class="darli-auth-body">
                <form id="darli-auth-login-form" method="POST" action="{{ url($locale . '/login') }}">
                    @csrf
                    <div class="darli-auth-alert alert d-none" role="alert"></div>

                    <!-- Email -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="login-email">{{ $isAr ? 'البريد الإلكتروني' : 'Email Address' }}</label>
                        <input type="email" id="login-email" name="email" class="darli-auth-input" placeholder="{{ $isAr ? 'name@example.com' : 'name@example.com' }}" required autofocus>
                    </div>

                    <!-- Password -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="login-password">{{ $isAr ? 'كلمة المرور' : 'Password' }}</label>
                        <div class="position-relative">
                            <input type="password" id="login-password" name="password" class="darli-auth-input" placeholder="{{ $isAr ? '••••••••' : '••••••••' }}" required>
                            <span class="darli-pwd-toggle" title="{{ $isAr ? 'إظهار / إخفاء كلمة المرور' : 'Toggle Password' }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="d-flex align-items-center justify-content-between mb-3" style="font-size: 0.88rem;">
                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="remember" class="form-check-input mt-0" value="1" checked>
                            <span>{{ $isAr ? 'تذكرني' : 'Remember me' }}</span>
                        </label>
                        <a href="{{ route('password.request', ['locale' => $locale]) }}" style="color: #64748b; text-decoration: none;">
                            {{ $isAr ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}
                        </a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="darli-auth-btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                        <span>{{ $isAr ? 'تسجيل الدخول' : 'Sign In' }}</span>
                    </button>

                    <!-- Social Logins (Google & Facebook ONLY) -->
                    <div class="darli-auth-divider">
                        <span>{{ $isAr ? 'أو تسجيل الدخول بواسطة' : 'Or sign in with' }}</span>
                    </div>

                    <div class="darli-social-btns">
                        <a href="{{ route('social.login', ['locale' => $locale, 'provider' => 'google']) }}" class="darli-social-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.7-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3 0-.8.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z"/><path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2-6.4-4.8L1.9 17c1.8 3.7 5.6 6.5 10.1 6.5z"/></svg>
                            <span>Google</span>
                        </a>
                        <a href="{{ route('social.login', ['locale' => $locale, 'provider' => 'facebook']) }}" class="darli-social-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </a>
                    </div>

                    <!-- Switch to Register -->
                    <div class="darli-auth-switch">
                        <span>{{ $isAr ? 'ليس لديك حساب بعد؟' : "Don't have an account?" }}</span>
                        <a href="#modalRegister" class="darli-switch-to-register">{{ $isAr ? 'تسجيل في الموقع الآن' : 'Register now' }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. REGISTER MODAL -->
<div class="modal fade darli-auth-modal" id="modalRegister" tabindex="-1" aria-labelledby="modalRegisterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content">
            <div class="darli-auth-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="darli-auth-icon-badge" style="background: rgba(200, 164, 92, 0.12); color: #c8a45c;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                </div>
                <h3 class="darli-auth-title" id="modalRegisterLabel">{{ $isAr ? 'إنشاء حساب جديد في دارلي' : 'Register an account on Darli' }}</h3>
                <p class="darli-auth-sub">{{ $isAr ? 'انضم إلينا للاستفادة من كافة خدمات البحث والوساطة العقارية' : 'Join Darli to discover properties and save favorites' }}</p>
            </div>

            <div class="darli-auth-body">
                <form id="darli-auth-register-form" method="POST" action="{{ url($locale . '/register') }}">
                    @csrf
                    <div class="darli-auth-alert alert d-none" role="alert"></div>

                    <!-- Full Name -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="darli-auth-label" for="reg-first-name">{{ $isAr ? 'الاسم الأول' : 'First Name' }} <span class="text-danger">*</span></label>
                            <input type="text" id="reg-first-name" name="first_name" class="darli-auth-input" placeholder="{{ $isAr ? 'محمد' : 'John' }}" required>
                        </div>
                        <div class="col-6">
                            <label class="darli-auth-label" for="reg-last-name">{{ $isAr ? 'اسم العائلة' : 'Last Name' }} <span class="text-danger">*</span></label>
                            <input type="text" id="reg-last-name" name="last_name" class="darli-auth-input" placeholder="{{ $isAr ? 'علي' : 'Doe' }}" required>
                        </div>
                    </div>

                    <!-- Email & Phone -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="darli-auth-label" for="reg-email">{{ $isAr ? 'البريد الإلكتروني' : 'Email Address' }} <span class="text-danger">*</span></label>
                            <input type="email" id="reg-email" name="email" class="darli-auth-input" placeholder="name@example.com" required>
                        </div>
                        <div class="col-5">
                            <label class="darli-auth-label" for="reg-phone">{{ $isAr ? 'رقم الهاتف' : 'Phone' }}</label>
                            <input type="tel" id="reg-phone" name="phone" class="darli-auth-input" placeholder="01001234567">
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="reg-password">{{ $isAr ? 'كلمة المرور' : 'Password' }} <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="reg-password" name="password" class="darli-auth-input" placeholder="{{ $isAr ? '8 أحرف على الأقل' : 'At least 8 characters' }}" required minlength="8">
                            <span class="darli-pwd-toggle" title="{{ $isAr ? 'إظهار / إخفاء كلمة المرور' : 'Toggle Password' }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="reg-password-confirmation">{{ $isAr ? 'تأكيد كلمة المرور' : 'Confirm Password' }} <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="reg-password-confirmation" name="password_confirmation" class="darli-auth-input" placeholder="{{ $isAr ? 'أعد كتابة كلمة المرور' : 'Repeat password' }}" required minlength="8">
                            <span class="darli-pwd-toggle" title="{{ $isAr ? 'إظهار / إخفاء كلمة المرور' : 'Toggle Password' }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="mb-3" style="font-size: 0.88rem;">
                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="agree_terms" class="form-check-input mt-0" value="1" required checked>
                            <span>{{ $isAr ? 'أوافق على الشروط والأحكام وسياسة الخصوصية' : 'I agree to the Terms & Privacy Policy' }}</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="darli-auth-btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        <span>{{ $isAr ? 'إنشاء حساب جديد' : 'Create Account' }}</span>
                    </button>

                    <!-- Social Logins (Google & Facebook ONLY) -->
                    <div class="darli-auth-divider">
                        <span>{{ $isAr ? 'أو التسجيل السريع بواسطة' : 'Or register with' }}</span>
                    </div>

                    <div class="darli-social-btns">
                        <a href="{{ route('social.login', ['locale' => $locale, 'provider' => 'google']) }}" class="darli-social-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.7-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3 0-.8.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15s.7 5.3 1.9 7.7l3.7-2.9z"/><path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2-6.4-4.8L1.9 17c1.8 3.7 5.6 6.5 10.1 6.5z"/></svg>
                            <span>Google</span>
                        </a>
                        <a href="{{ route('social.login', ['locale' => $locale, 'provider' => 'facebook']) }}" class="darli-social-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </a>
                    </div>

                    <!-- Switch to Login -->
                    <div class="darli-auth-switch">
                        <span>{{ $isAr ? 'لديك حساب بالفعل؟' : 'Already have an account?' }}</span>
                        <a href="#modalLogin" class="darli-switch-to-login">{{ $isAr ? 'تسجيل الدخول' : 'Sign in' }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
