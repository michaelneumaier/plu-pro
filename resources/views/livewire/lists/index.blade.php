<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Your Lists</h1>
        <a href="{{ route('lists.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Create New List</a>
    </div>

    @if($userLists->count())
    <ul class="mt-4 space-y-2">
        @foreach($userLists as $list)
        <li class="border rounded-lg p-4 bg-white shadow hover:shadow-md transition">
            <div class="flex justify-between items-center w-full">
                <a href="{{ route('lists.show', $list) }}" class="flex-1 flex justify-between items-center">
                    <span class="text-blue-500 font-semibold">{{ $list->name }}</span>
                    <span class="text-gray-600 text-sm">{{ $list->listItems->count() }} items</span>
                </a>
                <div class="ml-4 flex items-center space-x-2">
                    <!-- Share status indicator -->
                    @if($list->is_public)
                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                            </svg>
                            Public
                        </span>
                    @endif
                    
                    <!-- Share button -->
                    <button wire:click="toggleShareModal({{ $list->id }})"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full transition-all duration-150 shadow-sm bg-green-500 text-white hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-offset-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </button>
                </div>
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
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
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
                        <div x-show="$wire.isPublic" x-transition 
                             x-data="{ 
                                generateQR() {
                                    if ($wire.shareUrl && window.QRCode) {
                                        window.QRCode.toCanvas($refs.qrCanvas, $wire.shareUrl, { width: 150 }, (error) => {
                                            if (error) console.error(error);
                                        });
                                    }
                                }
                             }"
                             x-init="$watch('$wire.shareUrl', () => { $nextTick(() => generateQR()) })">
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
                                        <canvas x-ref="qrCanvas" class="max-w-full"></canvas>
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
</div>