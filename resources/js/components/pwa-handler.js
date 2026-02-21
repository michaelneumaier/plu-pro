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

                // Notify service worker of PWA standalone mode
                if (isStandaloneMode() && navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'SET_PWA_MODE' });
                }

                // Also notify when a new controller takes over
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    if (isStandaloneMode() && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({ type: 'SET_PWA_MODE' });
                    }
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

// Standalone mode detection
function isStandaloneMode() {
    // iOS Safari
    if (window.navigator.standalone) {
        return true;
    }

    // Android Chrome and other browsers
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return true;
    }

    // PWA detection via URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('source') === 'pwa') {
        return true;
    }

    return false;
}

// Apply PWA-specific styles and behavior when in standalone mode
if (isStandaloneMode()) {
    document.documentElement.classList.add('pwa-standalone');
    window.__isPWA = true;
    console.log('Running in standalone PWA mode');

    // Hide install button if already installed
    const installButton = document.getElementById('pwa-install-button');
    if (installButton) {
        installButton.style.display = 'none';
    }

    // Eager-cache list pages when on dashboard (pre-fetch for offline use)
    window.addEventListener('load', () => {
        if (navigator.onLine && navigator.serviceWorker?.controller) {
            // Wait a bit for page to fully render
            setTimeout(() => {
                const listLinks = document.querySelectorAll('a[href*="/lists/"]');
                const urls = [];
                listLinks.forEach(a => {
                    const href = a.getAttribute('href');
                    // Only cache list show pages (not edit, create, etc.)
                    if (href && /^\/lists\/\d+$/.test(href)) {
                        urls.push(new URL(href, window.location.origin).href);
                    }
                });

                if (urls.length > 0) {
                    navigator.serviceWorker.controller.postMessage({
                        type: 'CACHE_PAGES',
                        urls: [...new Set(urls)], // deduplicate
                    });
                }
            }, 2000);
        }
    });
}

// Default list helpers (for direct-to-list PWA feature)
window.setAsDefaultList = function(listId) {
    localStorage.setItem('plupro_default_list', String(listId));
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message: 'This list will open when you launch PLUPro', type: 'success' }
    }));
    // Update any UI that tracks this
    window.dispatchEvent(new CustomEvent('default-list-changed', { detail: { listId } }));
};

window.clearDefaultList = function() {
    localStorage.removeItem('plupro_default_list');
    window.dispatchEvent(new CustomEvent('notify', {
        detail: { message: 'Default list cleared', type: 'info' }
    }));
    window.dispatchEvent(new CustomEvent('default-list-changed', { detail: { listId: null } }));
};

window.getDefaultListId = function() {
    return localStorage.getItem('plupro_default_list');
};

// Network status indicator (Alpine.js component)
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
        });
    }
}));
