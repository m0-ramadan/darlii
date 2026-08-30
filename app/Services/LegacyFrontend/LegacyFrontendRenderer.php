<?php



namespace App\Services\LegacyFrontend;

use App\Services\Localization\LocalizedUrlResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegacyFrontendRenderer
{
    public function __construct(
        private readonly LocalizedUrlResolver $localizedUrlResolver,
        private readonly string $root = ''
    ) {}

    public function render(Request $request, ?string $forcedPath = null): ?string
    {
        $root = $this->root !== ''
            ? $this->root
            : storage_path('app/legacy-site');

        $path = trim($forcedPath ?? $request->path(), '/');

        if ($path === '') {
            $path = 'index.html';
        }

        // English homepage is stored at the root of the legacy mirror.
        if ($path === 'en') {
            $path = 'index.html';
        }

        $file = $this->resolveFile($root, $path, $request);

        if ($file === null) {
            return null;
        }

        $html = file_get_contents($file);

        if ($html === false) {
            return null;
        }

        return $this->prepareHtml($html, $request);
    }

    private function resolveFile(string $root, string $path, Request $request): ?string
    {
        // Prevent traversal / null-byte tricks while still allowing mirrored
        // filenames such as "properties?page=1&type=sale".
        $path = str_replace(["\0", '..', '\\'], '', $path);
        $path = ltrim($path, '/');

        $candidates = [];
        $query = $request->getQueryString();

        if ($query !== null && $query !== '') {
            // Exact query order first because the mirror stores query strings
            // literally as part of filenames.
            $candidates[] = $path . '?' . $query;

            parse_str($query, $params);

            // Add a normalized version for ordinary query strings.
            if ($params !== []) {
                $candidates[] = $path . '?' . http_build_query($params);

                // The downloaded Homzen mirror contains these query orders.
                $knownOrders = [
                    ['page', 'type'],
                    ['type', 'page'],
                    ['page'],
                    ['type'],
                ];

                foreach ($knownOrders as $order) {
                    $ordered = [];

                    foreach ($order as $key) {
                        if (array_key_exists($key, $params)) {
                            $ordered[$key] = $params[$key];
                        }
                    }

                    if ($ordered !== []) {
                        $candidates[] = $path . '?' . http_build_query($ordered);
                    }
                }
            }
        }

        $candidates[] = $path;

        // Listing pages in the mirror are commonly stored as ?page=1 files.
        if (preg_match('#^(en|ar|fr|id|vi)/(properties|projects)$#', $path)) {
            $candidates[] = $path . '?page=1';
        }

        if (in_array($path, ['properties', 'projects'], true)) {
            $candidates[] = $path . '?page=1';
        }

        // The localized homepage cannot be stored directly as /ar, /fr, etc.
        // because those names are directories in the static mirror.
        // Currency-switch snapshots contain the same localized homepage markup.
        if (preg_match('#^(ar|fr|id|vi)$#', $path, $m)) {
            $candidates[] = $m[1] . '/currency/switch/USD';
        }

        // Legacy mirror stores article details under /news/{slug}
        // while some frontend links use /blog/{slug}.
        if (preg_match('#^(en|ar|fr|id|vi)/blog/(.+)$#', $path, $m)) {
            $candidates[] = $m[1] . '/news/' . $m[2];
        }

        if (preg_match('#^blog/(.+)$#', $path, $m)) {
            $candidates[] = 'news/' . $m[1];
        }

        foreach (array_values(array_unique($candidates)) as $candidate) {
            $candidatePath = $root . DIRECTORY_SEPARATOR . $candidate;

            if (is_file($candidatePath)) {
                return $candidatePath;
            }
        }

        return null;
    }

    private function prepareHtml(string $html, Request $request): string
    {
        $base = rtrim($request->getSchemeAndHttpHost(), '/');
        $escapedBase = str_replace('/', '\\/', $base);

        /*
         * Keep the original Homzen HTML/CSS/JS structure untouched.
         *
         * Only backend/page URLs are pointed to this Laravel application.
         * Asset URLs are changed to local URLs only when the mirrored asset
         * actually exists under public/. Otherwise the original URL is kept.
         */
        $html = preg_replace_callback(
            '#https?://homzen\.botble\.com/([^"\'<>\s)]*)#i',
            function (array $matches) use ($base): string {
                $path = ltrim($matches[1], '/');

                if ($this->isAssetPath($path)) {
                    return $this->localAssetExists($path)
                        ? $base . '/' . $path
                        : $matches[0];
                }

                return $base . '/' . $path;
            },
            $html
        ) ?? $html;

        /*
         * Same replacement for URLs escaped inside inline JavaScript / JSON:
         * https:\/\/homzen.botble.com\/...
         */
        $html = preg_replace_callback(
            '#https?:\\\\/\\\\/homzen\.botble\.com\\\\/([^"\'<>\s)]*)#i',
            function (array $matches) use ($escapedBase): string {
                $path = str_replace('\\/', '/', $matches[1]);
                $path = ltrim($path, '/');

                if ($this->isAssetPath($path)) {
                    return $this->localAssetExists($path)
                        ? $escapedBase . '\\/' . str_replace('/', '\\/', $path)
                        : $matches[0];
                }

                return $escapedBase . '\\/' . str_replace('/', '\\/', $path);
            },
            $html
        ) ?? $html;

        // Host-only values in structured data and globals are not matched by
        // the path-aware callbacks above.
        $html = str_replace(
            ['"https:\\/\\/homzen.botble.com"', '"http:\\/\\/homzen.botble.com"', 'window.siteUrl = "https://homzen.botble.com"'],
            ['"' . $escapedBase . '"', '"' . $escapedBase . '"', 'window.siteUrl = "' . $base . '"'],
            $html
        );
        $html = str_replace('data-site-cookie-domain="homzen.botble.com"', 'data-site-cookie-domain="' . e($request->getHost()) . '"', $html);
        $html = preg_replace(
            '#href=(["\'])https?://homzen\.botble\.com/cdn-cgi/l/email-protection(?:\#[^"\']*)?\1#i',
            'href="mailto:contact@botble.com"',
            $html
        ) ?? $html;

        $locale = $this->localeFromRequest($request);
        $localizedAjax = $base . '/' . $locale . '/ajax/render-ui-blocks';
        $html = str_replace(
            [$base . '/ajax/render-ui-blocks', $escapedBase . '\\/ajax\\/render-ui-blocks'],
            [$localizedAjax, str_replace('/', '\\/', $localizedAjax)],
            $html
        );

        $token = csrf_token();

        // Replace existing hidden Laravel CSRF fields without changing
        // surrounding form markup or CSS classes.
        $html = preg_replace_callback(
            '/<input\b[^>]*\bname\s*=\s*(["\']?)_token\1[^>]*>/i',
            function (array $matches) use ($token): string {
                $tag = $matches[0];

                if (preg_match('/\bvalue\s*=\s*(["\'])(.*?)\1/i', $tag)) {
                    return preg_replace(
                        '/\bvalue\s*=\s*(["\'])(.*?)\1/i',
                        'value="' . $token . '"',
                        $tag,
                        1
                    ) ?? $tag;
                }

                if (preg_match('/\bvalue\s*=\s*([^\s>]+)/i', $tag)) {
                    return preg_replace(
                        '/\bvalue\s*=\s*([^\s>]+)/i',
                        'value="' . $token . '"',
                        $tag,
                        1
                    ) ?? $tag;
                }

                $selfClosing = str_ends_with(rtrim($tag), '/>');
                $tagWithoutEnd = preg_replace('/\s*\/?>$/', '', $tag) ?? $tag;

                return $tagWithoutEnd
                    . ' value="' . $token . '"'
                    . ($selfClosing ? ' />' : '>');
            },
            $html
        ) ?? $html;

        // Replace CSRF tokens embedded by the mirrored site in inline scripts.
        $html = preg_replace_callback(
            '/(const\s+csrfToken\s*=\s*[\'"])[A-Za-z0-9._-]{20,}([\'"])/',
            static fn(array $matches): string => $matches[1] . $token . $matches[2],
            $html
        ) ?? $html;

        // Add a standard CSRF meta tag only when the original HTML does not
        // already contain one.
        if (
            ! Str::contains($html, 'name="csrf-token"')
            && ! Str::contains($html, "name='csrf-token'")
        ) {
            $meta = '
<meta name="csrf-token" content="' . e($token) . '">';

            $html = preg_replace(
                '/<head([^>]*)>/i',
                '<head$1>' . $meta,
                $html,
                1
            ) ?? $html;
        }

        $html = $this->normalizeLocalAssetUrls($html);
        $html = $this->removeHomepageSelector($html);
        $html = $this->normalizeInternalUrls($html, $request);
        $html = $this->linkHeaderBlogNavigation($html, $request);
        $html = $this->activateHeaderNavigationItem($html, $request);
        $html = $this->injectFooterLanguageSwitcher($html, $request);
        $html = $this->prepareEgyptMap($html, $request);
        $html = $this->synchronizeWishlistPropertyIds($html, $request);
        $html = $this->removeHomepageSelector($html);
        $html = $this->applyLocalizedSeo($html, $request);
        $html = $this->injectCustomAssets($html);
        $html = $this->attachImagePlaceholders($html);

        // Cloudflare challenge code belongs to the old host and can cause
        // console errors locally. Removing it does not alter the design.
        $html = $this->removeCloudflareChallengeScript($html);

        return $this->deduplicateFormFieldIds($html);
    }

    private function normalizeLocalAssetUrls(string $html): string
    {
        return preg_replace_callback(
            '/\b(href|src)\s*=\s*(["\'])(?!https?:|\/\/|data:|mailto:|tel:|#|\/)([^"\']+)\2/i',
            function (array $matches): string {
                $path = ltrim($matches[3], './');

                if (! $this->isAssetPath($path)) {
                    return $matches[0];
                }

                return $matches[1] . '=' . $matches[2] . '/' . $path . $matches[2];
            },
            $html
        ) ?? $html;
    }

    private function normalizeInternalUrls(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $supported = config('locales.supported', ['en']);
        $query = $request->getQueryString();

        return preg_replace_callback(
            '/\b(href|action|data-url)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i',
            function (array $matches) use ($locale, $supported, $query, $request): string {
                $url = $matches[2] ?? $matches[3] ?? $matches[4] ?? '';

                if (
                    $url === ''
                    || str_starts_with($url, '#')
                    || preg_match('#^(?:data:|mailto:|tel:|javascript:|//)#i', $url)
                ) {
                    return $matches[0];
                }

                $parts = parse_url(html_entity_decode($url));
                if ($parts === false) {
                    return $matches[0];
                }

                if (isset($parts['scheme'])) {
                    $host = strtolower((string) ($parts['host'] ?? ''));
                    if ($host !== strtolower($request->getHost())) {
                        return $matches[0];
                    }
                }

                $path = (string) ($parts['path'] ?? '');
                if ($path === '' && str_starts_with($url, '?')) {
                    return $matches[0];
                }

                $path = $this->resolveRelativePath($path, $request->path());
                $clean = ltrim($path, '/');

                if ($clean === '' || preg_match('#^homepage-[1-5]$#i', $clean)) {
                    $clean = $locale;
                } elseif ($this->isAssetPath($clean) || preg_match('#^(?:admin|api|up|language)(?:/|$)#i', $clean)) {
                    // Assets and non-localized application endpoints stay at root.
                } else {
                    $first = explode('/', $clean, 2)[0];
                    if (! in_array($first, $supported, true)) {
                        $clean = $locale . '/' . $clean;
                    }
                }

                if ($locale === 'ar' && ($clean === 'en' || str_starts_with($clean, 'en/'))) {
                    if (! str_contains($url, 'language/switch') && ! str_contains($url, 'locale.switch')) {
                        $clean = preg_replace('#^en(/|$)#', 'ar$1', $clean) ?? 'ar';
                    }
                }

                $clean = preg_replace('#^(' . implode('|', array_map('preg_quote', $supported)) . ')/homepage-[1-5]$#i', '$1', $clean) ?? $clean;
                $clean = $this->localizedUrlResolver->translateEntityPath($clean);

                $targetLocale = explode('/', $clean, 2)[0];
                $targetQuery = isset($parts['query']) ? '?' . $parts['query'] : '';
                if ($targetQuery === '' && $query && in_array($targetLocale, $supported, true) && $targetLocale !== $locale) {
                    $targetQuery = '?' . $query;
                }

                $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

                return $matches[1] . '="/' . $clean . $targetQuery . $fragment . '"';
            },
            $html
        ) ?? $html;
    }

    private function activateHeaderNavigationItem(string $html, Request $request): string
    {
        $activeSection = $this->navigationSectionFromPath($request->path());

        return preg_replace_callback(
            '/<li\b([^>]*)>(\s*<a\b[^>]*\bhref\s*=\s*(["\'])([^"\']*)\3[^>]*>)/i',
            function (array $matches) use ($activeSection): string {
                $attributes = $this->removeClassToken($matches[1], 'current');
                $hrefSection = $this->navigationSectionFromPath(html_entity_decode($matches[4]));

                if ($hrefSection === $activeSection) {
                    $attributes = $this->addClassToken($attributes, 'current');
                }

                return '<li' . $attributes . '>' . $matches[2];
            },
            $html
        ) ?? $html;
    }

    private function linkHeaderBlogNavigation(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);

        return preg_replace(
            '/(<li\b[^>]*class\s*=\s*(["\'])[^"\']*\bdropdown2\b[^"\']*\2[^>]*>\s*<a\b[^>]*\bhref\s*=\s*)(["\'])#\3([^>]*>\s*(?:المدونة|Blog)\s*<\/a>)/iu',
            '$1"/' . $locale . '/blog"$4',
            $html
        ) ?? $html;
    }

    private function navigationSectionFromPath(string $path): string
    {
        $path = (string) (parse_url($path, PHP_URL_PATH) ?: $path);
        $path = trim($path, '/');
        $supported = config('locales.supported', ['en']);

        if ($path === '') {
            return 'home';
        }

        $segments = explode('/', $path);
        if (in_array($segments[0], $supported, true)) {
            array_shift($segments);
        }

        $section = $segments[0] ?? '';

        return match ($section) {
            '', 'homepage-1', 'homepage-2', 'homepage-3', 'homepage-4', 'homepage-5' => 'home',
            'properties', 'property-category' => 'properties',
            'projects' => 'projects',
            'agents' => 'agents',
            'blog', 'news' => 'blog',
            default => $section,
        };
    }

    private function removeClassToken(string $attributes, string $class): string
    {
        return preg_replace_callback(
            '/\bclass\s*=\s*(["\'])(.*?)\1/i',
            static function (array $matches) use ($class): string {
                $classes = preg_split('/\s+/', trim($matches[2])) ?: [];
                $classes = array_values(array_filter($classes, static fn (string $item): bool => $item !== $class));

                return 'class=' . $matches[1] . implode(' ', $classes) . $matches[1];
            },
            $attributes,
            1
        ) ?? $attributes;
    }

    private function addClassToken(string $attributes, string $class): string
    {
        if (preg_match('/\bclass\s*=\s*(["\'])(.*?)\1/i', $attributes)) {
            return preg_replace_callback(
                '/\bclass\s*=\s*(["\'])(.*?)\1/i',
                static function (array $matches) use ($class): string {
                    $classes = preg_split('/\s+/', trim($matches[2])) ?: [];
                    if (! in_array($class, $classes, true)) {
                        $classes[] = $class;
                    }

                    return 'class=' . $matches[1] . trim(implode(' ', array_filter($classes))) . $matches[1];
                },
                $attributes,
                1
            ) ?? $attributes;
        }

        return rtrim($attributes) . ' class="' . $class . '"';
    }

    private function resolveRelativePath(string $path, string $requestPath): string
    {
        if ($path === '' || str_starts_with($path, '/')) {
            return $path;
        }

        $base = explode('/', trim($requestPath, '/'));
        array_pop($base);

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($base);
                continue;
            }

            $base[] = $segment;
        }

        return '/' . implode('/', $base);
    }

    private function removeHomepageSelector(string $html): string
    {
        return $html;
    }

    private function injectFooterLanguageSwitcher(string $html, Request $request): string
    {
        if (str_contains($html, 'homzen-footer-languages')) {
            return $html;
        }

        $locale = $this->localeFromRequest($request);
        $label = config('locales.labels.' . $locale, 'Language');
        $redirect = $request->getRequestUri();
        $links = '';

        foreach (config('locales.names', []) as $code => $name) {
            $href = '/language/switch/' . rawurlencode($code) . '?redirect=' . rawurlencode($redirect);
            $current = $code === $locale ? ' is-active' : '';
            $ariaCurrent = $code === $locale ? ' aria-current="page"' : '';

            $links .= '<a class="homzen-footer-languages__link' . $current . '" href="' . e($href) . '" lang="' . e($code) . '" hreflang="' . e($code) . '"' . $ariaCurrent . '>' . e($name) . '</a>';
        }

        $switcher = '<div class="homzen-footer-languages" aria-label="' . e($label) . '"><div class="container homzen-footer-languages__inner"><span class="homzen-footer-languages__label">' . e($label) . '</span><nav class="homzen-footer-languages__links" aria-label="' . e($label) . '">' . $links . '</nav></div></div>';

        return preg_replace('/(?=<div\b[^>]*class\s*=\s*(["\'])[^"\']*\bbottom-footer\b[^"\']*\1)/i', $switcher, $html, 1) ?? $html;
    }

    private function applyLocalizedSeo(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $direction = in_array($locale, config('locales.rtl', []), true) ? 'rtl' : 'ltr';
        $base = rtrim($request->getSchemeAndHttpHost(), '/');
        $canonical = $base . '/' . $locale;

        $html = preg_replace('/<html\b[^>]*>/i', '<html lang="' . $locale . '" dir="' . $direction . '">', $html, 1) ?? $html;

        if ($request->routeIs('home')) {
            $html = str_replace(
                str_replace('/', '\\/', $base . '/homepage-4'),
                str_replace('/', '\\/', $canonical),
                $html
            );
            $html = preg_replace('#<link\b(?=[^>]*\brel\s*=\s*(["\'])alternate\1)(?=[^>]*\bhreflang\s*=)[^>]*>#i', '', $html) ?? $html;
            $html = preg_replace('#<link\b[^>]*rel\s*=\s*(["\']?)canonical\1[^>]*>#i', '<link rel="canonical" href="' . $canonical . '">', $html, 1) ?? $html;
            $html = preg_replace('#(<meta\b[^>]*property\s*=\s*(["\']?)og:url\2[^>]*content\s*=\s*)(["\'])[^"\']*\3([^>]*>)#i', '$1$3' . $canonical . '$3$4', $html, 1) ?? $html;

            $hreflang = '';
            foreach (config('locales.supported', ['en']) as $code) {
                $hreflang .= '<link rel="alternate" hreflang="' . $code . '" href="' . $base . '/' . $code . '">';
            }
            $hreflang .= '<link rel="alternate" hreflang="x-default" href="' . $base . '/' . config('locales.default', 'en') . '">';
            $html = preg_replace('/<\/head>/i', $hreflang . '</head>', $html, 1) ?? $html;
        }

        return $html;
    }

    private function injectCustomAssets(string $html): string
    {
        $themeBoot = <<<'HTML'
<script id="homzen-theme-bootstrap">(function(){try{var t=localStorage.getItem('homzen-theme');if(t!=='dark'&&t!=='light'){var c=document.cookie.match(/(?:^|;\s*)homzen_theme=(dark|light)(?:;|$)/);t=c?c[1]:null}if(t!=='dark'&&t!=='light'){t=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'}document.documentElement.dataset.theme=t}catch(e){document.documentElement.dataset.theme='light'}})();</script>
HTML;

        $customStyles = <<<'HTML'
<link rel="stylesheet" href="/themes/homzen/css/rtl-custom.css?v=2.0.1">
<link rel="stylesheet" href="/themes/homzen/css/locale-switcher.css?v=1.0.0">
<link rel="stylesheet" href="/themes/homzen/css/dark-mode.css?v=2.0.1">
HTML;

        $html = preg_replace('/<head([^>]*)>/i', '<head$1>' . $themeBoot, $html, 1) ?? $html;
        $html = preg_replace('/<\/head>/i', $customStyles . '</head>', $html, 1) ?? $html;

        return preg_replace(
            '/<\/body>/i',
            '<script defer src="/themes/homzen/js/theme-mode.js?v=2.0.1"></script><script defer src="/themes/homzen/js/wishlist.js?v=2.0.1"></script><script defer src="/themes/homzen/js/map-location.js?v=2.0.1"></script></body>',
            $html,
            1
        ) ?? $html;
    }

    private function prepareEgyptMap(string $html, Request $request): string
    {
        if (! str_contains($html, 'data-bb-toggle="list-map"')) {
            return $html;
        }

        $locale = $this->localeFromRequest($request);
        $html = preg_replace('~\bdata-center\s*=\s*["\'][^"\']*["\']~i', 'data-center="[30.0444,31.2357]"', $html, 1) ?? $html;
        $html = preg_replace('/\bdata-url\s*=\s*(["\'])[^"\']*\/ajax\/properties\/map[^"\']*\1/i', 'data-url="/' . $locale . '/ajax/properties/map"', $html, 1) ?? $html;
        $html = preg_replace('/\bdata-tile-layer\s*=\s*(["\'])[^"\']+\1/i', 'data-tile-layer="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"', $html, 1) ?? $html;

        return $html;
    }

    private function synchronizeWishlistPropertyIds(string $html, Request $request): string
    {
        $lastSlug = null;
        if (preg_match('~/properties/([^/?#]+)~', '/' . trim($request->path(), '/'), $pathMatch)) {
            $lastSlug = $pathMatch[1];
        }

        return preg_replace_callback(
            '~<a\b[^>]*\bhref\s*=\s*(["\'])/(?:en|ar|fr|id|vi)/properties/([^"\'/?#]+)[^"\']*\1[^>]*>|<button\b(?=[^>]*data-bb-toggle\s*=\s*(["\'])add-to-wishlist\3)(?=[^>]*data-type\s*=\s*(["\'])property\4)[^>]*>~i',
            function (array $matches) use (&$lastSlug): string {
                if (isset($matches[2]) && $matches[2] !== '') {
                    $lastSlug = $matches[2];
                    return $matches[0];
                }

                if ($lastSlug === null) {
                    return $matches[0];
                }

                $propertyId = $this->localizedUrlResolver->propertyIdForSlug($lastSlug);
                if ($propertyId === null) {
                    return $matches[0];
                }

                $tag = preg_replace(
                    '/\bdata-id\s*=\s*(?:["\']\d+["\']|\d+)/i',
                    'data-id="' . $propertyId . '"',
                    $matches[0],
                    1
                ) ?? $matches[0];

                if (! preg_match('/\baria-pressed\s*=/i', $tag)) {
                    $tag = preg_replace('/>$/', ' aria-pressed="false" aria-label="Add to wishlist">', $tag, 1) ?? $tag;
                }

                return $tag;
            },
            $html
        ) ?? $html;
    }

    private function localeFromRequest(Request $request): string
    {
        $firstSegment = (string) (explode('/', trim($request->path(), '/'))[0] ?? '');
        $supported = config('locales.supported', ['ar', 'en']);

        if (in_array($firstSegment, $supported, true)) {
            return $firstSegment;
        }

        $routeLocale = (string) $request->route('locale');
        if (in_array($routeLocale, $supported, true)) {
            return $routeLocale;
        }

        $appLocale = app()->getLocale();
        if (in_array($appLocale, $supported, true)) {
            return $appLocale;
        }

        return config('locales.default', 'ar');
    }

    private function isAssetPath(string $path): bool
    {
        return (bool) preg_match(
            '#^(storage|themes|vendor|cdn-cgi)/#i',
            $path
        );
    }

    private function localAssetExists(string $path): bool
    {
        $cleanPath = strtok($path, '?') ?: $path;

        return is_file(public_path($cleanPath));
    }

    private function removeCloudflareChallengeScript(string $html): string
    {
        return preg_replace(
            '#<script\b[^>]*>(?:(?!</script>).)*?/cdn-cgi/challenge-platform/scripts/jsd/main\.js(?:(?!</script>).)*?</script>#is',
            '',
            $html
        ) ?? $html;
    }

    private function deduplicateFormFieldIds(string $html): string
    {
        $seen = [];

        return preg_replace_callback(
            '/\s+id\s*=\s*(["\']?)(email|password)\1/i',
            function (array $matches) use (&$seen): string {
                $id = strtolower($matches[2]);
                $seen[$id] = ($seen[$id] ?? 0) + 1;

                if ($seen[$id] === 1) {
                    return $matches[0];
                }

                return ' id="' . $id . '-' . $seen[$id] . '"';
            },
            $html
        ) ?? $html;
    }

    private function attachImagePlaceholders(string $html): string
    {
        $placeholder = '/themes/homzen/images/darli-placeholder.png';

        return preg_replace_callback(
            '/<img\b([^>]*?)>/i',
            function (array $matches) use ($placeholder): string {
                $tag = $matches[0];

                if (! str_contains($tag, 'onerror=')) {
                    $tag = rtrim(rtrim($tag, '>'), '/') . ' onerror="this.onerror=null;this.src=\'' . $placeholder . '\';" >';
                }

                if (preg_match('/src=(["\'])\s*\1/i', $tag)) {
                    $tag = preg_replace('/src=(["\'])\s*\1/i', 'src="' . $placeholder . '"', $tag) ?? $tag;
                }

                return $tag;
            },
            $html
        ) ?? $html;
    }
}
