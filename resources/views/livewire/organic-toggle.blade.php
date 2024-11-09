<button wire:click.stop="toggleOrganic" class="px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200 ease-in-out inline-flex items-center space-x-1 {{ 
        $isOrganic 
            ? 'bg-green-500 hover:bg-green-600 text-white' 
            : 'bg-gray-200 hover:bg-gray-300 text-gray-500' 
    }}" title="{{ $isOrganic ? 'Click to make conventional' : 'Click to make organic' }}">
    @if($isOrganic)
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    @else
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
    </svg>
    @endif
    <span>Organic</span>
</button>