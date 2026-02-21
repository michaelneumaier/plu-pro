<div x-data="{
    carouselOpen: false,
    carouselIndex: 0,
    carouselStartX: 0,
    carouselCurrentX: 0,
    carouselDragging: false,
    carouselHasMoved: false,

    get carouselItems() {
        return this.$store.listManager.getItemsWithInventory();
    },

    init() {
        this.$store.listManager.init(@js($allItemsData), {{ $userList->id }});
    },

    openCarousel() {
        this.carouselIndex = 0;
        this.carouselOpen = true;
    },

    onCarouselTouchStart(e) {
        this.carouselStartX = e.touches[0].clientX;
        this.carouselCurrentX = this.carouselStartX;
        this.carouselDragging = true;
        this.carouselHasMoved = false;
    },
    onCarouselTouchMove(e) {
        if (!this.carouselDragging) return;
        e.preventDefault();
        this.carouselCurrentX = e.touches[0].clientX;
        const deltaX = this.carouselCurrentX - this.carouselStartX;
        const deltaY = e.touches[0].clientY - (this._carouselStartY || e.touches[0].clientY);
        if (!this.carouselHasMoved && Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 10) {
            this.carouselHasMoved = true;
        }
    },
    onCarouselTouchEnd() {
        if (!this.carouselDragging) return;
        this.carouselDragging = false;
        if (!this.carouselHasMoved) return;
        const deltaX = this.carouselCurrentX - this.carouselStartX;
        if (Math.abs(deltaX) > 75) {
            if (deltaX > 0 && this.carouselIndex > 0) {
                this.carouselIndex--;
                if (navigator.vibrate) navigator.vibrate(10);
            } else if (deltaX < 0 && this.carouselIndex < this.carouselItems.length - 1) {
                this.carouselIndex++;
                if (navigator.vibrate) navigator.vibrate(10);
            }
        }
    }
}" class="min-h-screen bg-gray-50">
    <!-- Read-only header with distinct styling -->
    <div class="bg-blue-50 border-b border-blue-200 sticky top-0 z-40">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg font-semibold text-blue-900 truncate">{{ $userList->name }}</h1>
                            <p class="text-sm text-blue-600 mt-0.5">📋 Shared List • {{ $listItems->count() }} items</p>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex items-center space-x-2 ml-4">
                    <!-- Copy button -->
                    @auth
                        <button wire:click="toggleCopyModal"
                            class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition-all duration-150 shadow-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            Copy List
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-all duration-150 shadow-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            Login to Copy
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="pb-6">
        <!-- Notice for shared view -->
        <div class="bg-blue-100 border-l-4 border-blue-500 p-4 mx-4 mt-4 rounded-r">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        You are viewing a shared list. This is a read-only view - you cannot make changes to this list.
                    </p>
                </div>
            </div>
        </div>

        <!-- List Items Table (Read-Only with Inventory) -->
        <div class="mx-4 mt-4">
            <x-plu-code-table
                :collection="$listItems"
                :readOnly="true"
                :showInventory="true"
                :dual-version-plu-codes="$dualVersionPluCodes"
                :user-list-id="$userList->id"
            />
        </div>
    </div>

    <!-- Floating Scan List Button -->
    <div class="fixed bottom-6 right-6 z-40" x-show="carouselItems.length > 0" x-cloak>
        <button @click="openCarousel()"
            class="flex items-center space-x-2 px-4 py-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 active:scale-95 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="2" y="4" width="20" height="16" rx="2" stroke-width="2" />
                <path d="M7 9v6M9 9v6M12 9v6M15 9v6M17 9v6" stroke-width="1.5" />
                <rect x="9" y="9" width="1" height="6" fill="currentColor" />
                <rect x="11" y="9" width="2" height="6" fill="currentColor" />
                <rect x="14" y="9" width="1" height="6" fill="currentColor" />
            </svg>
            <span class="text-xs font-semibold">Scan List</span>
        </button>
    </div>

    <!-- Read-only Carousel -->
    <div x-show="carouselOpen" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-95 z-50 flex flex-col"
        @keydown.left.window="carouselOpen && carouselIndex > 0 && (carouselIndex--, navigator.vibrate && navigator.vibrate(10))"
        @keydown.right.window="carouselOpen && carouselIndex < carouselItems.length - 1 && (carouselIndex++, navigator.vibrate && navigator.vibrate(10))"
        @keydown.escape.window="carouselOpen && (carouselOpen = false)"
        @keydown.space.window="carouselOpen && ($event.preventDefault(), carouselIndex < carouselItems.length - 1 && (carouselIndex++, navigator.vibrate && navigator.vibrate(10)))">

        <!-- Header Bar -->
        <div class="flex items-center justify-between p-4 bg-black bg-opacity-50 backdrop-blur-sm">
            <div class="flex items-center space-x-4">
                <button @click="carouselOpen = false"
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-white bg-opacity-20 text-white hover:bg-opacity-30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    aria-label="Close Scanner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
                <h1 class="text-white text-lg font-semibold">Scanner</h1>
            </div>
            <div class="text-white text-sm font-medium" x-show="carouselItems.length > 0">
                <span x-text="carouselIndex + 1"></span> of <span x-text="carouselItems.length"></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden"
            @touchstart="onCarouselTouchStart($event)"
            @touchmove="onCarouselTouchMove($event)"
            @touchend="onCarouselTouchEnd()"
            style="height: calc(100vh - 160px);">

            <template x-if="carouselItems.length > 0">
                <div class="flex-1 relative overflow-hidden">
                    <!-- Carousel Track -->
                    <div class="flex h-full items-center transition-transform duration-300 ease-out"
                        :style="`width: calc(${carouselItems.length} * 100vw); transform: translateX(-${carouselIndex * 100}vw);`">

                        <template x-for="(item, idx) in carouselItems" :key="item.id">
                            <div class="flex-shrink-0 flex items-center justify-center px-4 py-2"
                                style="width: 100vw; height: 100%;">
                                <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden max-h-full flex flex-col"
                                    @click.stop>

                                    <!-- Product Image Section -->
                                    <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <template x-if="item.has_image && item.image_url">
                                                <img :src="item.image_url" :alt="item.item_type === 'plu' ? item.variety : item.name"
                                                     class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.has_image || !item.image_url">
                                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                                    <span class="text-gray-400 text-lg">No Image</span>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Organic Badge -->
                                        <template x-if="item.organic">
                                            <div class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                                                Organic
                                            </div>
                                        </template>

                                        <!-- Inventory Count Overlay -->
                                        <div class="absolute top-4 right-4 bg-black bg-opacity-80 text-white px-4 py-2 rounded-full backdrop-blur-sm">
                                            <span class="text-2xl font-bold" x-text="$store.listManager.formatInventory($store.listManager.getInventory(item.id))"></span>
                                            <span class="text-sm ml-1">in stock</span>
                                        </div>
                                    </div>

                                    <!-- Product Information -->
                                    <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                                        <!-- Item Type Indicator -->
                                        <div class="text-center">
                                            <template x-if="item.item_type === 'plu'">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    PLU Code
                                                    <template x-if="item.organic"><span>&nbsp;• Organic</span></template>
                                                </span>
                                            </template>
                                            <template x-if="item.item_type === 'upc'">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                    UPC Code
                                                </span>
                                            </template>
                                        </div>

                                        <!-- Product Name -->
                                        <div class="text-center">
                                            <h2 class="text-2xl font-bold text-gray-900 leading-tight truncate"
                                                x-text="item.item_type === 'plu' ? item.variety : item.name"></h2>
                                            <p class="text-lg text-gray-600 mt-1 truncate" x-text="item.commodity"></p>
                                            <template x-if="item.item_type === 'plu' && item.size">
                                                <p class="text-sm text-gray-500 mt-1 truncate" x-text="'Size: ' + item.size"></p>
                                            </template>
                                            <template x-if="item.item_type === 'upc' && item.brand">
                                                <p class="text-sm text-gray-500 mt-1 truncate" x-text="'Brand: ' + item.brand"></p>
                                            </template>
                                        </div>

                                        <!-- Code Display -->
                                        <div class="bg-gray-50 rounded-lg p-3 text-center">
                                            <p class="text-sm text-gray-600 mb-1" x-text="item.item_type === 'plu' ? 'PLU Code' : 'UPC Code'"></p>
                                            <p class="text-2xl font-mono font-bold text-gray-900" x-text="item.display_code"></p>
                                        </div>

                                        <!-- Barcode Section -->
                                        <div class="bg-white border border-gray-200 rounded-lg p-2">
                                            <p class="text-xs text-gray-600 text-center mb-1">Barcode</p>
                                            <div class="flex justify-center items-center" x-html="window.renderBarcode(item.display_code)"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="carouselItems.length === 0">
                <!-- Empty State -->
                <div class="flex-1 flex items-center justify-center">
                    <div class="text-center text-white">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                            </path>
                        </svg>
                        <h2 class="text-xl font-semibold mb-2">No Items to Scan</h2>
                        <p class="text-gray-300">This shared list has no items with inventory.</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Navigation Controls -->
        <div class="flex items-center justify-between p-4 bg-black bg-opacity-50 backdrop-blur-sm border-t border-white border-opacity-10">
            <button @click="carouselIndex > 0 && (carouselIndex--, navigator.vibrate && navigator.vibrate(10))"
                class="flex items-center justify-center w-14 h-14 rounded-full bg-white bg-opacity-20 text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 hover:bg-opacity-30 active:scale-95"
                aria-label="Previous Item">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <div class="text-white text-sm font-medium">
                <template x-if="carouselItems.length > 0">
                    <span><span x-text="carouselIndex + 1"></span> of <span x-text="carouselItems.length"></span></span>
                </template>
                <template x-if="carouselItems.length === 0">
                    <span>No items</span>
                </template>
            </div>

            <button @click="carouselIndex < carouselItems.length - 1 && (carouselIndex++, navigator.vibrate && navigator.vibrate(10))"
                class="flex items-center justify-center w-14 h-14 rounded-full bg-white bg-opacity-20 text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 hover:bg-opacity-30 active:scale-95"
                aria-label="Next Item">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Copy List Modal -->
    <div x-show="$wire.showCopyModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showCopyModal', (value) => {
            if (value) {
                setTimeout(() => $refs.listNameInput?.focus(), 100);
            }
        })">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.toggleCopyModal()"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showCopyModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Copy Shared List</h3>
                        <button @click="$wire.toggleCopyModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form wire:submit.prevent="copyList">
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-4">
                                    You're about to copy "{{ $userList->name }}" with all of its items to your lists.
                                    <strong>All inventory levels will be preserved</strong>, including items not currently displayed.
                                </p>
                                <label for="customListName" class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                                <input type="text"
                                    wire:model="customListName"
                                    id="customListName"
                                    placeholder="Enter name for your copy..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    x-ref="listNameInput">
                                @error('customListName')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="bg-blue-50 p-3 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-800">
                                            <strong>Note:</strong> You'll get the complete list including items with zero quantities. Only items with quantities are shown here for clarity!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="$wire.toggleCopyModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Copy List
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
