<div>
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

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Your Lists</h1>
        <button wire:click="toggleCreateModal"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Create New List</button>
    </div>

    @if($userLists->count())
    <ul class="mt-4 space-y-2">
        @foreach($userLists as $list)
        <li class="border rounded-lg p-4 bg-white shadow hover:shadow-md transition">
            <!-- Mobile-friendly layout -->
            <div class="space-y-3">
                <!-- Main content row -->
                <div class="flex justify-between items-start">
                    <a href="{{ route('lists.show', $list) }}" class="flex-1 min-w-0 pr-3">
                        <div class="flex justify-between items-baseline">
                            <span class="text-blue-500 font-semibold truncate">{{ $list->name }}</span>
                            <span class="text-gray-600 text-sm ml-2 flex-shrink-0">{{ $list->listItems->count() }} items</span>
                        </div>
                    </a>
                    <!-- Action buttons in single row -->
                    <div class="flex items-center space-x-1 flex-shrink-0">
                        <!-- Share button -->
                        <button wire:click="toggleShareModal({{ $list->id }})"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-all duration-150 shadow-sm bg-green-500 text-white hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1"
                            title="Share List">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                        </button>
                        
                        <!-- Marketplace actions -->
                        @if($list->marketplace_enabled)
                            <button wire:click="confirmUnpublish({{ $list->id }})"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-all duration-150 shadow-sm bg-red-500 text-white hover:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1"
                                title="Unpublish from Marketplace">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @else
                            <button wire:click="togglePublishModal({{ $list->id }})"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-all duration-150 shadow-sm bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1"
                                title="Publish to Marketplace">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </button>
                        @endif
                        
                        <!-- Delete button -->
                        <button wire:click="confirmDelete({{ $list->id }})"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-all duration-150 shadow-sm bg-red-500 text-white hover:bg-red-600 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1"
                            title="Delete List">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Status badges row -->
                @if($list->is_public || $list->marketplace_enabled)
                    <div class="flex flex-wrap gap-2">
                        @if($list->is_public)
                            <a href="{{ route('lists.shared', $list->share_code) }}" 
                               class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition-colors duration-150"
                               title="View Public Share"
                               target="_blank">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                Public
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @endif
                        @if($list->marketplace_enabled)
                            <a href="{{ route('marketplace.view', $list->share_code) }}" 
                               class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-150"
                               title="View in Marketplace">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Marketplace
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </li>
        @endforeach
    </ul>
    @else
    <p class="mt-4">You have no lists yet.</p>
    @endif

    <!-- Share Modal -->
    <div x-show="$wire.showShareModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showShareModal', (value) => {
            if (value && $wire.isPublic && $wire.shareUrl) {
                setTimeout(() => {
                    const container = document.querySelector('[x-ref=qrContainer]');
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
        })"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        <!-- List name -->
                        <div>
                            <p class="text-sm text-gray-600">Sharing: <span class="font-medium text-gray-900" x-text="$wire.selectedList ? $wire.selectedList.name : ''"></span></p>
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
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="$wire.isPublic ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                        </div>
                        
                        <!-- Share URL (only shown when public) -->
                        <div x-show="$wire.isPublic" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Share URL</label>
                            <div class="flex mb-4">
                                <input type="text" :value="$wire.shareUrl" readonly
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md bg-gray-50 text-sm"
                                    x-ref="shareUrl">
                                <button @click="
                                    $refs.shareUrl.select();
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
                                        <div x-ref="qrContainer" class="w-[150px] h-[150px] flex items-center justify-center">
                                            <!-- QR code will be inserted here -->
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

    <!-- Create List Modal -->
    <div x-show="$wire.showCreateModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showCreateModal', (value) => {
            if (value) {
                setTimeout(() => $refs.listNameInput?.focus(), 100);
            }
        })"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <form wire:submit.prevent="createList">
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <label for="newListName" class="block text-sm font-medium text-gray-700 mb-2">List Name</label>
                                <input type="text" 
                                    wire:model="newListName" 
                                    id="newListName"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Delete List</h3>
                        </div>
                        <button @click="$wire.cancelDelete()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to delete 
                            <span class="font-medium text-gray-900" x-text="$wire.listToDelete ? $wire.listToDelete.name : ''"></span>?
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

    <!-- Publish to Marketplace Modal -->
    <div x-show="$wire.showPublishModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
        x-init="$watch('$wire.showPublishModal', (value) => {
            if (value) {
                setTimeout(() => $refs.marketplaceTitleInput?.focus(), 100);
            }
        })">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.togglePublishModal()"></div>
        
        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showPublishModal" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95"
                class="relative bg-white rounded-lg shadow-xl max-w-lg w-full z-20">
                
                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Publish to Marketplace</h3>
                        <button @click="$wire.togglePublishModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <form wire:submit.prevent="publishToMarketplace">
                    <div class="px-6 py-4">
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-600 mb-4">
                                    Publishing "<span class="font-medium" x-text="$wire.listToPublish ? $wire.listToPublish.name : ''"></span>" to the public marketplace will allow other users to discover and copy your list.
                                </p>
                            </div>
                            
                            <div>
                                <label for="marketplaceTitle" class="block text-sm font-medium text-gray-700 mb-2">Marketplace Title *</label>
                                <input type="text" 
                                    wire:model="marketplaceTitle" 
                                    id="marketplaceTitle"
                                    placeholder="Enter a descriptive title for the marketplace..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    x-ref="marketplaceTitleInput">
                                @error('marketplaceTitle') 
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="marketplaceDescription" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea 
                                    wire:model="marketplaceDescription" 
                                    id="marketplaceDescription"
                                    rows="3"
                                    placeholder="Describe your list to help others understand what it contains..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                                @error('marketplaceDescription') 
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="marketplaceCategory" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select 
                                    wire:model="marketplaceCategory" 
                                    id="marketplaceCategory"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select a category...</option>
                                    <option value="meal-planning">Meal Planning</option>
                                    <option value="seasonal">Seasonal</option>
                                    <option value="organic">Organic Focus</option>
                                    <option value="budget">Budget Friendly</option>
                                    <option value="healthy">Healthy Eating</option>
                                    <option value="family">Family Meals</option>
                                    <option value="quick-meals">Quick Meals</option>
                                    <option value="special-diet">Special Diet</option>
                                    <option value="entertaining">Entertaining</option>
                                    <option value="grocery-retail">Grocery Retail</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('marketplaceCategory') 
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="bg-blue-50 p-3 rounded-md">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-800">
                                            <strong>Note:</strong> Your list will be publicly visible to all users. Users can copy your list but cannot edit your original.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="$wire.togglePublishModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Publish to Marketplace
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Unpublish Confirmation Modal -->
    <div x-show="$wire.showUnpublishModal" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-10" @click="$wire.cancelUnpublish()"></div>
        
        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="$wire.showUnpublishModal" x-transition:enter="transition ease-out duration-300"
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-medium text-gray-900">Unpublish from Marketplace</h3>
                        </div>
                        <button @click="$wire.cancelUnpublish()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-500">
                            Are you sure you want to unpublish 
                            <span class="font-medium text-gray-900" x-text="$wire.listToUnpublish ? $wire.listToUnpublish.name : ''"></span> 
                            from the marketplace?
                        </p>
                        <div class="bg-yellow-50 p-3 rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Note:</strong> This will remove your list from the public marketplace. Users who have already copied it will keep their copies, but no new users will be able to discover or copy it.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex justify-end space-x-3">
                        <button @click="$wire.cancelUnpublish()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="unpublishFromMarketplace"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
                            Unpublish
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>