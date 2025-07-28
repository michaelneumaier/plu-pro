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
                
                <!-- Read-only scan button -->
                @if($listItems->where('inventory_level', '>', 0)->count() > 0)
                <div class="flex items-center space-x-2 ml-4">
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
                </div>
                @endif
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

        <!-- List Items Table (Read-Only) -->
        <div class="mx-4 mt-4">
            <x-plu-code-table 
                :collection="$listItems" 
                :readOnly="true"
                :dual-version-plu-codes="$dualVersionPluCodes" 
                :user-list-id="$userList->id"
            />
        </div>
    </div>

    <!-- Read-only carousel modal -->
    <div wire:key="shared-carousel-{{ $userList->id }}" x-show="carouselOpen" x-cloak
        @carousel-close.window="carouselOpen = false">
        @livewire('item-carousel', ['userListId' => $userList->id, 'readOnly' => true])
    </div>
</div>
