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

    /**
     * Reset to the first page when the search term is updated.
     */
    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    /**
     * Render the search input and PLU codes table.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $pluCodes = PLUCode::where('plu', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('variety', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('commodity', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('aka', 'like', '%' . $this->searchTerm . '%')
            ->paginate(10);

        return view('livewire.search-p-l-u-code', [
            'pluCodes' => $pluCodes,
        ]);
    }
}
