/**
 * Darli Visitor Intelligence & Geolocation Tracker
 * يلتقط بيانات الزائر، جهازه، شاشته، وموقعه الجغرافي والمركز والمحافظة تلقائياً.
 */
(function() {
    'use strict';

    if (window._darliVisitorTrackerInitialized) return;
    window._darliVisitorTrackerInitialized = true;

    function getVisitorId() {
        var id = localStorage.getItem('darli_visitor_device_id');
        if (!id) {
            id = 'vis_' + Math.random().toString(36).substring(2, 10) + Date.now().toString(36);
            try {
                localStorage.setItem('darli_visitor_device_id', id);
            } catch (e) {}
        }
        return id;
    }

    var visitorId = getVisitorId();

    function collectPayload(extra) {
        return Object.assign({
            visitor_id: visitorId,
            url: window.location.href,
            page_title: document.title || '',
            referrer: document.referrer || '',
            screen_resolution: (window.screen.width || 0) + 'x' + (window.screen.height || 0),
            language: navigator.language || navigator.userLanguage || 'ar',
            user_agent: navigator.userAgent || ''
        }, extra || {});
    }

    function sendTracking(payload) {
        var body = JSON.stringify(payload);
        var url = '/api/visitors/track';

        if (navigator.sendBeacon) {
            var blob = new Blob([body], { type: 'application/json' });
            navigator.sendBeacon(url, blob);
        } else {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: body,
                keepalive: true
            }).catch(function() {});
        }
    }

    function updateGpsLocation(lat, lng, accuracy) {
        fetch('/api/visitors/location', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                visitor_id: visitorId,
                latitude: lat,
                longitude: lng,
                accuracy: accuracy ? Math.round(accuracy) : null
            }),
            keepalive: true
        }).catch(function() {});
    }

    function requestPreciseLocation(forcePrompt) {
        if (!navigator.geolocation) return;

        // التحقق أولاً من صلاحيات المتصفح
        if (navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(function(permissionStatus) {
                if (permissionStatus.state === 'granted') {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        updateGpsLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    }, function() {}, { timeout: 10000, maximumAge: 60000 });
                } else if (forcePrompt && permissionStatus.state === 'prompt') {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        updateGpsLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    }, function() {}, { timeout: 10000 });
                }
            }).catch(function() {
                if (forcePrompt) {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        updateGpsLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    }, function() {}, { timeout: 10000 });
                }
            });
        } else if (forcePrompt) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                updateGpsLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
            }, function() {}, { timeout: 10000 });
        }
    }

    // إرسال التتبع الأولي عند تحميل الصفحة
    function initTracker() {
        // إذا كان المتصفح يمتلك إذن الموقع مسبقاً، نرسله مع الحمولة الأولى
        if (navigator.geolocation && navigator.permissions && navigator.permissions.query) {
            navigator.permissions.query({ name: 'geolocation' }).then(function(res) {
                if (res.state === 'granted') {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        sendTracking(collectPayload({
                            latitude: pos.coords.latitude,
                            longitude: pos.coords.longitude,
                            accuracy: Math.round(pos.coords.accuracy)
                        }));
                    }, function() {
                        sendTracking(collectPayload());
                    }, { timeout: 4000, maximumAge: 60000 });
                } else {
                    sendTracking(collectPayload());
                }
            }).catch(function() {
                sendTracking(collectPayload());
            });
        } else {
            sendTracking(collectPayload());
        }

        // عند تفاعل المستخدم أو النقر، محاولة الحصول على دقة GPS لتحديد المركز بدقة
        var promptTriggered = false;
        function onFirstInteraction() {
            if (promptTriggered) return;
            promptTriggered = true;
            document.removeEventListener('click', onFirstInteraction);
            document.removeEventListener('touchstart', onFirstInteraction);

            setTimeout(function() {
                requestPreciseLocation(false);
            }, 500);
        }

        document.addEventListener('click', onFirstInteraction, { passive: true, once: true });
        document.addEventListener('touchstart', onFirstInteraction, { passive: true, once: true });
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(initTracker, 200);
    } else {
        window.addEventListener('DOMContentLoaded', initTracker);
    }
})();

