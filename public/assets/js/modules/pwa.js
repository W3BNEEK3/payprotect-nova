if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('PWA ServiceWorker registered successfully with scope: ', registration.scope);
            })
            .catch(error => {
                console.error('PWA ServiceWorker registration failed: ', error);
            });
    });
}
