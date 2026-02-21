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

        // Delete queue state
        _deleteQueue: [],
        _deleteProcessing: false,
        _deletedItemIds: [],

        // PWA offline state
        _isPWA: false,

        get canUseLivewire() {
            return navigator.onLine && this.showComponentId && window.Livewire;
        },

        init(initialItems = [], userListId = null, componentId = null) {
            this._isPWA = document.documentElement.classList.contains('pwa-standalone');

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

            // Rehydrate from IndexedDB — local inventory is source of truth
            if (this._isPWA && userListId) {
                this._rehydrateFromIndexedDB(userListId);
            }

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
                    if (this._organicQueue.length > 0) {
                        this._processOrganicQueue();
                    }
                    if (this._deleteQueue.length > 0) {
                        this._processDeleteQueue();
                    }
                });

                window.addEventListener('beforeunload', (e) => {
                    const hasUnsaved = this.dirtyItems.size > 0
                        || this._organicQueue.length > 0
                        || this._deleteQueue.length > 0;
                    if (hasUnsaved) {
                        if (this.dirtyItems.size > 0) {
                            this.sendBeacon();
                        }
                        e.preventDefault();
                        e.returnValue = 'You have unsaved changes.';
                        return e.returnValue;
                    }
                });

                // Listen for service worker sync messages
                if (navigator.serviceWorker) {
                    navigator.serviceWorker.addEventListener('message', (event) => {
                        if (event.data?.type === 'SYNC_INVENTORY') {
                            this.flushSync();
                        }
                    });
                }
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

            // Persist to IndexedDB immediately (PWA safety net)
            if (this._isPWA && window.OfflineDB) {
                window.OfflineDB.updateListItemInventory(this.userListId, itemId, clamped)
                    .catch(e => console.error('IndexedDB inventory save failed:', e));
            }
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

        formatInventory(val) {
            const num = parseFloat(val) || 0;
            return num % 1 === 0 ? num.toString() : num.toFixed(1);
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

            let synced = false;

            try {
                // Try Livewire first (primary path)
                if (this.canUseLivewire) {
                    const result = await window.Livewire.find(this.showComponentId)
                        .call('batchUpdateInventory', changes);

                    if (result && result.success) {
                        synced = true;
                    }
                }
            } catch (error) {
                console.warn('Livewire sync failed, trying API fallback:', error.message);
            }

            // Fallback: direct API call (works even with stale Livewire snapshot)
            if (!synced && navigator.onLine) {
                try {
                    synced = await this._syncViaAPI(changes);
                } catch (error) {
                    console.error('API sync fallback also failed:', error);
                }
            }

            if (synced) {
                // Clear synced items from dirty set
                itemsToSync.forEach(id => this.dirtyItems.delete(id));

                // Mark as synced in IndexedDB
                if (this._isPWA && window.OfflineDB) {
                    window.OfflineDB.markItemsSynced(itemsToSync)
                        .catch(e => console.error('IndexedDB markSynced failed:', e));
                }
            } else if (navigator.onLine) {
                // Both paths failed but we're online — retry later
                this.scheduleSync();
            }

            this.isSyncing = false;

            // If more items became dirty during sync, schedule another
            if (this.dirtyItems.size > 0) {
                this.scheduleSync();
            }
        },

        // Fallback sync via fetch to /inventory/beacon (bypasses Livewire)
        async _syncViaAPI(changes) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken || !this.userListId) return false;

            const response = await fetch('/inventory/beacon', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    _token: csrfToken,
                    userListId: this.userListId,
                    changes: changes,
                }),
            });

            return response.ok;
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

            // Collect pending organic states from queue
            const pendingOrganic = {};
            this._organicQueue.forEach(entry => {
                pendingOrganic[entry.itemId] = entry.organic;
            });

            // Build new items list from server, filtering out items pending deletion
            this.items = serverItems
                .filter(si => !this._deletedItemIds.includes(si.id))
                .map(serverItem => {
                    const merged = { ...serverItem };
                    // Preserve dirty inventory values
                    if (dirtyValues.hasOwnProperty(serverItem.id)) {
                        merged.inventory_level = dirtyValues[serverItem.id];
                    }
                    // Preserve pending organic state
                    if (pendingOrganic.hasOwnProperty(serverItem.id)) {
                        merged.organic = pendingOrganic[serverItem.id];
                    }
                    return merged;
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

        // Organic toggle queue
        _organicQueue: [],
        _organicProcessing: false,

        toggleOrganic(itemId) {
            const item = this.items.find(i => i.id === itemId);
            if (!item) return;

            // Dual-version guard: prevent creating a duplicate
            if (item.item_type === 'plu') {
                const duplicate = this.items.find(i =>
                    i.plu_code_id === item.plu_code_id && i.id !== itemId &&
                    i.item_type === 'plu' && i.organic === !item.organic
                );
                if (duplicate) {
                    this.showNotification('Both organic and regular versions already exist', 'info');
                    return;
                }
            }

            // Optimistic toggle
            item.organic = !item.organic;
            this.applyFilters();

            // Deduplicate: replace any existing queue entry for this item
            const existingIndex = this._organicQueue.findIndex(e => e.itemId === itemId);
            if (existingIndex !== -1) {
                this._organicQueue[existingIndex].organic = item.organic;
            } else {
                this._organicQueue.push({ itemId, organic: item.organic });
            }

            this._processOrganicQueue();
        },

        async _processOrganicQueue() {
            if (this._organicProcessing || this._organicQueue.length === 0) return;
            if (!navigator.onLine) return;

            this._organicProcessing = true;

            while (this._organicQueue.length > 0) {
                if (!navigator.onLine) {
                    this.showNotification('Offline — changes will sync when reconnected', 'info');
                    break;
                }

                const entry = this._organicQueue.shift();
                try {
                    if (!this.showComponentId) {
                        console.error('No Show component ID available');
                        continue;
                    }
                    await window.Livewire.find(this.showComponentId)
                        .call('setOrganic', entry.itemId, entry.organic);
                } catch (error) {
                    console.error('Error setting organic:', error);
                    // Put back at front of queue for retry
                    this._organicQueue.unshift(entry);
                    this.showNotification('Offline — changes will sync when reconnected', 'info');
                    break;
                }
            }

            this._organicProcessing = false;
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

        deleteItem(listItemId) {
            const index = this.items.findIndex(i => i.id === listItemId);
            if (index === -1) return;

            // Track as deleted for visibility filtering (array for Alpine reactivity)
            this._deletedItemIds.push(listItemId);

            // Remove from items array
            this.items.splice(index, 1);

            // Clean up any pending queues/dirty state for this item
            this.dirtyItems.delete(listItemId);
            this._organicQueue = this._organicQueue.filter(e => e.itemId !== listItemId);

            // Update filters
            this.extractFilterOptions();
            this.applyFilters();

            // Queue server deletion
            this._deleteQueue.push({ listItemId });
            this._processDeleteQueue();

            this.showNotification('Item removed', 'success');
        },

        async _processDeleteQueue() {
            if (this._deleteProcessing || this._deleteQueue.length === 0) return;
            if (!navigator.onLine) return;

            this._deleteProcessing = true;

            while (this._deleteQueue.length > 0) {
                if (!navigator.onLine) {
                    this.showNotification('Offline — deletions will sync when reconnected', 'info');
                    break;
                }

                const entry = this._deleteQueue.shift();
                try {
                    if (!this.showComponentId) {
                        console.error('No Show component ID available');
                        continue;
                    }
                    await window.Livewire.find(this.showComponentId)
                        .call('removeListItemSilent', entry.listItemId);

                    // Successfully deleted on server — safe to clear from tracking array
                    this._deletedItemIds = this._deletedItemIds.filter(id => id !== entry.listItemId);
                } catch (error) {
                    console.error('Error deleting item:', error);
                    this._deleteQueue.unshift(entry);
                    this.showNotification('Offline — deletions will sync when reconnected', 'info');
                    break;
                }
            }

            this._deleteProcessing = false;
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
                this.dirtyItems.add(item.id);
            });

            // Persist zeroed values to IndexedDB
            if (this._isPWA && window.OfflineDB && this.userListId) {
                const itemsToSave = this.items.map(item => ({
                    ...item,
                    userListId: this.userListId,
                    lastModified: Date.now(),
                }));
                window.OfflineDB.saveListItems(this.userListId, itemsToSave)
                    .catch(e => console.error('IndexedDB clear save failed:', e));
            }

            // Schedule sync to push zeros to server
            this.scheduleSync();
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
            if (this._deletedItemIds.includes(itemId)) return false;

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
        },

        isItemOrganic(itemId) {
            const item = this.items.find(i => i.id === itemId);
            return item ? item.organic : false;
        },

        hasDualVersion(pluCodeId) {
            const items = this.items.filter(i => i.plu_code_id === pluCodeId && i.item_type === 'plu');
            return items.some(i => i.organic) && items.some(i => !i.organic);
        },

        // === IndexedDB Persistence (PWA offline support) ===

        async _rehydrateFromIndexedDB(listId) {
            if (!window.OfflineDB) return;

            try {
                const dbItems = await window.OfflineDB.getListItems(listId);
                if (!dbItems || dbItems.length === 0) {
                    // No IndexedDB data yet — save current server data as baseline
                    this._saveToIndexedDB(listId);
                    return;
                }

                // Build a lookup of IndexedDB items by ID
                const dbMap = {};
                for (const dbItem of dbItems) {
                    dbMap[dbItem.id] = dbItem;
                }

                // For each item, check if IndexedDB has a locally-modified value
                let rehydratedCount = 0;
                for (const item of this.items) {
                    const dbItem = dbMap[item.id];
                    if (dbItem && dbItem.lastModified) {
                        // IndexedDB has an unsynced local value — use it
                        const dbLevel = parseFloat(dbItem.inventory_level) || 0;
                        const serverLevel = parseFloat(item.inventory_level) || 0;
                        if (dbLevel !== serverLevel) {
                            item.inventory_level = dbLevel;
                            this.dirtyItems.add(item.id);
                            rehydratedCount++;
                        }
                    }
                }

                if (rehydratedCount > 0) {
                    this.applyFilters();
                    // Schedule sync to push rehydrated values to server
                    if (navigator.onLine) {
                        this.scheduleSync();
                    }
                }

                // If online and no rehydrated items, save fresh server data to IndexedDB
                if (rehydratedCount === 0 && navigator.onLine) {
                    this._saveToIndexedDB(listId);
                }
            } catch (e) {
                console.error('IndexedDB rehydration failed:', e);
            }
        },

        async _saveToIndexedDB(listId) {
            if (!window.OfflineDB || !listId) return;

            try {
                const itemsToSave = this.items.map(item => ({
                    ...item,
                    userListId: listId,
                }));
                await window.OfflineDB.saveListItems(listId, itemsToSave);
            } catch (e) {
                console.error('IndexedDB save failed:', e);
            }
        },
    });
});
