<?php
    $latId = $latId ?? 'prop-latitude';
    $lngId = $lngId ?? 'prop-longitude';
    $latName = $latName ?? 'latitude';
    $lngName = $lngName ?? 'longitude';
    $latValue = $latValue ?? null;
    $lngValue = $lngValue ?? null;
    $containerId = $containerId ?? 'darli-map-picker-box-' . uniqid();
    $mapId = $mapId ?? 'darli-leaflet-picker-' . uniqid();
    $defaultCity = $defaultCity ?? 'بني سويف';
    $defaultLat = $defaultLat ?? 29.0724;
    $defaultLng = $defaultLng ?? 31.0962;
?>

<?php if (! $__env->hasRenderedOnce('8a9d2c6e-eea8-4ee5-be37-f01f1ebc605b')): $__env->markAsRenderedOnce('8a9d2c6e-eea8-4ee5-be37-f01f1ebc605b');
$__env->startPush('head'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('themes/homzen/plugins/leaflet/leaflet.css')); ?>">
    <style>
        .darli-map-picker-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 18px;
            margin-bottom: 18px;
            transition: border-color 0.2s ease;
        }
        .darli-map-picker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }
        .darli-map-picker-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0F3D2E;
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 700;
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 61, 46, 0.2);
            transition: all 0.2s ease;
        }
        .darli-map-picker-btn:hover {
            background: #14523d;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(15, 61, 46, 0.3);
        }
        .darli-map-picker-btn.is-active {
            background: #b91c1c;
        }
        .darli-map-input-wrap {
            position: relative;
        }
        .darli-map-input-icon {
            position: absolute;
            inset-inline-end: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .darli-map-input-icon:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.15);
        }
        .darli-coord-input.is-updated {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2) !important;
            transition: all 0.3s ease;
        }
        .darli-picker-map-container {
            margin-top: 16px;
            border: 2px solid #0F3D2E;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            animation: darliSlideDown 0.25s ease-out;
        }
        @keyframes darliSlideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .darli-picker-toolbar {
            padding: 10px 14px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .darli-picker-search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 260px;
        }
        .darli-picker-search-input {
            width: 100%;
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 13px;
            background: #ffffff;
            color: #0f172a;
        }
        .darli-picker-search-input:focus {
            outline: none;
            border-color: #0F3D2E;
            box-shadow: 0 0 0 3px rgba(15, 61, 46, 0.15);
        }
        .darli-picker-hint {
            background: #eff6ff;
            padding: 8px 14px;
            font-size: 12.5px;
            color: #1e40af;
            border-bottom: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .darli-picker-footer {
            padding: 10px 16px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .darli-picker-pin-wrapper {
            position: relative;
            transform: translate(-50%, -100%);
            width: 38px;
            height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: grab;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        .darli-picker-pin-body {
            width: 38px;
            height: 38px;
            background: #db1d23;
            border: 3px solid #ffffff;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .darli-picker-pin-dot {
            width: 12px;
            height: 12px;
            background: #ffffff;
            border-radius: 50%;
            transform: rotate(45deg);
        }
        .darli-picker-pin-shadow {
            width: 16px;
            height: 6px;
            background: rgba(0,0,0,0.35);
            border-radius: 50%;
            margin-top: -3px;
            filter: blur(1px);
        }
        .darli-layer-switcher-btn {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            color: #334155;
            transition: all 0.15s ease;
        }
        .darli-layer-switcher-btn.active {
            background: #0F3D2E;
            color: #ffffff;
            border-color: #0F3D2E;
        }
    </style>
<?php $__env->stopPush(); endif; ?>

<div class="darli-map-picker-card">
    <div class="darli-map-picker-header">
        <div>
            <label class="ap-label" style="margin-bottom: 2px; font-weight: 800; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                <span>📍 الموقع الجغرافي والإحداثيات (GPS)</span>
            </label>
            <div style="font-size: 12.5px; color: #64748b;">
                اضغط على أيقونة أو زر الخريطة لاختيار مكان العقار بالماوس مباشرة ليتم إدراج القيم تلقائياً
            </div>
        </div>
        <button type="button" id="btn-toggle-<?php echo e($containerId); ?>" class="darli-map-picker-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon><line x1="8" y1="2" x2="8" y2="18"></line><line x1="16" y1="6" x2="16" y2="22"></line></svg>
            <span id="btn-text-<?php echo e($containerId); ?>">🗺️ فتح الخريطة لتحديد الموقع</span>
        </button>
    </div>

    <div class="ap-grid ap-grid--2">
        <div class="ap-field" style="margin-bottom: 0;">
            <label class="ap-label" for="<?php echo e($latId); ?>" style="font-size: 12.5px; font-weight: 700;">
                خط العرض (Latitude)
            </label>
            <div class="darli-map-input-wrap">
                <input type="text" id="<?php echo e($latId); ?>" name="<?php echo e($latName); ?>"
                    class="ap-input darli-coord-input"
                    value="<?php echo e($latValue); ?>"
                    placeholder="مثال: 29.0724120"
                    style="padding-inline-end: 38px;">
                <button type="button" class="darli-map-input-icon btn-open-map-<?php echo e($containerId); ?>" title="فتح الخريطة واختيار الموقع">
                    🗺️
                </button>
            </div>
        </div>

        <div class="ap-field" style="margin-bottom: 0;">
            <label class="ap-label" for="<?php echo e($lngId); ?>" style="font-size: 12.5px; font-weight: 700;">
                خط الطول (Longitude)
            </label>
            <div class="darli-map-input-wrap">
                <input type="text" id="<?php echo e($lngId); ?>" name="<?php echo e($lngName); ?>"
                    class="ap-input darli-coord-input"
                    value="<?php echo e($lngValue); ?>"
                    placeholder="مثال: 31.0961850"
                    style="padding-inline-end: 38px;">
                <button type="button" class="darli-map-input-icon btn-open-map-<?php echo e($containerId); ?>" title="فتح الخريطة واختيار الموقع">
                    🗺️
                </button>
            </div>
        </div>
    </div>

    
    <div id="<?php echo e($containerId); ?>" class="darli-picker-map-container" style="display: none;">
        
        <div class="darli-picker-toolbar">
            <div class="darli-picker-search-wrap">
                <input type="text" id="search-input-<?php echo e($containerId); ?>" class="darli-picker-search-input"
                    placeholder="🔍 ابحث عن مدينة، حي، شارع أو معلم (مثال: بني سويف، صلاح سالم، المعادي...)">
                <button type="button" id="search-btn-<?php echo e($containerId); ?>" class="ap-btn ap-btn--sm"
                    style="white-space: nowrap; padding: 8px 14px; font-size: 12.5px; background: #0F3D2E; color: #fff; border: none; border-radius: 8px;">
                    بحث
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <button type="button" id="layer-streets-<?php echo e($containerId); ?>" class="darli-layer-switcher-btn active" title="خريطة الشوارع">
                    🗺️ شوارع
                </button>
                <button type="button" id="layer-satellite-<?php echo e($containerId); ?>" class="darli-layer-switcher-btn" title="صور القمر الصناعي">
                    🛰️ قمر صناعي
                </button>
                <button type="button" id="gps-btn-<?php echo e($containerId); ?>" class="ap-btn ap-btn--sm ap-btn--secondary" style="font-size: 12px; padding: 6px 12px; border-radius: 6px;" title="تحديد موقعي الحالي عبر الـ GPS">
                    📍 موقعي الحالي
                </button>
                <button type="button" id="close-btn-<?php echo e($containerId); ?>" class="ap-btn ap-btn--sm ap-btn--ghost" style="font-size: 12px; padding: 6px 10px; color: #64748b;">
                    ✕ إخفاء
                </button>
            </div>
        </div>

        
        <div class="darli-picker-hint">
            <span>💡 <strong>كيفية التحديد:</strong> انقر بالماوس على أي نقطة على الخريطة أو اسحب المؤشر الأحمر لتثبيت المكان بدقة وسيتم تحديث خطوط الطول والعرض فوراً.</span>
            <span id="click-feedback-<?php echo e($containerId); ?>" style="font-weight: 700; color: #15803d; display: none;">
                ✓ تم تحديث الحقول
            </span>
        </div>

        
        <div id="<?php echo e($mapId); ?>" style="width: 100%; height: 420px; position: relative;"></div>

        
        <div class="darli-picker-footer">
            <div style="font-size: 13px; color: #334155;">
                <span style="color: #64748b;">الإحداثيات المختارة:</span>
                <code id="coords-display-<?php echo e($containerId); ?>" style="background: #e2e8f0; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12.5px; color: #0F3D2E;">
                    <?php echo e($latValue && $lngValue ? $latValue . ', ' . $lngValue : 'انقر على الخريطة لتحديد الإحداثيات'); ?>

                </code>
            </div>
            <button type="button" id="confirm-btn-<?php echo e($containerId); ?>" class="ap-btn ap-btn--sm"
                style="background: #15803d; color: #fff; font-weight: 700; padding: 7px 18px; border-radius: 8px; border: none; font-size: 13px;">
                ✓ تأكيد هذا المكان وإغلاق الخريطة
            </button>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<?php if (! $__env->hasRenderedOnce('b9a6455b-4170-4632-b57b-67ab585b7798')): $__env->markAsRenderedOnce('b9a6455b-4170-4632-b57b-67ab585b7798'); ?>
    <script src="<?php echo e(asset('themes/homzen/plugins/leaflet/leaflet.js')); ?>"></script>
<?php endif; ?>
<script>
    (function () {
        var container = document.getElementById('<?php echo e($containerId); ?>');
        var toggleBtn = document.getElementById('btn-toggle-<?php echo e($containerId); ?>');
        var toggleText = document.getElementById('btn-text-<?php echo e($containerId); ?>');
        var closeBtn = document.getElementById('close-btn-<?php echo e($containerId); ?>');
        var confirmBtn = document.getElementById('confirm-btn-<?php echo e($containerId); ?>');
        var quickOpenBtns = document.querySelectorAll('.btn-open-map-<?php echo e($containerId); ?>');

        var latInput = document.getElementById('<?php echo e($latId); ?>');
        var lngInput = document.getElementById('<?php echo e($lngId); ?>');
        var coordsDisplay = document.getElementById('coords-display-<?php echo e($containerId); ?>');
        var feedback = document.getElementById('click-feedback-<?php echo e($containerId); ?>');

        var searchInput = document.getElementById('search-input-<?php echo e($containerId); ?>');
        var searchBtn = document.getElementById('search-btn-<?php echo e($containerId); ?>');
        var gpsBtn = document.getElementById('gps-btn-<?php echo e($containerId); ?>');

        var layerStreetsBtn = document.getElementById('layer-streets-<?php echo e($containerId); ?>');
        var layerSatelliteBtn = document.getElementById('layer-satellite-<?php echo e($containerId); ?>');

        var map = null;
        var marker = null;
        var streetLayer = null;
        var satelliteLayer = null;

        var customPinIcon = (typeof L !== 'undefined') ? L.divIcon({
            className: 'darli-picker-pin',
            html: `
                <div class="darli-picker-pin-wrapper">
                    <div class="darli-picker-pin-body">
                        <div class="darli-picker-pin-dot"></div>
                    </div>
                    <div class="darli-picker-pin-shadow"></div>
                </div>
            `,
            iconSize: [0, 0],
            iconAnchor: [0, 0]
        }) : null;

        function setCoords(lat, lng, pan) {
            var fixedLat = Number(lat).toFixed(7);
            var fixedLng = Number(lng).toFixed(7);

            latInput.value = fixedLat;
            lngInput.value = fixedLng;

            // Flash visual feedback on inputs
            latInput.classList.add('is-updated');
            lngInput.classList.add('is-updated');
            setTimeout(function () {
                latInput.classList.remove('is-updated');
                lngInput.classList.remove('is-updated');
            }, 1000);

            coordsDisplay.textContent = fixedLat + ', ' + fixedLng;
            feedback.style.display = 'inline-block';
            feedback.textContent = '✓ تم تحديد: ' + Number(lat).toFixed(5) + ', ' + Number(lng).toFixed(5);

            if (!marker && typeof L !== 'undefined') {
                marker = L.marker([lat, lng], {
                    icon: customPinIcon,
                    draggable: true
                }).addTo(map);

                marker.on('dragend', function (e) {
                    var pos = e.target.getLatLng();
                    setCoords(pos.lat, pos.lng, false);
                });
            } else if (marker) {
                marker.setLatLng([lat, lng]);
            }

            if (pan && map) {
                map.panTo([lat, lng]);
            }
        }

        function initMap() {
            if (map || typeof L === 'undefined') return;

            var rawLat = parseFloat(latInput.value);
            var rawLng = parseFloat(lngInput.value);
            var hasExisting = !isNaN(rawLat) && !isNaN(rawLng) && rawLat !== 0;

            var initialLat = hasExisting ? rawLat : <?php echo e((float) $defaultLat); ?>;
            var initialLng = hasExisting ? rawLng : <?php echo e((float) $defaultLng); ?>;
            var initialZoom = hasExisting ? 16 : 13;

            map = L.map('<?php echo e($mapId); ?>', {
                center: [initialLat, initialLng],
                zoom: initialZoom,
                scrollWheelZoom: true
            });

            // Street tile layer (OpenStreetMap standard)
            streetLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Satellite tile layer (Esri World Imagery)
            satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });

            if (hasExisting) {
                setCoords(rawLat, rawLng, false);
            }

            // Click on map sets coordinates
            map.on('click', function (e) {
                setCoords(e.latlng.lat, e.latlng.lng, false);
            });

            // Listen to manual input changes to sync map marker
            function onInputChange() {
                var curLat = parseFloat(latInput.value);
                var curLng = parseFloat(lngInput.value);
                if (!isNaN(curLat) && !isNaN(curLng)) {
                    setCoords(curLat, curLng, true);
                }
            }
            latInput.addEventListener('change', onInputChange);
            lngInput.addEventListener('change', onInputChange);
        }

        function showMap() {
            container.style.display = 'block';
            toggleBtn.classList.add('is-active');
            toggleText.textContent = '✕ إخفاء الخريطة';
            initMap();
            setTimeout(function () {
                if (map) {
                    map.invalidateSize();
                    var l1 = parseFloat(latInput.value);
                    var l2 = parseFloat(lngInput.value);
                    if (!isNaN(l1) && !isNaN(l2) && l1 !== 0) {
                        map.panTo([l1, l2]);
                    }
                }
            }, 200);
            container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function hideMap() {
            container.style.display = 'none';
            toggleBtn.classList.remove('is-active');
            toggleText.textContent = '🗺️ فتح الخريطة لتحديد الموقع';
        }

        toggleBtn.addEventListener('click', function () {
            if (container.style.display === 'none') {
                showMap();
            } else {
                hideMap();
            }
        });

        quickOpenBtns.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                showMap();
            });
        });

        if (closeBtn) closeBtn.addEventListener('click', hideMap);
        if (confirmBtn) confirmBtn.addEventListener('click', hideMap);

        // Layer Switchers
        if (layerStreetsBtn && layerSatelliteBtn) {
            layerStreetsBtn.addEventListener('click', function () {
                layerStreetsBtn.classList.add('active');
                layerSatelliteBtn.classList.remove('active');
                if (map) {
                    map.removeLayer(satelliteLayer);
                    map.addLayer(streetLayer);
                }
            });

            layerSatelliteBtn.addEventListener('click', function () {
                layerSatelliteBtn.classList.add('active');
                layerStreetsBtn.classList.remove('active');
                if (map) {
                    map.removeLayer(streetLayer);
                    map.addLayer(satelliteLayer);
                }
            });
        }

        // GPS Geolocation
        if (gpsBtn && navigator.geolocation) {
            gpsBtn.addEventListener('click', function () {
                var origText = gpsBtn.textContent;
                gpsBtn.disabled = true;
                gpsBtn.textContent = '⏳ جاري تحديد موقعك...';

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        gpsBtn.disabled = false;
                        gpsBtn.textContent = origText;
                        var lat = pos.coords.latitude;
                        var lng = pos.coords.longitude;
                        if (map) {
                            map.setView([lat, lng], 17);
                        }
                        setCoords(lat, lng, true);
                    },
                    function (err) {
                        gpsBtn.disabled = false;
                        gpsBtn.textContent = origText;
                        alert('تعذر جلب موقعك الجغرافي: ' + (err.message || 'يرجى تفعيل خدمة الموقع والسماح للمتصفح.'));
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            });
        }

        // Search Bar Functionality
        if (searchBtn && searchInput) {
            function doSearch() {
                var query = searchInput.value.trim();
                if (!query) return;

                var origText = searchBtn.textContent;
                searchBtn.disabled = true;
                searchBtn.textContent = '⏳ جاري البحث...';

                var url = 'https://nominatim.openstreetmap.org/search?format=json&countrycodes=eg&q=' + encodeURIComponent(query);
                fetch(url)
                    .then(function (r) { return r.json(); })
                    .then(function (results) {
                        if (results && results.length > 0) {
                            var best = results[0];
                            var lat = parseFloat(best.lat);
                            var lon = parseFloat(best.lon);
                            if (map) {
                                map.flyTo([lat, lon], 16, { duration: 1.2 });
                            }
                            setCoords(lat, lon, false);
                        } else {
                            alert('لم يتم العثور على نتائج للعنوان المكتوب. جرّب كتابة اسم المحافظة أو الحي الرئيسي.');
                        }
                    })
                    .catch(function (err) {
                        console.error('Map search error:', err);
                        alert('حدث خطأ في الاتصال بالبحث عن العناوين.');
                    })
                    .finally(function () {
                        searchBtn.disabled = false;
                        searchBtn.textContent = origText;
                    });
            }

            searchBtn.addEventListener('click', doSearch);
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    doSearch();
                }
            });
        }
    })();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /var/www/html/resources/views/admin/partials/map-picker.blade.php ENDPATH**/ ?>