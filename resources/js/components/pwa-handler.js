// PWA installation and update handler
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('ServiceWorker registered:', registration);
                
                // Check for updates periodically
                setInterval(() => {
                    registration.update();
                }, 60000); // Every minute
                
                // Handle updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'activated' && navigator.serviceWorker.controller) {
                            // New service worker activated, show update prompt
                            if (confirm('A new version is available. Reload to update?')) {
                                window.location.reload();
                            }
                        }
                    });
                });
            })
            .catch(error => {
                console.error('ServiceWorker registration failed:', error);
            });
    });
}

// Handle PWA install prompt
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    
    // Show custom install button
    const installButton = document.getElementById('pwa-install-button');
    if (installButton) {
        installButton.style.display = 'block';
        
        installButton.addEventListener('click', () => {
            // Hide the button
            installButton.style.display = 'none';
            // Show the prompt
            deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            deferredPrompt.userChoice.then((choiceResult) => {
                if (choiceResult.outcome === 'accepted') {
                    console.log('User accepted the install prompt');
                }
                deferredPrompt = null;
            });
        });
    }
});

// Network status indicator
Alpine.data('networkStatus', () => ({
    online: navigator.onLine,
    showOfflineMessage: false,
    
    init() {
        window.addEventListener('online', () => {
            this.online = true;
            this.showOfflineMessage = false;
        });
        
        window.addEventListener('offline', () => {
            this.online = false;
            this.showOfflineMessage = true;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.showOfflineMessage = false;
            }, 5000);
        });
    }
}));