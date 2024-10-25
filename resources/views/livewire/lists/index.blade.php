<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Your Lists</h1>
        <a href="{{ route('lists.create') }}"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Create New List</a>
    </div>

    @if($userLists->count())
    <ul class="mt-4 space-y-2">
        @foreach($userLists as $list)
        <li class="border rounded-lg p-4 bg-white shadow hover:shadow-md transition">
            <a href="{{ route('lists.show', $list) }}" class="flex justify-between items-center w-full">
                <span class="text-blue-500 font-semibold">{{ $list->name }}</span>
                <span class="text-gray-600 text-sm">{{ $list->listItems->count() }} items</span>
            </a>
        </li>
        @endforeach
    </ul>
    @else
    <p class="mt-4">You have no lists yet.</p>
    @endif
</div>