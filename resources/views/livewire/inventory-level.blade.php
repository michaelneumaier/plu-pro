<div class="flex items-center space-x-0.5">
    <!-- Custom ±0.5 Button -->
    @php
    $isHalf = ($listItem->inventory_level - intval($listItem->inventory_level)) >= 0.5;
    @endphp

    <button wire:click.stop="{{ $isHalf ? 'subtractHalf' : 'addHalf' }}"
        class="w-6 p-0 bg-blue-500 text-sm text-white rounded hover:bg-blue-700 focus:outline-none"
        aria-label="{{ $isHalf ? 'Subtract 0.5 from Inventory' : 'Add 0.5 to Inventory' }}">
        {{ $isHalf ? '-.5' : '+.5' }}
    </button>

    <!-- Decrement Button (-) -->
    <button wire:click.stop="decrement"
        class="w-7 h-7 flex items-center justify-center bg-red-500 text-white rounded-full hover:bg-red-700 focus:outline-none"
        aria-label="Decrement Inventory">
        -
    </button>

    <!-- Inventory Level Display/Edit -->
    <div class="relative">
        @if($isEditing)
        <input type="text" wire:model.defer="editableValue" wire:blur="saveEdit" x-init="$el.focus(); $el.select()"
            class="w-8 p-0 m-0 text-md font-semibold text-center border-0 focus:outline-none" @click.stop
            aria-label="Edit Inventory Level">
        @error('editableValue')
        <div class="absolute top-full mt-0.5 left-0 text-xs text-red-500">
            {{ $message }}
        </div>
        @enderror
        @else
        <span class="w-8 text-md font-semibold cursor-pointer hover:text-blue-600 block text-center"
            wire:click.stop="startEditing" title="Click to edit inventory level">
            {{ number_format($listItem->inventory_level, 1) }}
        </span>
        @endif
    </div>

    <!-- Increment Button (+) -->
    <button wire:click.stop="increment"
        class="w-7 h-7 flex items-center justify-center bg-green-500 text-white rounded-full hover:bg-green-700 focus:outline-none"
        aria-label="Increment Inventory">
        +
    </button>
</div>