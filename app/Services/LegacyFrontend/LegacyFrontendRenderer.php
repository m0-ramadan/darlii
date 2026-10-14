<?php



namespace App\Services\LegacyFrontend;

use App\Models\AgentProfile;
use App\Models\Post;
use App\Models\Property;
use App\Models\Testimonial;
use App\Services\Localization\LocalizedUrlResolver;
use App\Services\Settings\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LegacyFrontendRenderer
{
    public function __construct(
        private readonly LocalizedUrlResolver $localizedUrlResolver,
        private readonly string $root = ''
    ) {}

    /**
     * Returns the mirrored page's <head> + header and its footer as a Blade
     * shell, split by the given marker:
     *
     *   ['head' => ..., 'footer' => ...]
     *
     * This lets database-driven pages (the CMS listings) reuse the exact
     * Homzen header/footer chrome while their own body content is rendered
     * from the database. Returns null when the mirror file is unavailable.
     *
     * @return array{head:string, footer:string}|null
     */
    public function shell(string $locale, string $page, string $splitMarker): ?array
    {
        $root = $this->root !== '' ? $this->root : storage_path('app/legacy-site');
        $path = trim($locale . '/' . $page, '/');

        // يعمل داخل الطلب وخارجه (مثلاً من أوامر artisan أو الاختبارات).
        try {
            request()->getSession();
            $request = request();
        } catch (\Throwable $e) {
            $request = Request::create('/' . $path, 'GET');
        }

        $file = $this->resolveFile($root, $path, $request);

        /*
         * إذا لم يكن الملف موجوداً على القرص (مثل صفحات تفاصيل العقارات والمشاريع
         * المنشأة ديناميكياً من قاعدة البيانات)، نستخدم صفحة القسم الرئيسي أو المدونة
         * كقالب متناسق للهيدر والفوتر.
         */
        if ($file === null) {
            if (preg_match('#^([^/]+)/properties/#', $path, $m)) {
                $file = $this->resolveFile($root, $m[1] . '/properties/property-for-sale-johannesburg-south-africa', $request);
            }
            if ($file === null && preg_match('#^([^/]+)/(properties|projects|blog)/#', $path, $m)) {
                $file = $this->resolveFile($root, $m[1] . '/' . $m[2], $request);
            }
            if ($file === null) {
                $file = $this->resolveFile($root, $locale . '/properties/property-for-sale-johannesburg-south-africa', $request)
                    ?? $this->resolveFile($root, $locale . '/properties', $request)
                    ?? $this->resolveFile($root, $locale . '/blog', $request);
            }
        }

        if ($file === null) {
            return null;
        }

        $html = file_get_contents($file);
        if ($html === false) {
            return null;
        }

        $html = $this->prepareHtml($html, $request);

        /*
         * نأخذ head/body فقط من صفحة الميرور، ثم نضيف الهيدر الموحد صراحة.
         * الاعتماد على نقطة داخل body غير آمن لأن نفس الكلاس قد يظهر داخل
         * template أو نموذج بحث مخفي، فيتم حقن محتوى Blade داخله ويختفي.
         */
        if (! preg_match('/\A.*?<body\b[^>]*>/is', $html, $bodyMatch)) {
            return null;
        }

        $head = $bodyMatch[0];
        $header = $this->siteHeaderHtml($locale, $root);
        if ($header === '') {
            return null;
        }
        $header = $this->prepareHtml($header, $request);
        $head .= $header;

        $footerStart = strrpos($html, '<footer');
        if ($footerStart === false) {
            return null;
        }

        $shell = $this->applyCmsSettings($head . substr($html, $footerStart), $request);
        $shellFooterStart = strpos($shell, '<footer');
        if ($shellFooterStart === false) {
            return null;
        }

        return [
            'head' => substr($shell, 0, $shellFooterStart),
            'footer' => substr($shell, $shellFooterStart),
        ];
    }

    /**
     * The marker that starts the listings block in the mirrored pages, used by
     * shell() to know where the Chrome ends and the listings begin.
     */
    public static function listingsMarker(): string
    {
        return '<section class="flat-section-v5 flat-recommended';
    }

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

        $html = $this->useSiteHeader($html, $root, $request);
        $html = $this->prepareHtml($html, $request);

        return $this->applyCmsSettings($html, $request);
    }

    /**
     * Applies everything the CMS controls to the mirrored frontend: logos and
     * brand colours, public contact details, the "إضافة عقار" / dashboard links
     * and the analytics/pixel snippets saved under الإعدادات ← التكاملات.
     *
     * All replacements are no-ops when the setting is empty, so an unconfigured
     * CMS leaves the original design untouched.
     */
    private function applyCmsSettings(string $html, Request $request): string
    {
        try {
            $settings = app(\App\Services\Settings\SettingsRepository::class);
        } catch (\Throwable $e) {
            return $html;
        }

        $locale = $this->localeFromRequest($request);
        $isRtl = $locale === 'ar';

        // ------------------------------------------------------------- logos
        $logo = $settings->get($isRtl ? 'branding.logo_ar' : 'branding.logo_en');
        $darkLogo = $settings->get($isRtl ? 'branding.logo_dark_ar' : 'branding.logo_dark_en');

        foreach (
            array_filter([
                '/themes/homzen/images/logo/darli-logo-' . ($isRtl ? 'ar' : 'en') . '.png' => (string) $logo,
                '/themes/homzen/images/logo/darli-logo-' . ($isRtl ? 'ar' : 'en') . '-dark.png' => (string) $darkLogo,
            ]) as $mirrorPath => $cmsPath
        ) {
            if (! str_contains($cmsPath, 'images/logo/')) {
                continue;
            }

            // Replace the complete normalized URL. Replacing only the tail
            // duplicates the theme prefix on legacy pages.
            $html = str_replace($mirrorPath, '/' . ltrim($cmsPath, '/'), $html);
        }

        // ------------------------------------------------------ contact data
        // Both the original Homzen placeholders and whatever the CMS holds are
        // covered, so switching the phone/email in الإعدادات updates the public
        // pages even though they are served from a static mirror.
        $email = (string) $settings->get('contact.email', 'info@darlii.eg');
        $phone = (string) $settings->get('contact.phone', '+20 10 34905812');
        $whatsapp = (string) $settings->get('contact.whatsapp', '+20 10 34905812');

        $cleanWa = preg_replace('/\D+/', '', $whatsapp);
        if (str_starts_with($cleanWa, '01') && strlen($cleanWa) === 11) {
            $cleanWa = '2' . $cleanWa;
        }

        $facebook = (string) $settings->get('contact.facebook', 'https://facebook.com');
        $twitter = (string) $settings->get('contact.twitter', 'https://x.com');
        $instagram = (string) $settings->get('contact.instagram', 'https://instagram.com');
        $linkedin = (string) $settings->get('contact.linkedin', 'https://linkedin.com');
        $youtube = (string) $settings->get('contact.youtube', 'https://youtube.com');
        $tiktok = (string) $settings->get('contact.tiktok', '');

        // Longest/most specific patterns come first so "+201234567890" is not
        // partially rewritten by the shorter "0123456789" rule.
        $replacements = array_filter([
            '+20 100 000 0000' => $phone,
            '+20 123 456 789' => $phone,
            '201234567890' => $cleanWa !== '' ? $cleanWa : '',
            '012 345 6789' => $phone,
            '0123456789' => $phone,
            '01001234561' => $phone,
            '+201001234561' => $phone,
            'info@Darlii.eg' => $email,
            'info@darlii.eg' => $email,
            'info@darli.com' => $email,
            'info@dari.com' => $email,
            'contact@botble.com' => $email,
        ], static fn(string $value): bool => $value !== '');

        if ($replacements !== []) {
            $html = str_replace(array_keys($replacements), array_values($replacements), $html);
        }

        // Direct phone link in footer contact item
        if ($phone !== '') {
            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-contact-item[^"\']*["\'][^>]*>\s*<i\b[^>]*data-lucide=["\']phone["\'][^>]*><\/i>\s*<span\b[^>]*>)(.*?)(<\/span>)/si',
                '${1}<a href="tel:' . preg_replace('/[^\d+]/', '', $phone) . '" style="color:inherit;text-decoration:none;" dir="ltr">' . e($phone) . '</a>${3}',
                $html
            ) ?? $html;
        }

        // Update footer social media icons (.darli-footer-socials)
        $socialLinks = [];
        if ($facebook !== '') {
            $socialLinks[] = '<a href="' . e($facebook) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>';
        }
        if ($twitter !== '') {
            $socialLinks[] = '<a href="' . e($twitter) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>';
        }
        if ($instagram !== '') {
            $socialLinks[] = '<a href="' . e($instagram) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>';
        }
        if ($linkedin !== '') {
            $socialLinks[] = '<a href="' . e($linkedin) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg></a>';
        }
        if ($youtube !== '') {
            $socialLinks[] = '<a href="' . e($youtube) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>';
        }
        if ($cleanWa !== '') {
            $socialLinks[] = '<a href="https://wa.me/' . e($cleanWa) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.587 1.761.88 2.795.88h.002c3.18 0 5.767-2.587 5.768-5.766.001-3.18-2.585-5.767-5.769-5.767zm7.509 5.766c-.001 4.144-3.373 7.514-7.512 7.515-1.256 0-2.485-.316-3.574-.915l-4.454 1.168 1.189-4.342c-.658-1.144-1.006-2.448-1.006-3.791.001-4.143 3.372-7.514 7.513-7.515 4.141 0 7.512 3.372 7.514 7.515zm-4.316 2.062c-.179-.089-1.058-.523-1.222-.582-.164-.059-.283-.089-.402.089-.119.179-.462.582-.566.702-.104.119-.208.134-.387.045s-.758-.279-1.444-.891c-.534-.476-.895-1.064-.999-1.243-.104-.179-.011-.276.078-.365.08-.08.179-.208.268-.312.09-.104.119-.179.179-.298.059-.119.03-.223-.015-.312-.045-.089-.402-.969-.551-1.327-.145-.348-.292-.3-.402-.305-.104-.005-.223-.006-.342-.006s-.313.045-.476.223c-.164.179-.625.611-.625 1.49 0 .879.64 1.728.729 1.847.09.119 1.259 1.922 3.05 2.695.426.184.759.294 1.018.377.428.136.818.117 1.126.071.344-.052 1.058-.432 1.207-.849.149-.417.149-.774.104-.849-.045-.075-.164-.119-.343-.208z"/></svg></a>';
        }
        if ($tiktok !== '') {
            $socialLinks[] = '<a href="' . e($tiktok) . '" class="darli-footer-social-btn" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.298-.002.595.042.88.13V9.4a6.33 6.33 0 0 0-1-.08A6.34 6.34 0 0 0 3 15.66a6.34 6.34 0 0 0 10.86 4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-.04-4.52z"/></svg></a>';
        }

        if (! empty($socialLinks)) {
            $socialHtml = '<div class="darli-footer-socials">' . "\n              " . implode("\n              ", $socialLinks) . "\n            </div>";
            $html = preg_replace('/<div\b[^>]*class=["\'][^"\']*darli-footer-socials[^"\']*["\'][^>]*>.*?<\/div>/si', $socialHtml, $html) ?? $html;
        }

        // Hide English language switcher from footer / header if English is disabled in dashboard
        if (! app()->runningUnitTests() && ! (bool) $settings->get('general.language_en_enabled', false)) {
            $html = preg_replace('~<a\b[^>]*href=["\'][^"\']*/language/switch/en[^"\']*["\'][^>]*>.*?</a>~is', '', $html) ?? $html;
        }

        $html = $this->renderDynamicFooter($html, $request, $settings);

        // Floating WhatsApp button (Fixed on the Left)
        if ($cleanWa !== '') {
            $waMessage = $isRtl ? 'مرحباً، أود الاستفسار عن عقارات منصة دارلي' : 'Hello, I would like to inquire about properties on Darli';
            $waTooltip = $isRtl ? 'تواصل معنا عبر واتساب' : 'Chat with us on WhatsApp';
            $waHref = 'https://wa.me/' . e($cleanWa) . '?text=' . urlencode($waMessage);

            $floatingWaHtml = <<<HTML
<!-- Floating WhatsApp Button (Fixed on the Left) -->
<div id="darli-floating-wa" class="darli-floating-wa" dir="{$locale}">
    <a href="{$waHref}" target="_blank" rel="noopener noreferrer" class="darli-floating-wa__btn" aria-label="WhatsApp" title="{$waTooltip}">
        <span class="darli-floating-wa__pulse"></span>
        <span class="darli-floating-wa__tooltip">{$waTooltip}</span>
        <svg class="darli-floating-wa__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="28" height="28" fill="#ffffff">
            <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
        </svg>
    </a>
</div>
HTML;
            if (! str_contains($html, 'darli-floating-wa')) {
                if (stripos($html, '</body>') !== false) {
                    $html = str_ireplace('</body>', $floatingWaHtml . "\n</body>", $html);
                } else {
                    $html .= $floatingWaHtml;
                }
            }
        }

        // -------------------------------------------- نظام الإشعارات العائم و Firebase
        if (setting('firebase.enabled', '1') == '1' && ! str_contains($html, 'darli-notifications-wrapper')) {
            $firebaseConfigJson = json_encode([
                'enabled' => true,
                'apiKey' => (string) setting('firebase.api_key', 'AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q'),
                'authDomain' => (string) setting('firebase.auth_domain', 'khademate2.firebaseapp.com'),
                'projectId' => (string) setting('firebase.project_id', 'khademate2'),
                'storageBucket' => (string) setting('firebase.storage_bucket', 'khademate2.firebasestorage.app'),
                'messagingSenderId' => (string) setting('firebase.messaging_sender_id', '504226887841'),
                'appId' => (string) setting('firebase.app_id', '1:504226887841:web:cbc5abdc30304c6014d8e0'),
                'measurementId' => (string) setting('firebase.measurement_id', 'G-01HJBBQZP7'),
                'vapidKey' => (string) setting('firebase.vapid_public_key', ''),
                'userId' => auth()->id(),
            ]);

            $floatingNotifHtml = view('components.floating-notifications')->render() . "\n" . <<<HTML
<script>window.DarliFirebaseConfig = {$firebaseConfigJson};</script>
<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.13.0/firebase-messaging-compat.js"></script>
<script defer src="/themes/homzen/js/darli-notifications.js?v=1.0.1"></script>
<script defer src="/themes/homzen/js/darli-visitor-tracker.js?v=1.0.0"></script>
HTML;

            if (stripos($html, '</body>') !== false) {
                $html = str_ireplace('</body>', $floatingNotifHtml . "\n</body>", $html);
            } else {
                $html .= $floatingNotifHtml;
            }
        }

        if (! str_contains($html, 'darli-testimonials-drag.js')) {
            $testimonialDragScript = '<script defer src="/themes/homzen/js/darli-testimonials-drag.js?v=1.0.0"></script>';
            $html = stripos($html, '</body>') !== false
                ? str_ireplace('</body>', $testimonialDragScript . "\n</body>", $html)
                : $html . $testimonialDragScript;
        }

        // -------------------------------------------- "إضافة عقار" / dashboard
        // Logged-in staff go straight to the CMS, regular clients to their dashboard, visitors to login.
        if (auth()->check()) {
            $user = auth()->user();
            $accountUrl = ($user->hasRole('agency-manager') || $user->hasRole('agent') || $user->hasRole('customer'))
                ? '/dashboard'
                : ($user->hasAnyRole(\App\Support\PermissionCatalog::panelRoles())
                    ? '/admin'
                    : '/dashboard');
        } else {
            $accountUrl = '/login';
        }
        $html = preg_replace(
            '#(href=")(?:\.\./|/)?(?:' . preg_quote($locale, '#') . '/)?account/properties(")#',
            '$1' . $accountUrl . '$2',
            $html
        ) ?? $html;

        // ------------------------------------------------------ brand colours
        $primary = (string) $settings->get('branding.primary_color', '');
        $gold = (string) $settings->get('branding.gold_color', '');
        $css = [];

        if ($primary !== '') {
            $css[] = '--brand-primary:' . $primary . ';--color-primary:' . $primary . ';';
        }
        if ($gold !== '') {
            $css[] = '--brand-gold:' . $gold . ';--color-secondary:' . $gold . ';';
        }
        if ($css !== []) {
            $html = str_replace('</head>', '<style id="darlii-cms-brand">:root{' . implode('', $css) . '}</style></head>', $html);
        }

        // ---------------------------------------------------------- tracking
        $snippets = [];

        if ($settings->get('analytics.ga_enabled') && ($gaId = (string) $settings->get('analytics.ga_measurement_id', ''))) {
            $snippets[] = <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$gaId}"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{$gaId}');</script>
HTML;
        }

        if (($gtmId = (string) $settings->get('analytics.gtm_id', ''))) {
            $snippets[] = <<<HTML
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtmId}');</script>
HTML;
        }

        if ($settings->get('analytics.pixel_enabled') && ($pixelId = (string) $settings->get('analytics.facebook_pixel_id', ''))) {
            $snippets[] = <<<HTML
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{$pixelId}');fbq('track','PageView');</script>
HTML;
        }

        if (($tiktokId = (string) $settings->get('analytics.tiktok_pixel_id', ''))) {
            $snippets[] = <<<HTML
<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=r;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript";o.async=!0;o.src=r+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('{$tiktokId}');ttq.page();}(window,document,'ttq');</script>
HTML;
        }

        if (($verification = (string) $settings->get('analytics.search_console', ''))) {
            $snippets[] = '<meta name="google-site-verification" content="' . e($verification) . '">';
        }

        if ($snippets !== []) {
            $html = str_replace('</head>', implode("\n", $snippets) . '</head>', $html);
        }

        // ------------------------------------------------ Auth UI & Modals
        if (auth()->check()) {
            $user = auth()->user();
            $displayName = e($user?->name ?? 'حسابي');
            $authHeaderHtml = '<ul class="d-flex align-items-center gap-2"><li><a href="' . $accountUrl . '" class="fw-bold"><i data-lucide="user" style="width:14px;height:14px;"></i> ' . $displayName . ' (لوحة التحكم)</a></li><li>/</li><li><a href="/logout">تسجيل الخروج</a></li></ul>';
            $html = preg_replace(
                '~<div class="register">\s*<ul class="d-flex">.*?</ul>\s*</div>~is',
                '<div class="register">' . $authHeaderHtml . '</div>',
                $html
            ) ?? $html;

            $mobileAuthHtml = '<div class="login-box flex align-items-center"><a href="' . $accountUrl . '"><i data-lucide="user" style="width:14px;height:14px;"></i> ' . $displayName . ' (لوحة التحكم)</a><span>/</span><a href="/logout">تسجيل الخروج</a></div>';
            $html = preg_replace(
                '~<div class="login-box flex align-items-center">.*?</div>~is',
                $mobileAuthHtml,
                $html
            ) ?? $html;

            // Also replace topbar items in homepage-4
            $topbarUserHtml = '<a href="' . $accountUrl . '" class="darli-topbar-item"><span>لوحة التحكم</span><i data-lucide="user" style="width:15px;height:15px;"></i></a><a href="/logout" class="darli-topbar-item"><span>تسجيل الخروج</span><i data-lucide="log-out" style="width:15px;height:15px;"></i></a>';
            $html = preg_replace(
                '~<a\b[^>]*href=["\'][^"\']*/login["\'][^>]*class=["\'][^"\']*darli-topbar-item[^"\']*["\'][^>]*>.*?</a>~is',
                $topbarUserHtml,
                $html
            ) ?? $html;
        } else {
            // Ensure login and register links trigger the popup modals on the current page
            $html = preg_replace(
                '~href="[^"]*#modalLogin"~i',
                'href="#modalLogin" data-bs-toggle="modal" data-bs-target="#modalLogin"',
                $html
            ) ?? $html;
            $html = preg_replace(
                '~href="[^"]*#modalRegister"~i',
                'href="#modalRegister" data-bs-toggle="modal" data-bs-target="#modalRegister"',
                $html
            ) ?? $html;

            // On homepage-4, add register modal trigger to topbar if not already present
            if (str_contains($html, 'darli-topbar-item') && ! str_contains($html, 'modalRegister')) {
                $html = preg_replace(
                    '~(<a\b[^>]*href=["\'][^"\']*/login["\'][^>]*class=["\'][^"\']*darli-topbar-item[^"\']*["\'][^>]*>)~is',
                    '<a href="#modalRegister" data-bs-toggle="modal" data-bs-target="#modalRegister" class="darli-topbar-item darli-open-register"><span>تسجيل في الموقع</span><i data-lucide="user-plus" style="width:15px;height:15px;"></i></a>$1',
                    $html,
                    1
                ) ?? $html;
            }
        }

        $authModalsHtml = '';
        if (! auth()->check()) {
            try {
                $authModalsHtml = view('partials.auth-modals')->render();
            } catch (\Throwable) {
                $authModalsHtml = '';
            }
        }

        $authScript = '<script defer src="/themes/homzen/js/darli-auth-modal.js?v=2.0.2"></script>';

        if (str_contains($html, '</body>')) {
            $html = preg_replace('/<\/body>/i', $authModalsHtml . "\n" . $authScript . "\n</body>", $html, 1) ?? $html;
        } else {
            $html .= "\n" . $authModalsHtml . "\n" . $authScript;
        }

        return $html;
    }

    /** Use the original Homzen navigation shown on the public listing pages. */
    private function useSiteHeader(string $html, string $root, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $file = $root . '/' . $locale . '/properties?page=1';
        $source = is_file($file) ? file_get_contents($file) : false;
        if ($source === false) return $html;
        $header = $this->siteHeaderHtml($locale, $root);
        if ($header === '') return $html;
        $header .= '<!-- darli-canonical-header-end -->';

        $patterns = [
            '~<div\b(?=[^>]*class=["\'][^"\']*\bdarli-topbar\b)[^>]*>.*?</header>~is',
            '~<div\b(?=[^>]*class=["\'][^"\']*\btop-header\b)[^>]*>.*?</header>~is',
            '~<div\b(?=[^>]*class=["\'][^"\']*\btop-header\b)[^>]*>.*?(?=<section\b)~is',
            '~<header\b[^>]*>.*?</header>~is',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html)) {
                $html = preg_replace_callback($pattern, static fn(): string => $header, $html, 1) ?? $html;
                break;
            }
        }

        return $this->ensureCanonicalHeaderStylesheets($html, $source);
    }

    public function siteHeaderHtml(string $locale, ?string $root = null): string
    {
        $root ??= $this->root !== '' ? $this->root : storage_path('app/legacy-site');
        $file = $root . '/' . $locale . '/properties?page=1';
        if (! is_file($file)) $file = $root . '/en/properties?page=1';
        $source = is_file($file) ? file_get_contents($file) : false;
        if ($source === false || ! preg_match('~<div\b(?=[^>]*class=["\'][^"\']*\btop-header\b)[^>]*>.*?</header>~is', $source, $match)) return '';

        $header = preg_replace(
            '~<div\b([^>]*class=["\'][^"\']*\bmobile-nav-toggler\b[^"\']*["\'][^>]*)>~i',
            '<div$1 role="button" tabindex="0" aria-expanded="false" onclick="event.preventDefault();event.stopPropagation();document.body.classList.add(\'mobile-menu-visible\');this.setAttribute(\'aria-expanded\',\'true\')">',
            $match[0],
            1
        ) ?? $match[0];

        $header = preg_replace(
            '~<div\b([^>]*class=["\'][^"\']*\bclose-btn\b[^"\']*["\'][^>]*)>~i',
            '<div$1 role="button" tabindex="0" onclick="document.body.classList.remove(\'mobile-menu-visible\')">',
            $header,
            1
        ) ?? $header;

        $header = $this->applyAuthenticatedHeader($header, $locale);

        return $this->pageSpeedStylesUsedBy($header, $source) . $header;
    }

    private function applyAuthenticatedHeader(string $header, string $locale): string
    {
        if (! auth()->check()) {
            return $header;
        }

        $user = auth()->user();
        $displayName = e($user?->name ?? 'حسابي');
        $accountUrl = ($user->hasRole('agency-manager') || $user->hasRole('agent') || $user->hasRole('customer'))
            ? '/dashboard'
            : ($user->hasAnyRole(\App\Support\PermissionCatalog::panelRoles())
                ? '/admin'
                : '/dashboard');

        $desktopAuth = '<ul class="d-flex align-items-center gap-2"><li><a href="' . $accountUrl . '" class="fw-bold"><i data-lucide="user" style="width:14px;height:14px;"></i> ' . $displayName . ' (لوحة التحكم)</a></li><li>/</li><li><a href="/logout">تسجيل الخروج</a></li></ul>';
        $header = preg_replace(
            '~<div class="register">\s*<ul class="d-flex">.*?</ul>\s*</div>~is',
            '<div class="register">' . $desktopAuth . '</div>',
            $header
        ) ?? $header;

        $mobileAuth = '<div class="login-box flex align-items-center"><a href="' . $accountUrl . '"><i data-lucide="user" style="width:14px;height:14px;"></i> ' . $displayName . ' (لوحة التحكم)</a><span>/</span><a href="/logout">تسجيل الخروج</a></div>';

        return preg_replace(
            '~<div class="login-box flex align-items-center">.*?</div>~is',
            $mobileAuth,
            $header
        ) ?? $header;
    }

    private function pageSpeedStylesUsedBy(string $fragment, string $sourceHtml): string
    {
        preg_match_all('/\bpage_speed_[A-Za-z0-9_-]+\b/', $fragment, $classMatches);
        $classes = array_values(array_unique($classMatches[0] ?? []));

        if ($classes === []) {
            return '';
        }

        $rules = [];
        foreach ($classes as $class) {
            if (preg_match_all('/\.' . preg_quote($class, '/') . '\s*\{[^}]*\}/i', $sourceHtml, $ruleMatches)) {
                foreach ($ruleMatches[0] as $rule) {
                    $rules[$rule] = true;
                }
            }
        }

        return $rules === []
            ? ''
            : '<style data-darli-unified-header>' . implode(' ', array_keys($rules)) . '</style>';
    }

    private function ensureCanonicalHeaderStylesheets(string $html, string $canonicalHtml): string
    {
        $requiredAssets = [
            '/vendor/core/plugins/language/css/language-public.css',
            '/themes/homzen/plugins/bootstrap/css/bootstrap',
            '/themes/homzen/css/animate.min.css',
            '/themes/homzen/plugins/swiper/swiper-bundle.min.css',
            '/themes/homzen/css/style.css',
            '/vendor/core/plugins/real-estate/css/front-auth.css',
            '/vendor/core/plugins/announcement/css/announcement.css',
        ];

        preg_match_all('/<link\b(?=[^>]*\brel\s*=\s*(["\'])stylesheet\1)[^>]*>/i', $canonicalHtml, $linkMatches);
        $missingLinks = [];

        foreach ($linkMatches[0] ?? [] as $link) {
            foreach ($requiredAssets as $asset) {
                if (str_contains($link, $asset) && ! str_contains($html, $asset)) {
                    $missingLinks[$link] = true;
                    break;
                }
            }
        }

        if ($missingLinks === []) {
            return $html;
        }

        $links = '<!-- Canonical properties-header dependencies -->' . implode('', array_keys($missingLinks));

        // On the homepage, its own stylesheet must remain last so the body
        // keeps the established homepage design while the header gains its CSS.
        if (str_contains($html, '/themes/homzen/css/darli-home.css')) {
            return preg_replace(
                '~<link\b[^>]*href=["\'][^"\']*/themes/homzen/css/darli-home\.css[^"\']*["\'][^>]*>~i',
                $links . '$0',
                $html,
                1
            ) ?? $html;
        }

        return preg_replace('/<\/head>/i', $links . '</head>', $html, 1) ?? $html;
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
        if (preg_match('#^(en|ar|fr|id|vi)/(properties|projects|blog)$#', $path)) {
            $candidates[] = $path . '?page=1';
        }

        if (in_array($path, ['properties', 'projects', 'blog'], true)) {
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

        // Also replace URL-encoded occurrences (such as social sharing links: facebook, pinterest, linkedin, whatsapp)
        $encodedOld = rawurlencode('https://homzen.botble.com');
        $encodedBase = rawurlencode($base);
        $html = str_replace($encodedOld, $encodedBase, $html);

        $localizedAjax = $base . '/ajax/render-ui-blocks';
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
        $html = $this->normalizeBrandAssets($html, $request);
        $html = $this->removeDuplicateHeaderBars($html);
        $html = $this->removeHomepageSelector($html);
        $html = $this->normalizeInternalUrls($html, $request);
        $html = $this->linkHeaderBlogNavigation($html, $request);
        $html = $this->activateHeaderNavigationItem($html, $request);
        $html = $this->injectFooterLanguageSwitcher($html, $request);
        $html = $this->sanitizeHeaderLanguageDropdown($html, $request);
        $html = $this->normalizeCurrencyDisplay($html, $request);
        $html = $this->prepareEgyptMap($html, $request);
        $html = $this->synchronizeWishlistPropertyIds($html, $request);
        $html = $this->removeHomepageSelector($html);
        $html = $this->applyLocalizedSeo($html, $request);
        $html = $this->normalizeBrandColours($html);
        $html = $this->renderHomepagePosts($html, $request);
        $html = $this->renderHomepagePartners($html, $request);
        $html = $this->renderAboutUsDynamicContent($html, $request);
        $html = $this->renderOurServicesDynamicContent($html, $request);
        $html = $this->injectCustomAssets($html);
        $html = $this->attachImagePlaceholders($html);

        // Cloudflare challenge code belongs to the old host and can cause
        // console errors locally. Removing it does not alter the design.
        $html = $this->removeCloudflareChallengeScript($html);

        return $this->deduplicateFormFieldIds($html);
    }

    private function renderHomepagePosts(string $html, Request $request): string
    {
        if (! str_contains($html, 'darli-blog-section')) {
            return $html;
        }

        $locale = $this->localeFromRequest($request);
        $posts = Post::query()
            ->with('translations')
            ->where('status', 'published')
            ->where(fn($query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(function (Post $post) use ($locale): array {
                $translation = $post->translation($locale);
                $image = $post->featured_image;

                if (! $image) {
                    $image = '/themes/homzen/images/darli-placeholder.png';
                } elseif (! str_starts_with($image, 'http')) {
                    $image = str_starts_with($image, 'storage/')
                        ? '/' . ltrim($image, '/')
                        : '/storage/' . ltrim($image, '/');
                }

                return [
                    'title' => (string) ($translation?->title ?? ''),
                    'excerpt' => Str::limit(strip_tags((string) ($translation?->excerpt ?: $translation?->content)), 125),
                    'url' => '/blog/' . ($translation?->slug ?: $post->id),
                    'image' => $image,
                    'date' => $post->published_at?->locale($locale)->translatedFormat('d F Y') ?? '',
                ];
            });

        try {
            $section = view('legacy.homepage-posts', compact('posts', 'locale'))->render();

            return preg_replace(
                '~<section\b[^>]*class=["\'][^"\']*darli-blog-section[^"\']*["\'][^>]*>.*?</section>~is',
                $section,
                $html,
                1
            ) ?? $html;
        } catch (\Throwable) {
            return $html;
        }
    }

    private function renderHomepagePartners(string $html, Request $request): string
    {
        if (! str_contains($html, 'darli-partners-section')) {
            return $html;
        }

        $locale = $this->localeFromRequest($request);

        // Fetch active partner companies & agents
        $agentQuery = \App\Models\AgentProfile::query()
            ->with(['translations', 'user', 'agency'])
            ->where('status', 'active');

        $agents = $agentQuery->get()
            ->filter(fn (\App\Models\AgentProfile $a) => $a->featured_on_homepage)
            ->sortBy(function (\App\Models\AgentProfile $a) {
                // Priority order: 50, 51, 52, 54, 53 first to match reference design exactly
                $priority = [50 => 1, 51 => 2, 52 => 3, 54 => 4, 53 => 5];
                return $priority[$a->id] ?? ($a->id + 100);
            })
            ->values();

        if ($agents->isEmpty()) {
            $agents = \App\Models\AgentProfile::query()
                ->with(['translations', 'user', 'agency'])
                ->where('status', 'active')
                ->limit(6)
                ->get();
        }

        $partners = $agents->map(function (\App\Models\AgentProfile $agent) use ($locale): array {
            $tr = $agent->translation($locale);
            $name = $tr?->display_name ?: ($agent->agency?->name ?: ($agent->user?->name ?: $agent->slug));

            return [
                'id' => $agent->id,
                'name' => $name,
                'job_title' => $agent->job_title ?: ($agent->agency?->name ?: 'وساطة وتسويق عقاري'),
                'city' => $agent->city,
                'url' => '/agents/' . $agent->slug,
                'phone' => $agent->user?->phone,
                'whatsapp' => $agent->whatsapp ?: $agent->user?->phone,
                'email' => $agent->user?->email,
                'icon' => $agent->icon,
                'icon_color' => $agent->icon_color,
                'custom_logo' => (!empty($agent->avatar_path) && !str_contains($agent->avatar_path, 'default') && !str_contains($agent->avatar_path, 'placeholder'))
                    ? (str_starts_with($agent->avatar_path, 'http') ? $agent->avatar_path : asset($agent->avatar_path))
                    : null,
            ];
        });

        $sectionTag = setting('partners.tag', '— شركاؤنا المميزون —');
        $sectionTitle = setting('partners.title', 'تعرف على وكلائنا');
        $sectionSubtitle = setting('partners.subtitle', 'تعاون مع نخبة من شركات الوساطة العقارية الموثوقة في مختلف مناطق المملكة');

        try {
            $section = view('legacy.homepage-partners', [
                'partners' => $partners,
                'sectionTag' => $sectionTag,
                'sectionTitle' => $sectionTitle,
                'sectionSubtitle' => $sectionSubtitle,
                'locale' => $locale,
            ])->render();

            return preg_replace(
                '~<section\b[^>]*class=["\'][^"\']*darli-partners-section[^"\']*["\'][^>]*>.*?</section>~is',
                $section,
                $html,
                1
            ) ?? $html;
        } catch (\Throwable $e) {
            return $html;
        }
    }

    private function renderAboutUsDynamicContent(string $html, Request $request): string
    {
        $path = trim($request->path(), '/');
        if (! str_ends_with($path, 'about-us') && $path !== 'about-us') {
            return $html;
        }

        $locale = $this->localeFromRequest($request);

        $agents = AgentProfile::query()
            ->with(['translations', 'user', 'agency'])
            ->where('status', 'active')
            ->limit(4)
            ->get()
            ->map(function (AgentProfile $agent) use ($locale): array {
                $translation = $agent->translation($locale);
                $name = $translation?->display_name ?? $agent->user?->name ?? $agent->slug;
                $avatar = $agent->avatar_path ? asset($agent->avatar_path) : '/themes/homzen/images/darli-placeholder.png';

                return [
                    'name' => $name,
                    'url' => '/agents/' . $agent->slug,
                    'image' => $avatar,
                    'job_title' => $agent->job_title ?: ($agent->agency ? $agent->agency->name : 'وكيل عقاري معتمد'),
                    'email' => $agent->user?->email,
                    'phone' => $agent->user?->phone,
                    'verified' => (bool) $agent->verified_at,
                    'properties_count' => Property::query()->where('owner_id', $agent->user_id)->where('status', 'published')->count(),
                ];
            });

        $testimonials = Testimonial::query()
            ->with('translations')
            ->where('status', true)
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->map(function (Testimonial $testimonial) use ($locale): array {
                $translation = $testimonial->translation($locale);

                return [
                    'name' => $translation?->name ?? '',
                    'position' => $translation?->position ?? 'مستثمر عقاري',
                    'content' => $translation?->content ?? '',
                    'image' => $testimonial->image_path ? asset($testimonial->image_path) : '/storage/avatars/1.jpg',
                    'rating' => min(max($testimonial->rating, 1), 5),
                ];
            });

        try {
            $rendered = view('legacy.about-us-content', [
                'agents' => $agents,
                'testimonials' => $testimonials,
                'locale' => $locale,
            ])->render();

            $p1 = '~<div class="ck-content[^"]*about-page-wrapper[^"]*">.*?</div>\s*<!-- Interactive Scripts for About Us -->\s*<script>.*?</script>~is';
            $p2 = '~<div class="ck-content[^>]*>.*?</div>(?=<div class="modal fade" id="modalLogin")~is';

            return preg_match($p1, $html) ? preg_replace($p1, $rendered, $html) : preg_replace($p2, $rendered, $html);
        } catch (\Throwable) {
            return $html;
        }
    }

    private function renderOurServicesDynamicContent(string $html, Request $request): string
    {
        $path = trim($request->path(), '/');
        if (! str_ends_with($path, 'our-services') && $path !== 'our-services') {
            return $html;
        }

        $locale = $this->localeFromRequest($request);

        $heroTitle = (string) setting('services.hero_title', $locale === 'en' ? 'Our Services' : 'خدماتنا');
        $heroSubtitle = (string) setting('services.hero_subtitle', $locale === 'en'
            ? "Integrated real estate solutions fulfilling all your needs\nin Beni Suef and beyond"
            : "حلول عقارية متكاملة تلبي جميع احتياجاتك\nفي بني سويف وما حولها");

        $whyTag = (string) setting('services.why_tag', $locale === 'en' ? 'Our Services' : 'خدماتنا');
        $whyTitle = (string) setting('services.why_title', $locale === 'en' ? 'Why Choose Darli?' : 'لماذا تختار دارلي؟');
        $whyDesc = (string) setting('services.why_desc', $locale === 'en'
            ? 'We provide comprehensive real estate solutions and deep local expertise in Beni Suef market to help you achieve your goals with ease and confidence.'
            : 'نقدم لك حلولاً عقارية متكاملة وخبرة محلية عميقة في سوق بني سويف لمساعدتك في تحقيق أهدافك العقارية بسهولة وأمان.');

        $badgeTitle = (string) setting('services.badge_title', $locale === 'en' ? 'Your Real Estate Future Starts Here' : 'مستقبلك العقاري يبدأ هنا');
        $badgeDesc = (string) setting('services.badge_desc', $locale === 'en'
            ? 'Your trusted partner in buying, selling, and renting properties in Beni Suef'
            : 'شريكك الموثوق في شراء وبيع وتأجير العقارات في بني سويف');

        $s1Title = (string) setting('services.s1_title', $locale === 'en' ? 'Buy a Home' : 'شراء منزل');
        $s1Desc = (string) setting('services.s1_desc', $locale === 'en'
            ? 'Discover top homes for sale in Beni Suef with comprehensive guidance along your journey.'
            : 'اكتشف أفضل المنازل المعروضة للبيع في بني سويف مع دعم كامل طوال رحلتك الشرائية.');
        $s1Btn = (string) setting('services.s1_btn', $locale === 'en' ? 'Learn More' : 'اعرف المزيد');

        $s2Title = (string) setting('services.s2_title', $locale === 'en' ? 'Rent a Home' : 'استئجار منزل');
        $s2Desc = (string) setting('services.s2_desc', $locale === 'en'
            ? 'A diverse collection of homes and apartments for rent tailored to your lifestyle and budget.'
            : 'مجموعة متنوعة من المنازل والشقق للإيجار بما يناسب احتياجاتك وميزانيتك.');
        $s2Btn = (string) setting('services.s2_btn', $locale === 'en' ? 'Learn More' : 'اعرف المزيد');

        $s3Title = (string) setting('services.s3_title', $locale === 'en' ? 'Sell a Home' : 'بيع منزل');
        $s3Desc = (string) setting('services.s3_desc', $locale === 'en'
            ? 'Sell your property at the best price with professional marketing and reach to serious buyers.'
            : 'قم ببيع عقارك بأفضل سعر مع تسويق احترافي ووصول واسع للمشترين المحتملين.');
        $s3Btn = (string) setting('services.s3_btn', $locale === 'en' ? 'Learn More' : 'اعرف المزيد');

        $testiTag = (string) setting('services.testi_tag', $locale === 'en' ? 'Client Reviews' : 'آراء عملائنا');
        $testiTitle = (string) setting('services.testi_title', $locale === 'en' ? 'What People Say' : 'ما يقوله الناس');
        $testiSubtitle = (string) setting('services.testi_subtitle', $locale === 'en'
            ? 'We pride ourselves on our clients trust and always strive to deliver the best real estate experience in Beni Suef.'
            : 'نفخر بثقة عملائنا ونسعى دائمًا لتقديم أفضل تجربة عقارية في بني سويف.');

        $ctaTag = (string) setting('services.cta_tag', $locale === 'en' ? 'Be Part of Darli Community' : 'كن جزءاً من مجتمع دارلي');
        $ctaTitle = (string) setting('services.cta_title', $locale === 'en' ? 'List your properties on Darli now' : 'قم بإدراج عقاراتك على Darli الآن');
        $ctaSubtitle = (string) setting('services.cta_subtitle', $locale === 'en'
            ? 'Reach thousands of property seekers in Beni Suef and start maximizing returns on your property.'
            : 'وصل إلى آلاف الباحثين عن العقارات في بني سويف وابدأ في تحقيق أفضل العوائد لعقارك.');
        $ctaBtnText = (string) setting('services.cta_btn_text', $locale === 'en' ? 'Start Now' : 'ابدأ الآن');
        $ctaF1 = (string) setting('services.cta_f1', $locale === 'en' ? "Broad reach to buyers\nand tenants" : "وصول لعدد كبير\nمن المشترين والمستأجرين");
        $ctaF2 = (string) setting('services.cta_f2', $locale === 'en' ? "Simple and fast\nlisting steps" : "خطوات بسيطة\nوسريعة للإدراج");
        $ctaF3 = (string) setting('services.cta_f3', $locale === 'en' ? "Dedicated 24/7\nexpert support" : "دعم متخصص\nعلى مدار الساعة");

        // Defaults matching reference image
        $defaultTestimonials = [
            [
                'name' => $locale === 'en' ? 'Amr Khaled' : 'عمرو خالد',
                'position' => $locale === 'en' ? 'Property Buyer' : 'مشتري عقار',
                'content' => $locale === 'en'
                    ? 'Darli platform made searching for a home in Beni Suef a seamless and enjoyable experience. Highly recommended for trusted properties.'
                    : 'منصة دارلي جعلت عملية البحث عن منزل في بني سويف تجربة سهلة وممتعة. أنصح بها لجميع الباحثين عن عقارات موثوقة.',
                'image' => asset('themes/homzen/images/avatar/avatar-amr.png'),
                'rating' => 5,
            ],
            [
                'name' => $locale === 'en' ? 'Sara Ali' : 'سارة علي',
                'position' => $locale === 'en' ? 'Property Seller' : 'بائعة عقار',
                'content' => $locale === 'en'
                    ? 'The Darli team helped me sell my home swiftly and at the right price. Exceptional professionalism and ongoing support throughout.'
                    : 'ساعدني فريق دارلي في بيع منزلي بسرعة وبسعر مناسب. احترافية عالية ومتابعة مستمرة من البداية للنهاية.',
                'image' => asset('themes/homzen/images/avatar/avatar-sara.png'),
                'rating' => 5,
            ],
            [
                'name' => $locale === 'en' ? 'Ahmed Mohamed' : 'أحمد محمد',
                'position' => $locale === 'en' ? 'Property Tenant' : 'مستأجر عقار',
                'content' => $locale === 'en'
                    ? 'My experience with Darli was excellent. I found the right home for my family easily. The site is user-friendly and support was great.'
                    : 'تجربتي مع دارلي كانت ممتازة. وجدت المنزل المناسب لعائلتي بسهولة. الموقع سهل الاستخدام وفريق الدعم متعاون جدًا.',
                'image' => asset('themes/homzen/images/avatar/avatar-ahmed.png'),
                'rating' => 5,
            ],
        ];

        $dbTestimonials = Testimonial::query()
            ->with('translations')
            ->where('status', true)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        $testimonials = [];
        foreach ($defaultTestimonials as $idx => $fallback) {
            $model = $dbTestimonials->get($idx);
            if ($model) {
                $trans = $model->translation($locale);
                $testimonials[] = [
                    'name' => $trans?->name ?: $fallback['name'],
                    'position' => $trans?->position ?: $fallback['position'],
                    'content' => $trans?->content ?: $fallback['content'],
                    'image' => $model->image_path ? asset($model->image_path) : $fallback['image'],
                    'rating' => min(max($model->rating, 1), 5),
                ];
            } else {
                $testimonials[] = $fallback;
            }
        }

        try {
            $rendered = view('legacy.our-services-content', [
                'heroTitle' => $heroTitle,
                'heroSubtitle' => $heroSubtitle,
                'whyTag' => $whyTag,
                'whyTitle' => $whyTitle,
                'whyDesc' => $whyDesc,
                'badgeTitle' => $badgeTitle,
                'badgeDesc' => $badgeDesc,
                's1Title' => $s1Title,
                's1Desc' => $s1Desc,
                's1Btn' => $s1Btn,
                's2Title' => $s2Title,
                's2Desc' => $s2Desc,
                's2Btn' => $s2Btn,
                's3Title' => $s3Title,
                's3Desc' => $s3Desc,
                's3Btn' => $s3Btn,
                'testiTag' => $testiTag,
                'testiTitle' => $testiTitle,
                'testiSubtitle' => $testiSubtitle,
                'testimonials' => $testimonials,
                'ctaTag' => $ctaTag,
                'ctaTitle' => $ctaTitle,
                'ctaSubtitle' => $ctaSubtitle,
                'ctaBtnText' => $ctaBtnText,
                'ctaF1' => $ctaF1,
                'ctaF2' => $ctaF2,
                'ctaF3' => $ctaF3,
                'locale' => $locale,
            ])->render();

            $patterns = [
                '~<section[^>]*class="[^"]*(?:flat-title-page|services-hero-section)[^"]*"[^>]*>.*?</section>\s*<div class="ck-content[^>]*>.*?</div>(?=<div class="modal fade" id="modalLogin")~is',
                '~<section[^>]*class="[^"]*(?:flat-title-page|services-hero-section)[^"]*"[^>]*>.*?</section>\s*<div class="ck-content[^"]*services-page-wrapper[^"]*">.*?</div>\s*(?:<script id="darli-services-script">.*?</script>)?~is',
                '~<div class="ck-content[^"]*services-page-wrapper[^"]*">.*?</div>\s*(?:<script id="darli-services-script">.*?</script>)?~is',
                '~<div class="ck-content[^>]*>.*?</div>(?=<div class="modal fade" id="modalLogin")~is',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html)) {
                    return preg_replace($pattern, $rendered, $html, 1);
                }
            }

            return $html;
        } catch (\Throwable) {
            return $html;
        }
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

    /** Keep the header's logo family and favicon authoritative everywhere, while removing misplaced logos on cards/avatars. */
    private function normalizeBrandAssets(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $logoLanguage = $locale === 'ar' ? 'ar' : 'en';
        $lightLogo = '/themes/homzen/images/logo/darli-logo-' . $logoLanguage . '.png';
        $darkLogo = '/themes/homzen/images/logo/darli-logo-' . $logoLanguage . '-dark.png';
        $favicon = '/themes/homzen/images/logo/favicon.png';
        $placeholder = $locale === 'ar'
            ? '/themes/homzen/images/darli-placeholder.png'
            : '/themes/homzen/images/darli-placeholder-en.png';

        $brandContainers = [
            'logo-box',
            'nav-logo',
            'footer-logo',
            'navbar-brand',
            'top-header',
            'site-logo',
            'darli-logo',
            'darli-footer',
            'darli-footer-about',
            'footer',
        ];
        $offset = 0;

        $html = preg_replace_callback(
            '/(<img\b[^>]*\bsrc\s*=\s*)(["\'])([^"\']*(?:themes\/homzen\/images\/logo\/darli-logo-(?:ar|en)(?:-dark)?\.png|storage\/general\/(?:logo|white-logo)[^"\']*))\2([^>]*>)/i',
            function (array $matches) use ($html, &$offset, $lightLogo, $darkLogo, $placeholder, $brandContainers): string {
                $fullTag = $matches[0];
                $pos = strpos($html, $fullTag, $offset);
                if ($pos !== false) {
                    $offset = $pos + strlen($fullTag);
                } else {
                    $pos = strpos($html, $fullTag);
                }
                $surrounding = $pos !== false ? substr($html, max(0, $pos - 200), 200) : '';

                $isBrandContainer = false;
                foreach ($brandContainers as $bc) {
                    if (str_contains($surrounding, $bc)) {
                        $isBrandContainer = true;
                        break;
                    }
                }

                if (! $isBrandContainer) {
                    if (str_contains($surrounding, 'avatar')) {
                        return '<img src="/storage/avatars/1.jpg" alt="User" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src=\'' . $placeholder . '\';">';
                    }

                    return '<img src="' . $placeholder . '" alt="Property" loading="lazy" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.src=\'' . $placeholder . '\';">';
                }

                $isFooter = str_contains(strtolower($surrounding), 'footer');
                $replacement = $isFooter ? $darkLogo : $lightLogo;

                return $matches[1] . $matches[2] . $replacement . $matches[2] . $matches[4];
            },
            $html
        ) ?? $html;

        return preg_replace_callback(
            '/<link\b(?=[^>]*\brel\s*=\s*(["\'])[^"\']*\b(?:shortcut\s+)?icon\b[^"\']*\1)[^>]*>/i',
            static function (array $matches) use ($favicon): string {
                $tag = $matches[0];

                if (preg_match('/\bhref\s*=\s*(["\'])[^"\']*\1/i', $tag)) {
                    return preg_replace('/\bhref\s*=\s*(["\'])[^"\']*\1/i', 'href="' . $favicon . '"', $tag, 1) ?? $tag;
                }

                return rtrim($tag, '>') . ' href="' . $favicon . '">';
            },
            $html
        ) ?? $html;
    }

    private function removeDuplicateHeaderBars(string $html): string
    {
        // The rebuilt homepage has its own Darli top bar before the canonical
        // legacy header. Once the shared header is injected this becomes a
        // duplicate first row, so remove the whole custom block.
        return preg_replace(
            '~<div\b[^>]*class\s*=\s*(["\'])[^"\']*\bdarli-topbar\b[^"\']*\1[^>]*>.*?(?=<style\b[^>]*data-darli-unified-header|<div\b[^>]*class\s*=\s*(["\'])[^"\']*\btop-header\b)~is',
            '',
            $html,
            1
        ) ?? $html;
    }

    private function normalizeInternalUrls(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $supported = config('locales.supported', ['ar', 'en']);
        $query = $request->getQueryString();

        return preg_replace_callback(
            '/\b(href|action|data-url)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i',
            function (array $matches) use ($locale, $supported, $query, $request): string {
                $url = $matches[2] ?? $matches[3] ?? $matches[4] ?? '';

                if (
                    $url === ''
                    || str_starts_with($url, '#')
                    || str_contains($url, '__')
                    || preg_match('#^(?:data:|mailto:|tel:|javascript:|//)#i', $url)
                ) {
                    return $matches[0];
                }

                if (str_contains($url, '#modalLogin')) {
                    return $matches[1] . '="#modalLogin" data-bs-toggle="modal" data-bs-target="#modalLogin"';
                }

                if (str_contains($url, '#modalRegister')) {
                    return $matches[1] . '="#modalRegister" data-bs-toggle="modal" data-bs-target="#modalRegister"';
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

                // If path points to known root or top-level application endpoints, avoid relative prepending
                $trimmedPath = ltrim($path, './');
                $topLevelSections = [
                    'wishlist',
                    'properties',
                    'projects',
                    'agents',
                    'blog',
                    'news',
                    'careers',
                    'about-us',
                    'contact-us',
                    'our-services',
                    'pricing-plans',
                    'faqs',
                    'privacy-policy',
                    'cookie-policy',
                    'property-category',
                    'tag',
                    'login',
                    'register',
                    'password',
                    'account',
                    'currency/switch',
                    'feed'
                ];

                $isTopLevel = false;
                foreach ($topLevelSections as $section) {
                    if ($trimmedPath === $section || str_starts_with($trimmedPath, $section . '/')) {
                        $isTopLevel = true;
                        $path = '/' . $trimmedPath;
                        break;
                    }
                }

                if (! $isTopLevel) {
                    $path = $this->resolveRelativePath($path, $request->path());
                }

                $clean = ltrim($path, '/');

                // Strip any leading language prefix (ar, en, vi, fr, id)
                $clean = preg_replace('#^(?:ar|en|vi|fr|id)(?:/|$)#i', '', $clean) ?? $clean;

                // Normalize homepages to root
                if ($clean === '' || preg_match('#^homepage-[1-5]$#i', $clean)) {
                    $clean = '';
                } elseif ($this->isAssetPath($clean) || preg_match('#^(?:admin|api|up|language|currency/switch)(?:/|$)#i', $clean)) {
                    // Assets and non-localized application endpoints stay at root.
                } else {
                    $clean = $this->localizedUrlResolver->translateEntityPath($clean);
                    $clean = preg_replace('#^(?:ar|en|vi|fr|id)(?:/|$)#i', '', $clean) ?? $clean;
                }

                $targetQuery = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
                $fragment = isset($parts['fragment']) && $parts['fragment'] !== '' ? '#' . $parts['fragment'] : '';
                $outPath = $clean === '' ? '/' : '/' . $clean;

                return $matches[1] . '="' . $outPath . $targetQuery . $fragment . '"';
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
            '$1"/blog"$4',
            $html
        ) ?? $html;
    }

    private function navigationSectionFromPath(string $path): string
    {
        $path = (string) (parse_url($path, PHP_URL_PATH) ?: $path);
        $path = trim($path, '/');
        $supported = config('locales.supported', ['ar', 'en']);

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
                $classes = array_values(array_filter($classes, static fn(string $item): bool => $item !== $class));

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

    private function sanitizeHeaderLanguageDropdown(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $redirect = $request->getRequestUri();

        $pattern = '#<li\b[^>]*>\s*<a\b[^>]*class=["\'][^"\']*dropdown-item[^"\']*["\'][^>]*>.*?</li>#is';

        $html = preg_replace_callback($pattern, function (array $matches) use ($redirect): string {
            $item = $matches[0];
            if (
                str_contains($item, 'flags_vn') ||
                str_contains($item, 'flags_fr') ||
                str_contains($item, 'flags_id') ||
                str_contains($item, 'Tiếng Việt') ||
                str_contains($item, 'Français') ||
                str_contains($item, 'Bahasa Indonesia') ||
                str_contains($item, '/vi/') ||
                str_contains($item, '/fr/') ||
                str_contains($item, '/id/')
            ) {
                return '';
            }

            if (str_contains($item, 'Arabic') || str_contains($item, 'العربية')) {
                return '<li><a class="dropdown-item" href="/language/switch/ar?redirect=' . rawurlencode($redirect) . '"><span>العربية</span></a></li>';
            }

            if (str_contains($item, 'English') || str_contains($item, 'الإنجليزية')) {
                if (! in_array('en', config('locales.supported', []), true)) {
                    return '';
                }

                return '<li><a class="dropdown-item" href="/language/switch/en?redirect=' . rawurlencode($redirect) . '"><span>English</span></a></li>';
            }

            return $item;
        }, $html) ?? $html;

        if ($locale === 'ar') {
            $html = str_replace(
                ['</svg> Arabic </a>', '</svg> Arabic</a>', '>Arabic </a>', '>Arabic</a>', '> Arabic </a>', '> Arabic</a>'],
                ['</svg> العربية </a>', '</svg> العربية</a>', '>العربية </a>', '>العربية</a>', '> العربية </a>', '> العربية</a>'],
                $html
            );
        }

        return $html;
    }

    private function normalizeCurrencyDisplay(string $html, Request $request): string
    {
        $currencyCode = app(\App\Services\Preferences\VisitorPreferences::class)->currency($request);
        $locale = $this->localeFromRequest($request);

        $egpSvg = '<svg class="darli-currency-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9.5" stroke="#E5B158" stroke-width="1.6" fill="rgba(229,177,88,0.15)"/><circle cx="12" cy="12" r="7.5" stroke="#E5B158" stroke-width="0.8" stroke-dasharray="1.5 1.5"/><text x="12" y="15.5" font-family="\'Almarai\', \'Cairo\', sans-serif" font-size="8.5" font-weight="bold" fill="#E5B158" text-anchor="middle">ج.م</text></svg>';

        $egpSvgEn = '<svg class="darli-currency-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9.5" stroke="#E5B158" stroke-width="1.6" fill="rgba(229,177,88,0.15)"/><circle cx="12" cy="12" r="7.5" stroke="#E5B158" stroke-width="0.8" stroke-dasharray="1.5 1.5"/><text x="12" y="15" font-family="\'Outfit\', sans-serif" font-size="7.5" font-weight="bold" fill="#E5B158" text-anchor="middle">E£</text></svg>';

        $usdSvg = '<svg class="darli-currency-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 6v12M15 9.5a2.5 2.5 0 0 0-2.5-2.5h-1a2.5 2.5 0 0 0 0 5h1a2.5 2.5 0 0 1 0 5h-1A2.5 2.5 0 0 1 9 14.5"/></svg>';

        $eurSvg = '<svg class="darli-currency-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M15.5 8.5a4.5 4.5 0 0 0-7 3.5 4.5 4.5 0 0 0 7 3.5M7 11h6M7 13h6"/></svg>';

        $activeIcon = match ($currencyCode) {
            'USD' => $usdSvg,
            'EUR' => $eurSvg,
            default => ($locale === 'ar' ? $egpSvg : $egpSvgEn),
        };

        $activeLabel = match ($currencyCode) {
            'USD' => 'USD',
            'EUR' => 'EUR',
            default => ($locale === 'ar' ? 'ج.م' : 'EGP'),
        };

        $currPrefix = '/' . $locale;

        if ($locale === 'ar') {
            $egpItemText = 'ج.م (الجنيه المصري)';
            $usdItemText = 'USD ($ الدولار الأمريكي)';
            $eurItemText = 'EUR (€ اليورو)';
        } else {
            $egpItemText = 'EGP (Egyptian Pound)';
            $usdItemText = 'USD ($ US Dollar)';
            $eurItemText = 'EUR (€ Euro)';
        }

        $newDropdown = '<span class="darli-fixed-currency" aria-label="Egyptian Pound">EGP</span>';

        // 1. Replace the currency dropdowns (desktop and mobile) safely without overshooting
        $dropdownPattern = '~<div\b[^>]*class\s*=\s*(["\'])[^"\']*\bdropdown\b[^"\']*\1[^>]*>.*?<ul\b[^>]*class\s*=\s*(["\'])[^"\']*\bdropdown-menu\b[^"\']*\2[^>]*>.*?</ul>\s*</div>~is';
        $html = preg_replace_callback(
            $dropdownPattern,
            static fn(array $matches): string => str_contains($matches[0], '/currency/switch/')
                ? $newDropdown
                : $matches[0],
            $html
        ) ?? $html;

        // 2. If currency is EGP (default), localize all prices and symbols
        if ($currencyCode === 'EGP') {
            $symbol = $locale === 'ar' ? 'ج.م' : 'EGP';

            // Filter range slider attributes
            $html = preg_replace('/\bdata-currency-symbol="[^"]*"/i', 'data-currency-symbol="' . $symbol . '"', $html) ?? $html;
            $html = preg_replace('/\bdata-currency-prefix-symbol="1"/i', 'data-currency-prefix-symbol=""', $html) ?? $html;

            // Formatted dollar prices in HTML: e.g. $628,300 -> 628,300 ج.م
            $html = preg_replace('/(?<=[>"\s])\$([0-9][0-9,.]*)/i', '$1 ' . $symbol, $html) ?? $html;

            // Mortgage calculator suffixes
            $html = str_replace('<span class="mortgage-calculator__input-suffix">$</span>', '<span class="mortgage-calculator__input-suffix">' . $symbol . '</span>', $html);
            $html = preg_replace('/(\b0)\s*\$/i', '$1 ' . $symbol, $html) ?? $html;
        }

        return $html;
    }

    private function applyLocalizedSeo(string $html, Request $request): string
    {
        $locale = $this->localeFromRequest($request);
        $direction = in_array($locale, config('locales.rtl', []), true) ? 'rtl' : 'ltr';
        $base = rtrim($request->getSchemeAndHttpHost(), '/');
        $canonical = $base;

        $html = preg_replace('/<html\b[^>]*>/i', '<html lang="' . $locale . '" dir="' . $direction . '">', $html, 1) ?? $html;

        if ($request->routeIs('home')) {
            $html = str_replace(
                str_replace('/', '\\/', $base . '/homepage-4'),
                str_replace('/', '\\/', $canonical),
                $html
            );
            if (preg_match('#<link\b[^>]*rel\s*=\s*(["\']?)canonical\1[^>]*>#i', $html)) {
                $html = preg_replace('#<link\b[^>]*rel\s*=\s*(["\']?)canonical\1[^>]*>#i', '<link rel="canonical" href="' . $canonical . '">', $html, 1) ?? $html;
            } else {
                $html = preg_replace('/<\/head>/i', '<link rel="canonical" href="' . $canonical . '">' . "\n" . '</head>', $html, 1) ?? $html;
            }
            $html = preg_replace('#(<meta\b[^>]*property\s*=\s*(["\']?)og:url\2[^>]*content\s*=\s*)(["\'])[^"\']*\3([^>]*>)#i', '$1$3' . $canonical . '$3$4', $html, 1) ?? $html;

            $hreflang = '<link rel="alternate" hreflang="ar" href="' . $base . '">' . "\n" . '<link rel="alternate" hreflang="x-default" href="' . $base . '">';
            $html = preg_replace('/<\/head>/i', $hreflang . "\n" . '</head>', $html, 1) ?? $html;
        }

        return $html;
    }

    /**
     * Two brand colours in the mirror are literal values that no stylesheet
     * can override:
     *
     *  - the login page writes its footer background as an inline
     *    `style="... !important"` declaration, and an inline `!important`
     *    beats any author stylesheet, so the diagonal banner image survived;
     *  - the mortgage-calculator widget paints itself from a JavaScript
     *    config object (`primaryColor: #e31837`).
     *
     * Both are normalised here so darli-unified.css remains authoritative.
     */
    private function normalizeBrandColours(string $html): string
    {
        // Keep <footer> surfaces flat brand navy.
        $html = preg_replace_callback(
            '/<footer\b[^>]*>/i',
            static function (array $matches): string {
                return preg_replace_callback(
                    '/\bstyle\s*=\s*(["\'])(.*?)\1/is',
                    static function (array $style): string {
                        $clean = preg_replace(
                            '/\s*background(?:-image|-color|-position|-repeat|-size|-attachment)?\s*:\s*[^;]*(?:;|$)/i',
                            '',
                            $style[2]
                        ) ?? '';
                        $clean = trim(preg_replace('/\s{2,}/', ' ', $clean) ?? $clean, " ;\t\n\r\0\x0B");

                        return $clean === '' ? '' : 'style=' . $style[1] . $clean . $style[1];
                    },
                    $matches[0]
                ) ?? $matches[0];
            },
            $html
        ) ?? $html;

        // Recolour the mortgage calculator widget to the brand gold.
        return str_replace(
            ['"primaryColor":"#e31837"', '"primaryColor": "#e31837"', "'primaryColor':'#e31837'"],
            ['"primaryColor":"#C99A45"', '"primaryColor": "#C99A45"', "'primaryColor':'#C99A45'"],
            $html
        );
    }

    private function injectCustomAssets(string $html): string
    {
        $themeBoot = <<<'HTML'
<script id="homzen-theme-bootstrap">(function(){try{var t=localStorage.getItem('homzen-theme');if(t!=='dark'&&t!=='light'){var c=document.cookie.match(/(?:^|;\s*)homzen_theme=(dark|light)(?:;|$)/);t=c?c[1]:null}if(t!=='dark'&&t!=='light'){t=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'}document.documentElement.dataset.theme=t}catch(e){document.documentElement.dataset.theme='light'}})();</script>
HTML;

        /*
         * darli-unified.css is loaded last on purpose: it is the single
         * source of truth for the site header, footer and brand colours and
         * must win the cascade against style.css, dark-mode.css and the
         * per-page generated `.page_speed_*` inline rules.
         */
        $customStyles = <<<'HTML'
<link rel="stylesheet" href="/themes/homzen/css/rtl-custom.css?v=2.0.1">
<link rel="stylesheet" href="/themes/homzen/css/locale-switcher.css?v=1.0.0">
<link rel="stylesheet" href="/themes/homzen/css/dark-mode.css?v=2.0.1">
<link rel="stylesheet" href="/themes/homzen/css/darli-unified.css?v=2.1.8">
<link rel="stylesheet" href="/themes/homzen/css/darli-about-us.css?v=2.2.0">
<link rel="stylesheet" href="/themes/homzen/css/darli-services.css?v=1.0.0">
HTML;

        $html = preg_replace('/<head([^>]*)>/i', '<head$1>' . $themeBoot, $html, 1) ?? $html;
        $html = preg_replace('/<\/head>/i', $customStyles . '</head>', $html, 1) ?? $html;

        // The mirror references two different cache-busting versions of the
        // design system; normalise them so the brand tokens cannot go stale.
        $html = str_replace(
            'darli-design-system.css?v=1.1.0',
            'darli-design-system.css?v=2.0.1',
            $html
        );

        return preg_replace(
            '/<\/body>/i',
            <<<'HTML'
<script id="darli-mobile-menu-bootstrap">
(function () {
    document.addEventListener('click', function (event) {
        var openButton = event.target.closest('.mobile-nav-toggler');
        if (openButton) {
            event.preventDefault();
            document.body.classList.add('mobile-menu-visible');
            openButton.setAttribute('aria-expanded', 'true');
            return;
        }

        if (event.target.closest('.mobile-menu .menu-backdrop, .close-btn')) {
            document.body.classList.remove('mobile-menu-visible');
            var toggle = document.querySelector('.mobile-nav-toggler');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }
    }, true);

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') return;
        document.body.classList.remove('mobile-menu-visible');
        var toggle = document.querySelector('.mobile-nav-toggler');
        if (toggle) toggle.setAttribute('aria-expanded', 'false');
    });
})();
</script>
<script defer src="/vendor/core/plugins/announcement/js/announcement.js?v=2.0.1"></script><script defer src="/themes/homzen/js/darli-mobile-menu.js?v=1.1.2"></script><script defer src="/themes/homzen/js/theme-mode.js?v=2.0.2"></script><script defer src="/themes/homzen/js/wishlist.js?v=2.0.1"></script><script defer src="/themes/homzen/js/map-location.js?v=2.3.4"></script></body>
HTML,
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
        $googleMapsKey = (string) config('services.google_maps.key', '');
        $html = preg_replace('~\bdata-center\s*=\s*["\'][^"\']*["\']~i', 'data-center="[29.0661,31.0994]"', $html, 1) ?? $html;
        $html = preg_replace('~(<[^>]*data-bb-toggle="list-map"[^>]*)>~i', '$1 data-map-theme="light">', $html, 1) ?? $html;
        $html = preg_replace('/\bdata-url\s*=\s*(["\'])[^"\']*\/ajax\/(?:properties|projects)\/map[^"\']*\1/i', 'data-url="/ajax/properties/map"', $html, 1) ?? $html;
        $html = preg_replace('/\bdata-tile-layer\s*=\s*(["\'])[^"\']+\1/i', 'data-tile-layer="https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}"', $html, 1) ?? $html;

        if ($googleMapsKey !== '' && ! str_contains($html, 'maps.googleapis.com/maps/api/js')) {
            $loader = '<script async src="https://maps.googleapis.com/maps/api/js?key=' . rawurlencode($googleMapsKey) . '&amp;loading=async&amp;libraries=places,geometry&amp;v=weekly&amp;language=' . rawurlencode($locale) . '&amp;region=EG"></script>';
            $html = preg_replace('/<\/head>/i', $loader . '</head>', $html, 1) ?? $html;
        }

        return $html;
    }

    private function synchronizeWishlistPropertyIds(string $html, Request $request): string
    {
        $lastSlug = null;
        if (preg_match('~/properties/([^/?#]+)~', '/' . trim($request->path(), '/'), $pathMatch)) {
            $lastSlug = $pathMatch[1];
        }

        return preg_replace_callback(
            '~<a\b[^>]*\bhref\s*=\s*(["\'])/(?:(?:en|ar|fr|id|vi)/)?properties/([^"\'/?#]+)[^"\']*\1[^>]*>|<button\b(?=[^>]*data-bb-toggle\s*=\s*(["\'])add-to-wishlist\3)(?=[^>]*data-type\s*=\s*(["\'])property\4)[^>]*>~i',
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

    private function renderDynamicFooter(string $html, Request $request, SettingsRepository $settings): string
    {
        $locale = $this->localeFromRequest($request);
        if ($locale !== 'ar') {
            return $html;
        }

        $aboutText = e((string) $settings->get('footer.about_text_ar', 'متخصصون في تقديم أرقى الجولات والخدمات العقارية لكل من يحتاجها. تواصل معنا'));
        $address = e((string) $settings->get('contact.address_ar', 'شارع صلاح سالم (تقاطع كورنيش النيل)، مدينة بني سويف، محافظة بني سويف'));
        $phone = e((string) $settings->get('contact.phone', '+20 10 34905812'));
        $cleanPhone = preg_replace('/[^\d+]/', '', $phone);
        $email = e((string) $settings->get('contact.email', 'contact@darlii.eg'));

        $col2Title = e((string) $settings->get('footer.col2_title_ar', 'فئات'));
        $col2Link1 = e((string) $settings->get('footer.col2_link1_text_ar', 'خطط الأسعار'));
        $col2Link2 = e((string) $settings->get('footer.col2_link2_text_ar', 'خدماتنا'));
        $col2Link3 = e((string) $settings->get('footer.col2_link3_text_ar', 'من نحن'));
        $col2Link4 = e((string) $settings->get('footer.col2_link4_text_ar', 'اتصل بنا'));

        $col3Title = e((string) $settings->get('footer.col3_title_ar', 'شركتنا'));
        $col3Link1 = e((string) $settings->get('footer.col3_link1_text_ar', 'عقارات للبيع'));
        $col3Link2 = e((string) $settings->get('footer.col3_link2_text_ar', 'عقارات للإيجار'));
        $col3Link3 = e((string) $settings->get('footer.col3_link3_text_ar', 'سياسة الخصوصية'));
        $col3Link4 = e((string) $settings->get('footer.col3_link4_text_ar', 'وكلاؤنا'));

        $newsletterTitle = e((string) $settings->get('footer.newsletter_title_ar', 'النشرة الإخبارية'));
        $newsletterSubtitle = e((string) $settings->get('footer.newsletter_subtitle_ar', 'جرعتك الأسبوعية/الشهرية من المعرفة والإلهام'));
        $newsletterPlaceholder = e((string) $settings->get('footer.newsletter_placeholder_ar', 'أدخل بريدك الإلكتروني'));

        $copyright = e((string) $settings->get('footer.copyright_ar', '© 2026 دارلي. جميع الحقوق محفوظة.'));
        $termsText = e((string) $settings->get('footer.terms_text_ar', 'شروط الاستخدام'));
        $privacyText = e((string) $settings->get('footer.privacy_text_ar', 'سياسة الخصوصية'));
        $cookieText = e((string) $settings->get('footer.cookie_text_ar', 'سياسة ملفات تعريف الارتباط'));

        // Extract CSRF token from page or Laravel helper
        $csrfToken = '';
        if (preg_match('/<input\b[^>]*name=["\']_token["\'][^>]*value=["\']([^"\']+)["\']/i', $html, $tokenMatch)) {
            $csrfToken = $tokenMatch[1];
        } elseif (function_exists('csrf_token')) {
            try {
                $csrfToken = (string) csrf_token();
            } catch (\Throwable) {
            }
        }

        // 1. Dynamic replacement for classic inner-footer (4 columns)
        $innerFooterPattern = '/<div\b[^>]*class=["\'][^"\']*\binner-footer\b[^"\']*["\'][^>]*>.*?<\/div>\s*(?=<div\b[^>]*class=["\'](?:homzen-footer-languages|bottom-footer)\b)/si';
        if (preg_match($innerFooterPattern, $html)) {
            $innerFooterHtml = <<<HTML
<div class="inner-footer"><div class="container"><div class="row"><div class="col-lg-4 col-md-6"><div class="footer-cl-1"><p class="text-variant-2">{$aboutText}</p><ul class="mt-12"><li class="mt-12 d-flex align-items-center gap-8"><svg xmlns="http://www.w3.org/2000/svg" width=24 height=24 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-variant-2 svg-icon-ti-ti-map-pin page_speed_1278168353"><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg><p class="text-white">{$address}</p></li><li class="mt-12 d-flex align-items-center gap-8"><svg xmlns="http://www.w3.org/2000/svg" width=24 height=24 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-variant-2 svg-icon-ti-ti-phone-call page_speed_1278168353"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" /><path d="M15 7a2 2 0 0 1 2 2" /><path d="M15 3a6 6 0 0 1 6 6" /></svg><p class="text-white" dir="ltr"><a href="tel:{$cleanPhone}" style="color:inherit;text-decoration:none;" dir="ltr">{$phone}</a></p></li><li class="mt-12 d-flex align-items-center gap-8"><svg xmlns="http://www.w3.org/2000/svg" width=24 height=24 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon text-variant-2 svg-icon-ti-ti-mail page_speed_1278168353"><path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" /><path d="M3 7l9 6l9 -6" /></svg><p class="text-white"><a href="mailto:{$email}" style="color:inherit;text-decoration:none;">{$email}</a></p></li></ul></div></div><div class="col-lg-2 col-md-4 col-6"><div class="footer-cl-3"><div class="fw-7 text-white">{$col2Title}</div><ul class="mt-10 navigation-menu-footer"><li><a href="/pricing-plans" title="{$col2Link1}" class="caption-1 text-variant-2" > {$col2Link1} </a></li><li><a href="/our-services" title="{$col2Link2}" class="caption-1 text-variant-2" > {$col2Link2} </a></li><li><a href="/about-us" title="{$col2Link3}" class="caption-1 text-variant-2" > {$col2Link3} </a></li><li><a href="/contact-us" title="{$col2Link4}" class="caption-1 text-variant-2" > {$col2Link4} </a></li></ul></div></div><div class="col-lg-2 col-md-4 col-6"><div class="footer-cl-3"><div class="fw-7 text-white">{$col3Title}</div><ul class="mt-10 navigation-menu-footer"><li><a href="/properties?type=sale" title="{$col3Link1}" class="caption-1 text-variant-2" > {$col3Link1} </a></li><li><a href="/properties?type=rent" title="{$col3Link2}" class="caption-1 text-variant-2" > {$col3Link2} </a></li><li><a href="/privacy-policy" title="{$col3Link3}" class="caption-1 text-variant-2" > {$col3Link3} </a></li><li><a href="/agents" title="{$col3Link4}" class="caption-1 text-variant-2" > {$col3Link4} </a></li></ul></div></div><div class="col-lg-4 col-md-6"><div class="footer-cl-4"><div class="fw-7 text-white"> {$newsletterTitle} </div><p class="mt-12 text-variant-2">{$newsletterSubtitle}</p><form method="POST" action="/newsletter/subscribe" accept-charset="UTF-8" id="botble-newsletter-forms-fronts-newsletter-form" class="subscribe-form mt-12 dirty-check"><input name="_token" type="hidden" value="{$csrfToken}"><div class="mb-3 position-relative"><span class="icon-left icon-mail"></span><input class="" placeholder="{$newsletterPlaceholder}" id="newsletter-email" required="required" name="email" type="email"><button class="" type="submit" title="اشترك"><i class="icon icon-send"></i></button></div><div class="newsletter-message newsletter-success-message page_speed_545063869"></div><div class="newsletter-message newsletter-error-message page_speed_545063869"></div></form></div></div></div></div></div>
HTML;
            $html = preg_replace($innerFooterPattern, $innerFooterHtml . "\n", $html, 1) ?? $html;
        }

        // 2. Dynamic replacement for classic bottom-footer (copyright & bottom links)
        $bottomFooterPattern = '/<div\b[^>]*class=["\'][^"\']*\bbottom-footer\b[^"\']*["\'][^>]*>.*?<\/div>\s*<\/div>\s*<\/div>(?=\s*<\/footer>)/si';
        if (preg_match($bottomFooterPattern, $html)) {
            $bottomFooterHtml = <<<HTML
<div class="bottom-footer"><div class="container"><div class="content-footer-bottom"><div class="copyright text-variant-2">{$copyright}</div><ul class="menu-bottom"><li><a href="/terms-and-conditions" title="{$termsText}" class="caption-1 text-variant-2" > {$termsText} </a></li><li><a href="/privacy-policy" title="{$privacyText}" class="caption-1 text-variant-2" > {$privacyText} </a></li><li><a href="/cookie-policy" title="{$cookieText}" class="caption-1 text-variant-2" > {$cookieText} </a></li></ul></div></div></div>
HTML;
            $html = preg_replace($bottomFooterPattern, $bottomFooterHtml, $html, 1) ?? $html;
        }

        // 3. Dynamic replacement for homepage darli-footer
        if (str_contains($html, 'darli-footer')) {
            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-about[^"\']*["\'][^>]*>[\s\S]*?<p>)(.*?)(<\/p>)/si',
                '${1}' . $aboutText . '${3}',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-bottom[^"\']*["\'][^>]*>\s*<div>)(.*?)(<\/div>)/si',
                '${1}' . $copyright . '${3}',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-bottom-links[^"\']*["\'][^>]*>\s*<a\b[^>]*href=["\'])[^"\']*privacy-policy[^"\']*(["\'][^>]*>)(.*?)(<\/a>)/si',
                '${1}/privacy-policy${2}' . $privacyText . '${4}',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-bottom-links[^"\']*["\'][^>]*>[\s\S]*?<a\b[^>]*href=["\'])[^"\']*(?:cookie-policy|terms-and-conditions)[^"\']*(["\'][^>]*>)(.*?)(<\/a>)/si',
                '${1}/terms-and-conditions${2}' . $termsText . '${4}',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-col[^"\']*["\'][^>]*>\s*<h4>)(?:اشترك في نشرتنا البريدية)(<\/h4>)/si',
                '${1}' . $newsletterTitle . '${2}',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/(<div\b[^>]*class=["\'][^"\']*darli-footer-col[^"\']*["\'][^>]*>[\s\S]*?<p[^>]*>)(?:احصل على آخر العروض والعقارات الجديدة)(<\/p>)/si',
                '${1}' . $newsletterSubtitle . '${2}',
                $html,
                1
            ) ?? $html;

            $html = preg_replace(
                '/(<input\b[^>]*class=["\'][^"\']*darli-newsletter-input[^"\']*["\'][^>]*placeholder=["\'])[^"\']*(["\'])/si',
                '${1}' . $newsletterPlaceholder . '${2}',
                $html,
                1
            ) ?? $html;
        }

        return $html;
    }
}
