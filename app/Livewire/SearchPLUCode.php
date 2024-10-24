<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PLUCode;
use Livewire\WithPagination;

class SearchPLUCode extends Component
{
    use WithPagination;

    public $searchTerm = '';

    protected $queryString = ['searchTerm'];

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function render()
    {
        $pluCodes = PLUCode::where('code', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('item_name', 'like', '%' . $this->searchTerm . '%')
            ->paginate(10);

        return view('livewire.search-p-l-u-code', [
            'pluCodes' => $pluCodes,
        ]);
    }
}
