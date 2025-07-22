import Alpine from 'alpinejs';

Alpine.data('virtualScroll', (config = {}) => ({
    // Configuration
    itemHeight: config.itemHeight || 120, // Height of each item in pixels
    overscan: config.overscan || 3, // Number of items to render outside viewport
    
    // State
    items: [],
    visibleItems: [],
    scrollTop: 0,
    containerHeight: 0,
    totalHeight: 0,
    startIndex: 0,
    endIndex: 0,
    
    init() {
        // Set up container
        this.$el.style.position = 'relative';
        this.$el.style.overflow = 'auto';
        
        // Calculate container height
        this.containerHeight = this.$el.clientHeight;
        
        // Set up scroll listener with throttling
        let scrollTimeout;
        this.$el.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.handleScroll();
            }, 10);
        });
        
        // Set up resize observer
        const resizeObserver = new ResizeObserver(() => {
            this.containerHeight = this.$el.clientHeight;
            this.updateVisibleItems();
        });
        resizeObserver.observe(this.$el);
        
        // Watch for items changes
        this.$watch('items', () => {
            this.totalHeight = this.items.length * this.itemHeight;
            this.updateVisibleItems();
        });
        
        // Initial render
        this.updateVisibleItems();
    },
    
    handleScroll() {
        this.scrollTop = this.$el.scrollTop;
        this.updateVisibleItems();
    },
    
    updateVisibleItems() {
        // Calculate visible range
        const visibleStart = Math.floor(this.scrollTop / this.itemHeight);
        const visibleEnd = Math.ceil((this.scrollTop + this.containerHeight) / this.itemHeight);
        
        // Add overscan
        this.startIndex = Math.max(0, visibleStart - this.overscan);
        this.endIndex = Math.min(this.items.length - 1, visibleEnd + this.overscan);
        
        // Update visible items
        this.visibleItems = [];
        for (let i = this.startIndex; i <= this.endIndex; i++) {
            if (this.items[i]) {
                this.visibleItems.push({
                    ...this.items[i],
                    _virtualIndex: i,
                    _virtualTop: i * this.itemHeight
                });
            }
        }
    },
    
    setItems(newItems) {
        this.items = newItems;
    },
    
    scrollToIndex(index) {
        const targetTop = index * this.itemHeight;
        this.$el.scrollTop = targetTop;
    },
    
    scrollToItem(item) {
        const index = this.items.findIndex(i => i.id === item.id);
        if (index >= 0) {
            this.scrollToIndex(index);
        }
    }
}));

// List component with virtual scrolling
Alpine.data('virtualList', () => ({
    items: [],
    loading: false,
    searchTerm: '',
    
    async init() {
        // Load initial items
        await this.loadItems();
        
        // Set up search debouncing
        this.$watch('searchTerm', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.filterItems();
            }, 300);
        });
    },
    
    async loadItems() {
        this.loading = true;
        try {
            // This would be replaced with actual data loading
            // For now, using the items passed from Livewire
            const virtualScroll = this.$refs.virtualScroll;
            if (virtualScroll && virtualScroll.setItems) {
                virtualScroll.setItems(this.items);
            }
        } finally {
            this.loading = false;
        }
    },
    
    filterItems() {
        if (!this.searchTerm) {
            this.$refs.virtualScroll.setItems(this.items);
            return;
        }
        
        const filtered = this.items.filter(item => {
            const searchLower = this.searchTerm.toLowerCase();
            return (
                item.plu_code.toLowerCase().includes(searchLower) ||
                item.commodity.toLowerCase().includes(searchLower) ||
                item.variety.toLowerCase().includes(searchLower)
            );
        });
        
        this.$refs.virtualScroll.setItems(filtered);
    }
}));