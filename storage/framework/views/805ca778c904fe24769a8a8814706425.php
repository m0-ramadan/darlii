<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo e($title); ?></title>
    <style>
        :root { color-scheme: light; --green: #0f3d2e; --gold: #c8a45c; --ink: #18342b; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; font-family: Tahoma, Arial, sans-serif; color: var(--ink); background: radial-gradient(circle at 15% 15%, rgba(200,164,92,.18), transparent 28%), linear-gradient(145deg, #f9f7f0, #edf4f0); }
        .card { width: min(660px, 100%); padding: clamp(34px, 7vw, 64px); text-align: center; background: rgba(255,255,255,.94); border: 1px solid rgba(15,61,46,.12); border-radius: 28px; box-shadow: 0 24px 70px rgba(15,61,46,.14); }
        .logo { max-width: 180px; max-height: 76px; object-fit: contain; margin-bottom: 28px; }
        .icon { width: 82px; height: 82px; margin: 0 auto 24px; display: grid; place-items: center; color: var(--gold); background: rgba(200,164,92,.12); border-radius: 50%; }
        .icon svg { width: 42px; height: 42px; }
        h1 { margin: 0 0 16px; color: var(--green); font-size: clamp(28px, 5vw, 42px); line-height: 1.25; }
        p { margin: 0 auto; max-width: 510px; color: #587067; font-size: 18px; line-height: 1.9; white-space: pre-line; }
        .line { width: 72px; height: 3px; margin: 30px auto 0; border-radius: 99px; background: var(--gold); }
    </style>
</head>
<body>
    <main class="card" role="main">
        <?php if($logo): ?>
            <img class="logo" src="<?php echo e(str_starts_with((string) $logo, 'http') ? $logo : asset($logo)); ?>" alt="<?php echo e(config('app.name')); ?>">
        <?php endif; ?>
        <div class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 0 0-5-5L7.6 3.4l3 3 2.1-2.1a4 4 0 0 0 2 2Z"/><path d="m5 21 9.7-9.7"/><path d="m3 18 3 3"/><path d="M14 14.5 19.5 20a1.4 1.4 0 0 0 2-2L16 12.5"/></svg>
        </div>
        <h1><?php echo e($title); ?></h1>
        <p><?php echo e($message); ?></p>
        <div class="line"></div>
    </main>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/maintenance.blade.php ENDPATH**/ ?>