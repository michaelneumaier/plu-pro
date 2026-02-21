<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Popular Categories -->
        <div class="mb-8 border-b border-gray-100 pb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Popular Categories</h3>
            <div class="flex flex-wrap gap-2">
                <a href="/commodity/apples" class="text-xs text-gray-400 hover:text-gray-600">Apples</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/bananas" class="text-xs text-gray-400 hover:text-gray-600">Bananas</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/berries" class="text-xs text-gray-400 hover:text-gray-600">Berries</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/citrus" class="text-xs text-gray-400 hover:text-gray-600">Citrus</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/grapes" class="text-xs text-gray-400 hover:text-gray-600">Grapes</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/lettuce" class="text-xs text-gray-400 hover:text-gray-600">Lettuce</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/melons" class="text-xs text-gray-400 hover:text-gray-600">Melons</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/onions" class="text-xs text-gray-400 hover:text-gray-600">Onions</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/peppers" class="text-xs text-gray-400 hover:text-gray-600">Peppers</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/potatoes" class="text-xs text-gray-400 hover:text-gray-600">Potatoes</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/stone-fruit" class="text-xs text-gray-400 hover:text-gray-600">Stone Fruit</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/tomatoes" class="text-xs text-gray-400 hover:text-gray-600">Tomatoes</a>
                <span class="text-gray-200">&middot;</span>
                <a href="/commodity/tropical-fruit" class="text-xs text-gray-400 hover:text-gray-600">Tropical Fruit</a>
                <span class="text-gray-200">&middot;</span>
                <a href="{{ route('plu.directory') }}" class="text-xs text-blue-500 hover:text-blue-600 font-medium">View All &rarr;</a>
            </div>
        </div>

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