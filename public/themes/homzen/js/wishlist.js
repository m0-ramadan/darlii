(function () {
    'use strict';

    const storageKey = 'homzen-wishlist-properties';
    const locale = document.documentElement.lang || 'en';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const stateUrl = '/' + locale + '/favorites/state';
    let authenticated = false;
    let activeIds = readGuestIds();
    let properties = [];

    function readGuestIds() {
        try {
            const value = JSON.parse(window.localStorage.getItem(storageKey) || '[]');
            return Array.isArray(value) ? value.map(String) : [];
        } catch (error) {
            return [];
        }
    }

    function writeGuestIds(ids) {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(ids));
        } catch (error) {
            // Storage can be disabled by browser privacy settings.
        }
    }

    function request(url, options) {
        const settings = Object.assign({
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
        }, options || {});

        return window.fetch(url, settings).then(function (response) {
            if (!response.ok) {
                throw new Error('Wishlist request failed');
            }
            return response.json();
        });
    }

    function setButtonState(button, active) {
        button.classList.toggle('active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
        button.setAttribute('aria-label', active ? 'Remove from wishlist' : 'Add to wishlist');
        const icon = button.querySelector('svg');
        if (icon) {
            icon.setAttribute('fill', active ? 'currentColor' : 'none');
        }
    }

    function refreshUi() {
        const uniqueIds = Array.from(new Set(activeIds.map(String)));
        activeIds = uniqueIds;
        document.querySelectorAll('[data-bb-toggle="wishlist-count"]').forEach(function (counter) {
            counter.textContent = String(uniqueIds.length);
        });
        document.querySelectorAll('[data-bb-toggle="add-to-wishlist"][data-type="property"]').forEach(function (button) {
            setButtonState(button, uniqueIds.includes(String(button.dataset.id)));
        });
        renderWishlistPage();
    }

    function applyState(data) {
        authenticated = Boolean(data.authenticated);
        activeIds = (data.active_ids || []).map(String);
        properties = data.properties || [];
        if (!authenticated) {
            writeGuestIds(activeIds);
        }
        refreshUi();
    }

    function showError(message) {
        if (window.Theme && typeof window.Theme.showError === 'function') {
            window.Theme.showError(message);
            return;
        }
        window.alert(message);
    }

    function showSuccess(message) {
        if (message && window.Theme && typeof window.Theme.showSuccess === 'function') {
            window.Theme.showSuccess(message);
        }
    }

    function toggle(button) {
        const id = String(button.dataset.id || '');
        if (!id) {
            return;
        }

        const wasActive = activeIds.includes(id);
        activeIds = wasActive ? activeIds.filter(function (item) { return item !== id; }) : activeIds.concat(id);
        if (!authenticated) {
            writeGuestIds(activeIds);
        }
        refreshUi();

        if (!authenticated) {
            showSuccess(button.dataset[wasActive ? 'removeMessage' : 'addMessage']);
            loadState();
            return;
        }

        request('/' + locale + '/favorites/properties/' + id, {
            method: wasActive ? 'DELETE' : 'POST',
            body: JSON.stringify({})
        }).then(function (data) {
            applyState(data);
            showSuccess(button.dataset[wasActive ? 'removeMessage' : 'addMessage']);
        }).catch(function () {
            activeIds = wasActive ? activeIds.concat(id) : activeIds.filter(function (item) { return item !== id; });
            refreshUi();
            showError(locale === 'ar' ? 'تعذر تحديث المفضلة. حاول مرة أخرى.' : 'Could not update your wishlist. Please try again.');
        });
    }

    function loadState() {
        const guestIds = readGuestIds();
        const query = guestIds.length ? '?ids=' + encodeURIComponent(guestIds.join(',')) : '';

        return request(stateUrl + query).then(function (data) {
            if (data.authenticated && guestIds.length) {
                return request('/' + locale + '/favorites/merge', {
                    method: 'POST',
                    body: JSON.stringify({ property_ids: guestIds.map(Number) })
                }).then(function (merged) {
                    writeGuestIds([]);
                    applyState(merged);
                    return merged;
                });
            }

            applyState(data);
            return data;
        }).catch(function () {
            refreshUi();
        });
    }

    function propertyCard(property) {
        const featured = property.featured ? '<span class="flag-tag success">Featured</span>' : '';
        const image = escapeHtml(property.image);
        const url = escapeHtml(property.url);
        const name = escapeHtml(property.name);

        return '<div class="col-md-6 col-lg-4">' +
            '<div class="property-item homeya-box style-2" data-property-id="' + property.id + '">' +
                '<div class="archive-top"><a href="' + url + '" class="images-group"><div class="images-style">' +
                    '<img src="' + image + '" loading="lazy" alt="' + name + '"></div><div class="top"><div class="d-flex gap-8">' + featured +
                    '<span class="flag-tag primary">' + escapeHtml(property.purpose) + '</span></div><div class="d-flex gap-4">' +
                    '<button type="button" class="box-icon w-32 active" data-type="property" data-bb-toggle="add-to-wishlist" data-id="' + property.id + '" aria-pressed="true" aria-label="Remove from wishlist">' +
                    '<svg class="icon" aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><path d="M19.5 12.572l-7.5 7.428l-7.5-7.428a5 5 0 1 1 7.5-6.566a5 5 0 1 1 7.5 6.566"></path></svg></button>' +
                    '</div></div></a><div class="content"><div class="text-capitalize h7 fw-7"><a href="' + url + '" class="link line-clamp-1">' + name + '</a></div>' +
                    '<div class="desc"><i class="icon icon-mapPin"></i><p class="line-clamp-1">' + escapeHtml(property.address || '') + '</p></div>' +
                    '<ul class="meta-list"><li class="item"><i class="icon icon-bed"></i><span>' + escapeHtml(property.bedrooms ?? '-') + '</span></li>' +
                    '<li class="item"><i class="icon icon-bathtub"></i><span>' + escapeHtml(property.bathrooms ?? '-') + '</span></li>' +
                    '<li class="item"><i class="icon icon-ruler"></i><span>' + escapeHtml(property.area ?? '-') + ' ' + escapeHtml(property.area_unit || 'm2') + '</span></li></ul></div></div>' +
                '<div class="archive-bottom d-flex justify-content-between align-items-center"><span>' + escapeHtml(property.agent || '') + '</span><h6>' + escapeHtml(property.price) + '</h6></div>' +
            '</div></div>';
    }

    function renderWishlistPage() {
        if (!/^\/(en|ar|fr|id|vi)\/wishlist\/?$/.test(window.location.pathname)) {
            return;
        }

        const heading = document.querySelector('.container > h1.d-none');
        const section = heading?.nextElementSibling;
        if (!section || !section.classList.contains('flat-section')) {
            return;
        }

        const visible = properties.filter(function (property) {
            return activeIds.includes(String(property.id));
        });

        section.innerHTML = visible.length
            ? '<div class="row g-4">' + visible.map(propertyCard).join('') + '</div>'
            : '<p class="text-center body-2">' + (locale === 'ar' ? 'لم تضف أي عقارات إلى المفضلة بعد.' : 'You have not added any properties to your wishlist.') + '</p>';
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-bb-toggle="add-to-wishlist"][data-type="property"]');
        if (!button) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        toggle(button);
    }, true);

    document.addEventListener('shortcode.loaded', refreshUi);
    document.addEventListener('DOMContentLoaded', function () {
        refreshUi();
        loadState();
    });
})();
