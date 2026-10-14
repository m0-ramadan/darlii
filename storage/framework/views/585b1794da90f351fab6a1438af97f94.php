<?php
    /**
     * هيكل صفحات الموقع العام: يستخدم نفس الهيدر والفوتر والخطوط الخاصة
     * بتصميم هومزن الأصلي (من نسخة الميرور) لكنه يعرض محتوى قاعدة البيانات.
     *
     * $shellHead / $shellFooter يأتيان من LegacyFrontendRenderer::shell().
     */
    $shellHead = $shellHead ?? null;
    $shellFooter = $shellFooter ?? null;

    if (empty($shellHead) || empty($shellFooter)) {
        try {
            $renderer = app(\App\Services\LegacyFrontend\LegacyFrontendRenderer::class);
            $autoShell = $renderer->shell(
                app()->getLocale(),
                'properties',
                \App\Services\LegacyFrontend\LegacyFrontendRenderer::listingsMarker()
            );
            $shellHead = $shellHead ?: ($autoShell['head'] ?? '');
            $shellFooter = $shellFooter ?: ($autoShell['footer'] ?? '');
        } catch (\Throwable $e) {}
    }

    $bodyClass = trim(($bodyClass ?? '') . ' darli-db-page');
    $head = $shellHead ?? '';

    // طبقة الأدوات المساعدة الخاصة بصفحات قاعدة البيانات (تُحمَّل بعد ثيم هومزن).
    $darliCss = '<link rel="stylesheet" href="' . asset('themes/homzen/css/darli-public.css') . '?v=1.0.0">';

    $pushedHead = (string) $__env->yieldPushContent('head');
    $headInjection = $darliCss . ($pushedHead !== '' ? "\n" . $pushedHead : '');

    if (stripos($head, '</head>') !== false) {
        $head = preg_replace('/<\/head>/i', $headInjection . '</head>', $head, 1) ?? $head;
    } else {
        $head .= $headInjection;
    }
    if ($bodyClass !== '') {
        $head = preg_replace_callback(
            '/<body\b([^>]*)>/i',
            function (array $m) use ($bodyClass): string {
                $attributes = $m[1];
                if (preg_match('/class\s*=\s*(["\'])(.*?)\1/i', $attributes, $c)) {
                    $attributes = str_replace($c[0], 'class="' . trim($c[2] . ' ' . $bodyClass) . '"', $attributes);
                } else {
                    $attributes .= ' class="' . $bodyClass . '"';
                }

                return '<body' . $attributes . '>';
            },
            $head,
            1
        ) ?? $head;
    }
?>
<?php
    // عنوان الصفحة: يستخدم @section('title') من الصفحة نفسها إن وُجد،
    // وإلا يبقى عنوان الميرور الأصلي كما هو.
    $pageTitle = trim((string) $__env->yieldContent('title'));
    $siteName = (string) setting('general.site_name', 'دارلي');

    if ($pageTitle !== '') {
        // لا نكرّر اسم الموقع إذا كان موجوداً في العنوان بالفعل.
        $fullTitle = str_contains($pageTitle, $siteName) ? $pageTitle : $pageTitle . ' | ' . $siteName;

        $head = preg_replace_callback(
            '/<title\b[^>]*>.*?<\/title>/is',
            static fn (): string => '<title>' . e($fullTitle) . '</title>',
            $head,
            1
        ) ?? $head;
    }

    $pushedScripts = (string) $__env->yieldPushContent('scripts');
    $footer = $shellFooter ?? '';
    if ($pushedScripts !== '') {
        if (stripos($footer, '</body>') !== false) {
            $footer = preg_replace('/<\/body>/i', $pushedScripts . "\n</body>", $footer, 1) ?? $footer;
        } else {
            $footer .= "\n" . $pushedScripts;
        }
    }
?>
<?php echo $head; ?>

<div id="wrapper">
    <?php echo $__env->yieldContent('content'); ?>
</div>
<?php echo $footer; ?>

<?php /**PATH /var/www/html/resources/views/layouts/public-shell.blade.php ENDPATH**/ ?>