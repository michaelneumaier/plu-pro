@props(['items' => [], 'userListId' => null])

<div x-data="virtualList" 
     x-init="items = @js($items)"
     class="relative w-full">
    
    <!-- Search bar (sticky) -->
    <div class="sticky top-0 z-10 bg-white border-b border-gray-200 pb-2">
        <input type="text" 
               x-model="searchTerm"
               placeholder="Search items in this list..."
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>
    
    <!-- Virtual scroll container -->
    <div x-ref="virtualScroll"
         x-data="virtualScroll({ itemHeight: 120, overscan: 5 })"
         class="relative overflow-auto"
         style="height: calc(100vh - 200px); min-height: 400px;">
        
        <!-- Total height spacer -->
        <div :style="`height: ${totalHeight}px`"></div>
        
        <!-- Visible items -->
        <div class="absolute top-0 left-0 right-0">
            <template x-for="item in visibleItems" :key="item.id">
                <div :style="`transform: translateY(${item._virtualTop}px)`"
                     class="absolute left-0 right-0 bg-white border-b border-gray-200 hover:bg-gray-50 transition-colors">
                    
                    <!-- Mobile-optimized PLU item -->
                    <div class="p-3 grid grid-cols-[auto,1fr,auto] gap-3 items-center"
                         :class="{ 'bg-green-50': item.organic }">
                        
                        <!-- PLU Code and Image -->
                        <div class="flex items-center gap-2">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                                <img :src="`/storage/plu-images/${item.plu_code}.jpg`" 
                                     :alt="item.variety"
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     onerror="this.src='/images/placeholder.png'">
                            </div>
                            <div class="flex flex-col">
                                <span class="text-lg font-bold text-green-600" 
                                      x-text="item.organic ? '9' + item.plu_code : item.plu_code"></span>
                                <span class="text-xs text-gray-500" x-text="item.commodity"></span>
                            </div>
                        </div>
                        
                        <!-- Item Details -->
                        <div class="flex flex-col min-w-0">
                            <span class="font-semibold text-gray-900 truncate" x-text="item.variety"></span>
                            <span class="text-sm text-gray-500" x-text="item.size || 'Standard'"></span>
                        </div>
                        
                        <!-- Inventory Control -->
                        <div class="flex-shrink-0">
                            @if($userListId)
                                <livewire:inventory-level 
                                    :list-item-id="item.id" 
                                    :user-list-id="$userListId"
                                    :key="'inventory-' . item.id"
                                    wire:key="'inventory-' . item.id" />
                            @endif
                        </div>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- Loading indicator -->
        <div x-show="loading" 
             class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500"></div>
        </div>
        
        <!-- Empty state -->
        <div x-show="!loading && items.length === 0"
             class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">No items in this list</p>
            </div>
        </div>
    </div>
</div>