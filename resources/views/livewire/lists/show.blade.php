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
        
        // Handle Livewire events - removed duplicate listener
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
                <div class="flex-1 min-w-0" x-data="{ 
                    editingName: false, 
                    listName: '{{ $userList->name }}',
                    originalName: '{{ $userList->name }}'
                }">
                    <!-- Edit Mode: Editable List Name -->
                    <div x-show="deleteMode && !editingName" class="flex items-center space-x-2">
                        <button @click="
                            editingName = true;
                            $nextTick(() => $refs.nameInput.focus());
                        " 
                            class="flex-shrink-0 p-1 text-orange-500 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $userList->name }}</h1>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $listItems->count() }} items</p>
                        </div>
                    </div>
                    
                    <!-- Editing Mode: Input Field -->
                    <div x-show="editingName" class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center space-x-1">
                            <input x-model="listName"
                                @keydown.enter="
                                    $wire.call('updateListName', listName);
                                    editingName = false;
                                "
                                @keydown.escape="
                                    listName = originalName;
                                    editingName = false;
                                "
                                @blur="
                                    if (listName.trim() !== originalName) {
                                        $wire.call('updateListName', listName);
                                    }
                                    editingName = false;
                                "
                                x-ref="nameInput"
                                class="text-base font-semibold text-gray-900 bg-white border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent flex-1 min-w-0"
                                maxlength="50">
                            <button @click="
                                $wire.call('updateListName', listName);
                                editingName = false;
                            " class="text-green-600 hover:text-green-700 p-0.5 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <button @click="
                                listName = originalName;
                                editingName = false;
                            " class="text-red-600 hover:text-red-700 p-0.5 flex-shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $listItems->count() }} items</p>
                    </div>
                    
                    <!-- Normal Mode: Regular Display -->
                    <div x-show="!deleteMode" class="flex-1 min-w-0">
                        <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $userList->name }}</h1>
                        <p class="text-sm text-gray-500 mt-0.5">{{ $listItems->count() }} items</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 ml-2 flex-shrink-0">
                    <!-- Share Button -->
                    <button @click="$wire.toggleShareModal()"
                        :disabled="deleteMode"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-full transition-all duration-150 shadow-sm"
                        :class="deleteMode ? 
                            'bg-gray-50 text-gray-400 cursor-not-allowed' : 
                            'bg-green-500 text-white hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </button>

                    <!-- Clear Button -->
                    <button @click="showClearModal = true"
                        :disabled="deleteMode"
                        class="inline-flex items-center justify-center h-9 px-4 rounded-full text-sm font-medium transition-all duration-150 shadow-sm"
                        :class="deleteMode ? 
                            'bg-gray-50 text-gray-400 cursor-not-allowed' : 
                            'bg-gray-100 text-gray-700 hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1'">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Clear
                    </button>
                    
                    <!-- Add Button -->
                    <button @click="showAddSection = !showAddSection"
                        :disabled="deleteMode"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-full transition-all duration-150 shadow-sm"
                        :class="deleteMode ? 
                            'bg-gray-50 text-gray-400 cursor-not-allowed' : 
                            showAddSection ? 
                                'bg-blue-600 text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1' : 
                                'bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1'">
                        <svg class="w-5 h-5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            :class="{ 'rotate-45': showAddSection && !deleteMode }">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                    
                    <!-- Edit Mode Button -->
                    <button @click="
                        deleteMode && $wire.call('refreshListAfterEdit');
                        deleteMode = !deleteMode; 
                        $dispatch('toggle-delete-buttons');
                    "
                        class="inline-flex items-center justify-center w-9 h-9 rounded-full transition-all duration-150 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1"
                        :class="deleteMode ? 
                            'bg-orange-500 text-white hover:bg-orange-600 active:bg-orange-700 focus:ring-orange-400 shadow-inner' : 
                            'bg-gray-50 text-gray-600 hover:bg-gray-100 active:bg-gray-200 focus:ring-gray-400'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <select wire:model.live="selectedCategory" id="category"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}">{{ ucwords(strtolower($category)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Commodity Filter -->
                    <div class="flex-1 md:p-1">
                        <label for="commodity" class="block text-sm font-medium text-gray-700">Commodity</label>
                        <select wire:model.live="selectedCommodity" id="commodity"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Commodities</option>
                            @foreach($commodities as $commodity)
                                <option value="{{ $commodity }}">{{ ucwords(strtolower($commodity)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reset Filters Button -->
                    <div class="flex-shrink-0 md:p-1 flex items-end">
                        <button wire:click="resetFilters"
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

    <!-- Share Modal -->
    <div x-show="$wire.showShareModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.toggleShareModal()"></div>
        
        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showShareModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Share List</h3>
                        <button @click="$wire.toggleShareModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <!-- Public sharing toggle -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-medium text-gray-700">Public Sharing</label>
                                <p class="text-xs text-gray-500 mt-1">Allow others to view this list with a link</p>
                            </div>
                            <button wire:click="togglePublicSharing" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                :class="$wire.isPublic ? 'bg-green-600' : 'bg-gray-200'">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="$wire.isPublic ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                        
                        <!-- Share URL (only shown when public) -->
                        <div x-show="$wire.isPublic" x-transition 
                             x-data="{ 
                                generateQR() {
                                    if ($wire.shareUrl && window.QRCode) {
                                        window.QRCode.toCanvas($refs.qrCanvas, $wire.shareUrl, { width: 150 }, (error) => {
                                            if (error) console.error(error);
                                        });
                                    }
                                }
                             }"
                             x-init="$watch('$wire.shareUrl', () => { $nextTick(() => generateQR()) })">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Share URL</label>
                            <div class="flex mb-4">
                                <input type="text" :value="$wire.shareUrl" readonly
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md bg-gray-50 text-sm"
                                    x-ref="shareUrl">
                                <button @click="
                                    $refs.shareUrl.select();
                                    document.execCommand('copy');
                                    $dispatch('notify', { message: 'Link copied to clipboard!', type: 'success' });
                                " 
                                    class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-r-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Copy
                                </button>
                            </div>
                            
                            <!-- QR Code -->
                            <div class="flex justify-center">
                                <div class="text-center">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                                    <div class="inline-block p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                                        <canvas x-ref="qrCanvas" class="max-w-full"></canvas>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Scan to open list</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 rounded-b-lg">
                    <button @click="$wire.toggleShareModal()"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>