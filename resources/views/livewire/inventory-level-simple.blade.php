<div class="w-full" 
     x-data="{
         localValue: @js($currentValue),
         serverValue: @js($currentValue),
         pendingChanges: 0,
         isOnline: navigator.onLine,
         isSyncing: false,
         hasLocalChanges: false,
         syncTimeout: null,
         SYNC_DELAY: 2000, // 2 seconds
         
         init() {
             // Listen for online/offline events
             window.addEventListener('online', () => {
                 this.isOnline = true;
                 if (this.pendingChanges !== 0) {
                     this.syncPendingChanges();
                 }
             });
             window.addEventListener('offline', () => {
                 this.isOnline = false;
             });
             
             // Watch for server value changes and only update if we don't have local changes
             this.$watch('serverValue', (newVal) => {
                 if (!this.hasLocalChanges) {
                     this.localValue = newVal;
                 }
             });
         },
         
         updateLocal(delta) {
             this.localValue = Math.max(0, this.localValue + delta);
             this.pendingChanges += delta;
             this.hasLocalChanges = true;
             
             if (this.isOnline) {
                 this.debouncedSync();
             }
         },
         
         debouncedSync() {
             // Clear any existing timeout
             if (this.syncTimeout) {
                 clearTimeout(this.syncTimeout);
             }
             
             // Set a new timeout for syncing
             this.syncTimeout = setTimeout(() => {
                 this.syncToServer();
             }, this.SYNC_DELAY);
         },
         
         setLocal(value) {
             const delta = value - this.localValue;
             this.updateLocal(delta);
         },
         
         async syncToServer() {
             if (this.isSyncing) return;
             
             // Clear the timeout since we're syncing now
             if (this.syncTimeout) {
                 clearTimeout(this.syncTimeout);
                 this.syncTimeout = null;
             }
             
             this.isSyncing = true;
             
             try {
                 // Use the setValue method to set the exact current local value
                 await this.$wire.setValue(this.localValue);
                 
                 // Sync successful - reset flags
                 this.pendingChanges = 0;
                 this.hasLocalChanges = false;
                 this.serverValue = this.localValue;
             } catch (error) {
                 console.log('Sync failed, will retry when online');
                 // On error, schedule another sync attempt
                 if (this.isOnline) {
                     this.debouncedSync();
                 }
             } finally {
                 this.isSyncing = false;
             }
         },
         
         async syncPendingChanges() {
             if (this.pendingChanges !== 0) {
                 // When coming back online, sync immediately without debouncing
                 await this.syncToServer();
             }
         },
         
         // Clean up timeout when component is destroyed
         destroy() {
             if (this.syncTimeout) {
                 clearTimeout(this.syncTimeout);
             }
         }
     }">
    @if($listItem && isset($currentValue))
    <!-- Simple mobile-optimized inventory controls -->
    <div class="flex flex-col items-center space-y-2">
        <!-- Main controls row -->
        <div class="flex items-center gap-3 w-full justify-center">
            <!-- Decrement button -->
            <button @click.stop="updateLocal(-1)"
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
                    @if($isEditing)
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
                    @else
                        <div wire:click.stop="startEditing"
                             wire:ignore
                             class="text-2xl font-bold cursor-pointer hover:text-blue-600 transition-colors"
                             x-text="localValue.toFixed(1)">
                        </div>
                    @endif
                    
                    <!-- Status indicators -->
                    <div class="absolute -bottom-5 left-0 right-0 text-xs text-center">
                        <!-- Syncing indicator -->
                        <span x-show="isSyncing" class="text-gray-500 animate-pulse">Syncing...</span>
                        
                        <!-- Scheduled sync indicator -->
                        <span x-show="!isSyncing && syncTimeout !== null && isOnline" 
                              class="text-blue-500">Will sync in 2s...</span>
                        
                        <!-- Pending changes indicator -->
                        <span x-show="!isSyncing && syncTimeout === null && pendingChanges !== 0 && isOnline" 
                              class="text-orange-500">Pending sync</span>
                        
                        <!-- Offline indicator -->
                        <span x-show="!isOnline && pendingChanges !== 0" 
                              class="text-red-500">Offline - will sync later</span>
                    </div>
                </div>
                
                @error('editableValue')
                <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Increment button -->
            <button @click.stop="updateLocal(1)"
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
            <button @click.stop="setLocal(0)" 
                    class="flex-1 px-3 py-2 text-sm bg-gray-200 rounded-md hover:bg-gray-300 
                           active:scale-95 transition-all duration-150 touch-manipulation">
                Clear
            </button>
            
            <button @click.stop="updateLocal(-0.5)" 
                    :disabled="localValue < 0.5"
                    class="flex-1 px-3 py-2 text-sm bg-blue-200 rounded-md hover:bg-blue-300 
                           active:scale-95 transition-all duration-150 touch-manipulation
                           disabled:bg-gray-100 disabled:text-gray-400">
                -0.5
            </button>
            
            <button @click.stop="updateLocal(0.5)" 
                    class="flex-1 px-3 py-2 text-sm bg-blue-200 rounded-md hover:bg-blue-300 
                           active:scale-95 transition-all duration-150 touch-manipulation">
                +0.5
            </button>
        </div>
    </div>
    @endif
</div>