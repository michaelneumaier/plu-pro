<div x-data="{ 
        isCarouselOpen: false,
        touchStartX: 0, 
        touchEndX: 0, 
        handleGesture() {
            const deltaX = this.touchEndX - this.touchStartX;
            if (deltaX > 50) {
                @this.previous();
            } else if (deltaX < -50) {
                @this.next();
            }
        } 
    }" x-show="$wire.isOpen" x-transition.opacity
    class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-75 z-50" @click="$wire.close()"
    @carousel-open.window="$wire.openCarousel()" @carousel-close.window="$wire.close()"
    @touchstart.window="touchStartX = $event.changedTouches[0].screenX" @touchend.window="
        touchEndX = $event.changedTouches[0].screenX; 
        handleGesture()
    " tabindex="0" @keydown.left.window="$wire.isOpen && $wire.previous()"
    @keydown.right.window="$wire.isOpen && $wire.next()">
    <div @click.stop
        class="bg-white rounded-lg shadow-lg w-11/12 max-w-md p-6 relative transform transition-transform duration-300"
        x-show="$wire.isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90">
        <!-- Close Button -->
        <button wire:click="close" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 focus:outline-none"
            aria-label="Close Carousel">
            &times;
        </button>

        <!-- Content -->
        <div class="flex flex-col items-center">
            @if($this->currentItem)
            <!-- Count -->
            <h2 class="text-3xl font-bold mb-2">
                Count: {{ $this->currentItem->inventory_level }}
            </h2>

            <p class="text-lg mb-4"></p>
            {{ optional($this->currentItem->pluCode)->commodity ?? 'N/A' }}
            </p>

            <p class="text-lg mb-4">
                {{ optional($this->currentItem->pluCode)->variety ?? 'N/A' }}
            </p>

            <!-- PLU Code -->
            <p class="text-lg mb-4">
                PLU: {{ optional($this->currentItem->pluCode)->plu ?? 'N/A' }}
            </p>
            <div class="flex justify-center h-10 m-2">
                <x-barcode code="{{ optional($this->currentItem->pluCode)->plu }}" />
            </div>

            <!-- Navigation Arrows -->
            <div class="flex items-center space-x-4">
                <button wire:click="previous"
                    class="bg-blue-500 text-white w-12 h-12 rounded-full hover:bg-blue-700 focus:outline-none flex items-center justify-center"
                    aria-label="Previous Item">
                    &#8592;
                </button>

                @if($this->items->isNotEmpty())
                <span class="text-gray-700 font-medium mx-4">
                    {{ $currentIndex + 1 }} of {{ $this->items->count() }}
                </span>
                @endif

                <button wire:click="next"
                    class="bg-blue-500 text-white w-12 h-12 rounded-full hover:bg-blue-700 focus:outline-none flex items-center justify-center"
                    aria-label="Next Item">
                    &#8594;
                </button>
            </div>

            @else
            <p class="text-lg text-gray-500">No items available.</p>
            @endif
        </div>
    </div>
</div>