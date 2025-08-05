<div class="w-full" 
     x-data="{
         localValue: parseFloat(@js($currentValue)) || 0,
         pendingChanges: 0,
         isOnline: navigator.onLine,
         syncTimeout: null,
         SYNC_DELAY: 200,
         isEditing: false,
         editValue: '',
         
         init() {
             // Initialize global editing state if it doesn't exist
             if (!window.globalEditingState) {
                 window.globalEditingState = {
                     currentEditor: null,
                     closeAll: () => {
                         if (window.globalEditingState.currentEditor) {
                             window.globalEditingState.currentEditor.cancelEdit();
                         }
                     }
                 };
             }
             
             window.addEventListener('online', () => {
                 this.isOnline = true;
                 if (this.pendingChanges !== 0) {
                     this.syncPendingChanges();
                 }
             });
             window.addEventListener('offline', () => {
                 this.isOnline = false;
             });
             
             // Listen for Livewire updates to sync local state when needed
             this.$wire.on('value-updated', (value) => {
                 if (this.pendingChanges === 0) {
                     this.localValue = parseFloat(value) || 0;
                 }
             });
             
             // Handle filter changes gracefully - wait for pending sync before refreshing
             this.$wire.on('inventory-filter-changed', () => {
                 this.handleFilterChange();
             });
             
             // Prevent data loss on page unload
             window.addEventListener('beforeunload', (e) => {
                 if (this.pendingChanges !== 0) {
                     // Try to send beacon for pending changes
                     const data = {
                         listItemId: @js($listItem->id),
                         delta: this.pendingChanges,
                         timestamp: Date.now()
                     };
                     
                     if ('sendBeacon' in navigator) {
                         navigator.sendBeacon('/api/inventory-beacon', JSON.stringify(data));
                     }
                     
                     // Show browser warning
                     e.preventDefault();
                     e.returnValue = 'You have unsaved inventory changes. Are you sure you want to leave?';
                     return e.returnValue;
                 }
             });
         },
         
         updateLocal(delta) {
             this.localValue = Math.max(0, this.localValue + delta);
             this.pendingChanges += delta;
             
             // Mark page as dirty on first change
             window.markDirty?.();
             
             if (this.isOnline) {
                 this.debouncedSync();
             }
         },
         
         setLocal(value) {
             const delta = value - this.localValue;
             this.updateLocal(delta);
         },
         
         // Helper to save edit and perform action
         saveEditAndDo(action) {
             if (this.isEditing) {
                 this.saveEdit(this.editValue);
             }
             action();
         },
         
         debouncedSync() {
             if (this.syncTimeout) {
                 clearTimeout(this.syncTimeout);
             }
             
             this.syncTimeout = setTimeout(() => {
                 // Don't sync while user is editing
                 if (!this.isEditing) {
                     this.syncToServer();
                 } else {
                     // Reschedule sync for after editing
                     this.debouncedSync();
                 }
             }, this.SYNC_DELAY);
         },
         
         async syncToServer() {
             if (this.syncTimeout) {
                 clearTimeout(this.syncTimeout);
                 this.syncTimeout = null;
             }
             
             try {
                 // Store the current local value before sync
                 const currentLocalValue = this.localValue;
                 
                 // Use Livewire component method call
                 await window.Livewire.find('{{ $this->getId() }}').call('setValue', this.localValue);
                 this.pendingChanges = 0;
                 
                 // Ensure local value stays consistent after sync
                 this.localValue = currentLocalValue;
                 
                 // Everything is flushed ➜ safe to navigate
                 window.markSynced?.();
             } catch (error) {
                 console.log('Sync failed, will retry when online');
                 if (this.isOnline) {
                     this.debouncedSync();
                 }
             }
         },
         
         syncPendingChanges() {
             if (this.pendingChanges !== 0) {
                 this.syncToServer();
             }
         },
         
         // Handle filter changes gracefully - wait for pending sync to complete
         async handleFilterChange() {
             if (this.pendingChanges !== 0) {
                 // Wait for pending sync to complete before allowing refresh
                 await this.syncToServer();
             }
             // Now safe to refresh component
             this.$wire.$refresh();
         },
         
         startEditing() {
             // Close any other open editors
             if (window.globalEditingState.currentEditor && window.globalEditingState.currentEditor !== this) {
                 window.globalEditingState.currentEditor.cancelEdit();
             }
             
             this.editValue = this.localValue.toFixed(1);
             this.isEditing = true;
             window.globalEditingState.currentEditor = this;
         },
         
         saveEdit(value) {
             const numValue = parseFloat(value) || 0;
             if (numValue >= 0) {
                 this.setLocal(numValue);
             }
             this.isEditing = false;
             
             // Clear global editor reference
             if (window.globalEditingState.currentEditor === this) {
                 window.globalEditingState.currentEditor = null;
             }
             
             // Trigger sync if there are pending changes
             if (this.pendingChanges !== 0 && this.isOnline) {
                 this.debouncedSync();
             }
         },
         
         cancelEdit() {
             this.isEditing = false;
             
             // Clear global editor reference
             if (window.globalEditingState.currentEditor === this) {
                 window.globalEditingState.currentEditor = null;
             }
         }
     }">
    @if($listItem && isset($currentValue))
    <!-- Compact mobile-first inventory controls -->
    <div class="flex flex-col items-center space-y-1">
        <!-- Main controls row - much more compact -->
        <div class="flex items-center w-full justify-center">
            <!-- Decrement button -->
            <button @click.stop="saveEditAndDo(() => updateLocal(-1))"
                    :disabled="localValue <= 0"
                    class="relative w-8 h-8 flex items-center justify-center bg-red-500 text-white rounded-md 
                           hover:bg-red-600 active:scale-95 transition-all duration-100 
                           disabled:bg-gray-300 disabled:cursor-not-allowed touch-manipulation
                           focus:outline-none focus:ring-1 focus:ring-red-400"
                    aria-label="Decrease inventory">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M20 12H4"></path>
                </svg>
            </button>
            
            <!-- Value display - more compact -->
            <div class="w-14 text-center relative">
                <input type="text" 
                       inputmode="decimal"
                       x-model="editValue"
                       x-show="isEditing"
                       @focus="$el.select()"
                       @keydown.enter.stop="saveEdit($el.value)"
                       @keydown.escape.stop="cancelEdit()"
                       @blur.stop="saveEdit($el.value)"
                       @click.stop
                       class="w-14 h-8 text-lg font-semibold text-center border border-blue-400 rounded-md 
                              focus:outline-none focus:ring-1 focus:ring-blue-400
                              [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                       min="0">
                
                <div @click.stop="startEditing(); $nextTick(() => $el.previousElementSibling.focus())"
                     x-show="!isEditing"
                     class="w-14 h-8 text-lg font-semibold cursor-pointer hover:text-blue-600 transition-colors rounded-md hover:bg-blue-50 flex items-center justify-center"
                     x-text="(parseFloat(localValue) || 0).toFixed(1)">
                </div>
                
                @error('editableValue')
                <div class="text-xs text-red-500 mt-0.5">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Increment button -->
            <button @click.stop="saveEditAndDo(() => updateLocal(1))"
                    class="relative w-8 h-8 flex items-center justify-center bg-green-500 text-white rounded-md 
                           hover:bg-green-600 active:scale-95 transition-all duration-100 
                           touch-manipulation focus:outline-none focus:ring-1 focus:ring-green-400"
                    aria-label="Increase inventory">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                </svg>
            </button>
        </div>
        
        <!-- Quick action buttons - more compact and professional -->
        <div class="flex gap-1 w-full max-w-[180px]">
            <button @click.stop="saveEditAndDo(() => setLocal(0))" 
                    class="flex-1 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-md 
                           hover:bg-gray-200 active:scale-95 transition-all duration-100 touch-manipulation
                           border border-gray-200">
                Clear
            </button>
            
            <button @click.stop="saveEditAndDo(() => (parseFloat(localValue) || 0) % 1 === 0.5 ? updateLocal(-0.5) : updateLocal(0.5))" 
                    class="flex-1 px-2 py-1 text-xs font-medium rounded-md 
                           active:scale-95 transition-all duration-100 touch-manipulation
                           border"
                    :class="(parseFloat(localValue) || 0) % 1 === 0.5 ? 
                        'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100' : 
                        'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'"
                    x-text="(parseFloat(localValue) || 0) % 1 === 0.5 ? '-½' : '+½'">
            </button>
        </div>
    </div>
    @endif
</div>