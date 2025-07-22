import Alpine from 'alpinejs';

// Global sync queue store
Alpine.store('syncQueue', {
    items: [],
    processing: false,
    
    add(item) {
        // Check if item already exists
        const existingIndex = this.items.findIndex(i => i.listItemId === item.listItemId);
        
        if (existingIndex >= 0) {
            // Update existing item
            this.items[existingIndex].delta += item.delta;
            this.items[existingIndex].timestamp = item.timestamp;
        } else {
            // Add new item
            this.items.push(item);
        }
    },
    
    remove(listItemId) {
        this.items = this.items.filter(i => i.listItemId !== listItemId);
    },
    
    hasItems() {
        return this.items.length > 0;
    },
    
    async processQueue() {
        if (this.processing || !navigator.onLine || this.items.length === 0) {
            return;
        }
        
        this.processing = true;
        const itemsToProcess = [...this.items];
        
        for (const item of itemsToProcess) {
            try {
                // Attempt to sync each item
                const component = document.querySelector(`[wire\\:id] [x-data*="inventoryItem"][data-list-item-id="${item.listItemId}"]`);
                if (component && component.__x) {
                    await component.__x.$data.sync();
                } else {
                    // If component not found, remove from queue
                    this.remove(item.listItemId);
                }
            } catch (error) {
                console.error('Queue processing error:', error);
            }
        }
        
        this.processing = false;
    },
    
    clear() {
        this.items = [];
    }
});

// Listen for sync events
document.addEventListener('alpine:init', () => {
    Alpine.effect(() => {
        const queue = Alpine.store('syncQueue');
        
        // Process queue when items are added and we're online
        if (queue.hasItems() && navigator.onLine && !queue.processing) {
            setTimeout(() => queue.processQueue(), 1000);
        }
    });
});

// Process sync queue on custom event
window.addEventListener('process-sync-queue', () => {
    Alpine.store('syncQueue').processQueue();
});