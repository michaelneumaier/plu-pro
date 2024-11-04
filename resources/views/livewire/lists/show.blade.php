<div>
    <h1 class="text-2xl font-bold mb-4">{{ $userList->name }}</h1>

    <h2 class="text-xl font-semibold mb-2">List Items</h2>
    <livewire:filter-section :categories="$categories" :commodities="$commodities" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" :key="'filter-section-' . $userList->id" />

    <div>
        <x-plu-code-table :collection="$listItems" :selectedCategory="$selectedCategory"
            :selectedCommodity="$selectedCommodity" :userListId="$userList->id" onDelete="removePLUCode" />
    </div>

    <div class="flex justify-end mt-2 space-x-1">
        <button x-data @click="$dispatch('toggle-delete-buttons')"
            class="px-4 py-2 bg-gray-600 text-sm text-white font-bold rounded-full hover:bg-gray-700 focus:outline-none">
            Edit Mode
        </button>
        <button x-data @click="$dispatch('carousel-open')"
            class="px-4 py-2 bg-blue-600 text-sm text-white font-bold rounded-full hover:bg-blue-700 focus:outline-none">
            Scan List
        </button>
    </div>

    <div wire:key="carousel-{{ $userList->id }}" x-data="{ show: false }" x-cloak x-show="show"
        @carousel-open.window="show = true" @carousel-close.window="show = false">
        @livewire('item-carousel', ['userListId' => $userList->id])
    </div>

    <div wire:key="search-section-{{ $userList->id }}">
        <h2 class="text-xl font-semibold mb-2">Add PLU Codes</h2>
        <input type="text" wire:model.live.debounce.300ms="searchTerm" placeholder="Search PLU Codes..."
            class="border p-1 w-full mb-4">
        <x-plu-code-table :collection="$pluCodes" :userListId="$userList->id" onAdd="addPLUCode" />
        @if($pluCodes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $pluCodes->links(data: ['scrollTo' => false]) }}
        </div>
        @endif
    </div>
</div>