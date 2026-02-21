@props(['items' => []])

@if(count($items) > 0)
<nav aria-label="Breadcrumb" class="bg-white border-b border-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-3">
        <ol class="flex items-center space-x-2 text-sm text-gray-500">
            @foreach($items as $index => $item)
                @if($index > 0)
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-gray-300 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </li>
                @endif
                <li>
                    @if(isset($item['url']) && $index < count($items) - 1)
                        <a href="{{ $item['url'] }}" class="hover:text-gray-700 hover:underline">{{ $item['label'] }}</a>
                    @else
                        <span class="text-gray-900 font-medium">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
@endif
