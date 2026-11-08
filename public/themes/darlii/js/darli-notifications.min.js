/**
 * Darlii Notifications & Firebase Web Push Client
 */
(function () {
    'use strict';

    const config = window.DarliFirebaseConfig || {};

    window.DarliPush = {
        token: function () {
            return window.DarliPushToken || window.localStorage.getItem('darli_fcm_token') || null;
        }
    };
    if (!config.enabled) {
        return;
    }

    // State
    let isSubscribed = false;
    let notificationsList = [];
    let unreadCount = 0;
    let swRegistration = null;
    let lastSeenNotificationId = parseInt(localStorage.getItem('darli_last_seen_notif_id') || '0', 10);

    // Elements
    let bellDropdown = null;
    let badgeEl = null;

    // Unique Visitor Device ID
    let visitorDeviceId = localStorage.getItem('darli_device_id');
    if (!visitorDeviceId) {
        visitorDeviceId = 'device_' + Math.random().toString(36).substring(2, 11) + '_' + Date.now();
        localStorage.setItem('darli_device_id', visitorDeviceId);
    }

    // Auto-register device immediately
    if (!localStorage.getItem('darli_device_registered')) {
        saveTokenToBackend(visitorDeviceId, null, 'web_visitor', function () {
            localStorage.setItem('darli_device_registered', '1');
        });
    }

    /**
     * Play notification chime using Web Audio API synthesis
     */
    function playNotificationChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;
            const osc1 = ctx.createOscillator();
            const osc2 = ctx.createOscillator();
            const gain = ctx.createGain();

            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now); // E5
            osc1.frequency.exponentialRampToValueAtTime(880, now + 0.12); // A5

            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(880, now + 0.12); // A5
            osc2.frequency.exponentialRampToValueAtTime(1318.51, now + 0.32); // E6

            gain.gain.setValueAtTime(0.35, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.65);

            osc1.connect(gain);
            osc2.connect(gain);
            gain.connect(ctx.destination);

            osc1.start(now);
            osc1.stop(now + 0.16);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.65);
        } catch (e) {
            console.log('[DarliNotifications] Audio notice:', e);
        }
    }

    /**
     * Trigger native system notification in the OS notification curtain / tray ("الستارة")
     */
    function triggerSystemPushNotification(title, body, image, url, tagId) {
        // Physical vibration pattern on supported mobile devices
        if ('vibrate' in navigator) {
            try { navigator.vibrate([300, 100, 300, 100, 300]); } catch (e) {}
        }

        // Native notification in system tray / notification shade
        if ('Notification' in window && Notification.permission === 'granted') {
            const options = {
                body: body || '',
                icon: '/themes/darlii/images/logo/notification-icon.png',
                badge: '/themes/darlii/images/logo/notification-badge.png',
                dir: 'rtl',
                lang: 'ar',
                vibrate: [300, 100, 300, 100, 300],
                renotify: true,
                requireInteraction: true,
                silent: false,
                tag: 'darli-notif-' + (tagId || Date.now()),
                data: {
                    url: url || '/ar'
                }
            };
            if (image) {
                options.image = image;
            }

            if (swRegistration && swRegistration.showNotification) {
                swRegistration.showNotification(title, options);
            } else if (navigator.serviceWorker && navigator.serviceWorker.ready) {
                navigator.serviceWorker.ready.then(function (reg) {
                    reg.showNotification(title, options);
                });
            } else {
                try {
                    const n = new Notification(title, options);
                    n.onclick = function () {
                        window.focus();
                        window.location.href = options.data.url;
                    };
                } catch (e) {}
            }
        }
    }

    /**
     * Initialize Firebase and Service Worker
     */
    function initFirebase() {
        if (!('serviceWorker' in navigator) || !('Notification' in window)) {
            console.log('[DarliNotifications] Web Push not supported on this browser.');
            return;
        }

        // Register Service Worker
        const swUrl = `/firebase-messaging-sw-v2.js?v=2.1.0&apiKey=${encodeURIComponent(config.apiKey || '')}&projectId=${encodeURIComponent(config.projectId || '')}&senderId=${encodeURIComponent(config.messagingSenderId || '')}&appId=${encodeURIComponent(config.appId || '')}`;

        navigator.serviceWorker.register(swUrl)
            .then(function (registration) {
                swRegistration = registration;

                // Initialize Firebase Web App if firebase compat is loaded
                if (window.firebase) {
                    try {
                        if (!firebase.apps.length) {
                            firebase.initializeApp({
                                apiKey: config.apiKey,
                                authDomain: config.authDomain,
                                projectId: config.projectId,
                                storageBucket: config.storageBucket,
                                messagingSenderId: config.messagingSenderId,
                                appId: config.appId,
                                measurementId: config.measurementId
                            });
                        }

                        const messaging = firebase.messaging();

                        // Listen for foreground push messages
                        messaging.onMessage(function (payload) {
                            console.log('[DarliNotifications] Push message received: ', payload);
                            const title = payload.notification?.title || payload.data?.title || 'إشعار جديد من منصة دارلي';
                            const body = payload.notification?.body || payload.data?.body || '';
                            const image = payload.notification?.image || payload.data?.image || null;
                            const targetUrl = payload.data?.url || payload.notification?.click_action || '/ar';
                            const notifId = payload.data?.id || Date.now();

                            playNotificationChime();
                            triggerSystemPushNotification(title, body, image, targetUrl, notifId);
                            showForegroundToast(payload);
                            fetchNotifications(false);
                        });

                        // If already granted, ensure token is refreshed/synced
                        if (Notification.permission === 'granted') {
                            requestAndSaveToken(messaging, registration, visitorDeviceId);
                        } else if (Notification.permission === 'default') {
                            showSoftPrompt(messaging, registration);
                            attachGlobalInteractionListener(messaging, registration);
                        }
                    } catch (e) {
                        console.warn('[DarliNotifications] Firebase initialization notice: ', e.message);
                    }
                }
            })
            .catch(function (err) {
                console.warn('[DarliNotifications] Service Worker registration error: ', err);
            });
    }

    /**
     * Attach global interaction listener to request notification permission on user click
     */
    function attachGlobalInteractionListener(messaging, registration) {
        let triggered = false;

        function handleFirstInteraction(e) {
            if (triggered) return;
            triggered = true;

            // Only trigger if permission is still default
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        const banner = document.getElementById('darli-push-prompt-banner');
                        if (banner) banner.remove();

                        requestAndSaveToken(messaging, registration, visitorDeviceId);
                    }
                }).catch(function (err) {
                    console.log('[DarliNotifications] Permission request error:', err);
                });
            }

            document.removeEventListener('click', handleFirstInteraction, true);
            document.removeEventListener('touchstart', handleFirstInteraction, true);
        }

        document.addEventListener('click', handleFirstInteraction, true);
        document.addEventListener('touchstart', handleFirstInteraction, true);
    }

    /**
     * Request FCM Token and save to backend
     */
    function requestAndSaveToken(messaging, registration, oldToken) {
        // بعد تحديث الـService Worker قد يكون التسجيل لسه installing، وطلب
        // التوكن يفشل. ننتظر ready لضمان أن الـSW نشط قبل getToken.
        const readyRegistration = (registration && registration.active)
            ? Promise.resolve(registration)
            : navigator.serviceWorker.ready;

        readyRegistration.then(function (activeRegistration) {
        const tokenOptions = {
            serviceWorkerRegistration: activeRegistration
        };
        if (config.vapidKey && config.vapidKey.trim() !== '') {
            tokenOptions.vapidKey = config.vapidKey.trim();
        }

        messaging.getToken(tokenOptions)
            .then(function (currentToken) {
                if (currentToken) {
                    // للاختبار والدعم: التوكن متاح من console كـ DarliPush.token()
                    window.DarliPushToken = currentToken;
                    try { console.info('[DarliNotifications] FCM token:', currentToken); } catch (e) {}

                    const lastToken = localStorage.getItem('darli_fcm_token');
                    const lastVapid = localStorage.getItem('darli_fcm_vapid');
                    if (lastToken !== currentToken || lastVapid !== (config.vapidKey || '')) {
                        saveTokenToBackend(currentToken, oldToken || visitorDeviceId, 'web_push', function () {
                            localStorage.setItem('darli_fcm_token', currentToken);
                            localStorage.setItem('darli_fcm_vapid', config.vapidKey || '');
                        });
                    }
                }
            })
            .catch(function (err) {
                console.log('[DarliNotifications] Unable to get token: ', err);
            });
        }).catch(function (err) {
            console.log('[DarliNotifications] Service worker not ready: ', err);
        });
    }

    /**
     * Save token to server API
     */
    function saveTokenToBackend(token, oldToken, deviceType, callback) {
        fetch('/api/notifications/fcm-token', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                token: token,
                old_token: oldToken || null,
                device_type: deviceType || 'web',
                user_id: config.userId || null
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success && typeof callback === 'function') {
                    callback(data);
                }
            })
            .catch(err => console.warn('[DarliNotifications] Error saving token:', err));
    }

    /**
     * Show gentle in-app prompt to allow notifications
     */
    function showSoftPrompt(messaging, registration) {
        if (sessionStorage.getItem('darli_push_prompt_dismissed')) {
            return;
        }

        // On mobile the header bell is enough; keep the screen clear for the
        // floating chat widget instead of showing a push banner.
        if (window.matchMedia && window.matchMedia('(max-width: 991px)').matches) {
            return;
        }

        setTimeout(function () {
            if (Notification.permission !== 'default') return;

            const banner = document.createElement('div');
            banner.id = 'darli-push-prompt-banner';
            banner.innerHTML = `
                <div style="position: fixed; bottom: 24px; right: 24px; z-index: 99999; max-width: 380px; width: calc(100% - 48px); background: var(--surface, #ffffff); border-radius: var(--radius-lg, 16px); box-shadow: var(--shadow-lg, 0 10px 25px rgba(11, 31, 58, 0.12)); border: 1px solid var(--border-color, #e2e8f0); padding: 18px; direction: rtl; font-family: 'Almarai', sans-serif; animation: darliFadeUp 0.35s ease;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: var(--radius-md, 12px); background: var(--brand-gold-light, #f7f0e3); color: var(--brand-gold, #c99a45); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: var(--text-primary, #0f172a);">تفعيل إشعارات دارلي</h4>
                            <p style="margin: 0 0 12px 0; font-size: 12.5px; color: var(--text-muted, #64748b); line-height: 1.45;">ابقَ على اطلاع بأحدث العقارات وتحديثات الأسعار الفورية في منطقتك فور إضافتها.</p>
                            <div style="display: flex; gap: 8px;">
                                <button id="darli-enable-push-btn" style="background: linear-gradient(135deg, var(--brand-navy, #0b1f3a) 0%, var(--brand-navy-dark, #071527) 100%); color: #fff; border: none; padding: 7px 16px; border-radius: var(--radius-sm, 8px); font-size: 13px; font-weight: 700; cursor: pointer; flex: 1; transition: background 0.2s; box-shadow: 0 3px 10px rgba(11, 31, 58, 0.2);">
                                    تفعيل الإشعارات
                                </button>
                                <button id="darli-dismiss-push-btn" style="background: var(--surface-elevated, #f8fafc); color: var(--text-secondary, #334155); border: 1px solid var(--border-color, #e2e8f0); padding: 7px 14px; border-radius: var(--radius-sm, 8px); font-size: 13px; cursor: pointer;">
                                    لاحقاً
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(banner);

            document.getElementById('darli-enable-push-btn').addEventListener('click', function () {
                banner.remove();
                Notification.requestPermission().then(function (permission) {
                    if (permission === 'granted') {
                        requestAndSaveToken(messaging, registration);
                    }
                });
            });

            document.getElementById('darli-dismiss-push-btn').addEventListener('click', function () {
                banner.remove();
                sessionStorage.setItem('darli_push_prompt_dismissed', '1');
            });
        }, 3500);
    }

    /**
     * Show rich foreground toast message
     */
    function showForegroundToast(payload) {
        const title = payload.notification?.title || payload.data?.title || 'إشعار جديد';
        const body = payload.notification?.body || payload.data?.body || '';
        const image = payload.notification?.image || payload.data?.image || null;
        const url = payload.data?.url || payload.notification?.click_action || '/ar';

        const toast = document.createElement('div');
        toast.className = 'darli-notification-toast';
        toast.style.cssText = `
            position: fixed; top: 24px; left: 24px; z-index: 100000; width: 360px; max-width: calc(100% - 48px);
            background: #ffffff; border-radius: 16px; box-shadow: 0 12px 35px rgba(0,0,0,0.18);
            border: 1px solid rgba(0,0,0,0.08); padding: 16px; direction: rtl; font-family: 'Almarai', sans-serif;
            cursor: pointer; transition: transform 0.2s ease, opacity 0.2s ease; animation: darliFadeDown 0.35s ease;
        `;

        toast.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <img src="/themes/darlii/images/logo/notification-icon.png" style="width: 20px; height: 20px; object-fit: contain;">
                    <span style="font-size: 12px; font-weight: 700; color: #1e293b;">دارلي العقارية</span>
                </div>
                <button class="toast-close" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer; padding: 0 4px; line-height: 1;">&times;</button>
            </div>
            <div style="display: flex; gap: 12px; align-items: flex-start;">
                <div style="flex: 1;">
                    <h5 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 700; color: #0f172a;">${escapeHtml(title)}</h5>
                    <p style="margin: 0; font-size: 12.5px; color: #64748b; line-height: 1.4;">${escapeHtml(body)}</p>
                </div>
                ${image ? `<img src="${escapeHtml(image)}" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover; flex-shrink: 0; border: 1px solid rgba(0,0,0,0.06);">` : ''}
            </div>
        `;

        toast.querySelector('.toast-close').addEventListener('click', function (e) {
            e.stopPropagation();
            toast.remove();
        });

        toast.addEventListener('click', function () {
            toast.remove();
            window.location.href = url;
        });

        document.body.appendChild(toast);

        setTimeout(function () {
            if (document.body.contains(toast)) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 250);
            }
        }, 7000);
    }

    /**
     * Fetch In-App Notifications with periodic new-item detection
     */
    function fetchNotifications(isPeriodicCheck = false) {
        fetch('/api/notifications/list', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    unreadCount = data.unread_count || 0;
                    notificationsList = data.notifications || [];
                    updateNotificationBadge();
                    renderNotificationsDropdown();

                    // If periodic check finds a brand-new notification that hasn't been presented
                    if (notificationsList.length > 0) {
                        const newest = notificationsList[0];
                        const newestId = parseInt(newest.id, 10);

                        if (isPeriodicCheck && lastSeenNotificationId > 0 && newestId > lastSeenNotificationId && !newest.is_read) {
                            playNotificationChime();
                            triggerSystemPushNotification(newest.title, newest.body, newest.image_url, newest.action_url, newest.id);
                            showForegroundToast({
                                notification: {
                                    title: newest.title,
                                    body: newest.body,
                                    image: newest.image_url
                                },
                                data: {
                                    url: newest.action_url || '/ar',
                                    id: newest.id
                                }
                            });
                        }

                        if (newestId > lastSeenNotificationId) {
                            lastSeenNotificationId = newestId;
                            localStorage.setItem('darli_last_seen_notif_id', newestId.toString());
                        }
                    }
                }
            })
            .catch(err => console.log('[DarliNotifications] fetch error:', err));
    }

    /**
     * Update Bell Badge
     */
    function updateNotificationBadge() {
        const badges = document.querySelectorAll('.darli-notif-badge');
        badges.forEach(b => {
            if (unreadCount > 0) {
                b.textContent = unreadCount > 99 ? '99+' : unreadCount;
                b.style.display = 'inline-flex';
            } else {
                b.style.display = 'none';
            }
        });
    }

    /**
     * Render Notifications in Dropdown
     */
    function renderNotificationsDropdown() {
        const containers = document.querySelectorAll('.darli-notif-dropdown-list');
        containers.forEach(container => {
            if (notificationsList.length === 0) {
                container.innerHTML = `
                    <div style="padding: 30px 16px; text-align: center; color: #94a3b8; font-size: 13px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 8px auto; display: block; opacity: 0.5;"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                        لا توجد إشعارات جديدة حالياً
                    </div>
                `;
                return;
            }

            let html = '';
            notificationsList.forEach(item => {
                html += `
                    <a href="${escapeHtml(item.action_url || '/ar')}" class="darli-notif-item ${item.is_read ? 'is-read' : 'is-unread'}" data-id="${item.id}" style="display: flex; gap: 12px; padding: 12px 16px; border-bottom: 1px solid rgba(0,0,0,0.05); text-decoration: none; color: inherit; transition: background 0.15s; background: ${item.is_read ? '#fff' : '#f8fafc'};">
                        ${item.image_url ? `<img src="${escapeHtml(item.image_url)}" style="width: 44px; height: 44px; border-radius: 8px; object-fit: cover; flex-shrink: 0; border: 1px solid rgba(0,0,0,0.05);">` : `<div style="width: 44px; height: 44px; border-radius: 8px; background: rgba(227, 44, 43, 0.08); color: #E32C2B; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg></div>`}
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(item.title)}</div>
                            <div style="font-size: 12px; color: #64748b; line-height: 1.35; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${escapeHtml(item.body)}</div>
                            <div style="font-size: 11px; color: #94a3b8;">${escapeHtml(item.created_at_human)}</div>
                        </div>
                    </a>
                `;
            });

            container.innerHTML = html;

            container.querySelectorAll('.darli-notif-item').forEach(el => {
                el.addEventListener('click', function () {
                    const id = this.getAttribute('data-id');
                    if (id) {
                        markNotificationAsRead(id);
                    }
                });
            });
        });
    }

    function markNotificationAsRead(id) {
        fetch(`/api/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
    }

    function markAllAsRead() {
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        }).then(() => {
            unreadCount = 0;
            notificationsList.forEach(n => n.is_read = true);
            updateNotificationBadge();
            renderNotificationsDropdown();
        });
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Attach mark all as read buttons
    document.addEventListener('click', function (e) {
        if (e.target.matches('.darli-mark-all-read-btn') || e.target.closest('.darli-mark-all-read-btn')) {
            e.preventDefault();
            markAllAsRead();
        }
    });

    // Run on page load
    document.addEventListener('DOMContentLoaded', function () {
        initFirebase();
        fetchNotifications(false);

        // Periodic check every 45s for instant live notifications while page is open
        setInterval(function () {
            fetchNotifications(true);
        }, 45000);
    });

})();
