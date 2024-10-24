<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use App\Models\UserList;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $name;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function createList()
    {
        $this->validate();

        $list = Auth::user()->userLists()->create([
            'name' => $this->name,
        ]);

        return redirect()->route('lists.show', $list);
    }

    public function render()
    {
        return view('livewire.lists.create');
    }
}
