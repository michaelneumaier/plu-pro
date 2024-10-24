<div>
    <input type="text" wire:model.live.debounce.500ms="searchTerm" placeholder="Search PLU Codes..."
        class="border p-2 rounded w-full mb-4" />

    @if($pluCodes->count())
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div
            class="grid grid-cols-[4rem,6rem,1fr,4rem,4rem] gap-2 bg-gray-50 text-gray-700 font-semibold text-xs sm:text-sm border-b border-gray-200">
            <div class="p-2 sm:p-3">PLU</div>
            <div class="p-2 sm:p-3 overflow-hidden text-ellipsis whitespace-nowrap">Commodity</div>
            <div class="p-2 sm:p-3">Variety</div>
            <div class="p-2 sm:p-3 overflow-hidden text-ellipsis whitespace-nowrap">Size</div>
            <div class="p-2 sm:p-3 overflow-hidden text-ellipsis whitespace-nowrap">AKA</div>
        </div>

        <!-- PLU Code Items -->
        @foreach($pluCodes as $pluCode)
        <div
            class="grid grid-cols-[4rem,6rem,1fr,4rem,4rem] gap-2 bg-white hover:bg-gray-50 border-b border-gray-200 last:border-b-0">
            <div class="flex items-center p-2 sm:p-3">
                <div
                    class="flex items-center justify-center w-10 h-7 sm:w-12 sm:h-8 bg-blue-100 text-blue-800 rounded overflow-hidden">
                    <span class="text-xs font-mono font-semibold">{{ $pluCode->plu }}</span>
                </div>
            </div>
            <div
                class="flex items-center p-2 sm:p-3 text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                {{ $pluCode->commodity }}
            </div>
            <div
                class="flex items-center p-2 sm:p-3 text-xs sm:text-sm md:text-base font-bold overflow-hidden text-ellipsis whitespace-nowrap">
                {{ $pluCode->variety }}
            </div>
            <div
                class="flex items-center p-2 sm:p-3 text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                {{ $pluCode->size }}
            </div>
            <div
                class="flex items-center p-2 sm:p-3 text-xs sm:text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                {{ $pluCode->aka }}
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $pluCodes->links() }}
    </div>
    @else
    <p class="mt-4">No PLU Codes found.</p>
    @endif
</div>