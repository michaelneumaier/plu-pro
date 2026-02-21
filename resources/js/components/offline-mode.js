// Offline mode Alpine.js store for PWA offline state management
document.addEventListener('alpine:init', () => {
    Alpine.store('offlineMode', {
        isOffline: !navigator.onLine,
        isPWA: false,

        init() {
            this.isPWA = document.documentElement.classList.contains('pwa-standalone');

            window.addEventListener('online', () => {
                this.isOffline = false;
            });

            window.addEventListener('offline', () => {
                this.isOffline = true;
            });
        },
    });
});
