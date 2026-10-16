<?php
    $locale = app()->getLocale();
    $isAr = $locale === 'ar';
?>

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
        <?php echo e($isAr ? 'left: 1.5rem;' : 'right: 1.5rem;'); ?>

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
        background: var(--brand-gold-light, #f7f0e3);
        color: var(--brand-gold, #c99a45);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }
    html[data-theme="dark"] .darli-auth-icon-badge {
        background: rgba(214, 174, 100, 0.16);
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
        border-color: var(--brand-gold, #c99a45);
        box-shadow: 0 0 0 3px rgba(201, 154, 69, 0.16);
    }
    .darli-auth-input:-webkit-autofill,
    .darli-auth-input:-webkit-autofill:hover,
    .darli-auth-input:-webkit-autofill:focus {
        -webkit-text-fill-color: var(--text-primary, #0f172a);
        -webkit-box-shadow: 0 0 0 1000px var(--surface, #ffffff) inset;
        caret-color: var(--text-primary, #0f172a);
        transition: background-color 9999s ease-out 0s;
    }
    html[data-theme="dark"] .darli-auth-input:-webkit-autofill,
    html[data-theme="dark"] .darli-auth-input:-webkit-autofill:hover,
    html[data-theme="dark"] .darli-auth-input:-webkit-autofill:focus {
        -webkit-text-fill-color: #f8fafc;
        -webkit-box-shadow: 0 0 0 1000px #0d1627 inset;
        caret-color: #f8fafc;
    }
    .darli-auth-btn-submit {
        width: 100%;
        padding: 0.85rem 1.25rem;
        background: linear-gradient(135deg, var(--brand-navy, #0b1f3a), var(--brand-navy-dark, #071527));
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
        box-shadow: 0 4px 14px rgba(11, 31, 58, 0.28);
        transition: background-color 0.2s, transform 0.2s;
    }
    .darli-auth-btn-submit:hover {
        background: linear-gradient(135deg, var(--brand-gold, #c99a45), var(--brand-gold-hover, #b38636));
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
        <?php echo e($isAr ? 'left: 12px;' : 'right: 12px;'); ?>

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
        color: var(--brand-gold-hover, #b38636);
        font-weight: 700;
        text-decoration: none;
        margin-right: 0.35rem;
    }
    .darli-auth-switch a:hover {
        text-decoration: underline;
        color: var(--brand-navy, #0b1f3a);
    }
    .darli-auth-modal .form-check-input:checked {
        background-color: var(--brand-gold, #c99a45);
        border-color: var(--brand-gold, #c99a45);
    }
    .darli-auth-modal .form-check-input:focus {
        border-color: var(--brand-gold, #c99a45);
        box-shadow: 0 0 0 3px rgba(201, 154, 69, 0.16);
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
                <h3 class="darli-auth-title" id="modalLoginLabel"><?php echo e($isAr ? 'تسجيل الدخول إلى حسابك' : 'Sign in to your account'); ?></h3>
                <p class="darli-auth-sub"><?php echo e($isAr ? 'أهلاً بك مجدداً في منصة دارلي العقارية' : 'Welcome back to Darli Real Estate Platform'); ?></p>
            </div>

            <div class="darli-auth-body">
                <form id="darli-auth-login-form" method="POST" action="<?php echo e(route('login')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="darli-auth-alert alert d-none" role="alert"></div>

                    <!-- Email or Phone -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="login-identifier"><?php echo e($isAr ? 'البريد الإلكتروني أو رقم الهاتف' : 'Email or Phone Number'); ?></label>
                        <input type="text" id="login-identifier" name="login" class="darli-auth-input" placeholder="<?php echo e($isAr ? '010xxxxxxxx أو name@example.com' : '010xxxxxxxx or name@example.com'); ?>" required autofocus>
                    </div>

                    <!-- Password -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="login-password"><?php echo e($isAr ? 'كلمة المرور' : 'Password'); ?></label>
                        <div class="position-relative">
                            <input type="password" id="login-password" name="password" class="darli-auth-input" placeholder="<?php echo e($isAr ? '••••••••' : '••••••••'); ?>" required>
                            <span class="darli-pwd-toggle" title="<?php echo e($isAr ? 'إظهار / إخفاء كلمة المرور' : 'Toggle Password'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="d-flex align-items-center justify-content-between mb-3" style="font-size: 0.88rem;">
                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="remember" class="form-check-input mt-0" value="1" checked>
                            <span><?php echo e($isAr ? 'تذكرني' : 'Remember me'); ?></span>
                        </label>
                        <a href="<?php echo e(route('password.request', ['locale' => $locale])); ?>" style="color: #64748b; text-decoration: none;">
                            <?php echo e($isAr ? 'نسيت كلمة المرور؟' : 'Forgot password?'); ?>

                        </a>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="darli-auth-btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                        <span><?php echo e($isAr ? 'تسجيل الدخول' : 'Sign In'); ?></span>
                    </button>

                    

                    <!-- Switch to Register -->
                    <div class="darli-auth-switch">
                        <span><?php echo e($isAr ? 'ليس لديك حساب بعد؟' : "Don't have an account?"); ?></span>
                        <a href="#modalRegister" class="darli-switch-to-register"><?php echo e($isAr ? 'إنشاء حساب جديد' : 'Register now'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. REGISTER MODAL -->
<div class="modal fade darli-auth-modal" id="modalRegister" tabindex="-1" aria-labelledby="modalRegisterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content">
            <div class="darli-auth-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="darli-auth-icon-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                </div>
                <h3 class="darli-auth-title" id="modalRegisterLabel"><?php echo e($isAr ? 'إنشاء حساب جديد في دارلي' : 'Create an Account on Darli'); ?></h3>
                <p class="darli-auth-sub"><?php echo e($isAr ? 'اختر نوع الحساب وأدخل بياناتك للانضمام إلى منصة دارلي' : 'Select account type and enter details to join Darli'); ?></p>
            </div>

            <div class="darli-auth-body">
                <form id="darli-auth-register-form" method="POST" action="<?php echo e(route('register')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="account_type" id="darli-reg-account-type" value="client">

                    <div class="darli-auth-alert alert d-none" role="alert"></div>

                    <!-- ROLE SELECTION CARDS (عميل / شركة / سمسار) -->
                    <div class="mb-3">
                        <label class="darli-auth-label mb-2"><?php echo e($isAr ? 'أنت تسجل بصفتك:' : 'You are registering as:'); ?></label>
                        <div class="darli-role-selector d-flex gap-2">
                            <!-- Option 1: Client -->
                            <div class="darli-role-card flex-fill active text-center p-2 rounded-3 border" data-role="client" style="cursor: pointer; transition: all 0.2s;">
                                <div class="darli-role-icon mb-1">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <span class="darli-role-title fw-bold d-block" style="font-size: 0.9rem;"><?php echo e($isAr ? 'عميل' : 'Customer'); ?></span>
                                <span class="darli-role-sub text-muted" style="font-size: 0.72rem;"><?php echo e($isAr ? 'باحث عن عقار' : 'Property seeker'); ?></span>
                            </div>

                            <!-- Option 2: Company -->
                            <div class="darli-role-card flex-fill text-center p-2 rounded-3 border" data-role="company" style="cursor: pointer; transition: all 0.2s;">
                                <div class="darli-role-icon mb-1">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="12" y1="18" x2="12" y2="18.01"/><line x1="12" y1="14" x2="12" y2="14.01"/><line x1="12" y1="10" x2="12" y2="10.01"/><line x1="12" y1="6" x2="12" y2="6.01"/><line x1="16" y1="18" x2="16" y2="18.01"/><line x1="16" y1="14" x2="16" y2="14.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="16" y1="6" x2="16" y2="6.01"/><line x1="8" y1="18" x2="8" y2="18.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="8" y1="6" x2="8" y2="6.01"/></svg>
                                </div>
                                <span class="darli-role-title fw-bold d-block" style="font-size: 0.9rem;"><?php echo e($isAr ? 'شركة' : 'Company'); ?></span>
                                <span class="darli-role-sub text-muted" style="font-size: 0.72rem;"><?php echo e($isAr ? 'تطوير وتسويق' : 'Real estate agency'); ?></span>
                            </div>

                            <!-- Option 3: Broker -->
                            <div class="darli-role-card flex-fill text-center p-2 rounded-3 border" data-role="broker" style="cursor: pointer; transition: all 0.2s;">
                                <div class="darli-role-icon mb-1">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </div>
                                <span class="darli-role-title fw-bold d-block" style="font-size: 0.9rem;"><?php echo e($isAr ? 'سمسار' : 'Broker / Agent'); ?></span>
                                <span class="darli-role-sub text-muted" style="font-size: 0.72rem;"><?php echo e($isAr ? 'وسيط عقاري حر' : 'Independent agent'); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- PENDING APPROVAL NOTICE (FOR COMPANY & BROKER) -->
                    <div id="darli-role-notice" class="p-2 px-3 mb-3 d-none" style="background: #FEF3C7; border: 1px solid #FDE68A; border-radius: 8px; font-size: 0.82rem; color: #92400E;">
                        ⏳ <strong><?php echo e($isAr ? 'ملاحظة هامة:' : 'Important Notice:'); ?></strong>
                        <span id="darli-role-notice-text">
                            <?php echo e($isAr ? 'حسابات الشركات والوسطاء تخضع للمراجعة والاعتماد من قبل إدارة دارلي قبل إتاحة إضافة ونشر العقارات والمشاريع.' : 'Partner accounts require review and activation by Darli administration before listing properties or projects.'); ?>

                        </span>
                    </div>

                    <!-- FULL NAME / CONTACT NAME -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="reg-name">
                            <span id="darli-label-name"><?php echo e($isAr ? 'الاسم بالكامل' : 'Full Name'); ?></span> <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="reg-name" name="name" class="darli-auth-input" placeholder="<?php echo e($isAr ? 'محمد أحمد علي' : 'Full Name'); ?>" required>
                    </div>

                    <!-- PHONE NUMBER (MANDATORY FOR ALL) -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="reg-phone">
                            <span id="darli-label-phone"><?php echo e($isAr ? 'رقم الهاتف' : 'Phone Number'); ?></span> <span class="text-danger">*</span>
                        </label>
                        <input type="tel" id="reg-phone" name="phone" class="darli-auth-input" placeholder="01012345678" required dir="ltr" inputmode="numeric" autocomplete="tel" maxlength="14" pattern="(?:01[0125][0-9]{8}|(?:\+20|0020)1[0125][0-9]{8})" title="<?php echo e($isAr ? 'أدخل رقم موبايل مصري صحيح، مثل 01012345678' : 'Enter a valid Egyptian mobile number, such as 01012345678'); ?>">
                    </div>

                    <!-- COMPANY SPECIFIC FIELDS (Hidden for client & broker) -->
                    <div id="darli-company-fields" class="d-none">
                        <!-- Company Name -->
                        <div class="darli-auth-group">
                            <label class="darli-auth-label" for="reg-company-name">
                                <?php echo e($isAr ? 'اسم الشركة / الوكالة العقارية' : 'Company / Agency Name'); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="reg-company-name" name="company_name" class="darli-auth-input" placeholder="<?php echo e($isAr ? 'شركة النيل للتطوير العقاري' : 'Al-Nile Real Estate Development'); ?>">
                        </div>

                        <!-- Company Official Email -->
                        <div class="darli-auth-group">
                            <label class="darli-auth-label" for="reg-company-email">
                                <?php echo e($isAr ? 'البريد الإلكتروني الرسمي للشركة' : 'Company Official Email'); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="reg-company-email" name="company_email" class="darli-auth-input" placeholder="info@company.com" dir="ltr">
                        </div>
                    </div>

                    <!-- BROKER SPECIFIC FIELDS (National ID Upload) -->
                    <div id="darli-broker-fields" class="d-none">
                        <div class="darli-auth-group">
                            <label class="darli-auth-label" for="reg-national-id">
                                <?php echo e($isAr ? 'صورة بطاقة الرقم القومي' : 'National ID Card Photo'); ?> <span class="text-danger">*</span>
                            </label>
                            <input type="file" id="reg-national-id" name="national_id_image" class="darli-auth-input" accept="image/*,.pdf" style="padding: 6px 10px;">
                            <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                                <?php echo e($isAr ? 'يرجى إرفاق صورة واضحة لبطاقة الرقم القومي (وجه أو وجهين) لتوثيق واعتماد الحساب من إدارة المنصة.' : 'Please upload a clear copy of your National ID for verification.'); ?>

                            </small>
                        </div>
                    </div>

                    <!-- OPTIONAL EMAIL FOR CLIENT (Hidden by default since user asked name + phone only) -->
                    <input type="hidden" name="email" id="reg-email" value="">

                    <!-- PASSWORD -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="reg-password"><?php echo e($isAr ? 'كلمة المرور' : 'Password'); ?> <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="reg-password" name="password" class="darli-auth-input" placeholder="<?php echo e($isAr ? '8 أحرف على الأقل' : 'At least 8 characters'); ?>" required minlength="8">
                            <span class="darli-pwd-toggle" title="<?php echo e($isAr ? 'إظهار / إخفاء كلمة المرور' : 'Toggle Password'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- CONFIRM PASSWORD -->
                    <div class="darli-auth-group">
                        <label class="darli-auth-label" for="reg-password-confirmation"><?php echo e($isAr ? 'تأكيد كلمة المرور' : 'Confirm Password'); ?> <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" id="reg-password-confirmation" name="password_confirmation" class="darli-auth-input" placeholder="<?php echo e($isAr ? 'أعد كتابة كلمة المرور' : 'Repeat password'); ?>" required minlength="8">
                            <span class="darli-pwd-toggle" title="<?php echo e($isAr ? 'إظهار / إخفاء كلمة المرور' : 'Toggle Password'); ?>">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </span>
                        </div>
                    </div>

                    <!-- TERMS CHECKBOX -->
                    <div class="mb-3" style="font-size: 0.88rem;">
                        <label class="d-flex align-items-center gap-2 mb-0" style="cursor: pointer;">
                            <input type="checkbox" name="agree_terms" class="form-check-input mt-0" value="1" required checked>
                            <span>
                                <?php if($isAr): ?>
                                    أوافق على <a href="<?php echo e(route('terms.index', ['locale' => $locale])); ?>" target="_blank" onclick="event.stopPropagation();" class="text-decoration-underline fw-semibold" style="color: var(--primary, #db1d23) !important;">الشروط والأحكام</a> و<a href="<?php echo e(route('privacy-policy.index', ['locale' => $locale])); ?>" target="_blank" onclick="event.stopPropagation();" class="text-decoration-underline fw-semibold" style="color: var(--primary, #db1d23) !important;">سياسة الخصوصية</a>
                                <?php else: ?>
                                    I agree to the <a href="<?php echo e(route('terms.index', ['locale' => $locale])); ?>" target="_blank" onclick="event.stopPropagation();" class="text-decoration-underline fw-semibold" style="color: var(--primary, #db1d23) !important;">Terms & Conditions</a> and <a href="<?php echo e(route('privacy-policy.index', ['locale' => $locale])); ?>" target="_blank" onclick="event.stopPropagation();" class="text-decoration-underline fw-semibold" style="color: var(--primary, #db1d23) !important;">Privacy Policy</a>
                                <?php endif; ?>
                            </span>
                        </label>
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit" class="darli-auth-btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                        <span id="darli-reg-btn-text"><?php echo e($isAr ? 'إنشاء حساب جديد' : 'Create Account'); ?></span>
                    </button>

                    

                    <!-- SWITCH TO LOGIN -->
                    <div class="darli-auth-switch">
                        <span><?php echo e($isAr ? 'لديك حساب بالفعل؟' : 'Already have an account?'); ?></span>
                        <a href="#modalLogin" class="darli-switch-to-login"><?php echo e($isAr ? 'تسجيل الدخول' : 'Sign in'); ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /var/www/html/resources/views/partials/auth-modals.blade.php ENDPATH**/ ?>