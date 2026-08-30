(function () {
    'use strict';

    const storageKey = 'darli-theme';
    const legacyStorageKey = 'homzen-theme';
    const root = document.documentElement;

    function isArabic() {
        const lang = root.getAttribute('lang') || '';
        const dir = root.getAttribute('dir') || '';
        const path = window.location.pathname;
        return lang === 'ar' || dir === 'rtl' || path.startsWith('/ar/') || path === '/ar';
    }

    const labels = isArabic()
        ? { dark: 'تفعيل الوضع الداكن', light: 'تفعيل الوضع الفاتح' }
        : { dark: 'Enable dark mode', light: 'Enable light mode' };

    const iconMarkup = [
        '<svg class="theme-icon-moon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"></path></svg>',
        '<svg class="theme-icon-sun" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"></path></svg>'
    ].join('');

    function currentTheme() {
        return root.dataset.theme === 'dark' ? 'dark' : 'light';
    }

    function updateButtons() {
        const label = currentTheme() === 'dark' ? labels.light : labels.dark;
        document.querySelectorAll('.darli-theme-toggle, .homzen-theme-toggle').forEach(function (button) {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
            button.setAttribute('aria-pressed', currentTheme() === 'dark' ? 'true' : 'false');
        });
    }

    function updateLogos() {
        const isDark = currentTheme() === 'dark';
        const isRtl = isArabic();

        const arLight = '/themes/homzen/images/logo/darli-logo-ar.png';
        const arDark = '/themes/homzen/images/logo/darli-logo-ar-dark.png';
        const enLight = '/themes/homzen/images/logo/darli-logo-en.png';
        const enDark = '/themes/homzen/images/logo/darli-logo-en-dark.png';

        const logoSrc = isRtl
            ? (isDark ? arDark : arLight)
            : (isDark ? enDark : enLight);

        const selectors = [
            '.darli-logo-img',
            '.navbar-brand img',
            '.site-logo img',
            'footer img.darli-logo-img',
            'img[src*="logo"]',
            'img[src*="homzen"]'
        ].join(', ');

        document.querySelectorAll(selectors).forEach(function (logo) {
            // Avoid changing non-brand icons if any
            const src = logo.getAttribute('src') || '';
            if (src.includes('logo') || src.includes('darli') || src.includes('homzen') || logo.classList.contains('darli-logo-img')) {
                logo.setAttribute('src', logoSrc);
            }
        });
    }

    function setTheme(theme) {
        root.dataset.theme = theme;
        try {
            window.localStorage.setItem(storageKey, theme);
            window.localStorage.setItem(legacyStorageKey, theme);
        } catch (error) {
            // Storage can be disabled by browser privacy settings.
        }
        document.cookie = 'darli_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
        document.cookie = 'homzen_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
        updateButtons();
        updateLogos();
        window.dispatchEvent(new CustomEvent('darli:theme-changed', { detail: { theme: theme } }));
    }

    function configureButton(button) {
        if (!button.querySelector('svg')) {
            button.innerHTML = iconMarkup;
        }
        if (button.dataset.darliThemeBound === 'true') {
            return button;
        }

        button.dataset.darliThemeBound = 'true';
        button.addEventListener('click', function () {
            setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });

        return button;
    }

    function createButton() {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'darli-theme-toggle';

        return configureButton(button);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const desktopTarget = document.querySelector('.top-header-right, .native-nav-actions');
        const mobileTarget = document.querySelector('.mobi-icon-box');

        document.querySelectorAll('.darli-theme-toggle, .homzen-theme-toggle').forEach(configureButton);

        if (desktopTarget && !desktopTarget.querySelector('.darli-theme-toggle, .homzen-theme-toggle')) {
            desktopTarget.appendChild(createButton());
        }
        if (mobileTarget && !mobileTarget.querySelector('.darli-theme-toggle, .homzen-theme-toggle')) {
            mobileTarget.prepend(createButton());
        }

        updateButtons();
        updateLogos();
    });

    window.addEventListener('storage', function (event) {
        if ((event.key === storageKey || event.key === legacyStorageKey) && (event.newValue === 'dark' || event.newValue === 'light')) {
            root.dataset.theme = event.newValue;
            updateButtons();
            updateLogos();
        }
    });
})();
