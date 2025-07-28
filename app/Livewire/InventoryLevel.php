<?php

namespace App\Livewire;

use App\Models\ListItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class InventoryLevel extends Component
{
    public $listItemId;

    public $userListId;

    public $pluCode;

    public bool $isEditing = false;

    public string $editableValue = '';

    public $inventoryLevel;

    protected $listeners = [
        'filter-changed' => '$refresh',
    ];

    public function boot()
    {
        $this->refreshValue();
    }

    public function mount($listItemId, $userListId, $pluCode = null)
    {
        $this->listItemId = $listItemId;
        $this->userListId = $userListId;
        $this->pluCode = $pluCode;
        $this->refreshValue();
    }

    protected function getListItem()
    {
        return ListItem::where('id', $this->listItemId)
            ->where('user_list_id', $this->userListId)
            ->first();
    }

    protected function refreshValue()
    {
        $listItem = $this->getListItem();
        if ($listItem) {
            $this->inventoryLevel = $listItem->inventory_level;
            $this->editableValue = number_format($listItem->inventory_level, 1);
        }
    }

    /**
     * Update inventory with conflict resolution
     * This method is called from Alpine.js component via $wire
     */
    public function updateInventory($listItemId, $delta, $clientTimestamp)
    {
        // Validate inputs
        if (! is_numeric($delta) || ! is_numeric($clientTimestamp)) {
            return [
                'success' => false,
                'error' => 'Invalid input parameters',
            ];
        }

        DB::beginTransaction();

        try {
            // Lock the row for update to prevent race conditions
            $listItem = ListItem::lockForUpdate()
                ->where('id', $listItemId)
                ->where('user_list_id', $this->userListId)
                ->first();

            if (! $listItem) {
                DB::rollback();

                return [
                    'success' => false,
                    'error' => 'Item not found',
                ];
            }

            // Check for conflicts based on last update time
            $lastUpdateTimestamp = $listItem->updated_at->timestamp * 1000;

            if ($lastUpdateTimestamp > $clientTimestamp) {
                // Conflict detected - return current server value
                DB::rollback();

                return [
                    'success' => false,
                    'conflict' => true,
                    'serverValue' => (float) $listItem->inventory_level,
                    'serverTimestamp' => $lastUpdateTimestamp,
                ];
            }

            // Apply the delta
            $newValue = max(0, $listItem->inventory_level + $delta);
            $listItem->inventory_level = $newValue;
            $listItem->save();

            DB::commit();

            // Update component state
            $this->inventoryLevel = $newValue;

            return [
                'success' => true,
                'newValue' => (float) $newValue,
                'timestamp' => now()->timestamp * 1000,
            ];

        } catch (\Exception $e) {
            DB::rollback();

            \Log::error('Inventory update failed', [
                'error' => $e->getMessage(),
                'listItemId' => $listItemId,
                'delta' => $delta,
            ]);

            return [
                'success' => false,
                'error' => 'Update failed: '.$e->getMessage(),
            ];
        }
    }

    // Legacy methods for backward compatibility
    public function startEditing()
    {
        $this->isEditing = true;
        $this->refreshValue();
    }

    public function saveEdit()
    {
        $this->validate([
            'editableValue' => ['required', 'numeric', 'min:0', 'regex:/^\d*\.?[05]$/'],
        ], [
            'editableValue.regex' => 'Value must be in .5 increments.',
        ]);

        $newValue = (float) $this->editableValue;
        $delta = $newValue - $this->inventoryLevel;

        $result = $this->updateInventory($this->listItemId, $delta, now()->timestamp * 1000);

        if (! $result['success']) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update inventory. Please try again.',
            ]);
        }

        $this->isEditing = false;
        $this->resetValidation();
    }

    public function increment()
    {
        $result = $this->updateInventory($this->listItemId, 1, now()->timestamp * 1000);

        if (! $result['success']) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update inventory. Please try again.',
            ]);
        }
    }

    public function decrement()
    {
        if ($this->inventoryLevel >= 1) {
            $result = $this->updateInventory($this->listItemId, -1, now()->timestamp * 1000);

            if (! $result['success']) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to update inventory. Please try again.',
                ]);
            }
        }
    }

    public function addHalf()
    {
        $result = $this->updateInventory($this->listItemId, 0.5, now()->timestamp * 1000);

        if (! $result['success']) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update inventory. Please try again.',
            ]);
        }
    }

    public function subtractHalf()
    {
        if (($this->inventoryLevel - 0.5) >= 0) {
            $result = $this->updateInventory($this->listItemId, -0.5, now()->timestamp * 1000);

            if (! $result['success']) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => 'Failed to update inventory. Please try again.',
                ]);
            }
        }
    }

    public function setValue($value)
    {
        $numValue = (float) $value;
        $delta = $numValue - $this->inventoryLevel;

        $result = $this->updateInventory($this->listItemId, $delta, now()->timestamp * 1000);

        if ($result['success']) {
            // Emit event to sync frontend state
            $this->dispatch('value-updated', $this->inventoryLevel);
        } else {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to update inventory. Please try again.',
            ]);
        }
    }

    public function render()
    {
        $listItem = $this->getListItem();

        if (! $listItem) {
            return null;
        }

        return view('livewire.inventory-level-working', [
            'listItem' => $listItem,
            'currentValue' => $this->inventoryLevel,
            'pluCode' => $this->pluCode,
        ]);
    }

    public function dehydrate()
    {
        // Ensure clean state when component is serialized
        $this->isEditing = false;
    }
}
