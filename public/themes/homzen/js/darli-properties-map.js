/**
 * Darli Properties Split Screen & Leaflet Interactive Map
 * Controls map initialization, custom marker rendering, layer switching,
 * purpose toggling, and card-to-map bidirectional interactions.
 */

(function () {
    'use strict';

    function initDarliProperties() {
        const mapEl = document.getElementById('darli-properties-map');
        if (!mapEl || typeof L === 'undefined') {
            return;
        }

        // Avoid duplicate initialization
        if (mapEl._leaflet_id) {
            return;
        }

        // 1. Initial coordinates for Beni Suef, Egypt
        const beniSuefCoords = [29.072, 31.096];
        const defaultZoom = 13;

        // 2. Base Tile Layers
        // ArcGIS Canvas Light Gray (exact theme tiles matching mockup)
        const lightBase = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            maxNativeZoom: 16,
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri'
        });
        const lightRef = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Reference/MapServer/tile/{z}/{y}/{x}', {
            maxNativeZoom: 16,
            maxZoom: 19
        });
        const lightGroup = L.layerGroup([lightBase, lightRef]);

        // ArcGIS Canvas Dark Gray
        const darkBase = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
            maxNativeZoom: 16,
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri'
        });
        const darkRef = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Reference/MapServer/tile/{z}/{y}/{x}', {
            maxNativeZoom: 16,
            maxZoom: 19
        });
        const darkGroup = L.layerGroup([darkBase, darkRef]);

        // ArcGIS Satellite
        const satTile = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxNativeZoom: 18,
            maxZoom: 19,
            attribution: 'Tiles &copy; Esri'
        });

        const isDark = document.documentElement.dataset.theme === 'dark';
        let currentGroup = isDark ? darkGroup : lightGroup;

        // Default layer is Satellite as requested
        const map = L.map('darli-properties-map', {
            center: beniSuefCoords,
            zoom: defaultZoom,
            layers: [satTile],
            zoomControl: false,
            attributionControl: false
        });

        // Add custom positioned zoom control top-left
        L.control.zoom({
            position: 'topleft'
        }).addTo(map);

        // Invalidate map size on initialization and window resize (crucial for mobile layout rendering)
        setTimeout(function () {
            map.invalidateSize();
        }, 150);
        setTimeout(function () {
            map.invalidateSize();
        }, 500);

        window.addEventListener('resize', function () {
            map.invalidateSize();
        });
        window.addEventListener('orientationchange', function () {
            setTimeout(function () {
                map.invalidateSize();
            }, 200);
        });

        // Theme change observer for automatic dark/light map switching
        const themeObserver = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
                    const darkNow = document.documentElement.dataset.theme === 'dark';
                    if (map.hasLayer(lightGroup) || map.hasLayer(darkGroup)) {
                        map.removeLayer(lightGroup);
                        map.removeLayer(darkGroup);
                        currentGroup = darkNow ? darkGroup : lightGroup;
                        currentGroup.addTo(map);
                    }
                }
            });
        });
        themeObserver.observe(document.documentElement, { attributes: true });

        // 3. Render Center City Marker (Matches Pill Mockup in Reference Photo)
        const currentCityName = window.__DARLI_CURRENT_CITY__ || 'بني سويف';
        const centerIcon = L.divIcon({
            className: 'darli-center-city-marker',
            html: `
                <div class="darli-center-city-pill-wrap">
                    <div class="darli-center-city-pill">${currentCityName}</div>
                    <div class="darli-center-city-dot"></div>
                </div>
            `,
            iconSize: [110, 42],
            iconAnchor: [55, 38]
        });
        L.marker([29.072, 31.096], { icon: centerIcon, zIndexOffset: 500 }).addTo(map);

        // 4. Load Property Data
        const properties = window.__DARLI_MAP_PROPERTIES__ || [];
        const markersGroup = L.layerGroup().addTo(map);
        const markerMap = {}; // ID -> { marker, data }

        // Find IDs of properties currently visible on page 1 cards
        const visibleCardIds = new Set(
            Array.from(document.querySelectorAll('.darli-prop-card'))
                .map(c => parseInt(c.getAttribute('data-id'), 10))
                .filter(id => !isNaN(id))
        );

        let firstMarker = null;

        function createMarker(prop, index) {
            const isSale = prop.purpose === 'sale';
            const purposeClass = isSale ? 'sale' : 'rent';
            const purposeLabel = prop.purpose_label || (isSale ? 'للبيع' : 'للإيجار');
            const fallbackThumb = '/themes/homzen/images/darli-placeholder.png';
            const thumbUrl = prop.image || fallbackThumb;
            const isFeatured = visibleCardIds.has(prop.id) || index < 10;

            const iconHtml = `
                <div class="darli-marker-wrapper ${purposeClass} ${isFeatured ? 'is-featured' : 'is-compact'}" data-id="${prop.id}">
                    <div class="darli-marker-badge">
                        <span class="darli-marker-tag ${prop.is_sold ? 'sold' : purposeClass}">${prop.is_sold ? (prop.sold_label || 'تم البيع') : purposeLabel}</span>
                        <span class="darli-marker-price">${prop.formatted_price}</span>
                    </div>
                    <div class="darli-marker-pin ${purposeClass}">
                        <svg class="darli-marker-house-svg" width="16" height="16" viewBox="0 0 24 24" fill="#ffffff" style="transform: rotate(45deg);">
                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                        </svg>
                    </div>
                </div>
            `;

            const icon = L.divIcon({
                className: 'darli-custom-marker',
                html: iconHtml,
                iconSize: [160, 42],
                iconAnchor: [142, 36],
                popupAnchor: [-45, -36]
            });

            const marker = L.marker([prop.lat, prop.lng], {
                icon: icon,
                zIndexOffset: isFeatured ? 100 + (10 - index) : 10
            });

            // Popup content matching mockup
            const popupHtml = `
                <div class="darli-popup-card">
                    <button class="darli-popup-close" type="button" aria-label="إغلاق">&times;</button>
                    <div class="darli-popup-thumb" style="background-image: url('${thumbUrl}');"></div>
                    <div class="darli-popup-info">
                        <div class="darli-popup-header" style="gap: 4px;">
                            ${prop.is_sold ? `<span class="darli-popup-badge sold">${prop.sold_label || 'تم البيع'}</span>` : ''}
                            <span class="darli-popup-badge ${purposeClass}">${purposeLabel}</span>
                        </div>
                        <a href="${prop.url}" class="darli-popup-title" title="${prop.name}">${prop.name}</a>
                        <div class="darli-popup-loc">
                            <svg class="darli-popup-pin-icon" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5z"/>
                            </svg>
                            <span>${prop.location || 'بني سويف'}</span>
                        </div>
                        <div class="darli-popup-price">${prop.formatted_price}</div>
                    </div>
                </div>
            `;

            marker.bindPopup(popupHtml, {
                closeButton: false,
                offset: [0, -6],
                maxWidth: 320,
                autoPanPadding: [20, 20]
            });

            marker.on('click', function () {
                highlightCard(prop.id);
            });

            markerMap[prop.id] = { marker: marker, data: prop };
            markersGroup.addLayer(marker);

            if (index === 0 && !firstMarker) {
                firstMarker = marker;
            }
        }

        // Populate markers
        properties.forEach(function (prop, idx) {
            if (prop.lat && prop.lng) {
                createMarker(prop, idx);
            }
        });

        // Filter initially by active mobile purpose tab if on mobile screen
        const activeMobPill = document.querySelector('.darli-mob-purpose-pill.active');
        const initialPurpose = activeMobPill ? activeMobPill.getAttribute('data-purpose') : 'sale';
        if (window.innerWidth <= 991 && initialPurpose) {
            markersGroup.clearLayers();
            Object.values(markerMap).forEach(function (item) {
                if (item.data.purpose === initialPurpose) {
                    markersGroup.addLayer(item.marker);
                }
            });

            const logoImg = document.querySelector('.logo img');
            if (logoImg) {
                logoImg.src = '/themes/homzen/images/logo/darli-logo-ar.png';
            }
        }

        // Open first marker popup by default on desktop screens to match mockup design
        if (firstMarker && window.innerWidth > 768) {
            setTimeout(function () {
                firstMarker.openPopup();
            }, 600);
        }

        // Delegate close button inside Leaflet popups
        document.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('darli-popup-close')) {
                map.closePopup();
            }
        });

        // 5. Highlight Property Card in Listing Pane
        function highlightCard(propId) {
            document.querySelectorAll('.darli-prop-card').forEach(function (c) {
                c.classList.remove('active-marker');
            });
            const card = document.querySelector(`.darli-prop-card[data-id="${propId}"]`);
            if (card) {
                card.classList.add('active-marker');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // 6. Highlight Marker when hovering Property Card in Listing Pane
        document.querySelectorAll('.darli-prop-card').forEach(function (card) {
            const propId = card.getAttribute('data-id');
            card.addEventListener('mouseenter', function () {
                const item = markerMap[propId];
                if (item && item.marker) {
                    const el = item.marker.getElement();
                    if (el) {
                        const wrap = el.querySelector('.darli-marker-wrapper');
                        if (wrap) wrap.classList.add('active');
                    }
                }
            });
            card.addEventListener('mouseleave', function () {
                const item = markerMap[propId];
                if (item && item.marker) {
                    const el = item.marker.getElement();
                    if (el) {
                        const wrap = el.querySelector('.darli-marker-wrapper');
                        if (wrap) wrap.classList.remove('active');
                    }
                }
            });
            card.addEventListener('click', function (e) {
                // If clicked outside link or wishlist
                if (!e.target.closest('a') && !e.target.closest('.darli-badge-wishlist')) {
                    const item = markerMap[propId];
                    if (item && item.marker) {
                        map.flyTo([item.data.lat, item.data.lng], 15, { duration: 0.8 });
                        item.marker.openPopup();
                    }
                }
            });
        });

        // 7. Top Floating Purpose Filter Tabs (للبيع / للإيجار)
        let activePurposeFilter = null; // null | 'sale' | 'rent'
        const purposeItems = document.querySelectorAll('.darli-map-purpose-item');

        purposeItems.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetPurpose = this.getAttribute('data-purpose');
                if (activePurposeFilter === targetPurpose) {
                    // Reset to show all
                    activePurposeFilter = null;
                    purposeItems.forEach(b => {
                        b.classList.remove('active');
                        b.classList.remove('inactive');
                    });
                } else {
                    activePurposeFilter = targetPurpose;
                    purposeItems.forEach(b => {
                        if (b.getAttribute('data-purpose') === activePurposeFilter) {
                            b.classList.add('active');
                            b.classList.remove('inactive');
                        } else {
                            b.classList.remove('active');
                            b.classList.add('inactive');
                        }
                    });
                }

                // Filter markers on map
                markersGroup.clearLayers();
                Object.values(markerMap).forEach(function (item) {
                    if (!activePurposeFilter || item.data.purpose === activePurposeFilter) {
                        markersGroup.addLayer(item.marker);
                    }
                });

                // Also filter visible cards in current grid page
                document.querySelectorAll('.darli-prop-card').forEach(function (c) {
                    const cardPurpose = c.getAttribute('data-purpose');
                    if (!activePurposeFilter || cardPurpose === activePurposeFilter) {
                        c.style.display = '';
                    } else {
                        c.style.display = 'none';
                    }
                });
            });
        });

        // 8. Bottom Map Type Dropdown
        const mapTypeBtn = document.getElementById('darli-map-btn-type');
        const mapLayerMenu = document.getElementById('darli-map-layer-menu');

        if (mapTypeBtn && mapLayerMenu) {
            mapTypeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                mapLayerMenu.classList.toggle('open');
            });

            document.addEventListener('click', function () {
                mapLayerMenu.classList.remove('open');
            });

            document.querySelectorAll('.darli-map-layer-item').forEach(function (item) {
                item.addEventListener('click', function () {
                    const layerKey = this.getAttribute('data-layer');
                    document.querySelectorAll('.darli-map-layer-item').forEach(i => i.classList.remove('active'));
                    this.classList.add('active');

                    // Remove current active layers
                    if (map.hasLayer(lightGroup)) map.removeLayer(lightGroup);
                    if (map.hasLayer(darkGroup)) map.removeLayer(darkGroup);
                    if (map.hasLayer(satTile)) map.removeLayer(satTile);

                    if (layerKey === 'satellite') {
                        satTile.addTo(map);
                    } else if (layerKey === 'dark') {
                        darkGroup.addTo(map);
                    } else {
                        lightGroup.addTo(map);
                    }
                    mapLayerMenu.classList.remove('open');
                });
            });
        }

        // 9. Bottom "عرض العقارات في هذه المنطقة" Button
        const fitAreaBtn = document.getElementById('darli-map-btn-area');
        if (fitAreaBtn) {
            fitAreaBtn.addEventListener('click', function () {
                const latLngs = [];
                Object.values(markerMap).forEach(function (item) {
                    if (!activePurposeFilter || item.data.purpose === activePurposeFilter) {
                        latLngs.push([item.data.lat, item.data.lng]);
                    }
                });
                if (latLngs.length > 0) {
                    const bounds = L.latLngBounds(latLngs);
                    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
                } else {
                    map.setView(beniSuefCoords, defaultZoom);
                }
            });
        }

        // 10. Layout View Switcher (Grid vs List)
        const gridBtn = document.getElementById('darli-view-grid');
        const listBtn = document.getElementById('darli-view-list');
        const propsGrid = document.querySelector('.darli-props-grid');

        if (gridBtn && listBtn && propsGrid) {
            const savedView = localStorage.getItem('darli-props-view');
            if (savedView === 'list') {
                propsGrid.classList.add('view-list');
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
            }

            gridBtn.addEventListener('click', function () {
                propsGrid.classList.remove('view-list');
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
                localStorage.setItem('darli-props-view', 'grid');
            });

            listBtn.addEventListener('click', function () {
                propsGrid.classList.add('view-list');
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
                localStorage.setItem('darli-props-view', 'list');
            });
        }

        // 11. Sort Dropdown Change -> Auto Submit Filter Form
        const sortSelect = document.getElementById('darli-sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                const form = document.getElementById('darli-filter-form');
                if (form) {
                    let sortInput = form.querySelector('input[name="sort"]');
                    if (!sortInput) {
                        sortInput = document.createElement('input');
                        sortInput.type = 'hidden';
                        sortInput.name = 'sort';
                        form.appendChild(sortInput);
                    }
                    sortInput.value = this.value;
                    form.submit();
                }
            });
        }

        // 12. Wishlist Heart Button Click
        document.querySelectorAll('.darli-badge-wishlist').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                e.preventDefault();
                this.classList.toggle('active');
                const propId = this.getAttribute('data-id');
                if (window.HomzenWishlist && typeof window.HomzenWishlist.toggle === 'function') {
                    window.HomzenWishlist.toggle(propId);
                }
            });
        });

        // 13. Mobile Map Floating Layer Switcher
        const mobLayerBtn = document.getElementById('darli-mob-layer-btn');
        const mobLayerPopover = document.getElementById('darli-mob-layer-popover');
        if (mobLayerBtn && mobLayerPopover) {
            mobLayerBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                mobLayerPopover.classList.toggle('open');
            });

            document.addEventListener('click', function () {
                mobLayerPopover.classList.remove('open');
            });

            mobLayerPopover.querySelectorAll('.darli-mob-layer-opt').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    const layerKey = this.getAttribute('data-layer');
                    mobLayerPopover.querySelectorAll('.darli-mob-layer-opt').forEach(o => o.classList.remove('active'));
                    this.classList.add('active');

                    if (map.hasLayer(lightGroup)) map.removeLayer(lightGroup);
                    if (map.hasLayer(darkGroup)) map.removeLayer(darkGroup);
                    if (map.hasLayer(satTile)) map.removeLayer(satTile);

                    if (layerKey === 'satellite') {
                        satTile.addTo(map);
                    } else if (layerKey === 'dark') {
                        darkGroup.addTo(map);
                    } else {
                        lightGroup.addTo(map);
                    }
                    mobLayerPopover.classList.remove('open');
                });
            });
        }

        // 14. Mobile Map Fullscreen / Expand Toggle
        const mobFullscreenBtn = document.getElementById('darli-mob-fullscreen-btn');
        const mapFrameCard = document.getElementById('darli-map-frame-card');
        if (mobFullscreenBtn && mapFrameCard) {
            mobFullscreenBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const isFull = mapFrameCard.classList.toggle('is-fullscreen');
                const iconExpand = mobFullscreenBtn.querySelector('.icon-expand');
                const iconCompress = mobFullscreenBtn.querySelector('.icon-compress');
                if (iconExpand && iconCompress) {
                    iconExpand.style.display = isFull ? 'none' : 'block';
                    iconCompress.style.display = isFull ? 'block' : 'none';
                }
                setTimeout(function () {
                    map.invalidateSize();
                }, 120);
            });
        }

        // 15. Mobile "عرض الخريطة" Button
        const mobShowMapBtn = document.getElementById('darli-mob-show-map-btn');
        if (mobShowMapBtn && mapFrameCard) {
            mobShowMapBtn.addEventListener('click', function (e) {
                e.preventDefault();
                mapFrameCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                mapFrameCard.classList.toggle('is-expanded');
                setTimeout(function () {
                    map.invalidateSize();
                }, 380);
            });
        }

        // 16. Mobile "فلترة متقدمة" Bottom Sheet
        const mobAdvFilterBtn = document.getElementById('darli-mob-adv-filter-btn');
        const mobFilterSheet = document.getElementById('darli-mob-filter-sheet');
        const mobFilterBackdrop = document.getElementById('darli-mob-filter-backdrop');
        const mobFilterClose = document.getElementById('darli-mob-filter-close');

        function openAdvFilter() {
            if (mobFilterSheet && mobFilterBackdrop) {
                mobFilterSheet.classList.add('open');
                mobFilterBackdrop.classList.add('open');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeAdvFilter() {
            if (mobFilterSheet && mobFilterBackdrop) {
                mobFilterSheet.classList.remove('open');
                mobFilterBackdrop.classList.remove('open');
                document.body.style.overflow = '';
            }
        }

        if (mobAdvFilterBtn) mobAdvFilterBtn.addEventListener('click', openAdvFilter);
        if (mobFilterClose) mobFilterClose.addEventListener('click', closeAdvFilter);
        if (mobFilterBackdrop) mobFilterBackdrop.addEventListener('click', closeAdvFilter);

        // Chip selection for bedrooms and bathrooms
        document.querySelectorAll('.sheet-chips').forEach(function (group) {
            const targetId = group.getAttribute('data-target') === 'bedrooms' ? 'darli-adv-bedrooms' : 'darli-adv-bathrooms';
            const hiddenInput = document.getElementById(targetId);
            group.querySelectorAll('.sheet-chip').forEach(function (chip) {
                chip.addEventListener('click', function () {
                    group.querySelectorAll('.sheet-chip').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    if (hiddenInput) {
                        hiddenInput.value = this.getAttribute('data-val');
                    }
                });
            });
        });

        // 17. Mobile Purpose Segmented Tabs (للبيع / للإيجار)
        const mobPurposePills = document.querySelectorAll('.darli-mob-purpose-pill');
        const mobInputPurpose = document.getElementById('darli-mob-input-purpose');
        const mobSearchForm = document.getElementById('darli-mob-search-form');

        mobPurposePills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                const targetPurpose = this.getAttribute('data-purpose');
                mobPurposePills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                if (mobInputPurpose) mobInputPurpose.value = targetPurpose;
                const advPurpose = document.getElementById('darli-adv-purpose');
                if (advPurpose) advPurpose.value = targetPurpose;

                // Immediately filter markers on map
                markersGroup.clearLayers();
                Object.values(markerMap).forEach(function (item) {
                    if (!targetPurpose || item.data.purpose === targetPurpose) {
                        markersGroup.addLayer(item.marker);
                    }
                });

                // Submit search form
                if (mobSearchForm) {
                    mobSearchForm.submit();
                }
            });
        });

        // 18. Mobile Custom Select Dropdowns Sync
        const mobLocSelect = document.getElementById('darli-mob-location-select');
        const mobLocDisplay = document.getElementById('darli-mob-location-display');
        if (mobLocSelect && mobLocDisplay) {
            mobLocSelect.addEventListener('change', function () {
                const text = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text.trim() : '';
                mobLocDisplay.textContent = text || 'كل المناطق';
            });
        }

        const mobCatSelect = document.getElementById('darli-mob-category-select');
        const mobCatDisplay = document.getElementById('darli-mob-category-display');
        if (mobCatSelect && mobCatDisplay) {
            mobCatSelect.addEventListener('change', function () {
                const text = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text.trim() : '';
                mobCatDisplay.textContent = text || 'أي نوع';
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDarliProperties);
    } else {
        initDarliProperties();
    }
})();

