<div class="min-h-screen max-w-4xl mx-auto bg-gray-100">
    <!-- Flash Messages -->
    @if (session()->has('message'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative">
        <div class="flex items-center justify-between">
            <span>{{ session('message') }}</span>
            <button @click="show = false" class="ml-2 text-green-500 hover:text-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-4 py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">👋 Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-sm text-gray-500 mt-1">Here's what's happening with your PLU lists</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="px-4 py-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="grid grid-cols-4 gap-2 text-center">
                <!-- Total Lists -->
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $userStats['total_lists'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Lists</p>
                </div>

                <!-- Total Items -->
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $userStats['total_items'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Items</p>
                </div>

                <!-- Published Lists -->
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ $userStats['published_lists'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Published</p>
                </div>

                <!-- Total Inventory -->
                <div>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($userStats['total_inventory'] ?? 0)
                        }}</p>
                    <p class="text-xs text-gray-500">Inventory</p>
                </div>
            </div>
        </div>

        <!-- My Lists Overview Widget -->
        <div class="mt-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">📋 My Lists</h3>
                        <a href="{{ route('lists.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            View All
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recentLists && $recentLists->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentLists as $list)
                        <a href="{{ route('lists.show', $list) }}"
                            class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0">
                                        @if($list->marketplace_enabled)
                                        <span class="text-lg">🏪</span>
                                        @elseif($list->is_public)
                                        <span class="text-lg">🔗</span>
                                        @else
                                        <span class="text-lg">📋</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $list->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $list->listItems->count() }} items</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($list->is_public)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Public
                                    </span>
                                    @endif
                                    @if($list->marketplace_enabled)
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Marketplace
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <div class="mx-auto h-12 w-12 text-gray-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No lists yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating your first PLU list.</p>
                    </div>
                    @endif

                    <!-- Create New List Button -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <button wire:click="toggleCreateModal"
                            class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg hover:bg-blue-600 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create New List
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Optional Analytics Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- PLU Usage Analytics -->
            @if(isset($pluInsights['category_breakdown']) && $pluInsights['category_breakdown']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">📊 Your PLU Insights</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Most Used PLU -->
                        @if(isset($pluInsights['most_used_plus']) && $pluInsights['most_used_plus']->count() > 0)
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">🥇 Most Used</p>
                            @php $topPLU = $pluInsights['most_used_plus']->first(); @endphp
                            <p class="text-sm text-gray-600">
                                {{ $topPLU->pluCode->variety ?? 'Unknown' }} ({{ $topPLU->pluCode->plu ?? 'N/A' }})
                                @if($topPLU->organic) - Organic @endif
                                <br>
                                <span class="text-xs text-gray-500">Used in {{ $topPLU->usage_count }} lists</span>
                            </p>
                        </div>
                        @endif

                        <!-- Top Category -->
                        @if(isset($pluInsights['category_breakdown']) && $pluInsights['category_breakdown']->count() >
                        0)
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">📈 Top Category</p>
                            @php
                            $topCategory = $pluInsights['category_breakdown']->first();
                            $totalItems = $pluInsights['total_items'] ?? 1;
                            $percentage = round(($topCategory->count / $totalItems) * 100, 1);
                            @endphp
                            <p class="text-sm text-gray-600">
                                {{ ucwords(strtolower($topCategory->commodity)) }} - {{ $percentage }}%
                                <br>
                                <span class="text-xs text-gray-500">{{ $topCategory->count }} of {{ $totalItems }}
                                    items</span>
                            </p>
                        </div>
                        @endif

                        <!-- Organic Ratio -->
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">🌱 Organic Ratio</p>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full"
                                        style="width: {{ $pluInsights['organic_ratio'] ?? 0 }}%"></div>
                                </div>
                                <span class="ml-2 text-sm text-gray-600">{{ $pluInsights['organic_ratio'] ?? 0
                                    }}%</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $pluInsights['organic_items'] ?? 0 }} of {{ $pluInsights['total_items'] ?? 0 }} items
                                are organic
                            </p>
                        </div>

                        <!-- This Week -->
                        @if(isset($pluInsights['weekly_activity']))
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">📅 This Week</p>
                            <p class="text-sm text-gray-600">
                                +{{ $pluInsights['weekly_activity']['items_added'] ?? 0 }} items added<br>
                                {{ $pluInsights['weekly_activity']['lists_updated'] ?? 0 }} lists updated
                            </p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Marketplace Insights -->
            @if(isset($marketplaceInsights['published_lists']) && $marketplaceInsights['published_lists']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">🏪 Marketplace Activity</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-2xl font-semibold text-blue-600">{{ $marketplaceInsights['total_views'] ?? 0
                                }}</p>
                            <p class="text-xs text-gray-500">Total Views</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-semibold text-green-600">{{ $marketplaceInsights['total_copies'] ??
                                0 }}</p>
                            <p class="text-xs text-gray-500">Total Copies</p>
                        </div>
                    </div>

                    @if(isset($marketplaceInsights['top_performing']) && $marketplaceInsights['top_performing']->count()
                    > 0)
                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">🔥 Top Performing</p>
                        <div class="space-y-2">
                            @foreach($marketplaceInsights['top_performing'] as $list)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-900 truncate">{{ $list->marketplace_title ?? $list->name
                                    }}</span>
                                <div class="flex space-x-2 text-gray-500">
                                    <span>👁️ {{ $list->view_count }}</span>
                                    <span>📥 {{ $list->copy_count }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Create List Modal -->
    <div x-show="$wire.showCreateModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-init="$watch('$wire.showCreateModal', (value) => {
            if (value) {
                setTimeout(() => $refs.listNameInput?.focus(), 100);
            }
        })" <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.toggleCreateModal()"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showCreateModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Create New List</h3>
                        <button @click="$wire.toggleCreateModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form wire:submit.prevent="createList">
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label for="newListName" class="block text-sm font-medium text-gray-700 mb-2">List
                                    Name</label>
                                <input type="text" wire:model="newListName" id="newListName"
                                    placeholder="Enter list name..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    x-ref="listNameInput">
                                @error('newListName')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="$wire.toggleCreateModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Create List
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>