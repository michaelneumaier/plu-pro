<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Marketplace</h1>
                    <p class="mt-1 text-sm text-gray-600">Discover and copy community-shared produce lists</p>
                </div>
                <button wire:click="toggleViewMode" 
                    class="p-2 text-gray-600 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    @if($viewMode === 'grid')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" id="search"
                        placeholder="Search lists..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select wire:model.live="category" id="category"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort -->
                <div>
                    <label for="sortBy" class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                    <select wire:model.live="sortBy" id="sortBy"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="newest">Newest</option>
                        <option value="popular">Most Popular</option>
                        <option value="views">Most Viewed</option>
                        <option value="alphabetical">Alphabetical</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @if($lists->count() > 0)
            @if($viewMode === 'grid')
                <!-- Grid View -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($lists as $list)
                        <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            {{ $list->marketplace_title }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            by {{ $list->user->name }}
                                        </p>
                                    </div>
                                    @if($list->marketplace_category)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                            $list->marketplace_category === 'organic' ? 'bg-green-100 text-green-800' :
                                            ($list->marketplace_category === 'seasonal' ? 'bg-orange-100 text-orange-800' :
                                            ($list->marketplace_category === 'grocery-retail' ? 'bg-purple-100 text-purple-800' :
                                            'bg-gray-100 text-gray-800'))
                                        }}">
                                            {{ $categories[$list->marketplace_category] ?? 'Other' }}
                                        </span>
                                    @endif
                                </div>

                                @if($list->marketplace_description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                        {{ $list->marketplace_description }}
                                    </p>
                                @endif

                                <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                    <span>{{ $list->listItems->count() }} items</span>
                                    <div class="flex items-center space-x-3">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            {{ $list->view_count }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                            </svg>
                                            {{ $list->copy_count }}
                                        </span>
                                    </div>
                                </div>

                                <a href="{{ route('marketplace.view', $list->share_code) }}" 
                                   class="block w-full text-center bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition">
                                    View List
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- List View -->
                <div class="space-y-4">
                    @foreach($lists as $list)
                        <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $list->marketplace_title }}
                                            </h3>
                                            @if($list->marketplace_category)
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                                    $list->marketplace_category === 'organic' ? 'bg-green-100 text-green-800' :
                                                    ($list->marketplace_category === 'seasonal' ? 'bg-orange-100 text-orange-800' :
                                                    ($list->marketplace_category === 'grocery-retail' ? 'bg-purple-100 text-purple-800' :
                                                    'bg-gray-100 text-gray-800'))
                                                }}">
                                                    {{ $categories[$list->marketplace_category] ?? 'Other' }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 mb-2">
                                            by {{ $list->user->name }} • Published {{ $list->published_at->diffForHumans() }}
                                        </p>
                                        @if($list->marketplace_description)
                                            <p class="text-sm text-gray-600 mb-3">
                                                {{ $list->marketplace_description }}
                                            </p>
                                        @endif
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span>{{ $list->listItems->count() }} items</span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                {{ $list->view_count }} views
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                                </svg>
                                                {{ $list->copy_count }} copies
                                            </span>
                                        </div>
                                    </div>
                                    <a href="{{ route('marketplace.view', $list->share_code) }}" 
                                       class="ml-4 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition whitespace-nowrap">
                                        View List
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Pagination -->
            <div class="mt-6">
                {{ $lists->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No lists found</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($search || $category)
                        Try adjusting your filters or search terms.
                    @else
                        Be the first to share a list to the marketplace!
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>