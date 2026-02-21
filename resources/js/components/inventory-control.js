// Per-item inventory control Alpine.js component
// Reads/writes from Alpine.store('listManager') — no Livewire round-trips
document.addEventListener('alpine:init', () => {
    // Global editing state: only one item can be in edit mode at a time
    if (!window._inventoryEditState) {
        window._inventoryEditState = {
            currentEditor: null,
            closeAll() {
                if (this.currentEditor) {
                    this.currentEditor.cancelEdit();
                }
            }
        };
    }

    Alpine.data('inventoryControl', (itemId) => ({
        itemId: itemId,
        isEditing: false,
        editValue: '',

        get value() {
            return Alpine.store('listManager').getInventory(this.itemId);
        },

        increment() {
            this.saveEditIfOpen();
            Alpine.store('listManager').adjustInventory(this.itemId, 1);
            this.haptic();
        },

        decrement() {
            if (this.value <= 0) return;
            this.saveEditIfOpen();
            Alpine.store('listManager').adjustInventory(this.itemId, -1);
            this.haptic();
        },

        addHalf() {
            this.saveEditIfOpen();
            Alpine.store('listManager').adjustInventory(this.itemId, 0.5);
            this.haptic();
        },

        subHalf() {
            if (this.value < 0.5) return;
            this.saveEditIfOpen();
            Alpine.store('listManager').adjustInventory(this.itemId, -0.5);
            this.haptic();
        },

        toggleHalf() {
            this.saveEditIfOpen();
            if (this.value % 1 === 0.5) {
                this.subHalf();
            } else {
                this.addHalf();
            }
        },

        clear() {
            this.saveEditIfOpen();
            Alpine.store('listManager').setInventory(this.itemId, 0);
            this.haptic();
        },

        startEditing() {
            // Close any other open editor
            if (window._inventoryEditState.currentEditor && window._inventoryEditState.currentEditor !== this) {
                window._inventoryEditState.currentEditor.cancelEdit();
            }

            this.editValue = this.value % 1 === 0 ? this.value.toString() : this.value.toFixed(1);
            this.isEditing = true;
            window._inventoryEditState.currentEditor = this;
        },

        saveEdit(value) {
            const raw = parseFloat(value) || 0;
            const numValue = Math.round(raw * 2) / 2;
            if (numValue >= 0) {
                Alpine.store('listManager').setInventory(this.itemId, numValue);
            }
            this.isEditing = false;

            if (window._inventoryEditState.currentEditor === this) {
                window._inventoryEditState.currentEditor = null;
            }
        },

        cancelEdit() {
            this.isEditing = false;
            if (window._inventoryEditState.currentEditor === this) {
                window._inventoryEditState.currentEditor = null;
            }
        },

        saveEditIfOpen() {
            if (this.isEditing) {
                this.saveEdit(this.editValue);
            }
        },

        haptic() {
            if (navigator.vibrate) {
                navigator.vibrate(10);
            }
        }
    }));
});
