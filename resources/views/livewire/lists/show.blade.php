<div x-data="{ 
    deleteMode: false,
    carouselOpen: false,
    showAddSection: false,
    showClearModal: false,
    
    init() {
        // Initialize list manager store with server data
        this.$store.listManager.init(@js($allItemsData));
        
        // Listen for notifications
        window.addEventListener('notify', (e) => {
            const { message, type } = e.detail;
            // You can integrate with your notification system here
            console.log(`${type}: ${message}`);
        });
        
        // Listen for add-item events from Alpine store
        window.addEventListener('trigger-add-item', (e) => {
            const { pluCodeId, organic } = e.detail;
            const addButton = document.getElementById(organic ? 'add-organic-btn' : 'add-regular-btn');
            if (addButton) {
                // Set the PLU code ID and trigger the click
                addButton.setAttribute('data-plu-id', pluCodeId);
                addButton.click();
            }
        });
        
        // Handle Livewire events
        this.$wire.on('item-added-to-list', (pluCodeId) => {
            // Update the Alpine store to reflect that the item was added successfully
            // Don't add optimistically, just mark it as no longer temporary
            console.log('Item added to list:', pluCodeId);
        });
    },
    
    clearInventoryStorage() {
        // Clear all local storage for inventory items
        for (let i = localStorage.length - 1; i >= 0; i--) {
            const key = localStorage.key(i);
            if (key && key.startsWith('plu_inventory_')) {
                localStorage.removeItem(key);
            }
        }
        // Update Alpine store
        this.$store.listManager.clearAllInventory();
    }
}" @inventory-cleared.window="clearInventoryStorage()"
    @force-inventory-sync.window="
    // Force all inventory components to sync immediately
    document.querySelectorAll('[x-data*=inventoryItem]').forEach(el => {
        if (el.__x && el.__x.$data && el.__x.$data.pendingDelta !== 0) {
            if (el.__x.$data.syncTimeout) {
                clearTimeout(el.__x.$data.syncTimeout);
            }
            el.__x.$data.sync();
        }
    });
