<div class="min-h-screen max-w-4xl mx-auto bg-gray-50">
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
                <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
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

        <!-- My Lists Widget -->
        <div class="mt-6"
            x-data="{}"
            x-init="
                let stored = window.getDefaultListId ? window.getDefaultListId() : null;
                if (stored) $wire.setDefaultList(parseInt(stored));
            "
            x-on:default-list-changed.window="
                if ($event.detail.listId) {
                    $wire.setDefaultList(parseInt($event.detail.listId));
                } else {
                    $wire.clearDefaultList();
                }
            ">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">My Lists</h3>
                        <button wire:click="toggleCreateModal"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                            New List
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    @if($lists->count() > 0)
                    <div class="space-y-3">
                        @foreach($lists as $list)
                        <div x-data="{ menuOpen: false }"
                            class="relative p-3 rounded-lg transition-colors {{ $defaultListId == $list->id ? 'bg-emerald-50 border border-emerald-200' : 'bg-gray-50 hover:bg-gray-100' }}">
                            <!-- Main clickable area -->
                            <a href="{{ route('lists.show', $list) }}" class="block pr-8">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $list->name }}</p>
                                <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-1">
                                    <span class="text-xs text-gray-500">{{ $list->list_items_count }} items</span>
                                    @if($list->is_public)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-700">Public</span>
                                    @endif
                                    @if($list->marketplace_enabled)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-700">Marketplace</span>
                                    @endif
                                </div>
                                @if($defaultListId == $list->id)
                                <p class="text-[11px] text-emerald-600 mt-1">Opens on app launch</p>
                                @endif
                            </a>

                            <!-- Kebab menu button -->
                            <button @click.prevent="menuOpen = !menuOpen"
                                class="absolute top-3 right-3 p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                </svg>
                            </button>

                            <!-- Dropdown menu -->
                            <div x-show="menuOpen" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                @click.outside="menuOpen = false"
                                class="absolute right-3 top-10 z-10 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
                                style="display: none;">

                                <!-- Set as App Home (PWA only) -->
                                <template x-if="$store.offlineMode && $store.offlineMode.isPWA">
                                    <button @click="
                                        menuOpen = false;
                                        if ({{ $defaultListId == $list->id ? 'true' : 'false' }}) {
                                            window.clearDefaultList();
                                        } else {
                                            window.setAsDefaultList({{ $list->id }});
                                        }
                                    " class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                        </svg>
                                        {{ $defaultListId == $list->id ? 'Remove as App Home' : 'Set as App Home' }}
                                    </button>
                                </template>

                                <!-- Share -->
                                <button @click="menuOpen = false; $wire.toggleShareModal({{ $list->id }})"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                                        </path>
                                    </svg>
                                    Share
                                </button>

                                <!-- Rename -->
                                <button @click="menuOpen = false; $wire.openRenameModal({{ $list->id }})"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                    Rename
                                </button>

                                <div class="border-t border-gray-100 my-1"></div>

                                <!-- Delete -->
                                <button @click="menuOpen = false; $wire.confirmDelete({{ $list->id }})"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($lists->hasPages())
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        {{ $lists->links() }}
                    </div>
                    @endif
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
                </div>
            </div>
        </div>

        <!-- Optional Analytics Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- PLU Usage Analytics -->
            @if(isset($pluInsights['category_breakdown']) && $pluInsights['category_breakdown']->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Your PLU Insights</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Most Used PLU -->
                        @if(isset($pluInsights['most_used_plus']) && $pluInsights['most_used_plus']->count() > 0)
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">Most Used</p>
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
                            <p class="text-sm font-medium text-gray-700 mb-2">Top Category</p>
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
                            <p class="text-sm font-medium text-gray-700 mb-2">Organic Ratio</p>
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
                            <p class="text-sm font-medium text-gray-700 mb-2">This Week</p>
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
                    <h3 class="text-lg font-medium text-gray-900">Marketplace Activity</h3>
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
                        <p class="text-sm font-medium text-gray-700 mb-3">Top Performing</p>
                        <div class="space-y-2">
                            @foreach($marketplaceInsights['top_performing'] as $list)
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-gray-900 truncate">{{ $list->marketplace_title ?? $list->name
                                    }}</span>
                                <div class="flex space-x-2 text-gray-500">
                                    <span>{{ $list->view_count }} views</span>
                                    <span>{{ $list->copy_count }} copies</span>
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
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showCreateModal', (value) => {
            if (value) {
                setTimeout(() => $refs.listNameInput?.focus(), 100);
            }
        })">
        <!-- Background overlay -->
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

    <!-- Share Modal -->
    <div x-show="$wire.showShareModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showShareModal', (value) => {
            if (value && $wire.isPublic && $wire.shareUrl) {
                setTimeout(() => {
                    const container = document.querySelector('[x-ref=dashboardQrContainer]');
                    if (container && window.QRCode) {
                        container.innerHTML = '';
                        window.QRCode.toString($wire.shareUrl, {
                            type: 'svg',
                            width: 150,
                            margin: 2
                        }, (err, svg) => {
                            if (!err) container.innerHTML = svg;
                        });
                    }
                }, 200);
            }
        })">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.toggleShareModal()"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showShareModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Share List</h3>
                        <button @click="$wire.toggleShareModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <!-- List name -->
                        <div>
                            <p class="text-sm text-gray-600">Sharing: <span class="font-medium text-gray-900"
                                    x-text="$wire.selectedList ? $wire.selectedList.name : ''"></span></p>
                        </div>

                        <!-- Public sharing toggle -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="text-sm font-medium text-gray-700">Public Sharing</label>
                                <p class="text-xs text-gray-500 mt-1">Allow others to view this list with a link</p>
                            </div>
                            <button wire:click="togglePublicSharing"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                :class="$wire.isPublic ? 'bg-green-600' : 'bg-gray-200'">
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="$wire.isPublic ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>

                        <!-- Share URL (only shown when public) -->
                        <div x-show="$wire.isPublic" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Share URL</label>
                            <div class="flex mb-4">
                                <input type="text" :value="$wire.shareUrl" readonly
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md bg-gray-50 text-sm"
                                    x-ref="dashboardShareUrl">
                                <button @click="
                                    $refs.dashboardShareUrl.select();
                                    document.execCommand('copy');
                                    $dispatch('notify', { message: 'Link copied to clipboard!', type: 'success' });
                                "
                                    class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-r-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Copy
                                </button>
                            </div>

                            <!-- QR Code -->
                            <div class="flex justify-center">
                                <div class="text-center">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                                    <div class="inline-block p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                                        <div x-ref="dashboardQrContainer"
                                            class="w-[150px] h-[150px] flex items-center justify-center">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-2">Scan to open list</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 rounded-b-lg">
                    <button @click="$wire.toggleShareModal()"
                        class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rename Modal -->
    <div x-show="$wire.showRenameModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showRenameModal', (value) => {
            if (value) {
                setTimeout(() => $refs.renameInput?.focus(), 100);
            }
        })">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.cancelRename()"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showRenameModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Rename List</h3>
                        <button @click="$wire.cancelRename()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <form wire:submit.prevent="saveRename">
                    <div class="px-6 py-4">
                        <div>
                            <label for="renameValue" class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                            <input type="text" wire:model="renameValue" id="renameValue"
                                placeholder="Enter new name..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                x-ref="renameInput">
                            @error('renameValue')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="$wire.cancelRename()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="$wire.showDeleteModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.cancelDelete()"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showDeleteModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Delete List</h3>
                        </div>
                        <button @click="$wire.cancelDelete()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete
                            <span class="font-medium text-gray-900"
                                x-text="$wire.listToDelete ? $wire.listToDelete.name : ''"></span>?
                        </p>
                        <p class="text-sm text-red-600">
                            This action cannot be undone. All items in this list will be permanently removed.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex justify-end space-x-3">
                        <button @click="$wire.cancelDelete()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteList"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            Delete List
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
