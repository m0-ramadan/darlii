<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="robots" content="noindex,nofollow">
    <title>تسجيل دخول الإدارة | دارلي</title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('themes/homzen/images/logo/favicon.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/css/admin-login.css')); ?>?v=1.0.0">
</head>

<body class="admin-login">
    <main class="admin-login__shell">
        <section class="admin-login__card" aria-labelledby="admin-login-title">
            <div class="admin-login__brand">
                <img src="<?php echo e(asset('themes/homzen/images/logo/darli-logo-ar-dark.png')); ?>" alt="دارلي">
            </div>

            <div class="admin-login__heading">
                <span class="admin-login__eyebrow">بوابة الإدارة</span>
                <h1 id="admin-login-title">تسجيل الدخول</h1>
                <p>أدخل بيانات حسابك الإداري للوصول إلى لوحة التحكم.</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="admin-login__alert" role="alert">
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo e(route('admin.login.store')); ?>" class="admin-login__form">
                <?php echo csrf_field(); ?>
                <label class="admin-login__field">
                    <span>البريد الإلكتروني</span>
                    <span class="admin-login__input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/><path d="m22 6-10 7L2 6"/></svg>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="username"
                            placeholder="admin@example.com" required autofocus>
                    </span>
                </label>

                <label class="admin-login__field">
                    <span>كلمة المرور</span>
                    <span class="admin-login__input-wrap">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <input id="admin-password" type="password" name="password" autocomplete="current-password"
                            placeholder="••••••••" required>
                        <button type="button" class="admin-login__password-toggle" data-password-toggle
                            aria-label="إظهار كلمة المرور">عرض</button>
                    </span>
                </label>

                <label class="admin-login__remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>تذكرني على هذا الجهاز</span>
                </label>

                <button type="submit" class="admin-login__submit">دخول لوحة التحكم</button>
            </form>

            <p class="admin-login__security">هذه الصفحة مخصصة للمسؤولين المصرح لهم فقط.</p>
        </section>
    </main>

    <script>
        document.querySelector('[data-password-toggle]')?.addEventListener('click', function() {
            var input = document.getElementById('admin-password');
            var visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            this.textContent = visible ? 'عرض' : 'إخفاء';
            this.setAttribute('aria-label', visible ? 'إظهار كلمة المرور' : 'إخفاء كلمة المرور');
        });
    </script>
</body>

</html>
<?php /**PATH /var/www/html/resources/views/admin/auth/login.blade.php ENDPATH**/ ?>