<!-- resources/views/livewire/plu-code-table.blade.php -->
@props([
'collection' => collect(),
'dualVersionPluCodes' => collect(),
'userListId' => null,
'refreshToken' => null,
'onDelete' => null,
'onAdd' => null,
'dispatchAdd' => false,
'readOnly' => false,
'showInventory' => true,
'showCommodityGroups' => true,
'showPagination' => true
])
@php
// Use the collection prop as pluCodes for backward compatibility
$pluCodes = $collection;
// Determine the number of columns based on the presence of actions
$hasActions = $onDelete || $onAdd || $dispatchAdd;
$colCount = $hasActions ? 5 : 4;
@endphp

<div class="w-full" x-data="{ 
    showDeleteButtons: false
}" @toggle-delete-buttons.window="showDeleteButtons = !showDeleteButtons">
    @if($pluCodes->count())
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div
            class="grid {{ $showInventory && $hasActions ? 'grid-cols-[3.5rem,3rem,1fr,7rem,auto]' : ($showInventory ? 'grid-cols-[3.5rem,3rem,1fr,7rem]' : ($hasActions ? 'grid-cols-[3.5rem,3rem,1fr,auto]' : 'grid-cols-[3.5rem,3rem,1fr]')) }} bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
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
        <div class="border-t-2 border-b-2 border-gray-300 bg-gray-50 commodity-header" data-commodity="{{ $commodity }}"
            x-show="$store.listManager.isCommodityVisible('{{ $commodity }}')"
            x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-4 py-1 text-xs font-medium text-gray-600 uppercase tracking-wide">
                {{ ucwords(strtolower($commodity)) }}
            </div>
        </div>
        @endif

        @php $nonOrganicBg = $loop->even ? 'bg-gray-50 hover:bg-gray-100' : 'bg-white hover:bg-gray-50'; @endphp
        <div class="py-1 {{ ($listItem && !$isUpcItem) ? '' : ($listItem && $listItem->organic ? 'bg-green-50 hover:bg-green-100' : $nonOrganicBg) }} cursor-pointer border-b border-gray-200 last:border-b-0 {{ $showCommodityGroups && $commodityChanged ? 'border-t-0' : '' }} list-item-row"
            @if($listItem && !$isUpcItem)
            :class="$store.listManager.isItemOrganic({{ $listItem->id }}) ? 'bg-green-50 hover:bg-green-100' : '{{ $nonOrganicBg }}'"
            @endif
            @if($listItem) data-search-content="{{
                ($isUpcItem ? $upcCode->upc : $pluCode->plu) . ' ' .
                ($isUpcItem ? $upcCode->name : $pluCode->variety) . ' ' .
                ($isUpcItem ? $upcCode->commodity : $pluCode->commodity) . ' ' .
                ($isUpcItem ? $upcCode->category : $pluCode->category) . ' ' .
                ($isUpcItem && $upcCode->brand ? $upcCode->brand : '') . ' ' .
                ($listItem && $listItem->organic && !$isUpcItem ? '9' . $pluCode->plu : '') . ' ' .
                ($isUpcItem ? '' : $pluCode->size ?? '')
            }}" data-item-id="{{ $listItem->id }}" data-item-type="{{ $isUpcItem ? 'upc' : 'plu' }}"
            data-commodity="{{ $commodity }}" @endif
            wire:key="{{ $isUpcItem ? 'upc' : 'plu' }}-row-{{ $listItem ? $listItem->id : ($isUpcItem ? $upcCode->id : $pluCode->id) }}-{{ $userListId }}"
            x-show="$store.listManager.isItemVisible({{ $listItem ? $listItem->id : 0 }})"
            x-transition:enter="transition-opacity duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="grid {{ $showInventory && $hasActions ? 'grid-cols-[3.5rem,3rem,1fr,auto,auto]' : ($showInventory ? 'grid-cols-[3.5rem,3rem,1fr,auto]' : ($hasActions ? 'grid-cols-[3.5rem,3rem,1fr,auto]' : 'grid-cols-[3.5rem,3rem,1fr]')) }} min-h-16"
                @click="$dispatch('{{ $isUpcItem ? 'upcCodeSelected' : 'pluCodeSelected' }}', {{ $isUpcItem ? '[' . $upcCode->id . ']' : ($listItem && !$isUpcItem ? '[' . $pluCode->id . ', $store.listManager.isItemOrganic(' . $listItem->id . ')]' : '[' . $pluCode->id . ', false]') }})"
                data-{{ $isUpcItem ? 'upc' : 'plu' }}-id="{{ $isUpcItem ? $upcCode->id : $pluCode->id }}">
                <div class="flex flex-col items-center justify-evenly">
                    @if($isUpcItem)
                    <!-- UPC Badge -->
                    <div
                        class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-blue-100 text-xs text-blue-800 border border-blue-200 rounded overflow-hidden">
                        <span class="font-semibold">UPC</span>
                    </div>
                    <div x-show="!showDeleteButtons">
                        <!-- No usage indicator for UPC items -->
                        <div class="w-4 h-2"></div>
                    </div>
                    @if(!$readOnly && $hasActions && $onDelete && $listItem && isset($listItem->id) && $listItem->id)
                    <div x-show="showDeleteButtons" x-cloak>
                        <button @click.stop="if(confirm('Remove this UPC item from your list?')) { $store.listManager.deleteItem({{ $listItem->id }}) }"
                            class="w-7 h-7 flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                            wire:key="delete-btn-{{ $listItem->id }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    @endif
                    @else
                    <!-- PLU Badge -->
                    @if($listItem && !$isUpcItem)
                    <a :href="$store.listManager.isItemOrganic({{ $listItem->id }}) ? '{{ route('plu.show', '9' . $pluCode->plu) }}' : '{{ route('plu.show', $pluCode->plu) }}'"
                        @click.stop
                        class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden hover:bg-green-200 transition-colors">
                        <span class="font-mono font-semibold"
                            x-text="$store.listManager.isItemOrganic({{ $listItem->id }}) ? '9{{ $pluCode->plu }}' : '{{ $pluCode->plu }}'">
                            {{ $listItem->organic ? '9' . $pluCode->plu : $pluCode->plu }}
                        </span>
                    </a>
                    @else
                    <a href="{{ route('plu.show', $pluCode->plu) }}"
                        @click.stop
                        class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden hover:bg-green-200 transition-colors">
                        <span class="font-mono font-semibold">
                            {{ $pluCode->plu }}
                        </span>
                    </a>
                    @endif
                    <div x-show="!showDeleteButtons"><x-consumer-usage-indicator :tier="$pluCode->consumer_usage_tier" /></div>
                    @if(!$readOnly && $hasActions && $onDelete && $listItem && isset($listItem->id) && $listItem->id)
                    <div x-show="showDeleteButtons" x-cloak>
                        <button @click.stop="if(confirm('Remove this ' + ($store.listManager.isItemOrganic({{ $listItem->id }}) ? 'organic' : 'regular') + ' item from your list?')) { $store.listManager.deleteItem({{ $listItem->id }}) }"
                            class="w-7 h-7 flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                            wire:key="delete-btn-{{ $listItem->id }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                    @endif
                    @endif
                </div>
                <div class="flex items-center p-1">
                    @if($isUpcItem)
                    @if($upcCode->has_image)
                    <img src="{{ asset('storage/upc_images/' . $upcCode->upc . '.jpg') }}" alt="{{ $upcCode->name }}"
                        class="w-12 h-12 object-cover rounded-lg">
                    @else
                    <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden">
                        <div class="w-full h-full flex items-center justify-center bg-gray-200">
                            <svg class="w-1/2 h-1/2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
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
                        @if($listItem && !$isUpcItem)
                        <span x-show="$store.listManager.isItemOrganic({{ $listItem->id }})" x-cloak
                            class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            Organic
                        </span>
                        @else
                        @if($listItem && $listItem->organic)
                        <span
                            class="inline-flex items-center ml-1 px-1.5 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            Organic
                        </span>
                        @endif
                        @endif
                        @endif
                    </span>
                    <div class="flex justify-between">
                        <span class="text-gray-500 capitalize inline-flex">
                            {{ ucwords(strtolower($commodity)) }}
                        </span>
                        <span class="text-gray-500">
                            @if($isUpcItem)
                            <span class="font-mono">{{ $upcCode->upc ?? '' }}</span>
                            @else
                            {{ $pluCode->size ?? '' }}
                            @endif
                            @if(!$showInventory)
                            <span class="pr-2"></span>
                            @endif
                        </span>
                    </div>
                </div>
                @if($showInventory)
                <!-- Inventory Level Component -->
                <div class="flex items-center p-1" x-show="!showDeleteButtons">
                    @if($listItem && !$onAdd && isset($listItem->id) && $listItem->id)
                    @if($readOnly)
                    <!-- Read-only inventory display -->
                    <div
                        class="flex items-center justify-center w-12 h-8 bg-gray-100 text-gray-700 font-semibold text-base rounded border"
                        x-text="($store.listManager.getInventory({{ $listItem->id }}) || 0) > 0 ? $store.listManager.getInventory({{ $listItem->id }}).toFixed(1) : '0'">
                    </div>
                    @else
                    <!-- Interactive inventory component (Alpine, no Livewire) -->
                    <div x-data="inventoryControl({{ $listItem->id }})" class="w-full">
                        <div class="flex flex-col items-center space-y-1">
                            <!-- Main controls row -->
                            <div class="flex items-center w-full justify-center">
                                <!-- Decrement button -->
                                <button @click.stop="decrement()"
                                        :disabled="value <= 0"
                                        class="relative w-8 h-8 flex items-center justify-center bg-red-500 text-white rounded-md
                                               hover:bg-red-600 active:scale-95 transition-all duration-100
                                               disabled:bg-gray-300 disabled:cursor-not-allowed touch-manipulation
                                               focus:outline-none focus:ring-1 focus:ring-red-400"
                                        aria-label="Decrease inventory">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path>
                                    </svg>
                                </button>

                                <!-- Value display -->
                                <div class="w-14 text-center relative">
                                    <input type="text"
                                           inputmode="decimal"
                                           x-model="editValue"
                                           x-show="isEditing"
                                           @focus="$el.select()"
                                           @keydown.enter.stop="saveEdit($el.value)"
                                           @keydown.escape.stop="cancelEdit()"
                                           @blur.stop="saveEdit($el.value)"
                                           @click.stop
                                           class="w-14 h-8 text-lg font-semibold text-center border border-blue-400 rounded-md
                                                  focus:outline-none focus:ring-1 focus:ring-blue-400
                                                  [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                           min="0">

                                    <div @click.stop="startEditing(); $nextTick(() => $el.previousElementSibling.focus())"
                                         x-show="!isEditing"
                                         class="w-14 h-8 text-lg font-semibold cursor-pointer hover:text-blue-600 transition-colors rounded-md hover:bg-blue-50 flex items-center justify-center"
                                         x-text="(parseFloat(value) || 0).toFixed(1)">
                                    </div>
                                </div>

                                <!-- Increment button -->
                                <button @click.stop="increment()"
                                        class="relative w-8 h-8 flex items-center justify-center bg-green-500 text-white rounded-md
                                               hover:bg-green-600 active:scale-95 transition-all duration-100
                                               touch-manipulation focus:outline-none focus:ring-1 focus:ring-green-400"
                                        aria-label="Increase inventory">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Quick action buttons -->
                            <div class="flex gap-1 w-full max-w-[180px]">
                                <button @click.stop="clear()"
                                        class="flex-1 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-md
                                               hover:bg-gray-200 active:scale-95 transition-all duration-100 touch-manipulation
                                               border border-gray-200">
                                    Clear
                                </button>

                                <button @click.stop="toggleHalf()"
                                        class="flex-1 px-2 py-1 text-xs font-medium rounded-md
                                               active:scale-95 transition-all duration-100 touch-manipulation
                                               border"
                                        :class="(parseFloat(value) || 0) % 1 === 0.5 ?
                                            'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' :
                                            'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'"
                                        x-text="(parseFloat(value) || 0) % 1 === 0.5 ? '-½' : '+½'">
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
                </div>
                <!-- Edit mode: Organic toggle (PLU only) -->
                @if(!$readOnly)
                <div class="flex items-center justify-center p-1" x-show="showDeleteButtons" x-cloak>
                    @if($listItem && !$onAdd && isset($listItem->id) && $listItem->id && !$isUpcItem)
                        <button @click.stop="$store.listManager.toggleOrganic({{ $listItem->id }})"
                            class="px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200 ease-in-out inline-flex items-center space-x-1 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="$store.listManager.isItemOrganic({{ $listItem->id }})
                                ? 'bg-green-500 hover:bg-green-600 text-white'
                                : 'bg-gray-200 hover:bg-gray-300 text-gray-500'"
                            :disabled="$store.listManager.hasDualVersion({{ $pluCode->id }})"
                            :title="$store.listManager.hasDualVersion({{ $pluCode->id }}) ? 'Both versions already exist — delete one first' : ($store.listManager.isItemOrganic({{ $listItem->id }}) ? 'Click to make conventional' : 'Click to make organic')">
                            <template x-if="$store.listManager.isItemOrganic({{ $listItem->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                            <template x-if="!$store.listManager.isItemOrganic({{ $listItem->id }})">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </template>
                            <span>Organic</span>
                        </button>
                    @endif
                </div>
                @endif
                @endif

                @if($hasActions)
                <div class="flex items-center">

                    @if($dispatchAdd && !$isUpcItem)
                    <div class="flex flex-col space-y-1 py-1"
                        x-data="{ addedRegular: false, addedOrganic: false }"
                        @item-added-to-list-from-modal.window="
                            if ($event.detail.pluCodeId == {{ $pluCode->id }}) {
                                if ($event.detail.organic) {
                                    addedOrganic = true;
                                    setTimeout(() => addedOrganic = false, 3000);
                                } else {
                                    addedRegular = true;
                                    setTimeout(() => addedRegular = false, 3000);
                                }
                            }
                        ">
                        <button @click.stop="$dispatch('open-add-to-list-modal', { pluCodeId: {{ $pluCode->id }}, organic: false })"
                            class="font-bold py-1 px-2 rounded text-xs transition-colors text-white"
                            :class="addedRegular ? 'bg-green-500' : 'bg-blue-500 hover:bg-blue-700'">
                            <span x-show="!addedRegular">Add</span>
                            <span x-show="addedRegular" x-cloak class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Added
                            </span>
                        </button>
                        <button @click.stop="$dispatch('open-add-to-list-modal', { pluCodeId: {{ $pluCode->id }}, organic: true })"
                            class="font-bold py-1 px-2 rounded text-xs transition-colors text-white"
                            :class="addedOrganic ? 'bg-green-500' : 'bg-green-600 hover:bg-green-700'">
                            <span x-show="!addedOrganic">Organic</span>
                            <span x-show="addedOrganic" x-cloak class="inline-flex items-center">
                                <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Added
                            </span>
                        </button>
                    </div>
                    @elseif($onAdd)
                    <div class="flex space-x-2">
                        @if($isUpcItem)
                        <button @click.stop="$dispatch('disable-add-buttons')"
                            wire:click.stop="{{ $onAdd }}({{ $upcCode->id }}, false, 'upc')"
                            x-data="{ disabled: false }" @disable-add-buttons.window="
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