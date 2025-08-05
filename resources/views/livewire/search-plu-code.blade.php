<div class="md:p-2 max-w-4xl mx-auto">
    <!-- Search and Filters Button -->
    <div class="flex flex-col md:flex-row md:items-end md:justify-between p-2 space-y-4 md:space-y-0">
        <!-- Search Input -->
        <div class="w-full">
            <label for="search" class="block text-sm font-medium text-gray-700">Search PLU Codes & UPC Codes</label>
            <div class="flex items-center mt-1">
                <input type="text" wire:model.live.debounce.500ms="searchTerm" id="search"
                    placeholder="Search PLU codes, UPC codes (12-13 digits), variety, commodity..."
                    class="flex-grow border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                <button wire:click="toggleFilters"
                    class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Combined Sort Dropdown -->
    <div class="flex items-center p-2">
        <label for="sortOption" class="mr-2 text-sm font-medium text-gray-700">Sort By:</label>
        <select wire:model.live="sortOption" id="sortOption"
            class="border border-gray-300 rounded-md shadow-sm p-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            <option value="plu_asc">PLU Ascending</option>
            <option value="plu_desc">PLU Descending</option>
            <option value="consumer_usage_tier_asc">Consumer Usage Ascending</option>
            <option value="consumer_usage_tier_desc">Consumer Usage Descending</option>
            <option value="created_at_asc">Date Created Ascending</option>
            <option value="created_at_desc">Date Created Descending</option>
        </select>
        @if($sortOption)
        <span class="ml-2 text-xs">
            @php
            $parts = explode('_', $sortOption);
            $direction = strtoupper($parts[1] ?? 'ASC');
            @endphp
            @if($direction === 'ASC')
            &uarr;
            @else
            &darr;
            @endif
        </span>
        @endif
    </div>


    <!-- Filters Section (Conditionally Rendered) -->
    @if($showFilters)
    <livewire:filter-section :categories="$categories" :commodities="$commodities" :selectedCategory="$selectedCategory"
        :selectedCommodity="$selectedCommodity" />
    @endif

    <!-- UPC Results Section -->
    @if(count($upcResults) > 0 || $upcLookupInProgress || $upcError)
    <div class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">UPC Results</h3>

        @if($upcLookupInProgress)
        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex items-center">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-blue-700">Looking up UPC code...</span>
            </div>
        </div>
        @endif

        @if($upcError)
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex items-center">
                <svg class="flex-shrink-0 h-5 w-5 text-red-400 mr-3" xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <span class="text-red-700">{{ $upcError }}</span>
            </div>
        </div>
        @endif

        @foreach($upcResults as $upcCode)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 mb-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- UPC Badge -->
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        UPC
                    </span>

                    <!-- Product Image -->
                    @if($upcCode->has_image)
                    <img src="{{ asset('storage/upc_images/' . $upcCode->upc . '.jpg') }}" alt="{{ $upcCode->name }}"
                        class="w-16 h-16 object-cover rounded-md">
                    @else
                    <div class="w-16 h-16 bg-gray-100 rounded-md flex items-center justify-center">
                        <span class="text-gray-400 text-xs">No Image</span>
                    </div>
                    @endif

                    <!-- Product Details -->
                    <div class="flex-1">
                        <h4 class="text-lg font-medium text-gray-900">{{ $upcCode->name }}</h4>
                        <p class="text-sm text-gray-600">
                            UPC: <span class="font-mono">{{ $upcCode->upc }}</span>
                            @if($upcCode->brand) • {{ $upcCode->brand }} @endif
                        </p>
                        @if($upcCode->description)
                        <p class="text-sm text-gray-500 mt-1">{{ Str::limit($upcCode->description, 100) }}</p>
                        @endif
                        @if($upcCode->category && $upcCode->commodity)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $upcCode->category }} • {{ $upcCode->commodity }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- View Details Button -->
                <button wire:click="viewUPCDetails({{ $upcCode->id }})"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    View Details
                </button>
            </div>
        </div>
        @endforeach
    </div>
    @endif


    <!-- PLU Codes Table -->
    <div>
        @if($pluCodes->count())
        <div id="plu-code-table">
            @if(count($upcResults) > 0 || $upcLookupInProgress || $upcError)
            <h3 class="text-lg font-semibold text-gray-900 mb-4">PLU Results</h3>
            @endif
            <x-plu-code-table :collection="$pluCodes" :showCommodityGroups="false" :showInventory="false"
                :showPagination="false" />
        </div>
        @if($pluCodes instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $pluCodes->links(data: ['scrollTo' => '#plu-code-table']) }}
        </div>
        @endif
        @elseif(count($upcResults) == 0 && !$upcLookupInProgress)
        <p class="mt-4 p-4 bg-red-100 text-red-700 rounded">No PLU Codes found.</p>
        @endif
    </div>

    <!-- UPC Details Modal -->
    @if($showUpcModal && $selectedUpcCode)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" x-data>
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Product Details</h3>
                    <button wire:click="closeUpcModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Product Image -->
                @if($selectedUpcCode->has_image)
                <div class="mb-4 text-center">
                    <img src="{{ asset('storage/upc_images/' . $selectedUpcCode->upc . '.jpg') }}"
                        alt="{{ $selectedUpcCode->name }}" class="w-32 h-32 object-cover rounded-md mx-auto">
                </div>
                @endif

                <!-- Product Info -->
                <div class="space-y-3">
                    <div>
                        <h4 class="font-medium text-gray-900">{{ $selectedUpcCode->name }}</h4>
                    </div>

                    <div>
                        <span class="text-sm text-gray-600">UPC:</span>
                        <span class="text-sm font-mono">{{ $selectedUpcCode->upc }}</span>
                    </div>

                    @if($selectedUpcCode->brand)
                    <div>
                        <span class="text-sm text-gray-600">Brand:</span>
                        <span class="text-sm">{{ $selectedUpcCode->brand }}</span>
                    </div>
                    @endif

                    @if($selectedUpcCode->description)
                    <div>
                        <span class="text-sm text-gray-600">Description:</span>
                        <p class="text-sm mt-1">{{ $selectedUpcCode->description }}</p>
                    </div>
                    @endif

                    @if($selectedUpcCode->category || $selectedUpcCode->commodity)
                    <div>
                        <span class="text-sm text-gray-600">Category:</span>
                        <span class="text-sm">{{ $selectedUpcCode->category }} • {{ $selectedUpcCode->commodity
                            }}</span>
                    </div>
                    @endif

                    <!-- Barcode -->
                    <div class="text-center mt-4">
                        <x-barcode :code="$selectedUpcCode->upc" />
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end mt-6">
                    <button wire:click="closeUpcModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        let pollingInterval;

        // Listen for polling start event
        Livewire.on('start-upc-polling', () => {
            pollingInterval = setInterval(() => {
                @this.checkUPCResults();
            }, 2000); // Check every 2 seconds
        });

        // Listen for polling stop event
        Livewire.on('stop-upc-polling', () => {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        });
    });
</script>
</div>