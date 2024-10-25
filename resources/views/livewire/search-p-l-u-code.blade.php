<div class="w-full">
    <input type="text" wire:model.live.debounce.500ms="searchTerm" placeholder="Search PLU Codes..."
        class="border p-1 rounded w-full mb-4" />
    <x-plu-code-table :collection="$pluCodes" />
</div>