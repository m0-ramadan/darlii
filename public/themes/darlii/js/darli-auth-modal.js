/**
 * Darli Real Estate - Global Mobile Navigation & Auth Modals (Login / Register Popups)
 * Handles:
 * 1. Mobile menu toggle (hamburger button & drawer navigation)
 * 2. Login & Register popup modals with in-page switching
 * 3. AJAX Authentication submission with instant feedback
 */

(function () {
    'use strict';

    function getLocale() {
        var match = window.location.pathname.match(/^\/(ar|en)\b/);
        return match ? match[1] : (document.documentElement.lang || 'ar');
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && meta.getAttribute('content')) {
            return meta.getAttribute('content');
        }
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    // =========================================================================
    // 1. MOBILE MENU TOGGLING (HAMBURGER BUTTON)
    // =========================================================================
    function initMobileMenu() {
        document.addEventListener('click', function (e) {
            // Hamburger button click
            var toggler = e.target.closest('.mobile-nav-toggler, .mobile-button, [data-action="toggle-mobile-menu"], .btn-menu-mobile');
            if (toggler) {
                e.preventDefault();
                e.stopPropagation();
                document.body.classList.toggle('mobile-menu-visible');
                return;
            }

            // Close triggers: Backdrop or Close buttons
            var closeTrigger = e.target.closest('.menu-backdrop, .close-btn, .btn-close, [data-action="close-mobile-menu"]');
            if (closeTrigger) {
                e.preventDefault();
                document.body.classList.remove('mobile-menu-visible');
                return;
            }

            // Clicking login or register inside mobile drawer should close drawer and open modal
            var modalLink = e.target.closest('.mobile-menu [data-bs-target="#modalLogin"], .mobile-menu [data-bs-target="#modalRegister"], .mobile-menu a[href*="#modalLogin"], .mobile-menu a[href*="#modalRegister"]');
            if (modalLink) {
                document.body.classList.remove('mobile-menu-visible');
            }

            // Submenu accordion dropdowns inside mobile menu
            var dropdownToggle = e.target.closest('.mobile-menu .navigation li.dropdown2 > a, .mobile-menu .navigation li.menu-item-has-children > a');
            if (dropdownToggle) {
                var parentLi = dropdownToggle.closest('li');
                var submenu = parentLi.querySelector('ul');
                if (submenu) {
                    e.preventDefault();
                    parentLi.classList.toggle('open');
                    if (parentLi.classList.contains('open')) {
                        submenu.style.display = 'block';
                    } else {
                        submenu.style.display = 'none';
                    }
                }
            }
        });
    }

    // =========================================================================
    // 2. AUTH MODALS CONTROLS (POPUP DIALOGS)
    // =========================================================================
    function showModal(modalSelector) {
        var modal = document.querySelector(modalSelector);
        if (!modal) return;

        // Ensure mobile drawer is closed
        document.body.classList.remove('mobile-menu-visible');

        // Close any other open modal first
        closeAllModals();

        if (window.bootstrap && window.bootstrap.Modal) {
            var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            bsModal.show();
        } else {
            // CSS fallback
            modal.classList.add('show');
            modal.style.display = 'block';
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            document.body.classList.add('modal-open');

            var backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show darli-modal-backdrop';
            document.body.appendChild(backdrop);
        }

        // Focus first relevant input
        setTimeout(function () {
            var firstInput = modal.querySelector('input:not([type="hidden"])');
            if (firstInput) firstInput.focus();
        }, 150);
    }

    function closeAllModals() {
        document.querySelectorAll('#modalLogin, #modalRegister').forEach(function (m) {
            if (window.bootstrap && window.bootstrap.Modal) {
                var inst = bootstrap.Modal.getInstance(m);
                if (inst) inst.hide();
            }
            m.classList.remove('show');
            m.style.display = 'none';
            m.setAttribute('aria-hidden', 'true');
        });

        document.body.classList.remove('modal-open');
        document.querySelectorAll('.darli-modal-backdrop').forEach(function (b) {
            b.remove();
        });
    }

    function initAuthModals() {
        document.addEventListener('click', function (e) {
            // Close buttons inside modal
            if (e.target.closest('#modalLogin .btn-close, #modalRegister .btn-close, [data-bs-dismiss="modal"]')) {
                e.preventDefault();
                closeAllModals();
                return;
            }

            // Click on backdrop outside modal content
            if (e.target.matches('#modalLogin, #modalRegister, .darli-modal-backdrop')) {
                closeAllModals();
                return;
            }

            // Switch from Login to Register
            if (e.target.closest('.darli-switch-to-register, [data-switch="register"]')) {
                e.preventDefault();
                showModal('#modalRegister');
                return;
            }

            // Switch from Register to Login
            if (e.target.closest('.darli-switch-to-login, [data-switch="login"]')) {
                e.preventDefault();
                showModal('#modalLogin');
                return;
            }

            // Trigger for Login Modal
            var loginTrigger = e.target.closest('[data-bs-target="#modalLogin"], a[href*="#modalLogin"], .darli-open-login, a[href$="/login"], a[href$="/login/"], a[href*="/login?"]');
            if (loginTrigger && !loginTrigger.closest('#modalLogin') && !loginTrigger.closest('#modalRegister')) {
                var href = loginTrigger.getAttribute('href') || '';
                if (href.indexOf('admin') === -1 && href.indexOf('logout') === -1) {
                    e.preventDefault();
                    showModal('#modalLogin');
                    return;
                }
            }

            // Trigger for Register Modal
            var registerTrigger = e.target.closest('[data-bs-target="#modalRegister"], a[href*="#modalRegister"], .darli-open-register, a[href$="/register"], a[href$="/register/"], a[href*="/register?"]');
            if (registerTrigger && !registerTrigger.closest('#modalLogin') && !registerTrigger.closest('#modalRegister')) {
                var hrefReg = registerTrigger.getAttribute('href') || '';
                if (hrefReg.indexOf('admin') === -1 && hrefReg.indexOf('logout') === -1) {
                    e.preventDefault();
                    showModal('#modalRegister');
                    return;
                }
            }

            // Password Toggle Visibility
            var pwdToggle = e.target.closest('.darli-pwd-toggle, [data-bb-toggle-password]');
            if (pwdToggle) {
                var inputGroup = pwdToggle.closest('.position-relative') || pwdToggle.parentElement;
                var pwdInput = inputGroup.querySelector('input[type="password"], input[type="text"]');
                if (pwdInput) {
                    if (pwdInput.type === 'password') {
                        pwdInput.type = 'text';
                        pwdToggle.classList.add('is-visible');
                    } else {
                        pwdInput.type = 'password';
                        pwdToggle.classList.remove('is-visible');
                    }
                }
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllModals();
                document.body.classList.remove('mobile-menu-visible');
            }
        });

        if (window.location.hash === '#modalLogin' || window.location.hash === '#modalRegister') {
            showModal(window.location.hash);
        }
    }

    // =========================================================================
    // 3. AJAX AUTHENTICATION FORMS (LOGIN & REGISTER)
    // =========================================================================
    function initAjaxAuth() {
        var locale = getLocale();
        var isAr = locale === 'ar';

        // ------------------------- LOGIN FORM -------------------------
        var loginForm = document.getElementById('darli-auth-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var form = this;
                var alertBox = form.querySelector('.darli-auth-alert');
                var submitBtn = form.querySelector('button[type="submit"]');

                if (alertBox) {
                    alertBox.style.display = 'none';
                    alertBox.className = 'darli-auth-alert alert d-none';
                    alertBox.innerHTML = '';
                }

                var loginInput = (form.querySelector('input[name="login"]') || form.querySelector('input[name="email"]') || {}).value || '';
                var password = (form.querySelector('input[name="password"]') || {}).value || '';
                var remember = (form.querySelector('input[name="remember"]') || {}).checked ? 1 : 0;

                if (!loginInput || !password) {
                    showError(alertBox, isAr ? 'يرجى إدخال البريد الإلكتروني أو رقم الهاتف وكلمة المرور.' : 'Please enter email/phone and password.');
                    return;
                }

                setLoading(submitBtn, true, isAr ? 'جاري التحقق...' : 'Signing in...');

                var loginUrl = form.getAttribute('action') || '/login';
                fetch(loginUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        login: loginInput,
                        email: loginInput,
                        password: password,
                        remember: remember
                    })
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { status: res.status, data: data };
                        });
                    })
                    .then(function (res) {
                        setLoading(submitBtn, false, isAr ? 'تسجيل الدخول' : 'Sign In');
                        if (res.status === 200 && res.data.success) {
                            showSuccess(alertBox, isAr ? 'تم تسجيل الدخول بنجاح! جاري التحويل...' : 'Logged in successfully! Redirecting...');
                            setTimeout(function () {
                                if (res.data.redirect) {
                                    window.location.href = res.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            }, 500);
                        } else {
                            var msg = (res.data && res.data.errors && (res.data.errors.login || res.data.errors.email))
                                ? (res.data.errors.login ? res.data.errors.login[0] : res.data.errors.email[0])
                                : ((res.data && res.data.message) || (isAr ? 'بيانات تسجيل الدخول غير صحيحة.' : 'Invalid credentials.'));

                            if (res.status >= 500 || /call_user_func|SQLSTATE|PDOException|Exception|ErrorException|TypeError|Argument #/i.test(msg)) {
                                msg = isAr
                                    ? 'حدث خطأ غير متوقع أثناء تسجيل الدخول. يرجى المحاولة مرة أخرى.'
                                    : 'An unexpected error occurred. Please try again.';
                            }

                            showError(alertBox, msg);
                        }
                    })
                    .catch(function () {
                        setLoading(submitBtn, false, isAr ? 'تسجيل الدخول' : 'Sign In');
                        showError(alertBox, isAr ? 'حدث خطأ أثناء الاتصال بالخادم، يرجى المحاولة لاحقاً.' : 'Network error. Please try again.');
                    });
            });
        }

        // ------------------------ ROLE TABS SWITCHING ------------------------
        var regForm = document.getElementById('darli-auth-register-form');
        if (regForm) {
            var roleCards = regForm.querySelectorAll('.darli-role-card');
            var accountTypeInput = document.getElementById('darli-reg-account-type');
            var companyFields = document.getElementById('darli-company-fields');
            var brokerFields = document.getElementById('darli-broker-fields');
            var roleNotice = document.getElementById('darli-role-notice');
            var roleNoticeText = document.getElementById('darli-role-notice-text');
            var socialSection = document.getElementById('darli-reg-social-section');
            var labelName = document.getElementById('darli-label-name');
            var labelPhone = document.getElementById('darli-label-phone');
            var submitBtnText = document.getElementById('darli-reg-btn-text');

            var companyNameInput = document.getElementById('reg-company-name');
            var companyEmailInput = document.getElementById('reg-company-email');
            var nationalIdInput = document.getElementById('reg-national-id');

            function applyRoleState(role) {
                if (accountTypeInput) accountTypeInput.value = role;

                roleCards.forEach(function (card) {
                    if (card.getAttribute('data-role') === role) {
                        card.classList.add('active');
                        card.style.borderColor = 'var(--brand-gold, #c99a45)';
                        card.style.background = 'var(--brand-gold-light, #f7f0e3)';
                    } else {
                        card.classList.remove('active');
                        card.style.borderColor = '#e2e8f0';
                        card.style.background = '';
                    }
                });

                if (role === 'company') {
                    if (companyFields) companyFields.classList.remove('d-none');
                    if (brokerFields) brokerFields.classList.add('d-none');
                    if (roleNotice) roleNotice.classList.remove('d-none');
                    if (roleNoticeText) {
                        roleNoticeText.textContent = isAr
                            ? 'حسابات الشركات تخضع للمراجعة والاعتماد السريع من إدارة المنصة قبل تفعيل إضافة العقارات والمشاريع.'
                            : 'Company accounts are subject to admin review before listing properties or projects.';
                    }
                    if (socialSection) socialSection.classList.add('d-none'); // دا للعميل بس!
                    if (labelName) labelName.textContent = isAr ? 'اسم مسؤول التواصل' : 'Contact Person Name';
                    if (labelPhone) labelPhone.textContent = isAr ? 'رقم هاتف المسؤول' : 'Contact Phone';
                    if (submitBtnText) submitBtnText.textContent = isAr ? 'تسجيل حساب شركة جديدة' : 'Register Company Account';

                    if (companyNameInput) companyNameInput.required = true;
                    if (companyEmailInput) companyEmailInput.required = true;
                    if (nationalIdInput) nationalIdInput.required = false;

                } else if (role === 'broker') {
                    if (companyFields) companyFields.classList.add('d-none');
                    if (brokerFields) brokerFields.classList.remove('d-none');
                    if (roleNotice) roleNotice.classList.remove('d-none');
                    if (roleNoticeText) {
                        roleNoticeText.textContent = isAr
                            ? 'حسابات السماسرة تخضع لمراجعة بطاقة الرقم القومي واعتماد إدارة دارلي قبل إتاحة نشر العقارات.'
                            : 'Broker accounts require National ID verification by Darli administration before listing properties.';
                    }
                    if (socialSection) socialSection.classList.add('d-none'); // دا للعميل بس!
                    if (labelName) labelName.textContent = isAr ? 'الاسم بالكامل كما في بطاقة الرقم القومي' : 'Full Name (as in National ID)';
                    if (labelPhone) labelPhone.textContent = isAr ? 'رقم الهاتف' : 'Phone Number';
                    if (submitBtnText) submitBtnText.textContent = isAr ? 'تسجيل حساب سمسار' : 'Register Broker Account';

                    if (companyNameInput) companyNameInput.required = false;
                    if (companyEmailInput) companyEmailInput.required = false;
                    if (nationalIdInput) nationalIdInput.required = true;

                } else {
                    // Client / Customer (Default)
                    if (companyFields) companyFields.classList.add('d-none');
                    if (brokerFields) brokerFields.classList.remove('d-none', false);
                    if (brokerFields) brokerFields.classList.add('d-none');
                    if (roleNotice) roleNotice.classList.add('d-none');
                    if (socialSection) socialSection.classList.remove('d-none'); // متاح للعميل
                    if (labelName) labelName.textContent = isAr ? 'الاسم بالكامل' : 'Full Name';
                    if (labelPhone) labelPhone.textContent = isAr ? 'رقم الهاتف' : 'Phone Number';
                    if (submitBtnText) submitBtnText.textContent = isAr ? 'إنشاء حساب عميل جديد' : 'Create Customer Account';

                    if (companyNameInput) companyNameInput.required = false;
                    if (companyEmailInput) companyEmailInput.required = false;
                    if (nationalIdInput) nationalIdInput.required = false;
                }
            }

            roleCards.forEach(function (card) {
                card.addEventListener('click', function () {
                    var role = this.getAttribute('data-role');
                    applyRoleState(role);
                });
            });

            // Initialize default state
            applyRoleState('client');

            // ------------------------ REGISTER FORM SUBMIT ------------------------
            regForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var form = this;
                var alertBox = form.querySelector('.darli-auth-alert');
                var submitBtn = form.querySelector('button[type="submit"]');

                if (alertBox) {
                    alertBox.style.display = 'none';
                    alertBox.className = 'darli-auth-alert alert d-none';
                    alertBox.innerHTML = '';
                }

                var role = (accountTypeInput ? accountTypeInput.value : 'client') || 'client';
                var name = (form.querySelector('input[name="name"]') || {}).value || '';
                var phone = (form.querySelector('input[name="phone"]') || {}).value || '';
                var password = (form.querySelector('input[name="password"]') || {}).value || '';
                var passwordConfirmation = (form.querySelector('input[name="password_confirmation"]') || {}).value || '';

                if (name.trim().split(/\s+/).length < 2) {
                    showError(alertBox, isAr ? 'يرجى إدخال الاسم ثنائيًا على الأقل.' : 'Please enter at least a first and last name.');
                    return;
                }

                if (role === 'broker') {
                    var fileInput = form.querySelector('input[name="national_id_image"]');
                    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
                        showError(alertBox, isAr ? 'يرجى إرفاق صورة بطاقة الرقم القومي لاعتماد الحساب.' : 'Please upload your National ID card photo.');
                        return;
                    }
                }

                if (role === 'company') {
                    var cName = (companyNameInput || {}).value || '';
                    var cEmail = (companyEmailInput || {}).value || '';
                    if (!cName.trim() || !cEmail.trim()) {
                        showError(alertBox, isAr ? 'يرجى إدخال اسم الشركة وبريدها الرسمي.' : 'Please enter company name and official email.');
                        return;
                    }
                }

                if (!phone.trim()) {
                    showError(alertBox, isAr ? 'يرجى إدخال رقم الهاتف.' : 'Please enter phone number.');
                    return;
                }

                var egyptianPhone = phone.replace(/\D/g, '').replace(/^0020/, '').replace(/^20/, '');
                if (egyptianPhone.length === 10 && egyptianPhone.charAt(0) === '1') {
                    egyptianPhone = '0' + egyptianPhone;
                }
                if (!/^01[0125][0-9]{8}$/.test(egyptianPhone)) {
                    showError(alertBox, isAr ? 'يرجى إدخال رقم موبايل مصري صحيح مكوّن من 11 رقمًا، مثل 01012345678.' : 'Enter a valid Egyptian mobile number, such as 01012345678.');
                    return;
                }

                if (password !== passwordConfirmation) {
                    showError(alertBox, isAr ? 'كلمات المرور غير متطابقة.' : 'Passwords do not match.');
                    return;
                }

                if (password.length < 8) {
                    showError(alertBox, isAr ? 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.' : 'Password must be at least 8 characters.');
                    return;
                }

                var defaultBtnText = submitBtnText ? submitBtnText.textContent : (isAr ? 'إنشاء حساب جديد' : 'Create Account');
                setLoading(submitBtn, true, isAr ? 'جاري إنشاء الحساب...' : 'Creating account...');

                var formData = new FormData(form);

                var registerUrl = form.getAttribute('action') || '/register';
                fetch(registerUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: formData
                })
                    .then(function (res) {
                        return res.json().then(function (data) {
                            return { status: res.status, data: data };
                        });
                    })
                    .then(function (res) {
                        setLoading(submitBtn, false, defaultBtnText);
                        if (res.status === 200 && res.data.success) {
                            showSuccess(alertBox, res.data.message || (isAr ? 'تم إنشاء الحساب وتسجيل الدخول بنجاح! جاري التحويل...' : 'Account created successfully! Redirecting...'));
                            setTimeout(function () {
                                if (res.data.redirect) {
                                    window.location.href = res.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            }, 700);
                        } else {
                            var errors = res.data ? (res.data.errors || {}) : {};
                            var errorMsg = '';

                            // 1. Check specific validation field errors
                            if (errors.company_name && errors.company_name.length) {
                                errorMsg = errors.company_name[0];
                            } else if (errors.company_email && errors.company_email.length) {
                                errorMsg = errors.company_email[0];
                            } else if (errors.national_id_image && errors.national_id_image.length) {
                                errorMsg = errors.national_id_image[0];
                            } else if (errors.phone && errors.phone.length) {
                                errorMsg = errors.phone[0];
                            } else if (errors.email && errors.email.length) {
                                errorMsg = errors.email[0];
                            } else if (errors.password && errors.password.length) {
                                errorMsg = errors.password[0];
                            } else if (errors.name && errors.name.length) {
                                errorMsg = errors.name[0];
                            } else {
                                var errorKeys = Object.keys(errors);
                                if (errorKeys.length > 0 && errors[errorKeys[0]] && errors[errorKeys[0]][0]) {
                                    errorMsg = errors[errorKeys[0]][0];
                                }
                            }

                            // 2. Fallback to general message if no field error
                            if (!errorMsg && res.data && res.data.message) {
                                errorMsg = res.data.message;
                            }

                            // 3. Technical error detection & sanitization:
                            // Never display raw PHP, SQL, callback, or framework trace messages to the user!
                            var isTechnical = res.status >= 500 ||
                                /call_user_func|SQLSTATE|PDOException|Exception|ErrorException|Stack trace|syntax error|TypeError|Argument #/i.test(errorMsg);

                            if (isTechnical || !errorMsg) {
                                errorMsg = isAr
                                    ? 'تعذر إتمام عملية التسجيل حالياً. يرجى التأكد من صحة البيانات والمحاولة مرة أخرى.'
                                    : 'We could not complete your registration. Please verify your information and try again.';
                            }

                            showError(alertBox, errorMsg);
                        }
                    })
                    .catch(function () {
                        setLoading(submitBtn, false, defaultBtnText);
                        showError(alertBox, isAr ? 'حدث خطأ أثناء الاتصال بالخادم، يرجى المحاولة لاحقاً.' : 'Network error. Please try again.');
                    });
            });
        }

        function setLoading(btn, isLoading, text) {
            if (!btn) return;
            btn.disabled = isLoading;
            if (isLoading) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> ' + text;
            } else {
                btn.innerHTML = text;
            }
        }

        function showError(box, msg) {
            if (!box) return;
            box.className = 'darli-auth-alert alert alert-danger';
            box.style.display = 'block';
            box.textContent = msg;
        }

        function showSuccess(box, msg) {
            if (!box) return;
            box.className = 'darli-auth-alert alert alert-success';
            box.style.display = 'block';
            box.textContent = msg;
        }
    }

    // Auto-init on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initMobileMenu();
            initAuthModals();
            initAjaxAuth();
        });
    } else {
        initMobileMenu();
        initAuthModals();
        initAjaxAuth();
    }
})();
