<div>
    <h1 class="text-2xl font-bold mb-4">{{ $userList->name }}</h1>

    <h2 class="text-xl font-semibold mb-2">List Items</h2>
    <livewire:filter-section :categories="$categories" :commodities="$commodities" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" />
    <x-plu-code-table :collection="$listItems" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" :userListId="$userList->id" onDelete="removePLUCode" />
    <button x-data @click="$dispatch('carousel-open')"
        class="px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 focus:outline-none">
        Scan Items
    </button>
    @livewire('item-carousel', ['userListId' => $userList->id])
    @foreach($categories as $category)
    {{$category}}
    @endforeach
    <h2 class="text-xl font-semibold mt-6 mb-2">Add PLU Codes</h2>
    <input type="text" wire:model.live.debounce.300ms="searchTerm" placeholder="Search PLU Codes..."
        class="border p-1 w-full mb-4">

    <x-plu-code-table :collection="$availablePLUCodes" :userListId="$userList->id" onAdd="addPLUCode" />

</div>