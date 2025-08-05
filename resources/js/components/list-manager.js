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
        
        init(initialItems = []) {
            this.items = initialItems;
            this.filteredItems = [...this.items];
            this.extractFilterOptions();
            this.applyFilters();
        },
        
        extractFilterOptions() {
            // Extract unique categories and commodities
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
            // Check if this specific version (regular or organic) already exists
            const isOrganic = pluData.organic || false;
            const exists = this.items.find(item => 
                item.plu_code_id === pluData.id && 
                item.organic === isOrganic
            );
            
            if (exists) {
                this.showNotification(`This ${isOrganic ? 'organic' : 'regular'} item is already in list`, 'info');
                return false;
            }
            
            // Don't add optimistically - let Livewire handle the server-side rendering
            // Just trigger the server call and let natural Livewire refresh handle the UI
            this.persistItem(pluData, listId);
            
            this.showNotification('Adding item...', 'info');
            return true;
        },
        
        async persistItem(pluData, listId) {
            try {
                // Trigger the hidden button via custom event
                window.dispatchEvent(new CustomEvent('trigger-add-item', {
                    detail: {
                        pluCodeId: pluData.id,
                        organic: pluData.organic || false
                    }
                }));
                
                // Livewire will handle the re-rendering and success message
                
            } catch (error) {
                console.error('Error persisting item:', error);
                this.showNotification('Failed to add item', 'error');
            }
        },
        
        removeItem(itemId) {
            const index = this.items.findIndex(item => item.id === itemId);
            if (index !== -1) {
                const item = this.items[index];
                
                // Optimistic update
                this.items.splice(index, 1);
                this.extractFilterOptions();
                this.applyFilters();
                
                // Persist to server if not temporary
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
                this.applyFilters();
            }
        },
        
        async persistRemoval(pluCodeId) {
            try {
                await window.Livewire.find(
                    document.querySelector('[wire\\:id]').getAttribute('wire:id')
                ).call('removePLUCodeHeadless', pluCodeId);
            } catch (error) {
                console.error('Error removing item:', error);
                this.showNotification('Failed to remove item from server', 'error');
            }
        },
        
        updateInventory(itemId, delta) {
            const item = this.items.find(i => i.id === itemId);
            if (item) {
                const newLevel = Math.max(0, (item.inventory_level || 0) + delta);
                item.inventory_level = newLevel;
                this.applyFilters(); // Refresh the display
                
                // The inventory component will handle the actual server sync
                // We just need to update our local state to match
            }
        },
        
        updateItem(itemId, updates) {
            const index = this.items.findIndex(i => i.id === itemId);
            if (index !== -1) {
                // Update the item
                Object.assign(this.items[index], updates);
                this.applyFilters();
                
                // TODO: Sync to server
                this.persistItemUpdate(itemId, updates);
            }
        },
        
        async persistItemUpdate(itemId, updates) {
            try {
                // Call server to update item
                await window.Livewire.find(
                    document.querySelector('[wire\\:id]').getAttribute('wire:id')
                ).call('updateListItemHeadless', itemId, updates);
            } catch (error) {
                console.error('Error updating item:', error);
                this.showNotification('Failed to update item', 'error');
            }
        },
        
        clearAllInventory() {
            this.items.forEach(item => {
                item.inventory_level = 0;
            });
            // Server sync handled separately
        },
        
        showNotification(message, type = 'info') {
            // Dispatch custom event for notification handling
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
            // Handle PLU items
            if (item.item_type === 'plu') {
                // Search in regular PLU code
                if (item.plu && item.plu.toString().includes(searchTerm)) {
                    return true;
                }
                
                // Search in organic PLU code (9 + regular PLU)
                if (item.organic && ('9' + item.plu).includes(searchTerm)) {
                    return true;
                }
                
                // Search in variety
                if (item.variety && item.variety.toLowerCase().includes(searchTerm)) {
                    return true;
                }
                
                // Search in commodity
                if (item.commodity && item.commodity.toLowerCase().includes(searchTerm)) {
                    return true;
                }
                
                // Search in category
                if (item.category && item.category.toLowerCase().includes(searchTerm)) {
                    return true;
                }
                
                // Search in size
                if (item.size && item.size.toLowerCase().includes(searchTerm)) {
                    return true;
                }
            }
            
            // Handle UPC items
            if (item.item_type === 'upc') {
                // Search in UPC code
                if (item.upc && item.upc.toString().includes(searchTerm)) {
                    return true;
                }
                
                // Search in product name
                if (item.name && item.name.toLowerCase().includes(searchTerm)) {
                    return true;
                }
                
                // Search in brand
                if (item.brand && item.brand.toLowerCase().includes(searchTerm)) {
                    return true;
                }
                
                // Search in commodity
                if (item.commodity && item.commodity.toLowerCase().includes(searchTerm)) {
                    return true;
                }
                
                // Search in category
                if (item.category && item.category.toLowerCase().includes(searchTerm)) {
                    return true;
                }
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
        
        // Check if a specific item should be visible based on current search
        isItemVisible(itemId) {
            if (!this.localSearchTerm || !this.localSearchTerm.trim()) {
                return true; // Show all items when no search term
            }
            
            // Find the corresponding DOM element
            const rowElement = document.querySelector(`[data-item-id="${itemId}"]`);
            if (!rowElement) {
                return true; // If we can't find the element, show it to be safe
            }
            
            const searchContent = rowElement.getAttribute('data-search-content');
            if (!searchContent) {
                return true; // If no search content, show it to be safe
            }
            
            // Perform the same search logic as the JavaScript version
            const searchTerm = this.localSearchTerm.trim().toLowerCase();
            return searchContent.toLowerCase().includes(searchTerm);
        },
        
        // Get count of visible items for search results
        getVisibleItemCount() {
            if (!this.localSearchTerm || !this.localSearchTerm.trim()) {
                return document.querySelectorAll('.list-item-row[data-item-id]').length;
            }
            
            const searchTerm = this.localSearchTerm.trim().toLowerCase();
            let count = 0;
            
            document.querySelectorAll('.list-item-row[data-item-id]').forEach(row => {
                const searchContent = row.getAttribute('data-search-content');
                if (searchContent && searchContent.toLowerCase().includes(searchTerm)) {
                    count++;
                }
            });
            
            return count;
        }
    });
});