<div>
    <h1 class="text-2xl font-bold mb-4">{{ $userList->name }}</h1>

    <h2 class="text-xl font-semibold mb-2">List Items</h2>
    <x-plu-code-table :collection="$listItems" onDelete="removePLUCode" />

    <h2 class="text-xl font-semibold mt-6 mb-2">Add PLU Codes</h2>
    <input type="text" wire:model.live.debounce="searchTerm" placeholder="Search PLU Codes..."
        class="border p-1 w-full mb-4">

    <x-plu-code-table :collection="$availablePLUCodes" onAdd="addPLUCode" />

</div>