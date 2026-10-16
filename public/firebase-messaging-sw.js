// Firebase Messaging Service Worker for Darlii
importScripts('https://www.gstatic.com/firebasejs/10.13.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.0/firebase-messaging-compat.js');

const urlParams = new URL(location).searchParams;
const apiKey = urlParams.get('apiKey') || 'AIzaSyBNv6O81fJnehjTBw12cc9IZOT-32Bfy9Q';
const projectId = urlParams.get('projectId') || 'khademate2';
const messagingSenderId = urlParams.get('senderId') || '504226887841';
const appId = urlParams.get('appId') || '1:504226887841:web:cbc5abdc30304c6014d8e0';

firebase.initializeApp({
    apiKey: apiKey,
    authDomain: projectId + '.firebaseapp.com',
    projectId: projectId,
    storageBucket: projectId + '.firebasestorage.app',
    messagingSenderId: messagingSenderId,
    appId: appId
});

const messaging = firebase.messaging();

function extractNotificationData(payload) {
    const title = payload.notification?.title || payload.data?.title || 'إشعار جديد من منصة دارلي';
    const body = payload.notification?.body || payload.data?.body || '';
    const icon = payload.notification?.icon || payload.data?.icon || '/themes/homzen/images/logo/logo-darli.png';
    const image = payload.notification?.image || payload.data?.image || null;
    const url = payload.data?.url || payload.notification?.click_action || '/ar';
    const tag = payload.data?.tag || ('darli-notif-' + (payload.data?.id || Date.now()));

    const options = {
        body: body,
        icon: icon,
        badge: '/themes/homzen/images/logo/favicon.png',
        dir: 'rtl',
        lang: 'ar',
        vibrate: [300, 100, 300, 100, 300],
        renotify: true,
        requireInteraction: true,
        silent: false,
        tag: tag,
        data: {
            url: url,
            time: Date.now()
        }
    };

    if (image) {
        options.image = image;
    }

    return { title, options };
}

// Handle FCM background message
messaging.onBackgroundMessage(function (payload) {
    console.log('[DarliSW] Background message received: ', payload);
    const { title, options } = extractNotificationData(payload);
    return self.registration.showNotification(title, options);
});

// Fallback native push listener for standard Web Push payloads
self.addEventListener('push', function (event) {
    if (!event.data) return;

    try {
        const payload = event.data.json();
        // If message has notification or data
        if (payload.notification || payload.data) {
            const { title, options } = extractNotificationData(payload);
            event.waitUntil(self.registration.showNotification(title, options));
        }
    } catch (e) {
        // Plain text message
        const text = event.data.text();
        if (text) {
            event.waitUntil(self.registration.showNotification('إشعار جديد من دارلي', {
                body: text,
                icon: '/themes/homzen/images/logo/logo-darli.png',
                vibrate: [300, 100, 300, 100, 300],
                tag: 'darli-raw-' + Date.now(),
                data: { url: '/ar' }
            }));
        }
    }
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    const targetUrl = event.notification.data?.url || '/ar';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === targetUrl && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
