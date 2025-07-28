<div x-data="{ carouselOpen: false }" class="min-h-screen bg-gray-50">
    <!-- Read-only header with distinct styling -->
    <div class="bg-blue-50 border-b border-blue-200 sticky top-0 z-40">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-2">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg font-semibold text-blue-900 truncate">{{ $userList->name }}</h1>
                            <p class="text-sm text-blue-600 mt-0.5">📋 Shared List • {{ $listItems->count() }} items</p>
                        </div>
                    </div>
                </div>
                
                <!-- Action buttons -->
                <div class="flex items-center space-x-2 ml-4">
                    <!-- Copy button -->
                    @auth
                        <button wire:click="toggleCopyModal"
                            class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition-all duration-150 shadow-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            Copy List
                        </button>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-all duration-150 shadow-sm">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                            Login to Copy
                        </a>
                    @endauth
                    
                    <!-- Read-only scan button -->
                    @if($listItems->count() > 0)
                        <button @click="carouselOpen = true; $dispatch('carousel-open')"
                            class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-500 text-white hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 transition-all duration-150 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <!-- Corner brackets -->
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7V5a2 2 0 012-2h2M3 17v2a2 2 0 002 2h2M21 17v2a2 2 0 01-2 2h-2M21 7V5a2 2 0 00-2-2h-2"></path>
                                <!-- Simple barcode -->
                                <rect x="9" y="9" width="1" height="6" fill="currentColor" />
                                <rect x="11" y="9" width="2" height="6" fill="currentColor" />
                                <rect x="14" y="9" width="1" height="6" fill="currentColor" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="pb-6">
        <!-- Notice for shared view -->
        <div class="bg-blue-100 border-l-4 border-blue-500 p-4 mx-4 mt-4 rounded-r">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        You are viewing a shared list. This is a read-only view - you cannot make changes to this list.
                    </p>
                </div>
            </div>
        </div>

        <!-- List Items Table (Read-Only with Inventory) -->
        <div class="mx-4 mt-4">
            <x-plu-code-table 
                :collection="$listItems" 
                :readOnly="true"
                :showInventory="true"
                :dual-version-plu-codes="$dualVersionPluCodes" 
                :user-list-id="$userList->id"
            />
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
                        <h3 class="text-lg font-medium text-gray-900">Copy Shared List</h3>
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
                                    You're about to copy "{{ $userList->name }}" with all of its items to your lists. 
                                    <strong>All inventory levels will be preserved</strong>, including items not currently displayed.
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
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-800">
                                            <strong>Note:</strong> You'll get the complete list including items with zero quantities. Only items with quantities are shown here for clarity!
                                        </p>
                                    </div>
                                </div>
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

    <!-- Read-only carousel modal -->
    <div wire:key="shared-carousel-{{ $userList->id }}" x-show="carouselOpen" x-cloak
        @carousel-close.window="carouselOpen = false">
        @livewire('item-carousel', ['userListId' => $userList->id, 'readOnly' => true])
    </div>
</div>
