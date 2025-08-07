<div class="flex flex-col md:flex-row mb-1 space-y-2 md:space-y-0 bg-black bg-opacity-10 rounded-md p-1">
    <div class="flex flex-row w-full space-x-1 md:space-x-2 flex-grow">
        <!-- Category Filter -->
        <div class="flex-1 md:p-1">
            <label for="category" class="block text-sm font-medium text-gray-700 hidden">Category</label>
            <select wire:model.live="selectedCategory" id="category"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Commodity Filter -->
        <div class="flex-1 md:p-1">
            <label for="commodity" class="block text-sm font-medium text-gray-700 hidden">Commodity</label>
            <select wire:model.live="selectedCommodity" id="commodity"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-1 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Commodities</option>
                @foreach($commodities as $commodity)
                <option value="{{ $commodity }}">{{ ucfirst($commodity) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Reset Filters Button -->
        <div class="flex-shrink-0 md:p-1 flex items-end">
            <button wire:click="resetFilters" class="bg-gray-500 hover:bg-gray-700 text-white py-1 px-2 rounded">
                Reset
            </button>
        </div>
    </div>
</div>