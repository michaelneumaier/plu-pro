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
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PLUPro">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="/icon-192.png">
    <link rel="apple-touch-icon" href="/icon-192.png">

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
        console.log('Marking page as dirty, adding beforeunload listener');
        unsaved = true;
        window.addEventListener('beforeunload', warn, { capture: true });
      };

      window.markSynced = () => {        // call when *all* changes are flushed
        if (!unsaved) return;
        console.log('Marking page as synced, removing beforeunload listener');
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