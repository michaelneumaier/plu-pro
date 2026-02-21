<?php

namespace App\Livewire\Lists;

use App\Models\UserList;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class SharedView extends Component
{
    public UserList $userList;

    public Collection $listItems;

    public Collection $allListItems; // For copying - includes items with 0 inventory

    // Copy functionality
    public bool $showCopyModal = false;

    public string $customListName = '';

    public function mount($shareCode)
    {
        // Find the list by share code and ensure it's public
        $this->userList = UserList::where('share_code', $shareCode)
            ->where('is_public', true)
            ->firstOrFail();

        $this->loadListItems();
    }

    protected function loadListItems()
    {
        // Load ALL list items (both PLU and UPC) for copying purposes
        $allItems = $this->userList->listItems()
            ->with(['pluCode', 'upcCode'])
            ->get();

        // Sort all items with a single combined sort key: commodity + sort_priority + code
        // Order: Regular PLU, Organic PLU, UPC
        $this->allListItems = $allItems->sortBy(function ($item) {
            if ($item->item_type === 'plu' && $item->pluCode) {
                $commodity = $item->pluCode->commodity;
                $code = str_pad($item->pluCode->plu, 10, '0', STR_PAD_LEFT);
            } elseif ($item->item_type === 'upc' && $item->upcCode) {
                $commodity = $item->upcCode->commodity;
                $code = $item->upcCode->name;
            } else {
                return 'ZZZ|9|ZZZ'; // Put items without codes at the end
            }

            // Priority: Regular PLU (0), Organic PLU (1), UPC (2)
            if ($item->item_type === 'plu' && ! $item->organic) {
                $priority = '0'; // Regular PLU first
            } elseif ($item->item_type === 'plu' && $item->organic) {
                $priority = '1'; // Organic PLU second
            } else {
                $priority = '2'; // UPC last
            }

            return $commodity.'|'.$priority.'|'.$code;
        });

        // For display, only show items with inventory > 0
        $this->listItems = $this->allListItems->filter(function ($item) {
            return $item->inventory_level > 0;
        });
    }

    // Prepare carousel - read-only version
    public function openCarousel()
    {
        $this->dispatch('carousel-ready-to-open');
    }

    public function toggleCopyModal()
    {
        $this->showCopyModal = ! $this->showCopyModal;

        if ($this->showCopyModal) {
            $this->customListName = $this->userList->name;
        } else {
            $this->resetCopyForm();
        }
    }

    public function copyList()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        // Validate the custom name
        if (empty(trim($this->customListName))) {
            session()->flash('error', 'Please provide a name for your list.');

            return;
        }

        try {
            // Copy the list for the current user WITH inventory levels
            // Use the UserList method which copies ALL items (including those with 0 inventory)
            $newList = $this->userList->copyForUserWithInventory(
                Auth::user(),
                trim($this->customListName)
            );

            // Close modal and redirect to the new list
            $this->showCopyModal = false;
            session()->flash('message', 'List copied successfully to your lists with inventory levels preserved!');

            return redirect()->route('lists.show', $newList);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to copy list. Please try again.');
        }
    }

    protected function resetCopyForm()
    {
        $this->customListName = '';
    }

    public function render()
    {
        // Create a map of PLU codes that have both regular and organic versions
        // Only applies to PLU items (UPC items don't have organic variants)
        $dualVersionPluCodes = $this->listItems
            ->where('item_type', 'plu')
            ->whereNotNull('plu_code_id')
            ->groupBy('plu_code_id')
            ->filter(function ($items) {
                return $items->where('organic', true)->isNotEmpty() &&
                       $items->where('organic', false)->isNotEmpty();
            })
            ->keys();

        // Build allItemsData for Alpine store (same structure as Show.php)
        $allItemsData = $this->allListItems->map(function ($item) {
            if ($item->item_type === 'plu' && $item->pluCode) {
                $displayCode = $item->organic ? '9'.$item->pluCode->plu : $item->pluCode->plu;
                $imageUrl = null;
                $hasImage = false;
                foreach (['jpg', 'png'] as $ext) {
                    $path = "product_images/{$item->pluCode->plu}.{$ext}";
                    if (Storage::disk('public')->exists($path)) {
                        $imageUrl = Storage::disk('public')->url($path);
                        $hasImage = true;
                        break;
                    }
                }

                return [
                    'id' => $item->id,
                    'item_type' => 'plu',
                    'plu_code_id' => $item->plu_code_id,
                    'plu' => $item->pluCode->plu,
                    'variety' => $item->pluCode->variety,
                    'commodity' => $item->pluCode->commodity,
                    'category' => $item->pluCode->category,
                    'organic' => $item->organic,
                    'inventory_level' => $item->inventory_level,
                    'size' => $item->pluCode->size,
                    'display_code' => $displayCode,
                    'image_url' => $imageUrl,
                    'has_image' => $hasImage,
                ];
            } else {
                $upcCode = $item->upcCode;
                if (! $upcCode) {
                    return null;
                }
                $hasImage = $upcCode->has_image ?? false;
                $imageUrl = $hasImage ? asset('storage/upc_images/'.$upcCode->upc.'.jpg') : null;

                return [
                    'id' => $item->id,
                    'item_type' => 'upc',
                    'upc_code_id' => $item->upc_code_id,
                    'upc' => $upcCode->upc,
                    'name' => $upcCode->name,
                    'commodity' => $upcCode->commodity,
                    'category' => $upcCode->category,
                    'organic' => false,
                    'inventory_level' => $item->inventory_level,
                    'brand' => $upcCode->brand,
                    'display_code' => $upcCode->upc,
                    'image_url' => $imageUrl,
                    'has_image' => $hasImage,
                ];
            }
        })->filter()->values();

        $title = $this->userList->name;
        $itemCount = $this->listItems->count();

        return view('livewire.lists.shared-view', [
            'listItems' => $this->listItems,
            'dualVersionPluCodes' => $dualVersionPluCodes,
            'allItemsData' => $allItemsData,
        ])->layout('layouts.app')
            ->title("{$title} - Shared PLU List | PLU Pro")
            ->layoutData([
                'metaDescription' => "{$title} - A shared produce PLU list with {$itemCount} items. View and copy this PLU code list for your grocery needs.",
                'canonical' => url("/list/{$this->userList->share_code}"),
            ]);
    }
}
