<div class="w-full">
    <!-- Search and Filters Button -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between mb-1 space-y-4 md:space-y-0">
        <!-- Search Input -->
        <div class="w-full">
            <label for="search" class="block text-sm font-medium text-gray-700">Search PLU Codes</label>
            <div class="flex items-center mt-1">
                <input type="text" wire:model.debounce.500ms="searchTerm" id="search" placeholder="Search PLU Codes..."
                    class="flex-grow border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <button wire:click="toggleFilters"
                    class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section (Conditionally Rendered) -->
    @if($showFilters)
    <livewire:filter-section :categories="$categories" :commodities="$commodities" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" />
    @endif

    <!-- PLU Codes Table -->
    <div>
        @if($pluCodes->count())
        <x-plu-code-table :collection="$pluCodes" />
        @else
        <p class="mt-4 p-4 bg-red-100 text-red-700 rounded">No PLU Codes found.</p>
        @endif
    </div>
</div>