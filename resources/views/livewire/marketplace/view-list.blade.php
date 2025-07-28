<div class="min-h-screen bg-gray-50">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative">
            <div class="flex items-center justify-between">
                <span>{{ session('message') }}</span>
                <button @click="show = false" class="ml-2 text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative">
            <div class="flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-2 text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between space-y-4 sm:space-y-0">
                <div class="flex-1">
                    <!-- Title and category - stack on mobile -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3 mb-2">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-0">
                            {{ $marketplaceList->marketplace_title }}
                        </h1>
                        @if($marketplaceList->marketplace_category)
                            <span class="px-3 py-1 text-sm font-medium rounded-full w-fit {{ 
                                $marketplaceList->marketplace_category === 'organic' ? 'bg-green-100 text-green-800' :
                                ($marketplaceList->marketplace_category === 'seasonal' ? 'bg-orange-100 text-orange-800' :
                                ($marketplaceList->marketplace_category === 'grocery-retail' ? 'bg-purple-100 text-purple-800' :
                                'bg-gray-100 text-gray-800'))
                            }}">
                                {{ $categories[$marketplaceList->marketplace_category] ?? 'Other' }}
                            </span>
                        @endif
                    </div>
                    
                    <!-- Mobile-friendly metadata layout -->
                    <div class="space-y-2 text-sm text-gray-600 mb-3">
                        <!-- Author - always on first line -->
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>by {{ $marketplaceList->user->name }}</span>
                        </div>
                        
                        <!-- Published date - second line -->
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Published {{ $marketplaceList->published_at->diffForHumans() }}</span>
                        </div>
                        
                        <!-- Stats - third line, can wrap on very small screens -->
                        <div class="flex items-center space-x-4 flex-wrap">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span>{{ $marketplaceList->view_count }} views</span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                </svg>
                                <span>{{ $marketplaceList->copy_count }} copies</span>
                            </div>
                        </div>
                    </div>

                    @if($marketplaceList->marketplace_description)
                        <p class="text-gray-600 mb-4">
                            {{ $marketplaceList->marketplace_description }}
                        </p>
                    @endif

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('marketplace.browse') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            ← Back to Marketplace
                        </a>
                    </div>
                </div>

                <div class="sm:ml-6 w-full sm:w-auto">
                    @auth
                        <button wire:click="toggleCopyModal" 
                            class="w-full sm:w-auto bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition font-medium text-center">
                            Copy to My Lists
                        </button>
                    @else
                        <a href="{{ route('login') }}" 
                           class="block w-full sm:w-auto bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition font-medium text-center">
                            Login to Copy
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- List Items -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">
                    List Items ({{ $listItems->count() }})
                </h2>
            </div>
            <div class="p-2 sm:p-6">
                <x-plu-code-table 
                    :collection="$listItems" 
                    :readOnly="true"
                    :showInventory="false"
                    :dual-version-plu-codes="$dualVersionPluCodes" 
                />
            </div>
        </div>
    </div>

    <!-- Copy List Modal -->
    <div x-show="$wire.showCopyModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showCopyModal', (value) => {
            if (value) {
                setTimeout(() => $refs.listNameInput?.focus(), 100);
            }
        })">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.toggleCopyModal()"></div>
        
        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showCopyModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-md w-full z-20">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Copy List</h3>
                        <button @click="$wire.toggleCopyModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <form wire:submit.prevent="copyList">
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-4">
                                    You're about to copy "{{ $marketplaceList->marketplace_title }}" with {{ $listItems->count() }} items to your lists. You can copy this list multiple times with different names.
                                </p>
                                <label for="customListName" class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                                <input type="text" 
                                    wire:model="customListName" 
                                    id="customListName"
                                    placeholder="Enter name for your copy..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    x-ref="listNameInput">
                                @error('customListName') 
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="bg-blue-50 p-3 rounded-md">
                                <p class="text-sm text-blue-800">
                                    <strong>Note:</strong> Items will be copied without inventory levels. You can set your own inventory amounts after copying.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="$wire.toggleCopyModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Copy List
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>