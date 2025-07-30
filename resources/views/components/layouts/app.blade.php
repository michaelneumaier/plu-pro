<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name', 'Laravel') }}</title>
    
    @if(isset($metaDescription))
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    
    @if(isset($metaKeywords))
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    
    @if(isset($canonical))
        <link rel="canonical" href="{{ $canonical }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased">
    <x-banner />

    <div class="min-h-screen bg-gray-100">
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
        <div class="flex justify-center">
            <main class="p-1 md:p-6 md:w-5/6 w-full max-w-4xl">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewire('plu-code-detail-modal')
    @livewire('upc-code-detail-modal')
    @stack('modals')

    @livewireScripts
</body>

</html>