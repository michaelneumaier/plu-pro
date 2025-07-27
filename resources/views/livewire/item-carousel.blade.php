<div x-data="{
        touchStartX: 0, 
        touchStartY: 0,
        lastTouchX: 0,
        isDragging: false,
        dragStarted: false,
        startTime: 0,
        lastTime: 0,
        velocity: 0,
        isTransitioning: false,
        translateX: 0,
        dampening: 0.85,
        snapThreshold: 100,
        velocityThreshold: 0.5,
        maxResistance: 80,
        
        init() {
            this.preloadAdjacentImages();
            this.$watch('$wire.currentIndex', () => {
                this.preloadAdjacentImages();
                this.smoothReset();
            });
        },
        
        preloadAdjacentImages() {
            const currentIndex = $wire.currentIndex || 0;
            const items = $wire.items || [];
            const itemCount = items.length;
            
            [currentIndex - 2, currentIndex - 1, currentIndex + 1, currentIndex + 2].forEach(index => {
                if (index >= 0 && index < itemCount && items[index]?.plu_code?.plu) {
                    const img = new Image();
                    img.src = `/storage/product_images/${items[index].plu_code.plu}.jpg`;
                }
            });
        },
        
        handleTouchStart(e) {
            if (this.isTransitioning) return;
            
            // Stop any ongoing animations
            this.isTransitioning = false;
            
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
            this.lastTouchX = this.touchStartX;
            this.startTime = performance.now();
            this.lastTime = this.startTime;
            this.isDragging = false;
            this.dragStarted = false;
            this.velocity = 0;
            
            // Prevent default scrolling behavior
            e.preventDefault();
        },
        
        handleTouchMove(e) {
            if (this.isTransitioning) return;
            
            const currentX = e.touches[0].clientX;
            const currentY = e.touches[0].clientY;
            const deltaX = currentX - this.touchStartX;
            const deltaY = currentY - this.touchStartY;
            const currentTime = performance.now();
            
            // Determine if this is a horizontal drag
            if (!this.dragStarted) {
                const absX = Math.abs(deltaX);
                const absY = Math.abs(deltaY);
                
                if (absX > 8 || absY > 8) {
                    this.dragStarted = true;
                    this.isDragging = absX > absY && absX > 8;
                }
            }
            
            if (this.isDragging) {
                e.preventDefault();
                e.stopPropagation();
                
                // Calculate velocity using time-based approach
                const timeDelta = currentTime - this.lastTime;
                if (timeDelta > 0) {
                    const moveDelta = currentX - this.lastTouchX;
                    this.velocity = (moveDelta / timeDelta) * this.dampening + this.velocity * (1 - this.dampening);
                }
                
                this.lastTouchX = currentX;
                this.lastTime = currentTime;
                
                // Apply resistance at boundaries
                const resistance = this.calculateResistance(deltaX);
                this.translateX = deltaX * resistance;
            }
        },
        
        handleTouchEnd(e) {
            if (!this.dragStarted) {
                this.resetState();
                return;
            }
            
            if (!this.isDragging) {
                this.resetState();
                return;
            }
            
            e.preventDefault();
            
            const deltaX = this.translateX;
            const absVelocity = Math.abs(this.velocity);
            const currentIndex = $wire.currentIndex || 0;
            const items = $wire.items || [];
            const itemCount = items.length;
            
            // Determine if we should navigate
            let shouldNavigate = false;
            let direction = null;
            
            // High velocity swipe
            if (absVelocity > this.velocityThreshold) {
                shouldNavigate = true;
                direction = this.velocity > 0 ? 'previous' : 'next';
            }
            // Distance threshold
            else if (Math.abs(deltaX) > this.snapThreshold) {
                shouldNavigate = true;
                direction = deltaX > 0 ? 'previous' : 'next';
            }
            
            // Check boundaries
            if (shouldNavigate) {
                if (direction === 'previous' && currentIndex <= 0) {
                    shouldNavigate = false;
                } else if (direction === 'next' && currentIndex >= itemCount - 1) {
                    shouldNavigate = false;
                }
            }
            
            if (shouldNavigate) {
                this.performSwipe(direction);
            } else {
                this.snapBack();
            }
        },
        
        calculateResistance(deltaX) {
            const currentIndex = $wire.currentIndex || 0;
            const items = $wire.items || [];
            const itemCount = items.length;
            
            // Check if we're at boundaries
            const atStart = currentIndex === 0 && deltaX > 0;
            const atEnd = currentIndex === itemCount - 1 && deltaX < 0;
            
            if (atStart || atEnd) {
                const distance = Math.abs(deltaX);
                const resistance = Math.max(0.1, 1 - (distance / this.maxResistance));
                return Math.min(resistance, 0.5);
            }
            
            return 1;
        },
        
        performSwipe(direction) {
            this.isTransitioning = true;
            
            // Add haptic feedback
            if (navigator.vibrate) {
                navigator.vibrate(5);
            }
            
            // Trigger navigation
            if (direction === 'previous') {
                $wire.previous();
            } else {
                $wire.next();
            }
            
            // Smooth transition to new position
            this.smoothReset();
        },
        
        snapBack() {
            this.smoothTransition(0, 250);
        },
        
        smoothReset() {
            this.smoothTransition(0, 200);
        },
        
        smoothTransition(targetX, duration = 300) {
            const startX = this.translateX;
            const distance = targetX - startX;
            const startTime = performance.now();
            
            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Use easeOutCubic for smooth deceleration
                const easeOut = 1 - Math.pow(1 - progress, 3);
                
                this.translateX = startX + (distance * easeOut);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    this.resetState();
                }
            };
            
            requestAnimationFrame(animate);
        },
        
        resetState() {
            this.translateX = 0;
            this.isDragging = false;
            this.dragStarted = false;
            this.isTransitioning = false;
            this.velocity = 0;
        },
        
        // Direct navigation for buttons
        navigateNext() {
            if (this.isTransitioning) return;
            
            if (navigator.vibrate) navigator.vibrate(10);
            this.performSwipe('next');
        },
        
        navigatePrevious() {
            if (this.isTransitioning) return;
            
            if (navigator.vibrate) navigator.vibrate(10);
            this.performSwipe('previous');
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
    @keydown.left.window="$wire.isOpen && navigatePrevious()"
    @keydown.right.window="$wire.isOpen && navigateNext()"
    @keydown.escape.window="$wire.isOpen && $wire.close()"
    @keydown.space.window="$wire.isOpen && (($event.preventDefault()), navigateNext())"
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
         @touchstart="handleTouchStart($event)"
         @touchmove="handleTouchMove($event)" 
         @touchend="handleTouchEnd($event)"
         @click="$event.target === $event.currentTarget && $wire.close()"
         style="height: calc(100vh - 140px);"> <!-- Reserve space for header (72px) and navigation (68px) -->
        
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
            
            <!-- Carousel Track with All Cards -->
            <div class="flex h-full items-center"
                 style="width: calc({{ $this->items->count() }} * 100vw); will-change: transform;"
                 :style="`transform: translateX(calc(-${($wire.currentIndex || 0)} * 100vw + ${translateX}px)); transition: ${isTransitioning ? 'none' : 'transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94)'}`"
                 wire:key="carousel-track-{{ $this->items->count() }}-{{ md5(json_encode($this->items->pluck('id'))) }}">
                
                @foreach($this->items as $index => $item)
                <!-- Card {{ $index }}: PLU {{ optional($item->pluCode)->plu ?? 'N/A' }} -->
                <div class="flex-shrink-0 flex items-center justify-center p-4"
                     style="width: 100vw; height: 100%;"
                     wire:key="carousel-card-{{ $item->id }}">
                    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
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
                        <div class="p-4 space-y-3">
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
                            <div class="bg-white border border-gray-200 rounded-lg p-3">
                                <p class="text-sm text-gray-600 text-center mb-2">Barcode</p>
                                <div class="flex justify-center items-center">
                                    <x-barcode code="{{ optional($item->pluCode)->plu }}" size="default" />
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
        <button @click="navigatePrevious()" 
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
        <button @click="navigateNext()" 
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