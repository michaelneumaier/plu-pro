@props(['listId'])

<div class="w-full" x-data="{ 
    showDeleteButtons: false
}" @toggle-delete-buttons.window="showDeleteButtons = !showDeleteButtons">
    <template x-if="$store.listManager.filteredItems.length > 0">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
            <!-- Header -->
            <div class="grid grid-cols-[3.5rem,3rem,1fr,7rem,auto,auto] bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
                <div class="p-1">PLU</div>
                <div class="p-1">Image</div>
                <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
                <div class="p-1">Actions</div>
            </div>

            <!-- PLU Code Items -->
            <template x-for="item in $store.listManager.filteredItems" :key="item.id">
                <div
                    :class="item.organic ? 'bg-green-50 hover:bg-green-100' : 'bg-white hover:bg-gray-50'"
                    class="cursor-pointer border-b border-gray-200 last:border-b-0 transition-all duration-200"
                    :style="item.isTemp ? 'opacity: 0.7' : ''"
                >
                    <div class="grid grid-cols-[3.5rem,3rem,1fr,auto,auto] min-h-16"
                        @click="$dispatch('pluCodeSelected', [item.plu_code_id, item.organic])"
                        :data-plu-id="item.plu_code_id">
                        <div class="flex flex-col items-center justify-evenly">
                            <div class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden">
                                <span class="font-mono font-semibold" x-text="item.organic ? '9' + item.plu : item.plu"></span>
                            </div>
                            <div class="mr-1">
                                <!-- Consumer usage indicator - simplified for Alpine -->
                                <div class="w-2 h-2 rounded-full" 
                                     :class="{
                                         'bg-green-500': item.consumer_usage_tier === 'high',
                                         'bg-yellow-500': item.consumer_usage_tier === 'medium', 
                                         'bg-red-500': item.consumer_usage_tier === 'low',
                                         'bg-gray-300': !item.consumer_usage_tier
                                     }">
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center p-1">
                            <!-- PLU Image - simplified for Alpine -->
                            <div class="w-8 h-8 bg-gray-100 rounded border flex items-center justify-center text-xs text-gray-500">
                                <span x-text="item.plu.toString().slice(-2)"></span>
                            </div>
                        </div>
                        <div class="flex flex-col py-1 text-sm justify-between overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
                            <div></div>
                            <span class="font-bold">
                                <span x-text="item.variety"></span>
                                <template x-if="item.aka">
                                    <span class="text-gray-500" x-text="' - ' + item.aka"></span>
                                </template>
                            </span>
                            <div class="flex justify-between">
                                <span class="text-gray-500 capitalize inline-flex" x-text="item.commodity ? item.commodity.toLowerCase().replace(/\b\w/g, l => l.toUpperCase()) : ''"></span>
                                <span class="text-gray-500" x-text="item.size || ''"></span>
                            </div>
                        </div>
                        <!-- Inventory Level Component -->
                        <div class="flex items-center p-1">
                            <template x-if="!item.isTemp">
                                <div class="flex items-center justify-center space-x-2 min-w-[100px]">
                                    <button @click="$store.listManager.updateInventory(item.id, -1)" 
                                        :disabled="item.inventory_level <= 0"
                                        class="w-6 h-6 bg-red-500 text-white rounded-full hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path>
                                        </svg>
                                    </button>
                                    <span class="text-sm font-semibold w-8 text-center" x-text="item.inventory_level || 0"></span>
                                    <button @click="$store.listManager.updateInventory(item.id, 1)" 
                                        class="w-6 h-6 bg-green-500 text-white rounded-full hover:bg-green-600 flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <template x-if="item.isTemp">
                                <div class="flex items-center justify-center text-gray-400 text-xs min-w-[100px]">
                                    <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Saving...</span>
                                </div>
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
                                // TODO: Sync to server via list manager
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
        <div class="bg-gray-50 rounded-lg p-8 text-center">
            <template x-if="$store.listManager.items.length === 0">
                <div>
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-gray-600 mb-2">No items in this list yet</p>
                    <button @click="showAddSection = true"
                        class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                        Add your first item
                    </button>
                </div>
            </template>
            <template x-if="$store.listManager.items.length > 0">
                <div>
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <p class="text-gray-600 mb-2">No items match your filters</p>
                    <button @click="$store.listManager.resetFilters()"
                        class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                        Clear filters
                    </button>
                </div>
            </template>
        </div>
    </template>
</div>