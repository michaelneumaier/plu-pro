<div
    class="flex flex-col md:flex-row md:items-end md:justify-between mb-1 space-y-4 md:space-y-0 bg-black bg-opacity-10 rounded-md p-2">
    <!-- Category Filter -->
    <div class="w-full p-1">
        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
        <select wire:model.live="selectedCategory" id="category"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Categories</option>
            @foreach($categories as $category)
            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
            @endforeach
        </select>
    </div>

    <!-- Commodity Filter -->
    <div class="w-full p-1">
        <label for="commodity" class="block text-sm font-medium text-gray-700">Commodity</label>
        <select wire:model.live="selectedCommodity" id="commodity"
            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Commodities</option>
            @foreach($commodities as $commodity)
            <option value="{{ $commodity }}">{{ ucfirst($commodity) }}</option>
            @endforeach
        </select>
    </div>

    <!-- Reset Filters Button -->
    <div class="p-1">
        <button wire:click="resetFilters" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Reset
        </button>
    </div>
</div>