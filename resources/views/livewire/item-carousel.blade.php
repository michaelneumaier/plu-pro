<div x-data="{
        startX: 0,
        startY: 0,
        currentX: 0,
        isDragging: false,
        hasMoved: false,
        
        init() {
            this.preloadImages();
            this.$watch('$wire.currentIndex', () => {
                this.preloadImages();
            });
        },
        
        preloadImages() {
            const currentIndex = $wire.currentIndex || 0;
            const items = $wire.items || [];
            
            // Preload current and adjacent images
            [currentIndex - 1, currentIndex, currentIndex + 1].forEach(index => {
                if (index >= 0 && index < items.length && items[index]?.plu_code?.plu) {
                    const img = new Image();
                    img.src = `/storage/product_images/${items[index].plu_code.plu}.jpg`;
                }
            });
        },
        
        onTouchStart(e) {
            this.startX = e.touches[0].clientX;
            this.startY = e.touches[0].clientY;
            this.currentX = this.startX;
            this.isDragging = true;
            this.hasMoved = false;
        },
        
        onTouchMove(e) {
            if (!this.isDragging) return;
            
            e.preventDefault();
            this.currentX = e.touches[0].clientX;
            
            const deltaX = this.currentX - this.startX;
            const deltaY = e.touches[0].clientY - this.startY;
            
            // Only start moving if horizontal movement is greater than vertical
            if (!this.hasMoved && Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 10) {
                this.hasMoved = true;
            }
        },
        
        onTouchEnd(e) {
            if (!this.isDragging) return;
            
            this.isDragging = false;
            
            if (!this.hasMoved) return;
            
            const deltaX = this.currentX - this.startX;
            const threshold = 75; // Minimum swipe distance
            
            if (Math.abs(deltaX) > threshold) {
                if (deltaX > 0) {
                    // Swipe right - go to previous
                    this.navigate('previous');
                } else {
                    // Swipe left - go to next
                    this.navigate('next');
                }
            }
        },
        
        navigate(direction) {
            if (navigator.vibrate) navigator.vibrate(10);
            
            if (direction === 'previous') {
                $wire.previous();
            } else if (direction === 'next') {
                $wire.next();
            }
        },
        
        // Button navigation
        goNext() {
            console.log('goNext called');
            if (navigator.vibrate) navigator.vibrate(10);
            $wire.next();
        },
        
        goPrevious() {
            console.log('goPrevious called');
            if (navigator.vibrate) navigator.vibrate(10);
            $wire.previous();
        }
    }" 
    x-show="$wire.isOpen" 
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black bg-opacity-95 z-50 flex flex-col"
    @carousel-open.window="$wire.openCarousel()" 
    @carousel-close.window="$wire.close()"
    @keydown.left.window="$wire.isOpen && goPrevious()"
    @keydown.right.window="$wire.isOpen && goNext()"
    @keydown.escape.window="$wire.isOpen && $wire.close()"
    @keydown.space.window="$wire.isOpen && (($event.preventDefault()), goNext())"
    tabindex="0">

    <!-- Header Bar -->
    <div class="flex items-center justify-between p-4 bg-black bg-opacity-50 backdrop-blur-sm">
        <div class="flex items-center space-x-4">
            <button @click="$wire.close()" 
                    class="flex items-center justify-center w-10 h-10 rounded-full bg-white bg-opacity-20 text-white hover:bg-opacity-30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                    aria-label="Close Scanner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <h1 class="text-white text-lg font-semibold">Scanner</h1>
        </div>
        
        @if($this->items->isNotEmpty())
        <div class="text-white text-sm font-medium">
            {{ $currentIndex + 1 }} of {{ $this->items->count() }}
        </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden" 
         @touchstart="onTouchStart($event)"
         @touchmove="onTouchMove($event)" 
         @touchend="onTouchEnd($event)"
         @click="$event.target === $event.currentTarget && $wire.close()"
         style="height: calc(100vh - 160px);"> <!-- Reserve space for header (80px) and navigation (80px) -->
        
        @if($isLoading)
        <!-- Loading State -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center text-white">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                <p class="text-lg">Loading scanner...</p>
            </div>
        </div>
        @elseif($this->items->count() > 0)
        <!-- Horizontal Carousel Container -->
        <div class="flex-1 relative overflow-hidden">
            
            <!-- Carousel Track with All Cards for Animation -->
            <div class="flex h-full items-center transition-transform duration-300 ease-out"
                 style="width: calc({{ $this->items->count() }} * 100vw); transform: translateX(-{{ $currentIndex * 100 }}vw);"
                 wire:key="carousel-track-{{ $this->items->count() }}-{{ md5(json_encode($this->items->pluck('id'))) }}">
                
                @foreach($this->items as $index => $item)
                <!-- Card {{ $index }}: PLU {{ optional($item->pluCode)->plu ?? 'N/A' }} -->
                <div class="flex-shrink-0 flex items-center justify-center px-4 py-2"
                     style="width: 100vw; height: 100%;"
                     wire:key="carousel-card-{{ $item->id }}-{{ $index }}">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden max-h-full flex flex-col"
                         @click.stop>
                        
                        <!-- Product Image Section -->
                        <div class="relative h-48 bg-gradient-to-br from-gray-100 to-gray-200">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-plu-image :plu="optional($item->pluCode)->plu" 
                                             size="lg" 
                                             class="w-full h-full object-cover" />
                            </div>
                            
                            <!-- Organic Badge -->
                            @if($item->organic)
                            <div class="absolute top-4 left-4 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                                Organic
                            </div>
                            @endif
                            
                            <!-- Inventory Count Overlay -->
                            <div class="absolute top-4 right-4 bg-black bg-opacity-80 text-white px-4 py-2 rounded-full backdrop-blur-sm">
                                <span class="text-2xl font-bold">{{ $item->inventory_level }}</span>
                                <span class="text-sm ml-1">in stock</span>
                            </div>
                            
                        </div>

                        <!-- Product Information -->
                        <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                            <!-- Debug Info -->
                            <div class="text-xs text-gray-400 text-center">
                                Card {{ $index }} of {{ $this->items->count() }} | Current: {{ $currentIndex }}
                            </div>
                            
                            <!-- Product Name -->
                            <div class="text-center">
                                <h2 class="text-2xl font-bold text-gray-900 leading-tight truncate">
                                    {{ optional($item->pluCode)->variety ?? 'Unknown Variety' }}
                                </h2>
                                <p class="text-lg text-gray-600 mt-1 truncate">
                                    {{ optional($item->pluCode)->commodity ?? 'Unknown Commodity' }}
                                </p>
                                @if(optional($item->pluCode)->size)
                                <p class="text-sm text-gray-500 mt-1 truncate">
                                    Size: {{ $item->pluCode->size }}
                                </p>
                                @endif
                            </div>

                            <!-- PLU Code Display -->
                            <div class="bg-gray-50 rounded-lg p-3 text-center">
                                <p class="text-sm text-gray-600 mb-1">PLU Code</p>
                                <p class="text-2xl font-mono font-bold text-gray-900">
                                    @if($item->organic)
                                        9{{ optional($item->pluCode)->plu ?? 'N/A' }}
                                    @else
                                        {{ optional($item->pluCode)->plu ?? 'N/A' }}
                                    @endif
                                </p>
                            </div>

                            <!-- Barcode Section -->
                            <div class="bg-white border border-gray-200 rounded-lg p-2">
                                <p class="text-xs text-gray-600 text-center mb-1">Barcode</p>
                                <div class="flex justify-center items-center">
                                    <x-barcode code="{{ optional($item->pluCode)->plu }}" size="sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @else
        <!-- Empty State -->
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center text-white">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h2 class="text-xl font-semibold mb-2">No Items to Scan</h2>
                <p class="text-gray-300">Add items with inventory to your list to start scanning.</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Navigation Controls - Always Visible -->
    <div class="flex items-center justify-between p-4 bg-black bg-opacity-50 backdrop-blur-sm border-t border-white border-opacity-10">
        <!-- Previous Button -->
        <button @click="goPrevious()" 
                class="flex items-center justify-center w-14 h-14 rounded-full bg-white bg-opacity-20 text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 hover:bg-opacity-30 active:scale-95"
                aria-label="Previous Item">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <!-- Item Counter -->
        <div class="text-white text-sm font-medium">
            @if($this->items->count() > 0)
            {{ $currentIndex + 1 }} of {{ $this->items->count() }}
            @else
            No items
            @endif
        </div>

        <!-- Next Button -->
        <button @click="goNext()" 
                class="flex items-center justify-center w-14 h-14 rounded-full bg-white bg-opacity-20 text-white transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 hover:bg-opacity-30 active:scale-95"
                aria-label="Next Item">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>

    <!-- Swipe Instruction (shown briefly) -->
    <div x-show="!isDragging && $wire.isOpen && ($wire.items || []).length > 1" 
         x-transition:enter="transition ease-out duration-500 delay-1000"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute bottom-24 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-50 text-white px-4 py-2 rounded-full text-sm backdrop-blur-sm">
        Swipe left or right to navigate
    </div>
</div>