importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-auth.js');

firebase.initializeApp({
    apiKey: "AIzaSyAb8UDuBj-90yKfQfayLSSTK90xqYzPPPo",
    authDomain: "elnisr-7dfb0.firebaseapp.com",
    databaseURL: "https://elnisr-7dfb0-default-rtdb.europe-west1.firebasedatabase.app",
    projectId: "elnisr-7dfb0",
    storageBucket: "elnisr-7dfb0.firebasestorage.app",
    messagingSenderId: "786488962854",
    appId: "1:786488962854:web:83afe734c6ec8a14b91be7",
    measurementId: "G-MNLG9BRV5N"
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body || '',
        icon: payload.data.icon || ''
    });
});
