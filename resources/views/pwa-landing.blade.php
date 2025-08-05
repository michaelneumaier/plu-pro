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
    </style>
</head>

<body>
    <div class="loading">
        <div class="logo">
            <img src="{{ asset('logo.png') }}" alt="PLUPro Logo" style="height: 60px; width: auto;">
        </div>
        <div id="status">Loading your workspace...</div>
        <div class="spinner" id="spinner"></div>
    </div>

    <script>
        function updateStatus(message) {
            document.getElementById('status').textContent = message;
        }

        function updateDebug(message) {
            // Debug removed for production
        }

        async function checkAuthAndRedirect() {
            try {
                // First check localStorage for cached auth state
                const cachedAuth = localStorage.getItem('plupro_auth_state');
                if (cachedAuth) {
                    const authData = JSON.parse(cachedAuth);
                    const cacheTime = authData.timestamp || 0;
                    const now = Date.now();

                    // If cache is less than 5 minutes old, use it
                    if (now - cacheTime < 5 * 60 * 1000) {
                        if (authData.authenticated) {
                            window.location.href = '/dashboard';
                            return;
                        } else {
                            window.location.href = '/';
                            return;
                        }
                    }
                }

                // Check authentication status via dedicated endpoint
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

                    if (authData.authenticated) {
                        window.location.href = '/dashboard';
                        return;
                    } else {
                        window.location.href = '/';
                        return;
                    }
                }
            } catch (error) {
                // On error, redirect to home
            }

            // If we get here, something went wrong, redirect to home
            window.location.href = '/';
        }

        // Clear any stale auth cache on load
        window.addEventListener('focus', () => {
            localStorage.removeItem('plupro_auth_state');
        });

        // Start the check after a small delay to ensure everything is loaded
        setTimeout(checkAuthAndRedirect, 200);
    </script>
</body>

</html>