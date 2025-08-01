<!-- resources/views/livewire/plu-code-table.blade.php -->
@props([
    'collection' => collect(),
    'dualVersionPluCodes' => collect(),
    'userListId' => null,
    'refreshToken' => null,
    'onDelete' => null,
    'onAdd' => null,
    'readOnly' => false,
    'showInventory' => true,
    'showCommodityGroups' => true,
    'showPagination' => true
])
@php
// Use the collection prop as pluCodes for backward compatibility
$pluCodes = $collection;
// Determine the number of columns based on the presence of actions
$hasActions = $onDelete || $onAdd;
$colCount = $hasActions ? 5 : 4;
@endphp

<div class="w-full" x-data="{ 
    showDeleteButtons: false
}" @toggle-delete-buttons.window="showDeleteButtons = !showDeleteButtons">
    @if($pluCodes->count())
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="grid {{ $showInventory && $hasActions ? 'grid-cols-[3.5rem,3rem,1fr,7rem,auto]' : ($showInventory ? 'grid-cols-[3.5rem,3rem,1fr,7rem]' : ($hasActions ? 'grid-cols-[3.5rem,3rem,1fr,auto]' : 'grid-cols-[3.5rem,3rem,1fr]')) }} bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
            <div class="p-1">PLU</div>
            <div class="p-1">Image</div>
            <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
            @if($showInventory)
                <div class="p-1">Inventory</div>
            @endif
            @if($hasActions)
                <div class="p-1">Actions</div>
            @endif
        </div>

        <!-- PLU Code Items -->
        @php $currentCommodity = null; @endphp
        @foreach($pluCodes as $item)
        @php
            // Determine if this is a list item or a PLU/UPC code
            $isListItem = isset($item->plu_code_id) || isset($item->upc_code_id);
            
            if ($isListItem) {
                // This is a ListItem with pluCode or upcCode relationship
                $listItem = $item;
                $pluCode = $item->pluCode ?? null;
                $upcCode = $item->upcCode ?? null;
                $isUpcItem = $item->item_type === 'upc' && $upcCode;
            } else {
                // This is a PLUCode, might have listItem relationship
                $pluCode = $item;
                $upcCode = null;
                $listItem = $item->listItem ?? null;
                $isUpcItem = false;
            }
            
            // Safety check - ensure we have a valid PLU code or UPC code
            if (!$pluCode && !$upcCode) {
                continue; // Skip this iteration if no valid code
            }
            
            // Get commodity for grouping (from PLU or UPC)
            $commodity = $isUpcItem ? $upcCode->commodity : $pluCode->commodity;
            
            // Check if commodity changed for visual grouping (only if enabled)
            $commodityChanged = false;
            if ($showCommodityGroups) {
                $commodityChanged = $currentCommodity !== null && $currentCommodity !== $commodity;
                $currentCommodity = $commodity;
            }
        @endphp
        
        @if($showCommodityGroups && ($commodityChanged || $loop->first))
        <!-- Commodity separator -->
        <div class="border-t-2 border-gray-300 bg-gray-50">
            <div class="px-4 py-1 text-xs font-medium text-gray-600 uppercase tracking-wide">
                {{ ucwords(strtolower($commodity)) }}
            </div>
        </div>
        @endif
        
        <div
            class="{{ $listItem && $listItem->organic ? 'bg-green-50 hover:bg-green-100' : 'bg-white hover:bg-gray-50' }} cursor-pointer border-b border-gray-200 last:border-b-0 {{ $showCommodityGroups && $commodityChanged ? 'border-t-0' : '' }}">
            <div class="grid {{ $showInventory && $hasActions ? 'grid-cols-[3.5rem,3rem,1fr,auto,auto]' : ($showInventory ? 'grid-cols-[3.5rem,3rem,1fr,auto]' : ($hasActions ? 'grid-cols-[3.5rem,3rem,1fr,auto]' : 'grid-cols-[3.5rem,3rem,1fr]')) }} min-h-16 "
                wire:click="$dispatch('{{ $isUpcItem ? 'upcCodeSelected' : 'pluCodeSelected' }}', {{ $isUpcItem ? '[' . $upcCode->id . ']' : '[' . $pluCode->id . ', ' . (($listItem && $listItem->organic) ? 'true' : 'false') . ']' }})"
                wire:key="{{ $isUpcItem ? 'upc' : 'plu' }}-row-{{ $listItem ? $listItem->id : ($isUpcItem ? $upcCode->id : $pluCode->id) }}-{{ $userListId }}-{{ $refreshToken ?? time() }}"
                data-{{ $isUpcItem ? 'upc' : 'plu' }}-id="{{ $isUpcItem ? $upcCode->id : $pluCode->id }}">
                <div class="flex flex-col items-center justify-evenly">
                    @if($isUpcItem)
                        <!-- UPC Badge -->
                        <div class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-blue-100 text-xs text-blue-800 border border-blue-200 rounded overflow-hidden">
                            <span class="font-semibold">UPC</span>
                        </div>
                        <div class="mr-1">
                            <!-- No usage indicator for UPC items -->
                            <div class="w-4 h-2"></div>
                        </div>
                    @else
                        <!-- PLU Badge -->
                        <a href="{{ route('plu.show', $listItem && $listItem->organic ? '9' . $pluCode->plu : $pluCode->plu) }}"
                           @click.stop
                           class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden hover:bg-green-200 transition-colors">
                            <span class="font-mono font-semibold">
                                @if($listItem && $listItem->organic)
                                9{{ $pluCode->plu }}
                                @else
                                {{ $pluCode->plu }}
                                @endif
                            </span>
                        </a>
                        <div class="mr-1"><x-consumer-usage-indicator :tier="$pluCode->consumer_usage_tier" /></div>
                    @endif
                </div>
                <div class="flex items-center p-1">
                    @if($isUpcItem)
                        @if($upcCode->has_image)
                            <img src="{{ asset('storage/upc_images/' . $upcCode->upc . '.jpg') }}" 
                                 alt="{{ $upcCode->name }}" 
                                 class="w-12 h-12 object-cover rounded-lg">
                        @else
                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                                <div class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <svg class="w-1/2 h-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        @endif
                    @else
                        <x-plu-image :plu="$pluCode->plu" size="sm" />
                    @endif
                </div>
                <div
                    class="flex flex-col py-1 text-sm justify-between overflow-hidden text-ellipsis whitespace-nowrap flex-grow {{ $readOnly ? 'pr-3' : '' }}">
                    <div></div>
                    <span class="font-bold">
                        @if($isUpcItem)
                            {{ $upcCode->name }}
                            @if(!empty($upcCode->brand))
                            <span class="text-gray-500"> - {{ $upcCode->brand }}</span>
                            @endif
                        @else
                            {{ $pluCode->variety }}
                            @if(!empty($pluCode->aka))
                            <span class="text-gray-500"> - {{ $pluCode->aka }}</span>
                            @endif
                            @if($listItem && $listItem->organic)
                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                Organic
                            </span>
                            @endif
                            @if($dualVersionPluCodes->contains($pluCode->id))
                            <span class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-800" title="Both regular and organic versions in list">
                                Both
                            </span>
                            @endif
                        @endif
                    </span>
                    <div class="flex justify-between">
                        <span class="text-gray-500 capitalize inline-flex">
                            {{ ucwords(strtolower($commodity)) }}
                        </span>
                        <span class="text-gray-500">
                            @if($isUpcItem)
                                {{ $upcCode->upc ?? '' }}
                            @else
                                {{ $pluCode->size ?? '' }}
                            @endif
                        </span>
                    </div>
                </div>
                @if($showInventory)
                    <!-- Inventory Level Component -->
                    <div class="flex items-center p-1">
                        @if($listItem && !$onAdd && isset($listItem->id) && $listItem->id)
                            @if($readOnly)
                                <!-- Read-only inventory display -->
                                <div class="flex items-center justify-center w-12 h-8 bg-gray-100 text-gray-700 font-semibold text-base rounded border">
                                    {{ ($listItem->inventory_level ?? 0) > 0 ? ($listItem->inventory_level ?? 0) : '0' }}
                                </div>
                            @else
                                <!-- Interactive inventory component -->
                                <livewire:inventory-level :listItemId="$listItem->id" :userListId="$userListId"
                                    :wire:key="'inv-level-' . $listItem->id . '-' . ($refreshToken ?? time())" />
                            @endif
                        @endif
                    </div>
                @endif

                @if($hasActions)
                <div class="flex items-center">

                    @if($onAdd)
                    <div class="flex space-x-2">
                        @if($isUpcItem)
                            <button @click.stop="$dispatch('disable-add-buttons')"
                                wire:click.stop="{{ $onAdd }}({{ $upcCode->id }}, false, 'upc')" x-data="{ disabled: false }"
                                @disable-add-buttons.window="
                                disabled = true;
                                setTimeout(() => $dispatch('enable-add-buttons'), 500);
                            " @enable-add-buttons.window="disabled = false" x-bind:disabled="disabled"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                Add UPC
                            </button>
                        @else
                            <button @click.stop="$dispatch('disable-add-buttons')"
                                wire:click.stop="{{ $onAdd }}({{ $pluCode->id }}, false)" x-data="{ disabled: false }"
                                @disable-add-buttons.window="
                                disabled = true;
                                setTimeout(() => $dispatch('enable-add-buttons'), 500);
                            " @enable-add-buttons.window="disabled = false" x-bind:disabled="disabled"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                Add
                            </button>
                            <button @click.stop="$dispatch('disable-add-buttons')"
                                wire:click.stop="{{ $onAdd }}({{ $pluCode->id }}, true)" x-data="{ disabled: false }"
                                @disable-add-buttons.window="
                                disabled = true;
                                setTimeout(() => $dispatch('enable-add-buttons'), 500);
                            " @enable-add-buttons.window="disabled = false" x-bind:disabled="disabled"
                                class="bg-green-600 hover:bg-green-800 text-white font-bold py-1 px-2 rounded disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                                Add as Organic
                            </button>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @if(!$readOnly)
            <div class="flex justify-between p-1 space-x-2 px-10 md:px-1" x-show=" showDeleteButtons" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90">
                @if($listItem && !$onAdd && isset($listItem->id) && $listItem->id && !$isUpcItem)
                <livewire:organic-toggle :list-item="$listItem"
                    :wire:key="'organic-toggle-' . $listItem->id . '-' . ($refreshToken ?? time())" />
                @endif
                @if($hasActions && $onDelete && $listItem && isset($listItem->id) && $listItem->id)
                <button x-show="showDeleteButtons" x-cloak
                    @click.stop="$event.preventDefault(); if(confirm('Are you sure you want to remove this {{ $isUpcItem ? 'UPC' : ($listItem->organic ? 'organic' : 'regular') }} item from your list?')) { $wire.call('removeListItem', {{ $listItem->id }}) }"
                    wire:key="delete-button-{{ $listItem->id }}-{{ now() }}"
                    class=" px-3 py-1 mr-1 bg-red-500 hover:bg-red-700 text-white text-sm font-bold rounded-md flex items-center justify-center"
                    aria-label="Delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Delete
                </button>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    @if($showPagination && $pluCodes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="mt-4">
        {{ $pluCodes->links() }}
    </div>
    @endif
    @else
    <p class="mt-4 p-4">No PLU Codes found.</p>
    @endif
</div>