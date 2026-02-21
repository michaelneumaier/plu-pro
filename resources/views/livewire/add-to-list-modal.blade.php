<div>
    <div x-data="{
        show: @entangle('showModal'),
        initSelectedList() {
            const lastList = localStorage.getItem('plupro_last_list_id');
            if (lastList && !$wire.selectedListId) {
                const listId = parseInt(lastList);
                const lists = $wire.userLists;
                if (lists && lists.some(l => l.id === listId)) {
                    $wire.selectedListId = listId;
                }
            }
        }
    }"
         x-show="show"
         x-on:keydown.escape.window="if (show) $wire.closeModal()"
         x-init="$watch('show', value => { if (value) $nextTick(() => initSelectedList()) })"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50"
             wire:click="closeModal"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="relative bg-white rounded-lg shadow-xl max-w-md w-full"
                 @click.stop>

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Add to List</h3>
                        <button wire:click="closeModal"
                                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 rounded-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Item being added -->
                @if($pendingPluCode)
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pendingOrganic ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $pendingOrganic ? 'Organic' : 'Regular' }}
                        </span>
                        <span class="font-mono font-semibold text-sm">{{ $pendingPluCode }}</span>
                        <span class="text-sm text-gray-600">{{ $pendingVariety }}</span>
                    </div>
                </div>
                @endif

                <!-- Body -->
                <div class="px-6 py-4">
                    <!-- Status Message -->
                    @if($statusMessage)
                    <div class="mb-4 px-4 py-3 rounded-md text-sm
                        {{ $statusType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : '' }}
                        {{ $statusType === 'error' ? 'bg-red-50 text-red-800 border border-red-200' : '' }}
                        {{ $statusType === 'info' ? 'bg-blue-50 text-blue-800 border border-blue-200' : '' }}">
                        {{ $statusMessage }}
                    </div>
                    @endif

                    <!-- List Selection -->
                    @if(!$showCreateForm && count($userLists) > 0)
                    <div class="space-y-2 mb-4">
                        <label class="block text-sm font-medium text-gray-700">Choose a list</label>
                        <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-md divide-y divide-gray-100">
                            @foreach($userLists as $list)
                            <label class="flex items-center px-4 py-3 cursor-pointer hover:bg-gray-50 transition-colors {{ $selectedListId == $list['id'] ? 'bg-green-50' : '' }}">
                                <input type="radio" wire:model="selectedListId" value="{{ $list['id'] }}"
                                       class="h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <span class="ml-3 text-sm text-gray-900">{{ $list['name'] }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <button wire:click="$set('showCreateForm', true)"
                            class="text-sm text-green-600 hover:text-green-800 font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create new list
                    </button>
                    @endif

                    <!-- Create New List Form -->
                    @if($showCreateForm)
                    <div class="space-y-3">
                        @if(count($userLists) > 0)
                        <button wire:click="$set('showCreateForm', false)"
                                class="text-sm text-gray-500 hover:text-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to lists
                        </button>
                        @endif

                        <label for="new-list-name" class="block text-sm font-medium text-gray-700">
                            {{ count($userLists) === 0 ? 'Create your first list' : 'New list name' }}
                        </label>
                        <input type="text"
                               wire:model="newListName"
                               id="new-list-name"
                               placeholder="Enter list name..."
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                               x-init="$nextTick(() => $el.focus())"
                               @keydown.enter.prevent="$wire.createListAndAdd()">
                        @error('newListName')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 rounded-b-lg">
                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeModal"
                                type="button"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </button>

                        @if($showCreateForm)
                        <button wire:click="createListAndAdd"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                            <svg wire:loading wire:target="createListAndAdd" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Create & Add
                        </button>
                        @else
                        <button wire:click="addToList"
                                wire:loading.attr="disabled"
                                :disabled="!$wire.selectedListId"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                            <svg wire:loading wire:target="addToList" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Add to List
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
