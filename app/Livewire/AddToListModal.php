<?php

namespace App\Livewire;

use App\Models\PLUCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AddToListModal extends Component
{
    public bool $showModal = false;

    public $userLists = [];

    public ?int $selectedListId = null;

    public string $newListName = '';

    public ?int $pendingPluCodeId = null;

    public bool $pendingOrganic = false;

    public bool $showCreateForm = false;

    public ?string $pendingPluCode = null;

    public ?string $pendingVariety = null;

    public ?string $statusMessage = null;

    public ?string $statusType = null;

    protected $listeners = [
        'open-add-to-list-modal' => 'openModal',
    ];

    protected $rules = [
        'newListName' => 'required|string|max:255',
    ];

    public function openModal($pluCodeId, $organic = false)
    {
        if (! auth()->check()) {
            return;
        }

        $this->resetStatus();

        $this->pendingPluCodeId = $pluCodeId;
        $this->pendingOrganic = $organic;

        $pluCode = PLUCode::find($pluCodeId);
        if ($pluCode) {
            $this->pendingPluCode = $organic ? '9'.$pluCode->plu : $pluCode->plu;
            $this->pendingVariety = $pluCode->variety;
        }

        $this->userLists = Auth::user()->userLists()->select('id', 'name')->get()->toArray();
        $this->showCreateForm = empty($this->userLists);
        $this->newListName = '';
        $this->showModal = true;
    }

    public function addToList()
    {
        if (! auth()->check() || ! $this->pendingPluCodeId || ! $this->selectedListId) {
            return;
        }

        $list = Auth::user()->userLists()->where('id', $this->selectedListId)->first();
        if (! $list) {
            $this->setStatus('List not found.', 'error');

            return;
        }

        $exists = $list->listItems()
            ->where('plu_code_id', $this->pendingPluCodeId)
            ->where('organic', $this->pendingOrganic)
            ->exists();

        if ($exists) {
            $label = $this->pendingOrganic ? 'organic' : 'regular';
            $this->setStatus("This {$label} item is already in \"{$list->name}\".", 'info');

            return;
        }

        DB::transaction(function () use ($list) {
            $list->listItems()->create([
                'plu_code_id' => $this->pendingPluCodeId,
                'item_type' => 'plu',
                'inventory_level' => 0.0,
                'organic' => $this->pendingOrganic,
            ]);
        });

        $this->js("localStorage.setItem('plupro_last_list_id', '{$this->selectedListId}')");

        $this->setStatus("Added to \"{$list->name}\"!", 'success');

        $this->closeModalAfterDelay();
    }

    public function createListAndAdd()
    {
        if (! auth()->check() || ! $this->pendingPluCodeId) {
            return;
        }

        $this->validate(['newListName' => 'required|string|max:255']);

        $list = Auth::user()->userLists()->create([
            'name' => $this->newListName,
        ]);

        DB::transaction(function () use ($list) {
            $list->listItems()->create([
                'plu_code_id' => $this->pendingPluCodeId,
                'item_type' => 'plu',
                'inventory_level' => 0.0,
                'organic' => $this->pendingOrganic,
            ]);
        });

        $this->selectedListId = $list->id;
        $this->js("localStorage.setItem('plupro_last_list_id', '{$list->id}')");

        $this->userLists = Auth::user()->userLists()->select('id', 'name')->get()->toArray();
        $this->showCreateForm = false;
        $this->newListName = '';

        $this->setStatus("Created \"{$list->name}\" and added item!", 'success');

        $this->closeModalAfterDelay();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['selectedListId', 'pendingPluCodeId', 'pendingOrganic', 'pendingPluCode', 'pendingVariety', 'newListName', 'showCreateForm', 'statusMessage', 'statusType']);
    }

    protected function setStatus(string $message, string $type)
    {
        $this->statusMessage = $message;
        $this->statusType = $type;
    }

    protected function resetStatus()
    {
        $this->statusMessage = null;
        $this->statusType = null;
    }

    protected function closeModalAfterDelay()
    {
        $this->js('setTimeout(() => { $wire.closeModal() }, 1200)');
    }

    public function render()
    {
        return view('livewire.add-to-list-modal');
    }
}
