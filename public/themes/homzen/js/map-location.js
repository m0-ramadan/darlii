(function () {
    'use strict';

    const mapElement = document.querySelector('[data-bb-toggle="list-map"]');
    if (!mapElement) {
        return;
    }

    const locale = document.documentElement.lang || 'en';
    const storageKey = 'visitor_governorate';
    const lightTiles = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const darkBaseTiles = 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}';
    const darkLabelTiles = 'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Reference/MapServer/tile/{z}/{y}/{x}';
    let governorates = [];
    let markerLayer = null;
    let tileLayers = [];
    let requestController = null;
    let loadTimer = null;

    function waitForMap(attempt) {
        if (window.activeMap && window.L) {
            setup(window.activeMap);
            return;
        }
        if (attempt < 40) {
            window.setTimeout(function () { waitForMap(attempt + 1); }, 100);
        }
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

        window.addEventListener('homzen:theme-changed', function () {
            applyTileLayer(map);
        });

        window.fetch('/' + locale + '/map/governorates', { headers: { Accept: 'application/json' } })
            .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
            .then(function (data) {
                governorates = data.governorates || [];
                chooseInitialGovernorate(map, data.default || 'cairo');
                connectLocationSearch(map);
            })
            .catch(function () {
                map.setView([30.0444, 31.2357], 10);
                loadMarkers(map);
            });
    }

    function applyTileLayer(map) {
        map.eachLayer(function (layer) {
            if (window.L.TileLayer && layer instanceof window.L.TileLayer) {
                map.removeLayer(layer);
            }
        });

        const dark = document.documentElement.dataset.theme === 'dark';
        const definitions = dark
            ? [
                [darkBaseTiles, { maxNativeZoom: 16, attribution: 'Tiles &copy; Esri' }],
                [darkLabelTiles, { maxNativeZoom: 16 }]
            ]
            : [[lightTiles, { attribution: '&copy; OpenStreetMap contributors' }]];

        tileLayers = definitions.map(function (definition) {
            return window.L.tileLayer(definition[0], Object.assign({ maxZoom: 19 }, definition[1])).addTo(map);
        });
    }

    function chooseInitialGovernorate(map, fallback) {
        const saved = window.localStorage.getItem(storageKey);
        if (saved && focusGovernorate(map, saved)) {
            return;
        }

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
