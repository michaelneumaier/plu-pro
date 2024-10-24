<div>
    <h1 class="text-2xl font-bold mb-4">{{ $userList->name }}</h1>

    <h2 class="text-xl font-semibold mb-2">List Items</h2>
    @if($listItems->count())
    <div class="mt-4">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
            <!-- Header -->
            <div
                class="grid grid-cols-[3rem,5rem,1fr,4rem,4rem,1rem] bg-gray-50 text-gray-700 font-semibold text-xs sm:text-sm border-b border-gray-200 overflow-x-auto">
                <div class="p-1 ">PLU</div>
                <div class="p-1  overflow-hidden text-ellipsis whitespace-nowrap">Commodity</div>
                <div class="p-1 ">Variety</div>
                <div class="p-1  overflow-hidden text-ellipsis whitespace-nowrap">Size</div>
                <div class="p-1  overflow-hidden text-ellipsis whitespace-nowrap">AKA</div>
                <div class="p-1 "></div>
            </div>

            <!-- List Items -->
            @foreach ($listItems as $item)
            <div
                class="grid grid-cols-[3rem,5rem,1fr,4rem,4rem,1rem] bg-white hover:bg-gray-50 border-b border-gray-200 last:border-b-0 overflow-x-auto">
                <div class="flex items-center p-1">
                    <div
                        class="flex items-center justify-center w-10 h-7 sm:w-12 sm:h-8 bg-blue-100 text-blue-800 rounded overflow-hidden">
                        <span class="text-xs font-mono font-semibold">{{ $item->pluCode->plu }}</span>
                    </div>
                </div>
                <div class="flex items-center p-1  text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                    {{ $item->pluCode->commodity }}
                </div>
                <div
                    class="flex items-center p-1  text-xs sm:text-sm md:text-base font-bold overflow-hidden text-ellipsis whitespace-nowrap">
                    {{ $item->pluCode->variety }}
                </div>
                <div class="flex items-center p-1  text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                    {{ $item->pluCode->size }}
                </div>
                <div class="flex items-center p-1  text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                    {{ $item->pluCode->aka }}
                </div>
                <div class="flex items-center justify-center p-1 ">
                    <button wire:click="removePLUCode({{ $item->id }})" class="text-red-500 hover:text-red-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    @else
    <p>No items in this list yet.</p>
    @endif

    <h2 class="text-xl font-semibold mt-6 mb-2">Add PLU Codes</h2>
    <input type="text" wire:model.live.debounce="searchTerm" placeholder="Search PLU Codes..."
        class="border p-1 w-full mb-4">

    @if($availablePLUCodes->count())
    @foreach($availablePLUCodes as $pluCode)
    <div
        class="grid grid-cols-[3rem,5rem,1fr,4rem,4rem,1rem] bg-white hover:bg-gray-50 border-b border-gray-200 last:border-b-0 overflow-x-auto">
        <div class="flex items-center p-1">
            <div
                class="flex items-center justify-center w-10 h-7 sm:w-12 sm:h-8 bg-blue-100 text-blue-800 rounded overflow-hidden">
                <span class="text-xs font-mono font-semibold">{{ $pluCode->plu }}</span>
            </div>
        </div>
        <div class="flex items-center p-1  text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
            {{ $pluCode->commodity }}
        </div>
        <div
            class="flex items-center p-1  text-xs sm:text-sm md:text-base font-bold overflow-hidden text-ellipsis whitespace-nowrap">
            {{ $pluCode->variety }}
        </div>
        <div class="flex items-center p-1  text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
            {{ $pluCode->size }}
        </div>
        <div class="flex items-center p-1  text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
            {{ $pluCode->aka }}
        </div>
        <div class="flex items-center justify-center p-1 ">
            <button wire:click="addPLUCode({{ $pluCode->id }})" class="text-green-500">+</button>
        </div>
    </div>
    @endforeach
    @else
    @if($searchTerm)
    <p>No PLU Codes found.</p>
    @endif
    @endif
</div>