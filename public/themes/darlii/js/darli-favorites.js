/**
 * Darlii Favorites (wishlist) — clean URLs, guest-safe, no server calls for
 * guests. Replaces the old localized wishlist.js which posted to /ar/... and
 * produced 301/405 errors.
 */
(function () {
    'use strict';

    if (window.__darliFavoritesLoaded) {
        return;
    }
    window.__darliFavoritesLoaded = true;

    var STORAGE_KEY = 'darlii-wishlist-properties';
    var locale = (document.documentElement.lang || 'ar').toLowerCase();
    var isAr = locale === 'ar';
    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var page = document.getElementById('darli-favorites-page');
    var grid = document.getElementById('darli-favorites-grid');
    var emptyBox = document.getElementById('darli-favorites-empty');

    var authenticated = false;
    var serverIds = [];

    function t(ar, en) {
        return isAr ? ar : en;
    }

    /* ------------------------------ storage ------------------------------ */
    function localIds() {
        try {
            var raw = JSON.parse(window.localStorage.getItem(STORAGE_KEY) || '[]');
            return Array.isArray(raw) ? raw.map(String) : [];
        } catch (e) {
            return [];
        }
    }

    function setLocal(ids) {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(ids.map(String)));
        } catch (e) {}
    }

    function activeIds() {
        return authenticated ? serverIds : localIds();
    }

    function isFavorite(id) {
        return activeIds().indexOf(String(id)) !== -1;
    }

    /* ------------------------------ requests ------------------------------ */
    function request(url, options) {
        options = options || {};
        var headers = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (csrf) {
            headers['X-CSRF-TOKEN'] = csrf;
        }

        return window.fetch(url, Object.assign({
            credentials: 'same-origin',
            headers: headers
        }, options)).then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        });
    }

    function stateUrl() {
        var ids = localIds();
        return ids.length ? '/favorites/state?ids=' + encodeURIComponent(ids.join(',')) : '/favorites/state';
    }

    /* ------------------------------ UI sync ------------------------------ */
    function updateButtons() {
        document.querySelectorAll('[data-bb-toggle="add-to-wishlist"][data-type="property"]').forEach(function (button) {
            if (!button.dataset.id) {
                return;
            }
            var active = isFavorite(button.dataset.id);
            button.classList.toggle('is-active', active);
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');

            var label = active
                ? t('إزالة من المفضلة', 'Remove from favorites')
                : t('إضافة إلى المفضلة', 'Add to favorites');
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);

            var svg = button.querySelector('svg');
            if (svg) {
                svg.setAttribute('fill', active ? 'currentColor' : 'none');
            }
        });

        var total = activeIds().length;
        document.querySelectorAll('[data-favorites-count], [data-bb-toggle="wishlist-count"]').forEach(function (node) {
            node.textContent = String(total);
        });
    }

    function announce(type, message) {
        if (!message) {
            return;
        }
        if (window.Theme && typeof window.Theme[type] === 'function') {
            window.Theme[type](message);
        }
    }

    function successFor(button, active) {
        if (!button) {
            return;
        }
        announce('showSuccess', button.dataset[active ? 'addMessage' : 'removeMessage']);
    }

    /* ------------------------------ cards ------------------------------ */
    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = String(value == null ? '' : value);
        return div.innerHTML;
    }

    function purposeLabel(purpose) {
        return purpose === 'rent' ? t('للإيجار', 'For rent') : t('للبيع', 'For sale');
    }

    function cardHtml(property) {
        var area = property.area
            ? String(property.area).replace(/\.?0+$/, '') + ' ' + t('م²', 'm²')
            : '—';
        var address = property.address ? '<p class="darli-fav-card__loc">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z"/></svg>' +
            '<span>' + escapeHtml(property.address) + '</span></p>' : '';

        return '<div class="col-md-6 col-lg-4" data-favorite-card="' + escapeHtml(property.id) + '">' +
            '<article class="darli-fav-card">' +
            '<div class="darli-fav-card__media">' +
            '<a href="' + escapeHtml(property.url) + '" aria-label="' + escapeHtml(property.name) + '">' +
            '<img src="' + escapeHtml(property.image) + '" alt="' + escapeHtml(property.name) + '" loading="lazy"></a>' +
            '<span class="darli-fav-card__purpose">' + purposeLabel(property.purpose) + '</span>' +
            '<button type="button" class="darli-fav-card__heart is-active" ' +
            'data-bb-toggle="add-to-wishlist" data-type="property" data-id="' + escapeHtml(property.id) + '" ' +
            'data-add-message="' + t('تمت الإضافة إلى المفضلة', 'Added to favorites') + '" ' +
            'data-remove-message="' + t('تمت الإزالة من المفضلة', 'Removed from favorites') + '" ' +
            'aria-pressed="true" aria-label="' + t('إزالة من المفضلة', 'Remove from favorites') + '">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19.5 12.572l-7.5 7.428l-7.5 -7.428a5 5 0 1 1 7.5 -6.566a5 5 0 1 1 7.5 6.572"/></svg>' +
            '</button></div>' +
            '<div class="darli-fav-card__body">' +
            '<h3 class="darli-fav-card__title"><a href="' + escapeHtml(property.url) + '">' + escapeHtml(property.name) + '</a></h3>' +
            address +
            '<ul class="darli-fav-card__meta">' +
            '<li><span>' + escapeHtml(property.bedrooms == null ? '—' : property.bedrooms) + '</span> ' + t('غرف', 'beds') + '</li>' +
            '<li><span>' + escapeHtml(property.bathrooms == null ? '—' : property.bathrooms) + '</span> ' + t('حمام', 'baths') + '</li>' +
            '<li><span>' + escapeHtml(area) + '</span></li>' +
            '</ul>' +
            '<div class="darli-fav-card__footer">' +
            '<span class="darli-fav-card__price">' + escapeHtml(property.price) + '</span>' +
            '<a href="' + escapeHtml(property.url) + '" class="darli-fav-card__details">' + t('التفاصيل', 'Details') + '</a>' +
            '</div></div></article></div>';
    }

    function renderCards(properties) {
        if (!grid) {
            return;
        }
        properties = Array.isArray(properties) ? properties : [];
        grid.innerHTML = properties.map(cardHtml).join('');

        if (emptyBox) {
            emptyBox.hidden = properties.length > 0;
        }
        updateButtons();
    }

    function applyState(data) {
        authenticated = !!(data && data.authenticated);
        serverIds = ((data && data.active_ids) || []).map(String);
        updateButtons();

        if (page && data && Array.isArray(data.properties)) {
            renderCards(data.properties);
        }
    }

    /* ------------------------------ actions ------------------------------ */
    function toggle(id, button) {
        id = String(id || '');
        if (!id) {
            return Promise.resolve();
        }

        var wasFavorite = isFavorite(id);
        var nextFavorite = !wasFavorite;

        // الزائر: تخزين محلي فقط — لا طلبات للخادم ولا أخطاء.
        if (!authenticated) {
            var ids = localIds();
            if (nextFavorite) {
                if (ids.indexOf(id) === -1) {
                    ids.push(id);
                }
            } else {
                ids = ids.filter(function (item) { return item !== id; });
            }
            setLocal(ids);
            updateButtons();
            successFor(button, nextFavorite);

            if (page) {
                if (!nextFavorite) {
                    var card = grid ? grid.querySelector('[data-favorite-card="' + id + '"]') : null;
                    if (card) {
                        card.remove();
                    }
                    if (grid && !grid.children.length && emptyBox) {
                        emptyBox.hidden = false;
                    }
                }
                refreshGuestCards();
            }

            return Promise.resolve();
        }

        // المسجّل: تحديث متفائل ثم مزامنة مع الخادم.
        var previousIds = serverIds.slice();
        serverIds = nextFavorite
            ? serverIds.concat([id]).filter(function (value, index, self) { return self.indexOf(value) === index; })
            : serverIds.filter(function (item) { return item !== id; });
        updateButtons();

        if (page && !nextFavorite) {
            var serverCard = grid ? grid.querySelector('[data-favorite-card="' + id + '"]') : null;
            if (serverCard) {
                serverCard.remove();
            }
            if (grid && !grid.children.length && emptyBox) {
                emptyBox.hidden = false;
            }
        }

        return request('/favorites/properties/' + encodeURIComponent(id), {
            method: nextFavorite ? 'POST' : 'DELETE',
            body: '{}'
        }).then(function (data) {
            applyState(data);
            successFor(button, nextFavorite);
        }).catch(function () {
            serverIds = previousIds;
            updateButtons();
            announce('showError', t('تعذر تحديث المفضلة. حاول مرة أخرى.', 'Could not update your favorites. Please try again.'));
        });
    }

    function refreshGuestCards() {
        if (!page || authenticated) {
            return;
        }
        var ids = localIds();
        if (!ids.length) {
            renderCards([]);
            return;
        }

        request('/favorites/state?ids=' + encodeURIComponent(ids.join(',')))
            .then(function (data) { renderCards(data.properties || []); })
            .catch(function () { renderCards([]); });
    }

    /* ------------------------------ boot ------------------------------ */
    function boot() {
        var ids = localIds();

        if (authenticated === false && ids.length) {
            // لا شيء: سنقرأ الحالة من الخادم وندمج إن كان مسجلًا.
        }

        request(stateUrl()).then(function (data) {
            authenticated = !!(data && data.authenticated);
            serverIds = ((data && data.active_ids) || []).map(String);

            if (authenticated && ids.length) {
                return request('/favorites/merge', {
                    method: 'POST',
                    body: JSON.stringify({ property_ids: ids.map(Number).filter(Boolean) })
                }).then(function (merged) {
                    setLocal([]);
                    applyState(merged);
                });
            }

            applyState(data);
        }).catch(function () {
            // فشل الشبكة: نبقى على التخزين المحلي بدون أي خطأ ظاهر.
            authenticated = false;
            serverIds = [];
            updateButtons();
            if (page) {
                refreshGuestCards();
            }
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-bb-toggle="add-to-wishlist"][data-type="property"]');
        if (!button) {
            return;
        }
        event.preventDefault();
        event.stopImmediatePropagation();
        toggle(button.dataset.id, button);
    }, true);

    window.DarliiWishlist = {
        toggle: function (id, button) {
            return toggle(id, button || document.querySelector('[data-bb-toggle="add-to-wishlist"][data-type="property"][data-id="' + id + '"]'));
        },
        isFavorite: isFavorite,
        refresh: boot,
        count: function () { return activeIds().length; }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
