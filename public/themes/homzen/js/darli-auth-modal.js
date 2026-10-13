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

                var email = (form.querySelector('input[name="email"]') || {}).value || '';
                var password = (form.querySelector('input[name="password"]') || {}).value || '';
                var remember = (form.querySelector('input[name="remember"]') || {}).checked ? 1 : 0;

                if (!email || !password) {
                    showError(alertBox, isAr ? 'يرجى إدخال البريد الإلكتروني وكلمة المرور.' : 'Please enter email and password.');
                    return;
                }

                setLoading(submitBtn, true, isAr ? 'جاري التحقق...' : 'Signing in...');

                fetch('/' + locale + '/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        email: email,
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
                        }, 600);
                    } else {
                        var msg = (res.data.errors && res.data.errors.email)
                            ? res.data.errors.email[0]
                            : (res.data.message || (isAr ? 'بيانات تسجيل الدخول غير صحيحة.' : 'Invalid credentials.'));
                        showError(alertBox, msg);
                    }
                })
                .catch(function () {
                    setLoading(submitBtn, false, isAr ? 'تسجيل الدخول' : 'Sign In');
                    showError(alertBox, isAr ? 'حدث خطأ أثناء الاتصال بالخادم، يرجى المحاولة لاحقاً.' : 'Network error. Please try again.');
                });
            });
        }

        // ------------------------ REGISTER FORM ------------------------
        var regForm = document.getElementById('darli-auth-register-form');
        if (regForm) {
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

                var firstName = (form.querySelector('input[name="first_name"]') || {}).value || '';
                var lastName = (form.querySelector('input[name="last_name"]') || {}).value || '';
                var name = (form.querySelector('input[name="name"]') || {}).value || (firstName + ' ' + lastName).trim();
                var email = (form.querySelector('input[name="email"]') || {}).value || '';
                var phone = (form.querySelector('input[name="phone"]') || {}).value || '';
                var password = (form.querySelector('input[name="password"]') || {}).value || '';
                var passwordConfirmation = (form.querySelector('input[name="password_confirmation"]') || {}).value || '';

                if (password !== passwordConfirmation) {
                    showError(alertBox, isAr ? 'كلمات المرور غير متطابقة.' : 'Passwords do not match.');
                    return;
                }

                if (password.length < 8) {
                    showError(alertBox, isAr ? 'يجب أن تتكون كلمة المرور من 8 أحرف على الأقل.' : 'Password must be at least 8 characters.');
                    return;
                }

                setLoading(submitBtn, true, isAr ? 'جاري إنشاء الحساب...' : 'Creating account...');

                fetch('/' + locale + '/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken()
                    },
                    body: JSON.stringify({
                        name: name,
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        phone: phone,
                        password: password,
                        password_confirmation: passwordConfirmation
                    })
                })
                .then(function (res) {
                    return res.json().then(function (data) {
                        return { status: res.status, data: data };
                    });
                })
                .then(function (res) {
                    setLoading(submitBtn, false, isAr ? 'إنشاء حساب جديد' : 'Create Account');
                    if (res.status === 200 && res.data.success) {
                        showSuccess(alertBox, isAr ? 'تم إنشاء الحساب وتسجيل الدخول بنجاح! جاري التحويل...' : 'Account created successfully! Redirecting...');
                        setTimeout(function () {
                            if (res.data.redirect) {
                                window.location.href = res.data.redirect;
                            } else {
                                window.location.reload();
                            }
                        }, 600);
                    } else {
                        var errors = res.data.errors || {};
                        var errorMsg = res.data.message || '';
                        if (errors.email) {
                            errorMsg = errors.email[0];
                        } else if (errors.password) {
                            errorMsg = errors.password[0];
                        } else if (errors.name) {
                            errorMsg = errors.name[0];
                        }
                        showError(alertBox, errorMsg || (isAr ? 'يرجى مراجعة البيانات المدخلة.' : 'Please review entered data.'));
                    }
                })
                .catch(function () {
                    setLoading(submitBtn, false, isAr ? 'إنشاء حساب جديد' : 'Create Account');
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
