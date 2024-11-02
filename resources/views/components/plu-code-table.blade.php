<!-- resources/views/livewire/plu-code-table.blade.php -->
@php
// Determine the number of columns based on the presence of actions
$hasActions = $onDelete || $onAdd;
$colCount = $hasActions ? 5 : 4;
@endphp

<div class="w-full">
    @if($pluCodes->count())
    <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
        <!-- Header -->
        <div
            class="grid grid-cols-[3rem,1fr,7rem,auto,auto] bg-gray-50 text-gray-700 font-semibold text-sm border-b border-gray-200">
            <div class="p-1">PLU</div>
            <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">Variety</div>
            <!-- <div class="p-1 overflow-hidden text-ellipsis whitespace-nowrap">UPC</div>
            <div class="p-1">Inventory</div> -->
            @if($hasActions)
            <div class="p-1">Actions</div>
            @endif
        </div>

        <!-- PLU Code Items -->
        @foreach($pluCodes as $pluCode)
        <div class="grid grid-cols-[3rem,1fr,auto,auto] min-h-12 bg-white hover:bg-gray-50 cursor-pointer border-b border-gray-200 last:border-b-0"
            wire:click="$dispatch('pluCodeSelected', [{{ $pluCode->id }}])">
            <div class="flex items-center p-1">
                <div
                    class="flex items-center justify-center w-10 h-7 sm:w-12 sm:h-8 bg-green-100 text-sm text-green-800 border border-green-200 rounded overflow-hidden">
                    <span class="font-mono font-semibold">{{ $pluCode->plu }}</span>
                </div>
            </div>
            <div class="flex flex-col p-1 text-sm self-end overflow-hidden text-ellipsis whitespace-nowrap flex-grow">
                <span class="font-bold">{{ $pluCode->variety }}
                    @if(!empty($pluCode->aka))
                    <span class="text-gray-500"> - {{ $pluCode->aka }}</span>
                    @endif
                </span>
                <div class="flex justify-between ">
                    <span class="text-gray-500 capitalize inline-flex">
                        <div class="mr-1"><x-consumer-usage-indicator :tier="$pluCode->consumer_usage_tier" /></div>
                        {{ ucwords(strtolower($pluCode->commodity))}}
                    </span>
                    <span class="text-gray-500">{{ $pluCode->size }}</span>
                    <!-- Size displayed on the right -->
                </div>
            </div>
            <!-- Inventory Level Component -->
            <div class="flex items-center p-1">
                @if($pluCode->listItem)
                <livewire:inventory-level :listItem="$pluCode->listItem"
                    wire:key="inventory-level-{{ $pluCode->id }}" />
                @endif
            </div>

            @if($hasActions)
            <div class="flex items-center">
                @if($onDelete)
                <button onclick="if(!confirm('Are you sure you want to remove this PLU Code from your list?')) return;"
                    wire:click.stop="{{ $onDelete }}({{ $pluCode->id }})"
                    class="bg-red-500 hidden hover:bg-red-700 text-white font-bold py-1 px-2 rounded mr-2">
                    Delete
                </button>
                @endif
                @if($onAdd)
                <button wire:click.stop="{{ $onAdd }}({{ $pluCode->id }})"
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
    @if($pluCodes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
    <div class="mt-4">
        {{ $pluCodes->links() }}
    </div>
    @endif
    @else
    <p class="mt-4 p-4">No PLU Codes found.</p>
    @endif
</div>
