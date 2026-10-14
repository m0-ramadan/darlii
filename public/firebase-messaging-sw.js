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

messaging.onBackgroundMessage(function (payload) {
    const notificationTitle = payload.notification?.title || payload.data?.title || 'إشعار جديد من منصة دارلي';
    const notificationOptions = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: payload.notification?.icon || '/themes/homzen/images/logo/logo-darli.png',
        image: payload.notification?.image || payload.data?.image || null,
        badge: '/themes/homzen/images/logo/favicon.png',
        dir: 'rtl',
        lang: 'ar',
        data: {
            url: payload.data?.url || payload.notification?.click_action || '/ar'
        }
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
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

