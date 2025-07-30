<?php

namespace App\Livewire\Marketplace;

use App\Models\UserList;
use Livewire\Component;
use Livewire\WithPagination;

class Browse extends Component
{
    use WithPagination;

    public $search = '';

    public $category = '';

    public $sortBy = 'newest';

    public $viewMode = 'grid'; // grid or list

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => ''],
        'sortBy' => ['except' => 'newest'],
        'viewMode' => ['except' => 'grid'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatingSortBy()
    {
        $this->resetPage();
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'grid' ? 'list' : 'grid';
    }

    public function render()
    {
        $query = UserList::marketplace()
            ->with(['user', 'listItems.pluCode', 'listItems.upcCode']);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('marketplace_title', 'like', '%'.$this->search.'%')
                    ->orWhere('marketplace_description', 'like', '%'.$this->search.'%');
            });
        }

        // Apply category filter
        if ($this->category) {
            $query->byCategory($this->category);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'popular':
                $query->orderBy('copy_count', 'desc');
                break;
            case 'views':
                $query->orderBy('view_count', 'desc');
                break;
            case 'alphabetical':
                $query->orderBy('marketplace_title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('published_at', 'desc');
                break;
        }

        $lists = $query->paginate($this->viewMode === 'grid' ? 12 : 10);
        $categories = UserList::getMarketplaceCategories();

        return view('livewire.marketplace.browse', [
            'lists' => $lists,
            'categories' => $categories,
        ])->layout('layouts.app');
    }
}
