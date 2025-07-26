@props(['listId'])

<div class="w-full" x-data="{ 
    showDeleteButtons: false
}" @toggle-delete-buttons.window="showDeleteButtons = !showDeleteButtons">
    <template x-if="$store.listManager.filteredItems.length > 0">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
            <!-- Header -->
            <div class="grid grid-cols-[3.5rem,3rem,1fr,auto,auto] bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
                <div class="p-1">PLU</div>
                <div class="p-1">Image</div>
                <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
                <!-- <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">UPC</div>
                <div class="p-1">Inventory</div> -->
            </div>

            <!-- PLU Code Items -->
            <template x-for="item in $store.listManager.filteredItems" :key="item.id">
                <div
                    :class="item.organic ? 'bg-green-50 hover:bg-green-100' : 'bg-white hover:bg-gray-50'"
                    class="cursor-pointer border-b border-gray-200 last:border-b-0"
                    :style="item.isTemp ? 'opacity: 0.7' : ''"
                >
                    <div class="grid grid-cols-[3.5rem,3rem,1fr,auto,auto] min-h-16 "
                        @click="$dispatch('pluCodeSelected', [item.plu_code_id])"
                        :data-plu-id="item.plu_code_id">
                        
                        <!-- PLU Column -->
                        <div class="flex flex-col items-center justify-evenly">
                            <div class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden">
                                <span class="font-mono font-semibold" x-text="item.organic ? '9' + item.plu : item.plu"></span>
                            </div>
                            <!-- Consumer Usage Indicator -->
                            <div class="mr-1">
                                <div class="flex items-end space-x-1" :title="(item.consumer_usage_tier || 'Unknown').charAt(0).toUpperCase() + (item.consumer_usage_tier || 'unknown').slice(1) + ' Usage Tier'">
                                    <template x-for="i in 3" :key="i">
                                        <div 
                                            :class="{
                                                'w-1 h-4 rounded': (item.consumer_usage_tier === 'high' && i <= 3) || (item.consumer_usage_tier === 'medium' && i <= 2) || (item.consumer_usage_tier === 'low' && i <= 1),
                                                'w-1 h-2 bg-gray-300 rounded': !((item.consumer_usage_tier === 'high' && i <= 3) || (item.consumer_usage_tier === 'medium' && i <= 2) || (item.consumer_usage_tier === 'low' && i <= 1)),
                                                'bg-green-500': item.consumer_usage_tier === 'high' && i <= 3,
                                                'bg-yellow-500': item.consumer_usage_tier === 'medium' && i <= 2,
                                                'bg-red-500': item.consumer_usage_tier === 'low' && i <= 1
                                            }"
                                        ></div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-1">
                            <!-- Use actual PLU image component -->
                            <div class="w-8 h-8 bg-gray-100 rounded-lg overflow-hidden">
                                <template x-if="item.plu">
                                    <div>
                                        <!-- We'll use a simple placeholder that matches the original -->
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                            <svg class="w-1/2 h-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        <!-- Variety Column -->
                        <div class="flex flex-col py-1 text-sm justify-between overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
                            <div></div>
                            <span class="font-bold" x-text="item.variety">
                                <template x-if="item.aka">
                                    <span class="text-gray-500" x-text="' - ' + item.aka"></span>
                                </template>
                            </span>
                            <div class="flex justify-between">
                                <span class="text-gray-500 capitalize inline-flex" x-text="item.commodity ? item.commodity.toLowerCase().replace(/\b\w/g, l => l.toUpperCase()) : ''"></span>
                                <span class="text-gray-500" x-text="item.size"></span>
                                <!-- Size displayed on the right -->
                            </div>
                        </div>
                        
                        <!-- Inventory Level Component -->
                        <div class="flex items-center p-1">
                            <template x-if="!item.isTemp">
                                <div :id="'inventory-component-' + item.id" class="w-full"></div>
                            </template>
                        </div>

                        <!-- Actions column (empty for list view) -->
                        <div class="flex items-center">
                        </div>
                    </div>
                    
                    <!-- Delete/Organic toggle row -->
                    <div class="flex justify-between p-1 space-x-2 px-10 md:px-1" 
                         x-show="showDeleteButtons && !item.isTemp" 
                         x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-90"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-90">
                        <div class="flex items-center">
                            <button @click="
                                item.organic = !item.organic;
                                $store.listManager.updateItem(item.id, { organic: item.organic });
                            "
                                class="flex items-center space-x-2 px-3 py-1 rounded-md text-sm font-medium transition-colors"
                                :class="item.organic ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                                <div class="w-3 h-3 rounded-full" 
                                     :class="item.organic ? 'bg-green-500' : 'bg-gray-400'"></div>
                                <span x-text="item.organic ? 'Organic' : 'Regular'"></span>
                            </button>
                        </div>
                        <button 
                            @click.stop="if(confirm('Are you sure you want to remove this PLU Code from your list?')) { $store.listManager.removeItem(item.id) }"
                            class="px-3 py-1 mr-1 bg-red-500 hover:bg-red-700 text-white text-sm font-bold rounded-md flex items-center justify-center"
                            aria-label="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </template>
    
    <!-- Empty State -->
    <template x-if="$store.listManager.filteredItems.length === 0">
        <p class="mt-4 p-4">No PLU Codes found.</p>
    </template>
</div>