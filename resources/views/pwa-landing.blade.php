<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loading PLUPro...</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#a9e8d3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PLUPro">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Basic styling -->
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #10b981;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        .loading {
            max-width: 300px;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .offline-message {
            display: none;
            margin-top: 1rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <div class="loading">
        <div class="logo">
            <img src="{{ asset('logo.png') }}" alt="PLUPro Logo" style="height: 60px; width: auto;">
        </div>
        <div id="status">Loading your workspace...</div>
        <div class="spinner" id="spinner"></div>
        <div class="offline-message" id="offline-message">
            Loading from saved data...
        </div>
    </div>

    <script>
        function updateStatus(message) {
            document.getElementById('status').textContent = message;
        }

        function isStandaloneMode() {
            if (window.navigator.standalone) return true;
            if (window.matchMedia('(display-mode: standalone)').matches) return true;
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('source') === 'pwa') return true;
            return false;
        }

        function getAuthCacheTTL() {
            // 30 days for PWA standalone mode, 5 minutes for browser
            return isStandaloneMode() ? 30 * 24 * 60 * 60 * 1000 : 5 * 60 * 1000;
        }

        function getRedirectUrl(authData) {
            if (!authData || !authData.authenticated) {
                return '/';
            }

            // Check for a default list preference
            const defaultListId = localStorage.getItem('plupro_default_list');
            if (defaultListId) {
                return '/lists/' + defaultListId;
            }

            return '/dashboard';
        }

        async function checkAuthAndRedirect() {
            try {
                const cachedAuth = localStorage.getItem('plupro_auth_state');
                const cacheTTL = getAuthCacheTTL();

                if (cachedAuth) {
                    const authData = JSON.parse(cachedAuth);
                    const cacheTime = authData.timestamp || 0;
                    const now = Date.now();

                    // If cache is within TTL, use it
                    if (now - cacheTime < cacheTTL) {
                        window.location.href = getRedirectUrl(authData);
                        return;
                    }
                }

                // If offline, use cached auth regardless of age (better than nothing)
                if (!navigator.onLine) {
                    if (cachedAuth) {
                        const authData = JSON.parse(cachedAuth);
                        document.getElementById('offline-message').style.display = 'block';
                        updateStatus('Opening offline...');
                        window.location.href = getRedirectUrl(authData);
                        return;
                    }

                    // No cached auth at all while offline — can't authenticate
                    updateStatus('Please connect to the internet to sign in.');
                    document.getElementById('spinner').style.display = 'none';
                    return;
                }

                // Online: check authentication status via dedicated endpoint
                const response = await fetch('/pwa/auth-check', {
                    method: 'GET',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    const authData = await response.json();

                    // Cache the auth state
                    localStorage.setItem('plupro_auth_state', JSON.stringify({
                        ...authData,
                        timestamp: Date.now()
                    }));

                    window.location.href = getRedirectUrl(authData);
                    return;
                }
            } catch (error) {
                // On error, try cached auth as fallback
                const cachedAuth = localStorage.getItem('plupro_auth_state');
                if (cachedAuth) {
                    const authData = JSON.parse(cachedAuth);
                    window.location.href = getRedirectUrl(authData);
                    return;
                }
            }

            // Last resort fallback
            window.location.href = '/';
        }

        // Start the check after a small delay to ensure everything is loaded
        setTimeout(checkAuthAndRedirect, 200);
    </script>
</body>

</html>
