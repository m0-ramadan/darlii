(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var menu = document.querySelector('.mobile-menu');
        var toggle = document.querySelector('.mobile-nav-toggler');
        if (!menu || !toggle) return;

        var locale = document.documentElement.lang || 'ar';
        var isArabic = locale === 'ar';
        var base = '/' + locale;
        var path = window.location.pathname.replace(/\/$/, '') || base;
        var icons = {
            home: '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/>',
            properties: '<path d="M5 21V4h10v17"/><path d="M15 8h4v13"/><path d="M8 8h4M8 12h4M8 16h4M3 21h18"/>',
            projects: '<path d="M4 21V9l8-5 8 5v12"/><path d="M9 21v-7h6v7M3 9h18"/>',
            agents: '<circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2"/><path d="M3 20c0-4 2-6 6-6s6 2 6 6M15 15c4 0 6 2 6 5"/>',
            blog: '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 7h8M8 11h3M14 11h2M8 15h3M14 15h2"/>',
            pages: '<path d="M6 2h8l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/>'
        };
        var labels = isArabic ? {
            home: 'الصفحة الرئيسية', properties: 'العقارات', projects: 'المشاريع',
            agents: 'الوكلاء', blog: 'المدونة', pages: 'الصفحات'
        } : {
            home: 'Home', properties: 'Properties', projects: 'Projects', agents: 'Agents', blog: 'Blog', pages: 'Pages'
        };
        var pageLinks = isArabic ? [
            ['من نحن', base + '/about-us'],
            ['خدماتنا', base + '/our-services'],
            ['تواصل معنا', base + '/contact-us'],
            ['الأسئلة الشائعة', base + '/faqs'],
            ['سياسة الخصوصية', base + '/privacy-policy']
        ] : [
            ['About Us', base + '/about-us'],
            ['Our Services', base + '/our-services'],
            ['Contact Us', base + '/contact-us'],
            ['FAQ', base + '/faqs'],
            ['Privacy Policy', base + '/privacy-policy']
        ];
        var links = [
            ['home', base], ['properties', base + '/properties'], ['projects', base + '/projects'],
            ['agents', base + '/agents'], ['blog', base + '/blog'], ['pages', '#', pageLinks]
        ];
        var outer = menu.querySelector('.menu-outer');

        function renderNavigation() {
            if (!outer) return;

            outer.innerHTML = '<ul class="navigation darli-mobile-navigation">' + links.map(function (item) {
                var key = item[0];
                var href = item[1];
                var children = item[2] || [];
                var active = key === 'home' ? path === base : (children.length
                    ? children.some(function (child) { return path.indexOf(child[1]) === 0; })
                    : path.indexOf(href) === 0);
                var classes = (active ? 'current ' : '') + (children.length ? 'darli-has-submenu' + (active ? ' open' : '') : '');
                var submenu = children.length ? '<ul class="darli-mobile-submenu">' + children.map(function (child) {
                    var childActive = path.indexOf(child[1]) === 0;
                    return '<li class="' + (childActive ? 'current' : '') + '"><a href="' + child[1] + '">' + child[0] + '</a></li>';
                }).join('') + '</ul>' : '';

                return '<li class="' + classes.trim() + '"><a href="' + href + '"' + (children.length ? ' aria-expanded="' + (active ? 'true' : 'false') + '"' : '') + '>' +
                    '<span class="darli-mobile-nav-icon"><svg viewBox="0 0 24 24" aria-hidden="true">' + icons[key] + '</svg></span>' +
                    '<span>' + labels[key] + '</span><span class="darli-mobile-chevron">‹</span></a>' + submenu + '</li>';
            }).join('') + '</ul>';
        }

        renderNavigation();

        // The legacy theme also clones the desktop menu on DOM ready. Re-assert
        // the single Darli menu after it runs so a second navigation is never shown.
        window.setTimeout(renderNavigation, 0);
        window.setTimeout(renderNavigation, 250);
        window.addEventListener('load', renderNavigation, { once: true });

        if (outer) {
            outer.addEventListener('click', function (event) {
                var parentLink = event.target.closest('.darli-has-submenu > a');
                if (!parentLink) return;

                event.preventDefault();
                var parent = parentLink.parentElement;
                var shouldOpen = !parent.classList.contains('open');
                outer.querySelectorAll('.darli-has-submenu.open').forEach(function (item) {
                    item.classList.remove('open');
                    var link = item.querySelector(':scope > a');
                    if (link) link.setAttribute('aria-expanded', 'false');
                });
                parent.classList.toggle('open', shouldOpen);
                parentLink.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            });
        }

        var tools = menu.querySelector('.mobi-icon-box');
        if (tools) {
            tools.innerHTML =
                '<button type="button" class="darli-theme-toggle darli-mobile-theme" aria-label="' + (isArabic ? 'تبديل الوضع الليلي' : 'Toggle dark mode') + '"></button>' +
                '<a class="darli-mobile-tool" href="' + base + '/language">' +
                    '<span class="darli-tool-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/></svg></span>' +
                    '<span class="darli-tool-title">' + (isArabic ? 'اللغة' : 'Language') + '</span>' +
                    '<span class="darli-tool-value">' + (isArabic ? 'العربية 🇸🇦' : 'English 🇺🇸') + '</span><span class="darli-tool-arrow">⌄</span></a>' +
                '<a class="darli-mobile-tool" href="' + base + '/currency/switch/SAR">' +
                    '<span class="darli-tool-icon"><svg viewBox="0 0 24 24"><ellipse cx="12" cy="6" rx="8" ry="3"/><path d="M4 6v6c0 1.7 3.6 3 8 3s8-1.3 8-3V6M4 12v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/></svg></span>' +
                    '<span class="darli-tool-title">' + (isArabic ? 'العملة' : 'Currency') + '</span>' +
                    '<span class="darli-tool-value">' + (isArabic ? 'ريال سعودي (SAR)' : 'Saudi Riyal (SAR)') + '</span><span class="darli-tool-arrow">⌄</span></a>' +
                '<a class="darli-mobile-tool darli-mobile-help" href="' + base + '/contact-us">' +
                    '<span class="darli-tool-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.3 2.3 0 1 1 3.5 2c-.8.5-1.3 1-1.3 2M12 17h.01"/></svg></span>' +
                    '<span class="darli-tool-title">' + (isArabic ? 'مركز المساعدة' : 'Help Center') + '</span><span class="darli-mobile-chevron">‹</span></a>';
        }

        var open = function () {
            document.body.classList.add('mobile-menu-visible');
            toggle.setAttribute('aria-expanded', 'true');
        };
        var close = function () {
            document.body.classList.remove('mobile-menu-visible');
            toggle.setAttribute('aria-expanded', 'false');
        };

        toggle.setAttribute('role', 'button');
        toggle.setAttribute('tabindex', '0');
        toggle.setAttribute('aria-label', isArabic ? 'فتح قائمة التنقل' : 'Open navigation menu');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.addEventListener('click', open);
        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            open();
        }, true);
        toggle.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                open();
            }
        });
        document.querySelectorAll('.mobile-menu .menu-backdrop, .close-btn').forEach(function (element) {
            element.addEventListener('click', close);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && document.body.classList.contains('mobile-menu-visible')) {
                close();
                toggle.focus();
            }
        });
    });
})();
