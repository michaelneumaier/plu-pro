<!-- resources/views/livewire/plu-code-table.blade.php -->
<div class="w-full">
    <!-- Filters Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center">
            <!-- Commodity Filter -->
            <div class="mr-4 mb-2 sm:mb-0">
                <label for="commodity" class="block text-sm font-medium text-gray-700">Commodity</label>
                <select wire:model="selectedCommodity" id="commodity"
                    class="mt-1 block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">All Commodities</option>
                    @foreach($commodities as $commodity)
                    <option value="{{ $commodity }}">{{ ucfirst($commodity) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Type Filter -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select wire:model="selectedType" id="type"
                    class="mt-1 block w-full sm:w-48 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- PLU Codes Table -->
    @if($pluCodes->count())
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div
            class="grid grid-cols-3 sm:grid-cols-4 bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
            <div class="p-1">PLU</div>
            <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
            <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">UPC</div>
            @if($onDelete || $onAdd)
            <div class="p-2">Actions</div>
            @endif
        </div>

        <!-- PLU Code Items -->
        @foreach($pluCodes as $pluCode)
        <div class="grid grid-cols-3 sm:grid-cols-4 bg-white hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0"
            wire:click="$dispatch('pluCodeSelected', {{ $pluCode->id }})">
            <div class="flex items-center p-1">
                <div
                    class="flex items-center justify-center w-10 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 rounded overflow-hidden">
                    <span class="font-mono font-semibold">{{ $pluCode->plu }}</span>
                </div>
            </div>
            <div class="flex flex-col p-1 text-sm overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
                <span class="font-bold">{{ $pluCode->variety }}
                    @if(!empty($pluCode->aka))
                    <span class="text-gray-500"> - {{ $pluCode->aka }}</span>
                    @endif
                </span>
                <div class="flex justify-between">
                    <span class="text-gray-500 capitalize">{{ ucwords(strtolower($pluCode->commodity)) }}</span>
                    <span class="text-gray-500">{{ $pluCode->size }}</span> <!-- Size displayed on the right -->
                </div>
            </div>
            <div class="flex items-center p-1 text-sm overflow-hidden text-ellipsis whitespace-nowrap">
                <x-barcode code="{{ $pluCode->plu }}" />
            </div>
            @if($onDelete || $onAdd)
            <div class="flex items-center">
                @if($onDelete)
                <button onclick="if(!confirm('Are you sure you want to remove this PLU Code from your list?')) return;"
                    wire:click="{{ $onDelete }}({{ $pluCode->id }})"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded mr-2">
                    Delete
                </button>
                @endif
                @if($onAdd)
                <button wire:click="{{ $onAdd }}({{ $pluCode->id }})"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">
                    Add
                </button>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Pagination Links -->
    <div class="mt-4">
        {{ $pluCodes->links() }}
    </div>
    @else
    <p class="mt-4 p-4">No PLU Codes found.</p>
    @endif
</div>