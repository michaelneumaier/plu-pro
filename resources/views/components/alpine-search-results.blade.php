@props(['pluCodes', 'userListId'])

<div class="w-full">
    @if($pluCodes->count())
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div class="grid grid-cols-[3.5rem,3rem,1fr,7rem,auto,auto] bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
            <div class="p-1">PLU</div>
            <div class="p-1">Image</div>
            <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
            <div class="p-1">Actions</div>
        </div>

        <!-- PLU Code Items -->
        @foreach($pluCodes as $pluCode)
        <div class="{{ $pluCode->listItem && $pluCode->listItem->organic ? 'bg-green-50 hover:bg-green-100' : 'bg-white hover:bg-gray-50' }} cursor-pointer border-b border-gray-200 last:border-b-0"
            x-data="{ 
                isAdding: false,
                alreadyInList: {{ $pluCode->listItem ? 'true' : 'false' }}
            }">
            <div class="grid grid-cols-[3.5rem,3rem,1fr,auto,auto] min-h-16"
                wire:click="$dispatch('pluCodeSelected', [{{ $pluCode->id }}])"
                wire:key="search-plu-row-{{ $pluCode->id }}-{{ $userListId }}">
                <div class="flex flex-col items-center justify-evenly">
                    <div class="flex items-center justify-center w-12 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden">
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
                <div class="flex flex-col py-1 text-sm justify-between overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
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
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex space-x-2">
                        <template x-if="!alreadyInList && !isAdding">
                            <button @click.stop="
                                isAdding = true;
                                const pluData = @js($pluCode->toArray());
                                const success = $store.listManager.addItem(pluData, {{ $userListId }});
                                if (success) {
                                    alreadyInList = true;
                                }
                                isAdding = false;
                            "
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-sm transition-colors">
                                Add
                            </button>
                        </template>
                        <template x-if="!alreadyInList && !isAdding">
                            <button @click.stop="
                                isAdding = true;
                                const pluData = @js($pluCode->toArray());
                                pluData.organic = true;
                                const success = $store.listManager.addItem(pluData, {{ $userListId }});
                                if (success) {
                                    alreadyInList = true;
                                }
                                isAdding = false;
                            "
                                class="bg-green-600 hover:bg-green-800 text-white font-bold py-1 px-2 rounded text-sm transition-colors">
                                Add as Organic
                            </button>
                        </template>
                        <template x-if="alreadyInList">
                            <span class="text-green-600 font-medium text-sm flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                In List
                            </span>
                        </template>
                        <template x-if="isAdding">
                            <span class="text-gray-500 text-sm flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Adding...
                            </span>
                        </template>
                    </div>
                </div>
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