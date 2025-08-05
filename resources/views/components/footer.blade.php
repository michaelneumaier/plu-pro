<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex justify-center md:order-2 space-x-6">
                <a href="{{ route('about') }}" class="text-gray-400 hover:text-gray-500">
                    About
                </a>
                <a href="{{ route('plu.directory') }}" class="text-gray-400 hover:text-gray-500">
                    PLU Directory
                </a>
                <a href="{{ route('marketplace.browse') }}" class="text-gray-400 hover:text-gray-500">
                    Marketplace
                </a>
                @auth
                <a href="{{ route('lists.index') }}" class="text-gray-400 hover:text-gray-500">
                    My Lists
                </a>
                @endauth
                <button onclick="Livewire.dispatch('openFeedbackModal', {url: window.location.href})" 
                        class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500">
                    Feedback
                </button>
            </div>
            <div class="mt-8 md:mt-0 md:order-1">
                <div class="flex justify-center md:justify-start items-center">
                    <img src="{{ asset('logo.png') }}" alt="PLUPro Logo" class="h-8 w-auto mr-3">
                    <p class="text-gray-400 text-sm">
                        &copy; {{ date('Y') }} PLUPro. Your trusted produce PLU companion.
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>