<!-- resources/views/livewire/plu-code-table.blade.php -->
@php
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
        <div
            class="grid grid-cols-[3.5rem,3rem,1fr,7rem,auto,auto] bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
            <div class="p-1">PLU</div>
            <div class="p-1">Image</div>
            <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
            <!-- <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">UPC</div>
            <div class="p-1">Inventory</div> -->
            @if($hasActions)
            <div class="p-1">Actions</div>
            @endif
        </div>

        <!-- PLU Code Items -->
        @foreach($pluCodes as $pluCode)
        <div
            class="{{ $pluCode->listItem && $pluCode->listItem->organic ? 'bg-green-50 hover:bg-green-100' : 'bg-white hover:bg-gray-50' }} cursor-pointer border-b border-gray-200 last:border-b-0">
            <div class="grid grid-cols-[3.5rem,3rem,1fr,auto,auto] min-h-16 "
                wire:click="$dispatch('pluCodeSelected', [{{ $pluCode->id }}])"
                wire:key="plu-row-{{ $pluCode->id }}-{{ $userListId }}"
                data-plu-id="{{ $pluCode->id }}">
                <div class="flex flex-col items-center justify-evenly">
                    <div
                        class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden">
                        <span class="font-mono font-semibold">
                            @if($pluCode->listItem && $pluCode->listItem->organic)
                            9{{ $pluCode->plu }}
                            @else
                            {{ $pluCode->plu }}
                            @endif
                        </span>
                    </div>
                    <div class="mr-1"><x-consumer-usage-indicator :tier="$pluCode->consumer_usage_tier" /></div>
                </div>
                <div class="flex items-center p-1">
                    <x-plu-image :plu="$pluCode->plu" size="sm" />
                </div>
                <div
                    class="flex flex-col py-1 text-sm justify-between overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
                    <div></div>
                    <span class="font-bold">{{ $pluCode->variety }}
                        @if(!empty($pluCode->aka))
                        <span class="text-gray-500"> - {{ $pluCode->aka }}</span>
                        @endif
                    </span>
                    <div class="flex justify-between">
                        <span class="text-gray-500 capitalize inline-flex">
                            
                            {{ ucwords(strtolower($pluCode->commodity))}}
                        </span>
                        <span class="text-gray-500">{{ $pluCode->size }}</span>
                        <!-- Size displayed on the right -->
                    </div>
                </div>
                <!-- Inventory Level Component -->
                <div class="flex items-center p-1">
                    @if($pluCode->listItem && !$onAdd)
                    <livewire:inventory-level :listItemId="$pluCode->listItem->id" :userListId="$userListId"
                        :wire:key="'inv-level-' . $pluCode->listItem->id" />
                    @endif
                </div>

                @if($hasActions)
                <div class="flex items-center">

                    @if($onAdd)
                    <div class="flex space-x-2">
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
                    </div>
                    @endif
                </div>
                @endif
            </div>
            <div class="flex justify-between p-1 space-x-2 px-10 md:px-1" x-show=" showDeleteButtons" x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-90">
                @if($pluCode->listItem && !$onAdd)
                <livewire:organic-toggle :list-item="$pluCode->listItem"
                    :wire:key="'organic-toggle-stable-'.$pluCode->listItem->id" />
                @endif
                @if($hasActions && $onDelete)
                <button x-show="showDeleteButtons" x-cloak
                    @click.stop="$event.preventDefault(); if(confirm('Are you sure you want to remove this PLU Code from your list?')) { $wire.{{ $onDelete }}({{ $pluCode->id }}) }"
                    wire:key="delete-button-{{ $pluCode->id }}-{{ now() }}"
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
        </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    @if($pluCodes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="mt-4">
        {{ $pluCodes->links() }}
    </div>
    @endif
    @else
    <p class="mt-4 p-4">No PLU Codes found.</p>
    @endif
</div>