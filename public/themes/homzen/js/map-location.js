(function () {
    'use strict';

    const mapElement = document.querySelector('[data-bb-toggle="list-map"]');
    if (!mapElement) {
        return;
    }

    const locale = document.documentElement.lang || 'en';
    const storageKey = 'visitor_governorate';
    const darkBaseTiles = 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}';
    const darkLabelTiles = 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Reference/MapServer/tile/{z}/{y}/{x}';
    const lightTiles = 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}';
    const lightLabelTiles = 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Reference/MapServer/tile/{z}/{y}/{x}';
    let governorates = [];
    let markerLayer = null;
    let tileLayers = [];
    let requestController = null;
    let loadTimer = null;
    let cairoMarker = null;
    let googleMap = null;
    let googleMarkers = [];
    let googleInfoWindow = null;
    let googleRouteLine = null;
    let governorateMask = null;

    function waitForMap(attempt) {
        if (window.google && window.google.maps) {
            setupGoogleMap();
            return;
        }
        if (window.activeMap && window.L) {
            // Give the async Google Maps loader a moment before falling back.
            if (attempt >= 50) {
                setup(window.activeMap);
                return;
            }
        }
        if (attempt < 80) {
            window.setTimeout(function () { waitForMap(attempt + 1); }, 100);
        }
    }

    function setupGoogleMap() {
        if (googleMap || !mapElement) {
            return;
        }

        if (window.activeMap && typeof window.activeMap.remove === 'function') {
            window.activeMap.remove();
            window.activeMap = null;
        }
        mapElement.removeAttribute('class');
        mapElement.innerHTML = '';
        mapElement.className = 'darli-google-map';

        googleMap = new window.google.maps.Map(mapElement, {
            center: { lat: 29.0661, lng: 31.0994 },
            zoom: 9,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true,
            clickableIcons: false,
            gestureHandling: 'greedy',
            zoomControlOptions: { position: window.google.maps.ControlPosition.LEFT_TOP },
            styles: document.documentElement.dataset.theme === 'dark' ? googleDarkStyle() : []
        });
        googleInfoWindow = new window.google.maps.InfoWindow();
        enhanceSearchCardMarkup();
        setupGooglePlaces();
        connectGoogleDirections();

        googleMap.addListener('idle', function () {
            window.clearTimeout(loadTimer);
            loadTimer = window.setTimeout(loadGoogleMarkers, 250);
        });
        function refreshGoogleTheme() {
            googleMap.setOptions({ styles: document.documentElement.dataset.theme === 'dark' ? googleDarkStyle() : [] });
        }
        window.addEventListener('darli:theme-changed', refreshGoogleTheme);
        window.addEventListener('homzen:theme-changed', refreshGoogleTheme);

        window.fetch('/' + locale + '/map/governorates', { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
            .then(function (data) {
                governorates = data.governorates || [];
                focusGoogleGovernorate(data.default || 'beni-suef');
                drawGovernorateMask(data.default || 'beni-suef');
                connectGoogleLocationSearch();
            })
            .catch(function () { loadGoogleMarkers(); });
    }

    function focusGoogleGovernorate(key) {
        const governorate = governorates.find(function (item) { return item.key === key; });
        if (!governorate) return false;
        const bounds = new window.google.maps.LatLngBounds();
        bounds.extend({ lat: governorate.bounds[0][0], lng: governorate.bounds[0][1] });
        bounds.extend({ lat: governorate.bounds[1][0], lng: governorate.bounds[1][1] });
        googleMap.fitBounds(bounds, 32);
        window.localStorage.setItem(storageKey, governorate.key);
        return true;
    }

    function drawGovernorateMask(key) {
        const governorate = governorates.find(function (item) { return item.key === key; });
        if (!governorate || !googleMap) return;
        if (governorateMask) governorateMask.setMap(null);
        const south = governorate.bounds[0][0], west = governorate.bounds[0][1];
        const north = governorate.bounds[1][0], east = governorate.bounds[1][1];
        governorateMask = new window.google.maps.Polygon({
            map: googleMap,
            paths: [
                [{lat: -85, lng: -180}, {lat: -85, lng: 180}, {lat: 85, lng: 180}, {lat: 85, lng: -180}],
                [{lat: south, lng: west}, {lat: south, lng: east}, {lat: north, lng: east}, {lat: north, lng: west}]
            ],
            strokeColor: '#94a3b8', strokeOpacity: 0.35, strokeWeight: 1,
            fillColor: '#0f172a', fillOpacity: 0.18,
            clickable: false, zIndex: 2
        });
    }

    function connectGoogleLocationSearch() {
        function focus(value) {
            const search = String(value || '').trim().toLocaleLowerCase();
            const item = governorates.find(function (governorate) {
                return governorate.key === search || governorate.name.toLocaleLowerCase() === search ||
                    (governorate.aliases || []).some(function (alias) { return alias.toLocaleLowerCase() === search; });
            });
            if (item) focusGoogleGovernorate(item.key);
            else geocodeAddress(value);
        }
        document.querySelectorAll('input[name="location"]').forEach(function (input) {
            input.addEventListener('change', function () { focus(input.value); });
        });
        document.addEventListener('click', function (event) {
            const item = event.target.closest('.search-suggestion-item');
            if (item) focus(item.textContent || item.dataset.value);
        });
    }

    function setupGooglePlaces() {
        if (!window.google.maps.places || !window.google.maps.places.PlaceAutocompleteElement) return;
        document.querySelectorAll('input[name="location"]').forEach(function (input) {
            if (input.dataset.googlePlacesReady) return;
            input.dataset.googlePlacesReady = 'true';
            const autocomplete = new window.google.maps.places.PlaceAutocompleteElement({
                includedRegionCodes: ['eg'],
                requestedLanguage: locale
            });
            autocomplete.className = 'darli-place-autocomplete';
            autocomplete.setAttribute('placeholder', input.placeholder || (locale === 'ar' ? 'ابحث عن مكان' : 'Search for a place'));
            input.hidden = true;
            input.insertAdjacentElement('afterend', autocomplete);
            autocomplete.addEventListener('gmp-select', function (event) {
                const place = event.placePrediction.toPlace();
                place.fetchFields({ fields: ['displayName', 'formattedAddress', 'location', 'viewport'] }).then(function () {
                    input.value = place.formattedAddress || place.displayName || '';
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                    if (place.viewport) googleMap.fitBounds(place.viewport);
                    else if (place.location) googleMap.setCenter(place.location);
                });
            });
        });
    }

    function geocodeAddress(address) {
        if (!address || !window.google.maps.Geocoder) return;
        new window.google.maps.Geocoder().geocode({ address: address, region: 'EG' }).then(function (response) {
            if (!response.results.length) return;
            const result = response.results[0];
            if (result.geometry.viewport) googleMap.fitBounds(result.geometry.viewport);
            else googleMap.setCenter(result.geometry.location);
        }).catch(function () {});
    }

    function connectGoogleDirections() {
        mapElement.addEventListener('click', function (event) {
            const button = event.target.closest('[data-darli-route]');
            if (!button) return;
            if (!navigator.geolocation) {
                button.textContent = locale === 'ar' ? 'الموقع غير متاح' : 'Location unavailable';
                return;
            }
            button.disabled = true;
            button.textContent = locale === 'ar' ? 'جارٍ حساب المسار…' : 'Calculating route…';
            navigator.geolocation.getCurrentPosition(function (position) {
                requestGoogleRoute(button, position.coords, {
                    latitude: Number(button.dataset.lat), longitude: Number(button.dataset.lng)
                });
            }, function () {
                button.disabled = false;
                button.textContent = locale === 'ar' ? 'اسمح بالوصول لموقعك' : 'Allow location access';
            }, { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 });
        });
    }

    function requestGoogleRoute(button, origin, destination) {
        const params = new URLSearchParams({
            origin_lat: origin.latitude, origin_lng: origin.longitude,
            destination_lat: destination.latitude, destination_lng: destination.longitude
        });
        window.fetch('/' + locale + '/map/route?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
            .then(function (route) {
                if (googleRouteLine) googleRouteLine.setMap(null);
                const path = window.google.maps.geometry.encoding.decodePath(route.encoded_polyline);
                googleRouteLine = new window.google.maps.Polyline({
                    map: googleMap, path: path, strokeColor: '#C99A45', strokeOpacity: 0.95, strokeWeight: 6
                });
                const bounds = new window.google.maps.LatLngBounds();
                path.forEach(function (point) { bounds.extend(point); });
                googleMap.fitBounds(bounds, 56);
                button.textContent = formatRouteSummary(route);
            }).catch(function () {
                button.disabled = false;
                button.textContent = locale === 'ar' ? 'تعذر حساب المسار' : 'Route unavailable';
            });
    }

    function formatRouteSummary(route) {
        const distance = (Number(route.distance_meters || 0) / 1000).toLocaleString(locale, { maximumFractionDigits: 1 });
        const minutes = Math.max(1, Math.round(parseFloat(route.duration || '0') / 60));
        return locale === 'ar' ? distance + ' كم • ' + minutes + ' دقيقة' : distance + ' km • ' + minutes + ' min';
    }

    function loadGoogleMarkers() {
        if (!googleMap || !googleMap.getBounds()) return;
        const bounds = googleMap.getBounds();
        const params = new URLSearchParams({
            sw_lat: bounds.getSouthWest().lat().toFixed(6),
            sw_lng: bounds.getSouthWest().lng().toFixed(6),
            ne_lat: bounds.getNorthEast().lat().toFixed(6),
            ne_lng: bounds.getNorthEast().lng().toFixed(6),
            per_page: '200'
        });
        if (requestController) requestController.abort();
        requestController = new AbortController();
        window.fetch('/' + locale + '/ajax/properties/map?' + params.toString(), {
            headers: { Accept: 'application/json' }, signal: requestController.signal
        }).then(function (response) {
            return response.ok ? response.json() : Promise.reject(new Error('Map request failed'));
        }).then(function (payload) {
            googleMarkers.forEach(function (marker) { marker.setMap(null); });
            googleMarkers = (payload.data || []).map(addGoogleMarker).filter(Boolean);
        }).catch(function (error) {
            if (error.name !== 'AbortError') googleMarkers = [];
        });
    }

    function addGoogleMarker(property) {
        if (!property.latitude || !property.longitude) return null;
        const marker = new window.google.maps.Marker({
            map: googleMap,
            position: { lat: Number(property.latitude), lng: Number(property.longitude) },
            title: property.name || '',
            icon: {
                path: window.google.maps.SymbolPath.CIRCLE,
                fillColor: '#C99A45', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2, scale: 8
            }
        });
        marker.addListener('click', function () {
            googleInfoWindow.setContent(propertyPopup(property) +
                '<button type="button" class="darli-route-button" data-darli-route data-lat="' + Number(property.latitude) +
                '" data-lng="' + Number(property.longitude) + '">' +
                (locale === 'ar' ? 'الاتجاهات والمسافة' : 'Directions & distance') + '</button>');
            googleInfoWindow.open({ map: googleMap, anchor: marker });
        });
        return marker;
    }

    function googleDarkStyle() {
        return [
            { elementType: 'geometry', stylers: [{ color: '#101827' }] },
            { elementType: 'labels.text.stroke', stylers: [{ color: '#101827' }] },
            { elementType: 'labels.text.fill', stylers: [{ color: '#94a3b8' }] },
            { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#26354a' }] },
            { featureType: 'road', elementType: 'geometry.stroke', stylers: [{ color: '#172235' }] },
            { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#07111f' }] },
            { featureType: 'poi', elementType: 'labels', stylers: [{ visibility: 'off' }] }
        ];
    }

    function setup(map) {
        map.eachLayer(function (layer) {
            if (window.L.MarkerClusterGroup && layer instanceof window.L.MarkerClusterGroup) {
                map.removeLayer(layer);
            }
        });

        if (!map.attributionControl) {
            map.attributionControl = window.L.control.attribution({ prefix: false, position: 'topright' }).addTo(map);
        }

        applyTileLayer(map);
        map.setView([29.0661, 31.0994], 9);
        addCairoGlowMarker(map);
        enhanceSearchCardMarkup();

        markerLayer = window.L.markerClusterGroup({
            showCoverageOnHover: false,
            removeOutsideVisibleBounds: true,
            chunkedLoading: true,
            maxClusterRadius: 80
        }).addTo(map);

        map.on('moveend', function () {
            window.clearTimeout(loadTimer);
            loadTimer = window.setTimeout(function () { loadMarkers(map); }, 250);
        });

        function refreshLeafletTheme() {
            applyTileLayer(map);
        }
        window.addEventListener('darli:theme-changed', refreshLeafletTheme);
        window.addEventListener('homzen:theme-changed', refreshLeafletTheme);

        window.fetch('/' + locale + '/map/governorates', { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
            .then(function (data) {
                governorates = data.governorates || [];
                chooseInitialGovernorate(map, data.default || 'cairo');
                connectLocationSearch(map);
            })
            .catch(function () {
                map.setView([29.0661, 31.0994], 9);
                loadMarkers(map);
            });
    }

    function applyTileLayer(map) {
        map.eachLayer(function (layer) {
            if (window.L.TileLayer && layer instanceof window.L.TileLayer) {
                map.removeLayer(layer);
            }
        });

        const isLight = mapElement.getAttribute('data-map-theme') === 'light' || document.documentElement.dataset.theme === 'light';
        const definitions = isLight
            ? [
                [lightTiles, { maxNativeZoom: 16, attribution: 'Tiles &copy; Esri' }],
                [lightLabelTiles, { maxNativeZoom: 16 }]
            ]
            : [
                [darkBaseTiles, { maxNativeZoom: 16, attribution: 'Tiles &copy; Esri' }],
                [darkLabelTiles, { maxNativeZoom: 16 }]
            ];

        tileLayers = definitions.map(function (definition) {
            return window.L.tileLayer(definition[0], Object.assign({ maxZoom: 19 }, definition[1])).addTo(map);
        });
    }

    function addCairoGlowMarker(map) {
        if (cairoMarker) {
            map.removeLayer(cairoMarker);
        }
        const isArabic = locale === 'ar';
        const labelHtml = isArabic
            ? 'القاهرة<span>Cairo</span>'
            : 'Cairo<span>القاهرة</span>';

        const cairoIcon = window.L.divIcon({
            className: 'darli-cairo-pulse-icon',
            html: '<div class="darli-cairo-pulse-wrapper">' +
                '<div class="darli-cairo-dot"></div>' +
                '<div class="darli-cairo-label">' + labelHtml + '</div>' +
                '</div>',
            iconSize: [60, 60],
            iconAnchor: [30, 15]
        });

        cairoMarker = window.L.marker([30.0444, 31.2357], {
            icon: cairoIcon,
            zIndexOffset: 2500,
            interactive: false
        }).addTo(map);
    }

    function enhanceSearchCardMarkup() {
        const keywordGroup = document.querySelector('.form-group-1');
        if (keywordGroup) {
            const label = keywordGroup.querySelector('label');
            if (label && !label.querySelector('.label-gold-icon')) {
                label.insertAdjacentHTML('beforeend', ' <svg class="label-gold-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E5B158" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>');
            }
            const posRel = keywordGroup.querySelector('.position-relative');
            if (posRel && !posRel.querySelector('.input-inner-search-icon')) {
                posRel.insertAdjacentHTML('beforeend', '<svg class="input-inner-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>');
            }
        }

        const locationGroup = document.querySelector('.form-group-2');
        if (locationGroup) {
            const label = locationGroup.querySelector('label');
            if (label && !label.querySelector('.label-gold-icon')) {
                label.insertAdjacentHTML('beforeend', ' <svg class="label-gold-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E5B158" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>');
            }
        }

        const categoryGroup = document.querySelector('.form-group-3');
        if (categoryGroup) {
            const label = categoryGroup.querySelector('label');
            if (label && !label.querySelector('.label-gold-icon')) {
                label.insertAdjacentHTML('beforeend', ' <svg class="label-gold-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#E5B158" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.9a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"></path><path d="m22 12.5-8.58 3.9a2 2 0 0 1-1.66 0L2 12.5"></path><path d="m22 17.5-8.58 3.9a2 2 0 0 1-1.66 0L2 17.5"></path></svg>');
            }
        }

        const submitBtn = document.querySelector('.flat-map .wd-find-select > button[type="submit"]') ||
            document.querySelector('.flat-map button.tf-btn[type="submit"]');
        if (submitBtn && !submitBtn.querySelector('.btn-search-icon')) {
            submitBtn.insertAdjacentHTML('afterbegin', '<svg class="btn-search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0B1422" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg> ');
        }
    }

    function chooseInitialGovernorate(map, fallback) {
        focusGovernorate(map, fallback);

        if (!navigator.geolocation || !navigator.permissions?.query) {
            return;
        }

        navigator.permissions.query({ name: 'geolocation' }).then(function (permission) {
            if (permission.state !== 'granted') {
                return;
            }
            navigator.geolocation.getCurrentPosition(function (position) {
                const match = governorates.find(function (governorate) {
                    const bounds = governorate.bounds;
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    return latitude >= bounds[0][0] && latitude <= bounds[1][0]
                        && longitude >= bounds[0][1] && longitude <= bounds[1][1];
                });
                if (match) {
                    focusGovernorate(map, match.key);
                }
            }, function () {
                focusGovernorate(map, fallback);
            }, { enableHighAccuracy: false, timeout: 5000, maximumAge: 3600000 });
        }).catch(function () {
            focusGovernorate(map, fallback);
        });
    }

    function focusGovernorate(map, key) {
        const governorate = governorates.find(function (item) { return item.key === key; });
        if (!governorate) {
            return false;
        }
        window.localStorage.setItem(storageKey, governorate.key);
        map.fitBounds(governorate.bounds, { padding: [18, 18], maxZoom: governorate.zoom || 10 });
        return true;
    }

    function connectLocationSearch(map) {
        document.querySelectorAll('input[name="location"]').forEach(function (input) {
            input.addEventListener('change', function () { focusByName(map, input.value); });
        });

        document.addEventListener('click', function (event) {
            const item = event.target.closest('.search-suggestion-item');
            if (item) {
                focusByName(map, item.textContent || item.dataset.value || '');
            }
        });
    }

    function focusByName(map, value) {
        const search = String(value).trim().toLocaleLowerCase();
        const governorate = governorates.find(function (item) {
            return item.key === search || item.name.toLocaleLowerCase() === search
                || (item.aliases || []).some(function (alias) { return alias.toLocaleLowerCase() === search; });
        });
        if (governorate) {
            focusGovernorate(map, governorate.key);
        } else if (googleMap) {
            geocodeAddress(value);
        }
    }

    function loadMarkers(map) {
        if (!markerLayer) {
            return;
        }
        const bounds = map.getBounds();
        const params = new URLSearchParams({
            sw_lat: bounds.getSouth().toFixed(6),
            sw_lng: bounds.getWest().toFixed(6),
            ne_lat: bounds.getNorth().toFixed(6),
            ne_lng: bounds.getEast().toFixed(6),
            per_page: '200'
        });

        if (requestController) {
            requestController.abort();
        }
        requestController = new AbortController();

        window.fetch('/' + locale + '/ajax/properties/map?' + params.toString(), {
            headers: { Accept: 'application/json' },
            signal: requestController.signal
        }).then(function (response) {
            return response.ok ? response.json() : Promise.reject(new Error('Map request failed'));
        }).then(function (payload) {
            markerLayer.clearLayers();
            (payload.data || []).forEach(addMarker);
        }).catch(function (error) {
            if (error.name !== 'AbortError') {
                markerLayer.clearLayers();
            }
        });
    }

    function addMarker(property) {
        if (!property.latitude || !property.longitude) {
            return;
        }
        const popup = propertyPopup(property);
        const marker = window.L.marker([property.latitude, property.longitude], {
            icon: window.L.divIcon({ iconSize: window.L.point(50, 20), className: 'boxmarker', html: property.map_icon })
        }).bindPopup(popup, { maxWidth: '100%' });
        markerLayer.addLayer(marker);
    }

    function propertyPopup(property) {
        const template = document.querySelector('#property-map-content')?.innerHTML || '';
        const values = {
            __name__: property.name,
            __location__: property.location,
            __image__: property.image_thumb,
            __price__: property.formatted_price,
            __url__: property.url,
            __status__: property.status_html,
            __bedroom__: property.number_bedroom || '0',
            __bathroom__: property.number_bathroom || '0',
            __square__: property.square_text || ''
        };
        return Object.keys(values).reduce(function (html, key) {
            const value = key === '__status__' ? values[key] : escapeHtml(values[key] || '');
            return html.split(key).join(value);
        }, template);
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value);
        return element.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function () { waitForMap(0); });
})();