" @carousel-ready-to-open.window="carouselOpen = true; $dispatch('carousel-open')" class="min-h-screen bg-gray-50">
    <!-- Mobile-first header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $userList->name }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5"><span x-text="$store.listManager.items.length"></span> items</p>
                </div>
                <div class="flex items-center space-x-2 ml-4">
                    <button @click="showClearModal = true"
                        class="inline-flex items-center justify-center px-3 py-2 rounded-md bg-gray-600 text-white text-sm font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Clear
                    </button>
                    <button @click="showAddSection = !showAddSection"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                        :class="{ 'bg-blue-700': showAddSection }">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                :d="showAddSection ? 'M6 18L18 6M6 6l12 12' : 'M12 4v16m8-8H4'"></path>
                        </svg>
                    </button>
                    <button @click="deleteMode = !deleteMode; $dispatch('toggle-delete-buttons')"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors"
                        :class="{ 'bg-gray-100 text-gray-900': deleteMode }">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter section - collapsible on mobile -->
        <div class="border-t border-gray-200">
            <div class="flex flex-col md:flex-row mb-1 space-y-2 md:space-y-0 bg-black bg-opacity-10 rounded-md p-1">
                <div class="flex flex-row w-full space-x-1 md:space-x-2 flex-grow">
                    <!-- Category Filter -->
                    <div class="flex-1 md:p-1">
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select x-model="$store.listManager.selectedCategory" @change="$store.listManager.applyFilters()" id="category"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            <template x-for="category in $store.listManager.categories" :key="category">
                                <option :value="category" x-text="category.charAt(0).toUpperCase() + category.slice(1)"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Commodity Filter -->
                    <div class="flex-1 md:p-1">
                        <label for="commodity" class="block text-sm font-medium text-gray-700">Commodity</label>
                        <select x-model="$store.listManager.selectedCommodity" @change="$store.listManager.applyFilters()" id="commodity"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Commodities</option>
                            <template x-for="commodity in $store.listManager.commodities" :key="commodity">
                                <option :value="commodity" x-text="commodity.charAt(0).toUpperCase() + commodity.slice(1)"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Reset Filters Button -->
                    <div class="flex-shrink-0 md:p-1 flex items-end">
                        <button @click="$store.listManager.resetFilters()"
                            class="bg-gray-500 hover:bg-gray-700 text-white py-1 px-2 rounded">
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="pb-20"> <!-- Bottom padding for floating button -->
        <!-- PLU Items Table -->
        <div wire:key="list-items-table-{{ $userList->id }}" key="stable-list-container">
            <x-plu-code-table :collection="$listItems" :user-list-id="$userList->id" :refresh-token="$refreshToken" :dual-version-plu-codes="$dualVersionPluCodes" onDelete="removePLUCode" />
        </div>
    </div>

    <!-- Hidden buttons for triggering add functionality -->
    <div style="display: none;">
        <button id="add-regular-btn" 
                wire:click="addPLUCodeSilent($event.target.getAttribute('data-plu-id'), false)"
                data-plu-id="">
            Add Regular
        </button>
        <button id="add-organic-btn" 
                wire:click="addPLUCodeSilent($event.target.getAttribute('data-plu-id'), true)"
                data-plu-id="">
            Add Organic
        </button>
    </div>

    <!-- Floating scan button - centered at bottom -->
    <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
        <button wire:click="prepareAndOpenCarousel" wire:loading.attr="disabled"
            class="flex flex-col items-center justify-center px-4 py-3 bg-green-600 text-white rounded-xl shadow-lg hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200 active:scale-95 min-w-[80px] disabled:opacity-75 disabled:cursor-not-allowed">

            <!-- Loading spinner -->
            <div wire:loading wire:target="prepareAndOpenCarousel" class="w-6 h-6 mb-1">
                <svg class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>

            <!-- QR/Barcode scanner icon -->
            <svg wire:loading.remove wire:target="prepareAndOpenCarousel" class="w-6 h-6 mb-1" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <!-- Corner brackets -->
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7V5a2 2 0 012-2h2M3 17v2a2 2 0 002 2h2M21 17v2a2 2 0 01-2 2h-2M21 7V5a2 2 0 00-2-2h-2"></path>
                <!-- Simple barcode -->
                <rect x="9" y="9" width="1" height="6" fill="currentColor" />
                <rect x="11" y="9" width="2" height="6" fill="currentColor" />
                <rect x="14" y="9" width="1" height="6" fill="currentColor" />
            </svg>

            <!-- Text -->
            <span wire:loading.remove wire:target="prepareAndOpenCarousel" class="text-xs font-semibold">Scan</span>
            <span wire:loading wire:target="prepareAndOpenCarousel" class="text-xs font-semibold">Syncing...</span>
        </button>
    </div>

    <div wire:key="carousel-{{ $userList->id }}" x-show="carouselOpen" x-cloak
        @carousel-close.window="carouselOpen = false">
        @livewire('item-carousel', ['userListId' => $userList->id])
    </div>

    <!-- Add PLU Codes Section - Mobile Slide-up Panel -->
    <div x-show="showAddSection" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-full"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-full" class="fixed inset-0 z-50 bg-white"
        wire:key="search-section-{{ $userList->id }}">

        <!-- Header -->
        <div class="bg-white border-b border-gray-200 sticky top-0">
            <div class="px-4 py-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Add PLU Codes</h2>
                    <button @click="showAddSection = false"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Search input -->
                <div class="mt-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm"
                            placeholder="Search PLU codes, variety, commodity..."
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg text-base placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Results -->
        <div class="flex-1 overflow-auto pb-20">
            <div wire:key="search-results-{{ $userList->id }}-{{ md5($searchTerm) }}">
                <x-alpine-search-results :plu-codes="$pluCodes" :user-list-id="$userList->id" />
            </div>
        </div>
    </div>

    <!-- Clear Values Confirmation Modal -->
    <div x-show="showClearModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="showClearModal = false"></div>

        <!-- Modal -->
        <div class="flex items-center justify-center min-h-screen p-4 relative z-20">
            <div x-show="showClearModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="bg-white rounded-lg shadow-xl max-w-md w-full mx-auto" @click.stop>

                <!-- Modal Header -->
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-gray-900">Clear All Inventory Values</h3>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="text-sm text-gray-600">
                            Are you sure you want to reset all inventory levels to 0? This action cannot be undone.
                        </p>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div
                    class="bg-gray-50 px-6 py-3 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3 rounded-b-lg">
                    <button @click="showClearModal = false"
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="clearAllInventoryLevels" @click="showClearModal = false"
                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                        Clear All Values
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>