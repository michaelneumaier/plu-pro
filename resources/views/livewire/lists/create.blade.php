<div>
    <h1 class="text-2xl font-bold mb-4">Create New List</h1>

    <form wire:submit.prevent="createList">
        <div class="mb-4">
            <label class="block text-gray-700">List Name</label>
            <input type="text" wire:model="name" class="border p-2 w-full">
            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create List</button>
    </form>
</div>