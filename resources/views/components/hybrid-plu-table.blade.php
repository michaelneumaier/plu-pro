@props(['listId'])

<div class="w-full" x-data="{ 
    showDeleteButtons: false,
    listItems: [],
    
    init() {
        // Watch for changes in the Alpine store and update the Livewire component
        this.$watch('$store.listManager.items', (items) => {
            this.listItems = items;
            this.refreshLivewireTable();
        });
        
        // Initialize with current items
        this.listItems = this.$store.listManager.filteredItems;
    },
    
    refreshLivewireTable() {
        // Trigger a Livewire refresh when items change  
        this.$wire.call('refreshFromStore', this.$store.listManager.items);
    }
}" @toggle-delete-buttons.window="showDeleteButtons = !showDeleteButtons">

    <!-- Use server-side rendered items with Livewire -->
    @if($listItems->count())
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
        @foreach($listItems as $listItem)
        <div
            class="{{ $listItem->organic ? 'bg-green-50 hover:bg-green-100' : 'bg-white hover:bg-gray-50' }} cursor-pointer border-b border-gray-200 last:border-b-0"
            wire:key="list-item-{{ $listItem->id }}"
            x-data="{ 
                isTemp: {{ $listItem->isTemp ?? 'false' }},
                itemData: @js([
                    'id' => $listItem->id,
                    'plu_code_id' => $listItem->plu_code_id,
                    'organic' => $listItem->organic,
                    'inventory_level' => $listItem->inventory_level
                ])
            }"
            :style="isTemp ? 'opacity: 0.7' : ''"
        >
            <div class="grid grid-cols-[3.5rem,3rem,1fr,auto,auto] min-h-16 "
                @click="$dispatch('pluCodeSelected', [{{ $listItem->plu_code_id }}, {{ $listItem->organic ? 'true' : 'false' }}])"
                data-plu-id="{{ $listItem->plu_code_id }}">
                
                <div class="flex flex-col items-center justify-evenly">
                    <div class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden">
                        <span class="font-mono font-semibold">
                            @if($listItem->organic)
                            9{{ $listItem->pluCode->plu }}
                            @else
                            {{ $listItem->pluCode->plu }}
                            @endif
                        </span>
                    </div>
                    <div class="mr-1"><x-consumer-usage-indicator :tier="$listItem->pluCode->consumer_usage_tier" /></div>
                </div>
                
                <div class="flex items-center p-1">
                    <x-plu-image :plu="$listItem->pluCode->plu" size="sm" />
                </div>
                
                <div class="flex flex-col py-1 text-sm justify-between overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
                    <div></div>
                    <span class="font-bold">{{ $listItem->pluCode->variety }}
                        @if(!empty($listItem->pluCode->aka))
                        <span class="text-gray-500"> - {{ $listItem->pluCode->aka }}</span>
                        @endif
                    </span>
                    <div class="flex justify-between">
                        <span class="text-gray-500 capitalize inline-flex">
                            {{ ucwords(strtolower($listItem->pluCode->commodity))}}
                        </span>
                        <span class="text-gray-500">{{ $listItem->pluCode->size }}</span>
                        <!-- Size displayed on the right -->
                    </div>
                </div>
                
                <!-- Inventory Level Component -->
                <div class="flex items-center p-1">
                    <template x-if="!isTemp">
                        <div>
                            <livewire:inventory-level 
                                :list-item-id="$listItem->id" 
                                :user-list-id="$listId"
                                :wire:key="'inv-level-' . $listItem->id" />
                        </div>
                    </template>
                    <template x-if="isTemp">
                        <div class="flex items-center justify-center text-gray-400 text-xs">
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
            
            <div class="flex justify-between p-1 space-x-2 px-10 md:px-1" x-show="showDeleteButtons && !isTemp" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90">
                <livewire:organic-toggle :list-item="$listItem"
                    :wire:key="'organic-toggle-stable-'.$listItem->id" />
                <button x-show="showDeleteButtons" x-cloak
                    @click.stop="if(confirm('Are you sure you want to remove this PLU Code from your list?')) { $store.listManager.removeItem(itemData.id) }"
                    class="px-3 py-1 mr-1 bg-red-500 hover:bg-red-700 text-white text-sm font-bold rounded-md flex items-center justify-center"
                    aria-label="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Delete
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <p class="mt-4 p-4">No items in this list yet.</p>
    @endif
</div>