@props(['listId'])

<template x-for="item in $store.listManager.filteredItems" :key="item.id">
    <div 
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300"
        :class="{ 'opacity-50': item.isTemp, 'ring-2 ring-blue-500': item.needsSync }"
        x-data="{ showDetails: false }"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
    >
        <div class="p-4">
            <div class="flex items-start justify-between">
                <!-- PLU Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2">
                        <h3 class="text-lg font-semibold text-gray-900" x-text="item.plu"></h3>
                        <template x-if="item.organic">
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Organic</span>
                        </template>
                    </div>
                    <p class="text-sm text-gray-600 mt-1" x-text="item.variety || 'No variety specified'"></p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded" x-text="item.commodity || 'Unknown'"></span>
                        <template x-if="item.size">
                            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded" x-text="'Size: ' + item.size"></span>
                        </template>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2 ml-4">
                    <template x-if="deleteMode">
                        <button 
                            @click="$store.listManager.removeItem(item.id)"
                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            :disabled="item.isTemp"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </template>
                    <button 
                        @click="showDetails = !showDetails"
                        class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': showDetails }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Inventory Control -->
            <div class="mt-4">
                <!-- We'll use a placeholder div that will be replaced with the actual inventory component -->
                <div :id="'inventory-container-' + item.id" class="min-h-[60px]">
                    <template x-if="!item.isTemp">
                        <div class="flex items-center justify-center space-x-4 p-2 bg-gray-50 rounded-lg">
                            <button @click="$store.listManager.updateInventory(item.id, -1)" 
                                :disabled="item.inventory_level <= 0"
                                class="p-2 bg-red-500 text-white rounded-full hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="text-xl font-semibold w-16 text-center" x-text="item.inventory_level"></span>
                            <button @click="$store.listManager.updateInventory(item.id, 1)" 
                                class="p-2 bg-green-500 text-white rounded-full hover:bg-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <template x-if="item.isTemp">
                        <div class="flex items-center justify-center p-4 text-gray-400">
                            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">Saving...</span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Collapsible Details -->
            <div x-show="showDetails" x-collapse class="mt-4 pt-4 border-t border-gray-100">
                <div class="space-y-2 text-sm">
                    <template x-if="item.category">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Category:</span>
                            <span class="text-gray-900" x-text="item.category"></span>
                        </div>
                    </template>
                    <template x-if="item.retail_price">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Retail Price:</span>
                            <span class="text-gray-900" x-text="'$' + item.retail_price"></span>
                        </div>
                    </template>
                    <template x-if="item.consumer_usage_tier">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Usage Tier:</span>
                            <span class="text-gray-900" x-text="item.consumer_usage_tier"></span>
                        </div>
                    </template>
                    <template x-if="item.isTemp">
                        <div class="text-yellow-600 text-xs mt-2">
                            <svg class="w-4 h-4 inline mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Saving...
                        </div>
                    </template>
                    <template x-if="item.needsSync">
                        <div class="text-orange-600 text-xs mt-2">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Pending sync
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>