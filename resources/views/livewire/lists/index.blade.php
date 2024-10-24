<div>
    <h1 class="text-2xl font-bold mb-4">Your Lists</h1>
    <a href="{{ route('lists.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Create New List</a>

    @if($userLists->count())
    <ul class="mt-4">
        @foreach($userLists as $list)
        <li class="border-b py-2">
            <a href="{{ route('lists.show', $list) }}" class="text-blue-500">{{ $list->name }}</a>
            <a href="{{ route('lists.edit', $list) }}" class="text-gray-500 ml-2">Edit</a>
        </li>
        @endforeach
    </ul>
    @else
    <p class="mt-4">You have no lists yet.</p>
    @endif
</div>