<?php

namespace App\Livewire\Lists;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public function render()
    {
        $userLists = Auth::user()->userLists;

        return view('livewire.lists.index', [
            'userLists' => $userLists,
        ]);
    }
}
