// List management Alpine.js component
document.addEventListener('alpine:init', () => {
    Alpine.store('listManager', {
        items: [],
        filteredItems: [],
        selectedCategory: '',
        selectedCommodity: '',
        categories: [],
        commodities: [],
        isLoading: false,
        localSearchTerm: '',

        // Inventory sync state
        dirtyItems: new Set(),
        syncTimeout: null,
        isSyncing: false,
        SYNC_DELAY: 500,
        showComponentId: null,
        userListId: null,

        init(initialItems = [], userListId = null, componentId = null) {
            if (this.items.length > 0 && initialItems.length > 0) {
                // Merge-aware re-init: keep dirty inventory values
                this.mergeServerItems(initialItems);
            } else {
                this.items = initialItems;
            }

            if (userListId) {
                this.userListId = userListId;
            }

            // Use the explicitly passed Show component ID (avoids grabbing nested components)
            if (componentId) {
                this.showComponentId = componentId;
            }

            this.filteredItems = [...this.items];
            this.extractFilterOptions();
            this.applyFilters();

            // Set up online/offline handlers (only once)
            if (!this._handlersInitialized) {
                this._handlersInitialized = true;

                window.addEventListener('online', () => {
                    if (this.dirtyItems.size > 0) {
                        this.scheduleSync();
                    }
                    if (this._addQueue.length > 0) {
                        this._processAddQueue();
                    }
                });

                window.addEventListener('beforeunload', (e) => {
                    if (this.dirtyItems.size > 0) {
                        this.sendBeacon();
                        e.preventDefault();
                        e.returnValue = 'You have unsaved inventory changes.';
                        return e.returnValue;
                    }
                });
            }
        },

        // === Inventory Management ===

        setInventory(itemId, newValue) {
            const item = this.items.find(i => i.id === itemId);
            if (!item) return;

            const clamped = Math.max(0, newValue);
            item.inventory_level = clamped;
            this.dirtyItems.add(itemId);
            this.scheduleSync();
        },

        adjustInventory(itemId, delta) {
            const item = this.items.find(i => i.id === itemId);
            if (!item) return;

            const current = parseFloat(item.inventory_level) || 0;
            const newValue = Math.max(0, current + delta);
            this.setInventory(itemId, newValue);
        },

        getInventory(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? parseFloat(item.inventory_level) || 0 : 0;
        },

        scheduleSync() {
            if (this.syncTimeout) {
                clearTimeout(this.syncTimeout);
            }
            this.syncTimeout = setTimeout(() => {
                this.sync();
            }, this.SYNC_DELAY);
        },

        async sync() {
            if (this.syncTimeout) {
                clearTimeout(this.syncTimeout);
                this.syncTimeout = null;
            }

            if (this.isSyncing || this.dirtyItems.size === 0) return;

            if (!navigator.onLine) return;

            this.isSyncing = true;

            // Snapshot the dirty items to send
            const itemsToSync = [...this.dirtyItems];
            const changes = itemsToSync.map(id => {
                const item = this.items.find(i => i.id === id);
                return item ? { listItemId: id, value: parseFloat(item.inventory_level) || 0 } : null;
            }).filter(Boolean);

            if (changes.length === 0) {
                this.isSyncing = false;
                return;
            }

            try {
                if (this.showComponentId) {
                    const result = await window.Livewire.find(this.showComponentId)
                        .call('batchUpdateInventory', changes);

                    if (result && result.success) {
                        // Clear synced items from dirty set
                        itemsToSync.forEach(id => this.dirtyItems.delete(id));
                    }
                }
            } catch (error) {
                console.error('Inventory sync failed:', error);
                // Items remain in dirty set for retry
                if (navigator.onLine) {
                    this.scheduleSync();
                }
            } finally {
                this.isSyncing = false;

                // If more items became dirty during sync, schedule another
                if (this.dirtyItems.size > 0) {
                    this.scheduleSync();
                }
            }
        },

        async flushSync() {
            if (this.syncTimeout) {
                clearTimeout(this.syncTimeout);
                this.syncTimeout = null;
            }

            if (this.dirtyItems.size > 0) {
                await this.sync();
            }
        },

        sendBeacon() {
            if (this.dirtyItems.size === 0) return;

            const changes = [...this.dirtyItems].map(id => {
                const item = this.items.find(i => i.id === id);
                return item ? { listItemId: id, value: parseFloat(item.inventory_level) || 0 } : null;
            }).filter(Boolean);

            if (changes.length === 0) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if ('sendBeacon' in navigator && csrfToken) {
                const blob = new Blob([JSON.stringify({
                    _token: csrfToken,
                    userListId: this.userListId,
                    changes: changes
                })], { type: 'application/json' });

                navigator.sendBeacon('/inventory/beacon', blob);
            }
        },

        // === Carousel Data ===

        getItemsWithInventory() {
            return this.items
                .filter(item => (item.inventory_level || 0) > 0)
                .sort((a, b) => {
                    // Sort by commodity, then by type priority, then by code
                    const commodityA = (a.commodity || '').toLowerCase();
                    const commodityB = (b.commodity || '').toLowerCase();
                    if (commodityA !== commodityB) return commodityA.localeCompare(commodityB);

                    // Priority: Regular PLU (0), Organic PLU (1), UPC (2)
                    const priorityA = a.item_type === 'plu' ? (a.organic ? 1 : 0) : 2;
                    const priorityB = b.item_type === 'plu' ? (b.organic ? 1 : 0) : 2;
                    if (priorityA !== priorityB) return priorityA - priorityB;

                    // Then by code
                    const codeA = a.item_type === 'plu' ? (a.plu || '').toString() : (a.name || '');
                    const codeB = b.item_type === 'plu' ? (b.plu || '').toString() : (b.name || '');
                    return codeA.localeCompare(codeB);
                });
        },

        // === Merge / Re-init ===

        mergeServerItems(serverItems) {
            const dirtyValues = {};
            this.dirtyItems.forEach(id => {
                const item = this.items.find(i => i.id === id);
                if (item) {
                    dirtyValues[id] = item.inventory_level;
                }
            });

            // Build new items list from server
            this.items = serverItems.map(serverItem => {
                // Preserve dirty inventory values
                if (dirtyValues.hasOwnProperty(serverItem.id)) {
                    return { ...serverItem, inventory_level: dirtyValues[serverItem.id] };
                }
                return serverItem;
            });
        },

        // === Filter Logic ===

        extractFilterOptions() {
            this.categories = [...new Set(this.items.map(item => item.category).filter(Boolean))].sort();
            this.commodities = [...new Set(this.items.map(item => item.commodity).filter(Boolean))].sort();
        },

        applyFilters() {
            this.filteredItems = this.items.filter(item => {
                let matchesCategory = true;
                let matchesCommodity = true;
                let matchesSearch = true;

                if (this.selectedCategory) {
                    matchesCategory = item.category === this.selectedCategory;
                }

                if (this.selectedCommodity) {
                    matchesCommodity = item.commodity === this.selectedCommodity;
                }

                if (this.localSearchTerm && this.localSearchTerm.trim()) {
                    matchesSearch = this.matchesSearchTerm(item, this.localSearchTerm.trim().toLowerCase());
                }

                return matchesCategory && matchesCommodity && matchesSearch;
            });
        },

        addItem(pluData, listId) {
            const isOrganic = pluData.organic || false;
            const exists = this.items.find(item =>
                item.plu_code_id === pluData.id &&
                item.organic === isOrganic
            );

            if (exists) {
                this.showNotification(`This ${isOrganic ? 'organic' : 'regular'} item is already in list`, 'info');
                return false;
            }

            this.persistItem(pluData, listId);
            this.showNotification('Adding item...', 'info');
            return true;
        },

        // Add queue state
        _addQueue: [],
        _addProcessing: false,

        async persistItem(pluData, listId) {
            this._addQueue.push({ pluData, listId });
            this._processAddQueue();
        },

        async _processAddQueue() {
            if (this._addProcessing || this._addQueue.length === 0) return;

            // If offline, wait — the 'online' handler will call us again
            if (!navigator.onLine) return;

            this._addProcessing = true;
            let addedCount = 0;

            while (this._addQueue.length > 0) {
                // Pause if we lost connection mid-queue
                if (!navigator.onLine) {
                    this.showNotification('Offline — items will be added when reconnected', 'info');
                    break;
                }

                const entry = this._addQueue.shift();
                const { pluData } = entry;
                try {
                    if (!this.showComponentId) {
                        console.error('No Show component ID available');
                        continue;
                    }
                    const result = await window.Livewire.find(this.showComponentId)
                        .call('addPLUCodeSilent', pluData.id, pluData.organic || false);

                    if (result && result.success && result.listItem) {
                        // Add to Alpine store
                        this.items.push(result.listItem);
                        this.extractFilterOptions();
                        this.applyFilters();
                        addedCount++;

                        // Notify search results UI
                        window.dispatchEvent(new CustomEvent('item-added-to-list', {
                            detail: {
                                pluCodeId: pluData.id,
                                organic: pluData.organic || false
                            }
                        }));
                        this.showNotification('Item added!', 'success');
                    } else if (result && !result.success) {
                        this.showNotification(result.message || 'Item already exists', 'info');
                    }
                } catch (error) {
                    console.error('Error adding item:', error);
                    // Network failure — put item back at front of queue for retry
                    this._addQueue.unshift(entry);
                    this.showNotification('Offline — items will be added when reconnected', 'info');
                    break;
                }
            }

            this._addProcessing = false;

            // After all queued adds, trigger one re-render for server-rendered rows
            if (addedCount > 0 && this.showComponentId) {
                window.Livewire.find(this.showComponentId).$refresh();
            }
        },

        removeItem(itemId) {
            const index = this.items.findIndex(item => item.id === itemId);
            if (index !== -1) {
                const item = this.items[index];

                this.items.splice(index, 1);
                this.dirtyItems.delete(itemId);
                this.extractFilterOptions();
                this.applyFilters();

                if (!item.isTemp) {
                    this.persistRemoval(item.plu_code_id);
                }

                this.showNotification('Item removed', 'success');
            }
        },

        removeItemById(id) {
            const index = this.items.findIndex(item => item.id === id);
            if (index !== -1) {
                this.items.splice(index, 1);
                this.dirtyItems.delete(id);
                this.applyFilters();
            }
        },

        async persistRemoval(pluCodeId) {
            try {
                if (this.showComponentId) {
                    await window.Livewire.find(this.showComponentId)
                        .call('removePLUCodeHeadless', pluCodeId);
                }
            } catch (error) {
                console.error('Error removing item:', error);
                this.showNotification('Failed to remove item from server', 'error');
            }
        },

        updateInventory(itemId, delta) {
            this.adjustInventory(itemId, delta);
        },

        updateItem(itemId, updates) {
            const index = this.items.findIndex(i => i.id === itemId);
            if (index !== -1) {
                Object.assign(this.items[index], updates);
                this.applyFilters();
                this.persistItemUpdate(itemId, updates);
            }
        },

        async persistItemUpdate(itemId, updates) {
            try {
                if (this.showComponentId) {
                    await window.Livewire.find(this.showComponentId)
                        .call('updateListItemHeadless', itemId, updates);
                }
            } catch (error) {
                console.error('Error updating item:', error);
                this.showNotification('Failed to update item', 'error');
            }
        },

        clearAllInventory() {
            this.items.forEach(item => {
                item.inventory_level = 0;
            });
            this.dirtyItems.clear();
            if (this.syncTimeout) {
                clearTimeout(this.syncTimeout);
                this.syncTimeout = null;
            }
        },

        showNotification(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { message, type }
            }));
        },

        setFilter(type, value) {
            if (type === 'category') {
                this.selectedCategory = value;
            } else if (type === 'commodity') {
                this.selectedCommodity = value;
            }
            this.applyFilters();
        },

        resetFilters() {
            this.selectedCategory = '';
            this.selectedCommodity = '';
            this.applyFilters();
        },

        matchesSearchTerm(item, searchTerm) {
            if (item.item_type === 'plu') {
                if (item.plu && item.plu.toString().includes(searchTerm)) return true;
                if (item.organic && ('9' + item.plu).includes(searchTerm)) return true;
                if (item.variety && item.variety.toLowerCase().includes(searchTerm)) return true;
                if (item.commodity && item.commodity.toLowerCase().includes(searchTerm)) return true;
                if (item.category && item.category.toLowerCase().includes(searchTerm)) return true;
                if (item.size && item.size.toLowerCase().includes(searchTerm)) return true;
            }

            if (item.item_type === 'upc') {
                if (item.upc && item.upc.toString().includes(searchTerm)) return true;
                if (item.name && item.name.toLowerCase().includes(searchTerm)) return true;
                if (item.brand && item.brand.toLowerCase().includes(searchTerm)) return true;
                if (item.commodity && item.commodity.toLowerCase().includes(searchTerm)) return true;
                if (item.category && item.category.toLowerCase().includes(searchTerm)) return true;
            }

            return false;
        },

        setLocalSearch(searchTerm) {
            this.localSearchTerm = searchTerm;
            this.applyFilters();
        },

        clearLocalSearch() {
            this.localSearchTerm = '';
            this.applyFilters();
        },

        // Check if a specific item should be visible based on current filters + search
        isItemVisible(itemId) {
            const item = this.items.find(i => i.id === itemId);
            if (!item) return true;

            // Check category filter
            if (this.selectedCategory && item.category !== this.selectedCategory) {
                return false;
            }

            // Check commodity filter
            if (this.selectedCommodity && item.commodity !== this.selectedCommodity) {
                return false;
            }

            // Check search term
            if (this.localSearchTerm && this.localSearchTerm.trim()) {
                return this.matchesSearchTerm(item, this.localSearchTerm.trim().toLowerCase());
            }

            return true;
        },

        getVisibleItemCount() {
            return this.items.filter(item => this.isItemVisible(item.id)).length;
        },

        isCommodityVisible(commodityName) {
            return this.items.some(item =>
                item.commodity === commodityName && this.isItemVisible(item.id)
            );
        }
    });
});
