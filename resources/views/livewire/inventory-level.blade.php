<div wire:ignore 
     x-data="inventoryItem({
         itemId: @js($pluCode?->id ?? $listItem->plu_code_id),
         listItemId: @js($listItem->id),
         initialValue: @js($listItem->inventory_level)
     })"
     data-list-item-id="{{ $listItem->id }}"
     class="w-full">
    
    @if($listItem)
    <!-- Mobile-optimized inventory controls -->
    <div class="flex flex-col items-center space-y-2">
        <!-- Main controls row -->
        <div class="flex items-center gap-3 w-full justify-center">
            <!-- Decrement button - larger touch target -->
            <button @click.stop="decrement"
                    :disabled="localValue <= 0"
                    class="relative w-12 h-12 flex items-center justify-center bg-red-500 text-white rounded-lg 
                           hover:bg-red-600 active:scale-95 transition-all duration-150 
                           disabled:bg-gray-300 disabled:cursor-not-allowed touch-manipulation
                           focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    aria-label="Decrease inventory">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                </svg>
            </button>
            
            <!-- Value display -->
            <div class="min-w-[80px] text-center">
                <div class="relative">
                    <!-- Display value -->
                    <div x-show="!$wire.isEditing" 
                         @click.stop="$wire.startEditing()"
                         class="text-2xl font-bold cursor-pointer hover:text-blue-600 transition-colors"
                         x-text="displayValue">
                    </div>
                    
                    <!-- Edit input -->
                    <div x-show="$wire.isEditing" x-cloak>
                        <input type="number" 
                               wire:model.defer="editableValue" 
                               wire:blur="saveEdit"
                               x-init="$el.focus(); $el.select()"
                               @keydown.enter="$wire.saveEdit()"
                               @keydown.escape="$wire.isEditing = false"
                               class="w-20 text-2xl font-bold text-center border-2 border-blue-500 rounded-md 
                                      focus:outline-none focus:ring-2 focus:ring-blue-500"
                               step="0.5"
                               min="0">
                    </div>
                    
                    <!-- Sync indicator -->
                    <div x-show="hasPendingChanges || isSyncing" 
                         class="absolute -bottom-5 left-0 right-0 text-xs text-gray-500">
                        <span x-show="isSyncing" class="animate-pulse">Syncing...</span>
                        <span x-show="!isSyncing && hasPendingChanges" class="text-orange-500">Pending</span>
                    </div>
                    
                    <!-- Error message -->
                    <div x-show="syncError" 
                         x-text="syncError"
                         class="absolute -bottom-8 left-0 right-0 text-xs text-red-500">
                    </div>
                </div>
                
                @error('editableValue')
                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Increment button -->
            <button @click.stop="increment"
                    class="relative w-12 h-12 flex items-center justify-center bg-green-500 text-white rounded-lg 
                           hover:bg-green-600 active:scale-95 transition-all duration-150 
                           touch-manipulation focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    aria-label="Increase inventory">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        
        <!-- Quick action buttons -->
        <div class="flex gap-1 w-full max-w-xs">
            <button @click.stop="setValue(0)" 
                    class="flex-1 px-3 py-2 text-sm bg-gray-200 rounded-md hover:bg-gray-300 
                           active:scale-95 transition-all duration-150 touch-manipulation">
                Clear
            </button>
            
            <button @click.stop="subtractHalf" 
                    :disabled="localValue < 0.5"
                    class="flex-1 px-3 py-2 text-sm bg-blue-200 rounded-md hover:bg-blue-300 
                           active:scale-95 transition-all duration-150 touch-manipulation
                           disabled:bg-gray-100 disabled:text-gray-400">
                -0.5
            </button>
            
            <button @click.stop="addHalf" 
                    class="flex-1 px-3 py-2 text-sm bg-blue-200 rounded-md hover:bg-blue-300 
                           active:scale-95 transition-all duration-150 touch-manipulation">
                +0.5
            </button>
            
            <button @click.stop="setValue(1)" 
                    class="flex-1 px-3 py-2 text-sm bg-gray-200 rounded-md hover:bg-gray-300 
                           active:scale-95 transition-all duration-150 touch-manipulation">
                1
            </button>
        </div>
    </div>
    
    <!-- Offline indicator (only shows when offline) -->
    <div x-data="{ online: navigator.onLine }"
         x-init="
             window.addEventListener('online', () => online = true);
             window.addEventListener('offline', () => online = false);
         "
         x-show="!online"
         x-transition
         class="mt-2 text-xs text-center text-orange-600 font-medium">
        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"></path>
        </svg>
        Offline - Changes will sync when connected
    </div>
    @endif
</div>