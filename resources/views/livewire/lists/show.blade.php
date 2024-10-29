<div>
    <h1 class="text-2xl font-bold mb-4">{{ $userList->name }}</h1>

    <h2 class="text-xl font-semibold mb-2">List Items</h2>
    <livewire:filter-section :categories="$categories" :commodities="$commodities" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" />
    <x-plu-code-table :collection="$listItems" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" onDelete="removePLUCode" />
    @foreach($categories as $category)
    {{$category}}
    @endforeach
    <h2 class="text-xl font-semibold mt-6 mb-2">Add PLU Codes</h2>
    <input type="text" wire:model.live.debounce="searchTerm" placeholder="Search PLU Codes..."
        class="border p-1 w-full mb-4">

    <x-plu-code-table :collection="$availablePLUCodes" onAdd="addPLUCode" />

</div>