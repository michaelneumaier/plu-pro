<div class="{{ $sizeClasses }} {{ $class }} bg-gray-100 rounded-lg overflow-hidden">
    @if($imagePath)
    <img src="{{ $imagePath }}" alt="PLU {{ $plu }}" class="w-full h-full object-cover"
        onerror="this.onerror=null; this.src='{{ asset('images/placeholder.png') }}';">
    @else
    <div class="w-full h-full flex items-center justify-center bg-gray-200">
        <svg class="w-1/2 h-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>
    @endif
</div>