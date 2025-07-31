<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-green-50 to-blue-50">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-xl overflow-hidden sm:rounded-lg border-t-4 border-green-600">
        {{ $slot }}
    </div>
    
    <p class="mt-4 text-sm text-gray-600">
        Your trusted produce PLU code companion
    </p>
</div>
