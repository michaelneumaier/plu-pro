<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Laravel') }} - PLUPro</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PLUPro">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/icon-192.png">
    <link rel="apple-touch-icon" sizes="57x57" href="/icon-192.png">

    <!-- iOS Splash Screens -->
    <!-- iPhone X, XS, 11 Pro -->
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1125x2436.svg">
    <!-- iPhone XR, 11 -->
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 2)" href="/splash-828x1792.svg">
    <!-- iPhone XS Max, 11 Pro Max -->
    <link rel="apple-touch-startup-image" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1242x2688.svg">
    <!-- iPhone 12 Mini -->
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1080x2340.svg">
    <!-- iPhone 12, 12 Pro -->
    <link rel="apple-touch-startup-image" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1170x2532.svg">
    <!-- iPhone 12 Pro Max -->
    <link rel="apple-touch-startup-image" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1284x2778.svg">
    <!-- iPhone 13 Mini -->
    <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1080x2340.svg">
    <!-- iPhone 13, 13 Pro -->
    <link rel="apple-touch-startup-image" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1170x2532.svg">
    <!-- iPhone 13 Pro Max -->
    <link rel="apple-touch-startup-image" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1284x2778.svg">
    <!-- iPhone 14 -->
    <link rel="apple-touch-startup-image" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1170x2532.svg">
    <!-- iPhone 14 Plus -->
    <link rel="apple-touch-startup-image" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1284x2778.svg">
    <!-- iPhone 14 Pro -->
    <link rel="apple-touch-startup-image" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1179x2556.svg">
    <!-- iPhone 14 Pro Max -->
    <link rel="apple-touch-startup-image" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1290x2796.svg">
    <!-- iPhone 15, 15 Pro -->
    <link rel="apple-touch-startup-image" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1179x2556.svg">
    <!-- iPhone 15 Plus, 15 Pro Max -->
    <link rel="apple-touch-startup-image" media="(device-width: 430px) and (device-height: 932px) and (-webkit-device-pixel-ratio: 3)" href="/splash-1290x2796.svg">
    <!-- iPad -->
    <link rel="apple-touch-startup-image" media="(device-width: 768px) and (device-height: 1024px) and (-webkit-device-pixel-ratio: 2)" href="/splash-1536x2048.svg">
    <!-- iPad Pro 11" -->
    <link rel="apple-touch-startup-image" media="(device-width: 834px) and (device-height: 1194px) and (-webkit-device-pixel-ratio: 2)" href="/splash-1668x2388.svg">
    <!-- iPad Pro 12.9" -->
    <link rel="apple-touch-startup-image" media="(device-width: 1024px) and (device-height: 1366px) and (-webkit-device-pixel-ratio: 2)" href="/splash-2048x2732.svg">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
    
    <!-- Additional PWA styles -->
    <style>
        /* Prevent double-tap zoom on mobile */
        .touch-manipulation {
            touch-action: manipulation;
        }
        
        /* Hide scrollbar for PWA feel */
        @media (max-width: 768px) {
            ::-webkit-scrollbar {
                width: 0px;
                background: transparent;
            }
        }
        
        /* Prevent text selection on buttons */
        button {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* PWA Standalone mode styles */
        .pwa-standalone {
            /* Enhance PWA experience when running standalone */
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        .pwa-standalone body {
            /* Prevent overscroll bounce on iOS */
            overscroll-behavior: none;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Status bar safe area support for iOS */
        @supports (padding-top: env(safe-area-inset-top)) {
            .pwa-standalone header,
            .pwa-standalone .fixed.top-0 {
                padding-top: env(safe-area-inset-top);
            }
            
            .pwa-standalone footer,
            .pwa-standalone .fixed.bottom-0 {
                padding-bottom: env(safe-area-inset-bottom);
            }
        }
        
        /* Alpine cloak */
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="font-sans antialiased">
    <x-banner />
    
    <!-- Network Status Indicator -->
    <div x-data="networkStatus" 
         x-show="showOfflineMessage" 
         x-transition
         class="fixed top-0 left-0 right-0 bg-orange-500 text-white text-center py-2 z-50">
        <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728"></path>
        </svg>
        You're offline - Changes will sync when reconnected
    </div>
    
    <!-- Floating Feedback Button -->
    <button onclick="Livewire.dispatch('openFeedbackModal', {url: window.location.href})" 
            class="fixed bottom-4 left-4 bg-blue-500 text-white w-12 h-12 rounded-full shadow-lg z-40 
                   hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 
                   focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2
                   transition-all duration-200 flex items-center justify-center group
                   hover:scale-105 active:scale-95 touch-manipulation
                   sm:bottom-6 sm:left-6 sm:w-14 sm:h-14
                   safe-area-inset-bottom safe-area-inset-left"
            title="Send Feedback"
            aria-label="Send Feedback"
            style="padding-bottom: env(safe-area-inset-bottom, 1rem); padding-left: env(safe-area-inset-left, 1rem);">
        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <!-- Tooltip - only show on hover on larger screens -->
        <div class="absolute left-full ml-3 px-2 py-1 bg-gray-800 text-white text-xs rounded 
                    opacity-0 group-hover:opacity-100 transition-opacity duration-200 
                    pointer-events-none whitespace-nowrap hidden sm:block">
            Send Feedback
        </div>
    </button>

    <!-- PWA Install Button -->
    <button id="pwa-install-button" 
            style="display: none;"
            class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 
                   hover:bg-green-600 transition-colors">
        <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Install App
    </button>

    <div class="min-h-screen bg-gray-100 flex flex-col">
        @livewire('navigation-menu')

        <!-- Page Heading -->
        @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>
        
        <x-footer />
    </div>

    @stack('modals')

    @livewireScripts
    
    <!-- Navigation protection helper (after Alpine/Livewire are loaded) -->
    <script>
    (() => {
      let unsaved = false;               // single source of truth

      function warn(e) {
        if (!unsaved) return;            // nothing dirty ➜ no dialog
        console.log('Preventing navigation - unsaved changes detected');
        e.preventDefault();              // **required** in Chrome ≥ 119
        e.returnValue = '';              // Safari / Firefox still look at this
      }

      window.markDirty  = () => {        // call when the page first becomes "dirty"
        if (unsaved) return;
        unsaved = true;
        window.addEventListener('beforeunload', warn, { capture: true });
      };

      window.markSynced = () => {        // call when *all* changes are flushed
        if (!unsaved) return;
        unsaved = false;
        window.removeEventListener('beforeunload', warn, { capture: true });
      };
      
      // Test function
      window.testUnsaved = () => {
        console.log('Page is dirty:', unsaved);
        return unsaved;
      };
      
      console.log('Navigation protection ready');
    })();
    </script>
    
    <!-- Modal Components -->
    @livewire('plu-code-detail-modal')
    @livewire('upc-code-detail-modal')
    @livewire('feedback-modal')
    
    <!-- PWA Auth State Management -->
    <script>
        // Update PWA auth cache when user is authenticated
        @auth
        localStorage.setItem('plupro_auth_state', JSON.stringify({
            authenticated: true,
            verified: {{ auth()->user()->hasVerifiedEmail() ? 'true' : 'false' }},
            user_id: {{ auth()->id() }},
            timestamp: Date.now()
        }));
        @else
        localStorage.removeItem('plupro_auth_state');
        @endauth
    </script>
    
    <script>
    document.addEventListener('livewire:initialized', () => {
        // Listen for PLU code selection events
        document.addEventListener('pluCodeSelected', function(event) {
            const pluModal = document.querySelector('[wire\\:snapshot*="PluCodeDetailModal"]');
            if (pluModal && pluModal.__livewire) {
                pluModal.__livewire.call('openModal', ...event.detail);
            }
        });
        
        // Listen for UPC code selection events  
        document.addEventListener('upcCodeSelected', function(event) {
            const upcModal = document.querySelector('[wire\\:snapshot*="UpcCodeDetailModal"]');
            if (upcModal && upcModal.__livewire) {
                upcModal.__livewire.call('openModal', ...event.detail);
            }
        });
    });
    </script>
</body>

</html>