// Register component when Alpine is available
document.addEventListener('livewire:init', () => {
    if (window.Alpine) {
        window.Alpine.data('inventoryItem', (config) => ({
    itemId: config.itemId,
    listItemId: config.listItemId,
    serverValue: config.initialValue || 0,
    localValue: config.initialValue || 0,
    pendingDelta: 0,
    syncTimeout: null,
    lastSyncTime: Date.now(),
    isSyncing: false,
    syncError: null,
    
    init() {
        // Load from local storage if available
        const stored = this.getStoredValue();
        if (stored !== null && stored.timestamp > this.lastSyncTime - 300000) { // 5 min cache
            this.localValue = stored.value;
            this.pendingDelta = stored.pendingDelta || 0;
        }
        
        // Listen for online/offline events
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
        
        // Listen for storage events (sync across tabs)
        window.addEventListener('storage', (e) => {
            if (e.key === this.storageKey) {
                const data = JSON.parse(e.newValue);
                this.localValue = data.value;
                this.pendingDelta = data.pendingDelta || 0;
            }
        });
    },
    
    get storageKey() {
        return `plu_inventory_${this.listItemId}`;
    },
    
    get displayValue() {
        return Number(this.localValue).toFixed(1);
    },
    
    get hasPendingChanges() {
        return this.pendingDelta !== 0;
    },
    
    get isOnline() {
        return navigator.onLine;
    },
    
    getStoredValue() {
        const stored = localStorage.getItem(this.storageKey);
        return stored ? JSON.parse(stored) : null;
    },
    
    storeValue() {
        localStorage.setItem(this.storageKey, JSON.stringify({
            value: this.localValue,
            pendingDelta: this.pendingDelta,
            timestamp: Date.now()
        }));
    },
    
    increment() {
        this.updateValue(1);
        this.provideFeedback();
    },
    
    decrement() {
        if (this.localValue > 0) {
            this.updateValue(-1);
            this.provideFeedback();
        }
    },
    
    addHalf() {
        this.updateValue(0.5);
        this.provideFeedback();
    },
    
    subtractHalf() {
        if (this.localValue >= 0.5) {
            this.updateValue(-0.5);
            this.provideFeedback();
        }
    },
    
    setValue(value) {
        const numValue = parseFloat(value);
        if (!isNaN(numValue) && numValue >= 0) {
            const delta = numValue - this.localValue;
            this.updateValue(delta);
            this.provideFeedback();
        }
    },
    
    updateValue(delta) {
        // Optimistic update
        this.localValue = Math.max(0, this.localValue + delta);
        this.pendingDelta += delta;
        
        // Store immediately
        this.storeValue();
        
        // Clear any existing sync timeout
        clearTimeout(this.syncTimeout);
        
        // Debounce sync (300ms)
        this.syncTimeout = setTimeout(() => {
            this.sync();
        }, 300);
    },
    
    async sync() {
        if (!this.isOnline || this.pendingDelta === 0) {
            if (!this.isOnline && this.pendingDelta !== 0) {
                // Queue for later
                Alpine.store('syncQueue').add({
                    listItemId: this.listItemId,
                    delta: this.pendingDelta,
                    timestamp: Date.now()
                });
            }
            return;
        }
        
        this.isSyncing = true;
        this.syncError = null;
        
        try {
            // Call Livewire component
            const result = await this.$wire.updateInventory(
                this.listItemId,
                this.pendingDelta,
                this.lastSyncTime
            );
            
            if (result.success) {
                this.serverValue = result.newValue;
                this.localValue = result.newValue;
                this.pendingDelta = 0;
                this.lastSyncTime = Date.now();
                this.storeValue();
                
                // Remove from sync queue if exists
                Alpine.store('syncQueue').remove(this.listItemId);
            } else if (result.conflict) {
                // Handle conflict
                this.handleConflict(result);
            }
        } catch (error) {
            console.error('Sync error:', error);
            this.syncError = 'Failed to sync. Will retry.';
            
            // Add to sync queue for retry
            Alpine.store('syncQueue').add({
                listItemId: this.listItemId,
                delta: this.pendingDelta,
                timestamp: Date.now()
            });
        } finally {
            this.isSyncing = false;
        }
    },
    
    handleConflict(result) {
        // Simple last-write-wins strategy
        // You could implement more sophisticated conflict resolution here
        this.serverValue = result.serverValue;
        this.localValue = result.serverValue + this.pendingDelta;
        this.lastSyncTime = result.serverTimestamp;
        
        // Retry sync with updated base
        setTimeout(() => this.sync(), 100);
    },
    
    handleOnline() {
        // Sync when coming back online
        if (this.pendingDelta !== 0) {
            this.sync();
        }
    },
    
    handleOffline() {
        // Store current state when going offline
        this.storeValue();
    },
    
    provideFeedback() {
        // Haptic feedback on mobile
        if ('vibrate' in navigator && window.matchMedia('(max-width: 768px)').matches) {
            navigator.vibrate(10);
        }
    },
    
    startEdit() {
        this.$dispatch('start-edit', { value: this.localValue });
    },
    
    handleEdit(value) {
        this.setValue(value);
    }
}));

        }));

        // List-level inventory management
        window.Alpine.data('inventoryList', () => ({
            isOnline: navigator.onLine,
            syncStatus: 'idle', // idle, syncing, error
            
            init() {
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.$dispatch('sync-all');
                });
                
                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });
                
                // Periodic sync check
                setInterval(() => {
                    if (this.isOnline && window.Alpine.store('syncQueue').hasItems()) {
                        this.$dispatch('process-sync-queue');
                    }
                }, 30000); // Every 30 seconds
            }
        }));
    }
});