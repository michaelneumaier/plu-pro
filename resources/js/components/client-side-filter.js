// Register Alpine.js component when Alpine is available
if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Alpine) {
            window.Alpine.data('clientSideFilter', ({ allItems, categories, commodities }) => ({
                // Filter state
                selectedCategory: '',
                selectedCommodity: '',
                
                // Original data
                allItems: allItems || [],
                categories: categories || [],
                commodities: commodities || [],
                
                // Filtered results
                filteredItems: allItems || [],
                
                init() {
                    // Initialize with all items
                    this.filteredItems = this.allItems;
                    
                    // Watch for filter changes
                    this.$watch('selectedCategory', () => this.applyFilters());
                    this.$watch('selectedCommodity', () => this.applyFilters());
                },
                
                applyFilters() {
                    let filtered = this.allItems;
                    
                    // Apply category filter
                    if (this.selectedCategory && this.selectedCategory !== '') {
                        filtered = filtered.filter(item => item.category === this.selectedCategory);
                    }
                    
                    // Apply commodity filter
                    if (this.selectedCommodity && this.selectedCommodity !== '') {
                        filtered = filtered.filter(item => item.commodity === this.selectedCommodity);
                    }
                    
                    this.filteredItems = filtered;
                    
                    // Hide/show PLU items based on filtering
                    this.updateVisibleItems();
                },
                
                updateVisibleItems() {
                    // Get all PLU items on the page
                    const pluItems = document.querySelectorAll('[data-plu-id]');
                    
                    pluItems.forEach(item => {
                        const pluId = item.getAttribute('data-plu-id');
                        const shouldShow = this.filteredItems.some(filteredItem => 
                            filteredItem.plu_code_id == pluId
                        );
                        
                        if (shouldShow) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                },
                
                resetFilters() {
                    this.selectedCategory = '';
                    this.selectedCommodity = '';
                    this.applyFilters();
                },
                
                updateCategory(category) {
                    this.selectedCategory = category;
                },
                
                updateCommodity(commodity) {
                    this.selectedCommodity = commodity;
                }
            }));
        }
    });
}

export function clientSideFilter({ allItems, categories, commodities }) {
    return {
        // Filter state
        selectedCategory: '',
        selectedCommodity: '',
        
        // Original data
        allItems: allItems || [],
        categories: categories || [],
        commodities: commodities || [],
        
        // Filtered results
        filteredItems: allItems || [],
        
        init() {
            // Initialize with all items
            this.filteredItems = this.allItems;
            
            // Watch for filter changes
            this.$watch('selectedCategory', () => this.applyFilters());
            this.$watch('selectedCommodity', () => this.applyFilters());
        },
        
        applyFilters() {
            let filtered = this.allItems;
            
            // Apply category filter
            if (this.selectedCategory && this.selectedCategory !== '') {
                filtered = filtered.filter(item => item.category === this.selectedCategory);
            }
            
            // Apply commodity filter
            if (this.selectedCommodity && this.selectedCommodity !== '') {
                filtered = filtered.filter(item => item.commodity === this.selectedCommodity);
            }
            
            this.filteredItems = filtered;
            
            // Hide/show PLU items based on filtering
            this.updateVisibleItems();
        },
        
        updateVisibleItems() {
            // Get all PLU items on the page
            const pluItems = document.querySelectorAll('[data-plu-id]');
            
            pluItems.forEach(item => {
                const pluId = item.getAttribute('data-plu-id');
                const shouldShow = this.filteredItems.some(filteredItem => 
                    filteredItem.plu_code_id == pluId
                );
                
                if (shouldShow) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        },
        
        resetFilters() {
            this.selectedCategory = '';
            this.selectedCommodity = '';
            this.applyFilters();
        },
        
        updateCategory(category) {
            this.selectedCategory = category;
        },
        
        updateCommodity(commodity) {
            this.selectedCommodity = commodity;
        }
    }
}